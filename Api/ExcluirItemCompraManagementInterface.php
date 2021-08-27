<?php

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Api;

interface ExcluirItemCompraManagementInterface
{
    /**
     * @return mixed
     */
    public function getExcluirItemCompra(int $orderId, int $itemId);
}
