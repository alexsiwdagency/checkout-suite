<?php

namespace IWD\CheckoutConnector\Api;

/**
 * Interface PayPalCheckoutInterface
 *
 * @package IWD\CheckoutConnector\Api
 */
interface PayPalCheckoutInterface
{
    /**
     * @api
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param mixed $data
     * @return array_iwd
     */
    public function getData($quote_id, $access_tokens, $data = null);
}
