<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class CustoMedio implements InterfaceModelo
{
    private mixed $codigo_custo_medio;
    private mixed $empresa;
    private mixed $pedido;
    private mixed $itens_pedido;
    private mixed $produto;
    private mixed $data_custo;
    private mixed $valor_custo;
    private bool $ignorar;

    private float $valor_custo_parametro;

    function tabela()
    {
        return (string) 'custo_medio';
    }

    function modelo()
    {
        return (array) ['empresa' => 'objectId', 'pedido' => 'objectId', 'item_pedido' => 'objectId', 'produto' => 'objectId', 'data_custo' => 'date', 'valor_custo' => (double) 0, 'ignorar' => 'bool'];
    }

    /**
     * Função responsável por colcoar os dados nas variáveis correspondentes
     * @param array $dados
     * @return void
     */
    function colocar_dados($dados)
    {
        if (array_key_exists('codigo_custo_medio', $dados) == true) {
            if ($dados['codigo_custo_medio'] != '') {
                $this->codigo_custo_medio = model_id($dados['codigo_custo_medio']);
            } else {
                $this->codigo_custo_medio = null;
            }
        } else {
            $this->codigo_custo_medio = null;
        }

        $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']) : '');
        $this->pedido = (isset($dados['pedido']) ? model_id($dados['pedido']) : '');
        $this->itens_pedido = (isset($dados['item_pedido']) ? model_id($dados['item_pedido']) : '');
        $this->produto = (isset($dados['produto']) ? model_id($dados['produto']) : '');

        $this->data_custo = (isset($dados['data_custo']) ? model_date($dados['data_custo']) : model_date());
        $this->ignorar = (bool) (isset($dados['ignorar']) ? (bool) $dados['ignorar'] : false);

        $this->valor_custo_parametro = (double) (isset($dados['valor_custo_parametro']) ? (double) doubleval(str_replace(',', '.', $dados['valor_custo_parametro'])) : 0);
    }

    /**
     * Função resposável por salvar os dados do custo médio
     * @param array $dados
     * @return bool
     */
    function salvar_dados($dados)
    {
        $this->colocar_dados($dados);
        $this->calcular_custo_medio();

        if ($this->codigo_custo_medio != null) {
            return (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_custo_medio], (array) ['empresa' => $this->empresa, 'pedido' => $this->pedido, 'item_pedido' => $this->itens_pedido, 'produto' => $this->produto, 'data_custo' => $this->data_custo, 'valor_custo' => (double) $this->valor_custo, 'ignorar' => (bool) $this->ignorar]);
        } else {
            return (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'pedido' => $this->pedido, 'item_pedido' => $this->itens_pedido, 'produto' => $this->produto, 'data_custo' => $this->data_custo, 'valor_custo' => (double) $this->valor_custo, 'ignorar' => (bool) $this->ignorar]);
        }
    }

    /**
     * Função responsável por pesqusiar os dados de um custo médio específico
     * @param array $filtro
     * @return array
     */
    function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    /**
     * Summary of pesquisar_todos
     * @param array $filtro
     * @return array
     */
    function pesquisar_todos($filtro)
    {
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
    }

    /** 
     * Função responsável por realizar o calculo do custo médio do produto
     */
    public function calcular_custo_medio()
    {
        $filtro = (array) [];

        array_push($filtro, (array) ['empresa', '===', $this->empresa]);
        array_push($filtro, (array) ['produto', '===', $this->produto]);
        array_push($filtro, (array) ['data_custo', '<=', $this->data_custo]);
        array_push($filtro, (array) ['ignorar', '===', (bool) $this->ignorar]);

        $filtro_pesquisa = (array) ['filtro' => (array) ['and' => (array) $filtro], 'ordenacao' => (array) [], 'limite' => (int) 0];
        $retorno_pesquisa = $this->pesquisar_todos($filtro_pesquisa);

        $quantidade_itens_custo = (int) 0;
        $valor_custo_temp = (double) 0;

        if (empty($retorno_pesquisa) == false) {
            foreach ($retorno_pesquisa as $custo) {
                $valor_custo_temp = (double) arredondar((double) $valor_custo_temp, (string) '+', (double) $custo['valor_custo'], (int) 2);
                $quantidade_itens_custo++;
            }

            $valor_custo_temp = arredondar((double) $valor_custo_temp, (string) '+', (double) $this->valor_custo_parametro, (int) 2);
            $quantidade_itens_custo++;
        } else {
            $valor_custo_temp = (double) $this->valor_custo_parametro;
            $quantidade_itens_custo++;
        }

        if ($quantidade_itens_custo == 0 || $quantidade_itens_custo == 1) {
            $this->valor_custo = (double) $valor_custo_temp;
        } else {
            $this->valor_custo = (double) arredondar((double) $valor_custo_temp, (string) '/', (int) $quantidade_itens_custo, (int) 2);
        }
    }
}
?>