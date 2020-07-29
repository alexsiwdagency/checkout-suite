<?php

namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Helper\Data as Helper;

/**
 * Class AddressStep
 *
 * @package IWD\CheckoutConnector\Model
 */
class AccessValidator
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * AccessValidator constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param $tokens
     * @return bool
     */
    public function checkAccess($tokens)
    {
        $integrationApiSecret = $this->helper->getIntegrationApiSecret();

        return $tokens['secret'] === $integrationApiSecret;
    }
}

