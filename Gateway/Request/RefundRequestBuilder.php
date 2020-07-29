<?php

namespace IWD\CheckoutConnector\Gateway\Request;

use IWD\CheckoutConnector\Gateway\Config\Config;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class RefundRequestBuilder
 *
 * @package IWD\CheckoutConnector\Gateway\Request
 */
class RefundRequestBuilder implements BuilderInterface
{
    const AMOUNT = 'amount';
    const ORDER_ID = 'order_id';
    const INTEGRATION_KEY = 'integration_key';
    const INTEGRATION_SECRET = 'integration_secret';
    const TRANSACTION_ID = 'txn_id';
    const CURRENCY = 'currency';

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $orderDO = $paymentDO->getOrder();
        $currencyCode = $orderDO->getCurrencyCode();

        return [
            self::TRANSACTION_ID     => $payment->getParentTransactionId(),
            self::AMOUNT             => SubjectReader::readAmount($buildSubject),
            self::ORDER_ID           => $orderDO->getOrderIncrementId(),
            self::INTEGRATION_KEY    => $this->config->getIntegrationApiKey(),
            self::INTEGRATION_SECRET => $this->config->getIntegrationApiSecret(),
            self::CURRENCY           => $currencyCode
        ];
    }
}
