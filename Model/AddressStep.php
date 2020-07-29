<?php

namespace IWD\CheckoutConnector\Model;

use Exception;
use IWD\CheckoutConnector\Api\AddressStepInterface;
use IWD\CheckoutConnector\Model\Address\Addresses;
use IWD\CheckoutConnector\Model\Address\Country;
use IWD\CheckoutConnector\Model\Address\Regions;
use IWD\CheckoutConnector\Model\Address\ShippingMethods;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Model\Quote\Quote;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AddressStep
 * @package IWD\CheckoutConnector\Model
 */
class AddressStep implements AddressStepInterface
{
    /**
     * @var CartItems
     */
    private $cartItems;

    /**
     * @var CartTotals
     */
    private $cartTotals;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var Regions
     */
    private $regions;

    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * @var ShippingMethods
     */
    private $shippingMethods;

    /**
     * @var Addresses
     */
    private $address;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * AddressStep constructor.
     *
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param Country $country
     * @param Regions $regions
     * @param AccessValidator $accessValidator
     * @param ShippingMethods $shippingMethods
     * @param Addresses $address
     * @param Quote $quote
     */
    public function __construct(
        CartItems $cartItems,
        CartTotals $cartTotals,
        Country $country,
        Regions $regions,
        AccessValidator $accessValidator,
        ShippingMethods $shippingMethods,
        Addresses $address,
        Quote $quote
    ) {
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->country = $country;
        $this->regions = $regions;
        $this->accessValidator = $accessValidator;
        $this->shippingMethods = $shippingMethods;
        $this->address = $address;
        $this->quote = $quote;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @return array|\IWD\CheckoutConnector\Api\array_iwd|mixed|string
     */
    public function getData($quote_id, $access_tokens)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        try {
            $data = $this->prepareData($quote_id);
        } catch (Exception $e) {
            $data = [
                'errors'  => true,
                'message' => $e->getMessage()
            ];
        }

        return $data;
    }

    /**
     * @param $quote_id
     * @return mixed
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function prepareData($quote_id)
    {
        $quote = $this->quote->getQuote($quote_id);

        $data['saved_addresses']     = $this->address->getSavedAddresses($quote);
        $data['addresses']           = $this->address->getCustomerAddresses($quote);
        $data['cart_items']          = $this->cartItems->getItems($quote);
        $data['cart']                = $this->cartTotals->getTotals($quote);
        $data['available_countries'] = $this->country->getCountry();
        $data['available_regions']   = $this->regions->getRegions();

        return $data;
    }
}