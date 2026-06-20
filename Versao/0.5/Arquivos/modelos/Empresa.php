<?php
require_once 'classes/bancoDeDados.php';

class Empresa implements InterfaceModelo
{
    private mixed $codigo_empresa;
    private string $nome_empresa;
    private string $nome_fantasia;
    private string $cnpj;
    private string $endereco;
    private mixed $data_cadastro;
    private string $logo;
    private string $telefone;

    public function tabela()
    {
        return (string) 'empresa';
    }

    public function modelo()
    {
        return (array) ['nome_empresa' => (string) '', 'nome_fantasia' => (string) '', 'cnpj' => (string) '', 'endereco' => (string) '', 'data_cadastro' => 'date', 'cidade' => (string) '', 'logo' => (string) '', 'telefone' => (string) ''];
    }

    /**
     * Função responsável por colocar os dados nas variáveis correspondentes
     * @param mixed $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_empresa', $dados) == true) {
            if ($dados['codigo_empresa'] != '') {
                $this->codigo_empresa = model_id($dados['codigo_empresa']);
            } else {
                $this->codigo_empresa = null;
            }
        } else {
            $this->codigo_empresa = null;
        }

        $this->nome_empresa = (string) (isset($dados['nome_empresa']) ? (string) strtoupper($dados['nome_empresa']) : '');
        $this->nome_fantasia = (string) (isset($dados['nome_fantasia']) ? (string) strtoupper($dados['nome_fantasia']) : '');
        $this->cnpj = (string) (isset($dados['cnpj']) ? (string) $dados['cnpj'] : '');
        $this->endereco = (string) (isset($dados['endereco']) ? (string) $dados['endereco'] : '');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
        $this->logo = (string) (isset($dados['logo']) ? (string) $dados['logo'] : '');
        $this->telefone = (string) (isset($dados['telefone']) ? (string) $dados['telefone'] : '');
    }

    /**
     * Função responsável pro salvar os dados no banco dados
     * @param array $dados
     * @return bool
     */
    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);


        if ($this->codigo_empresa == null) {
            $retorno_checagem = (bool) model_check((string) $this->tabela(), (array) ['cnpj', '===', (string) $this->cnpj]);

            if ($retorno_checagem == false) {
                return (bool) model_insert((string) $this->tabela(), (array) ['nome_empresa' => (string) $this->nome_empresa, 'nome_fantasia' => (string) $this->nome_fantasia, 'cnpj' => (string) $this->cnpj, 'endereco' => (string) $this->endereco, 'data_cadastro' => $this->data_cadastro, 'telefone' => (string) $this->telefone, 'logo' => (string) $this->logo]);
            } else {
                return (bool) false;
            }
        } else {
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_empresa], (array) ['nome_empresa' => (string) $this->nome_empresa, 'nome_fantasia' => (string) $this->nome_fantasia, 'cnpj' => (string) $this->cnpj, 'endereco' => (string) $this->endereco, 'data_cadastro' => $this->data_cadastro, 'telefone' => (string) $this->telefone, 'logo' => (string) $this->logo]);
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
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
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
}
