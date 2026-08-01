<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class ItensPedidos implements InterfaceModelo
{
    private $codigo_itens_pedido;
    private $empresa;
    private $pedido;
    private $produto;
    private $quantidade;
    private $valor_unitario;
    private $valor_bruto;
    private $valor_desconto;
    private $valor_frete;
    private $valor_liquido;
    private $tipo_item_pedido;
    private $data_cadastro;

    public function tabela()
    {
        return (string) 'itens_pedido';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'pedido' => 'objectId', 'produto' => 'objectId', 'quantidade' => (double) 0, 'valor_unitario' => (double) 0, 'valor_bruto' => (double) 0, 'valor_desconto' => (double) 0, 'valor_frete' => (double) 0, 'valor_liquido' => (double) 0, 'tipo_item_pedido' => (string) '', 'data_cadastro' => 'date'];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_itens_pedido', $dados) == true) {
            if ($dados['codigo_itens_pedido'] != '') {
                $this->codigo_itens_pedido = model_id($dados['codigo_itens_pedido']);
            } else {
                $this->codigo_itens_pedido = null;
            }
        } else {
            $this->codigo_itens_pedido = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->pedido = (isset($dados['pedido']) ? model_id($dados['pedido']) : '');
        $this->produto = (isset($dados['produto']) ? model_id($dados['produto']) : '');

        $this->quantidade = (double) (isset($dados['quantidade']) ? (double) doubleval(str_replace(',', '.', $dados['quantidade'])) : 0);
        $this->valor_unitario = (double) (isset($dados['valor_unitario']) ? (double) doubleval(str_replace(',', '.', $dados['valor_unitario'])) : 0);
        $this->valor_bruto = (double) (isset($dados['valor_bruto']) ? (double) doubleval(str_replace(',', '.', $dados['valor_bruto'])) : 0);
        $this->valor_desconto = (double) (isset($dados['valor_desconto']) ? (double) doubleval(str_replace(',', '.', $dados['valor_desconto'])) : 0);
        $this->valor_frete = (double) (isset($dados['valor_frete']) ? (double) doubleval(str_replace(',', '.', $dados['valor_frete'])) : 0);
        $this->valor_liquido = (double) (isset($dados['valor_liquido']) ? (double) doubleval(str_replace(',', '.', $dados['valor_liquido'])) : 0);

        $this->tipo_item_pedido = (bool) (isset($dados['tipo_item_pedido']) ? (bool) $dados['tipo_item_pedido'] : true);
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_itens_pedido != null) {
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_itens_pedido], (array) ['empresa' => $this->empresa, 'pedido' => $this->pedido, 'produto' => $this->produto, 'quantidade' => (double) $this->quantidade, 'valor_unitario' => (double) $this->valor_unitario, 'valor_bruto' => (double) $this->valor_bruto, 'valor_desconto' => (double) $this->valor_desconto, 'valor_frete' => (double) $this->valor_frete, 'valor_liquido' => (double) $this->valor_liquido, 'tipo_item_pedido' => (bool) $this->tipo_item_pedido, 'data_cadastro' => $this->data_cadastro]);
        } else {
            $retorno_insert = (string) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'pedido' => $this->pedido, 'produto' => $this->produto, 'quantidade' => (double) $this->quantidade, 'valor_unitario' => (double) $this->valor_unitario, 'valor_bruto' => (double) $this->valor_bruto, 'valor_desconto' => (double) $this->valor_desconto, 'valor_frete' => (double) $this->valor_frete, 'valor_liquido' => (double) $this->valor_liquido, 'tipo_item_pedido' => (bool) $this->tipo_item_pedido, 'data_cadastro' => $this->data_cadastro], false);

            if ($retorno_insert != '') {
                $this->codigo_itens_pedido = model_id($retorno_insert);
                return (bool) true;
            } else {
                return (bool) false;
            }
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

    public function deletar_itens_pedido($filtro)
    {
        return (bool) model_delete((string) $this->tabela(), (array) $filtro['filtro']);
    }

    /** 
     * Função responsável por retornar o código do item do pedido
     * @param mixed $this->codigo_itens_pedido = retorna o código em objeto do mongo.
     */
    public function get_codigo_item_pedido()
    {
        return $this->codigo_itens_pedido;
    }

    public function montar_array(){
        return (array) [];
    }
}
?>