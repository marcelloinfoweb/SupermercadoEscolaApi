<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Model;

use Funarbe\SupermercadoEscolaApi\Api\DetalhesProdutosManagementInterface;
use Magento\Framework\App\ObjectManager;

class DetalhesProdutosManagement implements DetalhesProdutosManagementInterface
{
    /**
     * @param $productId;
     */
    public function getDetalhesProdutos($productId)
    {
        $objectManager = ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $sql = "SELECT
                    V.order_id,
                    SO.increment_id AS id_order_admin,
                    CONCAT(SO.customer_firstname, ' ', SO.customer_lastname) AS nome_cliente_completo,
                    P.entity_id AS id_produto,
                    P.value AS nome_produto,
                    C.value AS nome_categoria,
                    V.qty_ordered AS qty_ordered,
                    JSON_EXTRACT(V.product_options, '$.options[0].value') AS observacao
                FROM sales_order_item V
                INNER JOIN catalog_category_product PC ON
                    V.product_id = PC.product_id
                INNER JOIN catalog_product_entity_varchar P ON
                    P.entity_id = PC.product_id AND P.attribute_id = 73 AND P.store_id = 0
                INNER JOIN catalog_category_entity_varchar C ON
                    C.entity_id = PC.category_id
                INNER JOIN sales_order SO
                WHERE PC.position = 0 AND C.attribute_id = (
                SELECT
                    attribute_id
                FROM eav_attribute
                WHERE attribute_code = 'name' AND entity_type_id = 3) AND V.product_id = $productId
                GROUP BY P.entity_id, id_order_admin, nome_cliente_completo, id_produto, nome_produto, nome_categoria, qty_ordered, observacao";

        return $connection->fetchAll($sql);
    }
}
