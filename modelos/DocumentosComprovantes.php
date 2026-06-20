<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/ContasPagarReceber.php';

class DocumentosComprovantes implements InterfaceModelo
{
    private $codigo_documentos_comprovantes;
    private $empresa;
    private $codigo_local;
    private $local_documento;
    private $nome_arquivo;
    private $data_cadastro;
    private $arquivo;

    public function tabela()
    {
        return (string) 'documentos_comprovantes';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'codigo_local' => 'objectId', 'local_documento' => (string) '', 'nome_arquivo' => (string) '', 'data_cadastro' => 'date'];
    }
    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_documentos_comprovantes', $dados) == true) {
            if ($dados['codigo_documentos_comprovantes'] != '') {
                $this->codigo_documentos_comprovantes = model_id($dados['codigo_documentos_comprovantes']);
            } else {
                $this->codigo_documentos_comprovantes = null;
            }
        } else {
            $this->codigo_documentos_comprovantes = null;
        }

        $this->empresa = (isset($dados['empresa_anexo_documento']) ? model_id($dados['empresa_anexo_documento']) : '');
        $this->codigo_local = (isset($dados['codigo_local']) ? model_id($dados['codigo_local']) : '');
        $this->nome_arquivo = (isset($dados['nome_arquivo']) ? (string) $dados['nome_arquivo'] : '');
        $this->local_documento = (string) (isset($dados['local_documento']) ? (string) $dados['local_documento'] : '');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
    }

    public function salvar_dados($dados)
    {
        if ($this->codigo_documentos_comprovantes != null) {
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_documentos_comprovantes], (array) ['empresa' => $this->empresa, 'codigo_local' => $this->codigo_local, 'local_documento' => (string) $this->local_documento, 'nome_arquivo' => (string) $this->nome_arquivo, 'data_cadastro' => $this->data_cadastro]);
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'codigo_local' => $this->codigo_local, 'local_documento' => (string) $this->local_documento, 'nome_arquivo' => (string) $this->nome_arquivo, 'data_cadastro' => $this->data_cadastro]);
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
     * Função responsável por salvar os dados no banco de dados e os arquivos
     * @param mixed $dados
     * @param mixed $file
     * @return bool
     */
    public function salvar_dados_arquivos($dados, $file)
    {
        $objeto_conta_pagar_receber = new ContasPagarReceber();
        $this->colocar_dados($dados);
        $this->arquivo = $file;

        $extensao = pathinfo($this->arquivo["arquivo"]["name"], PATHINFO_EXTENSION);
        $retorno = (bool) false;
        $nome_arquivo = (string) '';

        if ($this->local_documento == 'CONTAS_PAGAR_RECEBER') {
            $nome_arquivo = (string) 'anexos/comprovantes/contas_pagar_receber/';

            if (is_dir($nome_arquivo) == false) {
                mkdir($nome_arquivo, 0777, true);
            }

            $nome_arquivo = $nome_arquivo . $this->codigo_local . "." . $extensao;

            $retorno = (bool) $objeto_conta_pagar_receber->alterar_anexo_documento($this->codigo_local);
        } else if ($this->local_documento == 'CONTAS_PAGAR_RECEBER_BOLETOS') {
            $nome_arquivo = (string) 'anexos/comprovantes/contas_pagar_receber_boletos/';

            if (is_dir($nome_arquivo) == false) {
                mkdir($nome_arquivo, 0777, true);
            }

            $nome_arquivo = $nome_arquivo . $this->codigo_local . "." . $extensao;

            $retorno = (bool) $objeto_conta_pagar_receber->alterar_anexo_boleto($this->codigo_local);
        }

        $this->nome_arquivo = (string) $nome_arquivo;

        $retorno_documentos_comprovantes = (bool) $this->salvar_dados((array) []);

        if ($retorno_documentos_comprovantes == true) {
            move_uploaded_file($this->arquivo['arquivo']['tmp_name'], $nome_arquivo);
            return (bool) true;
        } else {
            return (bool) false;
        }
    }

    public function montar_array(){
        return (array) [];
    }
}
