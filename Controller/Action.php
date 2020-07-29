<?php

namespace IWD\CheckoutConnector\Controller;

use IWD\CheckoutConnector\Helper\Data as Helper;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session\Proxy as CustomerSession;
use Magento\Framework\App\Action\Action as MagentoAction;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Action
 *
 * @package IWD\CheckoutConnector\Controller
 */
abstract class Action extends MagentoAction
{
    public $customerSession;
    public $customerRepository;
    public $accountManagement;
    public $onepage;
    public $checkoutHelper;
    public $resultRawFactory;
    public $checkoutSession;
    public $resultPageFactory;
    public $helper;

    /**
     * Action constructor.
     *
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param Onepage $onepage
     * @param CheckoutHelper $checkoutHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param RawFactory $resultRawFactory
     * @param CheckoutSession $checkoutSession
     * @param PageFactory $resultPageFactory
     * @param Helper $helper
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        Onepage $onepage,
        CheckoutHelper $checkoutHelper,
        CustomerRepositoryInterface $customerRepository,
        RawFactory $resultRawFactory,
        CheckoutSession $checkoutSession,
        PageFactory $resultPageFactory,
        Helper $helper,
        AccountManagementInterface $accountManagement
    ) {
        $this->helper = $helper;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->onepage = $onepage;
        $this->customerRepository = $customerRepository;
        $this->accountManagement = $accountManagement;
        $this->resultRawFactory = $resultRawFactory;
        $this->checkoutHelper = $checkoutHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->onepage->getQuote();
    }

    /**
     * @return bool
     */
    public function isQuoteValid()
    {
        $quote = $this->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            return false;
        }

        return true;
    }

    /**
     * Check can page show for unregistered users
     *
     * @return boolean
     */
    public function canShowForUnregisteredUsers()
    {
        return $this->customerSession->isLoggedIn()
            || $this->getRequest()->getActionName() == 'index'
            || $this->checkoutHelper->isAllowedGuestCheckout($this->getQuote())
            || !$this->checkoutHelper->isCustomerMustBeLogged();
    }

    /**
     * Make sure customer is valid, if logged in
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function preDispatchValidateCustomer()
    {
        try {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
        } catch (NoSuchEntityException $e) {
            return true;
        }

        if (isset($customer)) {
            $validationResult = $this->accountManagement->validate($customer);
            if (!$validationResult->isValid()) {
                foreach ($validationResult->getMessages() as $error) {
                    $this->messageManager->addErrorMessage($error);
                }
                return false;
            }
        }
        return true;
    }
}

