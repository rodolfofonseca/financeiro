<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Usuario.php';
require_once 'modelos/Empresa.php';
require_once 'modelos/Sistema.php';
require_once 'modelos/Movimentacao.php';
require_once 'modelos/ContasPagarReceber.php';


router_add('salvar_dados_usuario', function () {
    $objeto_usuario = new Usuario();
    $objeto_empresa = new Empresa();
    $objeto_sistema = new Sistema();

    $retorno_usuario = (bool) false;
    $retorno_empresa = (array) [];

    $_REQUEST['status_empresa'] = (bool) true;

    $retorno_empresa = (array) $objeto_empresa->salvar_dados($_REQUEST);

    // if ($retorno_empresa['status'] == true) {
        $retorno_empresa_array = (array) $objeto_empresa->pesquisar((array) ['filtro' => (array) ['where' => [['cpf_cnpj', '=', (string) $_REQUEST['cnpj']]]]]);
        $_REQUEST['empresa'] = $retorno_empresa_array['codigo_empresa'];

        
        $retorno_usuario = (bool) $objeto_usuario->salvar_dados($_REQUEST);
        // file_put_contents('jsssss.json', json_encode(['request' => $retorno_empresa_array, 'array' => $_REQUEST]));
    // }

    $retorno_sistema = (bool) $objeto_sistema->salvar_dados((array) ['empresa' => (string) $_REQUEST['empresa'], 'versao_sistema' => (string) '0.0', 'anexa_documentos' => (string) 'NAO']);
    echo json_encode(['status' => (array) true]);
    exit;
});

router_add('login_usuario', function () {
    $objeto_usuario = new Usuario();
    $objeto_sistema = new Sistema();

    $usuario = (array) $objeto_usuario->login_sistema($_REQUEST);
    

    if (empty($usuario) == false) {
        session_start();

        $versao_sistema = 'alta 0.0';

        $_SESSION['codigo_usuario'] = $usuario['codigo_usuario'];
        $_SESSION['codigo_empresa'] = $usuario['codigo_empresa'];
        $_SESSION['nome_usuario'] = $usuario['nome_usuario'];

        $_SESSION['login_usuario'] = (string) 'Sem Login';
        $_SESSION['tipo_usuario'] = (string) 'Administrador';
        $_SESSION['versao_sistema'] = (string) 'alfa 0.00';

        $_SESSION['anexa_documentos'] = (bool) false;
        $_SESSION['pedidos'] = (bool) false;
        $_SESSION['modulo_contabil'] = (bool) false;
        $_SESSION['cloudinary'] = (bool) false;
        $_SESSION['google_agenda'] = (bool) false;

        $objeto_usuario->update_ultimo_login((array) ['codigo_usuario' => $usuario['codigo_usuario']]);
        $retorno_sistema = (array) $objeto_sistema->pesquisar((array) ['filtro' => (array) ['empresa', '===', $usuario['codigo_empresa']]]);

        if (array_key_exists('login_usuario', $usuario) == true) {
            $_SESSION['login_usuario'] = (string) $usuario['login_usuario'];
        }

        if (array_key_exists('tipo_usuario', $usuario) == true) {
            $_SESSION['tipo_usuario'] = (string) $usuario['tipo_usuario'];
        }

        if (array_key_exists('anexa_documentos', $retorno_sistema) == true) {
            $_SESSION['anexa_documentos'] = ($retorno_sistema['anexa_documentos'] === 'NAO') ? false : true;
        }

        if (array_key_exists('pedidos', $retorno_sistema) == true) {
            $_SESSION['pedidos'] = (bool) $retorno_sistema['pedidos'];
        }

        if (array_key_exists('modulo_contabil', $retorno_sistema) == true) {
            $_SESSION['modulo_contabil'] = (bool) $retorno_sistema['modulo_contabil'];
        }

        if (array_key_exists('versao_sistema', $retorno_sistema) == true) {
            $_SESSION['versao_sistema'] = (string) $retorno_sistema['versao_sistema'];
            $versao_sistema = (string) trim($retorno_sistema['versao_sistema']);
        }

        if (array_key_exists('cloudinary', $retorno_sistema) == true) {
            $_SESSION['cloudinary'] = (bool) $retorno_sistema['cloudinary'];
        }

        if (array_key_exists('google_agenda', $retorno_sistema) == true) {
            $_SESSION['google_agenda'] = (bool) $retorno_sistema['google_agenda'];
        }

        if ($versao_sistema == 'alfa 0.6.2') {
            header('location:dashboard.php');
        } else {
            header('location:index.php');
        }
    } else {
        header('location:index.php');
    }

    exit;
});

