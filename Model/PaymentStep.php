<?php
namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\PaymentStepInterface;
use IWD\CheckoutConnector\Model\Address\Addresses;
use IWD\CheckoutConnector\Model\Address\SaveToQuote;
use IWD\CheckoutConnector\Model\Address\ShippingMethods;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Model\Quote\Quote;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PaymentStep
 *
 * @package IWD\CheckoutConnector\Model
 */
class PaymentStep implements PaymentStepInterface
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
     * @var Address\ShippingMethods
     */
    private $shippingMethods;

    /**
     * @var Address\SaveToQuote
     */
    private $toQuote;

    /**
     * @var Address\Addresses
     */
    private $address;

    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * PaymentStep constructor.
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param ShippingMethods $shippingMethods
     * @param SaveToQuote $toQuote
     * @param Addresses $address
     * @param AccessValidator $accessValidator
     * @param Quote $quote
     */
    public function __construct(
        CartItems $cartItems,
        CartTotals $cartTotals,
        ShippingMethods $shippingMethods,
        SaveToQuote $toQuote,
        Addresses $address,
        AccessValidator $accessValidator,
        Quote $quote
    ) {
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->shippingMethods = $shippingMethods;
        $this->toQuote = $toQuote;
        $this->address = $address;
        $this->accessValidator = $accessValidator;
        $this->quote = $quote;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param null $data
     * @return array|\IWD\CheckoutConnector\Api\array_iwd|mixed|string
     */
    public function getData($quote_id, $access_tokens, $data = null)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        try {
            $result = $this->prepareData($quote_id, $data);
        } catch (\Exception $e) {
            $result = [
                'errors' => true,
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
     */
    public function prepareData($quote_id, $data)
    {
        $quote = $this->quote->getQuote($quote_id);

        if(isset($data['shipping_method']) && $data['shipping_method']) {
            $this->toQuote->saveShippingMethod($quote, $data['shipping_method']);
        }

        $result['addresses']              = $this->address->formatAddress($quote);
        $result['chosen_delivery_method'] = $this->shippingMethods->getSelectedShippingMethod($quote);
        $result['cart_items']             = $this->cartItems->getItems($quote);
        $result['cart']                   = $this->cartTotals->getTotals($quote);

        return $result;
    }
}