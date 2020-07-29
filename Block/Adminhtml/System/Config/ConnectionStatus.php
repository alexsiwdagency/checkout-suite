<?php

namespace IWD\CheckoutConnector\Block\Adminhtml\System\Config;

use IWD\CheckoutConnector\Helper\ApiAccessChecker;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class ConnectionStatus
 *
 * @package IWD\CartToQuote\Block\Adminhtml\System\Config
 */
class ConnectionStatus extends Field
{
    /**
     * @var ApiAccessChecker
     */
    private $apiAccessChecker;

    /**
     * @param Context $context
     * @param ApiAccessChecker $apiAccessChecker
     * @param array $data
     */
    public function __construct(
        Context $context,
        ApiAccessChecker $apiAccessChecker,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->apiAccessChecker = $apiAccessChecker;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $connectionCheck = $this->apiAccessChecker->checkIsAllow();

        $message = ($connectionCheck)
            ? '<b style="color:#059147; display: block;">' . __('Connection Successful') . '</b>'
            : '<b style="color:#D40707; display: block;">' . $this->apiAccessChecker->getErrorMessage() . '</b>';

        $note = $this->apiAccessChecker->getHelpText();
        $note = empty($note) ? '' : '<p class="note"><span>' . $note . '</span></p>';
        return "<span style='display:block; margin-top: 8px;'>" . $message . "</span>" . $note;
    }
}
