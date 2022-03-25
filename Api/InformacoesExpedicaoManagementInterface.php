<?php declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Api;

interface InformacoesExpedicaoManagementInterface
{
    /**
     * @param string $caixa
     * @param string $congelado
     * @param string $bolsa
     * @param string $observacao
     * @param int $order_id
     * @return mixed
     */
    public function getInformacoesExpedicao(
        string $caixa,
        string $congelado,
        string $bolsa,
        string $observacao,
        int $order_id
    );
}
