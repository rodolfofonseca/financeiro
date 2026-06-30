<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/ContasContabeis.php';

class Contas implements InterfaceModelo
{
    private int $codigo_conta;
    private int $empresa;
    private string $nome_conta;
    private string $descricao;
    private float $saldo_conta;
    private bool $status;
    private string $data_cadastro;

    private bool $modulo_contabil;

    public function tabela()
    {
        return (string) 'conta';
    }

    public function modelo()
    {
        return (array) [];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_conta', $dados) == true) {
            if ($dados['codigo_conta'] != 0) {
                $this->codigo_conta = (int) intval($dados['codigo_conta'], 10);
            } else {
                $this->codigo_conta = 0;
            }
        } else {
            $this->codigo_conta = 0;
        }

        $this->saldo_conta = (float) (isset($dados['saldo_conta']) ? (float) doubleval(str_replace(',', '.', $dados['saldo_conta'])) : 0);
        $this->empresa = (isset($dados['empresa']) ? (int) intval($dados['empresa'], 10) : 0);
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? (string) strtoupper($dados['nome_conta']) : '');
        $this->descricao = (string) (isset($dados['descricao']) ? (string) strtoupper($dados['descricao']) : '');
        $this->status = (bool) (isset($dados['status']) ? (bool) filter_var($dados['status'], FILTER_VALIDATE_BOOLEAN) : true);
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : '');
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_conta == 0) {
            return (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
        } else {
            return (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_conta', '=', $this->codigo_conta]]], (array) $this->montar_array());
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
        // return (bool) model_delete((string) $this->tabela(), ['_id', '===', model_id($codigo_conta)]);
        return (bool) false;
    }

    /** 
     * Função responsável por pesquisar as contas no banco de dados de acordo com o filtro, retornando a conta contábil, caso 
     * este habilitado esta opção nas configurações do sistema.
     * @param array $dados - Dados contendo as informações do filtro
     * @return array $retorno - Retorno com as contas cadastrados no sistema.
     */
    public function pesquisar_contas($dados)
    {
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? (string) strtoupper($dados['nome_conta']) : '');
        $this->status = (bool) (isset($dados['status']) ? (bool) filter_var($dados['status'], FILTER_VALIDATE_BOOLEAN) : true);
        $this->descricao = (string) (isset($dados['descricao']) ? (string) strtoupper($dados['descricao']) : '');
        $this->empresa = (int) (isset($dados['empresa']) ? (int) $dados['empresa'] : 0);
        $this->modulo_contabil = (bool) (isset($dados['modulo_contabil']) ? (bool) $dados['modulo_contabil'] : false);

        $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) [['nome_conta', 'ASC']], 'limite' => (int) 0];
        $filtro_montado = (array) [];
        $retorno = (array) [];
        $retorno_final = (array) [];

        if ($this->nome_conta != '') {
            array_push($filtro_montado, (array) ['nome_conta', 'LIKE', (string) $this->nome_conta]);
        }

            array_push($filtro_montado, (array) ['status', '=', (bool) $this->status]);

        if ($this->descricao != '') {
            array_push($filtro_montado, (array) ['descricao', 'LIKE', (string) $this->descricao]);
        }

        if ($this->empresa != 0) {
            array_push($filtro_montado, (array) ['codigo_empresa', '=', $this->empresa]);
        } else {
            return (array) [];
        }

        if (empty($filtro_montado) == false) {
            $filtro['filtro'] = (array) ['where' => (array) $filtro_montado];
        }
        // file_put_contents('json.json', json_encode(['dados' => $filtro]));
        return $retorno = (array) $this->pesquisar_todos($filtro);

        // if ($this->modulo_contabil == true) {
        //     $objeto_contas_contabeis = new ContasContabeis();
        //     if (empty($retorno) == false) {
        //         foreach ($retorno as $contas_bancarias) {
        //             $modulo_contabil = (array) ['grau_conta' => (int) 0, 'conta_contabil' => (string) ''];

        //             $filtro_contas_contabeis_montando = (array) [];
        //             $filtro_contas_contabeis = (array) ['filtro' => (array) [], 'ordenacao' => ['conta_contabil' => (bool) true], 'limite' => (int) 0];

        //             array_push($filtro_contas_contabeis_montando, (array) ['empresa', '===', model_id($this->empresa)]);
        //             array_push($filtro_contas_contabeis_montando, (array) ['local_conta_id', '===', (string) 'ATIVO_CIRCULANTE_CAIXA']);
        //             array_push($filtro_contas_contabeis_montando, (array) ['conta_tipo', '===', (bool) true]);
        //             array_push($filtro_contas_contabeis_montando, (array) ['id_local', '===', $contas_bancarias['_id']]);

        //             $filtro_contas_contabeis['filtro'] = (array) ['and' => (array) $filtro_contas_contabeis_montando];
        //             $retorno_conta_contabil = (array) $objeto_contas_contabeis->pesquisar($filtro_contas_contabeis);

        //             if (empty($retorno_conta_contabil) == false) {
        //                 $modulo_contabil['conta_contabil'] = (string) $retorno_conta_contabil['conta_contabil'];
        //                 $modulo_contabil['grau_conta'] = (int) $retorno_conta_contabil['grau_conta'];
        //             }

        //             $contas_bancarias['modulo_contabil'] = (array) $modulo_contabil;

        //             array_push($retorno_final, $contas_bancarias);
        //         }

        //         return (array) $retorno_final;
        //     } else {
        //         return (array) [];
        //     }
        // } else {
        //     return (array) $retorno;
        // }
    }

    public function montar_array(){
        $dados = (array) [];

        if($this->empresa != 0){
            $dados['codigo_empresa'] = (int) $this->empresa;
        }

        if($this->nome_conta != ''){
            $dados['nome_conta'] = (string) $this->nome_conta;
        }

        if($this->descricao != ''){
            $dados['descricao'] = (string) $this->descricao;
        }

        if($this->data_cadastro != ''){
            $dados['data_cadastro'] = (string) $this->data_cadastro;
        }

        $dados['status'] = (bool) $this->status;
        $dados['saldo_conta'] = (float) $this->saldo_conta;

        return (array) $dados;
    }
}
