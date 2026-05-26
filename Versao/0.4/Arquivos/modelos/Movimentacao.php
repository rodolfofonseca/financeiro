<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/Contas.php';

class Movimentacao implements InterfaceModelo
{
    private $codigo_movimentacao;
    private $conta;
    private $conta_destino;
    private $empresa;
    private $tipo_lancamento;
    private $valor_lancamento;
    private $data_lancamento;
    private $descricao;
    private $comprovante;

    private $data_inicio;
    private $data_final;
    private $pesquisar_conta;

    public function tabela()
    {
        return (string) 'movimentacao';
    }

    public function modelo()
    {
        return (array) ['conta' => 'objectId', 'empresa' => 'objectId', 'tipo_lancamento' => (string) '', 'valor_lancamento' => (float) 0, 'data_lancamento' => 'date', 'descricao' => (string) '', 'comprovante' => 'bool'];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_movimentacao', $dados) == true) {
            if ($dados['codigo_movimentacao'] != '') {
                $this->codigo_movimentacao = model_id($dados['codigo_movimentacao']);
            } else {
                $this->codigo_movimentacao = null;
            }
        } else {
            $this->codigo_movimentacao = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->conta = (isset($dados['conta']) ? model_id($dados['conta']) : '');
        $this->valor_lancamento = (float) (isset($dados['valor_lancamento']) ? (float) doubleval(str_replace(',', '.', $dados['valor_lancamento'])) : 0);
        $this->data_lancamento = (isset($dados['data_lancamento']) ? model_date($dados['data_lancamento']) : model_date());
        $this->descricao = (string) (isset($dados['descricao']) ? (string) strtoupper($dados['descricao']) : '');
        $this->tipo_lancamento = (string) (isset($dados['tipo_lancamento']) ? (string) $dados['tipo_lancamento'] : 'CREDITO');
        $this->conta_destino = (isset($dados['conta_destino']) ? model_id($dados['conta_destino']) : '');
        $this->comprovante = (bool) (isset($dados['comprovante']) ? (bool) $dados['comprovante'] : false);
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);
        $retorno_movimentacao = (bool) false;
        $resultado_conta = (float) 0;

