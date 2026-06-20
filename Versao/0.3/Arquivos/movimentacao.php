<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Movimentacao.php';
require_once 'modelos/Contas.php';

router_add('salvar_dados', function () {
    $objeto_movimentacao = new Movimentacao();

    echo json_encode((array) ['status' => (bool) $objeto_movimentacao->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

router_add('pesquisar_contas', function () {
    $data = new DateTime();
    $objeto_movimentacao = new Movimentacao();
    $objeto_conta = new Contas();

    $conta = (string) (isset($_REQUEST['conta']) ? (string) $_REQUEST['conta'] : 'TODOS');
    $tipo_lancamento = (string) (isset($_REQUEST['tipo_lancamento']) ? (string) $_REQUEST['tipo_lancamento'] : 'TODOS');
    $data_inicio = (isset($_REQUEST['data_inicio']) ? model_date($_REQUEST['data_inicio'], '00:00:00') : model_date($data->format('Y-m-01'), '00:00:00'));
    $data_final = (isset($_REQUEST['data_final']) ? model_date($_REQUEST['data_final'], '23:59:59') : model_date($data->format('Y-m-t'), '23:59:59'));
    $empresa = (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa'] : '');

    $retorno_validacao = (array) [];
    $filtro = (array) [];
    $filtro_pesquisa = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['data_lancamento' => (bool) false], 'limite' => (int) 0];
    $retorno = (array) [];

    array_push($filtro, ['data_lancamento', '>=', $data_inicio]);
    array_push($filtro, ['data_lancamento', '<=', $data_final]);

    if ($conta != 'TODOS') {
        array_push($filtro, ['conta', '===', model_id($conta)]);
    }

    if ($tipo_lancamento != 'TODOS') {
        array_push($filtro, ['tipo_lancamento', '===', (string) $tipo_lancamento]);
    }

    if ($empresa != '') {
        array_push($filtro, ['empresa', '===', model_id($empresa)]);
    }

    $filtro_pesquisa['filtro'] = (array) ['and' => (array) $filtro];
    $retorno_validacao = (array) $objeto_movimentacao->pesquisar_todos($filtro_pesquisa);

    if (empty($retorno_validacao) == false) {
        foreach ($retorno_validacao as $movimentacao) {
            $retorno_temporario = (array) [];
            $retorno_conta = (array) $objeto_conta->pesquisar(['filtro' => (array) ['_id', '===', $movimentacao['conta']]]);

            $retorno_temporario = (array) $movimentacao;

            if (empty($retorno_conta) == false) {
                $retorno_temporario['nome_conta'] = (string) $retorno_conta['nome_conta'];
            }

            array_push($retorno, $retorno_temporario);
        }
    }

    echo json_encode((array) ['dados' => (array) $retorno], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('deletar_movimentacao', function () {
    $objeto_movimentacao = new Movimentacao();
    $codigo_movimentacao = (string) (isset($_REQUEST['codigo_movimentacao']) ? (string) $_REQUEST['codigo_movimentacao'] : '');
    $filtro = (array) [];
    $retorno = (bool) false;

    if ($codigo_movimentacao != '') {
        $filtro['filtro'] = (array) ['_id', '===', model_id($codigo_movimentacao)];
        $retorno = (bool) $objeto_movimentacao->deletar_movimentacao($filtro);
    }

    echo json_encode(['status' => (bool) $retorno]);
});

router_add('index', function () {
    require_once 'includes/head.php';

    $data_inicio = $data->format('Y-m-01');
    $ultimo_dia = $data->format('Y-m-t');
    ?>
    <script>
        const DATA_INICIAL = "<?php echo $data_inicio; ?>";
        const DATA_FINAL = "<?php echo $ultimo_dia; ?>";
        const EMPRESA = "<?php echo $codigo_empresa; ?>";

        function cadastro_movimentacao(codigo_movimentacao) {
            window.location.href = sistema.url('/movimentacao.php', {
                'rota': 'cadastro_movimentacao',
                'codigo_movimentacao': codigo_movimentacao
            });
        }

        function pesquisar_movimentacao() {
            let conta = document.querySelector('#conta').value;
            let tipo_lancamento = document.querySelector('#tipo_lancamento').value;
            let data_inicio = document.querySelector('#data_inicio').value;
            let data_final = document.querySelector('#data_final').value;

            sistema.request.post('/movimentacao.php', {
                'rota': 'pesquisar_contas',
                'conta': conta,
                'tipo_lancamento': tipo_lancamento,
                'data_inicio': data_inicio,
                'data_final': data_final,
                'empresa': EMPRESA
            }, function (retorno) {
                let movimentacoes = retorno.dados;
                let tamanho_retorno = movimentacoes.length;
                let tabela = document.querySelector('#tabela_movimentacoes tbody');

                tabela = sistema.remover_linha_tabela(tabela);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');

                    linha.appendChild(sistema.gerar_td(['text-center'], 'NENHUMA MOVIMENTAÇÃO ENCONTRADA COM OS FILTROS PASSADOS!', 'inner', true, '10'));
                    tabela.appendChild(linha);
                } else {
                    sistema.each(movimentacoes, function (index, movimentacao) {

                        let linha = document.createElement('tr');

                        linha.appendChild(sistema.gerar_td(['text-center'], movimentacao.nome_conta, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], movimentacao.descricao, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(movimentacao.valor_lancamento), 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(movimentacao.data_lancamento, 'BRASIL', true), 'inner'));

                        if (movimentacao.tipo_lancamento == 'CREDITO' || movimentacao.tipo_lancamento == 'TRANSFERENCIA_CREDITO') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_lancamento_' + movimentacao._id.$oid, 'CREDITO', ['btn', 'btn-success'], function visualizar() { }), 'append'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_lancamento_' + movimentacao._id.$oid, 'DEBITO', ['btn', 'btn-danger'], function visualizar() { }), 'append'));
                        }

                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_deletar_movimentacao_' + movimentacao._id.$oid, 'EXCLUIR', ['btn', 'btn-danger'], function deletar_movimentacao_botao() {
                            deletar_movimentacao(movimentacao._id.$oid);
                        }), 'append'));
                        tabela.appendChild(linha);
                    });
                }
            });
        }

        function deletar_movimentacao(codigo_movimentacao) {
            Swal.fire({
                title: "Confirmar Exclusão!",
                text: "Excluir a movimentação não retornar o valor para a conta. Confirma a exclusão?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, deletar movimentação!"
            }).then((result) => {
                if (result.isConfirmed) {
                    sistema.request.post('/movimentacao.php', {
                        'rota': 'deletar_movimentacao',
                        'codigo_movimentacao': codigo_movimentacao
                    }, function (retorno) {
                        validar_retorno(retorno, '/movimentacao.php');
                    });
                }
            });
        }

        function pesquisa_contas_select() {
            let codigo_empresa = "<?php echo $_SESSION['codigo_empresa']; ?>";
            sistema.request.post('/contas.php', {
                'rota': 'pesquisar_contas',
                'empresa': codigo_empresa,
                'status': 'ATIVO'
            }, function (retorno) {
                let select = document.querySelector('#conta');
                let conta = retorno.dados;

                sistema.each(conta, function (index, contas) {
                    let option = sistema.gerar_option(contas._id.$oid, contas.nome_conta + ' | ' + contas.saldo_conta);
                    select.appendChild(option);
                });
            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Movimentação</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center"
                            onclick="cadastro_movimentacao('');">
                            Cadastrar Movimentação
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Pesquisa de Movimentação
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3 text-center">
                                    <label class="text">Conta</label>
                                    <select id="conta" class="form-control">
                                        <option value="TODOS">Todas as Contas</option>
                                    </select>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Tipo Lançamento</label>
                                    <select class="form-control" id="tipo_lancamento">
                                        <option value="TODOS">Todos os Lançamentos</option>
                                        <option value="CREDITO">CRÉDITO</option>
                                        <option value="DEBITO">DÉBITO</option>
                                        <option value="TRANSFERENCIA">TRANSFERÊNCIA</option>
                                    </select>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Data Inícial</label>
                                    <input type="date" id="data_inicio" class="form-control">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Data Final</label>
                                    <input type="date" id="data_final" class="form-control">
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-3 push-9">
                                    <button class="btn btn-secondary w-100"
                                        onclick="pesquisar_movimentacao();">Pesquisar</button>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_movimentacoes">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-center">Nome Conta</th>
                                                    <th scope="col" class="text-center">Descrição</th>
                                                    <th scope="col" class="text-center">Valor</th>
                                                    <th scope="col" class="text-center">Data</th>
                                                    <th scope="col" class="text-center">Tipo</th>
                                                    <th scope="col" class="text-center">Ação</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="10" class="text-center">UTILIZE O FILTRO PARA FACILITAR A
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
                document.querySelector('#data_inicio').value = DATA_INICIAL;
                document.querySelector('#data_final').value = DATA_FINAL;
                pesquisar_movimentacao();
                pesquisa_contas_select();
            }
        </script>
        <?php
        require_once 'includes/footer.php';
        exit;
});

