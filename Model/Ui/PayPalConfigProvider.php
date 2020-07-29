<?php

namespace IWD\CheckoutConnector\Model\Ui;

use IWD\CheckoutConnector\Helper\Data as Helper;
use IWD\CheckoutConnector\Model\CacheCleanerFlag;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Payment\Gateway\Config\Config;
use Magento\Quote\Model\Quote;

/**
 * Class PayPalConfigProvider
 */
class PayPalConfigProvider implements ConfigProviderInterface
{
    const CODE = 'iwd_checkout_paypal';
    const CONTAINER = 'iwd-paypal-container';

    /**
     * @var Random
     */
    private $random;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CheckoutSession
     */
    private $helper;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *  @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var CacheCleanerFlag
     */
    private $cacheCleanerFlag;

    /**
     * Constructor
     *
     * @param CheckoutSession $checkoutSession
     * @param Config $config
     * @param Random $random
     * @param Helper $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param CacheCleanerFlag $cacheCleanerFlag
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        Config $config,
        Random $random,
        Helper $helper,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        CacheCleanerFlag $cacheCleanerFlag
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->random = $random;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->cacheCleanerFlag = $cacheCleanerFlag;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'label' => $this->config->getValue('label'),
                    'description' => $this->config->getValue('description')
                ]
            ]
        ];
    }


    /**
     * @param $containerId
     * @return array
     */
    public function getButtonConfig($containerId)
    {
        return [
            'containerId' => $containerId,
            'checkoutPagePath' => $this->helper->getCheckoutPagePath(),
            'grandTotalAmount' => $this->getGrandTotalAmount(),
            'btnShape' => $this->getConfigData('btn_shape'),
            'btnColor' => $this->getConfigData('btn_color')
        ];
    }

    /**
     * @param $configs
     */
    public function updateConfig($configs) {
        foreach($configs as $configCode => $configValue) {
            $this->setConfigData($configCode, $configValue);
        }

        $this->cacheCleanerFlag->addFlag();
    }

    /**
     * @param $config
     * @return string
     */
    public function getConfigPath($config)
    {
        return 'payment/' . self::CODE . '/' . $config;
    }

    /**
     * @param $config
     * @return string
     */
    public function getConfigData($config)
    {
        return $this->scopeConfig->getValue($this->getConfigPath($config));
    }

    /**
     * @param $config
     * @param $value
     */
    public function setConfigData($config, $value)
    {
        $this->configWriter->save($this->getConfigPath($config),  $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getGeneratedContainerId()
    {
        return self::CONTAINER . $this->random->getRandomNumber();
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * @return string
     */
    public function getGrandTotalAmount()
    {
        return number_format($this->getQuote()->getBaseGrandTotal(),2,'.', '');
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->getQuote()->getBaseCurrencyCode();
    }
}
