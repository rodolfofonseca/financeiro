<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class ContasFornecedores implements InterfaceModelo{
    private mixed $codigo_conta_fornecedor;
    private mixed $empresa;
    private mixed $fornecedor;
    private string $nome_conta;
    private string $descricao_conta;
    private bool $status_conta;
    private mixed $data_cadastro;    

    public function tabela()
    {
        return (string) 'contas_fornecedores';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'fornecedor' => 'objectId', 'nome_conta' => (string) '', 'descricao_conta' => (string) '', 'status_conta' => 'bool', 'data_cadastro' => 'date'];
    }

    /**
     * Função responsável por colocar os dados no campos correspondentes fazendo a validação
     * @param array $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if(array_key_exists('codigo_conta_fornecedor', $dados) == true){
            if($dados['codigo_conta_fornecedor'] != ''){
                $this->codigo_conta_fornecedor = (isset($dados['codigo_conta_fornecedor']) ? model_id($dados['codigo_conta_fornecedor']):'');
            }else{
                $this->codigo_conta_fornecedor = null;
            }
        }else{
            $this->codigo_conta_fornecedor = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']):'');
        $this->fornecedor = (isset($dados['fornecedor']) ? model_id($dados['fornecedor']):'');
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? (string) strtoupper($dados['nome_conta']):'');
        $this->descricao_conta = (string) (isset($dados['descricao_conta']) ? (string) strtoupper($dados['descricao_conta']):'');
        $this->status_conta = (bool) (isset($dados['status_conta']) ? (bool) filter_var($dados['status_conta'], FILTER_VALIDATE_BOOLEAN):false);
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']): model_date());
    }

    /**
     * Função ressponsável por salvar os dados no banco de dados
     * @param array $dados
     * @return bool
     */
    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if($this->codigo_conta_fornecedor == null){
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'fornecedor' => $this->fornecedor, 'nome_conta' => (string) $this->nome_conta, 'descricao_conta' => (string) $this->descricao_conta, 'status_conta' => (bool) $this->status_conta, 'data_cadastro' => $this->data_cadastro]);
        }else{
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_conta_fornecedor], (array) ['empresa' => $this->empresa, 'fornecedor' => $this->fornecedor, 'nome_conta' => (string) $this->nome_conta, 'descricao_conta' => (string) $this->descricao_conta, 'status_conta' => (bool) $this->status_conta]);
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
}
?>