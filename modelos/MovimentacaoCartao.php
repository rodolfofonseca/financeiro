<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class MovimentacaoCartao implements InterfaceModelo
{
    private int $codigo_movimentacao_cartao;
    private int $codigo_cartao_credito;
    private string $loja_movimentacao;
    private string $historico_movimentacao;
    private float $valor_lancamento;
    private bool $tipo_lancamento;
    private string $data_movimentacao;

    public function tabela()
    {
        return (string) 'movimentacao_cartao';
    }

    public function modelo()
    {
        return (array) [];
    }

    public function colocar_dados($dados)
    {
        $this->codigo_movimentacao_cartao = (int) (isset($dados['codigo_movimentacao_cartao']) ? (int) intval($dados['codigo_movimentacao_cartao'], 10) : 0);
        $this->codigo_cartao_credito = (int) (isset($dados['codigo_cartao_credito']) ? (int) intval($dados['codigo_cartao_credito'], 10) : 0);
        $this->loja_movimentacao = (string) (isset($dados['loja_movimentacao']) ? (string) $dados['loja_movimentacao'] : '');
        $this->historico_movimentacao = (string) (isset($dados['historico_movimentacao']) ? (string) $dados['historico_movimentacao'] : '');
        $this->valor_lancamento = (float) (isset($dados['valor_lancamento']) ? (float) floatval(str_replace(',', '.', $dados['valor_lancamento'])) : 0.0);
        $this->tipo_lancamento = (bool) (isset($dados['tipo_lancamento']) ? (bool) filter_var($dados['tipo_lancamento'], FILTER_VALIDATE_BOOLEAN) : false);
        $this->data_movimentacao = (string) (isset($dados['data_movimentacao']) ? (string) $dados['data_movimentacao'] : '');
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_movimentacao_cartao != 0) {
            return (bool) model_update((string) $this->tabela(), (array) ['where' => (array) [(array) ['codigo_movimentacao_cartao', '==', (int) $this->codigo_movimentacao_cartao]]], (array) $this->montar_array());
        } else {
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

        if ($this->codigo_cartao_credito != 0) {
            $dados['codigo_cartao_credito'] = (int) $this->codigo_cartao_credito;
        }

        if ($this->loja_movimentacao != '') {
            $dados['loja_movimentacao'] = (string) $this->loja_movimentacao;
        }

        if ($this->historico_movimentacao != '') {
            $dados['historico_movimentacao'] = (string) $this->historico_movimentacao;
        }

        if ($this->valor_lancamento != 0.0) {
            $dados['valor_lancamento'] = (float) $this->valor_lancamento;
        }

        if ($this->data_movimentacao != '') {
            $dados['data_movimentacao'] = (string) model_date($this->data_movimentacao);
        }

        $dados['tipo_lancamento'] = (bool) $this->tipo_lancamento;

        return (array) $dados;
    }
}
?>