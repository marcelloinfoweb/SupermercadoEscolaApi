<?php

namespace Funarbe\SupermercadoEscolaApi\Model;

use Funarbe\SupermercadoEscolaApi\Api\ExcluirItemCompraManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class ExcluirItemCompraManagement implements ExcluirItemCompraManagementInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param int $orderId
     * @param int $itemId
     * @param float $qty_ordered
     * @throws \Exception
     */
    public function getExcluirItemCompra(int $orderId, int $itemId, float $qty_ordered)
    {

        $_order = $this->orderRepository->get($orderId);
        $items = $_order->getAllItems();

        $base_grand_total = $_order->getBaseGrandTotal();
        $base_subtotal = $_order->getBaseSubtotal();
        $grand_total = $_order->getGrandTotal();
        $subtotal = $_order->getSubtotal();
        $total_item_count = $_order->getTotalItemCount();

        foreach ($items as $item) {

            if ($itemId === (int)$item->getProductId()) {
                $customerGroup = $_order->getCustomerGroupId();
                $item_price = $item->getPrice() * $qty_ordered;

                $discount = 0.00;

                if ($customerGroup === '4') {
                    $baseDiscount = 5;
                    $discount = ($item_price * $baseDiscount) / 100;
                }

                try {
                    $this->logger->info(
                        "[ INFO ] - Item $itemId da compra $orderId foi deletado"
                    );
                    /* Deleta o produto */
                    $item->delete();

                } catch (\Exception $e) {
                    $this->logger->error(
                        "[ ERROR ] - Item $itemId da compra $orderId não foi deletado ou não existe",
                        ['exception' => $e]
                    );
                }

                $_order->setBaseGrandTotal($base_grand_total - $item_price);
                $_order->setBaseSubtotal($base_subtotal - $item_price);
                $_order->setGrandTotal($grand_total - $item_price);
                $_order->setSubtotal($subtotal - $item_price);
                //$_order->setTotalItemCount(count($items) - 1);
                $_order->setTotalItemCount($total_item_count - 1);
                $_order->setDiscountAmount(abs($_order->getDiscountAmount()) - $discount);
                $_order->save();
            }
        }
    }
}
