<?php

namespace Funarbe\SupermercadoEscolaApi\Model;

use Exception;
use Funarbe\Helper\Helper\Data;
use Magento\Framework\App\ResourceConnection;
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

    /**
     * @var \Funarbe\SupermercadoEscolaApi\Model\ExcluirItemCompraManagement
     */
    private ExcluirItemCompraManagement $excluirItemCompra;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private ResourceConnection $resource;

    public function __construct(
        \Funarbe\SupermercadoEscolaApi\Model\ExcluirItemCompraManagement $excluirItemCompra,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Funarbe\Helper\Helper\Data $data
    ) {
        $this->excluirItemCompra = $excluirItemCompra;
        $this->resource = $resource;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->cartItemFactory = $cartItemFactory;
        $this->quoteRepository = $quoteRepository;
        $this->orderItemFactory = $orderItemFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->helper = $data;
    }

    /**
     * @param int $order_id
     * @param float $quantidade
     * @param float $price
     * @param int $sku
     * @param int $itemId
     * @return \Magento\Framework\Message\ManagerInterface|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
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
        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        $query = "SELECT qty_ordered, price FROM sales_order_item WHERE order_id = $order_id AND product_id = $itemId";
        $results = $connection->fetchAll($query);

        $qty = 0;
        if (count($results) >= 1) {
            foreach ($results as $num => $values) {
                $qty += $values['qty_ordered'];
            }
            $this->excluirItemCompra->getExcluirItemCompra($order_id, $itemId, $quantidade);
        }

        $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getValue();
        $specialPrice = $product->getPriceInfo()->getPrice('special_price')->getValue();

        if ($specialPrice) {
            $preco = $specialPrice;
        } else {
            $preco = $regularPrice;
        }

        $quantidade += $qty;
        $priceQty = $preco * $quantidade;
        $comment = 'Produto adicionado: ';

        $discount = 0.00;
        if ($colaborador === '1') {
            $discount = abs(($priceQty * 5) / 100);
        }

        $requestInfo = ['qty' => $quantidade, 'options' => []];

        try {
            /* Add Quote Item Start */
            $quoteItem = $this->cartItemFactory->create();
            $quoteItem->setProduct($product)
                ->setQty($quantidade)
                ->setCustomPrice($preco)
                ->getProduct()->setIsSuperMode(true);

            $quote->addItem($quoteItem);
            $quote->collectTotals();
            $quote->save();
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
                ->setRowTotal($price * $quantidade)
                ->setBaseRowTotal($price * $quantidade)
                ->setProductOptions(['info_buyRequest' => $requestInfo]);

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
            $order->addStatusHistoryComment($comment . 'id ' . $product->getId() . ' - '
                . $product->getName(), false)->setIsCustomerNotified(false);
            $this->orderRepository->save($order);
            /* Update relevant order totals End */
        } catch (Exception $e) {
            return $this->messageManager->addError($e->getMessage());
        }
        return true;
    }
}

