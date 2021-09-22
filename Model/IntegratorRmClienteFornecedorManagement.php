<?php

declare(strict_types=1);

namespace Funarbe\SupermercadoEscolaApi\Model;

use Funarbe\SupermercadoEscolaApi\Api\IntegratorRmClienteFornecedorManagementInterface;
use Magento\Framework\HTTP\Client\Curl;

class IntegratorRmClienteFornecedorManagement implements IntegratorRmClienteFornecedorManagementInterface
{
    /**
     * @var Curl
     */
    protected Curl $curl;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }

    /**
     * @param string $cpf
     * @param string|null $dataAbertura
     * @param string|null $dataFechamento
     * @return mixed
     * @throws \Safe\Exceptions\JsonException
     */
    public function getIntegratorRmClienteFornecedorLimiteDisponivel(
        string $cpf,
        string $dataAbertura = null,
        string $dataFechamento = null
    ) {

        $URL = "https://integrator2.funarbe.org.br/rm/cliente-fornecedor/index";
        $URL .= "?expand=LIMITEDISPONIVELCHEQUINHO&filter[CGCCFO]=$cpf&";
        $URL .= "fields=NOME,CGCCFO,LIMITEDISPONIVELCHEQUINHO,LIMITECREDITO&";
        $URL .= "DTABERTURA=$dataAbertura&DTFECHAMENTO=$dataFechamento";

        return $this->curlIntegrator($URL);
    }

    /**
     * @throws \Safe\Exceptions\JsonException
     */
    public function getIntegratorRmClienteFornecedor($cpf)
    {
        $URL = "https://integrator2.funarbe.org.br/rm/cliente-fornecedor/";
        $URL .= "?expand=SALDOCARTAOALIMENTACAO&filter[CGCCFO]=$cpf";

        return $this->curlIntegrator($URL);
    }

    /**
     * @param string $URL
     * @return mixed
     * @throws \Safe\Exceptions\JsonException
     */
    public function curlIntegrator(string $URL)
    {
        $username = 'mestre';
        $password = 'cacg93d7';

        //set curl options
        $this->curl->setOption(CURLOPT_USERPWD, $username . ":" . $password);
        $this->curl->setOption(CURLOPT_HEADER, 0);
        $this->curl->setOption(CURLOPT_TIMEOUT, 60);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');

        //get request with url
        $this->curl->get($URL);

        //read response
        $response = $this->curl->getBody();
        $resp = \Safe\json_decode($response, true);
        return $resp['items'];
    }
}

