<?php

require_once 'postgres.php';

/**
 * Valida identificadores SQL (tabelas e colunas)
 */
function sql_identifier(string $identifier): string
{
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
        throw new InvalidArgumentException(
            "Identificador SQL inválido: {$identifier}"
        );
    }

    return $identifier;
}

/**
 * Operadores permitidos
 */
function sql_operator(string $operator): string
{
    $operators = [
        '==' => '=',
        '===' => '=',
        '=' => '=',
        '!=' => '<>',
        '<>' => '<>',
        '>' => '>',
        '<' => '<',
        '>=' => '>=',
        '<=' => '<=',
        'LIKE' => 'LIKE'
    ];

    if (!isset($operators[$operator])) {
        throw new InvalidArgumentException(
            "Operador inválido: {$operator}"
        );
    }

    return $operators[$operator];
}

/**
 * Monta WHERE
 */
function sql_build_where(array $filtro = []): array
{
    $where = [];
    $params = [];

    if (empty($filtro['where'])) {
        return [
            'sql' => '',
            'params' => []
        ];
    }

    foreach ($filtro['where'] as $i => $condicao) {

        [$campo, $operador, $valor] = $condicao;

        $campo = sql_identifier($campo);
        $operador = sql_operator($operador);

        $param = "where_{$i}";

        $where[] = "{$campo} {$operador} :{$param}";
        $params[$param] = $valor;
    }

    return [
        'sql' => ' WHERE ' . implode(' AND ', $where),
        'params' => $params
    ];
}

/**
 * Executa query preparada
 */
function sql_execute(string $sql, array $params = [])
{
    $pdo = conectarPostgres();

    $stmt = $pdo->prepare($sql);

    foreach ($params as $param => $valor) {

        if (is_bool($valor)) {

            $stmt->bindValue(":{$param}", $valor, PDO::PARAM_BOOL);

        } elseif (is_int($valor)) {

            $stmt->bindValue(":{$param}", $valor, PDO::PARAM_INT);

        } elseif (is_float($valor)) {

            $stmt->bindValue(":{$param}", (string) arredondar($valor), PDO::PARAM_STR);

        } elseif (is_null($valor)) {

            $stmt->bindValue(":{$param}", null, PDO::PARAM_NULL);

        } else {

            $stmt->bindValue(":{$param}", $valor, PDO::PARAM_STR);

        }
    }

    $stmt->execute();

    return [
        'pdo' => $pdo,
        'stmt' => $stmt
    ];
}

/**
 * INSERT
 */
function model_insert(
    string $tabela,
    array $dados,
    bool $return_type = true
) {
    try {

        $tabela = sql_identifier($tabela);

        $colunas = [];
        $placeholders = [];

        foreach ($dados as $campo => $valor) {
            $colunas[] = sql_identifier($campo);
            $placeholders[] = ':' . $campo;
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s);',
            $tabela,
            implode(', ', $colunas),
            implode(', ', $placeholders)
        );

        // file_put_contents('model_insert.json', json_encode(['sql' => $sql, 'dados' => $dados]));

        $exec = sql_execute($sql, $dados);

        $status = true;

        if ($return_type) {
            return $status;
        }

        return [
            'status' => $status
        ];

    } catch (Throwable $e) {

        if ($return_type) {
            return false;
        }

        return [
            'status' => false,
            'erro' => $e->getMessage()
        ];
    }
}

/**
 * UPDATE
 */
function model_update(
    string $tabela,
    array $filtro,
    array $dados,
    bool $return_type = true
) {
    try {

        $tabela = sql_identifier($tabela);

        $set = [];
        $params = [];

        foreach ($dados as $campo => $valor) {

            $campo = sql_identifier($campo);

            $set[] = "{$campo} = :set_{$campo}";
            $params["set_{$campo}"] = $valor;
        }

        $where = sql_build_where($filtro);

        $sql = sprintf(
            'UPDATE %s SET %s%s',
            $tabela,
            implode(', ', $set),
            $where['sql']
        );

        $params = array_merge(
            $params,
            $where['params']
        );
        // file_put_contents('json.json', json_encode(['sql' => $sql, 'dados' => $dados, 'filtro' => $filtro]));

        $exec = sql_execute($sql, $params);

        $status = true;
        $linhas = $exec['stmt']->rowCount();

        if ($return_type) {
            return $status;
        }

        return [
            'status' => $status,
            'linhas_afetadas' => $linhas
        ];

    } catch (Throwable $e) {

        if ($return_type) {
            return false;
        }

        return [
            'status' => false,
            'erro' => $e->getMessage()
        ];
    }
}

