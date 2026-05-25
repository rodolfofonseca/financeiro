<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/ContasContabeis.php';

class Contas implements InterfaceModelo
{
    private $codigo_conta;
    private $empresa;
    private $nome_conta;
    private $descricao;
    private $saldo_conta;
    private $status;
    private $data_cadastro;

    private $modulo_contabil;

    public function tabela()
    {
        return (string) 'contas';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'nome_conta' => (string) '', 'descricao' => (string) '', 'saldo_conta' => (float) 0, 'status' => (string) '', 'data_cadastro' => 'date'];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_conta', $dados) == true) {
            if ($dados['codigo_conta'] != '') {
                $this->codigo_conta = model_id($dados['codigo_conta']);
            } else {
                $this->codigo_conta = null;
            }
        } else {
            $this->codigo_conta = null;
        }

        $this->saldo_conta = (float) (isset($dados['saldo_conta']) ? (float) doubleval(str_replace(',', '.', $dados['saldo_conta'])) : 0);
        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? (string) strtoupper($dados['nome_conta']) : '');
        $this->descricao = (string) (isset($dados['descricao']) ? (string) strtoupper($dados['descricao']) : '');
        $this->status = (string) (isset($dados['status']) ? (string) $dados['status'] : 'ATIVO');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_conta == null) {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'nome_conta' => (string) $this->nome_conta, 'descricao' => (string) $this->descricao, 'saldo_conta' => (float) $this->saldo_conta, 'status' => (string) $this->status, 'data_cadastro' => $this->data_cadastro]);
        } else {
            return (bool) model_update((string) $this->tabela(), ['_id', '===', $this->codigo_conta], (array) ['nome_conta' => (string) $this->nome_conta, 'descricao' => (string) $this->descricao, 'saldo_conta' => (float) $this->saldo_conta, 'status' => (string) $this->status, 'data_cadastro' => $this->data_cadastro]);
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

    public function deletar_conta($codigo_conta)
    {
        return (bool) model_delete((string) $this->tabela(), ['_id', '===', model_id($codigo_conta)]);
    }

    /** 
     * Função responsável por pesquisar as contas no banco de dados de acordo com o filtro, retornando a conta contábil, caso 
     * este habilitado esta opção nas configurações do sistema.
     * @param array $dados - Dados contendo as informações do filtro
     * @param array $retorno - Retorno com as contas cadastrados no sistema.
    */
    public function pesquisar_contas($dados)
    {
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? (string) strtoupper($dados['nome_conta']) : '');
        $this->status = (string) (isset($dados['status']) ? (string) strtoupper($dados['status']) : 'TODOS');
        $this->descricao = (string) (isset($dados['descricao']) ? (string) strtoupper($dados['descricao']) : '');
        $this->empresa = (string) (isset($dados['empresa']) ? (string) $dados['empresa'] : '');
        $this->modulo_contabil = (bool) (isset($dados['modulo_contabil']) ? (bool) $dados['modulo_contabil'] : false);

        $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['nome_conta' => (bool) true], 'limite' => (int) 0];
        $filtro_montado = (array) [];
        $retorno = (array) [];
        $retorno_final = (array) [];

        if ($this->nome_conta != '') {
            array_push($filtro_montado, (array) ['nome_conta', '=', (string) $this->nome_conta]);
        }

        if ($this->status != 'TODOS') {
            array_push($filtro_montado, (array) ['status', '===', (string) $this->status]);
        }

        if ($this->descricao != '') {
            array_push($filtro_montado, (array) ['descricao', '=', (string) $this->descricao]);
        }

        if ($this->empresa != '') {
            array_push($filtro_montado, (array) ['empresa', '===', model_id($this->empresa)]);
        } else {
            return (array) [];
        }

        if (empty($filtro_montado) == false) {
            $filtro['filtro'] = (array) ['and' => (array) $filtro_montado];
        }

        $retorno = (array) $this->pesquisar_todos($filtro);

        if ($this->modulo_contabil == true) {
            $objeto_contas_contabeis = new ContasContabeis();
            if (empty($retorno) == false) {
                foreach ($retorno as $contas_bancarias) {
                    $modulo_contabil = (array) ['grau_conta' => (int) 0, 'conta_contabil' => (string) ''];

                    $filtro_contas_contabeis_montando = (array) [];
                    $filtro_contas_contabeis = (array) ['filtro' => (array) [], 'ordenacao' => ['conta_contabil' => (bool) true], 'limite' => (int) 0];

                    array_push($filtro_contas_contabeis_montando, (array) ['empresa', '===', model_id($this->empresa)]);
                    array_push($filtro_contas_contabeis_montando, (array) ['local_conta_id', '===', (string) 'ATIVO_CIRCULANTE_CAIXA']);
                    array_push($filtro_contas_contabeis_montando, (array) ['conta_tipo', '===', (bool) true]);
                    array_push($filtro_contas_contabeis_montando, (array) ['id_local', '===', $contas_bancarias['_id']]);

                    $filtro_contas_contabeis['filtro'] = (array) ['and' => (array) $filtro_contas_contabeis_montando];
                    $retorno_conta_contabil = (array) $objeto_contas_contabeis->pesquisar($filtro_contas_contabeis);

                    if (empty($retorno_conta_contabil) == false) {
                        $modulo_contabil['conta_contabil'] = (string) $retorno_conta_contabil['conta_contabil'];
                        $modulo_contabil['grau_conta'] = (int) $retorno_conta_contabil['grau_conta'];
                    }

                    $contas_bancarias['modulo_contabil'] = (array) $modulo_contabil;

                    array_push($retorno_final, $contas_bancarias);
                }

                return (array) $retorno_final;
            } else {
                return (array) [];
            }
        } else {
            return (array) $retorno;
        }
    }
}
