<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/ItensExtratos.php';

class ExtratoItens implements InterfaceModelo
{
    private $codigo_extrato_item;
    private $extrato;
    private $item_extrato;
    private $valor_lancamento_extrato;
    private $data_lancamento_extrato;

    public function tabela()
    {
        return (string) 'extrato_item';
    }

    public function modelo()
    {
        return (array) ['extrato' => 'objectId', 'item_extrato' => 'objectId', 'valor_lancamento_extrato' => (double) 0, 'data_lancamento_extrato' => (double) 0];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_extrato_item', $dados) == true) {
            if ($dados['codigo_extrato_item'] != '') {
                $this->codigo_extrato_item = model_id($dados['codigo_extrato_item']);
            } else {
                $this->codigo_extrato_item = null;
            }
        } else {
            $this->codigo_extrato_item = null;
        }

        $this->extrato = (isset($dados['extrato']) ? model_id($dados['extrato']) : '');
        $this->item_extrato = (isset($dados['item_extrato']) ? model_id($dados['item_extrato']) : '');
        $this->valor_lancamento_extrato = (double) (isset($dados['valor_lancamento_extrato']) ? (double) doubleval(str_replace(',', '.', $dados['valor_lancamento_extrato'])) : 0);
        $this->data_lancamento_extrato = (isset($dados['data_lancamento_extrato']) ? model_date($dados['data_lancamento_extrato']) : model_date());
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        $objeto_item_extrato = new ItensExtratos();

        $retorno_operacao = (bool) false;
        $retorno_item_extrato = (array) $objeto_item_extrato->pesquisar((array) ['filtro' => (array) ['_id', '===', $this->item_extrato]]);
        $retorno_extrato_item = (array) [];

        if ($this->codigo_extrato_item != null) {
            $retorno_operacao = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_extrato_item], (array) ['extrato' => $this->extrato, 'item_extrato' => $this->item_extrato, 'valor_lancamento_extrato' => (double) $this->valor_lancamento_extrato]);
        } else {
            $retorno_operacao = (bool) model_insert((string) $this->tabela(), (array) ['extrato' => $this->extrato, 'item_extrato' => $this->item_extrato, 'valor_lancamento_extrato' => (double) $this->valor_lancamento_extrato, 'data_lancamento_extrato' => $this->data_lancamento_extrato]);
        }

        if ($retorno_operacao == true) {
            if ($this->codigo_extrato_item == null) {
                $retorno_extrato_item = (array) ['valor_lancamento_extrato' => (double) $this->valor_lancamento_extrato];
            }
        } else {
            $retorno_extrato_item = (array) $this->pesquisar((array) ['filtro' => (array) ['_id', '===', $this->codigo_extrato_item]]);
        }

        return (array) ['retorno_operacao' => (bool) $retorno_operacao, 'extrato_item' => (array) $retorno_extrato_item, 'item_extrato' => (array) $retorno_item_extrato];
    }

    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    public function pesquisar_todos($filtro)
    {
        $retorno_extrato_item = (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);

        $objeto_item_extrato = new ItensExtratos();
        $retorno = (array) [];

        if (empty($retorno_extrato_item) == false) {
            foreach ($retorno_extrato_item as $extrato_item) {
                $codigo_item_extrato = (string) $extrato_item['item_extrato'];

                $retorno_item = (array) $objeto_item_extrato->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_item_extrato)]]);

                if (empty($retorno_item) == false) {
                    $extrato_item['nome_item_extrato'] = (string) $retorno_item['nome_item_extrato'];
                    $extrato_item['tipo_item_extrato'] = (string) $retorno_item['tipo_item_extrato'];
                }

                array_push($retorno, $extrato_item);
            }
        }

        return (array) $retorno;
    }

    public function deletar($filtro)
    {
        return (bool) model_delete((string) $this->tabela(), $filtro['filtro']);
    }
}
?>