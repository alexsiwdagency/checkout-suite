<?php
namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\DeliveryStepInterface;
use IWD\CheckoutConnector\Model\Address\Addresses;
use IWD\CheckoutConnector\Model\Address\SaveToQuote;
use IWD\CheckoutConnector\Model\Address\ShippingMethods;
use IWD\CheckoutConnector\Model\Api\FormatData;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Model\Quote\Quote;

/**
 * Class DeliveryStep
 *
 * @package IWD\CheckoutConnector\Model
 */
class DeliveryStep implements DeliveryStepInterface
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
     * @var Api\FormatData
     */
    private $formatData;

    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * DeliveryStep constructor.
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param ShippingMethods $shippingMethods
     * @param SaveToQuote $toQuote
     * @param Addresses $address
     * @param FormatData $formatData
     * @param AccessValidator $accessValidator
     * @param Quote $quote
     */
    public function __construct(
        CartItems $cartItems,
        CartTotals $cartTotals,
        ShippingMethods $shippingMethods,
        SaveToQuote $toQuote,
        Addresses $address,
        FormatData $formatData,
        AccessValidator $accessValidator,
        Quote $quote
    ) {
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->shippingMethods = $shippingMethods;
        $this->toQuote = $toQuote;
        $this->address = $address;
        $this->formatData = $formatData;
        $this->accessValidator = $accessValidator;
        $this->quote = $quote;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param mixed $data
     * @return \IWD\CheckoutConnector\Api\array_iwd|string
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
     * @param null $data
     * @return mixed
     * @throws \Exception
     */
    public function prepareData($quote_id, $data = null)
    {
        $quote = $this->quote->getQuote($quote_id);
        $formatData = $this->formatData->format($data);

        if ($formatData != null) {
            $this->toQuote->saveAddress($quote, $formatData);
        }

        $shippingMethods = $this->shippingMethods->getShippingMethods($quote);
        $selectedShippingMethod = $this->shippingMethods->getSelectedShippingMethod($quote);

        if(!$quote->getIsVirtual()) {
            if(empty($selectedShippingMethod) || !$this->shippingMethods->isSelectedShippingMethodAvailable($shippingMethods, $selectedShippingMethod)) {
                $selectedShippingMethod = $shippingMethods[0];
            }

            if(isset($data['force_choose_delivery']) && $data['force_choose_delivery']) {
                $this->toQuote->saveShippingMethod($quote, $selectedShippingMethod['method_code']);
            }
        }

        $result['delivery_methods']       = $shippingMethods;
        $result['anchor_data']            = $this->address->formatAddress($quote)['shipping'];
        $result['chosen_delivery_method'] = $selectedShippingMethod;
        $result['cart_items']             = $this->cartItems->getItems($quote);
        $result['cart']                   = $this->cartTotals->getTotals($quote);

        return $result;
    }
}