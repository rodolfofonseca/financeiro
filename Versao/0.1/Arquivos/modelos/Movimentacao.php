<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/Contas.php';

class Movimentacao implements InterfaceModelo
{
    private $codigo_movimentacao;
    private $conta;
    private $empresa;
    private $tipo_lancamento;
    private $valor_lancamento;
    private $data_lancamento;
    private $descricao;

    public function tabela()
    {
        return (string) 'movimentacao';
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_movimentacao', $dados) == true) {
            if ($dados['codigo_movimentacao'] != '') {
                $this->codigo_movimentacao = model_id($dados['codigo_movimentacao']);
            } else {
                $this->codigo_movimentacao = null;
            }
        } else {
            $this->codigo_movimentacao = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->conta = (isset($dados['conta']) ? model_id($dados['conta']) : '');
        $this->valor_lancamento = (double) (isset($dados['valor_lancamento']) ? (double) doubleval(str_replace(',', '.', $dados['valor_lancamento'])) : 0);
        $this->data_lancamento = (isset($dados['data_lancamento']) ? model_date($dados['data_lancamento']) : model_date());
        $this->descricao = (string) (isset($dados['descricao']) ? (string) $dados['descricao'] : '');
        $this->tipo_lancamento = (string) (isset($dados['tipo_lancamento']) ? (string) $dados['tipo_lancamento'] : 'CREDITO');
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);
        $retorno_movimentacao = (bool) false;

        if ($this->codigo_movimentacao == null) {
            $retorno_movimentacao = (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'conta' => $this->conta, 'tipo_lancamento' => (string) $this->tipo_lancamento, 'valor_lancamento' => (double) $this->valor_lancamento, 'descricao' => (string) $this->descricao, 'data_lancamento' => $this->data_lancamento]);
        } else {
            $retorno_movimentacao = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_movimentacao], (array) ['empresa' => $this->empresa, 'conta' => $this->conta, 'tipo_lancamento' => (string) $this->tipo_lancamento, 'valor_lancamento' => (double) $this->valor_lancamento, 'descricao' => (string) $this->descricao, 'data_lancamento' => $this->data_lancamento]);
        }

        if ($retorno_movimentacao == true) {
            $objeto_conta = new Contas();
            $retorno_conta = (array) $objeto_conta->pesquisar((array) ['filtro' => (array) ['_id', '===', $this->conta]]);

            if (empty($retorno_conta) == false) {
                $resultado_conta = (double) 0;

                if ($this->tipo_lancamento == 'CREDITO') {
                    $resultado_conta = (double) arredondar($retorno_conta['saldo_conta'], '+', $this->valor_lancamento);
                } else {
                    $resultado_conta = (double) arredondar($retorno_conta['saldo_conta'], '-', $this->valor_lancamento);
                }

                $retorno_conta['saldo_conta'] = (double) $resultado_conta;
                $retorno_conta['codigo_conta'] = $retorno_conta['_id'];

                return (bool) $objeto_conta->salvar_dados((array) $retorno_conta);
            } else {
                return (bool) false;
            }
        } else {
            return (bool) false;
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

    public function deletar_movimentacao($filtro)
    {
        return (bool) model_delete((string) $this->tabela(), (array) $filtro['filtro']);
    }
}
?>