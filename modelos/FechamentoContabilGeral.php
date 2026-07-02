<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';
require_once 'modelos/Movimentacao.php';

class FechamentoContabilGeral implements InterfaceModelo
{
    private int $codigo_fechamento_contabil;
    private int $empresa;
    private int $mes_referencia;
    private int $ano_referencia;
    private float $total_credito;
    private float $total_debito;
    private float $valor_resultado;
    private string $resultado;
    private string $data_fechamento;

    public function tabela()
    {
        return (string) 'fechamento_contabil_geral';
    }

    public function modelo()
    {
        return (array) [];
    }

    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_fechamento_contabil', $dados) == true) {
            if ($dados['codigo_fechamento_contabil'] != 0) {
                $this->codigo_fechamento_contabil = $dados['codigo_fechamento_contabil'];
            } else {
                $this->codigo_fechamento_contabil = 0;
            }
        } else {
            $this->codigo_fechamento_contabil = 0;
        }

        $this->empresa = (int) (isset($dados['empresa']) ? intval($dados['empresa'], 10) : 0);
        $this->mes_referencia = (int) (isset($dados['mes_referencia']) ? (int) intval($dados['mes_referencia'], 10) : 0);
        $this->ano_referencia = (int) (isset($dados['ano_referencia']) ? (int) intval($dados['ano_referencia'], 10) : 0);
        $this->total_credito = (double) (isset($dados['total_credito']) ? (double) doubleval(str_replace(',', '.', $dados['total_credito'])) : 0);
        $this->total_debito = (double) (isset($dados['total_debito']) ? (double) doubleval(str_replace(',', '.', $dados['total_debito'])) : 0);
        $this->valor_resultado = (double) (isset($dados['valor_resultado']) ? (double) doubleval(str_replace(',', '.', $dados['valor_resultado'])) : 0);
        $this->resultado = (string) (isset($dados['resultado']) ? (string) $dados['resultado'] : '');
        $this->data_fechamento = (isset($dados['data_fechamento']) ? model_date($dados['data_fechamento']) : model_date());

        // $this->empresa = (isset($dados['codigo_empresa']) ? intval($dados['codigo_empresa'], 10):0);
    }

    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        // file_put_contents('dados_var.json', json_encode(['dados' => $dados, 'variavel' => $this->empresa, 'montado' => $this->montar_array()]));

        if ($this->codigo_fechamento_contabil != 0) {
            return (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_fechamento_contabil', '=', $this->codigo_fechamento_contabil]]], (array) $this->montar_array());
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

    /**
     * Função responsável por fazer o calculo do fechamento contábil.
     * @param mixed $dados
     * @return bool
     */
    public function fazer_fechamento($dados)
    {
        $this->colocar_dados($dados);        

        $data_final = (string) '';
        $data_inicial = (string) '';

        if($this->mes_referencia < 10){
            $data_inicial = (string) $this->ano_referencia . '-0' . $this->mes_referencia . '-01';
        }else{
            $data_inicial = (string) $this->ano_referencia . '-' . $this->mes_referencia . '-01';
        }

        if ($this->mes_referencia == 4 || $this->mes_referencia == 6 || $this->mes_referencia == 9 || $this->mes_referencia == 11) {
            if($this->mes_referencia < 10){
                $data_final = (string) $this->ano_referencia . '-0' . $this->mes_referencia . '-30';
            }else{
                $data_final = (string) $this->ano_referencia . '-' . $this->mes_referencia . '-30';
            }
        } else if ($this->mes_referencia == 2) {
            $data_final = (string) $this->ano_referencia . '-0' . $this->mes_referencia . '-28';
        } else if($this->mes_referencia <10){
            $data_final = (string) $this->ano_referencia . '-0' . $this->mes_referencia . '-31';
        }

        $objeto_movimentacao = new Movimentacao();
        
        $filtro = (array) ['filtro' => ['where' => [['codigo_empresa', '==', $this->empresa], ['data_lancamento', '>=', model_date($data_inicial, '00:00:00')], ['data_lancamento', '<=', model_date($data_final, '23:59:59')]]], 'ordenacao' => [], 'limite' => (int) 0];
        $retorno_pesquisa_movimentacao = (array) $objeto_movimentacao->pesquisar_todos($filtro);

        if (empty($retorno_pesquisa_movimentacao) == false) {
            foreach ($retorno_pesquisa_movimentacao as $movimentacao) {
                if ($movimentacao['tipo_lancamento'] == true) {
                    $this->total_credito = (double) arredondar($this->total_credito, '+', $movimentacao['valor_lancamento'], 2);
                } else if ($movimentacao['tipo_lancamento'] == false) {
                    $this->total_debito = (double) arredondar($this->total_debito, '+', $movimentacao['valor_lancamento'], 2);
                }
            }
        }

        if ($this->total_credito > $this->total_debito) {
            $this->resultado = (string) 'POSITIVO';
            $this->valor_resultado = (double) arredondar($this->total_credito, '-', $this->total_debito, 2);
        } else if ($this->total_credito < $this->total_debito) {
            $this->resultado = (string) 'NEGATIVO';
            $this->valor_resultado = (double) arredondar($this->total_credito, '-', $this->total_debito, 2);
        } else {
            $this->resultado = (string) 'NEUTRO';
        }

        return (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
    }

    /**
     * Função responsável por excluir o fechamento contábil
     * @param mixed $filtro
     * @return bool
     */
    public function excluir_fechamento($filtro)
    {
        return (bool) model_delete((string) $this->tabela(), (array) $filtro);
    }

    public function montar_array(){
        $dados = [];

        if($this->empresa != 0){
            $dados['codigo_empresa'] = (int) $this->empresa;
        }

        if($this->mes_referencia != 0){
            $dados['mes_referencia'] = (int) $this->mes_referencia;
        }

        if($this->ano_referencia != 0){
            $dados['ano_referencia'] = (int) $this->ano_referencia;
        }

        if($this->total_credito != 0){
            $dados['total_credito'] = (float) $this->total_credito;
        }

        if($this->total_debito != 0){
            $dados['total_debito'] = (float) $this->total_debito;
        }

        if($this->valor_resultado != 0){
            $dados['valor_resultado'] = (float) $this->valor_resultado;
        }

        if($this->resultado != ''){
            $dados['resultado'] = (string) $this->resultado;
        }

        if($this->data_fechamento != ''){
            $dados['data_fechamento'] = (string) $this->data_fechamento;
        }
    
        return (array) $dados;
    }
}
