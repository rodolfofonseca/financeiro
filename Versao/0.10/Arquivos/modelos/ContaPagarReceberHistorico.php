<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class ContaPagarReceberHistorico implements InterfaceModelo{
    private int $codigo_conta_pagar_receber_historico;
    private int $codigo_usuario;
    private int $codigo_conta_pagar_receber;
    private string $data_conta_pagar_receber_historico;
    private string $descricao_conta_pagar_receber_historico;

    public function tabela(){
        return (string) 'conta_pagar_receber_historico';
    }

    public function modelo(){
        return (array) [];
    }

    public function colocar_dados($dados) {
        $this->codigo_conta_pagar_receber_historico = (int) (isset($dados['codigo_conta_pagar_receber_historico']) ? (int) intval($dados['codigo_conta_pagar_receber_historico'], 10):0);
        $this->codigo_usuario = (int) (isset($dados['codigo_usuario']) ? (int) intval($dados['codigo_usuario'], 10):0);
        $this->codigo_conta_pagar_receber = (int) (isset($dados['codigo_conta_pagar_receber']) ? (int) intval($dados['codigo_conta_pagar_receber'], 10):0);
        $this->data_conta_pagar_receber_historico = (string) (isset($dados['data_conta_pagar_receber']) ? (string) $dados['data_conta_pagar_receber']: model_date());
        $this->descricao_conta_pagar_receber_historico = (string) (isset($dados['descricao_conta_pagar_receber_historico']) ? (string) $dados['descricao_conta_pagar_receber_historico']:'');
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);
        return (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
    }

    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    public function pesquisar_todos($filtro)
    {
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
    }

    public function montar_array()
    {
        $dados = (array) [];

        if($this->codigo_usuario != 0){
            $dados['codigo_usuario'] = (int) $this->codigo_usuario;
        }

        if($this->codigo_conta_pagar_receber != 0){
            $dados['codigo_conta_pagar_receber'] = (int) $this->codigo_conta_pagar_receber;
        }

        if($this->descricao_conta_pagar_receber_historico != ''){
            $dados['descricao_conta_pagar_receber_historico'] = (string) $this->descricao_conta_pagar_receber_historico;
        }

        return (array) $dados;
    }

    /**
     * Função responsável por pesquisar os históricos de movimentação da conta em questão
     * @param mixed $codigo_conta
     * @return array
     */
    public function pesquisar_historico($codigo_conta){
        $sql = (string) "SELECT TO_CHAR(historico.data_conta_pagar_receber_historico, 'DD/MM/YYYY HH24:MI:SS') AS data_hora, historico.descricao_conta_pagar_receber_historico AS descricao, split_part(usuario.nome_usuario, ' ', 1) AS nome_usuario, usuario.avatar FROM conta_pagar_receber_historico historico, usuario usuario WHERE historico.codigo_conta_pagar_receber = :codigo_conta AND usuario.codigo_usuario = historico.codigo_usuario ORDER BY historico.data_conta_pagar_receber_historico ASC;";

        return (array) model_query($sql, ['codigo_conta' => $codigo_conta]);
    }
}
?>