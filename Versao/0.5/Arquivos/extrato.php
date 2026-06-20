<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Extratos.php';
require_once 'modelos/ExtratoItens.php';
require_once 'modelos/Usuario.php';
require_once 'modelos/Empresa.php';

/** 
 * Rota responsável por salvar o extrato no banco de daados
 */
router_add('salvar_dados', function () {
    $objeto_extrato = new Extratos();

    echo json_encode((array) ['dados' => (array) $objeto_extrato->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

/** 
 * Rota responsável por salvar os itens do extrato no extrato
 */
router_add('salvar_item_extrato', function () {
    $objeto_item_extrato = new ExtratoItens();

    echo json_encode((array) ['dados' => (array) $objeto_item_extrato->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

/** 
 * Rota responsável por pesquisar os dados do extrato
 */
router_add('pesquisar_todos_extratos', function () {
    $objeto_extrato = new Extratos();

    $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa'] : '');
    $funcionario = (string) (isset($_REQUEST['funcionario']) ? (string) $_REQUEST['funcionario'] : '');
    $data_extrato = (string) (isset($_REQUEST['data_extrato']) ? (string) $_REQUEST['data_extrato'] : '');
    $data_pagamento = (string) (isset($_REQUEST['data_pagamento']) ? (string) $_REQUEST['data_pagamento'] : '');
    $status_extrato = (string) (isset($_REQUEST['status_extrato']) ? (string) $_REQUEST['status_extrato'] : 'TODOS');

    $filtro_montado = (array) [];

    if ($empresa != '') {
        array_push($filtro_montado, (array) ['empresa', '===', model_id($empresa)]);
    }

    if ($funcionario != '') {
        array_push($filtro_montado, (array) ['usuario', '===', model_id($funcionario)]);
    }

    if ($data_extrato != '') {
        array_push($filtro_montado, (array) ['data_extrato', '>=', model_date($data_extrato, '00:00:00')]);
        array_push($filtro_montado, (array) ['data_extrato', '<=', model_date($data_extrato, '23:59:59')]);
    }

    if ($data_pagamento != '') {
        array_push($filtro_montado, (array) ['data_pagamento', '>=', model_date($data_extrato, '00:00:00')]);
        array_push($filtro_montado, (array) ['data_pagamento', '<=', model_date($data_extrato, '23:59:59')]);
    }

    if ($status_extrato != 'TODOS') {
        array_push($filtro_montado, (array) ['status', '===', (string) $status_extrato]);
    }

    $retorno_extrato = (array) $objeto_extrato->pesquisar_todos((array) ['filtro' => (array) ['and' => (array) $filtro_montado], 'ordenacao' => (array) ['data_extrato' => (bool) false], 'limite' => (int) 100]);
    $retorno = (array) [];

    if (empty($retorno_extrato) == false) {
        $objeto_usuario = new Usuario();
        foreach ($retorno_extrato as $extrato) {
            $usuario = (array) $objeto_usuario->pesquisar((array) ['filtro' => (array) ['_id', '===', $extrato['usuario']]]);

            if (empty($usuario) == false) {
                $extrato['nome_usuario'] = (string) $usuario['nome_usuario'];
            }

            array_push($retorno, $extrato);
        }
    }

    echo json_encode((array) ['dados' => (array) $retorno], JSON_UNESCAPED_UNICODE);
});

/** 
 * Rota reponsável por pesquisar os dados do extrato de forma completa
 */
router_add('pesquisar_dados_extrato_completo', function () {
    $objeto_extrato = new Extratos();
    $objeto_item_extrato = new ExtratoItens();
    $objeto_extrato_item = new ItensExtratos();

    $codigo_extrato = (string) (isset($_REQUEST['codigo_extrato']) ? (string) $_REQUEST['codigo_extrato'] : '');

    $retorno_extrato = (array) [];
    $retorno_extato_itens = (array) [];

    if ($codigo_extrato != '') {
        $retorno_extrato = (array) $objeto_extrato->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_extrato)]]);
        $retorno_extato_itens_mexendo = (array) $objeto_item_extrato->pesquisar_todos((array) ['filtro' => (array) ['extrato', '===', model_id($codigo_extrato)], 'ordenacao' => (array) ['valor_lancamento_extrato' => (bool) false], 'limite' => (int) 0]);

        if (empty($retorno_extato_itens_mexendo) == false) {
            foreach ($retorno_extato_itens_mexendo as $item_extrato_menxendo) {
                $retorno_item_extrato = (array) $objeto_extrato_item->pesquisar((array) ['filtro' => (array) ['_id', '===', $item_extrato_menxendo['item_extrato']]]);

                $item_extrato_menxendo['item_extrato'] = (array) $retorno_item_extrato;
                array_push($retorno_extato_itens, $item_extrato_menxendo);
            }
        }
    }

    echo json_encode((array) ['dados' => (array) ['extrato' => $retorno_extrato, 'itens_extrato' => (array) $retorno_extato_itens]], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * Rota responsável por baixar o extrato no banco de dados, criando uma conta para pagamento posteriormente
 */
router_add('baixar_extrato', function () {
    $objeto_extrato = new Extratos();

    echo json_encode((array) $objeto_extrato->baixar_extrato($_REQUEST), JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * Rota para a impressão do status
 */
router_add('imprimir_extrato', function () {
    require_once 'includes/head_sem_menu.php';
    $codigo_extrato = (string) (isset($_REQUEST['codigo_extrato']) ? (string) $_REQUEST['codigo_extrato'] : '');
    $codigo_empresa = (string) (isset($_REQUEST['codigo_empresa']) ? (string) $_REQUEST['codigo_empresa'] : '');

    $objeto_extrato = new Extratos();
    $objeto_empresa = new Empresa();
    $objeto_usuario = new Usuario();
    $objeto_extrato_itens = new ExtratoItens();

    $retorno_extrato = (array) $objeto_extrato->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_extrato)]]);
    $retorno_empresa = (array) $objeto_empresa->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_empresa)]]);
    $retorno_usuario = (array) $objeto_usuario->pesquisar((array) ['filtro' => (array) ['_id', '===', $retorno_extrato['usuario']]]);
    $retorno_extrato_itens = (array) $objeto_extrato_itens->pesquisar_todos((array) ['filtro' => (array) ['extrato', '===', model_id($retorno_extrato['_id'])], 'ordenacao' => [], 'limite' => (int) 0]);

    $total_proventos = (float) 0;
    $total_descontos = (float) 0;
    $total_liquido = (float) 0;
    ?>
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }

        .texto-branco {
            color: white;
        }
    </style>
    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Holerite</h3>
            <button class="btn btn-primary no-print" onclick="window.print()">Imprimir</button>
        </div>

        <table class="table table-bordered">
            <tr>
                <td>Empresa</td>
                <td><?php echo $retorno_empresa['nome_empresa']; ?></td>
                <td>Mês</td>
                <td><?php echo convert_date($retorno_extrato['data_extrato'], 'd/m/Y'); ?></td>
            </tr>
            <tr>
                <td>Funcionário</td>
                <td><?php echo $retorno_usuario['nome_usuario']; ?></td>
                <td>Matrícula</td>
                <td><?php echo $retorno_usuario['_id']; ?></td>
            </tr>
            <tr>
                <td>Cargo</td>
                <td><?php echo (isset($retorno_usuario['cargo']) ? (string) $retorno_usuario['cargo'] : ''); ?> </td>
                <td>Admissão</td>
                <td><?php echo convert_date($retorno_usuario['data_cadastro'], 'd/m/Y'); ?></td>
            </tr>
        </table>

        <h5 class="mt-4">Proventos</h5>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Data</th>
                    <th class="text-end">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($retorno_extrato_itens as $extrato_item) {
                    if ($extrato_item['tipo_item_extrato'] == 'CREDITO') {
                        echo '<tr>';
                        echo '<td>' . $extrato_item['nome_item_extrato'] . '</td>';

                        if (array_key_exists('data_lancamento_extrato', $extrato_item) == true) {
                            echo '<td>' . convert_date($extrato_item['data_lancamento_extrato'], 'd/m/Y') . '</td>';
                        } else {
                            echo '<td>' . $data->format('d/m/Y') . '</td>';
                        }

                        echo '<td class="text-end">R$ ' . formatar_numero($extrato_item['valor_lancamento_extrato'], 2, ',', '.') . '</td>';
                        echo '</tr>';
                        $total_proventos = (float) arredondar($total_proventos, '+', $extrato_item['valor_lancamento_extrato']);
                    }
                }
                ?>
            </tbody>
        </table>

        <h5 class="mt-4">Descontos</h5>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Data</th>
                    <th class="text-end">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($retorno_extrato_itens as $extrato_item) {
                    if ($extrato_item['tipo_item_extrato'] == 'DEBITO') {
                        echo '<tr>';
                        echo '<td>' . $extrato_item['nome_item_extrato'] . '</td>';

                        if (array_key_exists('data_lancamento_extrato', $extrato_item) == true) {
                            echo '<td>' . convert_date($extrato_item['data_lancamento_extrato'], 'd/m/Y') . '</td>';
                        } else {
                            echo '<td>' . $data->format('d/m/Y') . '</td>';
                        }

                        echo '<td class="text-end">R$ ' . formatar_numero($extrato_item['valor_lancamento_extrato'], 2, ',', '.') . '</td>';
                        echo '</tr>';
                        $total_descontos = (float) arredondar($total_descontos, '+', $extrato_item['valor_lancamento_extrato']);
                    }
                }

                $total_liquido = (float) arredondar($total_proventos, '-', $total_descontos);
                ?>
            </tbody>
        </table>

        <table class="table table-bordered mt-4">
            <tr>
                <td>Total Proventos</td>
                <td class="text-end">R$ <?php echo formatar_numero($total_proventos); ?></td>
            </tr>
            <tr>
                <td>Total Descontos</td>
                <td class="text-end">R$ <?php echo formatar_numero($total_descontos); ?></td>
            </tr>
            <tr class="table-success">
                <td>Salário Líquido</td>
                <td class="text-end"><strong>R$ <?php echo formatar_numero($total_liquido); ?></strong></td>
            </tr>
        </table>
        <div style="margin-top: 50px;">
            <div style="border-top: 1px solid #000; width: 300px;"></div>
            <p><?php echo $retorno_usuario['nome_usuario']; ?></p>
        </div>
    </div>
    <?php
    require_once 'includes/footer_sem.php';
    exit;
});

/** 
 * Rota resposável por pesquisar o extrato
 */
router_add('index', function () {
    require_once 'includes/head.php';
    ?>
    <script>
        const CODIGO_EMPRESA = "<?php echo $codigo_empresa; ?>";

        /**
         * Função responsável por abrir o formulário de cadastro de status
         * @param {*} codigo_extrato
         */
        function cadastro_extrato(codigo_extrato) {
            window.location.href = sistema.url('/extrato.php', {
                'rota': 'cadastro_extrato',
                'codigo_extrato': codigo_extrato
            });
        }

        /**
         * Função responsável por pesquisar os extratos cadastrados no banco de dados
         */
        function pesquisar_extrato() {
            barra_progresso('Carregando extratos...');

            let funcionario = document.querySelector('#funcionario').value;
            let data_extrato = document.querySelector('#data_extrato').value;
            let data_pagamento = document.querySelector('#data_pagamento').value;
            let status_extrato = document.querySelector('#status_extrato').value;

            let dados = { 'rota': 'pesquisar_todos_extratos', 'empresa': CODIGO_EMPRESA, 'funcionario': funcionario, 'data_extrato': data_extrato, 'data_pagamento': data_pagamento, 'status_extrato': status_extrato };

            sistema.request.post('/extrato.php', dados, function (retorno) {
                let extratos = retorno.dados;
                let tamanho_retorno = extratos.length;
                let index = 0;

                let tabela = document.querySelector("#tabela_extratos tbody");

                tabela = sistema.remover_linha_tabela(tabela);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], 'NENHUM EXTRATO ENCONTRADO!', 'inner', true, 15));
                    tabela.appendChild(linha);

                    Swal.fire({ icon: 'warning', title: 'Nenhum extrato encontrado!' });
                    return;
                }

                function processar_item() {
                    if (index >= tamanho_retorno) {
                        Swal.close();
                        return;
                    }

                    let extrato = extratos[index];

                    let linha = document.createElement('tr');

                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], extrato.nome_usuario, 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.retornar_data(extrato.data_extrato), 'inner'));

                    if (extrato.status == 'PAGO') {
                        linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.retornar_data(extrato.data_pagamento), 'inner'));
                    } else {
                        linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], '', 'inner'));
                    }

                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.number_format(extrato.valor_bruto), 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.number_format(extrato.valor_entrada), 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.number_format(extrato.valor_desconto), 'inner'));

                    if (extrato.valor_liquido <= 0) {
                        linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold', 'text-danger'], sistema.number_format(extrato.valor_liquido), 'inner'));
                    } else {
                        linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.number_format(extrato.valor_liquido), 'inner'));
                    }

                    if (extrato.status == 'AGUARDANDO' || extrato.status == '') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('bota_extrato_' + extrato._id.$oid, 'AGUARDANDO', ['btn', 'btn-outline-secondary'], function status_extrato() { }), 'append'));
                    } else if (extrato.status == 'PAGO') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('bota_extrato_' + extrato._id.$oid, 'PAGO', ['btn', 'btn-outline-success'], function status_extrato() { }), 'append'));
                    } else if (extrato.status == 'CANCELADO') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('bota_extrato_' + extrato._id.$oid, 'CANCELADO', ['btn', 'btn-outline-warning'], function status_extrato() { }), 'append'));
                    } else if (extrato.status == 'VENCIDA') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('bota_extrato_' + extrato._id.$oid, 'VENCIDA', ['btn', 'btn-outline-danger'], function status_extrato() { }), 'append'));
                    }

                    if (extrato.status == 'AGUARDANDO' || extrato.status == 'VENCIDA' || extrato.status == '') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('Botao_imprimir_extrato_' + extrato._id.$oid, 'IMPRIMIR', ['btn', 'btn-success'], function model_impressao() {
                            abrir_modal_impressao(extrato._id.$oid);
                        }), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('Botao_baixar_extrato_' + extrato._id.$oid, 'BAIXAR', ['btn', 'btn-primary'], function model_cadastro() {
                            baixar_extrato(extrato._id.$oid);
                        }), 'append'));

                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('Botao_editar_extrato_' + extrato._id.$oid, 'EDITAR', ['btn', 'btn-secondary'], function model_cadastro() {
                            cadastro_extrato(extrato._id.$oid);
                        }), 'append'));
                    } else {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('Botao_imprimir_extrato_' + extrato._id.$oid, 'IMPRIMIR', ['btn', 'btn-success'], function model_impressao() {
                            abrir_modal_impressao(extrato._id.$oid)
                        }), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('Botao_baixar_extrato_' + extrato._id.$oid, 'BAIXAR', ['btn', 'btn-primary', 'disabled'], function model_cadastro() {
                        }), 'append'));

                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('Botao_editar_extrato_' + extrato._id.$oid, 'EDITAR', ['btn', 'btn-secondary', 'disabled'], function model_cadastro() {
                        }), 'append'));

                    }

                    tabela.appendChild(linha);

                    atualizar_barra_progresso(index, tamanho_retorno, document.querySelector('#barra_progresso'), document.querySelector('#texto_progresso'));

                    index++;
                    setTimeout(processar_item, 1);
                }

                processar_item();
            });
        }

        /**
         * Função responsável por baixar o extrato no banco de dados, marcando o mesmo como pago e criando uma conta para realizar o pagamento
         * @param {*} codigo_extrato
         */
        function baixar_extrato(codigo_extrato) {
            sistema.request.post('/extrato.php', { 'rota': 'baixar_extrato', 'codigo_extrato': codigo_extrato }, function (retorno) {
                validar_retorno(retorno, '/extrato.php');
            });
        }

        /**
         * Função responsável por abrir o modal de impressão e extrato
         * @param {*} codigo_extrato
         * 
         */
        function abrir_modal_impressao(codigo_extrato) {
            let largura = 1200;
            let altura = 500;
            let left = (screen.width - largura) / 2;
            let top = (screen.height - altura) / 2;
            let url = sistema.url('/extrato.php', {
                'rota': 'imprimir_extrato',
                'codigo_extrato': codigo_extrato,
                'codigo_empresa': CODIGO_EMPRESA
            });
            let nome = 'Impressão de Extrato';
            let janela = window.open(url, nome, `width=${largura}, height=${altura}, left=${left}, top=${top}`);
            if (window.focus) {
                janela.focus();
            }
        }

        /** 
         * Função responsável por pesquisar os funcionários do sistema
        */
        function pesquisar_funcionarios() {
            sistema.request.post('/usuarios.php', { 'rota': 'pesquisar_usuarios', 'tipo_usuario': 'Administrador', 'status_usuario': true, 'empresa': CODIGO_EMPRESA }, function (retorno) {
                let funcionarios = document.querySelector('#funcionario');

                sistema.each(retorno.dados, function (index, funcionario) {
                    funcionarios.appendChild(sistema.gerar_option(funcionario._id.$oid, funcionario.nome_usuario));
                });
            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Extratos</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center"
                            onclick="cadastro_extrato('');">
                            Gerar extratos
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">Pesquisa de Extratos</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3 text-center">
                                    <label class="text">Funcionário</label>
                                    <select class="form-control select2" id="funcionario">
                                        <option value="">Selecione um funcionário</option>
                                    </select>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Data Extrato</label>
                                    <input type="date" class="form-control" id="data_extrato">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Data Pagamento</label>
                                    <input type="date" class="form-control" id="data_pagamento">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Status</label>
                                    <select class="form-control select2" id="status_extrato">
                                        <option value="TODOS">TODOS</option>
                                        <option value="AGUARDANDO">AGUARDANDO</option>
                                        <option value="CANCELADO">CANCELADO</option>
                                        <option value="VENCIDO">VENCIDO</option>
                                    </select>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-3 push-9">
                                    <button class="btn btn-secondary w-100"
                                        onclick="pesquisar_extrato();">Pesquisar</button>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_extratos">
                                            <thead>
                                                <tr class="text-center">
                                                    <th scope="col">Funcionário</th>
                                                    <th scope="col">Data Extrato</th>
                                                    <th scope="col">Data Pagamento</th>
                                                    <th scope="col">Valor Bruto</th>
                                                    <th scope="col">Valor Entrada</th>
                                                    <th scope="col">Valor Valor Saída</th>
                                                    <th scope="col">Valor líquido</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Imprimir</th>
                                                    <th scope="col">Baixar</th>
                                                    <th scope="col">Editar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="15" class="text-center">NENHUM EXTRATO ENCONTRADO COM OS
                                                        FILTROS PASSADOS</td>
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
                pesquisar_funcionarios();
                pesquisar_extrato();
            }
        </script>
        <?php
        require_once 'includes/footer.php';
        exit;
});

