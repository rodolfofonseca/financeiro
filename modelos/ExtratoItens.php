<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/ItensExtratos.php';

class ExtratoItens implements InterfaceModelo
{
    private int $codigo_extrato_item;
    private int $extrato;
    private int $item_extrato;
    private float $valor_lancamento_extrato;
    private string $data_lancamento_extrato;

    public function tabela()
    {
        return (string) 'extrato_item';
    }

    public function modelo()
    {
        return (array) [];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_extrato_item', $dados) == true) {
            if ($dados['codigo_extrato_item'] != 0) {
                $this->codigo_extrato_item = intval($dados['codigo_extrato_item'], 10);
            } else {
                $this->codigo_extrato_item = 0;
            }
        } else {
            $this->codigo_extrato_item = 0;
        }

        $this->extrato = (int) (isset($dados['extrato']) ? (int) intval($dados['extrato'], 10) : 0);
        $this->item_extrato = (int) (isset($dados['item_extrato']) ? (int) intval($dados['item_extrato'], 10) : 0);
        $this->valor_lancamento_extrato = (double) (isset($dados['valor_lancamento_extrato']) ? (double) doubleval(str_replace(',', '.', $dados['valor_lancamento_extrato'])) : 0);
        $this->data_lancamento_extrato = (isset($dados['data_lancamento_extrato']) ? model_date($dados['data_lancamento_extrato']) : model_date());
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        $objeto_item_extrato = new ItensExtratos();

        $retorno_operacao = (bool) false;
        $retorno_item_extrato = (array) $objeto_item_extrato->pesquisar((array) ['filtro' => (array) ['where' => [['codigo_item_extrato', '=', $this->item_extrato]]]]);
        $retorno_extrato_item = (array) [];

        if ($this->codigo_extrato_item != 0) {
            $retorno_operacao = (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_extrato_item', '=', $this->codigo_extrato_item]]], (array) $this->montar_array());
        } else {
            $retorno_operacao = (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
        }

        if ($retorno_operacao == true) {
            if ($this->codigo_extrato_item == null) {
                $retorno_extrato_item = (array) ['valor_lancamento_extrato' => (double) $this->valor_lancamento_extrato];
            }
        } else {
            $retorno_extrato_item = (array) $this->pesquisar((array) ['filtro' => (array) ['where' => [['codigo_extrato_item', '=', $this->codigo_extrato_item]]]]);
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

                $retorno_item = (array) $objeto_item_extrato->pesquisar((array) ['filtro' => (array) ['where' => [['codigo_item_extrato', '=', $codigo_item_extrato]]]]);

                if (empty($retorno_item) == false) {
                    $extrato_item['nome_item_extrato'] = (string) $retorno_item['nome_item_extrato'];
                    $extrato_item['tipo_item_extrato'] = (string) $retorno_item['tipo_item_extrato'];
                }

                array_push($retorno, $extrato_item);
            }
        }

        return (array) $retorno;
    }

    /**
     * Função responsável por excluir o extrato item
     * @param mixed $filtro
     * @return bool
     */
    public function deletar($filtro)
    {
        return (bool) model_delete((string) $this->tabela(), $filtro['filtro']);
    }

    public function montar_array(){
        $dados = (array) [];

        if($this->extrato != 0){
            $dados['codigo_extrato'] = (int) $this->extrato;
        }

        if($this->item_extrato != 0){
            $dados['codigo_item_extrato'] = (int) $this->item_extrato;
        }

        if($this->valor_lancamento_extrato != 0){
            $dados['valor_lancamento_extrato'] = (float) $this->valor_lancamento_extrato;
        }

        if($this->data_lancamento_extrato != ''){
            $dados['data_lancamento_extrato'] = (string) $this->data_lancamento_extrato;
        }

        return (array) $dados;
    }
}
?>