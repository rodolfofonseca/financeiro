<?php
require_once 'classes/bancoDeDados.php';
require_once 'classes/Mongo/Mongo.php';
require_once 'classes/CodigoBarras/EAN13.php';

require_once 'modelos/Interface.php';
require_once 'modelos/Movimentacao.php';
require_once 'modelos/Usuario.php';
require_once 'modelos/Sistema.php';
require_once 'modelos/ContasFornecedores.php';

class ContasPagarReceber implements InterfaceModelo
{
    private mixed $codigo_conta_pagar_receber;
    private mixed $empresa;
    private mixed $cliente_fornecedor;
    private mixed $conta_fornecedor;
    private string $nome_conta;
    private string $descricao;
    private float $valor_conta;
    private float $valor_pago;
    private float $valor_juro_desconto;
    private string $tipo_juro_desconto;
    private string $tipo_conta;
    private mixed $data_cadastro;
    private mixed $data_cadastro_fim;
    private mixed $data_vencimento;
    private mixed $data_vencimento_fim;
    private mixed $data_baixa;
    private mixed $data_baixa_fim;
    private string $status_conta;
    private string $comprovante;
    private string $boleto;
    private string $transacao;

    public function tabela()
    {
        return (string) 'contas_pagar_receber';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'cliente_fornecedor' => 'objectId', 'conta_fornecedor' => 'objectId', 'nome_conta' => (string) '', 'descricao' => (string) '', 'valor_conta' => (float) 0, 'valor_pago' => (float) 0, 'valor_juro_desconto' => (float) 0, 'tipo_juro_desconto' => (string) '', 'tipo_conta' => (string) 'PAGAR', 'data_cadastro' => 'date', 'data_vencimento' => 'date', 'data_baixa' => 'date', 'status_conta' => (string) 'AGUARDANDO', 'comprovante' => (string) 'NAO', 'boleto' => (string) 'NAO', 'transacao' => (string) ''];
    }

    public function colocar_dados($dados = [])
    {
        if (array_key_exists('codigo_conta_pagar_receber', $dados) == true) {
            if ($dados['codigo_conta_pagar_receber'] != '') {
                $this->codigo_conta_pagar_receber = model_id($dados['codigo_conta_pagar_receber']);
            } else {
                $this->codigo_conta_pagar_receber = null;
            }
        } else {
            $this->codigo_conta_pagar_receber = null;
        }

        $objeto_codigo_barras = new EAN13();
        $transacao = (string) $objeto_codigo_barras->getFullCode('');

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->cliente_fornecedor = (isset($dados['cliente_fornecedor']) ? model_id($dados['cliente_fornecedor']) : '');
        $this->conta_fornecedor = (isset($dados['conta_fornecedor']) ? model_id($dados['conta_fornecedor']) : '');
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? strtoupper($dados['nome_conta']) : '');
        $this->descricao = (string) (isset($dados['descricao']) ? (string) strtoupper($dados['descricao']) : '');
        $this->valor_conta = (float) (isset($dados['valor_conta']) ? (float) doubleval(str_replace(',', '.', $dados['valor_conta'])) : 0);
        $this->valor_pago = (float) (isset($dados['valor_pago']) ? (float) doubleval(str_replace(',', '.', $dados['valor_pago'])) : 0);
        $this->valor_juro_desconto = (float) (isset($dados['valor_juro_desconto']) ? (float) doubleval(str_replace(',', '.', $dados['valor_juro_desconto'])) : 0);
        $this->tipo_juro_desconto = (string) (isset($dados['tipo_juro_desconto']) ? (string) $dados['tipo_juro_desconto'] : '');
        $this->tipo_conta = (string) (isset($dados['tipo_conta']) ? (string) $dados['tipo_conta'] : 'PAGAR');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
        $this->data_vencimento = (isset($dados['data_vencimento']) ? model_date($dados['data_vencimento']) : model_date());
        $this->data_baixa = (isset($dados['data_baixa']) ? model_date($dados['data_baixa']) : model_date());
        $this->status_conta = (string) (isset($dados['status_conta']) ? (string) $dados['status_conta'] : 'AGUARDANDO');
        $this->comprovante = (string) (isset($dados['anexa_documentos']) ? (string) $dados['anexa_documentos'] : 'NAO');
        $this->boleto = (string) (isset($dados['boleto']) ? (string) $dados['boleto'] : 'NAO');
        $this->transacao = (string) (isset($dados['transacao']) ? (string) $dados['transacao'] : $transacao);
    }

    public function salvar_dados($dados = [])
    {
        $this->colocar_dados($dados);

        if ($this->codigo_conta_pagar_receber != null) {
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_conta_pagar_receber], (array) ['empresa' => $this->empresa, 'cliente_fornecedor' => $this->cliente_fornecedor, 'conta_fornecedor' => $this->conta_fornecedor, 'nome_conta' => (string) $this->nome_conta, 'descricao' => (string) $this->descricao, 'valor_conta' => (float) $this->valor_conta, 'valor_pago' => (float) $this->valor_pago, 'valor_juro_desconto' => (float) $this->valor_juro_desconto, 'tipo_juro_desconto' => (string) $this->tipo_juro_desconto, 'tipo_conta' => (string) $this->tipo_conta, 'data_cadastro' => $this->data_cadastro, 'data_vencimento' => $this->data_vencimento, 'data_baixa' => $this->data_baixa, 'status_conta' => (string) $this->status_conta, 'comprovante' => (string) $this->comprovante, 'boleto' => (string) $this->boleto, 'transacao' => (string) $this->transacao]);
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'cliente_fornecedor' => $this->cliente_fornecedor, 'conta_fornecedor' => $this->conta_fornecedor, 'nome_conta' => (string) $this->nome_conta, 'descricao' => (string) $this->descricao, 'valor_conta' => (float) $this->valor_conta, 'valor_pago' => (float) $this->valor_pago, 'valor_juro_desconto' => (float) $this->valor_juro_desconto, 'tipo_juro_desconto' => (string) $this->tipo_juro_desconto, 'tipo_conta' => (string) $this->tipo_conta, 'data_cadastro' => $this->data_cadastro, 'data_vencimento' => $this->data_vencimento, 'data_baixa' => $this->data_baixa, 'status_conta' => (string) $this->status_conta, 'comprovante' => (string) $this->comprovante, 'boleto' => (string) $this->boleto, 'transacao' => (string) $this->transacao]);
        }
    }

    public function pesquisar($filtro = [])
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    public function pesquisar_todos($filtro = [])
    {
        $retorno_contas = (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
        $retorno_final = (array) [];

        if (empty($retorno_contas) == false) {
            $objeto_cliente_fornecedor = new Usuario();
            $filtro_fornecedor = (array) ['filtro' => (array) []];

            foreach ($retorno_contas as $contas) {
                if (array_key_exists('cliente_fornecedor', $contas) == true) {
                    $filtro_fornecedor['filtro'] = (array) ['_id', '===', $contas['cliente_fornecedor']];
                    $retorno_cliente = (array) $objeto_cliente_fornecedor->pesquisar($filtro_fornecedor);

                    if (empty($retorno_cliente) == false) {
                        $contas['pessoa'] = $retorno_cliente;
                    }
                }

                array_push($retorno_final, $contas);
            }
        }

        return (array) $retorno_final;
    }

    /**
     * Função para baixar uma conta a pagar ou receber, realizando a movimentação financeira e atualizando o status da conta para "PAGO".
     * @param array $dados - Array contendo os dados necessários para o processo de baixa, incluindo o código da conta a pagar ou receber, o código da conta bancária para a movimentação, o valor pago, o valor de juros ou desconto, o tipo de juros ou desconto e a data de baixa.
     * @return bool - Retorna true se a baixa for realizada com sucesso, ou false caso ocorra algum erro durante o processo.
     */
    public function baixar_contas($dados)
    {
        $this->colocar_dados($dados);

        $objeto_movimentacao = new Movimentacao();

        $conta = (array) $this->pesquisar((array) ['filtro' => (array) ['_id', '===', $this->codigo_conta_pagar_receber]]);

        if (empty($conta) == false) {
            $array_movimentacao = (array) ['empresa' => (string) $dados['empresa'], 'conta' => (string) $dados['codigo_conta_bancaria'], 'valor_lancamento' => (float) $this->valor_pago];



            if ($this->tipo_conta == 'PAGAR') {
                $array_movimentacao['tipo_lancamento'] = (string) 'DEBITO';
                $array_movimentacao['descricao'] = (string) 'Pagamento da conta ' . $this->nome_conta;
            } else {
                $array_movimentacao['tipo_lancamento'] = (string) 'CREDITO';
                $array_movimentacao['descricao'] = (string) 'Recebimento da conta ' . $this->nome_conta;
            }

            $retorno_movimentacao = (bool) $objeto_movimentacao->salvar_dados($array_movimentacao);

            if ($retorno_movimentacao == true) {

                $retorno_contas_pagar_receber = (bool) model_update((string) $this->tabela(), ['_id', '===', $this->codigo_conta_pagar_receber], (array) ['valor_pago' => (float) $this->valor_pago, 'valor_juro_desconto' => (float) $this->valor_juro_desconto, 'tipo_juro_desconto' => (string) $this->tipo_juro_desconto, 'data_baixa' => $this->data_baixa, 'status_conta' => (string) 'PAGO']);

                if ($retorno_movimentacao == true && $retorno_contas_pagar_receber == true) {
                    return (bool) true;
                } else {
                    return (bool) false;
                }
            } else {
                return (bool) false;
            }
        } else {
            return (bool) false;
        }
    }

    /**
     * Função para alterar o status das contas a pagar ou receber para "VENCIDA" caso a data de vencimento seja menor ou igual à data atual e o status da conta seja "AGUARDANDO".
     * @param array $dados - Array contendo os dados necessários para a alteração do status, incluindo o código da empresa.
     * @return bool - Retorna true se o status for alterado com sucesso, ou false caso ocorra algum erro durante o processo.
     */
    public function alterar_status($dados)
    {
        $retorno_pesquisa = (array) model_all((string) $this->tabela(), (array) ['and' => [['empresa', '===', model_id($dados['empresa'])], ['data_vencimento', '<=', model_date('', '23:59:59')], ['status_conta', '===', (string) 'AGUARDANDO']]]);

        if (empty($retorno_pesquisa) == false) {
            $objeto_codigo_barras = new EAN13();
            $objeto_usuario = new Usuario();
            $objeto_conta_fornecedor = new ContasFornecedores();

            foreach ($retorno_pesquisa as $pesquisa) {
                if(array_key_exists('cliente_fornecedor', $pesquisa) == false){
                    $usuario = (array) $objeto_usuario->pesquisar((array) ['filtro' => (array) ['and' => (array) [(array) ['empresa', '===', $pesquisa['empresa']], (array) ['tipo_usuario', '===', (string) 'CLIENTE'], (array) ['nome_usuario', '===', (string) 'CLIENTE PADRAO']]]]);

                    if(empty($usuario) == false){
                        $pesquisa['cliente_fornecedor'] = $usuario['_id'];
                    }
                }

                if(array_key_exists('conta_fornecedor', $pesquisa) == false){
                    $conta = (array) $objeto_conta_fornecedor->pesquisar((array) ['filtro' => (array) ['fornecedor', '===', $pesquisa['cliente_fornecedor']]]);

                    if(empty($conta) == false){
                        $pesquisa['conta_fornecedor'] = $conta['_id'];
                    }
                }

                if(array_key_exists('transacao', $pesquisa) == false){
                    $pesquisa['transacao'] = (string) $objeto_codigo_barras->getFullCode();
                }

                $pesquisa['status_conta'] = (string) 'VENCIDA';
                $pesquisa['codigo_conta_pagar_receber'] = (string) $pesquisa['_id'];
                
                $this->salvar_dados($pesquisa);
            }

            return (bool) true;
        } else {
            return (bool) false;
        }
    }

    /**
     * Função para alterar o campo de anexo de documentos para "SIM" em uma conta a pagar ou receber específica, indicando que há documentos anexados à conta.
     * @param string $codigo_conta - O código da conta a pagar ou receber que terá o campo de anexo de documentos alterado para "SIM".
     * @return bool - Retorna true se o campo de anexo de documentos for alterado com sucesso, ou false caso ocorra algum erro durante o processo.
     */
    public function alterar_anexo_documento($codigo_conta)
    {
        $retorno = (array) $this->pesquisar((array) ['filtro' => (array) ['_id', '===', $codigo_conta]]);

        if (empty($retorno) == false) {
            $retorno['anexa_documentos'] = (string) 'SIM';
            $retorno['codigo_conta_pagar_receber'] = (string) $codigo_conta;
        }

        return (bool) $this->salvar_dados($retorno);
    }

    /**
     * Função para alterar o campo de boleto para "SIM" em uma conta a pagar ou receber específica, indicando que há um boleto associado à conta.
     * @param string $codigo_conta - O código da conta a pagar ou receber que terá o campo de boleto alterado para "SIM".
     * @return bool - Retorna true se o campo de boleto for alterado com sucesso, ou false caso ocorra algum erro durante o processo.
     */
    public function alterar_anexo_boleto($codigo_conta)
    {
        $retorno = (array) $this->pesquisar((array) ['filtro' => (array) ['_id', '===', $codigo_conta]]);

        if (empty($retorno) == false) {
            $retorno['boleto'] = (string) 'SIM';
            $retorno['codigo_conta_pagar_receber'] = (string) $codigo_conta;
        }

        return (bool) $this->salvar_dados($retorno);
    }


    /**
     * Função para gerar um relatório de contas a pagar, agrupando as contas por status e calculando a quantidade total de contas e o valor total para cada status.
     * @param string $codigo_empresa - O código da empresa para a qual o relatório de contas a pagar será gerado.
     * @return array - Retorna um array contendo o relatório de contas a pagar, onde cada elemento do array representa um status de conta e inclui a quantidade total de contas e o valor total para esse status.
     */
    public function relatorio_contas_pagar($codigo_empresa)
    {
        $pipeline = [
            [
                '$match' => [
                    'empresa' => model_id($codigo_empresa),
                    'status_conta' => [
                        '$nin' => ['PAGO', 'CANCELADO']
                    ],
                    'tipo_conta' => ['$in' => ['PAGAR', 'RECEBER']]
                ]
            ],
            [
                '$addFields' => [
                    'status_group' => [
                        '$cond' => [
                            'if' => [
                                '$in' => ['$status_conta', ['VENCIDA', 'VENCIDO']]
                            ],
                            'then' => 'VENCIDA',
                            'else' => 'AGUARDANDO'
                        ]
                    ]
                ]
            ],
            [
                '$group' => [
                    '_id' => [
                        'tipo_conta' => '$tipo_conta',
                        'status_group' => '$status_group'
                    ],
                    'COUNT(*)' => ['$sum' => 1],
                    'SUM(valor_conta)' => ['$sum' => '$valor_conta']
                ]
            ],
            [
                '$project' => [
                    '_id' => 0,
                    'tipo_conta' => '$_id.tipo_conta',
                    'status_conta' => '$_id.status_group',
                    'COUNT(*)' => 1,
                    'SUM(valor_conta)' => 1
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
     * Função para gerar um relatório de contas a pagar mensal, agrupando as contas por mês e status e calculando a quantidade total de contas e o valor total para cada grupo.
     * @param string $codigo_empresa - O código da empresa para a qual o relatório de contas a pagar mensal será gerado.
     * @param string $data - A data de vencimento das contas a serem incluídas no relatório.
     * @param string $hora - A hora de vencimento das contas a serem incluídas no relatório.
     * @return array - Retorna um array contendo o relatório de contas a pagar mensal, onde cada elemento do array representa um grupo de contas e inclui a quantidade total de contas e o valor total para esse grupo.
     */
    public function relatorio_contas_pagar_mensal($codigo_empresa, $data, $hora)
    {
        $pipeline = [

            [
                '$match' => [
                    'empresa' => model_id($codigo_empresa),
                    'data_vencimento' => [
                        '$gte' => model_date($data, $hora)
                    ]
                ]
            ],
            [
                '$addFields' => [
                    'status_normalizado' => [
                        '$switch' => [
                            'branches' => [
                                [
                                    'case' => ['$in' => ['$status_conta', ['VENCIDA', 'VENCIDO']]],
                                    'then' => 'VENCIDO'
                                ]
                            ],
                            'default' => '$status_conta'
                        ]
                    ]
                ]
            ],
            [
                '$group' => [
                    '_id' => [
                        'ano' => ['$year' => '$data_vencimento'],
                        'mes' => ['$month' => '$data_vencimento'],
                        'tipo_conta' => '$tipo_conta',
                        'status_conta' => '$status_normalizado'
                    ],
                    'total_contas' => ['$sum' => 1],
                    'total_valor' => ['$sum' => '$valor_conta']
                ]
            ],
            [
                '$project' => [
                    '_id' => 0,
                    'ano' => '$_id.ano',
                    'mes' => '$_id.mes',
                    'status_conta' => '$_id.status_conta',
                    'tipo_conta' => '$_id.tipo_conta',
                    'total_contas' => 1,
                    'total_valor' => 1
                ]
            ],
            [
                '$sort' => [
                    'ano' => 1,
                    'mes' => 1,
                    'tipo_conta' => 1,
                    'status_conta' => 1
                ]
            ]
        ];


        $cursor = pesquisa_banco_aggregate((string) $this->tabela(), $pipeline);

        $retorno = (array) [];

        foreach ($cursor as $document) {
            $array_temporario = (array) [];

            $array_temporario['total_contas'] = (int) $document['total_contas'];
            $array_temporario['total_valor'] = (float) doubleval(arredondar($document['total_valor']));
            $array_temporario['ano'] = (int) $document['ano'];
            $array_temporario['mes'] = (int) $document['mes'];
            $array_temporario['status_conta'] = (string) $document['status_conta'];
            $array_temporario['tipo_conta'] = (string) $document['tipo_conta'];

            array_push($retorno, $array_temporario);
        }

        return (array) $retorno;
    }

    /**
     * Função responsável por deletar a conta cadastrada, esta função não remove a movimentação, caso a mesma já tenha sido cadastrada
     * @param mixed $filtro
     * @return bool
     */
    public function deletar_conta($filtro)
    {
        return (bool) model_delete((string) $this->tabela(), (array) $filtro['filtro']);
    }

    /** 
     * Função responsável por pesquisar as contas vencidas no banco de dados
     * @param array $dados - array contendo a 'empresa', 'data_vencimento', 'status_conta'
     * @return array return - com as contas vencidas;
     */
    public function pesquisar_contas_vencidas($dados)
    {
        $this->colocar_dados($dados);

        $filtro = ['filtro' => (array) [], 'ordenacao' => (array) ['data_vencimento' => (bool) true], 'limite' => (int) 0];
        $filtro_montando = (array) [];

        array_push($filtro_montando, ['empresa', '===', $this->empresa]);
        array_push($filtro_montando, ['data_vencimento', '<=', $this->data_vencimento]);
        array_push($filtro_montando, ['status_conta', '===', (string) $this->status_conta]);

        $filtro['filtro'] = (array) ['and' => (array) $filtro_montando];

        return (array) $this->pesquisar_todos($filtro);
    }

    /**
     * Função responsável por pegar a data atual, tirar 5 anos e excluir as contas baixadas.
     * Sejam elas Pagas ou recebidas
     * @param mixed $dados - Da empresa que será excluída a conta
     * @return bool - com o resultado
     */
    public function deletar_contas_pagar_receber_antigas($dados)
    {
        $this->colocar_dados($dados);

        $data_atual = (new DateTime())->modify('-5 years')->format(('Y-m-d'));
        $filtro = (array) [];

        if ($this->empresa != '') {
            array_push($filtro, (array) ['empresa', '===', $this->empresa]);
        } else {
            return (bool) false;
        }

        array_push($filtro, (array) ['data_baixa', '<=', model_date($data_atual)]);
        array_push($filtro, (array) ['status_conta', '===', (string) 'PAGO']);

        return (bool) model_delete((string) $this->tabela(), (array) ['and' => (array) $filtro]);
    }

    /**
     * Função responsável por contar a quantidade de contas que possui vencidas e retornar, emitindo um alerta ao usuário
     * @param mixed $dados
     * @return array
     */
    public function contar_contas_vencidas($dados)
    {
        $this->colocar_dados($dados);

        $pipeline = [
            [
                '$match' => [
                    'empresa' => $this->empresa,
                    'tipo_conta' => 'PAGAR',
                    'status_conta' => 'VENCIDA'
                ]
            ],
            [
                '$group' => [
                    '_id' => [],
                    'COUNT(*)' => [
                        '$sum' => 1
                    ],
                    'SUM(valor_conta)' => [
                        '$sum' => '$valor_conta'
                    ]
                ]
            ],
            [
                '$project' => [
                    'COUNT(*)' => '$COUNT(*)',
                    'SUM(valor_conta)' => '$SUM(valor_conta)',
                    '_id' => 0
                ]
            ]
        ];

        $cursor = pesquisa_banco_aggregate((string) $this->tabela(), $pipeline);

        $retorno = (array) ['quantidade_contas' => (int) 0, 'valor_total_contas' => (double) 0];

        if (empty(($cursor)) == false) {
            foreach ($cursor as $document) {
                $retorno['quantidade_contas'] = (int) $document['COUNT(*)'];
                $retorno['valor_total_contas'] = (double) $document['SUM(valor_conta)'];
            }
        }

        return (array) $retorno;
    }

    /**
     * Função responsável por pesquisar todas as contas com o vencimento dentro do mês e retornar o seu status para montar o relatório
     * @param mixed $codigo_empresa
     * @param mixed $data_inicial
     * @param mixed $data_final
     * @return array
     */
    public function relatorio_conta_pagar_mes($codigo_empresa, $data_inicial, $data_final)
    {
        // $pipeline = [
        //     [
        //         '$match' => [
        //             '$and' => [
        //                 [
        //                     '$or' => [
        //                         [
        //                             '$and' => [
        //                                 ['empresa' => model_id($codigo_empresa)],
        //                                 ['tipo_conta' => 'PAGAR'],
        //                                 ['status_conta' => 'AGUARDANDO']
        //                             ]
        //                         ],
        //                         [
        //                             'status_conta' => 'VENCIDA'
        //                         ],
        //                         [
        //                             'tipo_conta' => 'RECEBER'
        //                         ]
        //                     ]
        //                 ],
        //                 [
        //                     'data_vencimento' => [
        //                         '$gte' => model_date($data_inicial, '00:00:00'),
        //                         '$lte' => model_date($data_final, '23:59:59')
        //                     ]
        //                 ]
        //             ]
        //         ]
        //     ],
        //     [
        //         '$group' => [
        //             '_id' => [
        //                 'status_conta' => '$status_conta',
        //                 'tipo_conta' => '$tipo_conta'
        //             ],
        //             'COUNT(*)' => [
        //                 '$sum' => 1
        //             ],
        //             'SUM(valor_conta)' => [
        //                 '$sum' => '$valor_conta'
        //             ]
        //         ]
        //     ],
        //     [
        //         '$project' => [
        //             'status_conta' => '$_id.status_conta',
        //             'tipo_conta' => '$_id.tipo_conta',
        //             'COUNT(*)' => '$COUNT(*)',
        //             'SUM(valor_conta)' => '$SUM(valor_conta)',
        //             '_id' => 0
        //         ]
        //     ]
        // ];

        $pipeline = [
    [
        '$match' => [
            '$and' => [
                [
                    'empresa' => model_id($codigo_empresa)
                ],
                [
                    '$or' => [
                        [
                            '$and' => [
                                ['tipo_conta' => 'PAGAR'],
                                ['status_conta' => 'AGUARDANDO']
                            ]
                        ],
                        [
                            'status_conta' => 'VENCIDA'
                        ],
                        [
                            'tipo_conta' => 'RECEBER'
                        ]
                    ]
                ],
                [
                    'data_vencimento' => [
                        '$gte' => model_date($data_inicial, '00:00:00'),
                        '$lte' => model_date($data_final, '23:59:59')
                    ]
                ]
            ]
        ]
    ],
    [
        '$group' => [
            '_id' => [
                'status_conta' => '$status_conta',
                'tipo_conta' => '$tipo_conta'
            ],
            'COUNT(*)' => [
                '$sum' => 1
            ],
            'SUM(valor_conta)' => [
                '$sum' => '$valor_conta'
            ]
        ]
    ],
    [
        '$project' => [
            'status_conta' => '$_id.status_conta',
            'tipo_conta' => '$_id.tipo_conta',
            'COUNT(*)' => '$COUNT(*)',
            'SUM(valor_conta)' => '$SUM(valor_conta)',
            '_id' => 0
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
     * Função responsável por pesquisar as contas a pagar ou receber com base em diversos filtros, como nome da conta, descrição, status, tipo, datas de cadastro, vencimento e baixa, empresa e cliente/fornecedor. A função monta um filtro dinâmico com base nos dados fornecidos e retorna as contas que correspondem aos critérios de pesquisa.
     * @param mixed $dados
     * @return array
     */
    public function pesquisar_contas($dados)
    {
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? (string) $dados['nome_conta'] : '');
        $this->descricao = (string) (isset($dados['descricao']) ? (string) $dados['descricao'] : '');
        
        $this->status_conta = (string) (isset($dados['status_conta']) ? (string) $dados['status_conta'] : 'TODOS');
        $this->tipo_conta = (string) (isset($dados['tipo_conta']) ? (string) $dados['tipo_conta'] : 'TODOS');
        
        $this->data_cadastro = (string) (isset($dados['data_cadastro_inicio']) ? (string) $dados['data_cadastro_inicio']:'');
        $this->data_cadastro_fim = (string) (isset($dados['data_cadastro_fim']) ?(string) $dados['data_cadastro_fim']:'');
        $this->data_vencimento = (string) (isset($dados['data_vencimento_inicio']) ? (string) $dados['data_vencimento_inicio']:'');
        $this->data_vencimento_fim = (string) (isset($dados['data_vencimento_fim']) ? (string) $dados['data_vencimento_fim']:'');
        $this->data_baixa = (string) (isset($dados['data_baixa_inicio']) ? (string) $dados['data_baixa_inicio']:'');
        $this->data_baixa_fim = (string) (isset($dados['data_baixa_fim']) ? (string) $dados['data_baixa_fim']:'');

        $this->empresa = (string) (isset($dados['empresa']) ? (string) $dados['empresa']:'');
        $this->cliente_fornecedor = (string) (isset($dados['cliente_fornecedor']) ? (string) $dados['cliente_fornecedor']:'');

        $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['data_vencimento' => (bool) true], 'limite' => (int) 0];
        $filtro_montando = (array) [];

        if($this->nome_conta != ''){
            array_push($filtro_montando, ['nome_conta', '=', (string) $this->nome_conta]);
        }

        if($this->descricao != ''){
            array_push($filtro_montando, ['descricao', '=', (string) $this->descricao]);
        }

        if($this->tipo_conta != 'TODOS'){
            array_push($filtro_montando, ['tipo_conta', '===', (string) $this->tipo_conta]);
        }

        if($this->status_conta != 'TODOS'){
            array_push($filtro_montando, ['status_conta', '===', (string) $this->status_conta]);
        }

        if($this->empresa != ''){
            array_push($filtro_montando, ['empresa', '===', model_id($this->empresa)]);
        }

        if($this->data_cadastro != ''){
            array_push($filtro_montando, ['data_cadastro', '>=', model_date($this->data_cadastro, '00:00:00')]);
        }

        if($this->data_cadastro_fim != ''){
            array_push($filtro_montando, ['data_cadastro', '<=', model_date($this->data_cadastro_fim, '23:59:59')]);
        }

        if($this->data_vencimento != ''){
            array_push($filtro_montando, ['data_vencimento', '>=', model_date($this->data_vencimento, '00:00:00')]);
        }

        if($this->data_vencimento_fim != ''){
            array_push($filtro_montando, ['data_vencimento', '<=', model_date($this->data_vencimento_fim, '23:59:59')]);
        }

        if($this->data_baixa != ''){
            array_push($filtro_montando, ['data_baixa', '>=', model_date($this->data_baixa, '00:00:00')]);
        }

        if($this->data_baixa_fim != ''){
            array_push($filtro_montando, ['data_baixa', '<=', model_date($this->data_baixa_fim, '23:59:59')]);
        }

        if($this->cliente_fornecedor != ''){
            array_push($filtro_montando, ['cliente_fornecedor', '===', model_id($this->cliente_fornecedor)]);
        }

        $filtro['filtro'] = (array) ['and' => (array) $filtro_montando];

        return (array) $this->pesquisar_todos($filtro);
    }
}
?>