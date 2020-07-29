<?php

namespace IWD\CheckoutConnector\Block;

use Magento\Checkout\Block\Onepage as CheckoutOnepage;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\CompositeConfigProvider;
use IWD\CheckoutConnector\Helper\Data;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Class Frame
 *
 * @package IWD\CheckoutConnector\Block
 */
class Frame extends CheckoutOnepage
{
    const CMS_TYPE = 'Magento2';
    const CHECKOUT_IFRAME_ID = 'iwd_checkout_iframe';

    /**
     * @var CheckoutSession
     */
    public $checkoutSession;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var TokenFactory
     */
    private $tokenModelFactory;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * Frame constructor.
     *
     * @param Context $context
     * @param FormKey $formKey
     * @param CompositeConfigProvider $configProvider
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param TokenFactory $tokenModelFactory
     * @param Data $helper
     * @param Http $request
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        CompositeConfigProvider $configProvider,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        TokenFactory $tokenModelFactory,
        Data $helper,
        Http $request,
        JsonHelper $jsonHelper,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->helper = $helper;
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
    }

    /**
     * @return string
     */
    public function getCheckoutIframeId()
    {
        return self::CHECKOUT_IFRAME_ID;
    }

    /**
     * @return string
     */
    public function getFrameUrl()
    {
        $requestParams = $this->request->getParams();

        $checkoutUrl = $this->helper->getCheckoutAppUrl();
        $integrationApiKey = $this->helper->getIntegrationApiKey();
        $quoteId = $this->checkoutSession->getQuote()->getId();

        $params = [
            'api_key' => $integrationApiKey,
            'quote_id' => $quoteId,
            'customer_token' => $this->getCustomerToken()
        ];

        if(isset($requestParams['paypal_order_id']) && $requestParams['paypal_order_id']) {
            $params['paypal_order_id'] = $requestParams['paypal_order_id'];
        }

        if($this->customerSession->isLoggedIn()) {
            $params['customer_token'] = $this->getCustomerToken();
        }

        return $checkoutUrl . '?' . http_build_query($params);
    }

    /**
     * @return mixed
     */
    private function getCustomerToken()
    {
        if($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();
            $customerToken = $this->tokenModelFactory->create();

            return $customerToken->createCustomerToken($customerId)->getToken();
        }

        return 'empty';
    }

    /**
     * Get iframe config
     *
     * @return bool|false|string
     */
    public function getJsonConfig()
    {
        $config = $this->getIframeConfig();

        $implementationArray = [
            'IWD_CheckoutConnector/js/view/iwd_checkout' => $config
        ];

        return $this->jsonHelper->jsonEncode($implementationArray);
    }

    /**
     * @return array
     */
    private function getIframeConfig()
    {
        return [
            'checkoutIframeId' => $this->getCheckoutIframeId(),
            'editCartUrl'      => $this->getUrl('checkout/cart'),
            'loginUrl'         => $this->getUrl('customer/ajax/login/'),
            'resetPasswordUrl' => $this->getUrl('customer/account/forgotpassword/'),
            'successActionUrl' => $this->getUrl('iwd_checkout/index/success')
        ];
    }
}
