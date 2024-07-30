<?php

namespace WB\InvoiceEmail\Plugin\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\TransactionFactory;
use WB\InvoiceEmail\Logger\Logger;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PlaceAfterPlugin
{
    private $orderRepository;
    private $invoiceRepository;
    private $invoiceService;
    private $transactionFactory;
    private $logger;
    private $orderSender;
    private $scopeConfig;

    const XML_PATH_PAYMENT_METHODS = 'jco_custom_modules/wb_invoiceemail/payment_methods';

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        Logger $logger,
        OrderSender $orderSender,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
        $this->logger = $logger;
        $this->orderSender = $orderSender;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * After placing the order, create an invoice if the payment method is allowed
     *
     * @param \Magento\Sales\Api\OrderManagementInterface $subject
     * @param \Magento\Sales\Model\Order $result
     * @return \Magento\Sales\Model\Order
     */
    public function afterPlace(\Magento\Sales\Api\OrderManagementInterface $subject, $result)
    {
        // Retrieve order ID and payment method
        $orderId = $result->getId();
        $paymentMethod = $result->getPayment()->getMethodInstance()->getCode();

        // Retrieve allowed payment methods from configuration
        $allowedPaymentMethods = $this->scopeConfig->getValue(
            self::XML_PATH_PAYMENT_METHODS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        // Check if the payment method is allowed
        if (in_array($paymentMethod, explode(',', $allowedPaymentMethods))) {
            try {
                // Create invoice for the order
                $invoice = $this->createInvoice($orderId);

                // Send order confirmation email if the invoice is created
                if ($invoice) {
                    $this->sendOrderConfirmationEmail($result);
                }
            } catch (\Exception $e) {
                // Log any errors that occur during the invoice creation process
                $this->logger->error("Error creating invoice for order ID: {$orderId} - " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Create an invoice for the given order ID
     *
     * @param int $orderId
     * @return \Magento\Sales\Model\Order\Invoice|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createInvoice($orderId)
    {
        try {
            // Retrieve the order by ID
            $order = $this->orderRepository->get($orderId);
            if ($order && $order->canInvoice()) {
                // Prepare and register the invoice
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(true); // Enable email notification for the order
                $invoice->getOrder()->setIsInProcess(true);

                // Save the transaction
                $transactionSave = $this->transactionFactory->create()
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();

                return $invoice;
            }
        } catch (\Exception $e) {
            // Log any errors that occur during the invoice creation process
            $this->logger->error("Error creating invoice: " . $e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }

        return null;
    }

    /**
     * Send order confirmation email
     *
     * @param \Magento\Sales\Model\Order $order
     */
    protected function sendOrderConfirmationEmail($order)
    {
        try {
            // Send the order confirmation email
            $this->orderSender->send($order);
            // Log success message
            $this->logger->info("Order confirmation email sent for order ID: {$order->getId()}");
        } catch (\Exception $e) {
            // Log any errors that occur during the email sending process
            $this->logger->error("Error sending order confirmation email for order ID: {$order->getId()} - " . $e->getMessage());
        }
    }
}
