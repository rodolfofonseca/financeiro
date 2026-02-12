<?php
ignore_user_abort(true);
ini_set('memory_limit', '-1');
set_time_limit(0);
error_reporting(E_ALL & ~E_DEPRECATED);
$url = '';
define('URL', 'http://' . $url);
define('DIRETORIO_SISTEMA', str_replace('\\', '/', __DIR__));
function rota($procurar='index') {
  $atual = (string) 'index';
  $procurar = (string) strtolower($procurar);

  if (isset($_REQUEST['rota']) == true) {
    $atual = (string) $_REQUEST['rota'];
  }

  return ($atual == $procurar);
}
?>