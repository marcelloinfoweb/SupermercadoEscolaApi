<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Api;

interface IntegratorRmClienteFornecedorManagementInterface
{

    /**
     * @param string $cpf
     * @param string $dataAbertura
     * @param string $dataFechamento
     * @return mixed
     */
    public function getIntegratorRmClienteFornecedorLimiteDisponivel(
        string $cpf,
        string $dataAbertura,
        string $dataFechamento
    );

    /**
     * @param string $cpf
     * @return mixed
     */
    public function getIntegratorRmClienteFornecedor(string $cpf);
}
