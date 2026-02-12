<?php
class DB {
  public static $instance = null;
  public $client = null;
  public $connection = null;
  public $filters = null;
  public $db = 'sistema';
  public $table = null;
  public static $settings = [
    'dns' => 'mongodb://127.0.0.1/',
    'authentication'=> [],
    'options' => [ 'typeMap' => [
      'array' => 'array',
      'document' => 'array',
      'root' => 'array'
    ]]
  ];

  static function use($table, $DB=null) {
    static $instance = null;

    if (null === $instance) {
      $instance = new static();
    }

    $instance->connect($table);

    return $instance;
  }

  static function is_filter($condition, $comparison) {
    if (is_array($condition) == true) {
      return false;
    }

    return array_key_exists($condition, $comparison);
  }

  static function filter($conditions=[]) {
    $query = [];
    $comparison = [
      '>' => '$gt',
      '>=' => '$gte',
      '<' => '$lt',
      '<=' => '$lte',
      '=' => '$regex',
	  '==' => '$regex',
      '===' => '$eq',
      '!=' => '$not',
      '!==' => '$not',
      '!===' => '$ne'
    ];
    $logical = [
      'and' => '$and',
      'or' => '$or'
    ];

    if ($conditions == null) {
      $conditions = [];
    }
    if (count($conditions) == 3 and self::is_filter($conditions[1], $comparison) == true) {
      if ($conditions[0] == '_id') {
        $conditions[2] = new MongoDB\BSON\ObjectId($conditions[2]);
      }

      if (($conditions[1] == '=') or ($conditions[1] == '!=')) {
        $conditions[2] = new MongoDB\BSON\Regex($conditions[2], 'i');
      }

      if (($conditions[1] == '==') or ($conditions[1] == '!==')) {
        $conditions[2] = new MongoDB\BSON\Regex($conditions[2]);
      }

      $query = [$conditions[0] => [$comparison[$conditions[1]] => $conditions[2]]];

    } else {
      foreach ($conditions as $chave => $condition) {
        if (array_key_exists($chave, $logical) == true) {
          $query[$logical[$chave]] = self::filter($condition);

        } else {
          if (count($condition) == 3) {
            if (is_string($condition[1]) == true) {
              if (count($condition) == 3 and array_key_exists($condition[1], $comparison) == true) {
                array_push($query, self::filter($condition));
              }
            }
          }
        }
      }
    }

    return $query;
  }

  static function order($order=[]) {
    $result = [];

    foreach ($order as $field => $value) {
      if ($value) {
        $result[$field] = 1;
      } else {
        $result[$field] = -1;
      }
    }

    return $result;
  }

  static function normalize_column($column) {
    return [$column => 1];
  }

  static function tables() {
    $i = self::use('empresa');
    $collections = [];
    $validacao = [];

    foreach ($i->client->{$i->db}->listCollections() as $collection) {
      $name = $collection['name'];
      if (isset($collection['options']['validator']['$jsonSchema']['properties']) == true) {
        $validacao = $collection['options']['validator']['$jsonSchema']['properties'];
      } elseif (isset($collection['options']['validator']) === true) {
        $validacao = $collection['options']['validator'];
      }

      if (empty($validacao) === false) {
        foreach ($validacao as $k => $v) {
          if (array_key_exists('$type', $v) == true) {
            $v = $v['$type'];
  
          } else if (array_key_exists('bsonType', $v) == true) {
            $v = $v['bsonType'];
  
          } else if (array_key_exists('type', $v) == true) {
            $v = $v['type'];
  
          } else if (array_key_exists(1, $v) == true) {
            $v = $v[1];
          }
          $collections[$name][$k] = $v;
        }
      }
    }

    return $collections;
  }

  function connect($table) {
    if ($this->client == null) {
      $arquivo_configuracao = (string) str_replace('\\', '/', __DIR__) . '/../../configuracao.ini';
      $dns = self::$settings['dns'];
      $authentication = self::$settings['authentication'];

      if (file_exists($arquivo_configuracao) == true) {
        $configuracao = (array) parse_ini_file($arquivo_configuracao, true);

        if (isset($configuracao['DB']['db']) == true) {
          $this->db = (string) $configuracao['DB']['db'];
        }

        if (isset($configuracao['DB']['dns']) == true) {
          $dns = (string) $configuracao['DB']['dns'];
        }

        if (isset($configuracao['DB']['username']) == true) {
          $authentication['username'] = (string) $configuracao['DB']['username'];
        }

        if (isset($configuracao['DB']['password']) == true) {
          $authentication['password'] = (string) $configuracao['DB']['password'];
        }
      }

      $this->client = (new MongoDB\Client(
        $dns,
        $authentication,
        self::$settings['options']
      ));
    }

    $this->connection = $this->client->selectCollection($this->db, $table);

    return $this;
  }

