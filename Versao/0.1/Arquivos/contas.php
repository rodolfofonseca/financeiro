<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Contas.php';

router_add('index', function () {
    require_once 'includes/head.php';
    ?>
    <script>
        const CODIGO_EMPRESA = "<?php echo $_SESSION['codigo_empresa']; ?>";

        function cadastro_contas(codigo_conta) {
            window.location.href = sistema.url('/contas.php', {
                'rota': 'cadastro_contas',
                'codigo_conta': codigo_conta
            });
        }

        function pesquisar_contas() {
            let nome_conta = document.querySelector('#nome_conta').value;
            let status = document.querySelector("#status_conta").value;
            let descricao = document.querySelector('#descricao').value;
            sistema.request.post('/contas.php', {
                'rota': 'pesquisar_contas',
                'empresa': CODIGO_EMPRESA,
                'nome_conta': nome_conta,
                'descricao': descricao,
                'status': status
            }, function (retorno) {
                let contas = retorno.dados;
                let tamanho_retorno = contas.length;
                let tabela = document.querySelector('#tabela_contas tbody');

                tabela = sistema.remover_linha_tabela(tabela);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');

                    linha.appendChild(sistema.gerar_td(['text-center'], 'NENHUMA CONTA ENCONTRADA COM OS FILTROS PASSADOS', 'inner', true, 5));
                    tabela.appendChild(linha);
                } else {
                    sistema.each(contas, function (index, conta) {
                        let linha = document.createElement('tr');
                        let div = document.createElement('div');

                        linha.appendChild(sistema.gerar_td(['text-center'], conta.nome_conta, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], conta.descricao, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(conta.saldo_conta), 'inner'));

                        if (conta.status == 'ATIVO') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_' + conta._id.$oid, 'ATIVO', ['btn', 'btn-success'], function visualizar() { }), 'append'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_' + conta._id.$oid, 'INATIVO', ['btn', 'btn-danger'], function visualizar() { }), 'append'));
                        }

                        div.classList.add('hstack');
                        div.classList.add('gap-2');
                        div.classList.add('fs-15');

                        div.appendChild(sistema.gerar_botao('botao_download_' + conta._id.$oid, 'DOWNLOAD', ['btn', 'btn-primary'], function visualizar() { }, 'append'));
                        div.appendChild(sistema.gerar_botao('botao_visualizar_' + conta._id.$oid, 'EDITAR', ['btn', 'btn-info'], function editar() {
                            cadastro_contas(conta._id.$oid);
                        }, 'append'));

                        linha.appendChild(div);

                        tabela.appendChild(linha);
                    });
                }
            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Contas</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center"
                            onclick="cadastro_contas('');">
                            Cadastrar Conta
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Pesquisa de Contas
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <label class="text">Nome da Conta</label>
                                    <input type="text" class="form-control" placeholder="Nome Conta" id="nome_conta">
                                </div>
                                <div class="col-6 text-center">
                                    <label class="text">Status</label>
                                    <select class="form-control" id="status_conta">
                                        <option value="TODOS">TODOS</option>
                                        <option value="ATIVO">ATIVO</option>
                                        <option value="INATIVO">INATIVO</option>
                                    </select>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-12">
                                    <textarea class="form-control" id="descricao"
                                        placeholder="Descrição da conta"></textarea>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-3 push-9">
                                    <button class="btn btn-secondary w-100" onclick="pesquisar_contas();">Pesquisar</button>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_contas">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Nome Conta</th>
                                                    <th scope="col">Descrição</th>
                                                    <th scope="col">Saldo</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Ação</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="5" class="text-center">UTILIZE O FILTRO PARA FACILITAR A
                                                        PESQUISA!</td>
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
        <script>
            window.onload = function () {
                pesquisar_contas();
            }
        </script>
        <?php
        require_once 'includes/footer.php';
});

