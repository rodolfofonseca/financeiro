<?php
require_once 'classes/bancoDeDados.php';

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

    #[Override]
    public function salvar_dados($dados)
    {
        return (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
    }

    #[Override]
    public function pesquisar($filtro)
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function pesquisar_todos($filtro)
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function montar_array()
    {
        $dados = (array) [];

        if($this->codigo_conta_pagar_receber_historico != 0){
            $dados[''];
        }

        return (array) $dados;
    }
}
?>