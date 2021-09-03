<?php

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Api;

interface AdicionarItemCompraManagementInterface
{
    /**
     * @param int $order_id
     * @param float $quantidade
     * @param float $price
     * @param int $sku
     * @param float $weight
     * @param int $itemId
     * @param string $substituir
     * @return mixed
     */
    public function getAdicionarItemCompra(
        int $order_id,
        float $quantidade,
        float $price,
        int $sku,
        float $weight,
        int $itemId,
        string $substituir
    );
}
