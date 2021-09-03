<?php

namespace Funarbe\SupermercadoEscolaApi\Model;

use Funarbe\SupermercadoEscolaApi\Api\AdicionarItemCompraManagementInterface;

class AdicionarItemCompraManagement implements AdicionarItemCompraManagementInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Quote\Api\Data\CartItemInterfaceFactory
     */
    protected $cartItemFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Funarbe\SupermercadoEscolaApi\Model\ExcluirItemCompraManagement
     */
    private $excluirItemCompraManagement;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Funarbe\SupermercadoEscolaApi\Model\ExcluirItemCompraManagement $excluirItemCompraManagement
    ) {
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->cartItemFactory = $cartItemFactory;
        $this->quoteRepository = $quoteRepository;
        $this->orderItemFactory = $orderItemFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->excluirItemCompraManagement = $excluirItemCompraManagement;
    }

    /**
     * @param int $order_id
     * @param float $quantidade
     * @param float $price
     * @param int $sku
     * @param float $weight
     * @param int $itemId
     * @return bool|\Magento\Framework\Message\ManagerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAdicionarItemCompra(
        int $order_id,
        float $quantidade,
        float $price,
        int $sku,
        float $weight,
        int $itemId
    ) {
        $order = $this->orderRepository->get($order_id);
        $product = $this->productRepository->get($sku);
        $quote = $this->quoteRepository->get($order->getQuoteId());

        try {
            /* Add Quote Item Start */
            $quoteItem = $this->cartItemFactory->create();
            $quoteItem->setProduct($product);
            $quoteItem->setQty($quantidade);
            $quoteItem->setCustomPrice($price);
            $quoteItem->setOriginalCustomPrice($price);
            $quoteItem->getProduct()->setIsSuperMode(true);
            $quote->addItem($quoteItem);
            $quote->collectTotals()->save();
            /* Add Quote Item End */

            /* Add Order Item Start */
            $orderItem = $this->orderItemFactory->create();
            $orderItem
                ->setStoreId($order->getStoreId())
                ->setQuoteItemId($quoteItem->getId())
                ->setProductId($product->getId())
                ->setProductType($product->getTypeId())
                ->setName($product->getName())
                ->setSku($product->getSku())
                ->setQtyOrdered($quantidade)
                ->setPrice($price)
                ->setBasePrice($price)
                ->setOriginalPrice($price)
                ->setBaseOriginalPrice($price)
                ->setPriceInclTax($price)
                ->setBasePriceInclTax($price)
                ->setRowTotal($price)
                ->setBaseRowTotal($price)
                ->setRowTotalInclTax($price)
                ->setBaseRowTotalInclTax($price)
                ->setWeight($weight)
                ->setIsVirtual(0);
            $order->addItem($orderItem);
            /* Add Order Item End */

            /* Update relavant order totals Start */
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $price);
            $order->setGrandTotal($order->getGrandTotal() + $price);
            $order->setBaseSubtotal($order->getBaseSubtotal() + $price);
            $order->setSubtotal($order->getSubtotal() + $price);
            $order->setBaseSubtotalInclTax($order->getBaseSubtotalInclTax() + $price);
            $order->setSubtotalInclTax($order->getSubtotalInclTax() + $price);
            $order->setTotalItemCount($order->getTotalItemCount() + 1);
            $order->setTotalQtyOrdered($order->getTotalQtyOrdered() + 1);
            $this->orderRepository->save($order);
            /* Update relavant order totals End */

            /* DELETAR PRODUTO */
            $this->excluirItemCompraManagement->getExcluirItemCompra($order_id, $itemId);
            /* DELETAR PRODUTO */

        } catch (\Exception $e) {
            return $this->messageManager->addError($e->getMessage());
        }
        return true;
    }
}
