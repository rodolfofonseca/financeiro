<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/Movimentacao.php';

class FechamentoContabilGeral implements InterfaceModelo
{
    private $codigo_fechamento_contabil;
    private $empresa;
    private $mes_referencia;
    private $ano_referencia;
    private $total_credito;
    private $total_debito;
    private $valor_resultado;
    private $resultado;
    private $data_fechamento;

    public function tabela()
    {
        return (string) 'fechamento_contabil_geral';
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_fechamento_contabil', $dados) == true) {
            if ($dados['codigo_fechamento_contabil'] != '') {
                $this->codigo_fechamento_contabil = model_id($dados['codigo_fechamento_contabil']);
            } else {
                $this->codigo_fechamento_contabil = null;
            }
        } else {
            $this->codigo_fechamento_contabil = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->mes_referencia = (int) (isset($dados['mes_referencia']) ? (int) intval($dados['mes_referencia'], 10) : 0);
        $this->ano_referencia = (int) (isset($dados['ano_referencia']) ? (int) intval($dados['ano_referencia'], 10) : 0);
        $this->total_credito = (double) (isset($dados['total_credito']) ? (double) doubleval(str_replace(',', '.', $dados['total_credito'])) : 0);
        $this->total_debito = (double) (isset($dados['total_debito']) ? (double) doubleval(str_replace(',', '.', $dados['total_debito'])) : 0);
        $this->valor_resultado = (double) (isset($dados['valor_resultado']) ? (double) doubleval(str_replace(',', '.', $dados['valor_resultado'])):0);
        $this->resultado = (string) (isset($dados['resultado']) ? (string) $dados['resultado'] : '');
        $this->data_fechamento = (isset($dados['data_fechamento']) ? model_date($dados['data_fechamento']) : model_date());
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        file_put_contents('json.json', json_encode(['empresa' => $this->empresa, 'mes_referencia' => (int) $this->mes_referencia, 'ano_referencia' => (int) $this->ano_referencia, 'total_credito' => (float) $this->total_credito, 'total_debito' => (float) $this->total_debito, 'resultado' => (string) $this->resultado, 'data_fechamento' => $this->data_fechamento], JSON_UNESCAPED_UNICODE));

        if ($this->codigo_fechamento_contabil != null) {
            return (bool) model_update((string) $this->tabela(), ['_id', '===', $this->codigo_fechamento_contabil], (array) ['empresa' => $this->empresa, 'mes_referencia' => (int) $this->mes_referencia, 'ano_referencia' => (int) $this->ano_referencia, 'total_credito' => (float) $this->total_credito, 'total_debito' => (float) $this->total_debito, 'valor_resultado' => (double) $this->valor_resultado, 'resultado' => (string) $this->resultado, 'data_fechamento' => $this->data_fechamento]);
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'mes_referencia' => (int) $this->mes_referencia, 'ano_referencia' => (int) $this->ano_referencia, 'total_credito' => (float) $this->total_credito, 'total_debito' => (float) $this->total_debito, 'valor_resultado' => (double) $this->valor_resultado,'resultado' => (string) $this->resultado, 'data_fechamento' => $this->data_fechamento]);
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

    public function fazer_fechamento($dados)
    {
        $this->colocar_dados($dados);

        $data_inicial = (string) $this->ano_referencia . '-' . $this->mes_referencia . '-01';
        $data_final = (string) '';

        if ($this->mes_referencia == 4 || $this->mes_referencia == 6  || $this->mes_referencia == 9 || $this->mes_referencia == 11) {
            $data_final = (string) $this->ano_referencia . '-' . $this->mes_referencia . '-30';
        } else if ($this->mes_referencia == 2) {
            $data_final = (string) $this->ano_referencia . '-' . $this->mes_referencia . '-28';
        }else{
            $data_final = (string) $this->ano_referencia.'-'.$this->mes_referencia.'-31';
        }

        $objeto_movimentacao = new Movimentacao();

        $retorno_pesquisa_movimentacao = (array) $objeto_movimentacao->pesquisar_todos(['filtro' => (array) ['and' => [['empresa', '===', $this->empresa], ['data_lancamento', '>=', model_date($data_inicial, '00:00:00')], ['data_lancamento', '<=', model_date($data_final, '23:59:59')]]], 'ordenacao' => (array) ['data_lancamento' => (bool) true], 'limite' => (int) 0]);

        if(empty($retorno_pesquisa_movimentacao) == false){
            foreach($retorno_pesquisa_movimentacao as $movimentacao){
                if($movimentacao['tipo_lancamento'] == 'CREDITO'){
                    $this->total_credito = (double) arredondar($this->total_credito, '+', $movimentacao['valor_lancamento'], 2);
                }else{
                    $this->total_debito = (double) arredondar($this->total_debito, '+', $movimentacao['valor_lancamento'], 2);
                }
            }
        }

        if($this->total_credito > $this->total_debito){
            $this->resultado = (string) 'POSITIVO';
            $this->valor_resultado = (double) arredondar($this->total_credito, '-', $this->total_debito, 2);
        }else if($this->total_credito < $this->total_debito){
            $this->resultado = (string) 'NEGATIVO';
            $this->valor_resultado = (double) arredondar($this->total_debito, '-', $this->total_credito, 2);
        }else{
            $this->resultado = (string) 'NEUTRO';
        }

        $array_fechamento_contabil_geral = (array) ['empresa' => $this->empresa, 'ano_referencia' => (int) $this->ano_referencia, 'mes_referencia' => (int) $this->mes_referencia, 'total_credito' => (double) $this->total_credito, 'total_debito' => (double) $this->total_debito, 'valor_resultado' => (double) $this->valor_resultado, 'resultado' => (string) $this->resultado];

        return $this->salvar_dados($array_fechamento_contabil_geral);
    }

    public function excluir_fechamento($filtro){
        return (bool) model_delete((string) $this->tabela(), (array) $filtro); 
    }
}
