<?php

namespace IWD\CheckoutConnector\Controller\Index;

use IWD\CheckoutConnector\Controller\Action;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class Success
 *
 * @package IWD\CheckoutConnector\Controller\Index
 */
class Success extends Action
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();

        if (isset($params['quote_id']) && $params['quote_id']
            && isset($params['order_id']) && $params['order_id']
            && isset($params['order_increment_id']) && $params['order_increment_id']
            && isset($params['order_status']) && $params['order_status'])
        {
            $this->checkoutSession->setLastQuoteId($params['quote_id']);
            $this->checkoutSession->setLastSuccessQuoteId($params['quote_id']);
            $this->checkoutSession->clearHelperData();

            $this->checkoutSession->setLastOrderId($params['order_id']);
            $this->checkoutSession->setLastRealOrderId($params['order_increment_id']);
            $this->checkoutSession->setLastOrderStatus($params['order_status']);

            $resultRedirect->setPath('checkout/onepage/success');
        } else {
            $resultRedirect->setPath('/');
        }

        return $resultRedirect;
    }
}
