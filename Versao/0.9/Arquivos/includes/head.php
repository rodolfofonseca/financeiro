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
define('AVATAR', $_SESSION['avatar']);

if (array_key_exists('codigo_usuario', $_SESSION) == true) {
	if ($_SESSION['codigo_usuario'] == '') {
		header("Location: index.php");
		exit;
	}
}

/**
 * Função responsável por retornar o texto de saudação de acordo com o horário do dia.
 * @param mixed $login_usuario
 * @return string Retorna o texto de saudação de acordo com o horário do dia.
 */
function retornar_texto_data($login_usuario)
{
	$texto = (string) '';

	if (HORA >= 0 && HORA < 12) {
		$texto = (string) 'BOM DIA ' . $login_usuario;
	} else if (HORA >= 12 && HORA < 18) {
		$texto = (string) 'BOA TARDE ' . $login_usuario;
	} else if (HORA >= 18 && HORA <= 23) {
		$texto = (string) 'BOA NOITE ' . $login_usuario;
	}

	return (string) $texto;
}

$login_usuario = (string) $_SESSION['login_usuario'];
$codigo_usuario = (string) $_SESSION['codigo_usuario'];
$codigo_empresa = (string) $_SESSION['codigo_empresa'];
$tipo_usuario = (string) $_SESSION['tipo_usuario'];
$versao_sistema = (string) $_SESSION['versao_sistema'];
$anexa_documentos = (bool) $_SESSION['anexa_documentos'];
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
	<link rel="stylesheet" href="css/alerta_css.css?v=<?php echo fileatime('css/alerta_css.css'); ?>">
	<script src="js/sistema.js?v=<?php echo filemtime('js/sistema.js'); ?>"></script>
	<script src="js/userJs.js?v=<?php echo filemtime('js/userJs.js'); ?>"></script>
	<link rel="stylesheet" href="css/estilo.css?v=<?php echo fileatime('css/estilo.css'); ?>">
</head>

