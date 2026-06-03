<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
$data = new DateTime();

$meses = [
	1 => 'Janeiro',
	2 => 'Fevereiro',
	3 => 'Março',
	4 => 'Abril',
	5 => 'Maio',
	6 => 'Junho',
	7 => 'Julho',
	8 => 'Agosto',
	9 => 'Setembro',
	10 => 'Outubro',
	11 => 'Novembro',
	12 => 'Dezembro'
];

define('DATA_HOJE', $data->format('Y-m-d'));
define('DATA_INICIO', $data->format('Y-m-01'));
define('DATA_FINAL', $data->format('Y-m-t'));
define('HORA', intval($data->format('H'), 10));
define('MES_NOME', $meses[date('n')]);
define('MES_NOME_ANTERIOR', $meses[date('n') - 1]);
define('MES_NOME_PROXIMO_MES', $meses[date('n') + 1]);
define('EMPRESA', $_SESSION['codigo_empresa']);

$login_usuario = (string) $_SESSION['login_usuario'];
$codigo_usuario = (string) $_SESSION['codigo_usuario'];
$codigo_empresa = (string) $_SESSION['codigo_empresa'];
$tipo_usuario = (string) $_SESSION['tipo_usuario'];
$versao_sistema = (string) $_SESSION['versao_sistema'];
$anexa_documentos = (bool) $_SESSION['anexa_documentos'];

if (array_key_exists('codigo_usuario', $_SESSION) == true) {
	if ($_SESSION['codigo_usuario'] == '') {
		header("Location: index.php");
		exit;
	}
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Controle Financeiro</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Sistema de Gerenciamento de micro e pequena empresa.">
	<meta name="keywords" content="admin, dashboard">
	<meta name="author" content="Rdolfo Fonseca">
	<link rel="shortcut icon" type="image/x-icon" href="imagens/imagens_sistema/icone_sistema.ico">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/daterangepicker.css">
	<link rel="stylesheet" href="css/all.min.css">
	<link rel="stylesheet" href="css/simplebar.min.css">
	<link rel="stylesheet" href="css/select2.min.css">
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/c3.min.css">
	<link rel="stylesheet" href="css/estilo.css?v=<?php echo fileatime('css/estilo.css'); ?>">
	<link rel="stylesheet" href="css/alerta_css.css?v=<?php echo fileatime('css/alerta_css.css'); ?>">
	<script src="js/sistema.js?v=<?php echo filemtime('js/sistema.js'); ?>"></script>
	<script src="js/userJs.js?v=<?php echo filemtime('js/userJs.js'); ?>"></script>
</head>

<body class="d-flex flex-column min-vh-100">