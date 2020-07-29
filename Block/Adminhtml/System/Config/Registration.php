<?php

namespace IWD\CheckoutConnector\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Documentation
 * @package IWD\CheckoutConnector\Block\Adminhtml\System\Config
 */
class Registration extends Field
{
    private $userGuideUrl = "https://www.iwdagency.com/account/register";

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return sprintf(
            "<span style='margin-bottom:-8px; display:block;'><a href='%s'>%s</a></span>",
            $this->userGuideUrl,
            __("Registration")
        );
    }
}
