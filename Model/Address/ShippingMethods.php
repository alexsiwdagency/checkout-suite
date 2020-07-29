<?php

namespace IWD\CheckoutConnector\Model\Address;

use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Model\ShippingMethodManagement;

/**
 * Class ShippingMethods
 *
 * @package IWD\CheckoutConnector\Model\Address
 */
class ShippingMethods
{

    /**
     * @var Addresses
     */
    private $addresses;

    /**
     * @var ShippingMethodManagement
     */
    private $shippingMethodManagement;

    /**
     * @var EstimateAddressInterface
     */
    private $estimateAddress;

    /**
     * ShippingMethods constructor.
     * @param Addresses $addresses
     * @param ShippingMethodManagement $shippingMethodManagement
     * @param EstimateAddressInterface $estimateAddress
     */
    public function __construct(
        Addresses $addresses,
        ShippingMethodManagement $shippingMethodManagement,
        EstimateAddressInterface $estimateAddress
    ) {
        $this->addresses = $addresses;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->estimateAddress = $estimateAddress;
    }

    /**
     * @param $quote
     * @return array
     */
    public function getShippingMethods($quote)
    {
        $address = $this->addresses->getAddresses($quote)['shipping'];

        $addressInterface = $this->estimateAddress;
        $addressInterface->setCountryId($address->getCountryId());
        $addressInterface->setRegionId($address->getRegionId());
        $addressInterface->setRegion($address->getRegion());
        $addressInterface->setPostcode($address->getPostcode());

        $methods = $this->shippingMethodManagement->estimateByAddress($quote->getId(), $addressInterface);
        $result = [];

        foreach ($methods as $key => $method) {
            if($method->getAvailable()) {
                $result[] = [
                    'method_code'   => $method->getCarrierCode().'_'.$method->getMethodCode(),
                    'carrier_title' => $method->getCarrierTitle(),
                    'method_title'  => $method->getMethodTitle(),
                    'amount'        => (number_format($method->getAmount(),2,'.','')),
                ];
            }
        }

        return $result;
    }


    /**
     * @param $quote
     * @return mixed
     */
    public function getSelectedShippingMethod($quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = [];

        if($shippingAddress->getShippingMethod()) {
            $shippingMethod['method_code'] = $shippingAddress->getShippingMethod();
            $shippingMethod['amount'] = number_format($shippingAddress->getShippingAmount(),2,'.','');
            $shippingMethod['carrier_title'] = $shippingAddress->getShippingDescription();
        }

        return $shippingMethod;
    }

    /**
     * @param $shippingMethods
     * @param $selectedShippingMethod
     * @return bool
     */
    public function isSelectedShippingMethodAvailable($shippingMethods, $selectedShippingMethod) {
        foreach($shippingMethods as $shippingMethod) {
            if($shippingMethod['method_code'] === $selectedShippingMethod['method_code']) {
                return true;
            }
        }

        return false;
    }
}