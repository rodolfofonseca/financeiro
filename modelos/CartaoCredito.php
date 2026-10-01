<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class CartaoCredito implements InterfaceModelo{
    private int $codigo_cartao_credito;
    private int $codigo_conta;
    private int $codigo_empresa;
    private string $nome_cartao;
    private string $descricao;
    private float $limite_cartao;
    private float $limite_utilizado;
    private float $anuidade;
    private bool $status_cartao;
    private int $mes_vencimento;
    private int $ano_vencimento;
    private int $dia_fechamento;

    public function tabela()
    {
        return (string) 'cartao_credito';
    }

    public function modelo()
    {
        return (array) [];
    }

    public function colocar_dados($dados)
    {
        $this->codigo_cartao_credito = (int) (isset($dados['codigo_cartao_credito']) ? (int) intval($dados['codigo_cartao_credito'], 10):0);
        $this->codigo_conta = (int) (isset($dados['codigo_conta']) ? (int) intval($dados['codigo_conta'], 10):0);
        $this->codigo_empresa = (int) (isset($dados['codigo_empresa']) ? (int) intval($dados['codigo_empresa'], 10):0);
        $this->nome_cartao = (string) (isset($dados['nome_cartao']) ? (string) strtoupper($dados['nome_cartao']):'');
        $this->descricao = (string) (isset($dados['descricao']) ? (string) $dados['descricao']:'');
        $this->limite_cartao = (float) (isset($dados['limite_cartao']) ? (float) floatval(str_replace(',', '.', $dados['limite_cartao'])):0);
        $this->limite_utilizado = (float) (isset($dados['limite_utilizado']) ? (float) floatval(str_replace(',', '.', $dados['limite_utilizado'])):0);
        $this->anuidade = (float) (isset($dados['anuidade']) ? (float) floatval(str_replace(',', '.', $dados['anuidade'])):0);
        $this->status_cartao = (bool) (isset($dados['status_cartao']) ? (bool) filter_var($dados['status_cartao'], FILTER_VALIDATE_BOOL):true);
        $this->mes_vencimento = (int) (isset($dados['mes_vencimento']) ? (int) intval($dados['mes_vencimento'], 10):0);
        $this->ano_vencimento = (int) (isset($dados['ano_vencimento']) ? (int) intval($dados['ano_vencimento'], 10):0);
        $this->dia_fechamento = (int) (isset($dados['dia_fechamento']) ? (int) intval($dados['dia_fechamento'], 10):0);
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if($this->codigo_cartao_credito != 0){
            return (bool) model_update((string) $this->tabela(), (array) ['where' => (array) [(array) ['codigo_cartao_credito', '==', (int) $this->codigo_cartao_credito]]], (array) $this->montar_array());
        }else{
            return (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
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

    public function montar_array()
    {
        $dados = (array) [];
      
        if($this->codigo_conta != 0){
            $dados['codigo_conta'] = (int) $this->codigo_conta;
        }

        if($this->codigo_empresa != 0){
            $dados['codigo_empresa'] = (int) $this->codigo_empresa;
        }

        if($this->nome_cartao != ''){
            $dados['nome_cartao'] = (string) $this->nome_cartao;
        }

        if($this->descricao != ''){
            $dados['descricao'] = (string) $this->descricao;
        }

        if($this->limite_cartao != 0){
            $dados['limite_cartao'] = (float) $this->limite_cartao;
        }

        if($this->limite_utilizado != 0){
            $dados['limite_utilizado'] = (float) $this->limite_utilizado;
        }

        if($this->anuidade != 0){
            $dados['anuidade'] = (float) $this->anuidade;
        }

        if($this->mes_vencimento != 0){
            $dados['mes_vencimento'] = (int) $this->mes_vencimento;
        }

        if($this->ano_vencimento != 0){
            $dados['ano_vencimento'] = (int) $this->ano_vencimento;
        }

        if($this->dia_fechamento != 0){
            $dados['dia_fechamento'] = (int) $this->dia_fechamento;
        }

        $dados['status_cartao'] = (bool) $this->status_cartao;
        return (array) $dados;
    }
}
?>