<?php

//namespace Reddes\pjbank;

class PJBank
{

    protected $data;
    public $url;


    public function credenciamentoBoleto()
    {
        if ($this->data) {
            $data = $this->conexaoPJBank($this->url, $this->getDadosEmpresa(), ['Content-Type: application/json']);
            return json_encode($data);
        }

        return 'Dados para credenciamento não informados ou inválidos.';

    }

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

    public function getDadosEmpresa()
    {
        return json_encode($this->data);
    }

    private function conexaoPJBank($_url, $_data, $_headers = array())
    {
        $headers = $_headers;

        if ($cr = curl_init($_url)) {

            curl_setopt($cr, CURLOPT_POST, true);
            curl_setopt($cr, CURLOPT_POSTFIELDS, $_data);
            curl_setopt($cr, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cr, CURLOPT_HTTPHEADER, $headers);

            $retorno = curl_exec($cr);
            $retorno = json_decode($retorno);

            //$status = curl_getinfo($cr, CURLINFO_HEADER_OUT);

            curl_close($cr);

            return $retorno;

        }
    }

    public function setAmbiente($_dev = true)
    {
        if ($_dev) {
            $this->url = "https://sandbox.pjbank.com.br/recebimentos";
        } else {
            $this->url = "https://api.pjbank.com.br/recebimentos";
        }

    }
}