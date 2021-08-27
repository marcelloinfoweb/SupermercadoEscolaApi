<?php

namespace Funarbe\SupermercadoEscolaApi\Model;

use Exception;
use Funarbe\SupermercadoEscolaApi\Api\ExcluirItemCompraManagementInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Psr\Log\LoggerInterface;

class ExcluirItemCompraManagement implements ExcluirItemCompraManagementInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private OrderItemRepositoryInterface $orderItemRepository;

    private $logger;

    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        LoggerInterface $logger
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    public function getExcluirItemCompra($orderId, $itemId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)
            ->addFilter('product_id', $itemId)->create();
        $orderItems = $this->orderItemRepository->getList($searchCriteria);

        if ($orderItems->getTotalCount() > 0) {
            $orderItemsData = $orderItems->getData();
            foreach ($orderItemsData as $orderItem) {
                $itemId = $orderItem['item_id'];
                try {
                    $this->orderItemRepository->deleteById($itemId);
                    $this->logger->info("[ INFO ] - Item $itemId da compra $orderId foi deletado com sucesso.");
                } catch (Exception $exception) {
                    $this->logger->error(
                        "[ ERROR ] - Item $itemId da compra $orderId não foi deletado",
                        ['exception' => $exception]
                    );
                }
            }
        }
    }
}
