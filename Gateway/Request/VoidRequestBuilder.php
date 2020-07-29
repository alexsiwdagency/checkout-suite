<?php

namespace IWD\CheckoutConnector\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use IWD\CheckoutConnector\Gateway\Config\Config;

/**
 * Class VoidRequestBuilder
 *
 * @package IWD\CheckoutConnector\Gateway\Request
 */
class VoidRequestBuilder implements BuilderInterface
{
    const TRANSACTION_ID = 'txn_id';
    const ORDER_ID = 'order_id';
    const INTEGRATION_KEY = 'integration_key';
    const INTEGRATION_SECRET = 'integration_secret';

    /**
     * @var Config
     */
    private $config;

    /**
     * VoidRequestBuilder constructor.
     *
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
     * @param  array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $orderDO = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        return [
            self::TRANSACTION_ID     => $payment->getLastTransId(),
            self::ORDER_ID           => $orderDO->getOrderIncrementId(),
            self::INTEGRATION_KEY    => $this->config->getIntegrationApiKey(),
            self::INTEGRATION_SECRET => $this->config->getIntegrationApiSecret(),
        ];
    }
}
