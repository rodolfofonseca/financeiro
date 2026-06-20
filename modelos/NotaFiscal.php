<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class NotaFiscal implements InterfaceModelo
{
    private int $codigo_nota_fiscal;
    private int $empresa;
    private int $cliente_fornecedor;
    private string $data_cadastro;
    private string $data_nota;
    private float $valor_nota;
    private string $chave_nota;
    private string $tipo_nota;

    public function tabela()
    {
        return (string) 'nota_fiscal';
    }

    public function modelo()
    {
        return (array) [];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_nota_fiscal', $dados) == true) {
            if ($dados['codigo_nota_fiscal'] != 0) {
                $this->codigo_nota_fiscal = (int) intval($dados['codigo_nota_fiscal'], 10);
            } else {
                $this->codigo_nota_fiscal = 0;
            }
        } else {
            $this->codigo_nota_fiscal = 0;
        }

        $this->empresa = (int) (isset($dados['empresa']) ? (int) intval($dados['empresa'], 10) : 0);
        $this->cliente_fornecedor = (int) (isset($dados['cliente_fornecedor']) ? (int) intval($dados['cliente_fornecedor'], 10) : 0);
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
        $this->data_nota = (isset($dados['data_nota']) ? model_date($dados['data_nota']) : model_date());
        $this->valor_nota = (float) (isset($dados['valor_nota']) ? (float) doubleval(str_replace(',', '.', $dados['valor_nota'])) : 0);
        $this->chave_nota = (string) (isset($dados['chave_nota']) ? (string) $dados['chave_nota'] : '');
        $this->tipo_nota = (string) (isset($dados['tipo_nota']) ? (string) $dados['tipo_nota'] : 'OUTRAS');
    }

    /**
     * Função responsável por salvar o arquivo da nota fiscal
     * @param mixed $dados
     * @param mixed $file
     * @return bool
     */
    public function salvar_dados_arquivo($dados, $file)
    {
        // file_put_contents('jsjsj.json', json_encode($dados));
        $retorno = (bool) $this->salvar_dados($dados);

        if ($retorno == true) {
            $extensao = pathinfo($file["arquivo_nota"]["name"], PATHINFO_EXTENSION);

            $pasta = (string) 'anexos/notas_fiscais';

            if (is_dir($pasta) == false) {
                mkdir($pasta, 0777, true);
            }

            $nome_arquivo = (string) $pasta . '/' . $this->chave_nota . "." . $extensao;

            if (move_uploaded_file($file['arquivo_nota']['tmp_name'], $nome_arquivo)) {
                return (bool) true;
            } else {
                return (bool) false;
            }
        } else {
            return (bool) false;
        }
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_nota_fiscal != 0) {
            return (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_nota_fiscal', '=', $this->codigo_nota_fiscal]]], (array) $this->montar_array());
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
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

    public function montar_array(){
        $dados = (array) [];

        if($this->empresa != 0){
            $dados['codigo_empresa'] = (int) $this->empresa;
        }

        if($this->cliente_fornecedor != 0){
            $dados['codigo_usuario'] = (int) $this->cliente_fornecedor; 
        }

        if($this->data_cadastro != ''){
            $dados['data_nota'] = (string) $this->data_cadastro;
        }

        if($this->valor_nota != 0){
            $dados['valor_nota'] = (float) arredondar($this->valor_nota);
        }

        if($this->chave_nota != ''){
            $dados['chave_nota'] = (string) $this->chave_nota;
        }

        if($this->tipo_nota != ''){
            $dados['tipo_nota'] = (string) $this->tipo_nota;
        }

        return (array) $dados;
    }
}
