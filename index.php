<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Usuario.php';

router_add('index', function () {
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
        <script>
            function login_sistema(){
                let email_usuario = document.querySelector('#email_usuario').value;
                let senha_usuario = document.querySelector('#senha_usuario').value;

                sistema.request.post('/index.php', {'rota':'login_usuario', 'email_usuario':email_usuario, 'senha_usuario':senha_usuario}, function(retorno){

                });
            }
        </script>
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
                                    <div class=" mx-auto mb-5 text-center">
                                        <img src="imagens/imagens_sistema/logo_empresa_preto_pequeno.jpg" class="img-fluid" alt="Logo">
                                    </div>
                                    <div class="card border-0 p-lg-3 shadow-lg">
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <h5 class="mb-2">Login</h5>
                                                <p class="mb-0">Digite os dados de acesso ao Dashboard</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text border-end-0">
                                                        <i class="isax isax-sms-notification"></i>
                                                    </span>
                                                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Informa o email" id="email_usuario">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Password</label>
                                                <div class="pass-group input-group">
                                                    <span class="input-group-text border-end-0">
                                                        <i class="isax isax-lock"></i>
                                                    </span>
                                                    <span class="isax toggle-password isax-eye-slash"></span>
                                                    <input type="password" class="pass-inputs form-control border-start-0 ps-0" placeholder="****************" id="senha_usuario">
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="form-check form-check-md mb-0">
                                                        <input class="form-check-input" id="remember_me" type="checkbox">
                                                        <label for="remember_me" class="form-check-label mt-0">Lembrar dados</label>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <a href="forgot-password.html">Esqueceu a senha?</a>
                                                </div>
                                            </div>
                                            <div class="mb-1">
                                                <button type="submit" class="btn bg-primary-gradient text-white w-100">Acessar Sistema</button>
                                            </div>
                                            <div class="login-or">
                                                <span class="span-or">Ou</span>
                                            </div>
                                            <div class="text-center">
                                                <h6 class="fw-normal fs-14 text-dark mb-0">Não possui conta?
                                                    <a href="index.php?rota=cadastro_usuario" class="hover-a"> Cadastre-se</a>
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
            window.addEventListener("load", function() {
                document.getElementById("loader").style.display = "none";
            });
        </script>
    </body>

    </html>
<?php
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

            sistema.request.post('/index.php', {
                'rota': 'salvar_dados_usuario',
                'nome_usuario': nome_usuario,
                'email_usuario': email_usuario,
                'senha_usuario': senha_usuario
            }, function(retorno) {
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
                                    <div class=" mx-auto mb-5 text-center">
                                        <img src="imagens/imagens_sistema/logo_empresa_preto_pequeno.jpg" class="img-fluid" alt="Logo">
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
                                                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Nome" id="nome_usuario">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Endereço Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text border-end-0">
                                                        <i class="isax isax-sms-notification"></i>
                                                    </span>
                                                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Endereço Email" id="email_usuario">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Senha</label>
                                                <div class="pass-group input-group">
                                                    <span class="input-group-text border-end-0">
                                                        <i class="isax isax-lock"></i>
                                                    </span>
                                                    <span class="isax toggle-password isax-eye-slash"></span>
                                                    <input type="password" class="pass-input form-control border-start-0 ps-0" placeholder="****************" id="senha_usuario">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Confime a Senha</label>
                                                <div class="pass-group input-group">
                                                    <span class="input-group-text border-end-0">
                                                        <i class="isax isax-lock"></i>
                                                    </span>
                                                    <span class="isax toggle-passwords isax-eye-slash"></span>
                                                    <input type="password" class="pass-input form-control border-start-0 ps-0" placeholder="****************" id="confirme_senha">
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="form-check form-check-md mb-0">
                                                        <input class="form-check-input" id="remember_me" type="checkbox" id="termos_uso">
                                                        <label for="remember_me" class="form-check-label mt-0">Aceitar os termos</label>
                                                        <div class="d-inline-flex"><a href="#" class="text-decoration-underline me-1">Termos de serviço</a> e <a href="#" class="text-decoration-underline ms-1"> política de privacidade</a></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-1">
                                                <button class="btn bg-primary-gradient text-white w-100" onclick="cadastrar_usuario();">cadastrar</button>
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
    </body>

    </html>
<?php
});

router_add('salvar_dados_usuario', function () {
    $objeto_usuario = new Usuario();
    echo json_encode((array) ['status' => (bool) $objeto_usuario->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('login_usuario', function(){
    $objeto_usuario = new Usuario();
    $usuario = (array) $objeto_usuario->login_sistema($_REQUEST);
exit;
});
?>