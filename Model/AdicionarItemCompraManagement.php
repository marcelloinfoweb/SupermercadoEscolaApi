<?php

namespace Funarbe\SupermercadoEscolaApi\Model;

use Exception;
use Funarbe\Helper\Helper\Data;
use Funarbe\SupermercadoEscolaApi\Api\AdicionarItemCompraManagementInterface;

class AdicionarItemCompraManagement implements AdicionarItemCompraManagementInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected \Magento\Sales\Api\OrderRepositoryInterface $orderRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected \Magento\Catalog\Api\ProductRepositoryInterface $productRepository;

    /**
     * @var \Magento\Quote\Api\Data\CartItemInterfaceFactory
     */
    protected \Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected \Magento\Quote\Api\CartRepositoryInterface $quoteRepository;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected \Magento\Sales\Model\Order\ItemFactory $orderItemFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected \Magento\Framework\App\Request\Http $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected \Magento\Framework\Message\ManagerInterface $messageManager;
    /**
     * @var \Funarbe\Helper\Helper\Data
     */
    private Data $helper;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerEntityFactory,
        \Funarbe\Helper\Helper\Data $data
    ) {
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->cartItemFactory = $cartItemFactory;
        $this->quoteRepository = $quoteRepository;
        $this->orderItemFactory = $orderItemFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->customerEntityFactory = $customerEntityFactory;
        $this->helper = $data;
    }

    /**
     * @param int $order_id
     * @param float $quantidade
     * @param float $price
     * @param int $sku
     * @param int $itemId
     * @return bool|\Magento\Framework\Message\ManagerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAdicionarItemCompra(
        int $order_id,
        float $quantidade,
        float $price,
        int $sku,
        int $itemId
    ) {
        $order = $this->orderRepository->get($order_id);
        $product = $this->productRepository->get($sku);
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $colaborador = $this->helper->getColaborador($order->getCustomerId());

        $price = $product->getPrice();
        $priceQty = $price * $quantidade;
        $comment = 'Produto adicionado: ';

        $discount = 0.00;
        if ($colaborador === '1') {
            $discount = abs(($priceQty * 5) / 100);
        }

        try {
            /* Add Quote Item Start */
            $quoteItem = $this->cartItemFactory->create();
            $quoteItem->setProduct($product)
                ->setQty($quantidade)
                ->setCustomPrice($price)
                ->getProduct()->setIsSuperMode(true);

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
                ->setPrice($product->getPrice())
                ->setBasePrice($product->getPrice())
                ->setOriginalPrice($product->getPrice())
                ->setBaseOriginalPrice($product->getPrice())
                ->setRowTotal($product->getPrice() * $quantidade)
                ->setBaseRowTotal($product->getPrice() * $quantidade)
                ->setDiscountAmount($discount);

            $order->addItem($orderItem);
            /* Add Order Item End */

            /* Update relevant order totals Start */
            $order->setSubtotal($order->getSubtotal() + $priceQty);
            $order->setBaseSubtotal($order->getBaseSubtotal() + $priceQty);
            $order->setGrandTotal(($order->getGrandTotal() + $priceQty) - $discount);
            $order->setBaseGrandTotal(($order->getBaseGrandTotal() + $priceQty) - $discount);
            $order->setTotalItemCount($order->getTotalItemCount() + $quantidade);
            $order->setTotalQtyOrdered($order->getTotalQtyOrdered() + $quantidade);
            $order->setDiscountAmount('-' . (abs($order->getDiscountAmount()) + $discount));
            $order->setBaseDiscountAmount('-' . (abs($order->getDiscountAmount()) + $discount));
            $order->addStatusHistoryComment($comment . 'id ' . $product->getId() . ' - ' . $product->getName(), false)
                ->setIsCustomerNotified(false);

            $this->orderRepository->save($order);
            /* Update relevant order totals End */
        } catch (Exception $e) {
            return $this->messageManager->addError($e->getMessage());
        }
        return true;
    }
}

