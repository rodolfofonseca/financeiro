<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/Movimentacao.php';
require_once 'classes/Mongo/Mongo.php';

use MongoDB\BSON\ObjectID;

class ContasPagarReceber implements InterfaceModelo
{
    private $codigo_conta_pagar_receber;
    private $empresa;
    private $nome_conta;
    private $descricao;
    private $valor_conta;
    private $valor_pago;
    private $valor_juro_desconto;
    private $tipo_juro_desconto;
    private $tipo_conta;
    private $data_cadastro;
    private $data_vencimento;
    private $data_baixa;
    private $status_conta;

    public function tabela()
    {
        return (string) 'contas_pagar_receber';
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_conta_pagar_receber', $dados) == true) {
            if ($dados['codigo_conta_pagar_receber'] != '') {
                $this->codigo_conta_pagar_receber = model_id($dados['codigo_conta_pagar_receber']);
            } else {
                $this->codigo_conta_pagar_receber = null;
            }
        } else {
            $this->codigo_conta_pagar_receber = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->nome_conta = (string) (isset($dados['nome_conta']) ? strtoupper($dados['nome_conta']) : '');
        $this->descricao = (string) (isset($dados['descricao']) ? (string) $dados['descricao'] : '');
        $this->valor_conta = (float) (isset($dados['valor_conta']) ? (float) doubleval(str_replace(',', '.', $dados['valor_conta'])) : 0);
        $this->valor_pago = (float) (isset($dados['valor_pago']) ? (float) doubleval(str_replace(',', '.', $dados['valor_pago'])) : 0);
        $this->valor_juro_desconto = (float) (isset($dados['valor_juro_desconto']) ? (float) doubleval(str_replace(',', '.', $dados['valor_juro_desconto'])) : 0);
        $this->tipo_juro_desconto = (string) (isset($dados['tipo_juro_desconto']) ? (string) $dados['tipo_juro_desconto'] : '');
        $this->tipo_conta = (string) (isset($dados['tipo_conta']) ? (string) $dados['tipo_conta'] : 'PAGAR');
        $this->data_cadastro = (isset($dados['data_cadastro']) ? model_date($dados['data_cadastro']) : model_date());
        $this->data_vencimento = (isset($dados['data_vencimento']) ? model_date($dados['data_vencimento']) : model_date());
        $this->data_baixa = (isset($dados['data_baixa']) ? model_date($dados['data_baixa']) : model_date());
        $this->status_conta = (string) (isset($dados['status_conta']) ? (string) $dados['status_conta'] : 'AGUARDANDO');
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        if ($this->codigo_conta_pagar_receber != null) {
            return (bool) model_update((string) $this->tabela(), (array)['_id', '===', $this->codigo_conta_pagar_receber], (array) ['empresa' => $this->empresa, 'nome_conta' => (string) $this->nome_conta, 'descricao' => (string) $this->descricao, 'valor_conta' => (float) $this->valor_conta, 'valor_pago' => (float) $this->valor_pago, 'valor_juro_desconto' => (float) $this->valor_juro_desconto, 'tipo_juro_desconto' => (string) $this->tipo_juro_desconto, 'tipo_conta' => (string) $this->tipo_conta, 'data_cadastro' => $this->data_cadastro, 'data_vencimento' => $this->data_vencimento, 'data_baixa' => $this->data_baixa, 'status_conta' => (string) $this->status_conta]);
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'nome_conta' => (string) $this->nome_conta, 'descricao' => (string) $this->descricao, 'valor_conta' => (float) $this->valor_conta, 'valor_pago' => (float) $this->valor_pago, 'valor_juro_desconto' => (float) $this->valor_juro_desconto, 'tipo_juro_desconto' => (string) $this->tipo_juro_desconto, 'tipo_conta' => (string) $this->tipo_conta, 'data_cadastro' => $this->data_cadastro, 'data_vencimento' => $this->data_vencimento, 'data_baixa' => $this->data_baixa, 'status_conta' => (string) $this->status_conta]);
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

    public function baixar_contas($dados)
    {
        $this->colocar_dados($dados);

        $objeto_movimentacao = new Movimentacao();

        $conta = (array) $this->pesquisar((array) ['filtro' => (array) ['_id', '===', $this->codigo_conta_pagar_receber]]);

        if (empty($conta) == false) {
            $array_movimentacao = (array) ['empresa' => (string) $dados['empresa'], 'conta' => (string) $dados['codigo_conta_bancaria'], 'valor_lancamento' => (float) $this->valor_pago];



            if ($this->tipo_conta == 'PAGAR') {
                $array_movimentacao['tipo_lancamento'] = (string) 'DEBITO';
                $array_movimentacao['descricao'] = (string) 'Pagamento da conta ' . $this->nome_conta;
            } else {
                $array_movimentacao['tipo_lancamento'] = (string) 'CREDITO';
                $array_movimentacao['descricao'] = (string) 'Recebimento da conta ' . $this->nome_conta;
            }

            $retorno_movimentacao = (bool) $objeto_movimentacao->salvar_dados($array_movimentacao);

            if ($retorno_movimentacao == true) {

                $retorno_contas_pagar_receber = (bool) model_update((string) $this->tabela(), ['_id', '===', $this->codigo_conta_pagar_receber], (array) ['valor_pago' => (float) $this->valor_pago, 'valor_juro_desconto' => (float) $this->valor_juro_desconto, 'tipo_juro_desconto' => (string) $this->tipo_juro_desconto, 'data_baixa' => $this->data_baixa, 'status_conta' => (string) 'PAGO']);

                if ($retorno_movimentacao == true && $retorno_contas_pagar_receber == true) {
                    return (bool) true;
                } else {
                    return (bool) false;
                }
            } else {
                return (bool) false;
            }
        } else {
            return (bool) false;
        }
    }

    public function alterar_status($dados)
    {
        return (bool) model_update((string) $this->tabela(), (array) ['and' => [['empresa', '===', model_id($dados['empresa'])], ['data_vencimento', '<=', model_date('', '23:59:59')], ['status_conta', '===', 'AGUARDANDO']]], (array) ['status_conta' => (string) 'VENCIDA']);
        // return (bool) model_update((string) $this->tabela(), (array) ['empresa', '===', model_id($dados['empresa'])], ['data_vencimento', '<=', model_date('', '23:59:59')], ['status_conta', '===', (string)'AGUARDANDO'], (array) ['status_conta' => (string) 'VENCIDA']);
    }


    public function relatorio_contas_pagar($codigo_empresa)
    {
        $pipeline = [['$match' => ['$or' => [['$and' => [['empresa' => model_id($codigo_empresa)], ['tipo_conta' => 'PAGAR'], ['status_conta' => 'AGUARDANDO']]], ['status_conta' => 'VENCIDA' ], ['tipo_conta' => 'RECEBER']]]], ['$group' => ['_id' => ['status_conta' => '$status_conta'], 'COUNT(*)' => ['$sum' => 1], 'SUM(valor_conta)' => ['$sum' => '$valor_conta']]], ['$project' => ['status_conta' => '$_id.status_conta', 'COUNT(*)' => '$COUNT(*)', 'SUM(valor_conta)' => '$SUM(valor_conta)', '_id' => 0]]];

        $cursor = pesquisa_banco_aggregate((string) $this->tabela(), $pipeline);

        $retorno = (array) [];

        foreach ($cursor as $document) {
            array_push($retorno, $document);
        }

        return (array) $retorno;
    }
}
