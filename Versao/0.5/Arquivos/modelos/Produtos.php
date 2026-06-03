<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class Produtos implements InterfaceModelo
{
    private mixed $codigo_produto;
    private mixed $empresa;
    private mixed $fornecedor;
    private string $nome_produto;
    private string $descricao;
    private string $imagem;
    private string $codigo_barras;
    private int $quantidade_alerta;
    private mixed $data_cadastro;
    private float $valor_venda;
    private float $valor_custo;
    private string $unidade_medida;
    private bool $status;
    private bool $tipo_produto;
    private string $sku_produto;

    private mixed $arquivo;

    public function tabela()
    {
        return (string) 'produtos';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'fornecedor' => 'objectId', 'nome_produto' => (string) '', 'descricao' => (string) '', 'imagem' => (string) '', 'codigo_barras' => (string) '', 'quantidade_alerta' => (float) 0, 'data_cadastro' => 'date', 'valor_venda' => (float) 0, 'valor_custo' => (float) 0, 'unidade_medida' => (string) '', 'status' => 'bool', 'tipo_produto' => 'bool', 'sku_produto' => (string) ''];
    }

    /**
     * Função responsável por colocar os dados nas variáveis correspondentes
     * @param array $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_produto', $dados)) {
            if ($dados['codigo_produto'] != null) {
                $this->codigo_produto = model_id($dados['codigo_produto']);
            }else{
                $this->codigo_produto = null;
            }
        }else{
            $this->codigo_produto = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : null);
        $this->fornecedor = (isset($dados['fornecedor']) ? model_id($dados['fornecedor']) : null);
        $this->nome_produto = (isset($dados['nome_produto']) ? (string) strtoupper($dados['nome_produto']) : '');
        $this->descricao = (isset($dados['descricao']) ? (string) htmlspecialchars($dados['descricao']) : '');
        $this->imagem = (string) (isset($dados['imagem']) ? (string) $dados['imagem'] : '');
        $this->codigo_barras = (string) (isset($dados['codigo_barras']) ? (string) $dados['codigo_barras'] : '');
        $this->quantidade_alerta = (float) (isset($dados['quantidade_alerta']) ? (float) str_replace(',', '.', $dados['quantidade_alerta']) : 0);
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
        $this->valor_venda = (float) (isset($dados['valor_venda']) ? (float) str_replace(',', '.', $dados['valor_venda']) : 0);
        $this->valor_custo = (float) (isset($dados['valor_custo']) ? (float) str_replace(',', '.', $dados['valor_custo']) : 0);
        $this->unidade_medida = (string) (isset($dados['unidade_medida']) ? (string) $dados['unidade_medida'] : '');
        $this->status = (bool) (isset($dados['status_produto']) ? (bool) filter_var($dados['status_produto'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->tipo_produto = (bool) (isset($dados['tipo_produto']) ? (bool) filter_var($dados['tipo_produto'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->sku_produto = (string) (isset($dados['sku_produto']) ? (string) $dados['sku_produto']:'');
    }

    /**
     * Função responsável por salvar os dados no banco de dados
     * @param array $dados
     * @return bool
     */
    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_produto != null) {
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_produto], (array) ['empresa' => $this->empresa, 'fornecedor' => $this->fornecedor, 'nome_produto' => (string) $this->nome_produto, 'descricao' => (string) $this->descricao, 'imagem' => (string) $this->imagem, 'codigo_barras' => (string) $this->codigo_barras, 'quantidade_alerta' => (float) $this->quantidade_alerta, 'data_cadastro' => $this->data_cadastro, 'valor_venda' => (float) $this->valor_venda, 'valor_custo' => (float) $this->valor_custo, 'unidade_medida' => (string) $this->unidade_medida, 'status' => (bool) $this->status, 'tipo_produto' => (bool) $this->tipo_produto, 'sku_produto' => (string) $this->sku_produto]);
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'fornecedor' => $this->fornecedor, 'nome_produto' => (string) $this->nome_produto, 'descricao' => (string) $this->descricao, 'imagem' => (string) $this->imagem, 'codigo_barras' => (string) $this->codigo_barras, 'quantidade_alerta' => (float) $this->quantidade_alerta, 'data_cadastro' => $this->data_cadastro, 'valor_venda' => (float) $this->valor_venda, 'valor_custo' => (float) $this->valor_custo, 'unidade_medida' => (string) $this->unidade_medida, 'status' => (bool) $this->status, 'tipo_produto' => (bool) $this->tipo_produto, 'sku_produto' => (string) $this->sku_produto]);
        }
    }

    /**
     * Função responsável por pesquisar os dados no banco de dados, retornando apenas um resultado
     * @param array $filtro
     * @return array
     */
    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    /**
     * Função responsável por pesquisar todos os dados encontrados no banco dados de acordo com os filtros passados
     * @param array $filtro
     * @return array 
     * */
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

    /**
     * Função responsável por contar a quantidade de produtos cadastrados no banco de dados e retornar.
     * @param mixed $dados
     * @return int
     */
    public function contar_quantidade_produtos($dados){
        $pipeline = [
            [
                '$match' => [
                    'empresa' => model_id($dados['empresa'])
                ]
            ],
            [
                '$group' => [
                    '_id' => [],
                    'COUNT(*)' => [
                        '$sum' => 1
                    ]
                ]
            ],
            [
                '$project' => [
                    'COUNT(*)' => '$COUNT(*)',
                    '_id' => 0
                ]
            ]
        ];

        $cursor = pesquisa_banco_aggregate((string) $this->tabela(), $pipeline);

        $retorno = (array) [];

        foreach ($cursor as $document) {
            $retorno = $document;
        }

        return (int) intval($retorno['COUNT(*)'], 10);
    }

    /**
     * Função responsável por montar o filtro de pesquisa
     * @param array $filtro
     * @return array
     */
    public function filtro_pesquisar_produto($filtro){
        $filtro_montando = (array) [];

        if(array_key_exists('nome_produto', $filtro) == true){
            array_push($filtro_montando, ['nome_produto', '=', $filtro['nome_produto']]);
        }

        if(array_key_exists('empresa', $filtro) == true){
            array_push($filtro_montando, ['empresa', '===', model_id($filtro['empresa'])]);
        }

        if(array_key_exists('status_produto', $filtro) == true){
            array_push($filtro_montando, ['status', '===', (bool) filter_var($filtro['status_produto'], FILTER_VALIDATE_BOOLEAN)]);
        }

        if(array_key_exists('tipo_produto', $filtro) == true){
            array_push($filtro_montando, ['tipo_produto', '===', (bool) filter_var($filtro['tipo_produto'], FILTER_VALIDATE_BOOLEAN)]);
        }

        if(array_key_exists('unidade_medida', $filtro) == true){
            array_push($filtro_montando, ['unidade_medida', '===', (string) $filtro['unidade_medida']]);
        }

        if(array_key_exists('data_cadastro', $filtro) == true){
            array_push($filtro_montando, ['data_cadastro', '>=', model_date($filtro['data_cadastro'], '00:00:00')]);
            array_push($filtro_montando, ['data_cadastro', '<=', model_date($filtro['data_cadastro'], '23:59:59')]);
        }

        return (array) ['filtro' => (array) ['and' => (array) $filtro_montando], 'ordenacao' => ['nome_produto' => (bool) true], 'limite' => (int) 0];
    }

    /**
     * Função responsável por pesquisar os produtos e trazer a quantidade de estoque atual
     * @param array $filtro_parametro
     * @return array
     */
    function pesquisar_produtos_estoque($filtro_parametro){
        $filtro = $this->filtro_pesquisar_produto($filtro_parametro);

        $retorno_produto = (array) $this->pesquisar_todos($filtro);

        if(empty($retorno_produto) == false){
            // foreach($retorno_produto as $produto){

            // }
            return (array) ['dados' => (array) $retorno_produto];
        }else{
            return (array) ['dados' => (array) []];
        }
    }
}
