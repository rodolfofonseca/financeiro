<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/ContasPagarReceber.php';

class DocumentosComprovantes implements InterfaceModelo
{
    private int $codigo_documentos_comprovantes;
    private int $empresa;
    private int $codigo_local;
    private string $local_documento;
    private string $nome_arquivo;
    private string $data_cadastro;
    private mixed $arquivo;

    public function tabela()
    {
        return (string) 'documento_comprovante';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'codigo_local' => 'objectId', 'local_documento' => (string) '', 'nome_arquivo' => (string) '', 'data_cadastro' => 'date'];
    }
    public function colocar_dados($dados)
    {
        $this->codigo_documentos_comprovantes = (int) (isset($dados['codigo_documentos_comprovantes']) ? (int) intval($dados['codigo_documentos_comprovantes'], 10) : 0);

        $this->empresa = (int) (isset($dados['empresa_anexo_documento']) ? (int) intval($dados['empresa_anexo_documento'], 10) : 0);
        $this->codigo_local = (int) (isset($dados['codigo_local']) ? (int) intval($dados['codigo_local'], 10) : 0);
        $this->nome_arquivo = (isset($dados['nome_arquivo']) ? (string) $dados['nome_arquivo'] : '');
        $this->local_documento = (string) (isset($dados['local_documento']) ? (string) $dados['local_documento'] : '');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
    }

    public function salvar_dados($dados)
    {
        if ($this->codigo_documentos_comprovantes != null) {
            return (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_documentos_comprovantes', '==', $this->codigo_documentos_comprovantes]]], (array) ['codigo_local' => $this->codigo_local, 'local_documento' => (string) $this->local_documento, 'nome_arquivo' => (string) $this->nome_arquivo, 'data_cadastro' => $this->data_cadastro]);
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) ['codigo_local' => $this->codigo_local, 'local_documento' => (string) $this->local_documento, 'nome_arquivo' => (string) $this->nome_arquivo, 'data_cadastro' => $this->data_cadastro]);
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
                chmod($nome_arquivo, 0777);
            }

            $nome_arquivo = $nome_arquivo . $this->codigo_local . "." . $extensao;

            $this->nome_arquivo = (string) $nome_arquivo;

            $retorno = (bool) $objeto_conta_pagar_receber->alterar_anexo_documento($this->codigo_local);

        } else {
            $nome_arquivo = (string) 'anexos/comprovantes/contas_pagar_receber_boletos/';

            if (is_dir($nome_arquivo) == false) {
                mkdir($nome_arquivo, 0777, true);
                chmod($nome_arquivo, 0777);
            }

            $nome_arquivo = $nome_arquivo . $this->codigo_local . "." . $extensao;
            $this->nome_arquivo = (string) $nome_arquivo;

            $retorno = (bool) $objeto_conta_pagar_receber->alterar_anexo_boleto($this->codigo_local);
        }

        $retorno_dados = $this->salvar_dados($dados);
        move_uploaded_file($this->arquivo['arquivo']['tmp_name'], $nome_arquivo);

        return (bool) true;
    }

    public function montar_array()
    {
        return (array) [];
    }
}
