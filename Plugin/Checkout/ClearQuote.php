<?php

namespace IWD\CheckoutConnector\Plugin\Checkout;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class ClearQuote
 *
 * @package IWD\CheckoutConnector\Plugin\Checkout
 */
class ClearQuote
{
    /**
     * @param CheckoutSession $subject
     * @return CheckoutSession
     * @throws Exception
     */
    public function afterClearQuote(CheckoutSession $subject)
    {
        $subject->setLoadInactive(false);
        $subject->replaceQuote($subject->getQuote()->save());

        return $subject;
    }
}