  function insert($columns) {
    foreach ($columns as $key => $value) :
      if (is_numeric($value)){
        continue;
      }else if(is_object($value)){
        $columns[$key] = $value;
      }else{
        $columns[$key] = str_replace(['"', "'"], "", $value);
      }
    endforeach;
    try {
      return $this
        ->connection
        ->insertOne($columns)
        ->getInsertedId();
    } catch (Exception $e) {
      return false;
    }
  }

  function update($filters, $columns=[]) {
    foreach ($columns as $key => $value) :
      if (is_numeric($value)){
        continue;
      }else if(is_object($value)){
        $columns[$key] = $value;
      }else{
        $columns[$key] = str_replace(['"', "'"], "", $value);
      }
    endforeach;
    try {
      return (bool) $this
        ->connection
        ->updateMany(self::filter($filters), ['$set' => $columns])
        ->getMatchedCount();
    } catch (Exception $e) {
      return false;
    }
  }

  function delete($filters=[]) {
    try {
      return (bool) $this
        ->connection
        ->deleteMany(self::filter($filters))
        ->getDeletedCount();
    } catch (Exception $e) {
      return false;
    }
  }

  function first($filters=[], $field=null) {
    if ($field == null) {
      $row = array_keys($this->one(self::filter($filters)));
      if (empty($row) == false) {
        $field = array_shift($row);
        $field = array_shift($row);
      }
    }

    if ($field == null) {
      $field = '_id';
    }

    return $this
      ->connection
      ->findOne(self::filter($filters), ['sort' => [$field => 1]]);
  }

  function last($filters=[], $field='_id') {
    return $this
      ->connection
      ->findOne(self::filter($filters), ['sort' => [$field => -1]]);
  }

  function one($filters=[], $order=[]) {
    $filtros = self::filter($filters);
    // $options = [
    //   'projection' => ['_id' => 1],
    //   'sort' => $this->order($order)
    // ];
    $options = ['sort' => $this->order($order)];
    $result = $this->connection->findOne($filtros, $options);
    
    if($result && isset($result['_id'])){
      $result['_id'] = (string) $result['_id'];
    }

    return $result;
  }

  /**
   * Função responsável por realizar a pesquisa de todas as informaçõs que contém na tabela de acordo com os parâmetros que são passados
   * @param array filters com os filtros que se deseja
   * @param array order array com a forma de ordenação que se deseja
   * @param int limit a quantidade que registros que deseja que seja retornada
   * @return array com os registros
   */
  function all($filters=[], $order=[], $limit = 0) {
    $filtros = self::filter($filters);

    if($limit == 0){
      $options = ['sort' => $this->order($order)];
    }else{
      $options = ['sort' => $this->order($order), 'limit' => $limit];
    }
    // if($limit == 0){
    //   $options = ['projection' => ['_id' => 0], 'sort' => $this->order($order)];
    // }else{
    //   $options = ['projection' => ['_id' => 0], 'sort' => $this->order($order), 'limit' => $limit];
    // }

    return $this->connection->find($filtros, $options)->toArray();
  }

  function columns($columns, $filters=[], $order=[]) {
    $filtros = self::filter($filters);
    $options = [
      'projection' => array_merge(array_fill_keys($columns, 1), ['_id' => 0]),
      'sort' => $this->order($order)
    ];

    return $this->connection->find($filtros, $options)->toArray();
  }

  function check($filters=[]) {
    return (bool) $this
      ->connection
      ->findOne(self::filter($filters), ['projection' => ['_id' => 1]]);
  }

  function next($field, $min=1) {
    $rs = $this
      ->connection
      ->findOne([], ['sort' => [$field => -1]]);

    return max($rs[$field] + 1, $min);
  }
}
?>