router_add('cadastro_contas', function () {
    $codigo_conta = (string) (isset($_REQUEST['codigo_conta']) ? (string) $_REQUEST['codigo_conta'] : '');

    require_once 'includes/head.php';
    ?>
        <script>
            const EMPRESA = "<?php echo $_SESSION['codigo_empresa']; ?>";
            const CODIGO_CONTA = "<?php echo $codigo_conta; ?>";

            function salvar_dados() {
                let nome_conta = document.querySelector('#nome_conta').value;
                let saldo_conta = document.querySelector("#saldo_conta").value;
                let descricao = document.querySelector("#descricao").value;

                let valida_nome_conta = true;
                let valida_saldo_conta = true;
                let valida_descricao = true;

                if (nome_conta == '') {
                    alerta_campo_vazio('NOME CONTA');
                    valida_nome_conta = false;
                }

                if (valida_saldo_conta == '') {
                    alerta_campo_vazio('SALDO CONTA');
                    valida_saldo_conta = false;
                }

                if (valida_descricao == '') {
                    alerta_campo_vazio('DESCRIÇÃO CONTA');
                    valida_descricao = false;
                }

                if (valida_descricao == true && valida_nome_conta == true && valida_saldo_conta == true) {
                    sistema.request.post('/contas.php', {
                        'rota': 'salvar_dados',
                        'codigo_conta': CODIGO_CONTA,
                        'empresa': EMPRESA,
                        'nome_conta': nome_conta,
                        'descricao': descricao,
                        'saldo_conta': saldo_conta
                    }, function (retorno) {
                        validar_retorno(retorno, '/contas.php');
                    });
                }

            }

            function voltar() {
                window.location.href = sistema.url('/contas.php', {
                    'rota': 'index'
                });
            }

            function limpar_dados() {
                document.querySelector('#nome_conta').value = '';
                document.querySelector('#saldo_conta').value = '';
                document.querySelector('#status_conta').value = '';
                document.querySelector('#descricao').value = '';
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">
                                    Cadastro de Contas
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <label class="text">Nome da Conta</label>
                                        <input type="text" class="form-control" id="nome_conta" placeholder="Nome da conta">
                                    </div>
                                    <div class="col-4">
                                        <label class="text">Saldo Conta</label>
                                        <input type="text" class="form-control" id="saldo_conta" sistema-mask="moeda"
                                            placeholder="Saldo da Conta">
                                    </div>
                                    <div class="col-4">
                                        <label class="text">Status</label>
                                        <select class="form-control" id="status_conta">
                                            <option value="">Selecione uma opção</option>
                                            <option value="ATIVO">ATIVO</option>
                                            <option value="INATIVO">INATIVO</option>
                                        </select>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-12">
                                        <label class="text">Descrição</label>
                                        <textarea class="form-control" id="descricao"
                                            placeholder="Informa a descrição"></textarea>
                                    </div>
                                </div>
                                <?php require_once 'includes/botao_cadastro.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = function () {
                    if (CODIGO_CONTA != '') {
                        sistema.request.post('/contas.php', {
                            'rota': 'pesquisa_conta',
                            'codigo_conta': CODIGO_CONTA
                        }, function (retorno) {
                            let conta = retorno.dados;

                            document.querySelector('#nome_conta').value = conta.nome_conta;
                            document.querySelector('#saldo_conta').value = conta.saldo_conta;
                            document.querySelector('#status_conta').value = conta.status;
                            document.querySelector('#descricao').value = conta.descricao;
                        });
                    }
                }
            </script>
            <?php
            require_once 'includes/footer.php';
});

router_add('salvar_dados', function () {
    $objeto_contas = new Contas();

    echo json_encode((array) ['status' => (bool) $objeto_contas->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('pesquisar_contas', function () {
    $objeto_conta = new Contas();
    $nome_conta = (string) (isset($_REQUEST['nome_conta']) ? (string) $_REQUEST['nome_conta'] : '');
    $status = (string) (isset($_REQUEST['status']) ? (string) $_REQUEST['status'] : 'TODOS');
    $descricao = (string) (isset($_REQUEST['descricao']) ? (string) $_REQUEST['descricao'] : '');
    $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa'] : '');

    $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['nome_empresa' => (bool) true], 'limite' => (int) 0];
    $filtro_montado = (array) [];

    if ($nome_conta != '') {
        array_push($filtro_montado, (array) ['nome_conta', '=', (string) $nome_conta]);
    }

    if ($status != 'TODOS') {
        array_push($filtro_montado, (array) ['status', '===', (string) $status]);
    }

    if ($descricao != '') {
        array_push($filtro_montado, (array) ['descricao', '=', (string) $descricao]);
    }

    if ($empresa != '') {
        array_push($filtro_montado, (array) ['empresa', '===', model_id($empresa)]);
    }

    if (empty($filtro_montado) == false) {
        $filtro['filtro'] = (array) ['and' => $filtro_montado];
    }

    echo json_encode(['dados' => (array) $objeto_conta->pesquisar_todos($filtro)], JSON_UNESCAPED_UNICODE);
});

router_add('pesquisa_conta', function () {
    $objeto_conta = new Contas();
    $codigo_conta = (string) (isset($_REQUEST['codigo_conta']) ? (string) $_REQUEST['codigo_conta'] : '');
    $retorno = (array) [];

    if ($codigo_conta != '') {
        $retorno = (array) $objeto_conta->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_conta)]]);
    }

    echo json_encode((array) ['dados' => (array) $retorno], JSON_UNESCAPED_UNICODE);
    exit;
});
?>