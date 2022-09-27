<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Api;

interface DetalhesProdutosManagementInterface
{
    /**
     * @param int $productId
     * @return mixed
     */
    public function getDetalhesProdutos(int $productId);

    /**
     * @return mixed
     */
    public function getProdutos();

    /**
     * @param int $sku
     * @param string $state
     * @return mixed
     */
    public function venderProdutoNoEcommerce(int $sku, string $state);
}
