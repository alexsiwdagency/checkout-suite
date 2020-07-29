<?php

namespace IWD\CheckoutConnector\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Client\Curl;
use IWD\CheckoutConnector\Helper\Data as Helper;
use IWD\CheckoutConnector\Model\Ui\PayPalConfigProvider;

/**
 * Class Data
 *
 * @package IWD\CheckoutConnector\Helper
 */
final class ApiAccessChecker extends AbstractHelper
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var null
     */
    private $apiResponse = null;

    /**
     * @var PayPalConfigProvider
     */
    private $payPalConfigProvider;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param Curl $curl
     * @param Data $helper
     * @param PayPalConfigProvider $payPalConfigProvider
     */
    public function __construct(
        Context $context,
        Curl $curl,
        Helper $helper,
        PayPalConfigProvider $payPalConfigProvider
    ) {
        $this->curl = $curl;
        $this->helper = $helper;
        $this->payPalConfigProvider = $payPalConfigProvider;
        parent::__construct($context);
    }

    /**
     * @return bool|string
     */
    public function checkIsAllow()
    {
        if ($this->apiResponse === null) {
            try {
                $params = $this->prepareParams();
                $url = $this->helper->getCheckConnectionAppUrl().'?'.http_build_query($params);

                $this->curl->get($url);

                $response = $this->curl->getBody();

                $parsedResponse = $this->parseResponse($response);

                $this->apiResponse = $parsedResponse;

                if(!$parsedResponse['Error']) {
                    if(isset($parsedResponse['paypal']) && $parsedResponse['paypal']) {
                        $this->payPalConfigProvider->updateConfig($parsedResponse['paypal']);
                    }
                }
            } catch (\Exception $e) {
                $this->apiResponse = [
                    'Error' => 1,
                    'ErrorMessage' => $e->getMessage(),
                    'ErrorCode' => ($e->getCode() == 111)
                        ? 'api_key_empty'
                        : (($e->getCode() == 222) ? 'connect_error' : $e->getCode())
                ];
            }
        }

        return (bool)$this->apiResponse['Error'] === false;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function prepareParams()
    {
        $apiKey = $this->helper->getIntegrationApiKey();
        $apiSecret = $this->helper->getIntegrationApiSecret();

        if (empty($apiKey)) {
            throw new \Exception('Integration API Key is empty. Please, enter valid API Key.', 111);
        }

        if (empty($apiSecret)) {
            throw new \Exception('Integration API Secret is empty. Please, enter valid API Secret.', 111);
        }

        return [
            'api_key'     => $apiKey,
            'api_secret'  => $apiSecret,
            'platform'    => $this->helper->getPlatform(),
            'website_url' => $this->helper->getCleanStoreUrl()
        ];
    }

    /**
     * @param $response
     * @return array|bool|mixed|string
     */
    private function parseResponse($response)
    {
        if (empty($response)) {
            return ['Error' => 'connect_error'];
        }

        $response = json_decode($response, true);

        return $response;
    }

    /**
     * @return null
     */
    public function getLastResponse()
    {
        return $this->apiResponse;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        $response = $this->getLastResponse();

        if (isset($response['ErrorCode'])) {
            switch ($response['ErrorCode']) {
                case 'wrong_api_credentials':
                    return 'Wrong Integration API Credentials';
                case 'not_configured_payments':
                    return 'Not Configured Payments';
                case 'wrong_website_url':
                    return 'Wrong Integration Website URL';
                case 'wrong_platform':
                    return 'Wrong Integration Platform Type';
                case 'api_key_empty':
                    return 'Empty API Key Field';
                case 'connect_error':
                    return 'Connection error';
            }
        }

        return isset($response['ErrorMessage']) ? $response['ErrorMessage'] : 'API Error!';
    }

    /**
     * @return string
     */
    public function getHelpText()
    {
        $response = $this->getLastResponse();

        if (isset($response['ErrorCode'])) {
            $platform = $this->helper->getPlatform();
            $iwdSiteUrl = '<a href="https://www.iwdagency.com">IWD Agency Site</a>';
            $checkoutAdminUrl = '<a href="https://www.iwdagency.com/account?checkout-saas">Account > IWD Checkout > Integrations</a>';

            switch ($response['ErrorCode']) {
                case 'wrong_api_credentials':
                    return "We were unable to locate an Integration with your Api Key & Secret. Please enter valid API Key & Secret from your $checkoutAdminUrl on our $iwdSiteUrl.";
                case 'not_configured_payments':
                    return "Your Payment Methods are not configured. Please go to your $checkoutAdminUrl on our $iwdSiteUrl and configure at least one Payment Method.";
                case 'wrong_website_url':
                    return "Your current Store URL differs from the Website URL saved for your Integration. Please go to your $checkoutAdminUrl on our $iwdSiteUrl and change Website URL value for your Integration";
                case 'wrong_platform':
                    return "Your current Platform Type differs from the Platform saved for your Integration. Please go to your $checkoutAdminUrl on our $iwdSiteUrl, change Platform to '$platform' and Save.";
                case 'api_key_empty':
                    return "Please enter the Integration API Key. You can find it after purchasing IWD Checkout SaaS in $checkoutAdminUrl on our $iwdSiteUrl";
                case 'connect_error':
                    return "Could not connect to server API. Please contact our $iwdSiteUrl support";
            }
        }

        return '';
    }
}
