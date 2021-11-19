<?php

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Model;

use Funarbe\SupermercadoEscolaApi\Api\VerificaProdutoSemImagemManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class VerificaProdutoSemImagemManagement implements VerificaProdutoSemImagemManagementInterface
{
    protected AdapterInterface $connection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->connection = $resource->getConnection();
    }

    public function getProdutoSemImagem($productId)
    {
        return $this->connection
            ->fetchAll("SELECT * FROM `catalog_product_entity` AS a
                LEFT JOIN `catalog_product_entity_media_gallery_value` AS b ON a.entity_id = b.entity_id
                LEFT JOIN `catalog_product_entity_media_gallery` AS c ON b.value_id = c.value_id
                WHERE c.value IS NULL AND a.entity_id=$productId");
    }
}
