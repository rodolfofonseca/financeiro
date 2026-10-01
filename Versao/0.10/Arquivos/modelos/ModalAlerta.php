<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class ModalAlerta implements InterfaceModelo{
    private int $codigo_modal_alerta;
    private int $codigo_usuario;
    private string $imagem;

    public function tabela(){
        return (string) 'modal_alerta';
    }

    public function modelo(){
        return (array) [];
    }

    public function colocar_dados($dados)
    {
        $this->codigo_modal_alerta = (int) (isset($dados['codigo_modal_alerta']) ? (int) intval($dados['codigo_modal_alerta'], 10):0);
        $this->codigo_usuario = (int) (isset($dados['codigo_usuario']) ? (int) intval($dados['codigo_usuario'], 10):0);
        $this->imagem = (string) (isset($dados['imagem']) ? (string) $dados['imagem']:'');
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if($this->codigo_modal_alerta != 0){
            return (bool) model_update((string) $this->tabela(), (array) ['where' => (array) [(array) ['codigo_modal_alerta', '==', (int) $this->codigo_modal_alerta]]], (array) $this->montar_array());
        }else{
            return (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
        }
    }

    public function pesquisar($filtros){
        return (array) model_one((string) $this->tabela(), (array) $filtros['filtro'], (array) $filtros['ordenacao']);
    }
    
    public function pesquisar_todos($filtro)
    {
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
    }

    public function montar_array()
    {
        $dados_retorno = (array) [];

        if($this->codigo_usuario != 0){
            $dados_retorno['codigo_usuario'] = (int) $this->codigo_usuario;
        }

        if($this->imagem != ''){
            $dados_retorno['imagem'] = (string) $this->imagem;
        }

        return (array) $dados_retorno;
    }

    /**
     * Função responsável por contar a quantidade de alerta que o usuário possui não visualizado dentro do sistema.
     * @param mixed $codigo_usuario
     * @return array
     */
    public function quantidade_alerta($codigo_usuario){
        $sql = (string) "select count(*) as quantidade_alerta from modal_alerta where codigo_usuario = :codigo_usuario;";

        return model_query((string) $sql, (array) ['codigo_usuario' => $codigo_usuario]);
    }

    /**
     * Função responsável por excluir o alerta do sistema depois que o usuário visualiza o mesmo.
     * @param mixed $codigo_alerta
     * @return bool
     */
    public function excluir_alerta($codigo_alerta){
        return (bool) model_delete((string) $this->tabela(), (array) ['where' => (array) [(array) ['codigo_modal_alerta', '==', (int) $codigo_alerta]]]);
    }
}

?>