<?php

namespace IWD\CheckoutConnector\Plugin\Checkout\Controller\Index;

use IWD\CheckoutConnector\Helper\Data as Helper;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\UrlInterface;

/**
 * Class Index
 *
 * @package IWD\CheckoutConnector\Plugin\Checkout\Controller\Index
 */
class Index
{
    private $helper;
    private $url;
    private $response;

    /**
     * Index constructor.
     *
     * @param Helper $helper
     * @param ResponseHttp $response
     * @param UrlInterface $url
     */
    public function __construct(
        Helper $helper,
        ResponseHttp $response,
        UrlInterface $url
    ) {
        $this->helper = $helper;
        $this->response = $response;
        $this->url = $url;
    }

    public function beforeExecute()
    {
        if ($this->helper->isEnable()) {
            $url = $this->url->getUrl($this->helper->getCheckoutPagePath());

            $this->response->setRedirect($url);
        }
    }
}

