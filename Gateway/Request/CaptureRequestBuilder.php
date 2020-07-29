<?php

namespace IWD\CheckoutConnector\Gateway\Request;

use IWD\CheckoutConnector\Gateway\Config\Config;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Class CaptureRequestBuilder
 *
 * @package IWD\CheckoutConnector\Gateway\Request
 */
class CaptureRequestBuilder implements BuilderInterface
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
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $currencyCode = $order->getCurrencyCode();
        $payment = $paymentDO->getPayment();

        if (!$payment instanceof OrderPaymentInterface) {
            throw new \LogicException('Order payment should be provided.');
        }

        return [
            self::AMOUNT             => SubjectReader::readAmount($buildSubject),
            self::ORDER_ID           => $order->getOrderIncrementId(),
            self::TRANSACTION_ID     => $payment->getLastTransId(),
            self::INTEGRATION_KEY    => $this->config->getIntegrationApiKey(),
            self::INTEGRATION_SECRET => $this->config->getIntegrationApiSecret(),
            self::CURRENCY           => $currencyCode
        ];
    }
}
