<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class Sistema implements InterfaceModelo{
    private $codigo_sistema;
    private $empresa;
    private $versao_sistema;

    public function tabela()
    {
        return (string) 'sistema';
    }

    public function colocar_dados($dados)
    {
        throw new \Exception('Not implemented');
    }

    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro);
    }

    public function pesquisar_todos($filtro)
    {
        throw new \Exception('Not implemented');
    }

    public  function salvar_dados($dados)
    {
        throw new \Exception('Not implemented');
    }
}
?>