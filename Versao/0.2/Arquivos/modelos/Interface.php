<?php
interface InterfaceModelo
{
    public function tabela();
    public function modelo();
    public function colocar_dados($dados);
    public function salvar_dados($dados);
    public function pesquisar($filtro);
    public function pesquisar_todos($filtro);
}
?>