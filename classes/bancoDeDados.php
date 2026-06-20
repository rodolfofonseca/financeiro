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

require_once 'funcoes.php';
require_once 'userFunctions.php';

function rota($procurar = 'index')
{
  $atual = (string) 'index';
  $procurar = (string) strtolower($procurar);

  if (isset($_REQUEST['rota']) == true) {
    $atual = (string) $_REQUEST['rota'];
  }

  return ($atual == $procurar);
}

/** * Função responsável por fazer o roteamento do sistema 
 * @param string $rota 
 * @param mixed $pagina 
 * @return void 
 * */
function router_add($rota, $pagina)
{
  $rota_atual = $_REQUEST['rota'] ?? 'index';
  if ($rota_atual == $rota) {
    call_user_func($pagina);
    exit;
  }
}
?>