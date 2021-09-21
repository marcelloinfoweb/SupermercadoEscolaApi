<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Model;

use Magento\Framework\App\ResourceConnection;

class AberturaPontoManagement implements \Funarbe\SupermercadoEscolaApi\Api\AberturaPontoManagementInterface
{

    protected $resourceConnection;

    private $connection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $this->connection = $resourceConnection->getConnectionByName('db_intranet2');
    }

    public function getAberturaPonto()
    {
        return $this->connection->fetchAll("SELECT * FROM `chequinho_abertura_ponto` ORDER BY `id` DESC LIMIT 1");
    }

}
