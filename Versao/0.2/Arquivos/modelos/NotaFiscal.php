<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class NotaFiscal implements InterfaceModelo
{
    private $codigo_nota_fiscal;
    private $empresa;
    private $cliente_fornecedor;
    private $data_cadastro;
    private $data_nota;
    private $valor_nota;
    private $chave_nota;
    private $tipo_nota;

    public function tabela()
    {
        return (string) 'nota_fiscal';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'cliente_fornecedor' => 'objectId', 'data_cadastro' => 'date', 'data_nota' => 'date', 'valor_nota' => (float) 0, 'chave_nota' => 'string', 'tipo_nota' => (string) ''];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_nota_fiscal', $dados) == true) {
            if ($dados['codigo_nota_fiscal'] != '') {
                $this->codigo_nota_fiscal = model_id($dados['codigo_nota_fiscal']);
            } else {
                $this->codigo_nota_fiscal = null;
            }
        } else {
            $this->codigo_nota_fiscal = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->cliente_fornecedor = (isset($dados['cliente_fornecedor']) ? model_id($dados['cliente_fornecedor']) : '');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
        $this->data_nota = (isset($dados['data_nota']) ? model_date($dados['data_nota']) : model_date());
        $this->valor_nota = (float) (isset($dados['valor_nota']) ? (float) doubleval(str_replace(',', '.', $dados['valor_nota'])) : 0);
        $this->chave_nota = (string) (isset($dados['chave_nota']) ? (string) $dados['chave_nota'] : '');
        $this->tipo_nota = (string) (isset($dados['tipo_nota']) ? (string) $dados['tipo_nota']:'OUTRAS');
    }

    public function salvar_dados_arquivo($dados, $file)
    {
        $retorno = (bool) $this->salvar_dados($dados);

        if ($retorno == true) {
            $extensao = pathinfo($file["arquivo_nota"]["name"], PATHINFO_EXTENSION);

            $pasta = (string) 'anexos/notas_fiscais';

            if(is_dir($pasta) == false){
                mkdir($pasta, 0777, true);
            }

            $nome_arquivo = (string) $pasta.'/' . $this->chave_nota . "." . $extensao;

            if (move_uploaded_file($file['arquivo_nota']['tmp_name'], $nome_arquivo)) {
                return (bool) true;
            } else {
                return (bool) false;
            }
        }else{
            return (bool) false;
        }
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_nota_fiscal != null) {
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_nota_fiscal], (array) ['data_nota' => $this->data_nota, 'valor_nota' => (float) $this->valor_nota, 'chave_nota' => (string) $this->chave_nota, 'tipo_nota' => (string) $this->tipo_nota]);
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'cliente_fornecedor' => $this->cliente_fornecedor, 'data_cadastro' => $this->data_cadastro, 'data_nota' => $this->data_nota, 'valor_nota' => (float) $this->valor_nota, 'chave_nota' => (string) $this->chave_nota, 'tipo_nota' => (string) $this->tipo_nota]);
        }
    }

    public function pesquisar($filtro) {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    public function pesquisar_todos($filtro) {
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
    }
}
