<?php

namespace IWD\CheckoutConnector\Plugin\Checkout\Block\Cart;

use IWD\CheckoutConnector\Helper\Data as Helper;
use Magento\Framework\UrlInterface;

/**
 * Class Sidebar
 *
 * @package IWD\CheckoutConnector\Plugin\Checkout\Block\Cart
 */
class Sidebar
{
    public $helper;
    public $url;

    /**
     * Sidebar constructor.
     *
     * @param Helper $helper
     * @param UrlInterface $url
     */
    public function __construct(
        Helper $helper,
        UrlInterface $url
    ) {
        $this->helper = $helper;
        $this->url = $url;
    }

    /**
     * @param $subject
     * @param $result
     * @return string
     */
    public function afterGetCheckoutUrl($subject, $result)
    {
        if ($this->helper->isEnable()) {
            $result = $this->url->getUrl($this->helper->getCheckoutPagePath());
        }

        return $result;
    }
}
