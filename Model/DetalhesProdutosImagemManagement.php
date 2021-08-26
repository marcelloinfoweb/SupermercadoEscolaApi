<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Model;

use Funarbe\SupermercadoEscolaApi\Api\DetalhesProdutosImagemManagementInterface;
use Magento\Framework\App\ObjectManager;

class DetalhesProdutosImagemManagement implements DetalhesProdutosImagemManagementInterface
{
    /**
     * {}
     * @param $productId ;
     */
    public function getDetalhesProdutosImagem($productId)
    {
        $objectManager = ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $sql = "SELECT CONCAT('https://sitenovo.supermercadoescola.org.br/media/catalog/product', value) AS image_url
                FROM catalog_product_entity_varchar
                WHERE entity_id = $productId and attribute_id = 88";

        return $connection->fetchAll($sql);
    }
}
