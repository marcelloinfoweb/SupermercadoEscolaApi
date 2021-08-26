<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Api;

interface BuscarCategoriasManagementInterface
{
    /**
     * @param $orderId
     * @param $filter
     */
    public function getBuscarCategorias($orderId, $filter);
}
