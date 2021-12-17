<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Model;

use Funarbe\SupermercadoEscolaApi\Api\DetalhesProdutosImagemManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

class DetalhesProdutosImagemManagement implements DetalhesProdutosImagemManagementInterface
{
    /**
     * {}
     * @param $productId ;
     */
    public function getDetalhesProdutosImagem($productId)
    {
        $connection = ObjectManager::getInstance()->get(ResourceConnection::class)->getConnection();

        $sql = "SELECT CONCAT('https://sitenovo.supermercadoescola.org.br/media/catalog/product', value) AS image_url
                FROM catalog_product_entity_varchar
                WHERE entity_id = $productId and attribute_id = 88";

        return $connection->fetchAll($sql);
    }
}
