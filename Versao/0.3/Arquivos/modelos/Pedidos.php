<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/ItensPedidos.php';
require_once 'modelos/CustoMedio.php';

class Pedidos implements InterfaceModelo
{
    private $codigo_pedido;
    private $empresa;
    private $fornecedor;
    private $status_pedido;
    private $tipo_pedido;
    private $data_cadastro;
    private $data_movimentacao;
    private $quantidade_total_itens;
    private $valor_unitario;
    private $valor_bruto;
    private $valor_desconto;
    private $valor_frete;
    private $valor_liquido;
    private $objeto_itens;

    /**
     * status_pedido
     * PEDIDO = Salva apenas o pedido, e os itens, mas não faz a movimentação de estoque
     * PEDIDO_ESTOQUE = Salva os pedidos, faz a movimentação de estoque, mas não gera as contas
     * PEDIDO_CONTA = Salva os pedidos, mas não gera a movimentação de estoque
     * PEDIDO_COMPLETO = Faz toda a operação, salva os pedidos, gera as contas, faz a movimentação de estoque e gera os custo médio
     */

    /**
     * tipo_pedido
     * true = pedido de entrada
     * false = pedido_saida
     */

    public function tabela()
    {
        return (string) 'pedidos';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'fornecedor' => 'objectId', 'quantidade_total_itens' => (float) 0, 'valor_unitario' => (float) 0, 'valor_unitario' => (float) 0, 'valor_bruto' => (float) 0, 'valor_desconto' => (float) 0, 'valor_frete' => (float) 0, 'valor_liquido' => (float) 0, 'data_cadastro' => 'date', 'data_movimentacao' => 'date', 'status' => (string) '', 'tipo_pedido' => 'bool'];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_pedido', $dados) == true) {
            if ($dados['codigo_pedido'] != '') {
                $this->codigo_pedido = model_id($dados['codigo_pedido']);
            } else {
                $this->codigo_pedido == null;
            }
        } else {
            $this->codigo_pedido == null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->fornecedor = (isset($dados['fornecedor']) ? model_id($dados['fornecedor']) : '');

        $this->quantidade_total_itens = (float) (isset($dados['quantidade_total_itens']) ? (float) doubleval(str_replace(',', '.', $dados['quantidade_total_itens'])) : 0);
        $this->valor_unitario = (float) (isset($dados['valor_unitario']) ? (float) doubleval(str_replace(',', '.', $dados['valor_unitario'])) : 0);
        $this->valor_bruto = (float) (isset($dados['valor_bruto']) ? (float) doubleval(str_replace(',', '.', $dados['valor_bruto'])) : 0);
        $this->valor_desconto = (float) (isset($dados['valor_desconto']) ? (float) doubleval(str_replace(',', '.', $dados['valor_desconto'])) : 0);
        $this->valor_frete = (float) (isset($dados['valor_frete']) ? (float) doubleval(str_replace(',', '.', $dados['valor_frete'])) : 0);
        $this->valor_liquido = (float) (isset($dados['valor_liquido']) ? (float) doubleval(str_replace(',', '.', $dados['valor_liquido'])) : 0);

        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
        $this->data_movimentacao = (isset($dados['data_movimentacao']) ? model_date($dados['data_movimentacao']) : model_date());

        $this->status_pedido = (string) (isset($dados['status_pedido']) ? (string) $dados['status_pedido'] : '');
        $this->tipo_pedido = (bool) (isset($dados['tipo_pedido']) ? (bool) $dados['tipo_pedido'] : false);
        $this->objeto_itens = (array) (isset($dados['objeto_itens']) ? (array) json_decode($dados['objeto_itens'], true) : []);
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        $retorno = (bool) false;
        $retorno_itens_pedido = (bool) false;

        if ($this->codigo_pedido != null) {
            $retorno = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_pedido], (array) ['empresa' => $this->empresa, 'fornecedor' => $this->fornecedor, 'quantidade_total_itens' => (float) $this->quantidade_total_itens, 'valor_unitario' => (float) $this->valor_unitario, 'valor_bruto' => (float) $this->valor_bruto, 'valor_desconto' => (float) $this->valor_desconto, 'valor_frete' => (float) $this->valor_frete, 'valor_liquido' => (float) $this->valor_liquido, 'data_cadastro' => $this->data_cadastro, 'data_movimentacao' => $this->data_movimentacao, 'status' => (string) $this->status_pedido, 'tipo_pedido' => (bool) $this->tipo_pedido]);
        } else {
            $codigo_insert = (string) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'fornecedor' => $this->fornecedor, 'quantidade_total_itens' => (float) $this->quantidade_total_itens, 'valor_unitario' => (float) $this->valor_unitario, 'valor_bruto' => (float) $this->valor_bruto, 'valor_desconto' => (float) $this->valor_desconto, 'valor_frete' => (float) $this->valor_frete, 'valor_liquido' => (float) $this->valor_liquido, 'data_cadastro' => $this->data_cadastro, 'data_movimentacao' => $this->data_movimentacao, 'status' => (string) $this->status_pedido, 'tipo_pedido' => (bool) $this->tipo_pedido], false);

            if ($codigo_insert != '') {
                $retorno = (bool) true;
                $this->codigo_pedido = model_id($codigo_insert);
            }
        }

        if ($retorno == true) {
            $objeto_itens_pedido = new ItensPedidos();

            if (empty($this->objeto_itens) == false) {
                $filtro_delete = (array) ['filtro' => ['pedido', '===', $this->codigo_pedido]];

                $retorno_deleletar_itens_pedido = (bool) $objeto_itens_pedido->deletar_itens_pedido($filtro_delete);

                foreach ($this->objeto_itens as $itens) {
                    $itens['empresa'] = $this->empresa;
                    $itens['pedido'] = $this->codigo_pedido;
                    $itens['produto'] = $itens['id_produto'];
                    $itens['quantidade'] = $itens['quantidade'];
                    $itens['valor_unitario'] = $itens['valor_unitario_produto'];
                    $itens['valor_bruto'] = $itens['valor_bruto_produto'];
                    $itens['valor_desconto'] = $itens['valor_desconto_produto'];
                    $itens['valor_frete'] = $itens['valor_frete_produto'];
                    $itens['valor_liquido'] = $itens['valor_total_produto'];

                    if ($this->tipo_pedido == true) {
                        $itens['tipo_item_pedido'] = (bool) true;
                    } else {
                        $itens['tipo_item_pedido'] = (bool) false;
                    }

                    $retorno_itens_pedido = (bool) $objeto_itens_pedido->salvar_dados($itens);
                    
                    if ($this->tipo_pedido == true) {
                        $itens['item_pedido'] = $objeto_itens_pedido->get_codigo_item_pedido();
                        $itens['valor_custo_parametro'] = $itens['valor_total_produto'];
                        
                        $objeto_custo_medio = new CustoMedio();
                        $retorno_custo_medio = (bool) $objeto_custo_medio->salvar_dados($itens);
                    }
                }
            }
        }

        return (array) ['retorno_pedido' => (bool) $retorno, 'retorno_itens_pedido' => (bool) $retorno_itens_pedido];
    }

    public function pesquisar($filtro)
    {
        throw new \Exception('Not implemented');
    }

    public function pesquisar_todos($filtro)
    {
        return (array) [];
    }
}
