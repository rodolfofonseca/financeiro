<?php
function pesquisa_banco_aggregate($tabela, $pipeline)
{
  $classe = new DB();
  $retorno = $classe->connect($tabela);
  $connection = $classe->connection;

  return $connection->aggregate($pipeline, ['allowDiskUse' => TRUE]);
}

/**
 * Função responsável por realizar o arredondamento de cálculos
 * @param double $valor valor em reais
 * @param string $operacao que deseja realizar dentro da função! EX '*'
 * @param double $quantidade a quantidade do item
 * @param int $casas_decimais a quantidade de casas decimais que deseja que a função retorne 
 */
function arredondar($valor, $operacao = '', $quantidade = null, $casas_decimais = 2)
{
  //  converte a quantidade que foi recebida pelo parâmetro em float para garantir que estaja no tipo correto
  $valor = (double) doubleval($valor);
  $quantidade = (double) doubleval($quantidade);

  //Se a quantidade for maior que zero realiza o cálculo de acordo com a operação que foi recebida por parâmetro.
  if ($quantidade != null && $operacao != '') {
    //Realiza o cálculo de acordo com o parâmetro que foi recebido.
    if ($operacao == '*') {
      $valor = $quantidade * $valor;
    } else if ($operacao == '+') {
      $valor = $valor + $quantidade;
    } else if ($operacao == '-') {
      $valor = $valor - $quantidade;
    } else if ($operacao == '/') {
      $valor = $valor / $quantidade;
    }

    //O valor precisa ser formatado para calcular corretamente quando for por ex: 15.549888888880;
    $valor = (double) formatar_numero($valor, 4, '.', '');
  }
  // verifica se o valor será um inteiro, para retorná-lo de forma direta, sem tratamento
  if (mb_strpos(strval($valor), '.')) {
    $auxPrecisao = (int) 3;
    $auxComparacao = (double) 5 * pow(10, $auxPrecisao - 1);

    $ultimoPonto = strripos($valor, '.') + 1;
    $valor = substr($valor, 0, $ultimoPonto) . substr($valor, $ultimoPonto, 4); // <- deixo o número com 4 casas decimais, caso vier a mais ou a menos
    $numeroInteiro = str_pad($valor, ($casas_decimais + $auxPrecisao + $ultimoPonto), "0", STR_PAD_RIGHT);
    $numeroInteiro = (int) intval(str_replace('.', '', $numeroInteiro));

    $sobra = intval(substr(($numeroInteiro), -$auxPrecisao), 10);
    $numero = intval(substr(($numeroInteiro), 0, (strlen($numeroInteiro) - $auxPrecisao)), 10);

    if ($numero % 2 == 0) {
      if ($sobra > $auxComparacao) {
        $numero++;
      }
    } else {
      if ($sobra >= $auxComparacao) {
        $numero++;
      }
    }

    $numero = (double) round(($numero / pow(10, $casas_decimais)), intval($casas_decimais, 10));
    return doubleval($numero);
  }
  return $valor;
}

/** FORMATAR NÚMERO
 * - Responsável por alterar a formatação do valor passado
 * @param $numero Deve ser informado o número a ser formatado
 * @param $decimais Pode ser informado a quantidade de números decimais
 * @param $decimal Pode ser informado o separador do decimal
 * @param $milhar Pode ser informado o separador dos milhares
 */
function formatar_numero($numero, $decimais = 2, $decimal = ',', $milhar = '')
{
  $numero = (double) arredondar($numero, '', 0, $decimais);
  return (string) number_format($numero, $decimais, $decimal, $milhar);
}
?>