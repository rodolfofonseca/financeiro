<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Usuario.php';

router_add('salvar_dados', function () {
    $objeto_usuario = new Usuario();

    echo json_encode((array) ['status' => (bool) $objeto_usuario->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('index', function () {
    require_once 'includes/head.php';
?>
    <script>
        let CODIGO_EMPRESA = "<?php echo $codigo_empresa; ?>";
        function cadastro_cliente(codigo_cliente) {
            window.location.href = sistema.url('/clientes.php', {
                'rota': 'cadastro_clientes',
                'codigo_clientes': codigo_cliente
            })
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Clientes</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center" onclick="cadastro_cliente('');">
                            Cadastrar Cliente
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">Pesquisa de Clientes</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_clientes">
                                            <thead>
                                                <tr class="text-center">
                                                    <th scope="col">Nome Cliente</th>
                                                    <th scope="col">Telefone</th>
                                                    <th scope="col">Email</th>
                                                    <th scope="col">Cadastro</th>
                                                    <th scope="col">Editar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="10" class="text-center">UTILIZE O FILTRO PARA FACILITAR A PESQUISA!</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    require_once 'includes/footer.php';
});

router_add('cadastro_clientes', function () {
    require_once 'includes/head.php';
    $codigo_cliente = (string) (isset($_REQUEST['codigo_cliente']) ? (string) $_REQUEST['codigo_cliente'] : '');
    ?>
        <script>
            let CODIGO_CLIENTE = "<?php echo $codigo_cliente; ?>";
            let CODIGO_EMPRESA = "<?php echo $codigo_empresa; ?>";

            function pesquisar_cep(valor) {
                let cep = valor.replace(/\D/g, '');

                if (cep != '') {
                    let valida_cep = /^[0-9]{8}$/;
                    document.querySelector('#logradouro').value = '...';
                    document.querySelector('#bairro').value = '...';
                    document.querySelector('#uf').value = '...';
                    document.querySelector('#estado').value = '...';

                    var script = document.createElement('script');
                    script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';
                    document.body.appendChild(script);

                    if (valida_cep.test(cep)) {}
                } else {
                    Swal.fire({
                        title: "FALHA NA OPERAÇÃO!",
                        text: "Erro durante o processo, tente mais tarde!",
                        icon: "error"
                    });
                }
            }

            function meu_callback(conteudo) {
                if (!("erro" in conteudo)) {
                    document.querySelector('#logradouro').value = (conteudo.logradouro);
                    document.querySelector('#bairro').value = (conteudo.bairro);
                    document.querySelector('#uf').value = (conteudo.localidade);
                    document.querySelector('#estado').value = (conteudo.uf);
                } else {
                    Swal.fire({
                        title: "FALHA NA OPERAÇÃO!",
                        text: "Erro durante o processo, tente mais tarde!",
                        icon: "error"
                    });
                }
            }

            function salvar_dados() {
                let nome_usuario = document.querySelector('#nome_usuario').value;
                let email_usuario = document.querySelector('#email_usuario').value;
                let celular = document.querySelector('#celular').value;
                let cep = document.querySelector('#cep').value;
                let logradouro = document.querySelector('#logradouro').value;
                let bairro = document.querySelector('#bairro').value;
                let numero = document.querySelector('#numero').value;
                let uf = document.querySelector('#uf').value;
                let estado = document.querySelector('#estado').value;

                sistema.request.post('/clientes.php', {
                    'rota': 'salvar_dados',
                    'codigo_usuario': CODIGO_CLIENTE,
                    'empresa': CODIGO_EMPRESA,
                    'nome_usuario': nome_usuario,
                    'email_usuario': email_usuario,
                    'tipo_usuario': 'CLIENTE',
                    'celular': celular,
                    'cep': cep,
                    'logradouro': logradouro,
                    'bairro': bairro,
                    'uf': uf,
                    'estado': estado,
                    'numero': numero
                }, function(retorno) {
                    validar_retorno(retorno, '/clientes.php');
                });
            }

            function limpar_dados() {
                document.querySelector('#nome_usuario').value = '';
                document.querySelector('#email_usuario').value = '';
                document.querySelector('#celular').value;
                document.querySelector('#cep').value = '';
                document.querySelector('#logradouro').value = '';
                document.querySelector('#numero').value = '';
                document.querySelector('#bairro').value = '';
                document.querySelector('#uf').value = '';
                document.querySelector('#estado').value;
            }

            function voltar() {
                window.location.href = sistema.url('/clientes.php', {
                    'rota': 'index'
                });
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <div class="card-title">Cadastro de Clientes</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <label class="text">Nome Cliente</label>
                                        <input type="text" class="form-control" placeholder="Nome Cliente" id="nome_usuario">
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="text">Email</label>
                                        <input type="mail" class="form-control" placeholder="Email Cliente" id="email_usuario">
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="text">Celular</label>
                                        <input type="phone" class="form-control" placeholder="telefone Cliente" id="celular">
                                    </div>
                                </div>
                                <br />
                                <div>
                                    <div class="row">
                                        <div class="col-2">
                                            <label class="text">Cep</label>
                                            <input type="text" class="form-control" placeholder="Cep Cliente" id="cep" onblur="pesquisar_cep(this.value);">
                                        </div>
                                        <div class="col-2">
                                            <label class="text">Logradouro</label>
                                            <input type="text" class="form-control" placeholder="Logradouro Cliente" id="logradouro">
                                        </div>
                                        <div class="col-2">
                                            <label class="text">Número</label>
                                            <input type="text" class="form-control" placeholder="Número Residência" id="numero">
                                        </div>
                                        <div class="col-2">
                                            <label class="text">Bairro</label>
                                            <input type="text" class="form-control" placeholder="Bairro Cliente" id="bairro">
                                        </div>
                                        <div class="col-2">
                                            <label class="text">Uf</label>
                                            <input type="text" class="form-control" placeholder="Uf Cliente" id="uf">
                                        </div>
                                        <div class="col-2">
                                            <label class="text">Estado</label>
                                            <input type="text" class="form-control" placeholder="Estado Cliente" id="estado">
                                        </div>
                                    </div>
                                </div>
                                <?php
                                include_once 'includes/botao_cadastro.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        require_once 'includes/footer.php';
    });
        ?>