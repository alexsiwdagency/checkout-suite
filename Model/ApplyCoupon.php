<?php
namespace IWD\CheckoutConnector\Model;

use Exception;
use IWD\CheckoutConnector\Api\ApplyCouponInterface;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Model\Quote\Quote;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ApplyCoupon
 * @package IWD\CheckoutConnector\Model
 */
class ApplyCoupon implements ApplyCouponInterface
{
    /**
     * @var Cart\CartItems
     */
    private $cartItems;

    /**
     * @var Cart\CartTotals
     */
    private $cartTotals;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * ApplyCoupon constructor.
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param Quote $quote
     * @param AccessValidator $accessValidator
     */
    public function __construct(
        CartItems $cartItems,
        CartTotals $cartTotals,
        Quote $quote,
        AccessValidator $accessValidator
    ) {
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->quote = $quote;
        $this->accessValidator = $accessValidator;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param null $data
     * @return array|\IWD\CheckoutConnector\Api\array_iwd|mixed|string
     */
    public function getData($quote_id, $access_tokens, $data = null)
    {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        try {
            $result = $this->setCoupon($quote_id, $data);
        } catch (Exception $e) {
            $result = [
                'errors'  => true,
                'message' => $e->getMessage()
            ];
        }

        return $result;
    }

    /**
     * @param $quote_id
     * @param $data
     * @return mixed
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function setCoupon($quote_id, $data)
    {
        $coupon = isset($data['coupon_code']) ? $data['coupon_code'] : '';

        $quote = $this->quote->getQuote($quote_id);
        $quote->setCouponCode($coupon);
        $quote->collectTotals();
        $quote->save();

        $result['cart_items'] = $this->cartItems->getItems($quote);
        $result['cart']       = $this->cartTotals->getTotals($quote);

        if ($quote->getCouponCode() != $data['coupon_code']) {
            $result['error'] = 'The coupon code "' . $data['coupon_code'] . '" is not valid.';
        }

        return $result;
    }
}
