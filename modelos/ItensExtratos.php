<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class ItensExtratos implements InterfaceModelo
{
    private int $codigo_item_extrato;
    private int $empresa;
    private string $nome_item_extrato;
    private bool $tipo_item_extrato;
    public function tabela()
    {
        return (string) 'item_extrato';
    }

    public function modelo()
    {
        return (array) [];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_item_extrato', $dados) == true) {
            if ($dados['codigo_item_extrato'] != 0) {
                $this->codigo_item_extrato = (int) intval($dados['codigo_item_extrato'], 10);
            } else {
                $this->codigo_item_extrato = 0;
            }
        } else {
            $this->codigo_item_extrato = 0;
        }

        $this->empresa = (int) (isset($dados['empresa']) ? (int) intval($dados['empresa'], 10) : 0);
        $this->nome_item_extrato = (string) (isset($dados['nome_item_extrato']) ? (string) strtoupper($dados['nome_item_extrato']) : '');
        $this->tipo_item_extrato = (bool) (isset($dados['tipo_item_extrato']) ? (bool) filter_var($dados['tipo_item_extrato'], FILTER_VALIDATE_BOOLEAN) : false);
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_item_extrato == 0) {
            return (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
        } else {
            return (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_item_extrato', '=', $this->codigo_item_extrato]]], (array) $this->montar_array());
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

    public function montar_array(){
        $dados = (array) [];

        if($this->empresa != 0){
            $dados['codigo_empresa'] = (int) $this->empresa;
        }

        if($this->nome_item_extrato != ''){
            $dados['nome_item_extrato'] = (string) strtoupper($this->nome_item_extrato);
        }

        $dados['tipo_item_extrato'] = (bool) $this->tipo_item_extrato;

        return (array) $dados;
    }
}
?>