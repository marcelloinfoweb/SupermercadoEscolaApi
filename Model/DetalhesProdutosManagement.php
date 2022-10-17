<?php

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Model;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Funarbe\SupermercadoEscolaApi\Api\DetalhesProdutosManagementInterface;
use Psr\Log\LoggerInterface;

/**
 * @property \Magento\Catalog\Model\Product $_productCollection
 * @property \Magento\Catalog\Model\ProductRepository $_productRepository
 * @property \Psr\Log\LoggerInterface $logger
 */
class DetalhesProdutosManagement implements DetalhesProdutosManagementInterface
{
    private CollectionFactory $_productCollectionFactory;

    /**
     * @param ProductRepository $productRepository
     * @param Product $productCollection
     * @param CollectionFactory $productCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductRepository $productRepository,
        Product           $productCollection,
        CollectionFactory $productCollectionFactory,
        LoggerInterface   $logger
    )
    {
        $this->_productRepository = $productRepository;
        $this->_productCollection = $productCollection;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed|null
     * @throws NoSuchEntityException
     */
    public function getDetalhesProdutos($productId)
    {
        $product = ObjectManager::getInstance()
            ->get('Magento\Catalog\Model\Product')->load($productId);

//        return $this->_productRepository->getById($productId);

//        $connection = $this->connection();
//
//        $sql = "SELECT
//                    V.order_id,
//                    SO.increment_id AS id_order_admin,
//                    CONCAT(SO.customer_firstname, ' ', SO.customer_lastname) AS nome_cliente_completo,
//                    P.entity_id AS id_produto,
//                    P.value AS nome_produto,
//                    C.value AS nome_categoria,
//                    V.qty_ordered AS qty_ordered,
//                    V.sku,
//                    P2.value AS ean,
//                    SO.caso_produto_nao_encontrado,
//                    JSON_EXTRACT(V.product_options, '$.options[0].value') AS observacao
//                FROM sales_order_item V
//                INNER JOIN catalog_category_product PC ON
//                    V.product_id = PC.product_id
//                INNER JOIN catalog_product_entity_varchar P ON
//                    P.entity_id = PC.product_id AND P.attribute_id = 73 AND P.store_id = 0
//                INNER JOIN catalog_product_entity_varchar P2 ON
//                    P2.entity_id = PC.product_id AND P2.attribute_id = 237 AND P2.store_id = 0
//                INNER JOIN catalog_category_entity_varchar C ON
//                    C.entity_id = PC.category_id
//                INNER JOIN sales_order SO
//                WHERE PC.position = 0 AND C.attribute_id = (
//                SELECT
//                    attribute_id
//                FROM eav_attribute
//                WHERE attribute_code = 'name' AND entity_type_id = 3) AND V.product_id = $productId
//                GROUP BY P.entity_id, id_order_admin, nome_cliente_completo, id_produto, nome_produto, nome_categoria, qty_ordered, observacao, V.sku, SO.caso_produto_nao_encontrado";
//
//        return $connection->fetchAll($sql);
    }

    public function getProdutos(): array
    {
        $connection = $this->connection();

        $sql = "SELECT DISTINCT cpev3.value AS nome_produto, cpev.value AS sku, cpev2.value AS ean
                FROM eav_attribute
                INNER JOIN catalog_product_entity_int cpei ON eav_attribute.attribute_id = cpei.attribute_id
                INNER JOIN catalog_product_entity_varchar cpev ON cpei.entity_id = cpev.entity_id AND cpev.attribute_id = 233
                INNER JOIN catalog_product_entity_varchar cpev2 ON cpev2.entity_id = cpev.entity_id AND cpev2.attribute_id = 237
                INNER JOIN catalog_product_entity_varchar cpev3 ON cpev3.entity_id = cpev.entity_id AND cpev3.attribute_id = 73
                INNER JOIN catalog_product_entity cpe ON cpei.entity_id = cpe.entity_id";

        return $connection->fetchAll($sql);
    }

