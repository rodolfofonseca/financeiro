<?php
require_once 'classes/bancoDeDados.php';
require_once 'classes/CodigoBarras/EAN13.php';

require_once 'modelos/Interface.php';
require_once 'modelos/Movimentacao.php';
require_once 'modelos/Usuario.php';
require_once 'modelos/Sistema.php';
require_once 'modelos/ContasFornecedores.php';

class ContasPagarReceber implements InterfaceModelo
{
    private int $codigo_conta_pagar_receber;
    private int $empresa;
    private int $cliente_fornecedor;
    private int $conta_fornecedor;
    private string $nome_conta;
    private string $descricao;
    private float $valor_conta;
    private float $valor_pago;
    private float $valor_juro_desconto;
    private string $tipo_juro_desconto;
    private bool $tipo_conta;
    private string $data_cadastro;
    private string $data_cadastro_fim;
    private string $data_vencimento;
    private string $data_vencimento_fim;
    private string $data_baixa;
    private string $data_baixa_fim;
    private string $status_conta;
    private string $comprovante;
    private string $boleto;
    private string $transacao;

    public function tabela()
    {
        return (string) 'conta_pagar_receber';
    }

    public function modelo()
    {
        return (array) [];
    }

    public function colocar_dados($dados = [])
    {
        if (array_key_exists('codigo_conta_pagar_receber', $dados) == true) {
            if ($dados['codigo_conta_pagar_receber'] != 0) {
                $this->codigo_conta_pagar_receber = (int) intval($dados['codigo_conta_pagar_receber'], 10);
            } else {
                $this->codigo_conta_pagar_receber = 0;
            }
        } else {
            $this->codigo_conta_pagar_receber = 0;
        }

        $objeto_codigo_barras = new EAN13();
        $transacao = (string) $objeto_codigo_barras->getFullCode('');

        $this->empresa = (isset($dados['empresa']) ? (int) intval($dados['empresa'], 10) : 0);
        $this->cliente_fornecedor = (isset($dados['cliente_fornecedor']) ? (int) intval($dados['cliente_fornecedor'], 10) : 0);
        $this->conta_fornecedor = (isset($dados['conta_fornecedor']) ? (int) intval($dados['conta_fornecedor']) : 0);
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? strtoupper($dados['nome_conta']) : '');
        $this->descricao = (string) (isset($dados['descricao']) ? (string) strtoupper($dados['descricao']) : '');
        $this->valor_conta = (float) (isset($dados['valor_conta']) ? (float) floatval(str_replace(',', '.', $dados['valor_conta'])) : 0);
        $this->valor_pago = (float) (isset($dados['valor_pago']) ? (float) floatval(str_replace(',', '.', $dados['valor_pago'])) : 0);
        $this->valor_juro_desconto = (float) (isset($dados['valor_juro_desconto']) ? (float) floatval(str_replace(',', '.', $dados['valor_juro_desconto'])) : 0);
        $this->tipo_juro_desconto = (bool) (isset($dados['tipo_juro_desconto']) ? (bool) filter_var($dados['tipo_juro_desconto'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->tipo_conta = (bool) (isset($dados['tipo_conta']) ? (bool) filter_var($dados['tipo_conta'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : '');
        $this->data_vencimento = (isset($dados['data_vencimento']) ? model_date($dados['data_vencimento']) : '');
        $this->data_baixa = (isset($dados['data_baixa']) ? model_date($dados['data_baixa']) : '');
        $this->status_conta = (string) (isset($dados['status_conta']) ? (string) $dados['status_conta'] : 'AGUARDANDO');
        $this->comprovante = (string) (isset($dados['anexa_documentos']) ? (string) $dados['anexa_documentos'] : 'NAO');
        $this->boleto = (string) (isset($dados['boleto']) ? (string) $dados['boleto'] : 'NAO');
        $this->transacao = (string) (isset($dados['transacao']) ? (string) $dados['transacao'] : $transacao);
    }

    public function salvar_dados($dados = [])
    {
        $this->colocar_dados($dados);

        if ($this->codigo_conta_pagar_receber != 0) {
            return (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_conta_pagar_receber', '=', $this->codigo_conta_pagar_receber]]], (array) $this->montar_array());
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
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

        $conta = (array) $this->pesquisar((array) ['filtro' => (array) ['where' => [['codigo_conta_pagar_receber', '=', $this->codigo_conta_pagar_receber]]]]);

        if (empty($conta) == false) {
            $this->status_conta = (string) 'PAGO';
            $retorno_update = (bool) model_update((string) $this->tabela(), ['where' => [['codigo_conta_pagar_receber', '=', $this->codigo_conta_pagar_receber]]], (array) $this->montar_array());

            if ($retorno_update == true) {
                $objeto_movimentacao = new Movimentacao();

                $dados_movimentacao = (array) ['empresa' => $this->empresa, 'conta' => (int) intval($dados['codigo_conta_bancaria'], 10), 'valor_lancamento' => (float) $this->valor_pago];

                if ($this->tipo_conta == false) {
                    $dados_movimentacao['tipo_lancamento'] = (bool) false;
                    $dados_movimentacao['descricao'] = (string) 'Pagamento da conta ' . $this->nome_conta;
                } else {
                    $dados_movimentacao['tipo_lancamento'] = (bool) true;
                    $dados_movimentacao['descricao'] = (string) 'Recebimento da conta ' . $this->nome_conta;
                }

                $retorno_movimentacao = (bool) $objeto_movimentacao->salvar_dados($dados_movimentacao);

                return (bool) $retorno_movimentacao;
            } else {
                return (bool) false;
            }
        } else {
            return (bool) false;
        }

        // if (empty($conta) == false) {
        //     $array_movimentacao = (array) ['empresa' => (string) $dados['empresa'], 'conta' => (string) $dados['codigo_conta_bancaria'], 'valor_lancamento' => (float) $this->valor_pago];

        //     if ($this->tipo_conta == 'PAGAR') {
        //         $array_movimentacao['tipo_lancamento'] = (string) 'DEBITO';
        //         $array_movimentacao['descricao'] = (string) 'Pagamento da conta ' . $this->nome_conta;
        //     } else {
        //         $array_movimentacao['tipo_lancamento'] = (string) 'CREDITO';
        //         $array_movimentacao['descricao'] = (string) 'Recebimento da conta ' . $this->nome_conta;
        //     }

        //     $retorno_movimentacao = (bool) $objeto_movimentacao->salvar_dados($array_movimentacao);

        //     if ($retorno_movimentacao == true) {
        //         $retorno_contas_pagar_receber = (bool) model_update((string) $this->tabela(), ['_id', '===', $this->codigo_conta_pagar_receber], (array) ['valor_pago' => (float) $this->valor_pago, 'valor_juro_desconto' => (float) $this->valor_juro_desconto, 'tipo_juro_desconto' => (string) $this->tipo_juro_desconto, 'data_baixa' => $this->data_baixa, 'status_conta' => (string) 'PAGO']);

        //         if ($retorno_movimentacao == true && $retorno_contas_pagar_receber == true) {
        //             $dados_cliente_fornecedor = (array) ['codigo_usuario' => (string) $conta['cliente_fornecedor']];

        //             $objeto_usuario = new Usuario();
        //             $retorno_usuario_update = (bool) $objeto_usuario->update_status_usuario($dados_cliente_fornecedor);

        //             return (bool) true;
        //         } else {
        //             return (bool) false;
        //         }
        //     } else {
        //         return (bool) false;
        //     }
        // } else {
        //     return (bool) false;
        // }
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
                if (array_key_exists('cliente_fornecedor', $pesquisa) == false) {
                    $usuario = (array) $objeto_usuario->pesquisar((array) ['filtro' => (array) ['and' => (array) [(array) ['empresa', '===', $pesquisa['empresa']], (array) ['tipo_usuario', '===', (string) 'CLIENTE'], (array) ['nome_usuario', '===', (string) 'CLIENTE PADRAO']]]]);

                    if (empty($usuario) == false) {
                        $pesquisa['cliente_fornecedor'] = $usuario['_id'];
                    }
                }

                if (array_key_exists('conta_fornecedor', $pesquisa) == false) {
                    $conta = (array) $objeto_conta_fornecedor->pesquisar((array) ['filtro' => (array) ['fornecedor', '===', $pesquisa['cliente_fornecedor']]]);

                    if (empty($conta) == false) {
                        $pesquisa['conta_fornecedor'] = $conta['_id'];
                    }
                }

                if (array_key_exists('transacao', $pesquisa) == false) {
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
     * @return array - Retorna um array contendo o relatório de contas a pagar mensal, onde cada elemento do array representa um grupo de contas e inclui a quantidade total de contas e o valor total para esse grupo.
     */
    public function relatorio_contas_pagar_mensal($codigo_empresa, $data)
    {
        $sql = "SELECT
        EXTRACT(YEAR FROM data_vencimento) AS ano,
    
        EXTRACT(MONTH FROM data_vencimento) AS mes,
    
        TO_CHAR(data_vencimento, 'MM/YYYY') AS mes_ano,
    
        CASE
            WHEN tipo_conta = TRUE THEN 'RECEBER'
            ELSE 'PAGAR'
        END AS tipo_conta,
    
        status_conta,
    
        COUNT(*) AS quantidade,
    
        SUM(
            CASE
                WHEN status_conta IN ('PAGO', 'CANCELADO')
                    THEN COALESCE(valor_pago, 0)
                ELSE
                    COALESCE(valor_conta, 0)
            END
        ) AS valor_total
    
    FROM conta_pagar_receber
    
    WHERE codigo_empresa = :empresa
      AND data_vencimento >= MAKE_TIMESTAMP(:ano, 1, 1, 0, 0, 0)
    
    GROUP BY
        EXTRACT(YEAR FROM data_vencimento),
        EXTRACT(MONTH FROM data_vencimento),
        TO_CHAR(data_vencimento, 'MM/YYYY'),
        tipo_conta,
        status_conta
    
    ORDER BY
        ano,
        mes,
        tipo_conta,
        status_conta;";

        $dados = (array) model_query($sql, ['empresa' => $codigo_empresa, 'ano' => $data]);
        return (array) $dados;
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

        // file_put_contents('teste.json', json_encode($dados));

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
        $sql = (string) "SELECT
        CASE
            WHEN tipo_conta = TRUE THEN 'RECEBER'
            ELSE 'PAGAR'
        END AS tipo_conta,
    
        status_conta,
    
        COUNT(*) AS quantidade,
    
        SUM(
            CASE
                WHEN status_conta NOT IN ('PAGO', 'CANCELADO')
                THEN COALESCE(valor_conta, 0)
                ELSE 0
            END
        ) AS total_previsto,
    
        SUM(
            CASE
                WHEN status_conta IN ('PAGO', 'CANCELADO')
                THEN COALESCE(valor_pago, 0)
                ELSE 0
            END
        ) AS total_baixado
    
    FROM conta_pagar_receber
    
    WHERE codigo_empresa = :empresa
  AND data_vencimento BETWEEN :inicio AND :fim
    
    GROUP BY
        tipo_conta,
        status_conta
    
    ORDER BY
        tipo_conta,
        status_conta;";

        $dados = (array) model_query($sql, ['empresa' => $codigo_empresa, 'inicio' => model_date($data_inicial, '00:00:00'), 'fim' => model_date($data_final, '23:59:59')]);
        return (array) $dados;
    }

    /**
     * Função que retorna as contas em aberto independentemente da data
     * @param mixed $codigo_empresa
     * @return array
     */
    public function relatorio_contas_futuras($codigo_empresa)
    {
        $sql = (string) "SELECT
        CASE
            WHEN tipo_conta = TRUE THEN 'RECEBER'
            ELSE 'PAGAR'
        END AS tipo_conta,
    
        status_conta,
    
        COUNT(*) AS quantidade,
    
        SUM(COALESCE(valor_conta, 0)) AS total_previsto
    
    FROM conta_pagar_receber
    
    WHERE codigo_empresa = :empresa
      AND status_conta NOT IN ('PAGO', 'CANCELADO')
    
    GROUP BY
        tipo_conta,
        status_conta
    
    ORDER BY
        tipo_conta,
        status_conta;";

        $dados = (array) model_query($sql, ['empresa' => $codigo_empresa]);
        return (array) $dados;
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
        // $this->tipo_conta = (string) (isset($dados['tipo_conta']) ? (string) $dados['tipo_conta'] : 'TODOS');

        $this->data_cadastro = (string) (isset($dados['data_cadastro_inicio']) ? (string) $dados['data_cadastro_inicio'] : '');
        $this->data_cadastro_fim = (string) (isset($dados['data_cadastro_fim']) ? (string) $dados['data_cadastro_fim'] : '');
        $this->data_vencimento = (string) (isset($dados['data_vencimento_inicio']) ? (string) $dados['data_vencimento_inicio'] : '');
        $this->data_vencimento_fim = (string) (isset($dados['data_vencimento_fim']) ? (string) $dados['data_vencimento_fim'] : '');
        $this->data_baixa = (string) (isset($dados['data_baixa_inicio']) ? (string) $dados['data_baixa_inicio'] : '');
        $this->data_baixa_fim = (string) (isset($dados['data_baixa_fim']) ? (string) $dados['data_baixa_fim'] : '');

        $this->empresa = (int) (isset($dados['empresa']) ? (int) intval($dados['empresa'], 10) : 0);
        $this->cliente_fornecedor = (int) (isset($dados['cliente_fornecedor']) ? (int) intval($dados['cliente_fornecedor'], 10) : 0);

        $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) [['data_vencimento', 'ASC']], 'limite' => (int) 0];
        $filtro_montando = (array) [];

        if ($this->nome_conta != '') {
            array_push($filtro_montando, ['nome_conta', 'LIKE', (string) $this->nome_conta]);
        }

        if ($this->descricao != '') {
            array_push($filtro_montando, ['descricao', 'LIKE', (string) $this->descricao]);
        }

        // if ($this->tipo_conta != 'TODOS') {
        //     array_push($filtro_montando, ['tipo_conta', '=', (string) $this->tipo_conta]);
        // }

        if ($this->status_conta != 'TODOS') {
            array_push($filtro_montando, ['status_conta', '=', (string) $this->status_conta]);
        }

        if ($this->empresa != 0) {
            array_push($filtro_montando, ['codigo_empresa', '=', $this->empresa]);
        }

        if ($this->data_cadastro != '') {
            array_push($filtro_montando, ['data_cadastro', '>=', model_date($this->data_cadastro, '00:00:00')]);
        }

        if ($this->data_cadastro_fim != '') {
            array_push($filtro_montando, ['data_cadastro', '<=', model_date($this->data_cadastro_fim, '23:59:59')]);
        }

        if ($this->data_vencimento != '') {
            array_push($filtro_montando, ['data_vencimento', '>=', model_date($this->data_vencimento, '00:00:00')]);
        }

        if ($this->data_vencimento_fim != '') {
            array_push($filtro_montando, ['data_vencimento', '<=', model_date($this->data_vencimento_fim, '23:59:59')]);
        }

        if ($this->data_baixa != '') {
            array_push($filtro_montando, ['data_baixa', '>=', model_date($this->data_baixa, '00:00:00')]);
        }

        if ($this->data_baixa_fim != '') {
            array_push($filtro_montando, ['data_baixa', '<=', model_date($this->data_baixa_fim, '23:59:59')]);
        }

        if ($this->cliente_fornecedor != 0) {
            array_push($filtro_montando, ['codigo_usuario', '=', $this->cliente_fornecedor]);
        }

        $filtro['filtro'] = (array) ['where' => (array) $filtro_montando];

        $retorno_contas = (array) $this->pesquisar_todos($filtro);

        if (empty($retorno_contas) == false) {
            $objeto_cliente_fornecedor = new Usuario();
            $contas = (array) [];

            foreach ($retorno_contas as $contas_retorno) {
                $filtro_cliente_fornecedor = (array) [];

                $filtro_cliente_fornecedor = (array) ['filtro' => (array) ['where' => [['codigo_usuario', '=', $contas_retorno['codigo_usuario']]]]];

                $pessoa = (array) $objeto_cliente_fornecedor->pesquisar($filtro_cliente_fornecedor);

                if (empty($pessoa) == false) {
                    $contas_retorno['pessoa'] = (array) $pessoa;
                } else {
                    $contas_retorno['pessoa'] = (array) [];
                }

                array_push($contas, $contas_retorno);
            }

            return (array) $contas;
        } else {
            return (array) [];
        }
    }

    public function montar_array()
    {
        $dados = (array) [];

        if ($this->cliente_fornecedor != 0) {
            $dados['codigo_usuario'] = (int) $this->cliente_fornecedor;
        }

        if ($this->conta_fornecedor != 0) {
            $dados['codigo_conta_fornecedor'] = (int) $this->conta_fornecedor;
        }

        if ($this->empresa != 0) {
            $dados['codigo_empresa'] = (int) $this->empresa;
        }

        if ($this->nome_conta != '') {
            $dados['nome_conta'] = (string) $this->nome_conta;
        }

        if ($this->descricao != '') {
            $dados['descricao'] = (string) $this->descricao;
        }

        if ($this->valor_conta != 0) {
            $dados['valor_conta'] = (string) formatar_numero($this->valor_conta, 2, '.');
        }

        if ($this->valor_pago != 0) {
            $dados['valor_pago'] = (string) formatar_numero($this->valor_pago, 2, '.');
        }

        if ($this->valor_juro_desconto != 0) {
            $dados['valor_juro_desconto'] = (string) formatar_numero($this->valor_juro_desconto, 2, '.');
        }

        if ($this->data_cadastro != '') {
            $dados['data_cadastro'] = (string) $this->data_cadastro;
        }

        if ($this->data_vencimento != '') {
            $dados['data_vencimento'] = (string) $this->data_vencimento;
        }

        if ($this->status_conta != '') {
            $dados['status_conta'] = (string) $this->status_conta;

            if ($this->status_conta != 'AGUARDANDO') {
                if ($this->data_baixa != '') {
                    $dados['data_baixa'] = (string) $this->data_baixa;
                }
            }
        }

        if ($this->comprovante != '') {
            $dados['comprovante'] = (string) $this->comprovante;
        }

        if ($this->boleto != '') {
            $dados['boleto'] = (string) $this->boleto;
        }

        if ($this->transacao != '') {
            $dados['transacao'] = (string) $this->transacao;
        }

        $dados['tipo_juro_desconto'] = (bool) $this->tipo_juro_desconto;
        $dados['tipo_conta'] = (bool) $this->tipo_conta;

        return (array) $dados;
    }
}
?>