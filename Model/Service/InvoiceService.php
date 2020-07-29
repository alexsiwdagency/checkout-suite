<?php

declare(strict_types=1);

namespace IWD\CheckoutConnector\Model\Service;

use IWD\CheckoutConnector\Api\InvoiceManagementInterface;

use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\RefundInvoiceInterface;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class InvoiceService
 *
 * @package IWD\CheckoutConnector\Model\Service
 */
class InvoiceService implements InvoiceManagementInterface
{
    /**
     * @var InvoiceOrderInterface
     */
    private $invoiceOrder;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;
    /**
     * @var RefundInvoiceInterface
     */
    private $refundInvoice;
    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditMemoRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;


    /**
     * InvoiceService constructor.
     *
     * @param InvoiceOrderInterface $invoiceOrder
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param RefundInvoiceInterface $refundInvoice
     * @param CreditmemoRepositoryInterface $creditMemoRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        InvoiceOrderInterface $invoiceOrder,
        InvoiceRepositoryInterface $invoiceRepository,
        RefundInvoiceInterface $refundInvoice,
        CreditmemoRepositoryInterface $creditMemoRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->invoiceOrder = $invoiceOrder;
        $this->invoiceRepository = $invoiceRepository;
        $this->refundInvoice = $refundInvoice;
        $this->creditMemoRepository = $creditMemoRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param $order
     * @param $txnId
     * @return mixed|void
     */
    public function addInvoiceToOrder($order, $txnId)
    {
        if ($order->canInvoice()) {
            $invoiceId = $this->invoiceOrder->execute($order->getId(), false, [], true);
            $invoice = $this->invoiceRepository->get($invoiceId);

            $invoice->setCanVoidFlag(false);
            $invoice->setTransactionId($txnId);
            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);

            $this->invoiceRepository->save($invoice);
        }
    }

    /**
     * @param $order
     * @param $txnId
     * @return mixed|void
     */
    public function refundInvoiceByOrder($order, $txnId)
    {
        $creditMemoId = [];
        if ($order->hasInvoices()) {
            foreach ($order->getInvoiceCollection() as $invoice) {
                $creditMemoId[] = $this->refundInvoice->execute($invoice->getId());
            }

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(CreditmemoInterface::ENTITY_ID, $creditMemoId, 'in')
                ->create();

            $creditMemoItems = $this->creditMemoRepository->getList($searchCriteria)->getItems();
            foreach ($creditMemoItems as $creditMemoItem) {
                $creditMemoItem->setTransactionId($txnId);
                $this->creditMemoRepository->save($creditMemoItem);
            }
        }
    }
}
