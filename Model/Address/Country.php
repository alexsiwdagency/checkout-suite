<?php

namespace IWD\CheckoutConnector\Model\Address;

use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\TopDestinationCountries;
use Magento\Framework\App\ObjectManager;

/**
 * Class Country
 *
 * @package IWD\CheckoutConnector\Model\Address
 */
class Country
{
    /**
     * @var Collection
     */
    protected $countryCollection;

    /**
     * @var mixed
     */
    private $topDestinationCountries;

    /**
     * Country constructor.
     * @param Collection $countryCollection
     * @param TopDestinationCountries|null $topDestinationCountries
     */
    public function __construct(
        Collection $countryCollection,
        TopDestinationCountries $topDestinationCountries = null
    ) {
        $this->countryCollection = $countryCollection;
        $this->topDestinationCountries = $topDestinationCountries ?:
            ObjectManager::getInstance()
                ->get(TopDestinationCountries::class);
    }

    /**
     * @return array
     */
    public function getCountry()
    {
        $data = $this->countryCollection->loadByStore()
            ->setForegroundCountries($this->topDestinationCountries->getTopDestinations())
            ->toOptionArray();

        array_shift($data);

        return $data;
    }
}
