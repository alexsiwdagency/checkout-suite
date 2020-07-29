<?php

namespace IWD\CheckoutConnector\Model\Address;

use Magento\Directory\Model\ResourceModel\Region\Collection;

/**
 * Class Regions
 *
 * @package IWD\CheckoutConnector\Model\Address
 */
class Regions
{
    /**
     * @var Collection
     */
    protected $regionCollection;

    /**
     * Regions constructor.
     *
     * @param Collection $regionCollection
     */
    public function __construct(
        Collection $regionCollection
    ) {
        $this->regionCollection = $regionCollection;
    }

    /**
     * @return array
     */
    public function getRegions()
    {
        return $this->regionCollection->addAllowedCountriesFilter()->toOptionArray();
    }
}
