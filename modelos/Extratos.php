<?php
require_once 'classes/bancoDeDados.php';
require_once 'classes/CodigoBarras/EAN13.php';

require_once 'modelos/Interface.php';
require_once 'modelos/ContasPagarReceber.php';
require_once 'modelos/ContasFornecedores.php';
require_once 'modelos/Usuario.php';

class Extratos implements InterfaceModelo
{
    private int $codigo_extrato;
    private int $empresa;
    private int $usuario;
    private float $total_bruto;
    private float $valor_entrada;
    private float $total_desconto;
    private float $valor_liquido;
    private mixed $data_extrato;
    private mixed $data_pagamento;
    private string $status_extrato;
    public function tabela()
    {
        return (string) 'extrato';
    }

    public function modelo()
    {
        return (array) [];
    }

    /**
     * Função responsável por colcoar os dados nas variáveis correspondentes, fazendo as devidas validações
     * @param array $dados
     * @return void
     */
    public function colocar_dados($dados)
    {
        if (array_key_exists('codigo_extrato', $dados) == true) {
            if ($dados['codigo_extrato'] != 0) {
                $this->codigo_extrato = (int) intval($dados['codigo_extrato']);
            } else {
                $this->codigo_extrato = 0;
            }
        } else {
            $this->codigo_extrato = 0;
        }

        $this->empresa = (int) (isset($dados['empresa']) ? (int) intval($dados['empresa'], 10) : 0);
        $this->usuario = (int) (isset($dados['usuario']) ? (int) intval($dados['usuario'], 10) : 0);
        $this->total_bruto = (double) (isset($dados['total_bruto']) ? (double) doubleval(str_replace(',', '.', $dados['total_bruto'])) : 0);
        $this->valor_entrada = (double) (isset($dados['valor_entrada']) ? (double) doubleval(str_replace(',', '.', $dados['valor_entrada'])) : 0);
        $this->total_desconto = (double) (isset($dados['total_desconto']) ? (double) doubleval(str_replace(',', '.', $dados['total_desconto'])) : 0);
        $this->valor_liquido = (double) (isset($dados['valor_liquido']) ? (double) doubleval(str_replace(',', '.', $dados['valor_liquido'])) : 0);
        $this->data_extrato = (isset($dados['data_extrato']) ? model_date($dados['data_extrato']) : model_date());
        $this->data_pagamento = (isset($dados['data_pagamento']) ? model_date($dados['data_pagamento']) : model_date());
        $this->status_extrato = (string) (isset($dados['status_extrato']) ? (string) $dados['status_extrato'] : 'AGUARDANDO');
    }

    /**
     * Função responsável por salvar os daados do extrato no banco de dados
     * @param array $dados
     * @return array
     */
    public function salvar_dados($dados)
    {
        $this->colocar_dados($dados);

        $retorno_operacao = (bool) false;

        if ($this->codigo_extrato != 0) {
            $retorno_operacao = (bool) model_update((string) $this->tabela(), (array) ['where' => [['codigo_extrato', '=', $this->codigo_extrato]]], (array) $this->montar_array());
        } else {
            $retorno_operacao = (bool) model_insert((string) $this->tabela(), (array) $this->montar_array());
        }

        $retorno_pesquisa_extrato = (array) [];
        if ($retorno_operacao == true) {
            if ($this->codigo_extrato == null) {
                $retorno_pesquisa_extrato = (array) $this->pesquisar((array) ['filtro' => (array) ['where' => (array) [(array) ['codigo_empresa', '=', $this->empresa], (array) ['codigo_usuario', '=', $this->usuario], (array) ['data_extrato', '=', $this->data_extrato]]]]);
            } else {
                $retorno_pesquisa_extrato = (array) $this->pesquisar((array) ['filtro' => (array) [['codigo_extrato', '=', $this->codigo_extrato]]]);
            }
        }

        return (array) $retorno_pesquisa_extrato;
    }

    /**
     * Função responsável por pesquisar o extrato no banco de dados, de acordo com o filtro passado.
     * @param array $filtro
     * @return array
     */
    public function pesquisar($filtro)
    {
        return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
    }

    /**
     * Função responsável por pesquisar todos os extratos no banco de dados, de acordo com o filtro passado.
     * @param array $filtro
     * @return array
     */
    public function pesquisar_todos($filtro)
    {
        return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
    }

