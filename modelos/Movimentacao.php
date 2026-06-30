<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/Contas.php';

class Movimentacao implements InterfaceModelo
{
    private int $codigo_movimentacao;
    private int $conta;
    private int $conta_destino;
    private int $empresa;
    private bool $tipo_lancamento;
    private float $valor_lancamento;
    private string $data_lancamento;
    private string $descricao;
    private bool $comprovante;
    private int $transferencia;

    private string $data_inicio;
    private string $data_final;
    private string $pesquisar_conta;

    public function tabela()
    {
        return (string) 'movimentacao';
    }

    public function modelo()
    {
        return (array) [];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_movimentacao', $dados) == true) {
            if ($dados['codigo_movimentacao'] != 0) {
                $this->codigo_movimentacao = (int) intval($dados['codigo_movimentacao'], 10);
            } else {
                $this->codigo_movimentacao = 0;
            }
        } else {
            $this->codigo_movimentacao = 0;
        }

        $this->empresa = (int) (isset($dados['empresa']) ? (int) intval($dados['empresa'], 10) : 0);
        $this->conta = (int) (isset($dados['conta']) ? (int) intval($dados['conta'], 10) : 0);
        $this->valor_lancamento = (float) (isset($dados['valor_lancamento']) ? (float) doubleval(str_replace(',', '.', $dados['valor_lancamento'])) : 0);
        $this->data_lancamento = (isset($dados['data_lancamento']) ? model_date($dados['data_lancamento']) : model_date());
        $this->descricao = (string) (isset($dados['descricao']) ? (string) strtoupper($dados['descricao']) : '');
        $this->tipo_lancamento = (bool) (isset($dados['tipo_lancamento']) ? (bool) filter_var($dados['tipo_lancamento'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->conta_destino = (int) (isset($dados['conta_destino']) ? (int) intval($dados['conta_destino'], 10) : 0);
        $this->comprovante = (bool) (isset($dados['comprovante']) ? (bool) filter_var($dados['comprovante'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->transferencia = (int) (isset($dados['transferencia']) ? (int) intval($dados['transferencia'], 10) : (0));
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);
        $retorno_movimentacao = (bool) false;
        $resultado_conta = (float) 0;
        $objeto_conta = new Contas();

        if ($this->conta_destino == 0) {
            $retorno_movimentacao = (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());

            $retorno_conta = (array) $objeto_conta->pesquisar(['filtro' => ['where' => [['codigo_conta', '=', $this->conta]]]]);

            if (empty($retorno_conta) == false) {
                if ($this->tipo_lancamento == true) {
                    $resultado_conta = arredondar($this->valor_lancamento, '+', $retorno_conta['saldo_conta'], 2);
                } else {
                    $resultado_conta = arredondar($retorno_conta['saldo_conta'], '-', $this->valor_lancamento, 2);
                }

                if ($retorno_conta['saldo_conta'] == null) {
                    $retorno_conta['saldo_conta'] = (float) 0;
                }

                $retorno = $objeto_conta->salvar_dados((array) ['codigo_conta' => (int) $this->conta, 'saldo_conta' => (float) $resultado_conta, 'status' => (bool) true]);
            }
        } else {
            $conta_atual = (array) $objeto_conta->pesquisar((array) ['filtro' => (array) ['where' => (array) [['codigo_conta', '==', (int) $this->conta]]]]);
            $conta_destino = (array) $objeto_conta->pesquisar((array) ['filtro' => (array) ['where' => (array) [['codigo_conta', '==', (int) $this->conta_destino]]]]);

            if (empty($conta_atual) == false && empty($conta_destino) == false) {
                if ($conta_atual['saldo_conta'] == null) {
                    $conta_atual['saldo_conta'] = (float) 0;
                }

                if ($conta_destino['saldo_conta'] == null) {
                    $conta_destino['saldo_conta'] = (float) 0;
                }

                $resultado_conta = arredondar($conta_atual['saldo_conta'], '-', $this->valor_lancamento, 2);
                
                $array_update_conta_atual = (array) ['codigo_empresa' => (int) $this->empresa, 'codigo_conta' => (int) $conta_atual['codigo_conta'], 'valor_lancamento' => (float) $this->valor_lancamento, 'tipo_lancamento' => (bool) false, 'data_lancamento' => (string) model_date(), 'descricao' => (string) $this->descricao, 'comprovante' => (bool) false, 'transferencia' => (bool) true];
                $retorno = (bool) model_insert((string) $this->tabela(), (array) $array_update_conta_atual);

                $retorno_conta = (bool) $objeto_conta->salvar_dados((array) ['codigo_conta' => (int) $this->conta, 'saldo_conta' => (float) $resultado_conta, 'status' => (bool) true]);

                if ($retorno_conta == true) {
                    $resultado_conta = arredondar($this->valor_lancamento, '+', $conta_destino['saldo_conta'], 2);
                    $array_update_conta_destino = (array) ['codigo_empresa' => (int) $this->empresa, 'codigo_conta' => (int) $conta_destino['codigo_conta'], 'valor_lancamento' => (float) $this->valor_lancamento, 'tipo_lancamento' => (bool) true, 'data_lancamento' => (string) model_date(), 'descricao' => (string) $this->descricao, 'comprovante' => (bool) false, 'transferencia' => (bool) true];
                    $retorno = (bool) model_insert((string) $this->tabela(), (array) $array_update_conta_destino);

                    $retorno_movimentacao = (bool) $objeto_conta->salvar_dados((array) ['codigo_conta' => (int) $this->conta_destino, 'saldo_conta' => (float) $resultado_conta, 'status' => (bool) true]);
                } else {
                    $retorno_movimentacao = (bool) false;
                }
            } else {
                $retorno_movimentacao = (bool) false;
            }
        }

        return (bool) $retorno_movimentacao;
    }

    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    public function pesquisar_todos($filtro)
    {
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
    }

    /**
     * Função responsável por deletar a movimentação
     * @param mixed $filtro
     * @return bool
     */
    public function deletar_movimentacao($filtro)
    {
        return (bool) model_delete((string) $this->tabela(), (array) $filtro['filtro']);
    }

    /**
     * Função responsável por alterar o campo comprovante para SIM, indicando que o comprovante foi adicionado a movimentação.
     * @param int $codigo_movimentacao - Código da movimentação a ser alterada.
     * @return bool Retorna true se a movimentação for alterada com sucesso, ou false caso contrário.
     */
    public function alterar_comprovante($codigo_movimentacao)
    {
        $this->codigo_movimentacao = (int) intval($codigo_movimentacao, 10);
        $retorno_movimentacao = (bool) false;

        if ($this->codigo_movimentacao != 0) {
            $retorno_movimentacao = (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_movimentacao', '=', $this->codigo_movimentacao]]], (array) ['comprovante' => (bool) true]);
        }

        return (bool) $retorno_movimentacao;
    }

    /** 
     * Função responsável por pesquisar as movimentações da conta, montando o filtro, pesquisar e retornando apenas o resultado
     * @param array $dados - Variável $_REQUEST vinda do front
     * @return array $retorno - com as movimentações
     */
    public function pesquisar_movimentacoes($dados)
    {
        $conta = (int) (isset($dados['conta']) ? (int) $dados['conta'] : 0);
        $data_inicio = (isset($dados['data_inicio']) ? model_date($dados['data_inicio'], '00:00:00') : model_date());
        $data_final = (isset($dados['data_final']) ? model_date($dados['data_final'], '23:59:59') : model_date());
        $empresa = (int) (isset($dados['empresa']) ? (int) intval($dados['empresa']) : 0);
        $tipo_lancamento = (string) (isset($dados['tipo_lancamento']) ? (string) $dados['tipo_lancamento'] : 'TODOS');

        $filtro = (array) [];
        $filtro_montando = (array) [];

        if ($empresa > 0) {
            array_push($filtro_montando, ['codigo_empresa', '=', $empresa]);
        }

        if ($conta > 0) {
            array_push($filtro_montando, ['codigo_conta', '=', $conta]);
        }

        if($tipo_lancamento != 'TODOS'){
            if($tipo_lancamento == 'CREDITO'){
                array_push($filtro_montando, ['tipo_lancamento', '=', (bool) true]);
            }else if($tipo_lancamento == 'DEBITO'){
                array_push($filtro_montando, ['tipo_lancamento', '=', (bool) false]);
            }else{
                array_push($filtro_montando, ['transferencia', '=', (bool) true]);
            }
        }

        array_push($filtro_montando, ['data_lancamento', '>=', $data_inicio]);
        array_push($filtro_montando, ['data_lancamento', '<=', $data_final]);

        $objeto_movimentacao = new Movimentacao();

        $filtro = (array) ['filtro' => (array) ['where' => $filtro_montando], 'ordenacao' => [['data_lancamento', 'DESC']]];

        $retorno = (array) $objeto_movimentacao->pesquisar_todos((array) $filtro);

        if (empty($retorno) == false) {
            $objeto_conta = new Contas();

            $movimentacao_retorno = (array) [];

            foreach ($retorno as $movimentacao) {
                $retorno_conta = (array) $objeto_conta->pesquisar(['filtro' => (array) ['where' => [['codigo_conta', '=', $movimentacao['codigo_conta']]]]]);

                $movimentacao['nome_conta'] = (string) $retorno_conta['nome_conta'];

                array_push($movimentacao_retorno, $movimentacao);
            }

            return (array) $movimentacao_retorno;
        } else {
            return (array) $retorno;
        }
    }

    /**
     * Função responsável por deletar os dados da movimentação, quando passa de 5 anos.
     * @param mixed $dados - código da empresa que será realizada a exclusão dos dados.
     * @return bool
     */
    public function deletar_antigo($dados)
    {
        // $this->colocar_dados($dados);

        // $data_atual = (new DateTime())->modify('-5 years')->format('Y-m-d');

        // if ($this->empresa != '') {
        //     $filtro_montando = (array) [];
        //     array_push($filtro_montando, (array) ['empresa', '===', $this->empresa]);
        //     array_push($filtro_montando, (array) ['data_lancamento', '<=', model_date($data_atual)]);

        //     return (bool) model_delete((string) $this->tabela(), (array) ['and' => (array) $filtro_montando]);
        // } else {
        //     return (bool) false;
        // }

        return (bool) false;
    }

    public function montar_array()
    {
        $dados = (array) [];

        if ($this->empresa != 0) {
            $dados['codigo_empresa'] = (int) $this->empresa;
        }

        if ($this->conta != 0) {
            $dados['codigo_conta'] = (int) $this->conta;
        }

        if ($this->data_lancamento != '') {
            $dados['data_lancamento'] = (string) $this->data_lancamento;
        }

        if ($this->descricao != '') {
            $dados['descricao'] = (string) $this->descricao;
        }

        $dados['tipo_lancamento'] = (bool) $this->tipo_lancamento;
        $dados['comprovante'] = (bool) $this->comprovante;
        $dados['transferencia'] = (bool) $this->transferencia;
        $dados['valor_lancamento'] = (float) $this->valor_lancamento;

        return (array) $dados;
    }
}
