<?php

namespace IWD\CheckoutConnector\Model\Api;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;

/**
 * Class FormatData
 *
 * @package IWD\CheckoutConnector\Model\Api
 */
class FormatData
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * FormatData constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param null $data
     * @return array|null
     */
    public function format($data = null)
    {
        if ($data == null) {
            return null;
        }

        $formatData = [];
        $email = $data['email'];
        $billToDiffAddress = $data['bill_to_different_address'];

        unset($data['email']);
        unset($data['bill_to_different_address']);

        foreach ($data as $key => $item) {
            $regionId = isset($item["region_id"]) ? $item["region_id"] : null;

            if(!$regionId && $regionData = $this->getRegionDataFromNameIfExists($item["state"])) {
                $regionId = $regionData['region_id'];
            }

            $formatData[$key] = [
                "region_id"       => $regionId,
                "region"          => isset($item["state"]) ? $item["state"] : null,
                "country_id"      => $item['country'],
                "street"          => $item['address'],
                "postcode"        => $item['postcode'],
                "city"            => $item['city'],
                "firstname"       => $item['first_name'],
                "lastname"        => $item['last_name'],
                "telephone"       => isset($item["phone"]) ? $item["phone"] : null,
                "email"           => $email,
                "same_as_billing" => $billToDiffAddress ? $billToDiffAddress : 0,
            ];
        }

        return $formatData;
    }

    /**
     * @param $regionName
     * @return mixed
     */
    public function getRegionDataFromNameIfExists($regionName)
    {
        $regionData = $this->collectionFactory->create()
            ->addRegionNameFilter($regionName)
            ->getFirstItem()
            ->toArray();

        if($regionData) {
            return $regionData;
        }
        return null;
    }
}