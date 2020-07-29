<?php

namespace IWD\CheckoutConnector\Gateway\Http;

use IWD\CheckoutConnector\Gateway\Config\Config;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;
    /**
     * @var ConfigInterface
     */
    private $config;
    /**
     * @var string
     */
    private $uri;

    /**
     * @param TransferBuilder $transferBuilder
     * @param Config $config
     * @param string $uri
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        Config $config,
        $uri
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
        $this->uri = $uri;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        $url = $this->getUrl();
        return $this->transferBuilder
            ->setMethod('POST')
            ->setUri($url)
            ->setBody($request)
            ->build();
    }

    private function getUrl()
    {
        $url = $this->config->getGatewayUrl();
        return $url . $this->uri;
    }
}