/**
 * Retorna um registro
 */
function model_one(
    string $tabela,
    array $filtro = []
): ?array {
    try {

        $tabela = sql_identifier($tabela);

        $where = sql_build_where($filtro);

        $sql = "SELECT * FROM {$tabela}{$where['sql']} LIMIT 1";

        // file_put_contents('json.json', json_encode(['sql' => $sql, 'filtro' => $filtro]));

        $exec = sql_execute(
            $sql,
            $where['params']
        );

        $dados = $exec['stmt']->fetch(PDO::FETCH_ASSOC);

        return $dados ?: null;

    } catch (Throwable $e) {

        return null;
    }
}

/**
 * Retorna vários registros
 */
function model_all(
    string $tabela,
    array $filtro = [],
    array $ordenacao = [],
    ?int $limite = null
): array {
    try {

        $tabela = sql_identifier($tabela);

        $where = sql_build_where($filtro);

        $sql = "SELECT * FROM {$tabela}{$where['sql']}";

        if (!empty($ordenacao)) {

            $orders = [];

            foreach ($ordenacao as $ordem) {

                $campo = sql_identifier($ordem[0]);

                $direcao = strtoupper(
                    $ordem[1] ?? 'ASC'
                );

                $direcao = $direcao === 'DESC'
                    ? 'DESC'
                    : 'ASC';

                $orders[] = "{$campo} {$direcao}";
            }

            $sql .= ' ORDER BY ' . implode(', ', $orders);
        }

        if ($limite !== null && $limite > 0) {
            $sql .= ' LIMIT ' . (int) $limite;
        }
        // file_put_contents('json.json', json_encode(['sql' => $sql, 'dados' => $filtro]));
        $exec = sql_execute(
            $sql,
            $where['params']
        );

        return $exec['stmt']->fetchAll(
            PDO::FETCH_ASSOC
        );

    } catch (Throwable $e) {

        return [];
    }
}

/**
 * Verifica existência
 */
function model_check(
    string $tabela,
    array $filtro = []
): bool {
    try {

        $tabela = sql_identifier($tabela);

        $where = sql_build_where($filtro);

        $sql = "SELECT EXISTS (
                    SELECT 1
                    FROM {$tabela}
                    {$where['sql']}
                )";

        $exec = sql_execute(
            $sql,
            $where['params']
        );

        return (bool) $exec['stmt']->fetchColumn();

    } catch (Throwable $e) {

        return false;
    }
}

/**
 * Data atual no padrão PostgreSQL
 */
function model_date(
    string $data = '',
    string $hora = ''
): string {
    date_default_timezone_set('America/Sao_Paulo');

    $now = new DateTime();

    $data = $data ?: $now->format('Y-m-d');
    $hora = $hora ?: $now->format('H:i:s');

    return "{$data} {$hora}";
}

function model_delete(
    string $tabela,
    array $filtro,
    bool $return_type = true
) {
    try {

        $tabela = sql_identifier($tabela);

        $where = sql_build_where($filtro);

        if (empty($where['sql'])) {
            throw new InvalidArgumentException(
                'DELETE sem cláusula WHERE não é permitido.'
            );
        }

        $sql = "DELETE FROM {$tabela}{$where['sql']}";

        // file_put_contents('json.json', json_encode(['sql' => $sql, 'param' => $filtro]));

        $exec = sql_execute(
            $sql,
            $where['params']
        );

        $linhas_afetadas = $exec['stmt']->rowCount();

        if ($return_type) {
            return true;
        }

        return [
            'status' => true,
            'linhas_afetadas' => $linhas_afetadas
        ];

    } catch (Throwable $e) {

        if ($return_type) {
            return false;
        }

        return [
            'status' => false,
            'erro' => $e->getMessage()
        ];
    }
}