        if ($this->tipo_lancamento == 'CREDITO' || $this->tipo_lancamento == 'DEBITO') {
            if ($this->codigo_movimentacao == null) {
                $retorno_movimentacao = (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'conta' => $this->conta, 'tipo_lancamento' => (string) $this->tipo_lancamento, 'valor_lancamento' => (float) $this->valor_lancamento, 'descricao' => (string) $this->descricao, 'data_lancamento' => $this->data_lancamento, 'comprovante' => (bool) $this->comprovante]);
            } else {
                $retorno_movimentacao = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_movimentacao], (array) ['empresa' => $this->empresa, 'conta' => $this->conta, 'tipo_lancamento' => (string) $this->tipo_lancamento, 'valor_lancamento' => (float) $this->valor_lancamento, 'descricao' => (string) $this->descricao, 'data_lancamento' => $this->data_lancamento, 'comprovante' => (bool) $this->comprovante]);
            }

            if ($retorno_movimentacao == true) {
                $objeto_conta = new Contas();
                $retorno_conta = (array) $objeto_conta->pesquisar((array) ['filtro' => (array) ['_id', '===', $this->conta]]);

                if (empty($retorno_conta) == false) {

                    if ($this->tipo_lancamento == 'CREDITO') {
                        $resultado_conta = (float) arredondar($retorno_conta['saldo_conta'], '+', $this->valor_lancamento);
                    } else {
                        $resultado_conta = (float) arredondar($retorno_conta['saldo_conta'], '-', $this->valor_lancamento);
                    }

                    $retorno_conta['saldo_conta'] = (float) $resultado_conta;
                    $retorno_conta['codigo_conta'] = $retorno_conta['_id'];

                    return (bool) $objeto_conta->salvar_dados((array) $retorno_conta);
                } else {
                    return (bool) false;
                }
            } else {
                return (bool) false;
            }
        } else if ($this->tipo_lancamento == 'TRANSFERENCIA') {
            $objeto_conta = new Contas();
            $retorno_conta = (array) $objeto_conta->pesquisar((array) ['filtro' => (array) ['_id', '===', $this->conta]]);

            $retorno_movimentacao = (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'conta' => $this->conta, 'tipo_lancamento' => (string) 'TRANSFERENCIA_DEBITO', 'valor_lancamento' => (float) $this->valor_lancamento, 'descricao' => (string) $this->descricao, 'data_lancamento' => $this->data_lancamento, 'comprovante' => (bool) $this->comprovante]);
            $retorno_movimentacao = (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'conta' => $this->conta_destino, 'tipo_lancamento' => (string) 'TRANSFERENCIA_CREDITO', 'valor_lancamento' => (float) $this->valor_lancamento, 'descricao' => (string) $this->descricao, 'data_lancamento' => $this->data_lancamento, 'comprovante' => (bool) $this->comprovante]);

            $resultado_conta = (float) arredondar($retorno_conta['saldo_conta'], '-', $this->valor_lancamento);

            $retorno_conta['saldo_conta'] = (float) $resultado_conta;
            $retorno_conta['codigo_conta'] = $retorno_conta['_id'];
            $retorno_conta_boleano = (bool) $objeto_conta->salvar_dados((array) $retorno_conta);

            $resultado_conta = (float) 0;
            $retorno_conta = (array) $objeto_conta->pesquisar((array) ['filtro' => (array) ['_id', '===', $this->conta_destino]]);
            $resultado_conta = (float) arredondar($retorno_conta['saldo_conta'], '+', $this->valor_lancamento);

            $retorno_conta['saldo_conta'] = (float) $resultado_conta;
            $retorno_conta['codigo_conta'] = $retorno_conta['_id'];
            $retorno_conta_boleano = (bool) $objeto_conta->salvar_dados((array) $retorno_conta);

            return (bool) true;
        } else {
            return (bool) false;
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

    public function deletar_movimentacao($filtro)
    {
        return (bool) model_delete((string) $this->tabela(), (array) $filtro['filtro']);
    }

    /**
     * Função responsável por alterar o campo comprovante para SIM, indicando que o comprovante foi adicionado a movimentação.
     * @param (string) $codigo_movimentacao - Código da movimentação a ser alterada.
     * @return (bool) Retorna true se a movimentação for alterada com sucesso, ou false caso contrário.
     */
    public function alterar_comprovante($codigo_movimentacao)
    {
        $this->codigo_movimentacao = model_id($codigo_movimentacao);
        $retorno_movimentacao = (bool) false;

        if ($this->codigo_movimentacao != null) {
            $retorno_movimentacao = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_movimentacao], (array) ['comprovante' => (bool) true]);
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
        $data = new DateTime();
        $objeto_conta = new Contas();

        $this->conta = (string) (isset($dados['conta']) ? (string) $dados['conta'] : 'TODOS');
        $this->tipo_lancamento = (string) (isset($dados['tipo_lancamento']) ? (string) $dados['tipo_lancamento'] : 'TODOS');
        $this->data_inicio = (isset($dados['data_inicio']) ? model_date($dados['data_inicio'], '00:00:00') : model_date($data->format('Y-m-01'), '00:00:00'));
        $this->data_final = (isset($dados['data_final']) ? model_date($dados['data_final'], '23:59:59') : model_date($data->format('Y-m-t'), '23:59:59'));
        $this->empresa = (isset($dados['empresa']) ? (string) $dados['empresa'] : '');
        $this->pesquisar_conta = (bool) (isset($dados['pesquisar_conta']) ? (bool) $dados['pesquisar_conta']:true);

        $retorno_validacao = (array) [];
        $filtro = (array) [];
        $filtro_pesquisa = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['_id' => (bool) false], 'limite' => (int) 0];
        $retorno = (array)[];

        array_push($filtro,(array) ['data_lancamento', '>=', $this->data_inicio]);
        array_push($filtro,(array) ['data_lancamento', '<=', $this->data_final]);

        if($this->conta != 'TODOS'){
            array_push($filtro, (array) ['conta', '===', model_id($this->conta)]);
        }

        if($this->tipo_lancamento != 'TODOS'){
            array_push($filtro, (array) ['tipo_lancamento', '===', (string) $this->tipo_lancamento]);
        }

        if($this->empresa != ''){
            array_push($filtro, (array) ['empresa', '===', model_id($this->empresa)]);
        }else{
            return (array) [];
        }

        $filtro_pesquisa['filtro'] = (array) ['and' => (array) $filtro];

        $retorno_validacao = (array) $this->pesquisar_todos($filtro_pesquisa);

        if(empty($retorno_validacao) == false){
            if($this->pesquisar_conta == true){
                $objeto_conta = new Contas();

                foreach($retorno_validacao as $movimentacao){
                    $retorno_temporario = (array) ['nome_conta' => (string) ''];
                    $retorno_conta = (array) $objeto_conta->pesquisar((array) ['filtro' => (array) ['_id', '===', $movimentacao['conta']]]);

                    $retorno_temporario = (array) $movimentacao;

                    if(empty($retorno_conta) == false){
                        $retorno_temporario['nome_conta'] = (string)  $retorno_conta['nome_conta'];
                    }

                    array_push($retorno, $retorno_temporario);
                }

                return (array) $retorno;
            }else{
                return (array) $retorno_validacao;
            }
        }else{
            return (array) [];
        }
    }

    /**
     * Função responsável por deletar os dados da movimentação, quando passa de 5 anos.
     * @param mixed $dados - código da empresa que será realizada a exclusão dos dados.
     * @return bool
     */
    public function deletar_antigo($dados){
        $this->colocar_dados($dados);
        
        $data_atual = (new DateTime())->modify('-5 years')->format('Y-m-d');

        if($this->empresa != ''){
            $filtro_montando = (array) [];
            array_push($filtro_montando, (array) ['empresa', '===', $this->empresa]);
            array_push($filtro_montando, (array) ['data_lancamento', '<=', model_date($data_atual)]);

            return (bool) model_delete((string) $this->tabela(), (array) ['and' => (array) $filtro_montando]);
        }else{
            return (bool) false;
        }
    }
}