router_add('trocar_senha_usuario', function () {
    $objeto_usuario = new Usuario();

    echo json_encode((array) ['status' => (bool) $objeto_usuario->alterar_senha($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

router_add('index', function () {
    session_start();
    $_SESSION = array();
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Login | Controle Financeiro</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Sistema de Gerenciamento de micro e pequena empresa.">
        <meta name="keywords" content="admin, dashboard">
        <meta name="author" content="Rdolfo Fonseca">
        <link rel="shortcut icon" type="image/x-icon" href="imagens/imagens_sistema/icone_sistema.ico">
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/alerta_css.css?v=<?php echo fileatime('css/alerta_css.css'); ?>">
        <link rel="stylesheet" href="css/estilo.css?v=<?php echo fileatime('css/estilo.css'); ?>">
        <link rel="stylesheet" href="css/tabler-icons.min.css">
        <link rel="stylesheet" href="css/iconsax.css">
        <link rel="stylesheet" href="css/style.css">

        <script src="js/sistema.js?v=<?php echo filemtime('js/sistema.js'); ?>"></script>
        <script src="js/userJs.js?v=<?php echo filemtime('js/userJs.js'); ?>"></script>
    </head>

    <body class="bg-white">
        <div class="loader-modal" id="loader">
            <div class="spinner-grow text-light m-2" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>
        <div class="main-wrapper">
            <div class="container-fuild">
                <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
                    <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
                        <div class="col-lg-4 mx-auto">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="d-flex flex-column justify-content-lg-center p-4 p-lg-0 pb-0 flex-fill">
                                    <div class=" mx-auto text-center">
                                        <img src="imagens/imagens_sistema/imagem_menor.png" class="img-fluid" alt="Logo">
                                    </div>
                                    <div class="card border-0 p-lg-3 shadow-lg">
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <h5 class="mb-2">Login</h5>
                                                <p class="mb-0">Digite os dados de acesso ao Dashboard</p>
                                            </div>
                                            <form method="POST" action="index.php">
                                                <input type="hidden" name="rota" value="login_usuario">
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control border-start-0 ps-0"
                                                            placeholder="Informa o email" id="email_usuario"
                                                            name="email_usuario">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Password</label>
                                                    <div class="pass-group input-group">
                                                        <input type="password"
                                                            class="pass-inputs form-control border-start-0 ps-0"
                                                            placeholder="****************" id="senha_usuario"
                                                            name="senha_usuario">
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="form-check form-check-md mb-0">
                                                            <input class="form-check-input" id="remember_me"
                                                                type="checkbox">
                                                            <label for="remember_me" class="form-check-label mt-0">Lembrar
                                                                dados</label>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <a href="index.php?rota=esqueceu_senha">Esqueceu a senha?</a>
                                                    </div>
                                                </div>
                                                <div class="mb-1">
                                                    <button type="submit"
                                                        class="btn bg-primary-gradient text-white w-100">Acessar
                                                        Sistema</button>
                                                </div>
                                            </form>
                                            <div class="login-or">
                                                <span class="span-or">Ou</span>
                                            </div>
                                            <div class="text-center">
                                                <h6 class="fw-normal fs-14 text-dark mb-0">Não possui conta?
                                                    <a href="index.php?rota=cadastro_usuario" class="hover-a">
                                                        Cadastre-se</a>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="js/jquery-3.7.1.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/script.js"></script>
        <script src="js/alerta.js?v=<?php echo fileatime('js/alerta.js'); ?>"></script>
        <script>
            document.querySelector("#loader").style.display = "none";
        </script>
    </body>

    </html>
    <?php
    session_destroy();
});

router_add('cadastro_usuario', function () {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Login | Controle Financeiro</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Sistema de Gerenciamento de micro e pequena empresa.">
        <meta name="keywords" content="admin, dashboard">
        <meta name="author" content="Rdolfo Fonseca">
        <link rel="shortcut icon" type="image/x-icon" href="imagens/imagens_sistema/icone_sistema.ico">
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/alerta_css.css?v=<?php echo fileatime('css/alerta_css.css'); ?>">
        <link rel="stylesheet" href="css/estilo.css">
        <link rel="stylesheet" href="css/tabler-icons.min.css">
        <link rel="stylesheet" href="css/iconsax.css">
        <link rel="stylesheet" href="css/style.css">

        <script src="js/sistema.js?v=<?php echo filemtime('js/sistema.js'); ?>"></script>
        <script src="js/userJs.js?v=<?php echo filemtime('js/userJs.js'); ?>"></script>
    </head>
    <script>
        function cadastrar_usuario() {
            let nome_usuario = document.querySelector('#nome_usuario').value;
            let email_usuario = document.querySelector('#email_usuario').value;
            let senha_usuario = document.querySelector('#senha_usuario').value;
            let nome_empresa = document.querySelector('#nome_empresa').value;
            let nome_fantasia = document.querySelector('#nome_fantasia').value;
            let cnpj = document.querySelector('#cnpj').value;
            let endereco = document.querySelector('#endereco').value;

            sistema.request.post('/index.php', {
                'rota': 'salvar_dados_usuario',
                'nome_usuario': nome_usuario,
                'email_usuario': email_usuario,
                'senha_usuario': senha_usuario,
                'nome_empresa': nome_empresa,
                'nome_fantasia': nome_fantasia,
                'cnpj': cnpj,
                'endereco': endereco
            }, function (retorno) {
                Swal.fire({'title':'sucesso', 'text':'Cadastro Realizado com sucesso!'});
            });
        }
    </script>

    <body class="bg-white">
        <div class="loader-modal" id="loader">
            <div class="spinner-grow text-light m-2" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>
        <div class="main-wrapper">
            <div class="container-fuild">
                <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
                    <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
                        <div class="col-lg-4 mx-auto">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="d-flex flex-column justify-content-lg-center p-4 p-lg-0 pb-0 flex-fill">
                                    <div class=" mx-auto text-center">
                                        <img src="imagens/imagens_sistema/imagem_100.png" class="img-fluid" alt="Logo">
                                    </div>
                                    <div class="card border-0 p-lg-3 shadow-lg rounded-2">
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <h5 class="mb-2">cadastro</h5>
                                                <p class="mb-0">Informe seus dados para o cadastro</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nome completo</label>
                                                <div class="input-group">
                                                    <span class="input-group-text border-end-0">
                                                        <i class="isax isax-profile"></i>
                                                    </span>
                                                    <input type="text"
                                                        class="form-control border-start-0 ps-0 text-uppercase"
                                                        placeholder="Nome" id="nome_usuario">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Endereço Email</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control border-start-0 ps-0"
                                                        placeholder="Endereço Email" id="email_usuario">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Senha</label>
                                                <div class="pass-group input-group">
                                                    <input type="password"
                                                        class="pass-input form-control border-start-0 ps-0"
                                                        placeholder="****************" id="senha_usuario">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nome Empresa</label>
                                                <div class="pass-group input-group">
                                                    <input type="text"
                                                        class="form-control border-start-0 ps-0 text-uppercase"
                                                        placeholder="Nome Empresa" id="nome_empresa">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nome Fantasia</label>
                                                <div class="pass-group input-group">
                                                    <input type="text"
                                                        class="form-control border-start-0 ps-0 text-uppercase"
                                                        placeholder="Nome Fantasia" id="nome_fantasia">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">CNPJ</label>
                                                <div class="pass-group input-group">
                                                    <input type="text" class="form-control border-start-0 ps-0"
                                                        placeholder="CNPJ" id="cnpj" sistema-mask="cpf-cnpj">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Endereço</label>
                                                <div class="pass-group input-group">
                                                    <input type="text" class="form-control border-start-0 ps-0"
                                                        placeholder="Endereço" id="endereco">
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="form-check form-check-md mb-0">
                                                        <input class="form-check-input" id="remember_me" type="checkbox"
                                                            id="termos_uso">
                                                        <label for="remember_me" class="form-check-label mt-0">Aceitar os
                                                            termos</label>
                                                        <div class="d-inline-flex"><a href="#"
                                                                class="text-decoration-underline me-1">Termos de serviço</a>
                                                            e <a href="#" class="text-decoration-underline ms-1"> política
                                                                de privacidade</a></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-1">
                                                <button class="btn bg-primary-gradient text-white w-100"
                                                    onclick="cadastrar_usuario();">cadastrar</button>
                                            </div>
                                            <div class="login-or">
                                                <span class="span-or">Ou</span>
                                            </div>
                                            <div class="text-center">
                                                <h6 class="fw-normal fs-14 text-dark mb-0">Já possui login?
                                                    <a href="index.php" class="hover-a"> Faça Login</a>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="js/jquery-3.7.1.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/script.js"></script>
        <script src="js/alerta.js?v=<?php echo fileatime('js/alerta.js'); ?>"></script>
        <script>
            document.querySelector("#loader").style.display = "none";
        </script>
    </body>

    </html>
    <?php
});

router_add('esqueceu_senha', function () {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Esqueceu a senha | Controle Financeiro</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Sistema de Gerenciamento de micro e pequena empresa.">
        <meta name="keywords" content="admin, dashboard">
        <meta name="author" content="Rdolfo Fonseca">
        <link rel="shortcut icon" type="image/x-icon" href="imagens/imagens_sistema/icone_sistema.ico">
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="css/alerta_css.css?v=<?php echo fileatime('css/alerta_css.css'); ?>">
        <link rel="stylesheet" href="css/estilo.css">
        <link rel="stylesheet" href="css/tabler-icons.min.css">
        <link rel="stylesheet" href="css/iconsax.css">
        <link rel="stylesheet" href="css/style.css">

        <script src="js/sistema.js?v=<?php echo filemtime('js/sistema.js'); ?>"></script>
        <script src="js/userJs.js?v=<?php echo filemtime('js/userJs.js'); ?>"></script>
    </head>
    <script>
        function alterar_senha() {
            let email = document.querySelector('#email_usuario').value;
            let senha = document.querySelector('#senha_usuario').value;

            sistema.request.post('/index.php', {
                'rota': 'trocar_senha_usuario',
                'email_usuario': email,
                'senha_usuario': senha
            }, function (retorno) {
                validar_retorno(retorno, '/index.php');
            });
        }
    </script>

    <body class="bg-white">
        <div class="loader-modal" id="loader">
            <div class="spinner-grow text-light m-2" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
        </div>
        <div class="main-wrapper">
            <div class="container-fuild">
                <div class="w-100 overflow-hidden position-relative flex-wrap d-block vh-100">
                    <div class="row justify-content-center align-items-center vh-100 overflow-auto flex-wrap ">
                        <div class="col-lg-4 mx-auto">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="d-flex flex-column justify-content-lg-center p-4 p-lg-0 pb-0 flex-fill">
                                    <div class=" mx-auto text-center">
                                        <img src="imagens/imagens_sistema/imagem_100.png" class="img-fluid" alt="Logo">
                                    </div>
                                    <div class="card border-0 p-lg-3 shadow-lg rounded-2">
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <h5 class="mb-2">Esqueceu a Senha</h5>
                                                <p class="mb-0">Informe seu email e sua nova senha.</p>
                                                <p class="mb-0">Se o email pertencer a uma conta a senha será alterada.</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Endereço Email</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control border-start-0 ps-0"
                                                        placeholder="Endereço Email" id="email_usuario">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Senha</label>
                                                <div class="pass-group input-group">
                                                    <input type="password"
                                                        class="pass-input form-control border-start-0 ps-0"
                                                        placeholder="****************" id="senha_usuario">
                                                </div>
                                            </div>
                                            <div class="mb-1">
                                                <button class="btn bg-primary-gradient text-white w-100"
                                                    onclick="alterar_senha();">Alterar Senha</button>
                                            </div>
                                            <div class="login-or">
                                                <span class="span-or">Ou</span>
                                            </div>
                                            <div class="text-center">
                                                <h6 class="fw-normal fs-14 text-dark mb-0">Já possui login?
                                                    <a href="index.php" class="hover-a"> Faça Login</a>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="js/jquery-3.7.1.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/script.js"></script>
        <script src="js/alerta.js?v=<?php echo fileatime('js/alerta.js'); ?>"></script>
        <script>
            document.querySelector("#loader").style.display = "none";
        </script>
    </body>

    </html>
    <?php
});
?>