router_add('cadastro_movimentacao', function () {
    require_once 'includes/head.php';
    $hoje = $data->format('Y-m-d');
    ?>
        <script>
            const HOJE = "<?php echo $hoje; ?>";
            const EMPRESA = "<?php echo $codigo_empresa; ?>";

            function pesquisar_contas() {
                sistema.request.post('/contas.php', {
                    'rota': 'pesquisar_contas',
                    'status': 'ATIVO',
                    'empresa': EMPRESA
                }, function (retorno) {
                    let contas = retorno.dados;
                    let select = document.querySelector('#conta');
                    let select_destino = document.querySelector('#conta_destino');

                    sistema.each(contas, function (index, conta) {
                        let option = sistema.gerar_option(conta._id.$oid, conta.nome_conta + ' | ' + conta.saldo_conta);
                        select.appendChild(option);
                    });
                    sistema.each(contas, function (index, conta) {
                        let option = sistema.gerar_option(conta._id.$oid, conta.nome_conta + ' | ' + conta.saldo_conta);
                        select_destino.appendChild(option);
                    });
                });
            }

            function salvar_dados() {
                let conta = document.querySelector('#conta').value;
                let descricao = document.querySelector('#descricao').value;
                let data_lancamento = document.querySelector('#data_lancamento').value;
                let tipo_lancamento = document.querySelector('#tipo_lancamento').value;
                let valor_lancamento = document.querySelector('#valor_lancamento').value;
                let conta_destino = document.querySelector('#conta_destino').value;

                let valida_conta = true;
                let valida_descricao = true;
                let valida_tipo_lancamento = true;
                let valida_valor_lancamento = true;

                if (conta == '') {
                    alerta_campo_vazio('CONTA');
                    valida_conta = false;
                }

                if (descricao == '') {
                    alerta_campo_vazio('DESCRIÇÃO');
                    valida_descricao = false;
                }

                if (tipo_lancamento == '') {
                    alerta_campo_vazio('TIPO LANÇAMENTO');
                    valida_tipo_lancamento = false;
                }

                if (valor_lancamento == '') {
                    alerta_campo_vazio('VALOR');
                    valida_valor_lancamento = false;
                }

                if (valida_conta == true && valida_descricao == true && valida_tipo_lancamento == true && valida_valor_lancamento == true) {

                    if (conta_destino != '') {
                        sistema.request.post('/movimentacao.php', {
                            'rota': 'salvar_dados',
                            'conta': conta,
                            'descricao': descricao,
                            'data_lancamento': data_lancamento,
                            'tipo_lancamento': tipo_lancamento,
                            'valor_lancamento': valor_lancamento,
                            'empresa': EMPRESA,
                            'conta_destino': conta_destino
                        }, function (retorno) {
                            validar_retorno(retorno, '/movimentacao.php');
                        });
                    } else {
                        sistema.request.post('/movimentacao.php', {
                            'rota': 'salvar_dados',
                            'conta': conta,
                            'descricao': descricao,
                            'data_lancamento': data_lancamento,
                            'tipo_lancamento': tipo_lancamento,
                            'valor_lancamento': valor_lancamento,
                            'empresa': EMPRESA
                        }, function (retorno) {
                            validar_retorno(retorno, '/movimentacao.php');
                        });
                    }
                }

            }

            function limpar_dados() {
                document.querySelector('#conta').value = '';
                document.querySelector('#conta_destino').value = '';
                document.querySelector('#tipo_lancamento').value = '';
                document.querySelector('#data_lancamento').value = HOJE;
                document.querySelector('#descricao').value = '';
                document.querySelector('#valor_lancamento').value = 0;
            }

            function voltar() {
                window.location.href = sistema.url('/movimentacao.php', {
                    'rota': 'index'
                });
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Cadastro Movimentação
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4 text-center">
                                    <label class="text">Conta Origem</label>
                                    <select class="form-control" id="conta">
                                        <option value="">Selecione uma opção</option>
                                    </select>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Tipo de Lançamento</label>
                                    <select class="form-control" id="tipo_lancamento">
                                        <option value="">Selecione uma Opção</option>
                                        <option value="CREDITO">CREDITO</option>
                                        <option value="DEBITO">DÉBITO</option>
                                        <option value="TRANSFERENCIA">TRANSFÊNCIA</option>
                                    </select>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Valor Lançamento</label>
                                    <input type="text" class="form-control" id="valor_lancamento" sistema-mask="moeda">
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Data Lançamento</label>
                                    <input type="date" class="form-control" id="data_lancamento">
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Tipo de Lançamento</label>
                                    <select class="form-control" id="conta_destino">
                                        <option value="">Selecione uma Opção</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-center">
                                    <label class="text">Descrição</label>
                                    <textarea id="descricao" class="form-control text-uppercase"></textarea>
                                </div>
                            </div>
                            <br />
                            <?php require_once 'includes/botao_cadastro.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = function () {
                    document.querySelector('#data_lancamento').value = HOJE;
                    pesquisar_contas();
                }
            </script>
            <?php
            require_once 'includes/footer.php';
            exit;
});
?>