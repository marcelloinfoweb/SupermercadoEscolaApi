<?php

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Api;

interface ExcluirItemCompraManagementInterface
{
    /**
     * @param int $orderId
     * @param int $itemId
     * @param float $qty_ordered
     * @return mixed
     */
    public function getExcluirItemCompra(int $orderId, int $itemId, float $qty_ordered);
}
