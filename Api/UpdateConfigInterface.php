<?php

namespace IWD\CheckoutConnector\Api;

/**
 * Interface UpdateConfigInterface
 *
 * @package IWD\CheckoutConnector\Api
 */
interface UpdateConfigInterface
{
    /**
     * @api
     * @param mixed $access_tokens
     * @param mixed $data
     * @return array_iwd
     */
    public function updateConfig($access_tokens, $data);
}