    /**
     * @param int $sku
     * @param string $state
     */
    public function venderProdutoNoEcommerce(int $sku, string $state)
    {
        $store_ids = [28, 0];
        foreach ($store_ids as $store_id) {

            try {
                // Trás um produto especifico, do sku enviado.
                $product = $this->_productRepository->get($sku, true, $store_id, true);

                $product_id_alterar = $product->getId();
                $product_name_alterar = $product->getName();
                $product_status_alterar = $product->getStatus();

                // Se o produto estiver desabilitato, se for para habilitar e o produto tenha a palavra Inativo
                if ($product_status_alterar === '2' && $state === 'enable'
                    && preg_match("~\bINATIVO\b~", $product_name_alterar) === 1) {

                    // Trás todos os produtos com o mesmo nome no BD Magento
                    $productEquals = $this->searchProductEquals($product_name_alterar, $product_id_alterar);
                    if (!empty($productEquals)) {
                        echo 'Existe produto com o mesmo nome.';
                        echo PHP_EOL;
                        continue;
                    }
                    $this->updateUrlAndNameProduct($sku, $product_name_alterar, $state, $store_id);
                }

                // Se for para desabilitar e o produto não tenha a palavra Inativo
                if ($product_status_alterar === '1' && $state === 'disable' && preg_match("~\bINATIVO\b~", $product_name_alterar) === 0) {
                    $this->updateUrlAndNameProduct($sku, $product_name_alterar, $state, $store_id);
                }

                echo 'Produto Sem Alteração.';
                return;

            } catch (\Exception $e) {
                echo $e->getMessage();
                return;
            }
        }
    }

    /**
     *
     * Product collection Data
     * Se os skus forem iguais e o produto buscado não tiver INATIVO no nome, retorna true.
     *
     * @param $product_name_alterar
     * @param $sku
     * @param $store_id
     * @return bool
     */
    public function getProductCollection($product_name_alterar, $sku, $store_id): bool
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('name', ['like' => $product_name_alterar]);
        $collection->addStoreFilter($store_id);
        $datas = $collection->getData();

        if (!empty($datas)) {
            foreach ($datas as $value) {
                if ($value['sku'] === (string)$sku && !stripos($value['name'], "INATIVO")) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Atualiza o status, o nome e a url do produto
     */
    public function updateUrlAndNameProduct(int $sku, string $product_name, string $state, int $store_id): void
    {
        $today = date("dmY-His");
        $product_name = preg_replace('/ INATIVO.*/', '', $product_name);
        $url = preg_replace("#[^0-9a-z]+#i", "-", $product_name);

        try {
            $product = $this->_productRepository->get($sku, true, $store_id, true);

            if ($state === 'enable') {
                $product->setUrlKey(strtolower($url));
                $product->setName($product_name);
                $product->setStatus(Status::STATUS_ENABLED);
            } else {
                $product->setUrlKey(strtolower($url . "-inativo-" . $today));
                $product->setName($product_name . " INATIVO " . $today);
                $product->setStatus(Status::STATUS_DISABLED);
            }
            $product->save();

            return;
        } catch (Exception $e) {
            echo "updateUrlAndNameProduct: " . $e->getMessage() . " " . $url . PHP_EOL;
        }
    }

    /**
     * @param $product_name_alterar
     * @param $product_id_alterar
     * @return array
     */
    public function searchProductEquals($product_name_alterar, $product_id_alterar): array
    {
        $product_name = preg_replace('/ INATIVO.*/', '', $product_name_alterar);
        return $this->connection()
            ->fetchAll("SELECT value_id, entity_id FROM catalog_product_entity_varchar
                           WHERE value = '$product_name' AND entity_id <> $product_id_alterar AND attribute_id = 73");
    }

    public function connection(): AdapterInterface
    {
        $objectManager = ObjectManager::getInstance();
        return $objectManager->get(ResourceConnection::class)->getConnection();
    }

}
