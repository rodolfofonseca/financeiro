<?php
require_once 'postgres.php';
/**
 * Função responsável por inserir um novo registro no banco de dados
 * @param string $tabela
 * @param array $dados
 * @param bool $return_type
 * @return mixed
 */
function model_insert(string $tabela, array $dados, bool $return_type = true)
{
  try {
    $colunas = implode(',', array_keys($dados));
    $placeholder = ':' . implode(', :', array_keys($dados));

    $sql = "INSERT INTO {$tabela} ({$colunas}) VALUES ({$placeholder})";

    $pdo = conectarPostgres();
    $stmt = $pdo->prepare($sql);

    foreach ($dados as $campo => $valor) {
      $stmt->bindValue(":{$campo}", $valor);
    }

    file_put_contents('json.json', json_encode(['sql' => $sql, 'dados' => $dados]));

    $sucesso = $stmt->execute();

    $id = null;

    if ($sucesso) {
      $id = $pdo->lastInsertId();
    }

    $stmt = null;
    $pdo = null;

    if ($return_type == true) {
      return (bool) true;
    } else {
      return [
        'status' => $sucesso,
        'id' => $id
      ];
    }
  } catch (PDOException $e) {

    $pdo = null;

    if ($return_type == true) {
      return (bool) false;
    } else {
      return [
        'status' => false,
        'id' => null,
        'erro' => $e->getMessage()
      ];
    }
  }
}

/**
 * Função responsável por fazer o update dos campos
 * @param string $tabela
 * @param array $filtro
 * @param array $dados
 * @param bool $return_type
 * @return mixed{erro: string, linhas_afetadas: int, status: bool|array{linhas_afetadas: int, status: bool}|bool}
 */
function model_update(string $tabela, array $filtro, array $dados, bool $return_type = true)
{
  try {
    $set = [];

    foreach ($dados as $campo => $valor) {
      $set[] = "{$campo} = :set_{$campo}";
    }

    $where_sql = [];
    $params = [];

    if (!empty($filtro['where'])) {
      foreach ($filtro['where'] as $i => $condicao) {
        list($campo, $operador, $valor) = $condicao;

        $operadores = [
          '==' => '=',
          '!=' => '<>',
          '>' => '>',
          '<' => '<',
          '>=' => '>=',
          '<=' => '<=',
          'LIKE' => 'LIKE',
          'IN' => 'IN'
        ];

        $operador_sql = $operadores[$operador] ?? '=';

        $param = "where_{$i}";

        $where_sql[] = "{$campo} {$operador_sql} :{$param}";
        $params[$param] = $valor;
      }
    }

    $sql = "UPDATE {$tabela} SET " . implode(', ', $set);

    if (!empty($where_sql)) {
      $sql .= " WHERE " . implode(' AND ', $where_sql);
    }

    $pdo = conectarPostgres();
    $stmt = $pdo->prepare($sql);

    foreach ($dados as $campo => $valor) {
      $stmt->bindValue(":set_{$campo}", $valor);
    }

    foreach ($params as $param => $valor) {
      $stmt->bindValue(":{$param}", $valor);
    }

    $sucesso = $stmt->execute();

    return $return_type ? (bool) $sucesso : ['status' => $sucesso, 'linhas_afetadas' => $stmt->rowCount()];

  } catch (PDOException $e) {
    return $return_type ? false : ['status' => false, 'erro' => $e->getMessage()];
  }
}

/**
 * Função responsável por pesquisar no banco de dados e retornar apenas um registro de acordo com os filtros que foram passados
 * @param string $tabela
 * @param array $filtro
 */
function model_one(string $tabela, array $filtro = [])
{
  try {

    $where_sql = [];
    $params = [];

    // file_put_contents('sql.json', json_encode($filtro));

    if (!empty($filtro['where'])) {

      foreach ($filtro['where'] as $i => $condicao) {

        list($campo, $operador, $valor) = $condicao;

        $operadores = [
          '==' => '=',
          '!=' => '<>',
          '>' => '>',
          '<' => '<',
          '>=' => '>=',
          '<=' => '<=',
          'LIKE' => 'LIKE'
        ];

        $operador_sql = $operadores[$operador] ?? '=';

        $param = "where_{$i}";

        $where_sql[] = "{$campo} {$operador_sql} :{$param}";
        $params[$param] = $valor;
      }
    }

    $sql = "SELECT * FROM {$tabela}";

    if (!empty($where_sql)) {
      $sql .= " WHERE " . implode(' AND ', $where_sql);
    }

    $sql .= " LIMIT 1";

    $pdo = conectarPostgres();
    $stmt = $pdo->prepare($sql);

    foreach ($params as $param => $valor) {
      $stmt->bindValue(":{$param}", $valor);
    }

    $stmt->execute();

    $retorno = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = null;
    $pdo = null;

    return $retorno ?: null;

  } catch (PDOException $e) {

    return null;
  }
}

/**
 * Função responsável por pesquisar todos os registro no banco de dados e retornar de acordo com a opção do usuário
 * @param string $tabela
 * @param array $filtro
 * @param array $ordenacao
 * @param mixed $limite
 * @return array
 */
function model_all(string $tabela, array $filtro = [], array $ordenacao = [], ?int $limite = null)
{
  try {
    $where_sql = [];
    $params = [];

    if (!empty($filtro['where'])) {

      foreach ($filtro['where'] as $i => $condicao) {

        list($campo, $operador, $valor) = $condicao;

        $operadores = [
          '==' => '=',
          '!=' => '<>',
          '>' => '>',
          '<' => '<',
          '>=' => '>=',
          '<=' => '<=',
          'LIKE' => 'LIKE'
        ];

        $operador_sql = $operadores[$operador] ?? '=';

        $param = "where_{$i}";

        $where_sql[] = "{$campo} {$operador_sql} :{$param}";
        $params[$param] = $valor;
      }
    }

    $sql = "SELECT * FROM {$tabela}";

    if (!empty($where_sql)) {
      $sql .= " WHERE " . implode(' AND ', $where_sql);
    }

    if (!empty($ordenacao)) {

      $order_by = [];

      foreach ($ordenacao as $ordem) {

        $campo = $ordem[0];
        $direcao = strtoupper($ordem[1]);

        $direcao = ($direcao === 'DESC') ? 'DESC' : 'ASC';

        $order_by[] = "{$campo} {$direcao}";
      }

      $sql .= " ORDER BY " . implode(', ', $order_by);
    }

    if (!empty($limite)) {
      $sql .= " LIMIT " . (int) $limite;
    }
    // file_put_contents('json.json', json_encode(['sql' => $sql]));
    $pdo = conectarPostgres();
    $stmt = $pdo->prepare($sql);

    foreach ($params as $param => $valor) {
      $stmt->bindValue(":{$param}", $valor);
    }

    $stmt->execute();

    $retorno = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = null;
    $pdo = null;

    return $retorno;

  } catch (PDOException $e) {

    return [];
  }
}

/**
 * Função responsável por pegar a data e a hora e converter no formmato aceito do banco de dados
 * @param string $data
 * @param string $hora
 * @return string 
 */
function model_date($data = '', $hora = '')
{
  date_default_timezone_set('America/Sao_Paulo');
  $objeto_data = new DateTime();

  if ($data == '') {
    $data = $objeto_data->format('Y-m-d');
  }

  if ($hora == '') {
    $hora = $objeto_data->format('H:i:s');
  }

  return (string) $data . ' ' . $hora;
}
?>