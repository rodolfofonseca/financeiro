<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/Contas.php';
require_once 'modelos/Usuario.php';
require_once 'modelos/ContasFornecedores.php';
require_once 'modelos/Produtos.php';

class ContasContabeis implements InterfaceModelo
{
    private int $codigo_conta_contabil;
    private int $empresa;
    private int $codigo_local;
    private string $local_conta_id;
    private int $grau_conta;
    private string $conta_contabil;
    private string $local_conta;
    private bool $tipo_conta;
    private string $nome_conta;
    private string $data_cadastro;
    private bool $conta_tipo;
    
    public function tabela()
    {
        return (string) 'conta_contabil';
    }

    public function modelo()
    {
        return (array) [];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_conta_contabil', $dados) == true) {
            if ($dados['codigo_conta_contabil'] != 0) {
                $this->codigo_conta_contabil = (int) intval($dados['codigo_conta_contabil'], 10);
            } else {
                $this->codigo_conta_contabil = 0;
            }
        } else {
            $this->codigo_conta_contabil = 0;
        }

        $this->empresa = (int) (isset($dados['empresa']) ? (int) intval($dados['empresa'], 10) : 0);
        $this->codigo_local = (int) (isset($dados['codigo_local']) ? (int) intval($dados['codigo_local'], 10) : 0);
        $this->local_conta_id = (string) (isset($dados['local_conta_id']) ? (string) $dados['local_conta_id'] : '');
        $this->grau_conta = (int) (isset($dados['grau_conta']) ? (int) intval($dados['grau_conta'], 10) : 0);
        $this->conta_contabil = (string) (isset($dados['conta_contabil']) ? (string) $dados['conta_contabil'] : '');
        $this->local_conta = (string) (isset($dados['local_conta']) ? (string) $dados['local_conta'] : '');
        $this->tipo_conta = (bool) (isset($dados['tipo_conta']) ? (bool) $dados['tipo_conta'] : false);
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? (string) strtoupper($dados['nome_conta']) : '');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
        $this->conta_tipo = (bool) (isset($dados['conta_tipo']) ? (bool) $dados['conta_tipo'] : false);
    }

    public function salvar_dados($dados)
    {
        // $this->colocar_dados($dados);

        // if ($this->codigo_conta_contabil != null) {
        //     return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_conta_contabil], (array) ['empresa' => $this->empresa, 'id_local' => $this->codigo_local, 'local_conta_id' => (string) $this->local_conta_id, 'grau_conta' => (int) $this->grau_conta, 'conta_contabil' => (string) $this->conta_contabil, 'local_conta' => (string) $this->local_conta, 'tipo_conta' => (bool) $this->tipo_conta, 'nome_conta' => (string) $this->nome_conta, 'data_cadastro' => $this->data_cadastro, 'conta_tipo' => (bool) $this->conta_tipo]);
        // } else {
        //     return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'id_local' => $this->codigo_local, 'local_conta_id' => (string) $this->local_conta_id, 'grau_conta' => (int) $this->grau_conta, 'conta_contabil' => (string) $this->conta_contabil, 'local_conta' => (string) $this->local_conta, 'tipo_conta' => (bool) $this->tipo_conta, 'nome_conta' => (string) $this->nome_conta, 'data_cadastro' => $this->data_cadastro, 'conta_tipo' => (bool) $this->conta_tipo]);
        // }

        return (bool) false;
    }

    public function pesquisar($filtro)
    {
        // return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
        return (array) [];
    }

    public function pesquisar_todos($filtro)
    {
        // return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);

        return (array) [];
    }

    /** 
     * Função responsável por validar o tipo de conta e pesquisar as informações correspondentes
     */
    public function pesquisar_informacoes_conta($dados)
    {
        // $this->colocar_dados($dados);

        // $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) [], 'limite' => (int) 0];
        // $filtro_montando = (array) [];
        // $retorno_final = (array) [];
        // $retorno_temporario = (array) [];
        // $status = (bool) false;

        // array_push($filtro_montando, ['empresa', '===', $this->empresa]);

        // if ($this->local_conta_id == 'ATIVO_CIRCULANTE_CAIXA') {
        //     array_push($filtro_montando, ['status', '===', (string) 'ATIVO']);

        //     $objeto_contas = new Contas();
        //     $filtro['filtro'] = (array) ['and' => (array) $filtro_montando];
        //     $filtro['ordenacao'] = (array) ['nome_conta' => (bool) true];

        //     $retorno_temporario = (array) $objeto_contas->pesquisar_todos($filtro);

        //     if (empty($retorno_temporario) == false) {
        //         foreach ($retorno_temporario as $contas) {
        //             array_push($retorno_final, $this->montar_array($contas['_id'], $contas['nome_conta']));
        //             $status = (bool) true;
        //         }
        //     }
        // } else if ($this->local_conta_id == 'ATIVO_NAO_CIRCULANTE_CLIENTE' || $this->local_conta_id == 'ATIVO_CIRCULANTE_CLIENTE') {
        //     array_push($filtro_montando, ['tipo_usuario', '===', 'CLIENTE']);

        //     $objeto_usuario = new Usuario();
        //     $filtro['filtro'] = (array) ['and' => (array) $filtro_montando];
        //     $filtro['ordenacao'] = (array) ['nome_usuario' => (bool) true];

        //     $retorno_temporario = (array) $objeto_usuario->pesquisar_todos($filtro);

        //     if (empty($retorno_temporario) == false) {
        //         foreach ($retorno_temporario as $usuario) {
        //             array_push($retorno_final, $this->montar_array($usuario['_id'], $usuario['nome_usuario']));
        //             $status = (bool) true;
        //         }
        //     }
        // } else if ($this->local_conta_id == 'PASSIVO_NAO_CIRCULANTE_FORNECEDOR' || $this->local_conta_id == 'PASSIVO_CIRCULANTE_FORNECEDOR') {
        //     array_push($filtro_montando, ['tipo_usuario', '===', 'FORNECEDOR']);

        //     $objeto_usuario = new Usuario();
        //     $filtro['filtro'] = (array) ['and' => (array) $filtro_montando];
        //     $filtro['ordenacao'] = (array) ['nome_usuario' => (bool) true];

        //     $retorno_temporario = (array) $objeto_usuario->pesquisar_todos($filtro);

        //     if (empty($retorno_temporario) == false) {
        //         foreach ($retorno_temporario as $usuario) {
        //             array_push($retorno_final, $this->montar_array($usuario['_id'], $usuario['nome_usuario']));
        //             $status = (bool) true;
        //         }
        //     }
        // } else if ($this->local_conta_id == 'PASSIVO_CIRCULANTE_CONTAS' || $this->local_conta_id == 'DESPESAS') {
        //     $objeto_contas_fornecedores = new ContasFornecedores();
        //     array_push($filtro_montando, ['status_conta', '===', (bool) true]);

        //     $retorno_temporario = (array) $objeto_contas_fornecedores->pesquisar_todos((array) ['filtro' => (array) ['and' => $filtro_montando], 'ordenacao' => ['nome_conta' => (bool) true], 'limite' => (int) 0]);

        //     if (empty($retorno_temporario) == false) {
        //         foreach ($retorno_temporario as $contas_fornecedores) {
        //             array_push($retorno_final, $this->montar_array($contas_fornecedores['_id'], $contas_fornecedores['nome_conta']));
        //             $status = (bool) true;
        //         }
        //     }
        // } else if ($this->local_conta_id == 'ATIVO_CIRCULANTE_ESTOQUE') {
        //     $objeto_produtos = new Produtos();
        //     array_push($filtro_montando, ['status', '===', (bool) true]);

        //     $retorno_temporario = (array) $objeto_produtos->pesquisar_todos((array) ['filtro' => (array) ['and' => $filtro_montando], 'ordenacao' => (array) ['nome_produto' => (bool) true], 'limite' => (int) 0]);

        //     if (empty($retorno_temporario) == false) {
        //         foreach ($retorno_temporario as $produtos) {
        //             array_push($retorno_final, $this->montar_array($produtos['_id'], $produtos['nome_produto']));
        //             $status = (bool) true;
        //         }
        //     }
        // } else if ($this->local_conta_id == 'PATRIMONIO_LIQUIDO' || $this->local_conta_id == 'SERVICOS' || $this->local_conta_id == 'CUSTOS' || $this->local_conta_id == 'RESULTADO') {
        //     $status = (bool) true;
        // }

        // return (array) ['status' => (bool) $status, 'dados' => $retorno_final];

        return (array) [];
    }

    /** 
     * Função responsável por excluir a conta contábil do sistema
     * @param array $dados - contendo as informações da conta que deseja deletar
     * @return bool - com o resultado da operação
     */
    function excluir_conta($dados)
    {
        // $this->colocar_dados($dados);
        // return (bool) model_delete((string) $this->tabela(), ['_id', '===', $this->codigo_conta_contabil]);
        return (bool) false;
    }

    public function montar_array()
    {
        return (array) [];
    }
}
