<?php

namespace IWD\CheckoutConnector\Model\Cart;

use Magento\Directory\Model\Currency;

/**
 * Class CartTotals
 *
 * @package IWD\CheckoutConnector\Model\Cart
 */
class CartTotals
{
    /**
     * @var Currency
     */
    private $currency;

    /**
     * CartTotals constructor.
     *
     * @param Currency $currency
     */
    public function __construct(
        Currency $currency
    ) {
        $this->currency = $currency;
    }

    /**
     * @param $quote
     * @return array
     */
    public function getTotals($quote)
    {
        $quoteShippingAddress = $quote->getShippingAddress();

        return [
            'is_virtual'      => $quote->isVirtual(),
            'currency'        => $quote->getBaseCurrencyCode(),
            'currency_symbol' => $this->currency->getCurrencySymbol(),
            'subtotal'        => number_format($quote->getSubtotal(),2,'.',''),
            'shipping'        => number_format($quoteShippingAddress->getShippingAmount(),2,'.',''),
            'tax'             => number_format($quoteShippingAddress->getTaxAmount(),2,'.',''),
            'discount'        => number_format(abs($quoteShippingAddress->getDiscountAmount()),2,'.',''),
            'grand_total'     => number_format($quote->getBaseGrandTotal(),2,'.',''),
            'coupon_code'     => $quote->getCouponCode()
        ];
    }
}