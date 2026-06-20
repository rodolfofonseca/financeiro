<?php
require_once 'classes/bancoDeDados.php';

class Empresa implements InterfaceModelo
{
    private int $codigo_empresa;
    private string $nome_empresa;
    private string $nome_fantasia;
    private string $cnpj;
    private string $endereco;
    private string $data_cadastro;
    private string $logo;
    private string $telefone;
    private bool $status_empresa;

    public function tabela()
    {
        return (string) 'empresa';
    }

    public function modelo()
    {
        return (array) [];
    }

    /**
     * Função responsável por colocar os dados nas variáveis correspondentes
     * @param mixed $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_empresa', $dados) == true) {
            if ($dados['codigo_empresa'] != 0) {
                $this->codigo_empresa = (int) intval($dados['codigo_empresa'], 10);
            } else {
                $this->codigo_empresa = (int) 0;
            }
        } else {
            $this->codigo_empresa = (int) 0;
        }

        $this->nome_empresa = (string) (isset($dados['nome_empresa']) ? (string) strtoupper($dados['nome_empresa']) : '');
        $this->nome_fantasia = (string) (isset($dados['nome_fantasia']) ? (string) strtoupper($dados['nome_fantasia']) : '');
        $this->cnpj = (string) (isset($dados['cnpj']) ? (string) $dados['cnpj'] : '');
        $this->endereco = (string) (isset($dados['endereco']) ? (string) $dados['endereco'] : '');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
        $this->logo = (string) (isset($dados['logo']) ? (string) $dados['logo'] : '');
        $this->telefone = (string) (isset($dados['telefone']) ? (string) $dados['telefone'] : '');
        $this->status_empresa = (bool) (isset($dados['status_empresa']) ? (bool) filter_var($dados['status_empresa'] ,FILTER_VALIDATE_BOOLEAN):false);
    }

    /**
     * Função responsável pro salvar os dados no banco dados
     * @param array $dados
     * @return array
     */
    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if($this->codigo_empresa != 0){
            return (array) model_update((string) $this->tabela(), (array) ['where' => (array) ['codigo_empresa', '==', (int) $this->codigo_empresa]], (array) $this->montar_array(), false);
        }else{
            return (array) model_insert((string) $this->tabela(), (array) $this->montar_array(), false);
        }
    }

    /**
     * Função responsável por pesquisar os dados de uma empresa em específico
     * @param array $filtro
     * @return array
     */
    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    /**
     * Função responsável por pesquisar todas as empresas cadastradas no banco de dados
     * @param array $filtro
     * @return array
     */
    public function pesquisar_todos($filtro)
    {
        // return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
        return (array) [];
    }

    /**
     * Função responsável por checar se um empresa já esta cadastrada no banco de dados
     * @param array $filtro
     * @return bool
     */
    public function checar_empresa($filtro)
    {
        return (bool) model_check((string) $this->tabela(), (array) $filtro['filtro']);
    }

    public function montar_array(){
        $dados = (array) [];

        if($this->nome_empresa != ''){
            $dados['nome_empresa'] = (string) $this->nome_empresa;
        }

        if($this->cnpj != ''){
            $dados['cpf_cnpj'] = (string) $this->cnpj;
        }

        if($this->data_cadastro != ''){
            $dados['data_cadastro'] = (string) $this->data_cadastro;
        }

        if($this->cidade != ''){
            $dados['cidade'] = (string) $this->cidade;
        }

        if($this->logo != ''){
            $dados['logo'] = (string) $this->logo;
        }

        if($this->telefone != ''){
            $dados['telefone'] = (string) $this->telefone;
        }

        if($this->endereco != ''){
            $dados['endereco'] = (string) $this->endereco;
        }

        $dados['status_empresa'] = (bool) $this->status_empresa;

        return (array) $dados;
    }
}
