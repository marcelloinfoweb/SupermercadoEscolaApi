<?php declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Model;

use Funarbe\SupermercadoEscolaApi\Api\InformacoesExpedicaoManagementInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * @property \Magento\Framework\App\ResourceConnection $resourceConnection
 */
class InformacoesExpedicaoManagement implements InformacoesExpedicaoManagementInterface
{
    public const SALES_ORDER_TABLE = 'sales_order';

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param $caixa
     * @param $congelado
     * @param $bolsa
     * @param $observacao
     * @param $order_id
     * @return bool|string
     */
    public function getInformacoesExpedicao($caixa, $congelado, $bolsa, $observacao, $order_id)
    {
        $connection = $this->resourceConnection->getConnection();
        $sales_order = $connection->getTableName(self::SALES_ORDER_TABLE);

        try {

            $data = '{"caixa":"' . $caixa . '", "congelado":"' . $congelado;
            $data .= '", "bolsa":"' . $bolsa . '", "observacao":"' . $observacao . '"}';

            $info_embalar = ['info_embalar' => $data];
            $where = ['entity_id = ?' => (int)$order_id];

            $connection->update($sales_order, $info_embalar, $where);

            return true;

        } catch (\Exception $e) {

            return $e->getMessage();

        }
    }
}
