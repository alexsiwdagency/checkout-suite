<?php
namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\array_iwd;
use IWD\CheckoutConnector\Api\UpdateConfigInterface;
use IWD\CheckoutConnector\Model\Ui\PayPalConfigProvider;

/**
 * Class UpdateConfig
 * @package IWD\CheckoutConnector\Model
 */
class UpdateConfig implements UpdateConfigInterface
{
    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * @var PayPalConfigProvider
     */
    private $payPalConfigProvider;

    /**
     * UpdateConfig constructor.
     *
     * @param AccessValidator $accessValidator
     * @param PayPalConfigProvider $payPalConfigProvider
     */
    public function __construct(
        AccessValidator $accessValidator,
        PayPalConfigProvider $payPalConfigProvider
    ) {
        $this->accessValidator = $accessValidator;
        $this->payPalConfigProvider = $payPalConfigProvider;
    }

    /**
     * @param mixed $access_tokens
     * @param mixed $data
     * @return array_iwd|string
     */
    public function updateConfig($access_tokens, $data)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        if(isset($data['paypal']) && $data['paypal']) {
            $this->payPalConfigProvider->updateConfig($data['paypal']);
        }

        return 'Success!';
    }
}
