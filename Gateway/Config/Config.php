<?php

namespace IWD\CheckoutConnector\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use IWD\CheckoutConnector\Helper\Data as Helper;

/**
 * Class Config
 *
 * @package IWD\CheckoutConnector\Gateway\Config
 */
class Config implements ConfigInterface
{
    const DEFAULT_PATH_PATTERN = 'payment/%s/%s';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string|null
     */
    private $methodCode;

    /**
     * @var string|null
     */
    private $pathPattern;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param Helper $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        Helper $helper,
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->methodCode = $methodCode;
        $this->pathPattern = $pathPattern;
    }

    /**
     * Sets method code
     *
     * @param string $methodCode
     * @return void
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
    }

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        if ($this->methodCode === null || $this->pathPattern === null) {
            return null;
        }
        return $this->scopeConfig->getValue(
            sprintf($this->pathPattern, $this->methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getIntegrationApiKey($storeId = null)
    {
        return $this->helper->getIntegrationApiKey($storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getIntegrationApiSecret($storeId = null)
    {
        return $this->helper->getIntegrationApiSecret($storeId);
    }

    /**
     * @return string
     */
    public function getGatewayUrl()
    {
        return $this->helper->getAppUrl() . '/platform/';
    }
}
