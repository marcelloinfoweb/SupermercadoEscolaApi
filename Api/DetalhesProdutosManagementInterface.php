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
}
