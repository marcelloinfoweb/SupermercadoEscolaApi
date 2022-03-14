<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Model;

use Funarbe\SupermercadoEscolaApi\Api\BuscarCategoriasManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\OrderRepository;

class BuscarCategoriasManagement implements BuscarCategoriasManagementInterface
{
    /**
     * {@inheritdoc}
     * @param $orderId ;
     * @param $filter ;
     * @return array|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBuscarCategorias($orderId, $filter)
    {
        $objectManager = ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $sales_order_item = $resource->getTableName('sales_order_item');
        $catalog_category_product = $resource->getTableName('catalog_category_product');
        $catalog_product_entity_varchar = $resource->getTableName('catalog_product_entity_varchar');
        $catalog_category_entity_varchar = $resource->getTableName('catalog_category_entity_varchar');
        $order = $objectManager->create(OrderRepository::class)->get($orderId);
        $orderItems = $order->getAllItems();

        foreach ($orderItems as $item) {
            $order_Id = $item->getOrderId();
            $sql = "SELECT
                      V.order_id,
                      P.value AS nome_produto,
                      C.value AS nome_categoria,
                      V.qty_ordered AS qty_ordered
                    FROM $sales_order_item V
                      INNER JOIN $catalog_category_product PC
                        ON V.product_id = PC.product_id
                      INNER JOIN $catalog_product_entity_varchar P
                        ON P.entity_id = PC.product_id
                        AND P.attribute_id = 73
                        AND P.store_id = 0
                      INNER JOIN $catalog_category_entity_varchar C
                        ON C.entity_id = PC.category_id
                    WHERE PC.position = 0
                    AND C.attribute_id = (SELECT
                        attribute_id
                      FROM eav_attribute
                      WHERE attribute_code = 'name'
                      AND entity_type_id = 3)
                    AND V.order_id IN ($order_Id)
                    AND C.entity_id = $filter
                    GROUP BY P.entity_id, V.order_id, nome_produto, nome_categoria, qty_ordered";
            return $connection->fetchAll($sql);
        }
    }
}
