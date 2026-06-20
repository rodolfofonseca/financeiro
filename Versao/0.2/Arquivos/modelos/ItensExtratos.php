<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class ItensExtratos implements InterfaceModelo
{
    private $codigo_item_extrato;
    private $empresa;
    private $nome_item_extrato;
    private $tipo_item_extrato;
    public function tabela()
    {
        return (string) 'item_extrato';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'nome_item_extrato' => (string) '', 'tipo_item_extrato' => (string) ''];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_item_extrato', $dados) == true) {
            if ($dados['codigo_item_extrato'] != '') {
                $this->codigo_item_extrato = model_id($dados['codigo_item_extrato']);
            } else {
                $this->codigo_item_extrato = null;
            }
        } else {
            $this->codigo_item_extrato = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->nome_item_extrato = (isset($dados['nome_item_extrato']) ? (string) strtoupper($dados['nome_item_extrato']) : '');
        $this->tipo_item_extrato = (isset($dados['tipo_item_extrato']) ? (string) strtoupper($dados['tipo_item_extrato']) : 'DEBITO');
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_item_extrato == null) {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'nome_item_extrato' => (string) $this->nome_item_extrato, 'tipo_item_extrato' => (string) $this->tipo_item_extrato]);
        } else {
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_item_extrato], (array) ['nome_item_extrato' => (string) $this->nome_item_extrato, 'tipo_item_extrato' => (string) $this->tipo_item_extrato]);
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
?>