<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Interface.php';

class Extratos implements InterfaceModelo
{private $codigo_extrato;
private $empresa;
private $usuario;
private $total_bruto;
private $valor_entrada;
private $total_desconto;
private $valor_liquido;
private $data_extrato;
private $data_pagamento;
private $status_extrato;
public function tabela()
{
    return (string) 'extrato';
}

public function colocar_dados($dados)
{
    if(array_key_exists('codigo_extrato', $dados) == true){
        if($dados['codigo_extrato'] != ''){
            $this->codigo_extrato = model_id($dados['codigo_extrato']);
        }else{
            $this->codigo_extrato = null;
        }
    }else{
        $this->codigo_extrato = null;
    }

    $this->empresa = (isset($dados['empresa']) ? model_id($dados['empresa']):'');
    $this->usuario = (isset($dados['usuario']) ? model_id($dados['usuario']):'');
    $this->total_bruto = (double) (isset($dados['total_bruto']) ? (double) doubleval(str_replace(',', '.', $dados['total_bruto'])):0);
    $this->valor_entrada = (double) (isset($dados['valor_entrada']) ? (double) doubleval(str_replace(',', '.', $dados['valor_entrada'])):0);
    $this->total_desconto = (double) (isset($dados['total_desconto']) ? (double) doubleval(str_replace(',', '.', $dados['total_desconto'])):0);
    $this->valor_liquido = (double) (isset($dados['valor_liquido']) ? (double) doubleval(str_replace(',', '.', $dados['valor_liquido'])):0);
    $this->data_extrato = (isset($dados['data_extrato']) ? model_date($dados['data_extrato']):model_date());
    $this->data_pagamento = (isset($dados['data_pagamento']) ? model_date($dados['data_pagamento']):model_date());
    $this->status_extrato = (string) (isset($dados['status_extrato']) ? (string) $dados['status_extrato']:'AGUARDANDO');
}

public function salvar_dados($dados)
{
    $this->colocar_dados($dados);

    $retorno_operacao = (bool) false;

    if($this->codigo_extrato != null){
        $retorno_operacao = (bool) model_update((string) $this->tabela(), (array) ['_id', '===', $this->codigo_extrato], (array) ['empresa' => $this->empresa, 'usuario' => $this->usuario, 'data_extrato' => $this->data_extrato, 'data_pagamento' => $this->data_pagamento, 'valor_bruto' => (double) $this->total_bruto, 'valor_entrada' => (double) $this->valor_entrada,'valor_desconto' => (double) $this->total_desconto, 'valor_liquido' => (double) $this->valor_liquido, 'status' => (string) $this->status_extrato]);
    }else{
        $retorno_operacao = (bool) model_insert((string) $this->tabela(), (array) ['empresa' => $this->empresa, 'usuario' => $this->usuario, 'data_extrato' => $this->data_extrato, 'data_pagamento' => $this->data_pagamento, 'valor_bruto' => (double) $this->total_bruto, 'valor_entrada' => (double) $this->valor_entrada,'valor_desconto' => (double) $this->total_desconto, 'valor_liquido' => (double) $this->valor_liquido, 'status' => (string) $this->status_extrato]);
    }

    $retorno_pesquisa_extrato = (array) [];
    if($retorno_operacao == true){
        if($this->codigo_extrato == null){
            $retorno_pesquisa_extrato = (array) $this->pesquisar((array) ['filtro' => (array) ['and' => (array) [(array) ['empresa', '===', $this->empresa], (array) ['usuario', '===', $this->usuario], (array) ['data_extrato', '===', $this->data_extrato]]]]);
        }else{
            $retorno_pesquisa_extrato = (array) $this->pesquisar((array) ['filtro' => (array) ['_id', '===', $this->codigo_extrato]]);
        }
    }        

    return (array) $retorno_pesquisa_extrato;
}

public function pesquisar($filtro)
{
    return (array) model_one((string) $this->tabela(), (array) $filtro['filtro']);
}

public function pesquisar_todos($filtro)
{
    return (array) model_all((string) $this->tabela(), (array) $filtro['filtro'], (array) $filtro['ordenacao'], (int) $filtro['limite']);
}
}
