<?php

namespace IWD\CheckoutConnector\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class VoidHandler implements HandlerInterface
{
    const TRANSACTION_ID = 'transaction_id';
    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        if ($paymentDO->getPayment() instanceof Payment) {
            /** @var Payment $orderPayment */
            $orderPayment = $paymentDO->getPayment();
            $orderPayment->setIsTransactionClosed(true);
            $orderPayment->setShouldCloseParentTransaction(true);
        }
    }
}
