<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class Contas implements InterfaceModelo
{
    private $codigo_conta;
    private $empresa;
    private $nome_conta;
    private $descricao;
    private $saldo_conta;
    private $status;
    private $data_cadastro;

    public function tabela()
    {
        return (string) 'contas';
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_conta', $dados) == true) {
            if ($dados['codigo_conta'] != '') {
                $this->codigo_conta = model_id($dados['codigo_conta']);
            } else {
                $this->codigo_conta = null;
            }
        } else {
            $this->codigo_conta = null;
        }

        $this->saldo_conta = (float) (isset($dados['saldo_conta']) ? (float) doubleval(str_replace(',', '.', $dados['saldo_conta'])) : 0);
        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? (string) strtoupper($dados['nome_conta']) : '');
        $this->descricao = (string) (isset($dados['descricao']) ? (string) $dados['descricao'] : '');
        $this->status = (string) (isset($dados['status']) ? (string) $dados['status'] : 'ATIVO');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']): model_date());
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_conta == null) {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'nome_conta' => (string) $this->nome_conta, 'descricao' => (string) $this->descricao, 'saldo_conta' => (float) $this->saldo_conta, 'status' => (string) $this->status, 'data_cadastro' => $this->data_cadastro]);
        } else {
            return (bool) model_update((string) $this->tabela(), ['_id', '===', $this->codigo_conta], (array) ['nome_conta' => (string) $this->nome_conta, 'descricao' => (string) $this->descricao, 'saldo_conta' => (float) $this->saldo_conta, 'status' => (string) $this->status, 'data_cadastro' => $this->data_cadastro]);
        }
    }

    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    public function pesquisar_todos($filtro)
    {
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
    }
}
