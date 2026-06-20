<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class Sistema implements InterfaceModelo
{
    private $codigo_sistema;
    private $empresa;
    private $versao_sistema;
    private $anexa_documentos;

    public function tabela()
    {
        return (string) 'sistema';
    }

    public function modelo()
    {
        return (array) ['empresa' => 'objectId', 'versao_sistema' => (string) '', 'anexa_documentos' => (string) ''];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_sistema', $dados) == true) {
            if ($dados['codigo_sistema'] != '') {
                $this->codigo_sistema = model_id($dados['codigo_sistema']);
            } else {
                $this->codigo_sistema = null;
            }
        } else {
            $this->codigo_sistema = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->versao_sistema = (string) (isset($dados['versao_sistema']) ? (string) $dados['versao_sistema'] : 'alfa 0.0');
        $this->anexa_documentos = (string) (isset($dados['anexa_documentos']) ? (string) $dados['anexa_documentos'] : 'NAO');
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_sistema == null) {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'versao_sistema' => (string) $this->versao_sistema, 'anexa_documentos' => (string) $this->anexa_documentos]);
        } else {
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_sistema], (array) ['empresa' => $this->empresa, 'versao_sistema' => (string) $this->versao_sistema, 'anexa_documentos' => (string) $this->anexa_documentos]);
        }
    }

    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    public function pesquisar_todos($filtro)
    {
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['odenacao'], (int) $filtro['limite']);
    }
}
?>