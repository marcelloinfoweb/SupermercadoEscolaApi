<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Api;

interface VerificaProdutoSemImagemManagementInterface
{
    /**
     * @param string $sku
     * @return mixed
     */
    public function getProdutoSemImagem(string $sku);

}

