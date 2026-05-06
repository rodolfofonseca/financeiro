<?php
ignore_user_abort(true);
ini_set('memory_limit', '-1');
set_time_limit(0);
error_reporting(E_ALL & ~E_DEPRECATED);
$url = '';
define('URL', 'http://' . $url);
define('DIRETORIO_SISTEMA', str_replace('\\', '/', __DIR__));

date_default_timezone_set('America/Sao_Paulo');
$data = new DateTime();

require_once __DIR__ . '/sistema/env.php';
env_load_dotenv((string) (dirname(__DIR__) . '/.env'));

require_once 'funcoes.php';
require_once 'userFunctions.php';

function rota($procurar='index') {
  $atual = (string) 'index';
  $procurar = (string) strtolower($procurar);

  if (isset($_REQUEST['rota']) == true) {
    $atual = (string) $_REQUEST['rota'];
  }

  return ($atual == $procurar);
}
?>