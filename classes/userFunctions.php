<?php
function pesquisa_banco_aggregate($tabela, $pipeline)
{
  $classe = new DB();
  $retorno = $classe->connect($tabela);
  $connection = $classe->connection;

  return $connection->aggregate($pipeline, ['allowDiskUse' => TRUE]);
}
?>