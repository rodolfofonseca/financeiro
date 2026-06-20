<?php
require_once 'classes/bancoDeDados.php';

class Empresa implements InterfaceModelo
{
    private $codigo_empresa;
    private $nome_empresa;
    private $nome_fantasia;
    private $cnpj;
    private $endereco;
    private $data_cadastro;
    public function tabela()
    {
        return (string) 'empresa';
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_empresa', $dados) == true) {
            if ($dados['codigo_empresa'] != '') {
                $this->codigo_empresa = model_id($dados['codigo_empresa']);
            } else {
                $this->codigo_empresa = null;
            }
        } else {
            $this->codigo_empresa = null;
        }

        $this->nome_empresa = (string) (isset($dados['nome_empresa']) ? (string) strtoupper($dados['nome_empresa']) : '');
        $this->nome_fantasia = (string) (isset($dados['nome_fantasia']) ? (string) strtoupper($dados['nome_fantasia']) : '');
        $this->cnpj = (string) (isset($dados['cnpj']) ? (string) $dados['cnpj'] : '');
        $this->endereco = (string) (isset($dados['endereco']) ? (string) $dados['endereco'] : '');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);


        if ($this->codigo_empresa == null) {
            $retorno_checagem = (bool) model_check((string) $this->tabela(), (array) ['cnpj', '===', (string) $this->cnpj]);

            if ($retorno_checagem == false) {
                return (bool) model_insert((string) $this->tabela(), (array) ['nome_empresa' => (string) $this->nome_empresa, 'nome_fantasia' => (string) $this->nome_fantasia, 'cnpj' => (string) $this->cnpj, 'endereco' => (string) $this->endereco, 'data_cadastro' => $this->data_cadastro]);
            } else {
                return (bool) false;
            }
        } else {
            return (array) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_empresa], (array) ['nome_empresa' => (string) $this->nome_empresa, 'nome_fantasia' => (string) $this->nome_fantasia, 'cnpj' => (string) $this->cnpj, 'endereco' => (string) $this->endereco, 'data_cadastro' => $this->data_cadastro]);
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

    public function checar_empresa($filtro)
    {
        return (bool) model_check((string) $this->tabela(), (array) $filtro['filtro']);
    }
}
