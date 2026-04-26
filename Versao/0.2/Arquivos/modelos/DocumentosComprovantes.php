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
    private $data_cadastro;
    private $arquivo;

    public function tabela()
    {
        return (string) 'documentos_comprovantes';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'codigo_local' => 'objectId', 'local_documento' => (string) '', 'data_cadastro' => 'date'];
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
        $this->local_documento = (string) (isset($dados['local_documento']) ? (string) $dados['local_documento'] : '');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
    }

    public function salvar_dados($dados) {}

    public function pesquisar($filtro)
    {
        throw new \Exception('Not implemented');
    }

    public function pesquisar_todos($filtro)
    {
        throw new \Exception('Not implemented');
    }

    public function salvar_dados_arquivos($dados, $file)
    {
        $this->colocar_dados($dados);
        
        $this->arquivo = $file;
        $extensao = pathinfo($this->arquivo["arquivo"]["name"], PATHINFO_EXTENSION);
        $nome_arquivo = (string) '';

        $retorno_banco = (bool) false;
        $retorno = (bool) false;
        $retorno_checagem = (bool) model_check((string) $this->tabela(), (array) ['codigo_local', '===', $this->codigo_local]);

        if ($retorno_checagem == true) {
            $retorno_banco = (bool) model_update((string) $this->tabela(), ['codigo_local', '===', $this->codigo_local], (array) ['data_cadastro' => model_date()]);
        } else {
            $retorno_banco = (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'codigo_local' => $this->codigo_local, 'local_documento' => (string) $this->local_documento, 'data_cadastro' => $this->data_cadastro]);
        }

        if ($retorno_banco == true) {
            $objeto_conta_pagar_receber = new ContasPagarReceber();
                $nome_arquivo = (string) 'anexos/comprovantes/contas_pagar_receber_boletos/' . $this->codigo_local . "." . $extensao;
                $retorno = (bool) $objeto_conta_pagar_receber->alterar_anexo_boleto($this->codigo_local);

            if($retorno == true){
                if (move_uploaded_file($this->arquivo['arquivo']['tmp_name'], $nome_arquivo)) {
                    return (bool) true;
                } else {
                    return (bool) false;
                }
            }else{
                return (bool) false;
            }
        } else {
            return (bool) false;
        }
    }
}
