<?php

namespace Funarbe\SupermercadoEscolaApi\Model;

use Exception;
use Funarbe\SupermercadoEscolaApi\Api\ExcluirItemCompraManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class ExcluirItemCompraManagement implements ExcluirItemCompraManagementInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

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
        $order = $this->orderRepository->get($orderId);
        foreach ($order->getAllItems() as $item) {

            if ($itemId === (int)$item->getProductId()) {
                $colaborador = $this->helper->getColaborador($order->getCustomerId());
                $item_price = $item->getRowTotal();

                $discount = 0.00;
                $comment = "Produto excluído: ";

                if ($colaborador === '1') {
                    $discount = abs(($item_price * 5) / 100);
                }

                try {
                    /* Deleta o produto */
                    $item->delete();

                    $this->logger->info("[ INFO ] - Item $itemId da compra $orderId foi deletado");
                } catch (Exception $e) {
                    $this->logger->error(
                        "[ ERROR ] - Item $itemId da compra $orderId não foi deletado ou não existe",
                        ['exception' => $e]
                    );
                }

                $order->setSubtotal($order->getSubtotal() - $item_price);
                $order->setBaseSubtotal($order->getBaseSubtotal() - $item_price);

                $order->setGrandTotal($order->getGrandTotal() - $item_price + $discount);
                $order->setBaseGrandTotal($order->getBaseGrandTotal() - $item_price + $discount);

                $order->setTotalItemCount($order->getTotalQtyOrdered() - $item->getQtyOrdered());

                $order->setDiscountAmount($order->getDiscountAmount() - $discount);
                $order->setBaseDiscountAmount($order->getDiscountAmount() - $discount);

                $order->addStatusHistoryComment($comment . "id " . $item->getId() . " - " . $item->getName(), false);
                $order->setIsCustomerNotified(false);


                $order->save();
            }
        }
    }
}
