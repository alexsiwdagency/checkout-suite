<?php

namespace IWD\CheckoutConnector\Api;

/**
 * Interface OrderInterface
 *
 * @package IWD\CheckoutConnector\Api
 */
interface OrderInterface
{
    /**
     * @api
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param mixed $data
     * @return array_iwd
     */
    public function create($quote_id, $access_tokens, $data);

    /**
     * @api
     * @param mixed $access_tokens
     * @param mixed $data
     * @return array_iwd
     */
    public function update($access_tokens, $data);

    /**
     * @api
     * @param string $quote_id
     * @param mixed $access_tokens
     * @return array_iwd
     */
    public function getQuoteData($quote_id, $access_tokens);
}