/**
 * Função responsável por realizar o count, retornando a quantidade de itens que encontrou de acordo com o filtro passado
 * @param string $tabela
 * @param array $filtro
 * @param string $campo
 * @throws Exception
 * @return int
 */
function model_count(string $tabela, array $filtro = [], string $campo = '*'): int
{
    try {

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabela)) {
            throw new Exception('Nome da tabela inválido.');
        }


        if ($campo !== '*' && !preg_match('/^[a-zA-Z0-9_]+$/', $campo)) {
            throw new Exception('Nome do campo inválido.');
        }

        $where_sql = [];
        $params = [];

        if (!empty($filtro['where'])) {

            foreach ($filtro['where'] as $i => $condicao) {

                [$campo_where, $operador, $valor] = $condicao;

                if (!preg_match('/^[a-zA-Z0-9_]+$/', $campo_where)) {
                    throw new Exception("Campo '{$campo_where}' inválido.");
                }

                $param = "w{$i}";

                switch (strtolower($operador)) {

                    case '=':
                    case '==':
                        $where_sql[] = "{$campo_where} = :{$param}";
                        $params[$param] = $valor;
                        break;

                    case '!=':
                    case '<>':
                        $where_sql[] = "{$campo_where} <> :{$param}";
                        $params[$param] = $valor;
                        break;

                    case '>':
                    case '>=':
                    case '<':
                    case '<=':
                        $where_sql[] = "{$campo_where} {$operador} :{$param}";
                        $params[$param] = $valor;
                        break;

                    case 'like':
                        $where_sql[] = "{$campo_where} ILIKE :{$param}";
                        $params[$param] = $valor;
                        break;

                    case 'in':

                        if (!is_array($valor) || empty($valor)) {
                            throw new Exception("Operador IN requer um array.");
                        }

                        $placeholders = [];

                        foreach ($valor as $k => $v) {
                            $in_param = "{$param}_{$k}";
                            $placeholders[] = ":{$in_param}";
                            $params[$in_param] = $v;
                        }

                        $where_sql[] = "{$campo_where} IN (" . implode(',', $placeholders) . ")";
                        break;

                    case 'is null':
                        $where_sql[] = "{$campo_where} IS NULL";
                        break;

                    case 'is not null':
                        $where_sql[] = "{$campo_where} IS NOT NULL";
                        break;

                    default:
                        throw new Exception("Operador '{$operador}' não suportado.");
                }
            }
        }

        $sql = "SELECT COUNT({$campo}) AS total
                FROM {$tabela}";

        if (!empty($where_sql)) {
            $sql .= ' WHERE ' . implode(' AND ', $where_sql);
        }

        $pdo = conectarPostgres();

        $stmt = $pdo->prepare($sql);

        foreach ($params as $param => $valor) {
            $stmt->bindValue(":{$param}", $valor);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();

    } catch (Throwable $e) {

        error_log($e->getMessage());

        return 0;
    }
}

/**
 * Função responsável por pesquisar a query direto
 * @param string $sql
 * @param array $params
 * @return array
 */
function model_query(string $sql, array $params = []): array
{
    try {

        $pdo = conectarPostgres();

        $stmt = $pdo->prepare($sql);

        foreach ($params as $param => $valor) {

            if (is_bool($valor)) {
                $stmt->bindValue(":{$param}", $valor, PDO::PARAM_BOOL);

            } elseif (is_int($valor)) {
                $stmt->bindValue(":{$param}", $valor, PDO::PARAM_INT);

            } elseif (is_float($valor)) {
                $stmt->bindValue(":{$param}", (string)$valor, PDO::PARAM_STR);

            } elseif (is_null($valor)) {
                $stmt->bindValue(":{$param}", null, PDO::PARAM_NULL);

            } else {
                $stmt->bindValue(":{$param}", $valor, PDO::PARAM_STR);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Throwable $e) {

        error_log($e->getMessage());

        return [];
    }
}

/**
 * Função responsávle por converter o campo data que vem do banco de dados para o formato brasileiro
 * @param mixed $data
 * @param mixed $hora
 * @return string
 */
function convert_date($data, $hora = false){
    $data = new DateTime($data);

    if($hora == true){
        return $data->format('d/m/Y H:i:s');
    }else{
        return $data->format('d/m/Y');
    }
}