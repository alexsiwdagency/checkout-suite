<?php

namespace IWD\CheckoutConnector\Model\Quote;

use Magento\Quote\Model\QuoteFactory;

/**
 * Class Quote
 * @package IWD\CheckoutConnector\Model\Quote
 */
class Quote
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * Quote constructor.
     *
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        QuoteFactory $quoteFactory
    ) {
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * @param $quote_id
     * @return \Magento\Quote\Model\Quote
     * @throws \Exception
     */
    public function getQuote($quote_id)
    {
        $quote = null;
        $quote = $this->quoteFactory->create()->load($quote_id);

        if (!$quote->getId()) {
            throw new \Exception('Quote ID is invalid.');
        }

        return $quote;
    }
}