<body class="d-flex flex-column min-vh-100">
	<div id="drag-fixed" class="hidden">
		<div id="basic-slider" class="hidden"></div>
		<div id="range-slider" class="hidden"></div>
		<div class="loader-modal" id="loader">
			<div class="spinner-grow text-light m-2" role="status">
				<span class="visually-hidden">Carregando...</span>
			</div>
		</div>
		<div class="main-wrapper">
			<div class="header">
				<div class="main-header">
					<div class="header-left">
						<a href="dashboard.php" class="logo">
							<img src="imagens/imagens_sistema/logo_sem_nome_250.png" alt="Logo">
						</a>
					</div>
					<a id="mobile_btn" class="mobile_btn" href="#sidebar">
						<span class="bar-icon">
							<span></span>
							<span></span>
							<span></span>
						</span>
					</a>
					<div class="header-user">
						<div class="nav user-menu nav-list">
							<div class="me-auto d-flex align-items-center" id="header-search">
							</div>
							<div class="d-flex align-items-center">
								<div class="input-icon-end position-relative me-2">
									<input type="text" class="form-control" placeholder="Pesquisar">
									<span class="input-icon-addon">
										<i class="isax isax-search-normal"></i>
									</span>
								</div>
								<div class="notification_item me-2">
									<a href="#" class="btn btn-menubar position-relative" id="notification_popup"
										data-bs-toggle="dropdown" data-bs-auto-close="true">
										<img src="imagens/icones/sino.png">
										<span class="position-absolute badge bg-success border border-white"></span>
									</a>
									<div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg"
										style="min-height: 300px;">
										<div class="p-2 border-bottom">
											<div class="row align-items-center">
												<div class="col">
													<h6 class="m-0 fs-16 fw-semibold"> Notificações</h6>
												</div>
												<div class="col-auto">
													<div class="dropdown">
														<a href="#" class="dropdown-toggle drop-arrow-none link-dark"
															data-bs-toggle="dropdown" data-bs-offset="0,15"
															aria-expanded="false">
															<img src="imagens/icones/engrenagem.png">
														</a>
														<div class="dropdown-menu dropdown-menu-end">
															<a href="javascript:void(0);" class="dropdown-item"><i
																	class="ti ti-bell-check me-1"></i>Marcar todas como
																lidas</a>
															<a href="javascript:void(0);" class="dropdown-item"><i
																	class="ti ti-trash me-1"></i>Deletar todas</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="notification-body position-relative z-2 rounded-0" data-simplebar>
											<div class="dropdown-item notification-item py-2 text-wrap border-bottom"
												id="notification-1">
												<div class="d-flex">
													<div class="me-2 position-relative flex-shrink-0">
														<img src="<?php echo AVATAR; ?>"
															class="avatar-md rounded-circle" alt="User Img">
													</div>
													<div class="flex-grow-1">
														<p class="mb-0 fw-semibold text-dark">
															<?php echo $login_usuario; ?>
														</p>
														<p class="mb-1 text-wrap fs-14">
															Um novo usuário foi cadastrado no sistema
														</p>
														<div class="d-flex justify-content-between align-items-center">
															<span class="fs-12"><i
																	class="isax isax-clock me-1"></i>Algum tempo
																atrás</span>
															<div
																class="notification-action d-flex align-items-center float-end gap-2">
																<a href="javascript:void(0);"
																	class="notification-read rounded-circle bg-info"
																	data-bs-toggle="tooltip" title=""
																	data-bs-original-title="Make as Read"
																	aria-label="Make as Read"></a>
																<button class="btn rounded-circle text-danger p-0"
																	data-dismissible="#notification-1">
																	<i class="isax isax-close-circle fs-12"></i>
																</button>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="p-2 rounded-bottom border-top text-center">
											<a href="error-404.php" class="text-center fw-medium fs-14 mb-0">
												Visualizar todas
											</a>
										</div>
									</div>
								</div>
								<div class="dropdown profile-dropdown">
									<a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center"
										data-bs-toggle="dropdown" data-bs-auto-close="true">
										<span class="avatar online">
											<img src="<?php echo AVATAR; ?>" alt="Img"
												class="img-fluid rounded-circle">
										</span>
									</a>
									<div class="dropdown-menu dropdown-menu-end p-2">
										<div class="d-flex align-items-center bg-light rounded-1 p-2 mb-2">
											<span class="avatar avatar-lg me-2">
												<img src="<?php echo AVATAR; ?>" alt="img"
													class="rounded-circle">
											</span>
											<div>
												<h6 class="fs-14 fw-medium mb-1"><?php echo $login_usuario; ?></h6>
												<p class="fs-13"><?php echo $tipo_usuario; ?></p>
											</div>
										</div>
										<a class="dropdown-item d-flex align-items-center" href="usuarios.php">
											<i class="isax isax-profile-circle me-2"></i>Configurações Usuário
										</a>
										<?php
										if (trim($tipo_usuario) == 'Administrador') {
											echo "<a class='dropdown-item d-flex align-items-center' href='sistema.php'>";
											echo "<i class='isax isax-document-text me-2'></i>Configurações Sistema";
											echo "</a>";
											echo "<a class='dropdown-item d-flex align-items-center' href='error-404.php'>";
											echo "Relatórios";
											echo "</a>";
										}
										?>
										<div
											class="form-check form-switch form-check-reverse d-flex align-items-center justify-content-between dropdown-item mb-0">
											<label class="form-check-label" for="notify"><i
													class="isax isax-notification me-2"></i>Notificações</label>
											<input class="form-check-input" type="checkbox" role="switch" id="notify">
										</div>
										<hr class="dropdown-divider my-2">
										<a class="dropdown-item logout d-flex align-items-center" href="index.php">
											<i class="isax isax-logout me-2"></i>Sair
										</a>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="two-col-sidebar" id="two-col-sidebar">
				<div class="sidebar" id="sidebar-two">
					<div class="sidebar-inner" data-simplebar>
						<div id="sidebar-menu" class="sidebar-menu">
							<ul>
								<li class="menu-title"><a href="dashboard.php"><span>Dashboard</span></a></li>
								<li>
									<ul>
										<li class="submenu">
											<a href="javascript:void(0);" class="active subdrop">
												<span>Movimentações</span>
												<span class="menu-arrow"></span>
											</a>
											<ul>
												<li><a href="movimentacao.php">Movimentações</a></li>
											</ul>
										</li>
										<li class="submenu">
											<a href="javascript:void(0);">
												<span>Cadastros</span>
												<span class="menu-arrow"></span>
											</a>
											<ul>
												<li><a href="contas.php">Cadastro de Contas</a></li>
												<li><a href="clientes.php">Cadastro de Clientes/Fornecedores</a></li>
												<li><a href="nota_fiscal.php">Notas Fiscais</a></li>
												<?php
												if (trim($_SESSION['tipo_usuario']) == 'Administrador') {
													echo "<li><a href='contas_pagar_receber.php?rota=contas_fornecedores_pesquisa'>Vincular Fornecedores/Contas Pagar</a></li>";
												}
												?>
											</ul>
										</li>
										<li class="submenu">
											<a href="javascript:void(0);">
												<span>Contas</span>
												<span class="menu-arrow"></span>
											</a>
											<ul>
												<li><a href="contas_pagar_receber.php?rota=cadastro_contas">Cadastrar
														Conta</a></li>
												<li><a href="contas_pagar_receber.php">Listar Contas</a></li>
											</ul>
										</li>
										<?php
										if (trim($_SESSION['tipo_usuario']) == 'Administrador' && $_SESSION['pedidos'] == true) {
											?>
											<li class="submenu">
												<a href="javascript:void(0);">
													<span>Produtos</span>
													<span class="menu-arrow"></span>
												</a>
												<ul>
													<li><a href="produtos.php">Produtos</a></li>
												</ul>
											</li>
											<li class="submenu">
												<a href="javascript:void(0);">
													<span>Pedidos</span>
													<span class="menu-arrow"></span>
												</a>
												<ul>
													<li><a href="pedidos.php?rota=entrada">Pedidos Entrada</a></li>
													<li><a href="pedidos.php?rota=saida">Pedidos Saída</a></li>
												</ul>
											</li>
											<?php
										}
										if (trim($_SESSION['tipo_usuario']) == 'Administrador') {
											?>
											<li class="submenu">
												<a href="javascript:void(0);">
													<span>Extratos</span>
													<span class="menu-arrow"></span>
												</a>
												<ul>
													<li><a href="extrato.php">Extrato</a></li>
													<li><a href="item_extrato.php">Itens Extratos</a></li>
												</ul>
											</li>
											<li class="submenu">
												<a href="javascript:void(0);">
													<span>Contabilidade</span>
													<span class="menu-arrow"></span>
												</a>
												<ul>
													<li><a href="fechamento_contabil_geral.php">Fechamento Contábil
															Geral</a></li>
												</ul>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>