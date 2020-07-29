<?php

namespace IWD\CheckoutConnector\Gateway\Response;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

class RefundHandler implements HandlerInterface
{
    const TRANSACTION_ID = 'transaction_id';
    /**
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $payment->setTransactionId($response[self::TRANSACTION_ID]);
        $payment->setIsTransactionClosed(true);
        $closed = $this->needCloseParentTransaction($payment);
        $payment->setShouldCloseParentTransaction($closed);
    }

    /**
     * @param $payment
     * @return bool
     */
    private function needCloseParentTransaction($payment)
    {
        return !(bool)$payment->getCreditmemo()->getInvoice()->canRefund();
    }
}
