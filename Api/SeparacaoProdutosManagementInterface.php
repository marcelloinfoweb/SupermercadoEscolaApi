<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Api;

interface SeparacaoProdutosManagementInterface
{
    /**
     * @param int $orderId
     * @return mixed
     */
    public function getSeparacaoProdutos(int $orderId);
}
