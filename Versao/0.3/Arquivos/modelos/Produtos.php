<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class Produtos implements InterfaceModelo
{
    private $codigo_produto;
    private $empresa;
    private $fornecedor;
    private $nome_produto;
    private $descricao;
    private $imagem;
    private $codigo_barras;
    private $quantidade_alerta;
    private $data_cadastro;
    private $valor_venda;
    private $valor_custo;
    private $unidade_medida;
    private $status;
    private $tipo_produto;

    private $arquivo;

    public function tabela()
    {
        return (string) 'produtos';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'fornecedor' => 'objectId', 'nome_produto' => (string) '', 'descricao' => (string) '', 'imagem' => (string) '', 'codigo_barras' => (string) '', 'quantidade_alerta' => (float) 0, 'data_cadastro' => 'date', 'valor_venda' => (float) 0, 'valor_custo' => (float) 0, 'unidade_medida' => (string) '', 'status' => 'bool', 'tipo_produto' => 'bool'];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_produto', $dados)) {
            if ($dados['codigo_produto'] != null) {
                $this->codigo_produto = model_id($dados['codigo_produto']);
            }
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : null);
        $this->fornecedor = (isset($dados['fornecedor']) ? model_id($dados['fornecedor']) : null);
        $this->nome_produto = (isset($dados['nome_produto']) ? (string) strtoupper($dados['nome_produto']) : '');
        $this->descricao = (isset($dados['descricao']) ? (string) htmlspecialchars($dados['descricao']) : '');
        $this->imagem = (isset($dados['imagem']) ? (string) $dados['imagem'] : '');
        $this->codigo_barras = (isset($dados['codigo_barras']) ? (string) $dados['codigo_barras'] : '');
        $this->quantidade_alerta = (isset($dados['quantidade_alerta']) ? (float) str_replace(',', '.', $dados['quantidade_alerta']) : 0);
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
        $this->valor_venda = (isset($dados['valor_venda']) ? (float) str_replace(',', '.', $dados['valor_venda']) : 0);
        $this->valor_custo = (isset($dados['valor_custo']) ? (float) str_replace(',', '.', $dados['valor_custo']) : 0);
        $this->unidade_medida = (isset($dados['unidade_medida']) ? (string) $dados['unidade_medida'] : '');
        $this->status = (isset($dados['status_produto']) ? (bool) $dados['status_produto'] : false);
        $this->tipo_produto = (isset($dados['tipo_produto']) ? (bool) $dados['tipo_produto'] : false);
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_produto != null) {
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_produto], (array) ['empresa' => $this->empresa, 'fornecedor' => $this->fornecedor, 'nome_produto' => (string) $this->nome_produto, 'descricao' => (string) $this->descricao, 'imagem' => (string) $this->imagem, 'codigo_barras' => (string) $this->codigo_barras, 'quantidade_alerta' => (float) $this->quantidade_alerta, 'data_cadastro' => $this->data_cadastro, 'valor_venda' => (float) $this->valor_venda, 'valor_custo' => (float) $this->valor_custo, 'unidade_medida' => (string) $this->unidade_medida, 'status' => (bool) $this->status, 'tipo_produto' => (bool) $this->tipo_produto]);
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'fornecedor' => $this->fornecedor, 'nome_produto' => (string) $this->nome_produto, 'descricao' => (string) $this->descricao, 'imagem' => (string) $this->imagem, 'codigo_barras' => (string) $this->codigo_barras, 'quantidade_alerta' => (float) $this->quantidade_alerta, 'data_cadastro' => $this->data_cadastro, 'valor_venda' => (float) $this->valor_venda, 'valor_custo' => (float) $this->valor_custo, 'unidade_medida' => (string) $this->unidade_medida, 'status' => (bool) $this->status, 'tipo_produto' => (bool) $this->tipo_produto]);
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

    /**
     * Função para alterar o status do produto, se estiver ativo, ficará inativo e vice-versa. Retorna true se a alteração for bem-sucedida, ou false caso contrário.
     * @param string $codigo_produto O código do produto cujo status deve ser alterado.
     * @return bool Retorna true se a alteração for bem-sucedida, ou false caso contrário.
     */
    public function alterar_status($codigo_produto)
    {
        $filtro = (array) ['_id', '===', model_id($codigo_produto)];
        $produto = (array) $this->pesquisar((array) ['filtro' => $filtro]);

        if (!empty($produto)) {
            $novo_status = !$produto['status'];
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', model_id($codigo_produto)], (array) ['status' => (bool) $novo_status]);
        } else {
            return (bool) false;
        }
    }

    /**
     * Função para colocar a foto do produto. Recebe os dados do produto e o arquivo da foto, salva a foto no servidor e atualiza o caminho da imagem no banco de dados. Retorna true se a operação for bem-sucedida, ou false caso contrário.
      * @param array $dados Os dados do produto.
      * @param array $file O arquivo da foto do produto.
        * @return bool Retorna true se a operação for bem-sucedida, ou false caso contrário.
     */
    public function colocar_foto($dados, $file)
    {
        $this->colocar_dados($dados);
        $this->arquivo = $file;

        $extensao = pathinfo($this->arquivo["arquivo"]["name"], PATHINFO_EXTENSION);
        $retorno = (bool) false;
        $nome_arquivo = (string) 'imagens/produtos/';

        if (!is_dir($nome_arquivo)) {
            mkdir($nome_arquivo, 0755, true);
        }

        $nome_arquivo = $nome_arquivo . $this->codigo_produto . "." . $extensao;
        if (move_uploaded_file($this->arquivo["arquivo"]["tmp_name"], $nome_arquivo)) {
            $retorno = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_produto], (array) ['imagem' => (string) $nome_arquivo]);
        }

        return $retorno;
    }
}