/**
 * Rota responsável por cadastrar um novo extrato
 */
router_add('cadastro_extrato', function () {
    require_once 'includes/head.php';
    $codigo_extrato = (string) (isset($_REQUEST['codigo_extrato']) ? (string) $_REQUEST['codigo_extrato'] : '');
    ?>
        <script>
            const CODIGO_EXTRATO = "<?php echo $codigo_extrato; ?>";
            const EMPRESA = "<?php echo $codigo_empresa; ?>";
            const DATA_HOJE = "<?php echo $data->format('Y-m-d'); ?>";
            const DATA_FORMATADA = "<?php echo $data->format('d/m/Y'); ?>";

            function pesquisar_usuario() {
                sistema.request.post('/usuarios.php', {
                    'rota': 'pesquisar_usuarios',
                    'empresa': EMPRESA,
                    'tipo_usuario': 'Administrador'
                }, function (retorno) {
                    let select = document.querySelector('#usuario');
                    let usuarios = retorno.dados;

                    sistema.each(usuarios, function (index, usuario) {
                        select.appendChild(sistema.gerar_option(usuario._id.$oid, usuario.nome_usuario));
                    });
                });
            }

            function pesquisar_usuario_selecionado() {
                let codigo_usuario = document.querySelector('#usuario').value;

                sistema.request.post('/usuarios.php', {
                    'rota': 'pesquisa_usuario',
                    'codigo_usuario': codigo_usuario
                }, function (retorno) {
                    let usuario = retorno.dados;
                    document.querySelector('#valor_bruto').value = sistema.number_format(usuario.salario);
                });
            }

            function pesquisar_item_extrato() {
                sistema.request.post('/item_extrato.php', {
                    'rota': 'pesquisar_todos',
                    'empresa': EMPRESA
                }, function (retorno) {
                    let select = document.querySelector('#item_extrato');
                    let item_extratos = retorno.dados;

                    sistema.each(item_extratos, function (index, item_extrato) {
                        select.appendChild(sistema.gerar_option(item_extrato._id.$oid, item_extrato.nome_item_extrato));
                    });
                });
            }

            function voltar() {
                window.location.href = sistema.url('/extrato.php', {
                    'rota': 'index'
                });
            }

            function salvar_dados() {
                let codigo_extrato = document.querySelector('#codigo_extrato').value;
                let usuario = document.querySelector('#usuario').value;
                let valor_bruto = document.querySelector('#valor_bruto').value;
                let valor_entrada = document.querySelector('#valor_entrada').value;
                let valor_desconto = document.querySelector('#valor_desconto').value;
                let valor_liquido = document.querySelector('#valor_liquido').value;
                let data_extrato = document.querySelector('#data_extrato').value;
                let data_pagamento = document.querySelector('#data_pagamento').value;
                let status_extratro = document.querySelector('#status_extrato').value;

                let validacao = true;

                if (usuario == '') {
                    validacao = false;
                    alerta_campo_vazio('USUÁRIO');
                }

                if (validacao == true) {
                    sistema.request.post('/extrato.php', {
                        'rota': 'salvar_dados',
                        'codigo_extrato': codigo_extrato,
                        'empresa': EMPRESA,
                        'usuario': usuario,
                        'total_bruto': valor_bruto,
                        'valor_entrada': valor_entrada,
                        'valor_liquido': valor_liquido,
                        'total_desconto': valor_desconto,
                        'data_extrato': data_extrato,
                        'data_pagamento': data_pagamento,
                        'status_extrato': status_extratro
                    }, function (retorno) {
                        document.querySelector('#codigo_extrato').value = retorno.dados._id;
                        this.Swal.fire({
                            title: "SUCESSO NA OPERAÇÃO!",
                            text: "Operação realizada com sucesso!",
                            icon: "success"
                        });
                    });
                }
            }

            function adicionar_item_extrato() {
                let item_extrato = document.querySelector("#item_extrato").value;
                let valor_lancamento_extrato = document.querySelector("#valor_lancamento_extrato").value;
                let codigo_extrato = document.querySelector('#codigo_extrato').value;

                sistema.request.post('/extrato.php', {
                    'rota': 'salvar_item_extrato',
                    'extrato': codigo_extrato,
                    'item_extrato': item_extrato,
                    'valor_lancamento_extrato': valor_lancamento_extrato
                }, function (retorno) {
                    let item_extrato = retorno.dados.item_extrato;
                    let extrato_item = retorno.dados.extrato_item;
                    let tabela = document.querySelector('#tabela_extratos_item tbody');

                    let linha = document.createElement('tr');

                    linha.appendChild(sistema.gerar_td(['text-center'], item_extrato.nome_item_extrato, 'inner'));

                    if (item_extrato.tipo_item_extrato == 'CREDITO') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(extrato_item.valor_lancamento_extrato), 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));

                        let valor = sistema.float(sistema.replace(',', '.', document.querySelector("#valor_entrada").value));
                        valor = valor + extrato_item.valor_lancamento_extrato;
                        document.querySelector('#valor_entrada').value = sistema.number_format(valor);
                    } else {
                        linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(extrato_item.valor_lancamento_extrato), 'inner'));

                        let valor = sistema.float(sistema.replace(',', '.', document.querySelector('#valor_desconto').value));

                        valor = valor + extrato_item.valor_lancamento_extrato;
                        document.querySelector('#valor_desconto').value = sistema.number_format(valor);
                    }

                    linha.appendChild(sistema.gerar_td(['text-center'], DATA_FORMATADA, 'inner'));

                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(extrato_item.valor_lancamento_extrato), 'inner'));

                    tabela.appendChild(linha);

                    let valor_entrada = sistema.replace(',', '.', document.querySelector('#valor_entrada').value);
                    let valor_desconto = sistema.replace(',', '.', document.querySelector('#valor_desconto').value);
                    let valor_liquido = 0;

                    valor_liquido = valor_entrada + valor_liquido;
                    valor_liquido = valor_liquido - valor_desconto;
                    document.querySelector('#valor_liquido').value = sistema.number_format(valor_liquido);

                    $('#modal_extrato_item').modal('hide');
                });
            }

            function pesquisar_dados_extrato() {
                sistema.request.post('/extrato.php', {
                    'rota': 'pesquisar_dados_extrato_completo',
                    'codigo_extrato': CODIGO_EXTRATO
                }, function (retorno) {
                    let extrato = retorno.dados.extrato;
                    let item_extrato = retorno.dados.itens_extrato;

                    document.querySelector('#codigo_extrato').value = CODIGO_EXTRATO;

                    document.querySelector('#usuario').value = extrato.usuario.$oid;
                    document.querySelector('#valor_bruto').value = sistema.number_format(extrato.valor_bruto);
                    document.querySelector('#valor_desconto').value = sistema.number_format(extrato.valor_desconto);
                    document.querySelector('#valor_entrada').value = sistema.number_format(extrato.valor_entrada);
                    document.querySelector('#valor_liquido').value = sistema.number_format(extrato.valor_liquido);
                    document.querySelector('#data_extrato').value = sistema.retornar_data(extrato.data_extrato, 'AMERICANO');
                    document.querySelector('#data_pagamento').value = sistema.retornar_data(extrato.data_pagamento, 'AMERICANO');
                    document.querySelector('#status_extrato').value = extrato.status;

                    let tabela = document.querySelector('#tabela_extratos_item tbody');

                    sistema.each(item_extrato, function (index, item) {
                        let linha = document.createElement('tr');
                        let item_extrato = item.item_extrato;

                        linha.appendChild(sistema.gerar_td(['text-center'], item_extrato.nome_item_extrato, 'inner'));

                        if (item_extrato.tipo_item_extrato == 'DEBITO') {
                            linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(item.valor_lancamento_extrato), 'inner'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(item.valor_lancamento_extrato), 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));
                        }

                        if (item?.data_lancamento_extrato) {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(item.data_lancamento_extrato), 'inner'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));
                        }

                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(item.valor_lancamento_extrato), 'inner'));

                        tabela.appendChild(linha);
                    });
                });
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                    <div>
                        <h6>Extratos</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <div class="card-title">Cadastro Extrato</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <input type="hidden" id="codigo_extrato">
                                    <div class="col-4 text-center">
                                        <label class="text">Usuário</label>
                                        <select class="form-control select2" id="usuario"
                                            onchange="pesquisar_usuario_selecionado();">
                                            <option value="">Selecione um Usuário</option>
                                        </select>
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Pró Labore</label>
                                        <input type="text" class="form-control" sistema-mask="moeda"
                                            placeholder="Pró Labore" id="valor_bruto">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Valor Entrada</label>
                                        <input type="text" class="form-control" sistema-mask="moeda"
                                            placeholder="Valor valor_entrada" id="valor_entrada" value="0">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Valor Desconto</label>
                                        <input type="text" class="form-control" sistema-mask="moeda"
                                            placeholder="Valor Desconto" id="valor_desconto" value="0">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Valor Líquido</label>
                                        <input type="text" class="form-control" sistema-mask="moeda"
                                            placeholder="Valor Líquido" id="valor_liquido" value="0">
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <label class="text">Data Extrato</label>
                                        <input type="date" class="form-control" placeholder="Data Extrato"
                                            id="data_extrato">
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="text">Data Pagamento</label>
                                        <input type="date" class="form-control" placeholder="Data Extrato"
                                            id="data_pagamento">
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="text">Status Usuário</label>
                                        <select class="form-control" id="status_extrato">
                                            <option value="">Selecione um Opção</option>
                                            <option value="AGUARDANDO">AGUARDANDO</option>
                                            <option value="PAGO">PAGO</option>
                                            <option value="VENCIDO">VENCIDO</option>
                                            <option value="CANCELADO">CANCELADO</option>
                                        </select>
                                    </div>
                                </div>
                                <br />
                                <?php require_once 'includes/botao_cadastro.php'; ?>
                                <br />
                                <div class="row">
                                    <div class="col-4 push-8">
                                        <button
                                            class="btn btn-primary d-flex align-items-center justify-content-center w-100 btn-lg"
                                            data-bs-toggle="modal" data-bs-target="#modal_extrato_item">Adicionar Item Ao
                                            Extrato</button>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-nowrap text-nowrap table-hover"
                                                id="tabela_extratos_item">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th scope="col">ITEM</th>
                                                        <th scope="col">ENTRADA</th>
                                                        <th scope="col">SAIDA</th>
                                                        <th scope="col">DATA</th>
                                                        <th scope="col">VALOR LIQUIDO</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
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
            <div class="modal fade" id="modal_extrato_item" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Adicionar Item Extrato</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <label class="text">Item Extrato</label>
                                    <select id="item_extrato" class="form-control">
                                        <option value="">Selecione uma Opção</option>
                                    </select>
                                </div>
                                <div class="col-6 text-center">
                                    <label class="text">Valor</label>
                                    <input type="text" class="form-control" id="valor_lancamento_extrato"
                                        placeholder="Valor Lançamento">
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-4 push-4">
                                    <button type="button" class="btn btn-danger w-100 btn-lg" data-bs-dismiss="modal"
                                        aria-label="Close">Fechar</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-success w-100 btn-lg"
                                        onclick="adicionar_item_extrato();">Adicionar Ao Extrato</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = function () {
                    let botao_limpar_dados = document.querySelector('#btn_limpar_dados');
                    document.querySelector('#data_extrato').value = DATA_HOJE;

                    botao_limpar_dados.disabled = true;
                    pesquisar_usuario();
                    pesquisar_item_extrato();

                    if (CODIGO_EXTRATO != '') {
                        pesquisar_dados_extrato();
                    }
                }
            </script>
            <?php
            require_once 'includes/footer.php';
            exit;
});
?>