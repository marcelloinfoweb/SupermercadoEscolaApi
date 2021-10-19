<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Model;

use Funarbe\SupermercadoEscolaApi\Api\SeparacaoProdutosManagementInterface;
use Magento\Framework\App\ObjectManager;

class SeparacaoProdutosManagement implements SeparacaoProdutosManagementInterface
{

    /**
     * @param int $orderId
     * @return array|mixed|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSeparacaoProdutos(int $orderId)
    {
        $objectManager = ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        // $eav_attribute = $resource->getTableName('eav_attribute');
        // $sales_order_item = $resource->getTableName('sales_order_item');
        // $sales_order = $resource->getTableName('sales_order');
        // $catalog_category_product = $resource->getTableName('catalog_category_product');
        // $catalog_product_entity_varchar = $resource->getTableName('catalog_product_entity_varchar');
        // $catalog_category_entity_varchar = $resource->getTableName('catalog_category_entity_varchar');
        $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
        $orderItems = $order->getAllItems();

        foreach ($orderItems as $item) {
            $order_Id = $item->getOrderId();

//            $sql = "SELECT * FROM api_separacao_produtos WHERE order_id = $order_Id";

            $sql = "SELECT
                     V.order_id,
                     SO.increment_id AS id_order_admin,
                     CONCAT(SO.customer_firstname, ' ', SO.customer_lastname) AS nome_cliente_completo,
                     SOA.telephone,
                     P.entity_id AS id_produto,
                     P.value AS nome_produto,
                     P2.value AS ean,
                     C.value AS nome_categoria,
                     V.qty_ordered AS qty_ordered,
                     SO.status,
                     V.sku,
                     JSON_EXTRACT(V.product_options, '$.options[0].value') AS observacao,
                     SO.caso_produto_nao_encontrado
                    FROM sales_order_item V
                    INNER JOIN catalog_category_product PC ON V.product_id = PC.product_id
                    INNER JOIN catalog_category_entity_varchar C ON C.entity_id = PC.category_id
                    INNER JOIN catalog_product_entity_varchar P ON P.entity_id = PC.product_id AND P.attribute_id = 73 AND P.store_id = 0
                    INNER JOIN catalog_product_entity_varchar P2 ON P2.entity_id = PC.product_id AND P2.attribute_id = 237 AND P2.store_id = 0
                    INNER JOIN catalog_product_entity_int CPEI ON P.entity_id = CPEI.entity_id
                    INNER JOIN sales_order SO ON SO.entity_id = $order_Id
                    INNER JOIN sales_order_address SOA ON SOA.entity_id = SO.entity_id
                    WHERE PC.position = 0 AND C.attribute_id = (
                    SELECT attribute_id
                    FROM eav_attribute
                    WHERE attribute_code = 'name' AND entity_type_id = 3) AND V.order_id = $order_Id
                    GROUP BY P.entity_id, V.order_id, id_order_admin, nome_cliente_completo, id_produto, nome_produto, nome_categoria, qty_ordered, SO.status, observacao, SO.caso_produto_nao_encontrado, V.sku";

            return $connection->fetchAll($sql);
        }
    }
}
