<?php

namespace IWD\CheckoutConnector\Observer;

use IWD\CheckoutConnector\Block\Shortcut\PayPalButton;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AddPayPalButton
 *
 * @package IWD\ApplePay\Observer
 */
class AddPayPalButton implements ObserverInterface
{
    /**
     * Add PayPal Button
     *
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        // Check if we are not in Product Section
        if (!$observer->getData('is_catalog_product')) {
            /** @var ShortcutButtons $shortcutButtons */
            $shortcutButtons = $observer->getEvent()->getContainer();
            $shortcut = $shortcutButtons->getLayout()->createBlock(PayPalButton::class);

            /** Add PayPal btn to Shortcuts Section */
            $shortcutButtons->addShortcut($shortcut);
        }
    }
}