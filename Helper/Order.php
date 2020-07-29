<?php

namespace IWD\CheckoutConnector\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Group;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * Class Data
 *
 * @package IWD\CheckoutConnector\Helper
 */
class Order extends AbstractHelper
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * @var CreditmemoFactory
     */
    private $creditmemoFactory;

    /**
     * @var CreditmemoService
     */
    private $creditmemoService;

    /**
     * Order constructor.
     *
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param BuilderInterface $transactionBuilder
     * @param InvoiceService $invoiceService
     * @param InvoiceSender $invoiceSender
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoService $creditmemoService
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        BuilderInterface $transactionBuilder,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoService $creditmemoService
    ) {
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        parent::__construct($context);
    }

    /**
     * @param $quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function assignCustomerToQuote($quote)
    {
        $customerEmail = $quote->getBillingAddress()->getEmail();

        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($quote->getWebsiteId());
        $customer->loadByEmail($customerEmail);

        if (!$customer->getEntityId()) {
            // Assign Guest Customer if customer with email is not registered in our system
            $quote->setCustomerId(null)
                ->setCustomerEmail($customerEmail)
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(Group::NOT_LOGGED_IN_ID);
        } else {
            // Assign Existing Customer
            $customer = $this->customerRepository->getById($customer->getEntityId());
            $quote->assignCustomer($customer);
        }

        $quote->save();
    }

    /**
     * @param $order
     * @param $transaction
     * @param $type
     * @param $comment
     * @return string
     */
    public function addTransactionToOrder($order, $transaction, $type, $comment)
    {
        try {
            $txnId = $transaction['id'];

            // Prepare payment object
            $payment = $order->getPayment();
            $payment->setLastTransId($txnId);
            $payment->setTransactionId($txnId);
            $method = $payment->getMethodInstance();
            $methodTitle = $method->getTitle();

            // Formatted price
            $formattedPrice = $order->getBaseCurrency()->formatTxt($order->getGrandTotal());

            // Prepare transaction
            $transaction = $this->transactionBuilder->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($txnId)
                ->setFailSafe(true)
                ->build($type);

            // Add transaction to payment
            $payment->addTransactionCommentsToOrder(
                $transaction,
                __('%1 : the %2 amount is %3.', $methodTitle, $comment, $formattedPrice)
            );
            $parentTxnId = $this->addParentTransaction($order, $transaction, $type);
            $payment->setParentTransactionId($parentTxnId);
            // Save payment, transaction and order
            $payment->save();
            $order->save();
            $transaction->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $order
     * @param $txnId
     * @param $transaction
     * @param $type
     */
    private function addParentTransaction($order, $transaction, $type)
    {
        $authTransaction = $order->getPayment()->getAuthorizationTransaction();
        if ($type == TransactionInterface::TYPE_CAPTURE && $authTransaction) {
            $authTxnId = $authTransaction->getTxnId();
            $transaction->setParentTxnId($authTxnId);
            $authTransaction->setIsClosed(1);
            $authTransaction->save();
            return $authTxnId;
        }
        return  null;
    }

    /**
     * @param $order
     * @param $transaction
     * @throws LocalizedException
     */
    public function addInvoiceToOrder($order, $transaction)
    {
        if ($order->canInvoice()) {
            $txnId = $transaction['id'];

            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
            $invoice->setTransactionId($txnId);
            $invoice->register();
            $invoice->save();

            $order->addRelatedObject($invoice);

            if (!$invoice->getEmailSent()) {
                //Send Invoice mail to customer
                $this->invoiceSender->send($invoice);
            }

            $orderState = MagentoOrder::STATE_PROCESSING;
            $order->setState($orderState)->setStatus(MagentoOrder::STATE_PROCESSING);

            $order->addStatusHistoryComment(
                __('Invoice #%1 was automatically created. Email was sent to the customer.', $invoice->getId())
            );
            $order->setIsCustomerNotified(true);

            $order->save();
        }
    }

    /**
     * @param $order
     * @param $transaction
     * @throws LocalizedException
     *
     */
    public function refundOrder($order, $transaction)
    {
        $txnId = $transaction['id'];

        $creditmemo = $this->creditmemoFactory->createByOrder($order);
        $creditmemo->setTransactionId($txnId);

        $this->creditmemoService->refund($creditmemo);

        $order->save();
    }
}
