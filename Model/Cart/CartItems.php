<?php

namespace IWD\CheckoutConnector\Model\Cart;

use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Directory\Model\Currency;

/**
 * Class CartItems
 *
 * @package IWD\CheckoutConnector\Model\Cart
 */
class CartItems
{
    /**
     * @var ProductRepositoryInterfaceFactory
     */
    protected $productRepository;

    /**
     * @var ImageFactory
     */
    protected $productImageHelper;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * CartItems constructor.
     *
     * @param ProductRepositoryInterfaceFactory $productRepository
     * @param ImageFactory $productImageHelper
     * @param Currency $currency
     */
    public function __construct(
        ProductRepositoryInterfaceFactory $productRepository,
        ImageFactory $productImageHelper,
        Currency $currency
    ) {
        $this->productRepository = $productRepository;
        $this->productImageHelper = $productImageHelper;
        $this->currency = $currency;
    }

    /**
     * @param $quote
     * @return array
     */
    public function getItems($quote)
    {
        $data = [];

        foreach ($quote->getAllVisibleItems() as $index => $item) {
            $productData = $this->productRepository->create()->getById($item->getProductId());
            $imageUrl = $this->productImageHelper->create()->init($productData, 'product_thumbnail_image')->setImageFile($productData->getThumbnail())->getUrl();
            $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

            $data[] = [
                "name"    => $item->getName(),
                "sku"     => $item->getSku(),
                "price"   => $this->currency->format($item->getPrice(), ['display' => \Zend_Currency::NO_SYMBOL], false),
                "qty"     => $item->getQty(),
                "item_id" => $item->getProductId(),
                "type"    => $item->getProductType(),
                "image"   => $imageUrl,
                "options" => $this->getOptions($options),
            ];
        }

        return $data;
    }

    /**
     * @param $options
     * @return array
     */
    public function getOptions($options)
    {
        $product_options = [];

        if (!empty($options['attributes_info'])) {
            foreach ($options['attributes_info'] as $option) {
                $product_options[] = [
                    'label' => $option['label'],
                    'value' => $option['value'],
                ];
            }
        }

        if (!empty($options['bundle_options'])) {
            foreach ($options['bundle_options'] as $option) {
                $value = '';
                foreach ($option['value'] as $item) {
                    $value .= '(' . $item['qty'] . ') ' . $item['title'] . ' ' . $this->currency->format($item['price'], false, false);
                }

                $product_options[] = [
                    'label' => $option['label'],
                    'value' => $value,
                ];
            }
        }

        return $product_options;
    }
}
