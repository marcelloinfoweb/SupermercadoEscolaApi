<?php

namespace Funarbe\SupermercadoEscolaApi\Model;

use Funarbe\SupermercadoEscolaApi\Api\AdicionarItemCompraManagementInterface;

class ExcluirItemCompraManagement implements AdicionarItemCompraManagementInterface
{
    private \Psr\Log\LoggerInterface $logger;
    private \Magento\Sales\Api\OrderRepositoryInterface $orderRepository;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param int $orderId
     * @param int $itemId
     * @throws \Exception
     */
    public function getExcluirItemCompra(int $orderId, int $itemId)
    {
        $_order = $this->orderRepository->get($orderId);
        $items = $_order->getAllItems();

        foreach ($items as $item) {
            $base_grand_total = $_order->getBaseGrandTotal();
            $base_subtotal = $_order->getBaseSubtotal();
            $base_tva = $_order->getBaseTaxAmount();
            $grand_total = $_order->getGrandTotal();
            $subtotal = $_order->getSubtotal();
            $tva = $_order->getTaxAmount();
            $base_subtotal_incl_tax = $_order->getBaseSubtotalInclTax();
            $subtotal_incl_tax = $_order->getSubtotalInclTax();
            $total_item_count = $_order->getTotalItemCount();

            if ($item->getProductId() == $itemId) {
                $item_price = $item->getPrice();
                $item_tva = $item->getTaxAmount();
                try {
                    $this->logger->info("[ INFO ] - Item $itemId da compra $orderId foi deletado com sucesso.");
                    $item->delete();
                } catch (\Exception $exception) {
                    $this->logger->error(
                        "[ ERROR ] - Item $itemId da compra $orderId não foi deletado",
                        ['exception' => $exception]
                    );
                }
                $_order->setBaseGrandTotal($base_grand_total - $item_price - $item_tva);
                $_order->setBaseSubtotal($base_subtotal - $item_price);
                $_order->setBaseTaxAmount($base_tva - $item_tva);
                $_order->setGrandTotal($grand_total - $item_price - $item_tva);
                $_order->setSubtotal($subtotal - $item_price);
                $_order->setTaxAmount($tva - $item_tva);
                $_order->setBaseSubtotalInclTax($base_subtotal_incl_tax - $item_price);
                $_order->setSubtotalInclTax($subtotal_incl_tax - $item_price);
                $_order->setTotalItemCount(count($items) - 1);
                $_order->save();
            }
        }
    }
}
