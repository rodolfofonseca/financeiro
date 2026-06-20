<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class MovimentacaoEstoque implements InterfaceModelo
{
    private mixed $codigo_movimentacao_estoque;
    private mixed $empresa;
    private mixed $produto;
    private mixed $pedido;
    private mixed $data_movimentacao;
    private float $quantidade;
    private bool $tipo_movimentacao;

    /*
    ! TRUE ENTRADA
    ! FALSE SAÍDA
    */

    public function tabela()
    {
        return (string) 'movimentacao_estoque';
    }
    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'produto' => 'objectId', 'pedido' => 'objectId', 'data_movimentacao' => 'date', 'quantidade' => (float) 0, 'tipo_movimentacao' => 'bool'];
    }

    /**
     * Função responsável por colocar os dados nas variáveis
     * @param array $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_movimentacao_estoque', $dados) == true) {
            if ($dados['codigo_movimentacao_estoque'] != '') {
                $this->codigo_movimentacao_estoque = model_id($dados['codigo_movimentacao_estoque']);
            } else {
                $this->codigo_movimentacao_estoque = null;
            }
        } else {
            $this->codigo_movimentacao_estoque = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->produto = (isset($dados['produto']) ? model_id($dados['produto']) : '');
        $this->pedido = (isset($dados['pedido']) ? model_id($dados['pedido']) : '');
        $this->data_movimentacao = (isset($dados['data_movimentacao']) ? model_date($dados['data_movimentacao']) : model_date());
        $this->quantidade = (float) (isset($dados['quantidade']) ? (float) floatval(str_replace(',', '.', $dados['quantidade'])) : 0);
        $this->tipo_movimentacao = (bool) (isset($dados['tipo_movimentacao']) ? (bool) filter_var($dados['tipo_movimentacao'], FILTER_VALIDATE_BOOLEAN) : false);
    }

    /**
     * Função responsável por salvar no banco de dados os dados
     * @param array $dados
     * @return bool
     */
    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        $dados = (array) ['empresa' => $this->empresa, 'produto' => $this->produto, 'pedido' => $this->pedido, 'data_movimentacao' => $this->data_movimentacao, 'quantidade' => (float) $this->quantidade, 'tipo_movimentacao' => (bool) $this->tipo_movimentacao];

        if ($this->codigo_movimentacao_estoque != null) {
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_movimentacao_estoque], (array) $dados);
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) $dados);
        }
    }

    /**
     * Função responsável por pesquisar apenas produto.
     * @param array $filtro
     * @return array
     */
    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }
    /**
     * Função responsável por pesquisar todos os itens do banco de dados
     * @param array $filtro
     * @return array
     */
    public function pesquisar_todos($filtro)
    {
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
    }

    /** 
     * Função responsável por retornar todas as entradas e todas as saídas para o produto
     * @param array $dados
     * @return array
     */
    public function retornar_estoque($dados)
    {
        $pipeline = [
            [
                '$match' => [
                    'produto' => $dados['produto']
                ]
            ],
            [
                '$group' => [
                    '_id' => null,
                    'entradas' => [
                        '$sum' => [
                            '$cond' => [
                                ['$eq' => ['$tipo_movimentacao', true]],
                                '$quantidade',
                                0
                            ]
                        ]
                    ],
                    'saidas' => [
                        '$sum' => [
                            '$cond' => [
                                ['$eq' => ['$tipo_movimentacao', false]],
                                '$quantidade',
                                0
                            ]
                        ]
                    ]
                ]
            ],
            [
                '$project' => [
                    '_id' => 0,
                    'entradas' => 1,
                    'saidas' => 1,
                    'saldo' => [
                        '$subtract' => ['$entradas', '$saidas']
                    ]
                ]
            ]
        ];

        $cursor = pesquisa_banco_aggregate((string) $this->tabela(), $pipeline);

        $retorno = (array) [];

        foreach ($cursor as $document) {
            array_push($retorno, $document);
        }

        return (array) $retorno;
    }

    /**
     * Função responsável por deletar as movimentações de estoque, caso possuam
     * @param mixed $filtro
     * @return bool
     */
    public function deletar_movimentacao($filtro)
    {
        return (bool) model_delete((string) $this->tabela(), (array) $filtro['filtro']);
    }

    public function montar_array(){
        return (array) [];
    }
}
?>