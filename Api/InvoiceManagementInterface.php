<?php

namespace IWD\CheckoutConnector\Api;

/**
 * Interface InvoiceManagementInterface
 *
 * @package IWD\CheckoutConnector\Api
 */
interface InvoiceManagementInterface
{
    /**
     * @param $order
     * @param $txnId
     * @return mixed
     */
    public function addInvoiceToOrder($order, $txnId);

    /**
     * @param $order
     * @param $txnId
     * @return mixed
     */
    public function refundInvoiceByOrder($order, $txnId);
}