    /**
     * Função responsável por baixar o extrato no banco de dados, e criar a conta para que seja pago
     * @param array $dados
     * @return array
     */
    public function baixar_extrato($dados)
    {
        // $this->colocar_dados($dados);

        // $extrato = (array) $this->pesquisar((array) ['filtro' => (array) ['where' => [['codigo_extrato', '=', $this->codigo_extrato]]]]);

        // if (empty($extrato) == false) {
        //     $objeto_conta_fornecedor = new ContasFornecedores();
        //     $objeto_conta_pagar_receber = new ContasPagarReceber();
        //     $objeto_usuario = new Usuario();
        //     $objeto_codigo_barras = new EAN13();

        //     $conta_fornecedor = (array) $objeto_conta_fornecedor->pesquisar((array) ['filtro' => (array) ['fornecedor', '=', $extrato['usuario']]]);
        //     $usuario = (array) $objeto_usuario->pesquisar((array) ['filtro' => (array) ['_id', '===', $extrato['usuario']]]);

        //     if (empty($usuario) == false) {
        //         if (empty($conta_fornecedor) == true) {
        //             $array_cadastro_conta_fornecedor = (array) ['empresa' => $extrato['empresa'], 'fornecedor' => $extrato['usuario'], 'data_cadastro' => model_date(), 'status_conta' => (bool) true, 'nome_conta' => (string) 'EXTRATO - ' . $usuario['nome_usuario'], 'descricao_conta' => (string) 'CONTA CRIADA AUTOMATICAMENTE PARA O FUNCIONÁRIO ' . $usuario['nome_usuario']];

        //             $retorno_cadastro_conta_fornecedor = (bool) $objeto_conta_fornecedor->salvar_dados($array_cadastro_conta_fornecedor);

        //             if ($retorno_cadastro_conta_fornecedor == true) {
        //                 $conta_fornecedor = (array) $objeto_conta_fornecedor->pesquisar((array) ['filtro' => (array) ['fornecedor', '===', $extrato['usuario']]]);
        //                 if (empty($conta_fornecedor) == true) {
        //                     return (array) ['status' => (bool) false];
        //                 }
        //             } else {
        //                 return (array) ['status' => (bool) false];
        //             }
        //         }

        //         $transacao = (string) $objeto_codigo_barras->getFullCode('');

        //         $array_cadastro_conta_pagar_receber = (array) ['empresa' => $extrato['empresa'], 'cliente_fornecedor' => $usuario['_id'], 'conta_fornecedor' => $conta_fornecedor['_id'], 'nome_conta' => (string) 'EXTRATO ' . $usuario['nome_usuario'], 'descricao' => (string) 'CONTA GERADA AUTOMÁTICAMENTE ATRAVÉS DO EXTRATO', 'valor_conta' => (float) $extrato['valor_liquido'], 'valor_pago' => (float) 0, 'valor_juro_desconto' => (float) 0, 'tipo_juro_desconto' => (string) '', 'tipo_conta' => (string) 'PAGAR', 'data_cadastro' => model_date(), 'data_vencimento' => $extrato['data_extrato'], 'data_baixa' => model_date(), 'status_conta' => (string) 'AGUARDANDO', 'comprovante' => (string) 'NAO', 'boleto' => (string) 'NAO', 'transacao' => (string) $transacao];

        //         $retorno_conta_pagar_receber = (bool) $objeto_conta_pagar_receber->salvar_dados($array_cadastro_conta_pagar_receber);

        //         if ($retorno_conta_pagar_receber == true) {
        //             $array_udpate_extrato = (array) ['status' => (string) 'PAGO'];
        //             return (array) ['status' => (bool) model_update((string) $this->tabela(), ['_id', '===', $this->codigo_extrato], (array) $array_udpate_extrato)];
        //         } else {
        //             return (array) ['status' => (bool) false];
        //         }
        //     } else {
        //         return (array) ['status' => (bool) false];
        //     }
        // } else {
        //     return (array) ['status' => (bool) false];
        // }
        return (array) ['status' => (bool) false];
    }

    public function montar_array(){
        $dados = (array) [];

        if($this->empresa != 0){
            $dados['codigo_empresa'] = (int) $this->empresa;
        }

        if($this->usuario != 0){
            $dados['codigo_usuario'] = (int) $this->usuario;
        }

        if($this->total_bruto != 0){
            $dados['valor_bruto'] = (float) $this->total_bruto;
        }

        if($this->valor_entrada != 0){
            $dados['valor_entrada'] = (float) $this->valor_entrada;
        }

        if($this->total_desconto != 0){
            $dados['valor_desconto'] = (float) $this->total_desconto;
        }

        if($this->valor_liquido != 0){
            $dados['valor_liquido'] = (float) $this->valor_liquido;
        }

        if($this->data_extrato != ''){
            $dados['data_extrato'] = (string) $this->data_extrato;
        }

        if($this->data_pagamento){
            $dados['data_pagamento'] = (string) $this->data_pagamento;
        }

        if($this->status_extrato != ''){
            $dados['status'] = (string) $this->status_extrato;
        }

        return (array) $dados;
    }
}
