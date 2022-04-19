<?php

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Api;

interface AdicionarItemCompraManagementInterface
{
    /**
     * @param int $order_id
     * @param float $quantidade
     * @param int $sku
     * @param int $itemId
     * @return mixed
     */
    public function getAdicionarItemCompra(
        int $order_id,
        float $quantidade,
        int $sku,
        int $itemId
    );
}
