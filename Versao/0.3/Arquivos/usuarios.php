<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST)) {
        if (array_key_exists('rota', $_POST) == true) {
            if ($_POST['rota'] == 'salvar_avatar') {
                $objeto_usuario = new Usuario();

                $retorno = (bool) $objeto_usuario->salvar_imagem_avatar($_POST, $_FILES);

                if ($retorno == true) {
                    header('Location: usuarios.php?cadastro_avatar=true&retorno=true');
                } else {
                    header('Location: usuarios.php?cadastro_avatar=true&retorno=false');
                }
            }
        }
    }
}

router_add('alterar_com_pesquisa', function () {
    $objeto_usuario = new Usuario();

    echo json_encode((array) ['status' => (bool) $objeto_usuario->alterar_com_pesquisa($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('pesquisa_usuario', function () {
    $objeto_usuario = new Usuario();
    $codigo_usuario = (string) (isset($_REQUEST['codigo_usuario']) ? (string) $_REQUEST['codigo_usuario'] : '');

    $retorno_usuario = (array) [];

    if ($codigo_usuario != '') {
        $retorno_usuario = (array) $objeto_usuario->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_usuario)]]);
    }

    echo json_encode((array) ['dados' => (array) $retorno_usuario], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('salvar_dados', function () {
    $objeto_usuario = new Usuario();

    echo json_encode(['status' => (bool) $objeto_usuario->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('pesquisar_usuarios', function () {
    $objeto_usuario = new Usuario();
    $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa'] : '');
    $tipo_usuario = (string) (isset($_REQUEST['tipo_usuario']) ? (string) $_REQUEST['tipo_usuario'] : 'FUNCIONARIO');
    $filtro_montando = (array) [];
    $retorno = (array) [];

    if ($empresa != '') {
        array_push($filtro_montando, (array) ['empresa', '===', model_id($empresa)]);
    }

    if ($tipo_usuario != 'CLIENTE') {
        array_push($filtro_montando, (array) ['tipo_usuario', '===', (string) $tipo_usuario]);
    }

    if (empty($filtro_montando) == false) {
        $retorno = (array) $objeto_usuario->pesquisar_todos((array) ['filtro' => (array) ['and' => (array) $filtro_montando], 'ordenacao' => (array) ['nome_usuario' => (bool) true], 'limite' => (int) 0]);
    }

    echo json_encode((array) ['dados' => (array) $retorno], JSON_UNESCAPED_UNICODE);
    exit;
});

/**
 * Rota responsável por alterar o salário do colaborador.
 */
router_add('alterar_salario', function(){
    $objeto_usuario = new Usuario();

    echo json_encode((array) ['status' => (bool) $objeto_usuario->alterar_salario($_REQUEST)]);
    exit;
});

router_add('index', function () {
    include_once 'includes/head.php';
    $mensagem = (string) (isset($_REQUEST['retorno']) ? (string) $_REQUEST['retorno'] : 'false');
    $cadastro_avatar = (string) (isset($_REQUEST['cadastro_avatar']) ? (string) $_REQUEST['cadastro_avatar'] : 'false');
?>
    <script>
        let MENSAGEM = "<?php echo $mensagem; ?>";
        let CADASTRO = "<?php echo $cadastro_avatar; ?>";
        let CODIGO_USUARIO = "<?php echo $codigo_usuario; ?>";

        function cadastro_avatar() {
            window.location.href = sistema.url('/usuarios.php', {
                'rota': 'cadastrar_avatar'
            });
        }

        function salvar_senha() {
            let senha = document.querySelector('#senha_usuario').value;
            sistema.request.post('/usuarios.php', {
                'rota': 'alterar_com_pesquisa',
                'senha_usuario': senha,
                'codigo_usuario': CODIGO_USUARIO
            }, function(retorno) {
                validar_retorno(retorno, '/usuarios.php');
            });
        }

        function salvar_salario() {
            let salario = document.querySelector('#salario').value;

            sistema.request.post('/usuarios.php', {
                'rota': 'alterar_salario',
                'salario': salario,
                'codigo_usuario': CODIGO_USUARIO
            }, function(retorno) {
                validar_retorno(retorno, '/usuarios.php');
            });
        }

        function alterar_informacoes_gerais() {
            window.location.href = sistema.url('/usuarios.php', {
                'rota': 'alterar_informacoes_gerais'
            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">Dados Usuários</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <button class="btn btn-primary w-100" onclick="cadastro_avatar();">Cadastrar Avatar</button>
                            </div>
                            <div class="col-3 text-center">
                                <button class="btn btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#modal_trocar_senha">Trocar Senha</button>
                            </div>
                            <div class="col-3 text-center">
                                <button class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#cadastro_salario">Cadastro Salário</button>
                            </div>
                            <div class="col-3 text-center">
                                <button class="btn btn-warning w-100" onclick="alterar_informacoes_gerais();">Informações</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modal_trocar_senha" tabindex="-1" role="dialog" aria-labelledby="modal_troca_senha" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal_troca_senha">Trocar Senha</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-8">
                                <input type="password" class="form-control" id="senha_usuario" placeholder="Nova Senha">
                            </div>
                            <div class="col-4">
                                <button class="btn btn-success w-100" onclick="salvar_senha();">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="cadastro_salario" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Cadastro Salário</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-8">
                                <input type="text" class="form-control" id="salario" placeholder="Salário" sistema-mask="moeda">
                            </div>
                            <div class="col-4">
                                <button class="btn btn-success w-100" onclick="salvar_salario();">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function() {
                if (CADASTRO == 'true') {
                    if (MENSAGEM == 'true') {
                        Swal.fire('Sucesso!', 'Operação realizada com sucesso!', 'success');

                        setTimeout(pesquisar_documento(), 5000);
                    } else {
                        Swal.fire('Erro', 'Erro durante a operação!', 'error');
                    };
                }
            }
        </script>
    <?php
    include_once 'includes/footer.php';
    exit;
});

router_add('cadastrar_avatar', function () {
    include_once 'includes/head.php';
    ?>
        <script>
            function retornar(parametro, sair) {
                parametro.preventDefault();
                if (sair == true) {
                    window.location.href = sistema.url('/usuarios.php', {
                        'rota': 'index'
                    });
                }
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">Dados Usuários</div>
                        </div>
                        <div class="card-body">
                            <form method="POST" accept="usuarios.php" enctype="multipart/form-data">
                                <input type="hidden" name="rota" value="salvar_avatar">
                                <input type="hidden" name="codigo_usuario" value="<?php echo $codigo_usuario; ?>">
                                <div class="row">
                                    <div class="col-12">
                                        <input type="file" class="form-control custom-radius text-center" id="arquivo" placeholder="Imagem Avatar" name="arquivo" />
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-4">
                                        <input type="submit" class="btn btn-success btn-lg w-100" value="Salvar Dados" />
                                    </div>
                                    <div class="col-4">
                                        <input type="reset" class="btn btn-info btn-lg w-100" value="Limpar Campos" />
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-danger btn-lg w-100" onclick="retornar(event, true);">Voltar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        include_once 'includes/footer.php';
        exit;
    });

    router_add('alterar_informacoes_gerais', function () {
        include_once 'includes/head.php';
        ?>
            <script>
                const ID_USUARIO = "<?php echo $codigo_usuario; ?>";

                function pesquisar_usuario() {
                    sistema.request.post('/usuarios.php', {
                        'rota': 'pesquisa_usuario',
                        'codigo_usuario': ID_USUARIO
                    }, function(retorno) {
                        let usuario = retorno.dados;

                        document.querySelector('#nome_usuario').value = usuario.nome_usuario;
                        document.querySelector('#email_usuario').value = usuario.email_usuario;
                        document.querySelector('#login_usuario').value = usuario.login_usuario;
                        document.querySelector('#data_cadastro').value = sistema.retornar_data(usuario.data_cadastro, 'AMERICANO');
                        document.querySelector('#ultimo_login').value = sistema.retornar_data(usuario.ultimo_login, 'AMERICANO');
                        document.querySelector('#salario').value = usuario.salario;
                        document.querySelector('#login_usuario').value = usuario.login_usuario;
                        document.querySelector('#tipo_usuario').value = usuario.tipo_usuario;
                    });
                }

                function voltar() {
                    window.location.href = sistema.url('/usuarios.php', {
                        'rota': 'index'
                    });
                }

                function salvar_dados() {
                    let nome_usuario = document.querySelector('#nome_usuario').value;
                    let email_usuario = document.querySelector('#email_usuario').value;
                    let salario = document.querySelector("#salario").value;
                    let login_usuario = document.querySelector('#login_usuario').value;
                    let tipo_usuario = document.querySelector('#tipo_usuario').value;

                    sistema.request.post('/usuarios.php', {
                        'rota': 'salvar_dados',
                        'salario': salario,
                        'login_usuario': login_usuario,
                        'tipo_usuario': tipo_usuario,
                        'codigo_usuario': ID_USUARIO,
                        'email_usuario': email_usuario,
                        'nome_usuario': nome_usuario
                    }, function(retorno) {
                        validar_retorno(retorno, '/usuarios.php');
                    });
                }
            </script>
            <div class="page-wrapper">
                <div class="content">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header justify-content between">
                                <div class="card-title">Dados Usuários</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 text-center">
                                        <label class="text">Nome Usuário</label>
                                        <input type="text" class="form-control" id="nome_usuario" readonly="true" placeholder="Nome Usuário">
                                    </div>
                                    <div class="col-6 text-center">
                                        <label class="text">Email</label>
                                        <input type="text" class="form-control" id="email_usuario" placeholder="Email" readonly="true">
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-6 text-center">
                                        <label class="text">Data Cadastro</label>
                                        <input type="date" class="form-control" id="data_cadastro" readonly="true">
                                    </div>
                                    <div class="col-6 text-center">
                                        <label class="text">Data Último login</label>
                                        <input type="date" class="form-control" id="ultimo_login" readonly="true">
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <label class="text">Salário</label>
                                        <input type="text" class="form-control" id="salario" placeholder="Salário R$ 2000,00">
                                    </div>
                                    <div class="text-center col-4">
                                        <label class="text">Login Usuário</label>
                                        <input type="text" class="form-control" id="login_usuario" placeholder="Login Usuário">
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="text">Tipo Usuário</label>
                                        <select class="form-control" id="tipo_usuario">
                                            <option value="">Selecione Uma Opção</option>
                                            <option value="Administrador">Administrador</option>
                                            <option value="Comum">Comum</option>
                                        </select>
                                    </div>
                                </div>
                                <?php include_once 'includes/botao_cadastro.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    window.onload = function() {
                        document.querySelector('#btn_limpar_dados').disabled = true;
                        pesquisar_usuario();
                    }
                </script>
            <?php
            include_once 'includes/footer.php';
            exit;
        }); ?>