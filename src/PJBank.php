<?php

//namespace Reddes\pjbank;

class PJBank
{

    protected $data;
    public $url;
    /* DADOS DO BOLETO */
    protected $vencimento; //Vencimento da cobrança no formato MM/DD/AAAA
    protected $valor; //Valor a ser cobrado em reais.
    protected $juros; //Taxa de juros ao mês. Valor informado será dividido por 30 pra ser encontrado a taxa diária.
    protected $multa; //Taxa de multa por atraso.
    protected $desconto; //Valor do desconto por pontualidade, em Reais
    protected $nome_cliente; //Nome completo do pagador.
    protected $cpf_cliente;//CPF ou CNPJ do pagador.
    protected $endereco_cliente; //Endereço do pagador.
    protected $numero_cliente; //Número do endereço do pagador.
    protected $complemento_cliente; //Opcionalmente adicione o complemento do endereço do pagador.
    protected $bairro_cliente; //Bairro do endereço do pagador.
    protected $cidade_cliente; //Cidade do endereço do pagador.
    protected $estado_cliente; //Estado do endereço do pagador, com 2 caracteres.
    protected $cep_cliente; //CEP do endereço do pagador. Apenas números.
    protected $logo_url; //URL do logo da empresa. Será cacheado de forma agressiva, portanto, para mudar o logo altere a url. Essa imagem deve ser PNG, GIF ou JPG.
    protected $texto; //exto que ficará no topo dos boletos.
    protected $grupo; //Identificação do grupo. É uma string que identifica um grupo de boletos.
    protected $pedido_numero; //Numero do pedido da cobrança. Este número é importante se você precisar editar o boleto sem necessidade de duplica-lo. O sistema não vai gerar outro boleto se o número do pedido existir.
    protected $webhook = null; //Opcionalmente informe uma URL de Webhook. Iremos chamá-la com as novas informações sempre que a cobrança for atualizada.
    protected $especie_documento;


    /* EFETUAR O CREDENCIMENTO DA EMPRESA QUE IRÁ GERAR OS BOLETOS.
       OBS: VERIFICAR O AMBIENTE DE DESENVOLVIMENTO (HOMOLOGAÇÃO OU PRODUCAO)
    */
    public function credenciamentoBoleto()
    {
        if ($this->data) {
            $data = $this->conexaoPJBank($this->url, $this->getDadosEmpresa(), ['Content-Type: application/json'],"POST");
            return json_encode($data);
        }
        return 'Dados para credenciamento não informados ou inválidos.';
    }

    /* DEFINE OS DADOS DA EMPRESA PARA EFETUAR O CREDENCIAMENTO*/
    public function setDadosEmpresa($_nome_empresa, $_conta_repasse, $_agencia_repasse, $_banco_repasse, $_cnpj, $_ddd, $_telefone, $_email)
    {
        $this->data = [
            "nome_empresa" => trim($_nome_empresa),
            "conta_repasse" => trim($_conta_repasse),
            "agencia_repasse" => trim($_agencia_repasse),
            "banco_repasse" => trim($_banco_repasse),
            "cnpj" => trim($_cnpj),
            "ddd" => trim($_ddd),
            "telefone" => trim($_telefone),
            "email" => trim($_email)
        ];
    }

    /* RETORNA OS DADOS DA EMPRESA */
    public function getDadosEmpresa()
    {
        return json_encode($this->data);
    }

    /* DEFINE OS DADOS DO BOLETO PARA GERAR */
    public function setDadosBoleto($_vencimento, $_valor_, $_juros, $_multa, $_desconto, $_nome_cliente, $_cpf_cliente, $_endereco_cliente, $_numero_cliente, $_complemento_cliente, $_bairro_cliente, $_cidade_cliente, $_estado_cliente, $_cep_cliente, $_logo_url, $_texto, $_grupo, $_pedido_numero, $_webhook = '', $_especie_documento = '')
    {
        $this->vencimento = $_vencimento;
        $this->valor = $_valor_;
        $this->juros = $_juros;
        $this->multa = $_multa;
        $this->desconto = $_desconto;
        $this->nome_cliente = $_nome_cliente;
        $this->cpf_cliente = $_cpf_cliente;
        $this->endereco_cliente = $_endereco_cliente;
        $this->numero_cliente = $_numero_cliente;
        $this->complemento_cliente = $_complemento_cliente;
        $this->bairro_cliente = $_bairro_cliente;
        $this->cidade_cliente = $_cidade_cliente;
        $this->estado_cliente = $_estado_cliente;
        $this->cep_cliente = $_cep_cliente;
        $this->logo_url = $_logo_url;
        $this->texto = $_texto;
        $this->grupo = $_grupo;
        $this->pedido_numero = $_pedido_numero;
        $this->webhook = $_webhook;
        $this->especie_documento = $_especie_documento;
    }

    public function getDadosBoleto()
    {
        $data = [
            "vencimento" => $this->vencimento,
            "valor" => $this->valor,
            "juros" => $this->juros,
            "multa" => $this->multa,
            "desconto" => $this->desconto,
            "nome_cliente" => $this->nome_cliente,
            "cpf_cliente" => $this->cpf_cliente,
            "endereco_cliente" => $this->endereco_cliente,
            "numero_cliente" => $this->numero_cliente,
            "complemento_cliente" => $this->complemento_cliente,
            "bairro_cliente" => $this->bairro_cliente,
            "cidade_cliente" => $this->cidade_cliente,
            "estado_cliente" => $this->estado_cliente,
            "cep_cliente" => $this->cep_cliente,
            "logo_url" => $this->logo_url,
            "texto" => $this->texto,
            "grupo" => $this->grupo,
            "pedido_numero" => $this->pedido_numero,
            "webhook" => $this->webhook,
            "especie_documento" => $this->especie_documento
        ];
        return $data;
    }

    /* REGISTRA O BOLETO NO PJBANK*/
    public function geraBoleto($_credencial_boleto,$_chave)
    {
        $url = $this->url . '/' . $_credencial_boleto . '/transacoes';
        $data = $this->getDadosBoleto();


        if (count($data) > 0) {
            $data = http_build_query($this->getDadosBoleto());
            $retorno = $this->conexaoPJBank($url, $data, ["Content-Type: application/x-www-form-urlencoded","x-chave: 9bc874519193afb692e8facb132a627bf29253c1"],"POST");
            return $retorno;
        }
        return 'Dados do boleto invalidos ou não preenchidos.';
    }

    /* EFETUA A CONEXAO COM O SISTEMA DO PJBANK*/
    private function conexaoPJBank($_url, $_data, $_headers = array(),$metodo = "POST")
    {
        $headers = $_headers;

        if ($cr = curl_init($_url)) {

            curl_setopt($cr, CURLOPT_CUSTOMREQUEST, $metodo);
            curl_setopt($cr, CURLOPT_POSTFIELDS, $_data);
            curl_setopt($cr, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cr, CURLOPT_HTTPHEADER, $headers);

            $retorno = curl_exec($cr);
            $retorno = json_decode($retorno);

            curl_close($cr);

            return $retorno;
        }
    }

    /* DEFINE O AMBIENTE DE DESENVOLVIMENTO*/
    public function setAmbiente($_dev = true)
    {
        if ($_dev) {
            $this->url = "https://sandbox.pjbank.com.br/recebimentos";
        } else {
            $this->url = "https://api.pjbank.com.br/recebimentos";
        }
    }
}