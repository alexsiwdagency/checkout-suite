<?php

namespace IWD\CheckoutConnector\Controller\Index;

use IWD\CheckoutConnector\Controller\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;

/**
 * Class Index
 *
 * @package IWD\CheckoutConnector\Controller\Index
 */
class Index extends Action
{
    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if ($this->getQuote()->isMultipleShippingAddresses()) {
            $this->getQuote()->removeAllAddresses();
        }

        return parent::dispatch($request);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     * @throws NotFoundException
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!$this->helper->isEnable()
            || !$this->helper->isModuleOutputEnabled('IWD_CheckoutConnector')
        ) {
            return $this->resultRedirectFactory->create()->setPath('checkout');
        }

        if (!$this->preDispatchValidateCustomer()) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return $this->resultRedirectFactory->create()->setPath('customer/account/edit');
        }

        if (!$this->canShowForUnregisteredUsers()) {
            throw new NotFoundException(__('Page not found.'));
        }

        if (!$this->checkoutHelper->canOnepageCheckout()) {
            $this->messageManager->addErrorMessage(__('One-page checkout is turned off.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $quote = $this->onepage->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        if (!$this->customerSession->isLoggedIn() && !$this->checkoutHelper->isAllowedGuestCheckout($quote)) {
            $this->messageManager->addErrorMessage(__('Guest checkout is disabled. Please Login or Create an Account'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $this->customerSession->regenerateId();
        $this->checkoutSession->setCartWasUpdated(false);
        $this->onepage->initCheckout();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set('IWD Checkout');

        return $resultPage;
    }
}
