<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class ContasFornecedores implements InterfaceModelo
{
    private int $codigo_conta_fornecedor;
    private int $empresa;
    private int $fornecedor;
    private string $nome_conta;
    private string $descricao_conta;
    private bool $status_conta;
    private mixed $data_cadastro;

    public function tabela()
    {
        return (string) 'conta_fornecedor';
    }

    public function modelo()
    {
        return (array) [];
    }

    /**
     * Função responsável por colocar os dados no campos correspondentes fazendo a validação
     * @param array $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_conta_fornecedor', $dados) == true) {
            if ($dados['codigo_conta_fornecedor'] != 0) {
                $this->codigo_conta_fornecedor = (isset($dados['codigo_conta_fornecedor']) ? (int) intval($dados['codigo_conta_fornecedor'], 10) : 0);
            } else {
                $this->codigo_conta_fornecedor = 0;
            }
        } else {
            $this->codigo_conta_fornecedor = 0;
        }

        $this->empresa = (isset($dados['empresa']) ? (int) intval($dados['empresa']) : 0);
        $this->fornecedor = (isset($dados['fornecedor']) ? (int) intval($dados['fornecedor']) : 0);
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? (string) strtoupper($dados['nome_conta']) : '');
        $this->descricao_conta = (string) (isset($dados['descricao_conta']) ? (string) strtoupper($dados['descricao_conta']) : '');
        $this->status_conta = (bool) (isset($dados['status_conta']) ? (bool) filter_var($dados['status_conta'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
    }

    /**
     * Função ressponsável por salvar os dados no banco de dados
     * @param array $dados
     * @return bool
     */
    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_conta_fornecedor == 0) {
            return (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
        } else {
            return (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_conta_fornecedor', '=', $this->codigo_conta_fornecedor]]], (array) $this->montar_array());
        }
    }

    /**
     * Função responsável por pesquisar os dados de uma conta no banco de dados
     * @param array $filtro
     * @return array
     */
    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    /**
     * Função responsável por pesquisar todos os dados de contas no banco de dados
     * @param array $filtro
     * @return array
     */
    public function pesquisar_todos($filtro)
    {
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
    }

    /**
     * Função responsável por inativar as contas dos fornecedores inativos
     * @param mixed $dados
     * @return void
     */
    public function inativar_conta_fornecedor($dados)
    {
        $filtro = (array) [];
        $filtro_montando = (array) [];

        array_push($filtro_montando, ['codigo_empresa', '=', (int) intval($dados['empresa'], 10)]);
        array_push($filtro_montando, ['codigo_usuario', '=', (int) intval($dados['fornecedor'], 10)]);

        $retorno = (bool) model_update((string) $this->tabela(), (array) ['where' => (array) $filtro_montando], (array) ['status_conta' => (bool) $dados['status']]);

    }

    public function montar_array(){
        $dados = (array) [];

        if($this->empresa != 0){
            $dados['codigo_empresa'] = (int) $this->empresa;
        }

        if($this->fornecedor != 0){
            $dados['codigo_usuario'] = (int) $this->fornecedor;
        }

        if($this->nome_conta != ''){
            $dados['nome_conta'] = (string) $this->nome_conta;
        }

        if($this->descricao_conta != ''){
            $dados['descricao_conta'] = (string) $this->descricao_conta;
        }

        if($this->data_cadastro != ''){
            $dados['data_cadastro'] = (string) $this->data_cadastro;
        }

        $dados['status_conta'] = (bool) $this->status_conta;

        return (array) $dados;
    }
}
?>