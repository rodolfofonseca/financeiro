<?php
interface InterfaceModelo
{
    /**
     * Função responsável por retornar o nome da tabela do banco de dados
     * @return string
     */
    public function tabela();
    /**
     * Função responsável por mostrar ao programador de forma lágica o modelo do banco de dados
     * @return string
     */
    public function modelo();
    /**
     * Função responsável por colocar os dados nas variaáveis correspondentes
     * @param array $dados
     * @return void
     */
    public function colocar_dados($dados);
    /**
     * Função responsável por salvar no banco de dados os dados
     * @param array $dados
     * @return mixed
     */
    public function salvar_dados($dados);
    /**
     * Função responsável por pesquisar os dados no banco de dados
     * @param array $filtro
     * @return array
     */
    public function pesquisar($filtro);
    /**
     * Função responsável por pesquisar os ddos no banco de dados
     * @param array $filtro
     * @return array
     */
    public function pesquisar_todos($filtro);

    /**
     * Função responsável por montar o array que será enviado ao banco de dados
     * @return array
     */
    public function montar_array();
}
?>