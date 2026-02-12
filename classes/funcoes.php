<?php
require_once 'Sistema/db.php';
include_once 'Mongo/Mongo.php';

function router_add($rota, $pagina) {
  $rota_atual = $_REQUEST['rota'] ?? 'index';
  if ($rota_atual == $rota) {
    call_user_func($pagina);
    exit;
  }
}

function model_insert($table, $data) {
  return (bool) DB::use($table)->insert($data);
}

function model_update($table, $condition, $data) {
  return DB::use($table)->update($condition, $data);
}

function model_delete($table, $condition) {
  return DB::use($table)->delete($condition);
}

function model_all($table, $condition = [], $order = [], $limit = 0) {
  return DB::use($table)->all($condition, $order, $limit);
}

function model_one($table, $condition = [], $order = []) {
  return DB::use($table)->one($condition, $order);
}

function model_check($table, $condition = []) {
  return (bool) DB::use($table)->one($condition);
}

function http_request($url, $data = []) {
  $curl = curl_init();

  curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $data
  ]);

  $retorno = curl_exec($curl);

  if (curl_errno($curl)) {
      $retorno = curl_error($curl);
  }

  curl_close($curl);

  return $retorno;
}

function model_date($date = null, $time = null, $inverter = false){
  date_default_timezone_set('America/Sao_Paulo');
  if (is_object($date)){// caso chegue uma data que já está como objeto
    return $date;
  }

  if($inverter == true){
    $explode = explode("/", $date);
    $date = $explode[2].'-'.$explode[1].'-'.$explode[0];
  }
  
  if ($date == ('' || null)){
    // $date = (string) date('d/m/Y');
    $date = (string) date('Y-m-d');
  }

  if ($time == ('' || null)){
    $time = (string) date('H:i:s');
  }

  $date_format = date_create_from_format('Y-m-dH:i:s', $date.$time, new DateTimeZone('UTC'));
  $timestamp = date_timestamp_get($date_format);
  return new MongoDB\BSON\UTCDateTime($timestamp * 1000);
}

function convert_date($date_time, $format = 'Y-m-d'){
  if ($date_time == ('' || null)){
    $date_time = new MongoDB\BSON\UTCDateTime;
  }
  return (string) $date_time->toDateTime()->format($format);
}

function model_id($string_id){
  return new MongoDB\BSON\ObjectId($string_id);
}
spl_autoload_register(function ($classe) {
    $arquivo = (string) str_replace('\\', '/', __DIR__ . DIRECTORY_SEPARATOR . $classe . '.php');
    if (is_readable($arquivo) === true) {
        include_once $arquivo;
    }
});
?>