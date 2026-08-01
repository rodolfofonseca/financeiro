<?php
require_once 'classes/bancoDeDados.php';
require_once 'classes/CodigoBarras/EAN13.php';

require_once 'modelos/Interface.php';
require_once 'modelos/ItensPedidos.php';
require_once 'modelos/CustoMedio.php';
require_once 'modelos/MovimentacaoEstoque.php';
require_once 'modelos/ContasPagarReceber.php';
require_once 'modelos/ContasFornecedores.php';
require_once 'modelos/Sistema.php';
require_once 'modelos/Produtos.php';

class Pedidos implements InterfaceModelo
{
    private mixed $codigo_pedido;
    private mixed $empresa;
    private mixed $fornecedor;
    private string $status_pedido;
    private bool $tipo_pedido;
    private mixed $data_cadastro;
    private mixed $data_movimentacao;
    private float $quantidade_total_itens;
    private float $valor_unitario;
    private float $valor_bruto;
    private float $valor_desconto;
    private float $valor_frete;
    private float $valor_liquido;
    private mixed $objeto_itens;
    private string $transacao;
    private string $observacao;

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
        return (array) ['empresa' => 'objectId', 'fornecedor' => 'objectId', 'quantidade_total_itens' => (float) 0, 'valor_unitario' => (float) 0, 'valor_bruto' => (float) 0, 'valor_desconto' => (float) 0, 'valor_frete' => (float) 0, 'valor_total' => (float) 0, 'data_cadastro' => 'date', 'data_movimentacao' => 'date', 'status' => (string) '', 'tipo_pedido' => 'bool', 'transacao' => (string) '', 'observacao' => (string) ''];
    }

    /**
     * Função responsável por validar e colocar os dados nas variáveis correspondentes
     * @param mixed $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_pedido', $dados) == true) {
            if ($dados['codigo_pedido'] != '') {
                $this->codigo_pedido = model_id($dados['codigo_pedido']);
            } else {
                $this->codigo_pedido = null;
            }
        } else {
            $this->codigo_pedido = null;
        }

        $objeto_codigo_barras = new EAN13();
        $transacao = (string) $objeto_codigo_barras->getFullCode('');

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
        $this->tipo_pedido = (bool) (isset($dados['tipo_pedido']) ? (bool) filter_var($dados['tipo_pedido'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->transacao = (string) (isset($dados['transacao']) ? (string) $dados['transacao'] : $transacao);
        $this->observacao = (string) (isset($dados['observacao']) ? (string) $dados['observacao'] : '');

        $this->objeto_itens = (array) (isset($dados['objeto_itens']) ? (array) json_decode($dados['objeto_itens'], true) : []);
    }

    /**
     * Função responsável por salvar os dados no banco de dados
     * @param mixed $dados
     * @return array
     */
    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        $retorno = (bool) false;
        $retorno_itens_pedido = (bool) false;

        if ($this->codigo_pedido != null) {
            $retorno = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_pedido], (array) ['empresa' => $this->empresa, 'fornecedor' => $this->fornecedor, 'quantidade_total_itens' => (float) $this->quantidade_total_itens, 'valor_unitario' => (float) $this->valor_unitario, 'valor_bruto' => (float) $this->valor_bruto, 'valor_desconto' => (float) $this->valor_desconto, 'valor_frete' => (float) $this->valor_frete, 'valor_liquido' => (float) $this->valor_liquido, 'data_cadastro' => $this->data_cadastro, 'data_movimentacao' => $this->data_movimentacao, 'status' => (string) $this->status_pedido, 'tipo_pedido' => (bool) $this->tipo_pedido, 'transacao' => (string) $this->transacao, 'observacao' => (string) $this->observacao]);
        } else {
            $codigo_insert = (string) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'fornecedor' => $this->fornecedor, 'quantidade_total_itens' => (float) $this->quantidade_total_itens, 'valor_unitario' => (float) $this->valor_unitario, 'valor_bruto' => (float) $this->valor_bruto, 'valor_desconto' => (float) $this->valor_desconto, 'valor_frete' => (float) $this->valor_frete, 'valor_liquido' => (float) $this->valor_liquido, 'data_cadastro' => $this->data_cadastro, 'data_movimentacao' => $this->data_movimentacao, 'status' => (string) $this->status_pedido, 'tipo_pedido' => (bool) $this->tipo_pedido, 'transacao' => (string) $this->transacao, 'observacao' => (string) $this->observacao], false);

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
                    $itens['quantidade_item'] = $itens['quantidade'];
                    $itens['valor_unitario'] = $itens['valor_unitario_produto'];
                    $itens['valor_bruto'] = $itens['valor_bruto_produto'];
                    $itens['valor_desconto'] = $itens['valor_desconto_produto'];
                    $itens['valor_frete'] = $itens['valor_frete_produto'];
                    $itens['valor_liquido'] = $itens['valor_total_produto'];
                    $itens['pedido'] = $this->codigo_pedido;

                    if ($this->tipo_pedido == true) {
                        $itens['tipo_item_pedido'] = (bool) true;
                    } else {
                        $itens['tipo_item_pedido'] = (bool) false;
                    }

                    $retorno_itens_pedido = (bool) $objeto_itens_pedido->salvar_dados($itens);

                    if ($this->tipo_pedido == true) {
                        if ($this->status_pedido == 'PEDIDO_COMPLETO') {
                            $itens['item_pedido'] = $objeto_itens_pedido->get_codigo_item_pedido();
                            $itens['valor_custo_parametro'] = $itens['valor_unitario'];

                            $objeto_custo_medio = new CustoMedio();
                            $retorno_custo_medio = (bool) $objeto_custo_medio->salvar_dados($itens);
                        }

                        if ($this->status_pedido == 'PEDIDO_COMPLETO' || $this->status_pedido == 'PEDIDO_ESTOQUE') {
                            $objeto_movimentacao_estoque = new MovimentacaoEstoque();

                            $itens['tipo_movimentacao'] = (bool) true;
                            $retorno_movimentacao_estoque = $objeto_movimentacao_estoque->salvar_dados($itens);
                        }
                    } else {
                        if ($this->status_pedido == 'PEDIDO_COMPLETO' || $this->status_pedido == 'PEDIDO_ESTOQUE') {
                            $itens['tipo_movimentacao'] = (bool) false;
                            $objeto_movimentacao_estoque = new MovimentacaoEstoque();

                            $retorno_movimentacao_estoque = $objeto_movimentacao_estoque->salvar_dados($itens);
                        }
                    }
                }
            }

            if ($this->tipo_pedido == true) {
                if ($this->status_pedido == 'PEDIDO_COMPLETO' || $this->status_pedido == 'PEDIDO_CONTA') {
                    $objeto_conta_fornecedor = new ContasFornecedores();
                    $objeto_conta_pagar = new ContasPagarReceber();

                    $retorno_conta_fornecedor = (array) $objeto_conta_fornecedor->pesquisar((array) ['filtro' => (array) ['and' => (array) [['fornecedor', '===', $this->fornecedor], ['status_conta', '===', (bool) true]]]]);

                    if (empty($retorno_conta_fornecedor) == false) {
                        $conta = (array) [];
                        $conta['nome_conta'] = (String) $retorno_conta_fornecedor['nome_conta'];
                        $conta['descricao'] = (string) $retorno_conta_fornecedor['descricao_conta'];
                        $conta['conta_fornecedor'] = $retorno_conta_fornecedor['_id'];
                        $conta['cliente_fornecedor'] = $this->fornecedor;
                        $conta['empresa'] = $this->empresa;
                        $conta['valor_conta'] = $this->valor_liquido;
                        $conta['transacao'] = $this->transacao;

                        $retorno = (bool) $objeto_conta_pagar->salvar_dados($conta);
                    }
                }
            } else {
                if ($this->status_pedido == 'PEDIDO_COMPLETO' || $this->status_pedido == 'PEDIDO_CONTA') {
                    $objeto_sistema = new Sistema();
                    $retorno_sistema = $objeto_sistema->pesquisar((array) ['filtro' => (array) ['empresa', '===', $this->empresa]]);

                    if (empty($retorno_sistema) == false) {
                        $cliente = $retorno_sistema['cliente_padrao'];

                        $objeto_conta_fornecedor = new ContasFornecedores();
                        $objeto_conta_pagar = new ContasPagarReceber();
                        $objeto_produto = new Produtos();

                        $custo_total = (float) 0;

                        foreach ($this->objeto_itens as $item) {
                            $retorno_produto = (array) $objeto_produto->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($item['id_produto'])]]);

                            $custo_for = (float) 0;
                            $venda_for = (float) 0;
                            $valor_custo_for = (float) 0;

                            $venda_for = (float) $retorno_produto['valor_venda'];
                            $valor_custo_for = (float) $retorno_produto['valor_custo'];

                            $custo_for = (float) arredondar($venda_for, '-', $valor_custo_for, 2);

                            $custo_total_for = (float) arredondar($item['quantidade'], '*', $custo_for, 2);
                            $custo_total = (float) arredondar($custo_total_for, '+', $custo_total, 2);
                        }

                        $retorno_conta_fornecedor = (array) $objeto_conta_fornecedor->pesquisar((array) ['filtro' => (array) ['and' => (array) [['fornecedor', '===', $cliente], ['status_conta', '===', (bool) true]]]]);

                        if (empty($retorno_conta_fornecedor) == false) {
                            $conta = (array) [];
                            $conta['nome_conta'] = (String) $retorno_conta_fornecedor['nome_conta'];
                            $conta['descricao'] = (string) $retorno_conta_fornecedor['descricao_conta'] . ' | ' . $this->observacao;
                            $conta['conta_fornecedor'] = $retorno_conta_fornecedor['_id'];
                            $conta['cliente_fornecedor'] = $cliente;
                            $conta['empresa'] = $this->empresa;
                            $conta['valor_conta'] = $custo_total;
                            $conta['transacao'] = $this->transacao;
                            $conta['tipo_conta'] = (string) 'RECEBER';

                            $data = new DateTime();
                            $data->modify('+29 days');
                            $conta['data_vencimento'] = $data->format('Y-m-d');


                            $retorno = (bool) $objeto_conta_pagar->salvar_dados($conta);
                        }
                    }
                }
            }
        }

        return (array) ['retorno_pedido' => (bool) $retorno, 'retorno_itens_pedido' => (bool) $retorno_itens_pedido];
    }

    /**
     * Funçãpo responsável por pesquisa os dados de um pedido em específico
     * @param array $filtro
     * @return array
     */
    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), $filtro['filtro']);
    }

    /**
     * Função responsável por pesquisar todos os pedidos de acordo com os filtros passados
     * @param array $filtro
     * @return array
     */
    public function pesquisar_todos($filtro)
    {
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
    }

    /**
     * Função responsável por montar o filtro de pesquisa e pesquisar as informações
     * @param array $dados
     * @return array
     */
    public function pesquisa($dados)
    {
        $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['data_cadastro' => (bool) false], 'limite' => (int) 0];
        $filtro_montando = (array) [];

        if (array_key_exists('empresa', $dados) == true) {
            if ($dados['empresa'] != '') {
                array_push($filtro_montando, ['empresa', '===', model_id($dados['empresa'])]);
            }
        }

        if (array_key_exists('data_cadastro', $dados) == true) {
            if ($dados['data_cadastro'] != '') {
                array_push($filtro_montando, ['data_cadastro', '>=', model_date($dados['data_cadastro'], '00:00:00')]);
                array_push($filtro_montando, ['data_cadastro', '<=', model_date($dados['data_cadastro'], '23:59:59')]);
            }
        }

        if (array_key_exists('data_movimentacao', $dados) == true) {
            if ($dados['data_movimentacao'] != '') {
                array_push($filtro_montando, ['data_movimentacao', '>=', model_date($dados['data_movimentacao'], '00:00:00')]);
                array_push($filtro_montando, ['data_movimentacao', '<=', model_date($dados['data_cadastro'], '23:59:59')]);
            } else {
                $data = new DateTime();
                $data->modify('-60 days');
                array_push($filtro_montando, ['data_movimentacao', '>=', model_date($data->format('Y-m-d'), '00:00:00')]);
            }
        }

        if (array_key_exists('status_pedido', $dados) == true) {
            if ($dados['status_pedido'] != '') {
                array_push($filtro_montando, ['status', '===', (string) $dados['status_pedido']]);
            }
        }

        if (array_key_exists('tipo_pedido', $dados) == true) {
            array_push($filtro_montando, ['tipo_pedido', '===', (bool) filter_var($dados['tipo_pedido'], FILTER_VALIDATE_BOOLEAN)]);
        }

        if (array_key_exists('transacao', $dados) == true) {
            if ($dados['transacao'] != '') {
                array_push($filtro_montando, ['transacao', '===', (string) $dados['transacao']]);
            }
        }

        $filtro['filtro'] = (array) ['and' => (array) $filtro_montando];

        return (array) $this->pesquisar_todos($filtro);
    }

    /**
     * Função responsável por alterar o status do pedido
     * @param mixed $dados
     * @return bool
     */
    public function alterar_status_pedido($dados)
    {
        $this->colocar_dados($dados);
        $retorno_pedido = (array) $this->pesquisar((array) ['filtro' => ['_id', '===', $this->codigo_pedido]]);

        if (empty($retorno_pedido) == false) {
            $pedido_atual = (string) $retorno_pedido['status'];

            if ($this->status_pedido == 'PEDIDO_ESTOQUE' || $this->status_pedido == 'PEDIDO_COMPLETO') {
                $objeto_movimentacao = new MovimentacaoEstoque();
                $objeto_itens_pedido = new ItensPedidos();

                $boolean_deletar = $objeto_movimentacao->deletar_movimentacao((array) ['filtro' => (array) ['pedido', '===', $this->codigo_pedido]]);

                $retorno_itens = (array) $objeto_itens_pedido->pesquisar_todos((array) ['filtro' => ['pedido', '===', $this->codigo_pedido], 'ordenacao' => (array) ['data_cadastro' => (bool) false], 'limite' => (int) 0]);

                if (empty($retorno_itens) == false) {
                    foreach ($retorno_itens as $itens) {
                        $itens['tipo_movimentacao'] = $itens['tipo_item_pedido'];
                        $itens['data_movimentacao'] = $itens['data_cadastro'];
                        $itens['tipo_movimentacao'] = $itens['tipo_item_pedido'];
                        $retorno = $objeto_movimentacao->salvar_dados($itens);
                    }
                }
            }

            if ($this->status_pedido == 'PEDIDO_CONTA' || $this->status_pedido == 'PEDIDO_COMPLETO') {
                $objeto_conta_fornecedor = new ContasFornecedores();
                $objeto_conta_pagar = new ContasPagarReceber();

                $this->fornecedor = $retorno_pedido['fornecedor'];

                if ($retorno_pedido['tipo_pedido'] == true) {
                    $retorno_conta_fornecedor = (array) $objeto_conta_fornecedor->pesquisar((array) ['filtro' => (array) ['and' => (array) [['fornecedor', '===', $this->fornecedor], ['status_conta', '===', (bool) true]]]]);

                    if (empty($retorno_conta_fornecedor) == false) {
                        $conta = (array) [];
                        $conta['nome_conta'] = (string) $retorno_conta_fornecedor['nome_conta'];
                        $conta['descricao'] = (string) $retorno_conta_fornecedor['descricao_conta'];
                        $conta['conta_fornecedor'] = $retorno_conta_fornecedor['_id'];
                        $conta['cliente_fornecedor'] = $this->fornecedor;
                        $conta['empresa'] = $retorno_conta_fornecedor['empresa'];
                        $conta['valor_conta'] = $retorno_pedido['valor_liquido'];
                        $conta['transacao'] = $retorno_pedido['transacao'];

                        $retorno = (bool) $objeto_conta_pagar->salvar_dados($conta);
                    }
                } else {
                    $objeto_sistema = new Sistema();
                    $retorno_sistema = $objeto_sistema->pesquisar((array) ['filtro' => (array) ['empresa', '===', $retorno_pedido['empresa']]]);

                    if (empty($retorno_sistema) == false) {
                        $cliente = $retorno_sistema['cliente_padrao'];

                        $objeto_conta_fornecedor = new ContasFornecedores();
                        $objeto_conta_pagar = new ContasPagarReceber();

                        $retorno_conta_fornecedor = (array) $objeto_conta_fornecedor->pesquisar((array) ['filtro' => (array) ['and' => (array) [['fornecedor', '===', $cliente], ['status_conta', '===', (bool) true]]]]);

                        if (empty($retorno_conta_fornecedor) == false) {
                            $conta = (array) [];
                            $conta['nome_conta'] = (String) $retorno_conta_fornecedor['nome_conta'];
                            $conta['descricao'] = (string) $retorno_conta_fornecedor['descricao_conta'];
                            $conta['conta_fornecedor'] = $retorno_conta_fornecedor['_id'];
                            $conta['cliente_fornecedor'] = $cliente;
                            $conta['empresa'] = $this->empresa;
                            $conta['valor_conta'] = $this->valor_liquido;
                            $conta['transacao'] = $this->transacao;
                            $conta['tipo_conta'] = (string) 'RECEBER';

                            $data = new DateTime();
                            $data->modify('+28 days');
                            $conta['data_vencimento'] = $data->format('Y-m-d');


                            $retorno = (bool) $objeto_conta_pagar->salvar_dados($conta);
                        }
                    }
                }
            }

            $retorno_update = model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_pedido], (array) ['status' => (string) $this->status_pedido, 'data_movimentacao' => model_date()]);

            return (bool) true;
        } else {
            return (bool) false;
        }
    }

    /**
     * Função responsável por cancelar o pedido
     * Cancelando junto a movimentação de estoque e a conta, 
     * caso a mesma tenha sido gerada junto, 
     * além de alterar o status do pedido para cancelado
     * @param array $dados
     * @return bool
     */
    public function cancelar_pedido($dados)
    {
        $this->colocar_dados($dados);

        $objeto_movimentacao = new MovimentacaoEstoque();
        $objeto_conta = new ContasPagarReceber();

        $filtro_delete_movimentacao = (array) ['filtro' => (array) ['pedido', '===', $this->codigo_pedido]];
        $filtro_delete_conta = (array) ['filtro' => (array) ['transacao', '===', (string) $this->transacao]];

        $retorno_delete_movimentacao = (bool) $objeto_movimentacao->deletar_movimentacao($filtro_delete_movimentacao);

        $retorno_delete_conta = (bool) $objeto_conta->deletar_conta($filtro_delete_conta);

        return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_pedido], (array) ['status' => (string) 'CANCELADO']);
    }

    public function montar_array(){
        return (array) [];
    }
}
