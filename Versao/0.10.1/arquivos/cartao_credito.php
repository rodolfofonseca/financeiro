<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/CartaoCredito.php';

/**
 * Rota responsável por realizar o salvamentos dos dados do cartão
 */
router_add('salvar_dados', function () {
    $objeto_cartao_credito = new CartaoCredito();

    echo json_encode(['status' => (bool) $objeto_cartao_credito->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

/**
 * Rota resposável por montar o filtro de pesquisa
 */
router_add('pesquisar_cartoes', function () {
    $objeto_cartao_credito = new CartaoCredito();

    $codigo_empresa = (int) (isset($_REQUEST['codigo_empresa']) ? (int) intval($_REQUEST['codigo_empresa'], 10) : 0);
    $codigo_conta = (int) (isset($_REQUEST['codigo_conta']) ? (int) intval($_REQUEST['codigo_conta'], 10) : 0);
    $codigo_cartao = (int) (isset($_REQUEST['codigo_cartao']) ? (int) intval($_REQUEST['codigo_cartao'], 10) : 0);
    $status_cartao = (bool) (isset($_REQUEST['status_cartao']) ? (bool) filter_var($_REQUEST['status_cartao'], FILTER_VALIDATE_BOOLEAN) : true);

    $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) [['nome_cartao', 'ASC']], 'limite' => (int) 0];
    $filtro_montando = (array) [];
    $retorno = (array) [];

    if ($codigo_empresa != 0) {
        array_push($filtro_montando, ['codigo_empresa', '==', (int) $codigo_empresa]);
    }

    if ($codigo_conta != 0) {
        array_push($filtro_montando, ['codigo_conta', '==', (int) $codigo_conta]);
    }

    if ($codigo_cartao != 0) {
        array_push($filtro_montando, ['codigo_cartao', '==', (int) $codigo_cartao]);
    }

    array_push($filtro_montando, ['status_cartao', '==', $status_cartao]);

    if ($codigo_empresa != 0) {
        $filtro['filtro'] = (array) ['where' => $filtro_montando];
        $retorno = (array) $objeto_cartao_credito->pesquisar_todos($filtro);
    }

    echo json_encode(['dados' => (array) $retorno], JSON_UNESCAPED_UNICODE);
    exit;
});

/**
 * Rota responsável por pesquisar o cartão
 */
router_add('pesquisar_cartao', function () {
    $objeto_cartao_credto = new CartaoCredito();

    $codigo_cartao = (int) (isset($_REQUEST['codigo_cartao']) ? (int) intval($_REQUEST['codigo_cartao'], 10) : 0);

    $filtro = (array) ['filtro' => (array) []];
    $filtro_montando = (array) [];
    $retorno = (array) [];

    if ($codigo_cartao != 0) {
        array_push($filtro_montando, ['codigo_cartao_credito', '==', (int) $codigo_cartao]);

        $filtro['filtro'] = (array) ['where' => $filtro_montando];
        $retorno = (array) $objeto_cartao_credto->pesquisar($filtro);
    }

    echo json_encode((array) ['dados' => (array) $retorno], JSON_UNESCAPED_UNICODE);
});

/**
 * Rota que é aberta sempre que abre o módulo de cartão de crédito
 */
router_add('index', function () {
    $menu = (string) 'CARTAO_CREDITO';
    $submenu = (string) 'LISTAR_CARTAO_CREDITO';

    require_once 'includes/head.php';
    ?>
    <script>
        const EMPRESA = <?php echo json_encode(EMPRESA, JSON_UNESCAPED_UNICODE); ?>;
        var codigo_cartao_credito = 0;

        /**
         * Função responsável por pesquisar as contas
         */
        function pesquisar_contas() {
            let dados = { 'rota': 'pesquisar_contas', 'empresa': EMPRESA };

            sistema.request.post('/contas.php', dados, function (retorno) {
                let contas = document.querySelector('#conta');
                let conta_cad = document.querySelector('#conta_cad');

                sistema.each(retorno.dados, (index, banco) => {
                    let option = sistema.gerar_option(banco.codigo_conta, banco.nome_conta);
                    contas.appendChild(option);
                });

                sistema.each(retorno.dados, (index, banco) => {
                    let option = sistema.gerar_option(banco.codigo_conta, banco.nome_conta);
                    conta_cad.appendChild(option);
                });
            });
        }

        /**
         * Função responsável por validar e cadastrar o cartão de crédito no sistema
         */
        function cadastrar_cartao() {
            let conta_cad = document.querySelector('#conta_cad');
            let nome_cartao_cad = document.querySelector('#nome_cartao_cad');
            let descricao_cad = document.querySelector('#descricao_cad');
            let limite_cartao_cad = document.querySelector('#limite_cartao_cad');
            let limite_utilizado_cad = document.querySelector('#limite_utilizado_cad');
            let anuidade_cad = document.querySelector('#anuidade_cad');
            let mes_vencimento_cad = document.querySelector('#mes_vencimento_cad');
            let ano_vencimento_cad = document.querySelector('#ano_vencimento_cad');
            let dia_fechamento_cad = document.querySelector('#dia_fechamento_cad');
            let status_cad = document.querySelector('#status_cartao_cad');

            let conta = '';
            let nome_cartao = '';
            let descricao = '';
            let limite_cartao = '';
            let limite_utilizado = '';
            let anuidade = '';
            let mes_vencimento = '';
            let ano_vencimento = '';
            let dia_fechamento = '';
            let status = '';

            let validacao = true;

            if (conta_cad.value == '0') {
                alerta_campo_vazio('Selecione uma conta!');
                validacao = false;
            } else {
                conta = conta_cad.value;
            }

            if (nome_cartao_cad.value == '') {
                validar_campo(nome_cartao_cad, document.querySelector('#nome_cartao_cad_validacao'), false, 'Informe o nome do cartão!');
                validacao = false;
            } else {
                validar_campo(nome_cartao_cad, document.querySelector('#nome_cartao_cad_validacao'), true);
                nome_cartao = nome_cartao_cad.value;
            }

            if (descricao_cad.value == '') {
                validar_campo(descricao_cad, document.querySelector('#descricao_cad_validacao'), false, 'Informe uma descrição!');
                validacao = false;
            } else {
                validar_campo(descricao_cad, document.querySelector('#descricao_cad_validacao'), true);
                descricao = descricao_cad.value;
            }

            if (limite_cartao_cad.value == '') {
                validar_campo(limite_cartao_cad, document.querySelector('#limite_cartao_cad_validacao'), false, 'Informe o limite do cartão!');
                validacao = false;
            } else {
                validar_campo(limite_cartao_cad, document.querySelector('#limite_cartao_cad_validacao'), true);
                limite_cartao = limite_cartao_cad.value;
            }

            if (limite_utilizado_cad.value == '') {
                validar_campo(limite_utilizado_cad, document.querySelector('#limite_utilizado_cad_validacao'), false, 'Informe o Limite utilizado, mesmo que seja (0)!');
                validacao = false;
            } else {
                validar_campo(limite_utilizado_cad, document.querySelector('#limite_utilizado_cad_validacao'), true);
                limite_utilizado = limite_utilizado_cad.value;
            }

            if (anuidade_cad.value == '') {
                validar_campo(anuidade_cad, document.querySelector('#anuidade_cad_validacao'), false, 'Informe uma anuidade para o cartão, mesmo que seja (0)!');
                validacao = false;
            } else {
                validar_campo(anuidade_cad, document.querySelector('#anuidade_cad_validacao'), true);
                anuidade = anuidade_cad.value;
            }

            mes_vencimento = mes_vencimento_cad.value;
            ano_vencimento = ano_vencimento_cad.value;
            dia_fechamento = dia_fechamento_cad.value;
            status = status_cad.value;

            let dados = { 'rota': 'salvar_dados', 'codigo_cartao_credito': codigo_cartao_credito, 'codigo_conta': conta, 'codigo_empresa': EMPRESA, 'nome_cartao': nome_cartao, 'descricao': descricao, 'limite_cartao': limite_cartao, 'limite_utilizado': limite_utilizado, 'anuidade': anuidade, 'status_cartao': status, 'mes_vencimento': mes_vencimento, 'ano_vencimento': ano_vencimento, 'dia_fechamento': dia_fechamento };

            if (validacao == true) {
                sistema.request.post('/cartao_credito.php', dados, (retorno) => {
                    validar_retorno(retorno, '/cartao_credito.php');
                });
            }
        }

        /**
         * Função responsável por pesquisar os cartões cadastrados no banco de dados
         */
        function pesquisar_cartoes() {
            barra_progresso('Carregando cartões...');

            let codigo_conta = document.querySelector('#conta').value;
            let codigo_cartao = document.querySelector('#cartao').value;
            let status = document.querySelector('#status_cartao').value;

            let dados = { 'rota': 'pesquisar_cartoes', 'codigo_empresa': EMPRESA, 'codigo_conta': codigo_conta, 'codigo_cartao': codigo_cartao, 'status_cartao': status };

            sistema.request.post('/cartao_credito.php', dados, (retorno) => {
                let cartoes = retorno.dados;
                let tamanho_retorno = cartoes.length;
                let tabela = document.querySelector('#tabela_cartoes tbody');
                let index = 0;

                tabela = sistema.remover_linha_tabela(tabela);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], 'NENHUM CARTÃO ENCONTRADO COM OS FILTROS PASSADOS!', 'inner', true, 10));
                    tabela.appendChild(linha);
                    Swal.fire({ icon: 'warning', title: 'Nenhum cartão encontrado!' });
                    return;
                }

                function processar_item() {
                    if (index >= tamanho_retorno) {
                        Swal.close();
                        return;
                    }

                    let cartao = cartoes[index];
                    let linha = document.createElement('tr');

                    linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], cartao.nome_cartao, 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.number_format(cartao.limite_cartao ?? '0', 2, ','), 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.number_format(cartao.limite_utilizado ?? '0', 2, ','), 'inner'));

                    let limite_cartao = cartao.limite_cartao ?? 0;
                    let limite_utilizado = cartao.limite_utilizado ?? 0;

                    let disponivel = limite_cartao - limite_utilizado;
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.number_format(disponivel, 2, ','), 'inner'));

                    let div_acao = document.createElement('div');
                    let botao_div_acao = document.createElement('button');
                    let ul_acao = document.createElement('ul');

                    div_acao.classList.add('dropdown');

                    botao_div_acao.classList.add('btn');
                    botao_div_acao.setAttribute('data-bs-toggle', 'dropdown');
                    botao_div_acao.setAttribute('aria-expanded', false);
                    botao_div_acao.style.width = '40px';
                    botao_div_acao.style.height = '40px';
                    botao_div_acao.style.fontSize = '24px';
                    botao_div_acao.innerHTML = '⋮';

                    ul_acao.classList.add('dropdown-menu');
                    ul_acao.setAttribute('aria-labelledby', 'Menu Conta');

                    div_acao.appendChild(botao_div_acao);

                    let li_editar_cartao = document.createElement('li');
                    li_editar_cartao.appendChild(sistema.gerar_a('Editar', ['dropdown-item'], () => { abrir_modal_editar_cartao(cartao); }, {}, ['fa-solid', 'fa-pen-to-square']));
                    ul_acao.appendChild(li_editar_cartao);

                    let li_adicionar_movimentacao = document.createElement('li');
                    li_adicionar_movimentacao.appendChild(sistema.gerar_a('Adicionar Movimentação', ['dropdown-item'], () => { cadastrar_movimentacao(cartao) }, {}, ['fa-solid', 'fa-book']));
                    ul_acao.appendChild(li_adicionar_movimentacao);

                    div_acao.appendChild(ul_acao);
                    linha.appendChild(sistema.gerar_td(['text-center'], div_acao, 'append'));

                    tabela.appendChild(linha);

                    atualizar_barra_progresso(index, tamanho_retorno, document.querySelector('#barra_progresso'), document.querySelector('#texto_progresso'));
                    index++;
                    setTimeout(processar_item, 1);
                }

                processar_item();
            });
        }

        /**
         * Função responsável por abrir o modal de editar o cartão de crédito
         */
        function abrir_modal_editar_cartao(cartao) {
            codigo_cartao_credito = cartao.codigo_cartao_credito;
            let conta_cad = $('#conta_cad');
            conta_cad.val(String(cartao.codigo_conta)).trigger('change');

            document.querySelector('#nome_cartao_cad').value = cartao.nome_cartao;
            document.querySelector('#descricao_cad').value = cartao.descricao;
            document.querySelector('#limite_cartao_cad').value = sistema.number_format(cartao.limite_cartao, 2, ',');
            document.querySelector('#limite_utilizado_cad').value = sistema.number_format(cartao.limite_utilizado ?? 0, 2, ',');
            document.querySelector('#anuidade_cad').value = sistema.number_format(cartao.anuidade ?? 0, 2, ',');

            let mes_vencimento_cad = $('#mes_vencimento_cad');
            mes_vencimento_cad.val(String(cartao.mes_vencimento)).trigger('change');

            let ano_vencimento_cad = $('#ano_vencimento_cad');
            ano_vencimento_cad.val(String(cartao.ano_vencimento)).trigger('change');

            let dia_fechamento_cad = $('#dia_fechamento_cad');
            dia_fechamento_cad.val(String(cartao.dia_fechamento)).trigger('change');

            let status_cartao_cad = $('#status_cartao_cad');

            if (cartao.status_cartao == true) {
                status_cartao_cad.val(String('1')).trigger('change');
            } else {
                status_cartao_cad.val(String('0')).trigger('change');
            }

            let modal = new bootstrap.Modal(document.querySelector('#cadastro_edicao_cartao'));
            modal.show();
        }

        /**
         * Função responsável por abrir o formulário de cadastro de movimentação.
         */
        function cadastrar_movimentacao(cartao) {
            window.location.href = sistema.url('/cartao_credito.php', { 'rota': 'cadastro_movimentacao', 'codigo_cartao_credito': cartao.codigo_cartao_credito });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Cartão de Crédito</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center"
                            data-bs-toggle="modal" data-bs-target="#cadastro_edicao_cartao"><i
                                class="fa fa-credit-card"></i> CADASTRAR CARTÃO
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Pesquisa de Cartões
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3 text-center">
                                    <label class="text">Conta</label>
                                    <select id="conta" class="form-control select2">
                                        <option value="0">Todas as Contas</option>
                                    </select>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Catão</label>
                                    <select class="form-control select2" id="cartao">
                                        <option value="0">Todos os Cartões</option>
                                    </select>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Status</label>
                                    <select class="form-control" id="status_cartao">
                                        <option value="1">ATIVO</option>
                                        <option value="0">INATIVO</option>
                                    </select>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-3 push-6">
                                    <button class="btn btn-light w-100 text-uppercase" onclick="gerar_excell();"><i
                                            class="fa-solid fa-file-excel"></i> Gerar
                                        Excell</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn btn-secondary w-100 text-uppercase" onclick="pesquisar_cartao();"><i
                                            class="fa-solid fa-search"></i> Pesquisar</button>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_cartoes">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-center">CARTÃO</th>
                                                    <th scope="col" class="text-center">LIMITE</th>
                                                    <th scope="col" class="text-center">UTILIZADO</th>
                                                    <th scope="col" class="text-center">DISPONÍVEL</th>
                                                    <th scope="col" class="text-center">AÇÃO</th>
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
        <div class="modal fade" id="cadastro_edicao_cartao" data-bs-backdrop="static" data-bs-keyboard="false"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Dados do Cartão</h1>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <label class="text">Conta</label>
                                <select class="form-control select2" id="conta_cad">
                                    <option value="0">Selecione uma Conta</option>
                                </select>
                                <div class="invalid-feedback" id="conta_cad_validacao">Selecione uma conta!</div>
                            </div>
                            <div class="col-9 text-center">
                                <label class="text">Nome do Cartão</label>
                                <input type="text" id="nome_cartao_cad" class="form-control text-uppercase">
                                <div class="invalid-feedback" id="nome_cartao_cad_validacao">Informe um nome!</div>
                            </div>
                        </div>
                        <br />
                        <div class="row">
                            <div class="col-12 text-center">
                                <label class="text">Descrição</label>
                                <textarea class="form-control text-uppercase" id="descricao_cad"></textarea>
                                <div class="invalid-feedback" id="descricao_cad_validacao">Informe uma descrição!</div>
                            </div>
                        </div>
                        <br />
                        <div class="row">
                            <div class="col-4 text-center">
                                <label class="text">Limite Total</label>
                                <input type="text" class="form-control" id="limite_cartao_cad" sistema-mask="moeda">
                                <div class="invalid-feedback" id="limite_cartao_cad_validacao">Informe o limite do cartão!
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <label class="text">Utilizado</label>
                                <input type="text" class="form-control" id="limite_utilizado_cad" sistema-mask="moeda">
                                <div class="invalid-feedback" id="limite_utilizado_cad_validacao">Informe o limite utilizado
                                    do cartão!
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <label class="text">Anuidade</label>
                                <input type="text" class="form-control" id="anuidade_cad" sistemas-mask="moeda">
                                <div class="invalid-feedback" id="anuidade_cad_validacao">Informe o valor da anuidade do
                                    cartão!
                                </div>
                            </div>
                        </div>
                        <br />
                        <div class="row">
                            <div class="col-3 text-center">
                                <label class="text">Mês vencimento</label>
                                <select class="form-control select2" id="mes_vencimento_cad">
                                    <?php
                                    for ($contador = 1; $contador <= 12; $contador++) {
                                        echo "<option value='" . $contador . "'>" . $contador . "</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback" id="mes_vencimento_cad_validacao"></div>
                            </div>
                            <div class="col-3 text-center">
                                <label class="text">Ano Vencimento</label>
                                <select class="form-control select2" id="ano_vencimento_cad">
                                    <?php
                                    for ($contador = 2026; $contador <= 2050; $contador++) {
                                        echo "<option value='" . $contador . "'>" . $contador . "</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback" id="ano_vencimento_cad_validacao"></div>
                            </div>
                            <div class="col-3 text-center">
                                <label class="text">Dia Fechamento</label>
                                <select class="form-control select2" id="dia_fechamento_cad">
                                    <?php
                                    for ($contador = 1; $contador <= 31; $contador++) {
                                        echo "<option value='" . $contador . "'>" . $contador . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-3 text-center">
                                <label class="text">Status Cartão</label>
                                <select class="form-control select2" id="status_cartao_cad">
                                    <option value="1">ATIVO</option>
                                    <option value="0">INATIVO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i
                                class="fa-solid fa-xmark"></i> FECHAR</button>
                        <button type="button" class="btn btn-success" onclick="cadastrar_cartao();"><i
                                class="fa-solid fa-floppy-disk"></i>
                            SALVAR</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = () => {
                pesquisar_contas();
                pesquisar_cartoes();
            }
        </script>
        <?php
        require_once 'includes/footer.php';
});

/**
 * Rota responsável por cadastrar as movimentações do cartão de crédito.
 */
router_add('cadastro_movimentacao', function () {
    $menu = (string) 'CARTAO_CREDITO';
    $submenu = (string) 'LISTAR_CARTAO_CREDITO';
    $codigo_cartao_credito = (int) (isset($_GET['codigo_cartao_credito']) ? (intval($_GET['codigo_cartao_credito'], 10)) : 0);

    require_once 'includes/head.php';
    ?>
        <script>
            const EMPRESA = <?php echo json_encode(EMPRESA, JSON_UNESCAPED_UNICODE); ?>;
            const CARTAO_CREDITO = <?php echo json_encode($codigo_cartao_credito, JSON_UNESCAPED_UNICODE); ?>;

            /**
             * Função responsável por salvar a movimentação do cartão de crédito.
             */
            function salvar_movimentacao() {
                let loja_movimentacao_objeto = document.querySelector('#loja_movimentacao');
                let valor_lancamento_objeto = document.querySelector('#valor_lancamento');
                let historico_movimentacao_objeto = document.querySelector('#historico_movimentacao');

                let loja_movimentacao = '';
                let valor_lancamento = '0.00';
                let historico_movimentacao = '';
                let tipo_lancamento = document.querySelector('#tipo_lancamento').value;
                let data_movimentacao = document.querySelector('#data_movimentacao').value;

                let validacao = true;

                if (loja_movimentacao_objeto.value == '') {
                    validar_campo(loja_movimentacao_objeto, document.querySelector('#loja_movimentacao_validacao'), false, 'Informe a loja da movimentação!');
                    validacao = false;
                } else {
                    validar_campo(loja_movimentacao_objeto, document.querySelector('#loja_movimentacao_validacao'), true);
                    loja_movimentacao = loja_movimentacao_objeto.value;
                }

                if (valor_lancamento_objeto.value == '') {
                    validar_campo(valor_lancamento_objeto, document.querySelector('#valor_lancamento_validacao'), false, 'Informe o valor do lançamento!');
                    validacao = false;
                } else {
                    validar_campo(valor_lancamento_objeto, document.querySelector('#valor_lancamento_validacao'), true);
                    valor_lancamento = valor_lancamento_objeto.value;
                }

                if (historico_movimentacao_objeto.value == '') {
                    validar_campo(historico_movimentacao_objeto, document.querySelector('#historico_movimentacao_validacao'), false, 'Informe a descrição da movimentação!');
                    validacao = false;
                } else {
                    validar_campo(historico_movimentacao_objeto, document.querySelector('#historico_movimentacao_validacao'), true);
                    historico_movimentacao = historico_movimentacao_objeto.value;
                }

                let dados = { 'rota': 'salvar_dados', 'codigo_cartao_credito': CARTAO_CREDITO, 'loja_movimentacao': loja_movimentacao, 'historico_movimentacao': historico_movimentacao, 'valor_lancamento': valor_lancamento, 'tipo_lancamento': tipo_lancamento, 'data_movimentacao': data_movimentacao };

                if (validacao == true) {
                    sistema.request.post('/movimentacao_cartao.php', dados, (retorno) => {

                        if (retorno.status == true) {
                            Swal.fire({ icon: 'success', title: 'Movimentação cadastrada com sucesso!' }).then(() => {
                                const elemento = document.querySelector('#cadastro_compra');
                                const meuModal = bootstrap.Modal.getInstance(elemento);

                                if (meuModal) {
                                    meuModal.hide();
                                }

                                pesquisar_cartao();
                                pesquisar_movimentacao();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Erro ao cadastrar a movimentação!' });
                        }
                    });
                }
            }

            /**
             * Função responsável por pesquisar as movimentações do cartão de crédito.
             */
            function pesquisar_movimentacao() {
                barra_progresso('Carregando Movimentações...');

                let dados = { 'rota': 'pesquisar_movimentacoes', 'codigo_cartao_credito': CARTAO_CREDITO };

                sistema.request.post('/movimentacao_cartao.php', dados, (retorno) => {
                    let movimentacoes = retorno.dados;
                    let tamanho_retorno = movimentacoes.length;
                    let tabela = document.querySelector('#tabela_compras_cartao tbody');
                    let index = 0;

                    tabela = sistema.remover_linha_tabela(tabela);

                    if (tamanho_retorno == 0) {
                        let linha = document.createElement('tr');
                        linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], 'NENHUMA MOVIMENTAÇÃO ENCONTRADA!', 'inner', true, 5));
                        tabela.appendChild(linha);
                        Swal.fire({ icon: 'warning', title: 'Nenhuma movimentação encontrada!' });
                        return;
                    }

                    function processar_item() {
                        if (index >= tamanho_retorno) {
                            Swal.close();
                            return;
                        }

                        let movimentacao = movimentacoes[index];
                        let linha = document.createElement('tr');

                        linha.appendChild(sistema.gerar_td(['text-center'], movimentacao.loja_movimentacao, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-start'], movimentacao.historico_movimentacao, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(movimentacao.data_movimentacao, true), 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(movimentacao.valor_lancamento ?? '0', 2, ','), 'inner'));

                        if (movimentacao.tipo_lancamento == false) {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_lancamento_' + movimentacao.codigo_movimentacao_cartao, 'COMPRA', ['btn', 'btn-danger'], () => { }, true, ['fa-solid', 'fa-circle-arrow-down']), 'append'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_lancamento_' + movimentacao.codigo_movimentacao_cartao, 'PAGAMENTO DE FATURA', ['btn', 'btn-success'], () => { }, true, ['fa-solid', 'fa-circle-arrow-up']), 'append'));
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
             * Função responsável por pesquisar o cartão de crédito e preencher os campos com as informações.
             */
            function pesquisar_cartao() {
                let dados = { 'rota': 'pesquisar_cartao', 'codigo_cartao': CARTAO_CREDITO };

                sistema.request.post('/cartao_credito.php', dados, (retorno) => {
                    let cartao = retorno.dados;

                    document.querySelector('#nome_cartao').value = cartao.nome_cartao;
                    document.querySelector('#limite_cartao').value = sistema.number_format(cartao.limite_cartao ?? '0', 2, ',');
                    document.querySelector('#limite_utilizado').value = sistema.number_format(cartao.limite_utilizado ?? '0', 2, ',');
                    let disponivel = (cartao.limite_cartao ?? 0) - (cartao.limite_utilizado ?? 0);
                    document.querySelector('#limite_disponivel').value = sistema.number_format(disponivel, 2, ',');
                });
            }

            /**
             * Função responsável por voltar a tela de listagem de cartões de crédito.
             */
            function voltar(){
                window.location.href = sistema.url('/cartao_credito.php', {});
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="d-flex d-block align-items-center justify-content-between flex-wrap gap-3 mb-3">
                    <div>
                        <h6>Movimentação do Cartão</h6>
                    </div>

                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <button type="button" class="btn btn-secondary d-flex align-items-center justify-content-center"
                            onclick="voltar();">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            VOLTAR
                        </button>
                        <button type="button" class="btn btn-primary d-flex align-items-center justify-content-center"
                            data-bs-toggle="modal" data-bs-target="#cadastro_compra">
                            <i class="fa-solid fa-credit-card me-1"></i>
                            ADICIONAR COMPRA
                        </button>

                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <div class="card-title">Informações do Cartão</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-3 text-center">
                                        <label class="text">Cartão</label>
                                        <input type="text" class="form-control text-uppercase" id="nome_cartao" disabled>
                                    </div>
                                    <div class="col-3 text-center">
                                        <label class="text">Limite</label>
                                        <input type="text" class="form-control" id="limite_cartao" disabled>
                                    </div>
                                    <div class="col-3 text-center">
                                        <label class="text">Utilizado</label>
                                        <input type="text" class="form-control" id="limite_utilizado" disabled>
                                    </div>
                                    <div class="col-3 text-center">
                                        <label class="text">Disponível</label>
                                        <input type="text" class="form-control" id="limite_disponivel" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br />
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <div class="card-title">Compras cartão</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-nowrap text-nowrap table-hover"
                                                id="tabela_compras_cartao">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="text-center">LOJA</th>
                                                        <th scope="col" class="text-center">DESCRIÇÃO</th>
                                                        <th scope="col" class="text-center">DATA</th>
                                                        <th scope="col" class="text-center">VALOR</th>
                                                        <th scope="col" class="text-center">TIPO</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="20" class="text-center fw-bold">NENHUMA MOVIMENTAÇÃO
                                                            ENCONTRADA!</td>
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
            <div class="modal fade" id="cadastro_compra" data-bs-backdrop="static" data-bs-keyboard="false"
                aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">Dados do Cartão</h1>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-3 text-center">
                                    <label class="text">Loja</label>
                                    <input type="text" class="form-control text-uppercase" id="loja_movimentacao">
                                    <div class="invalid-feedback" id="loja_movimentacao_validacao">Informe o nome da loja!
                                    </div>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Valor</label>
                                    <input type="text" class="form-control" id="valor_lancamento" sistema-mask="moeda">
                                    <div class="invalid-feedback" id="valor_lancamento_validacao">Informe o valor da movimentação!</div>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Tipo</label>
                                    <select class="form-control" id="tipo_lancamento">
                                        <option value="0">COMPRA</option>
                                        <option value="1">PAGAMENTO DE FATURA</option>
                                    </select>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Data</label>
                                    <input type="date" class="form-control" id="data_movimentacao"
                                        value="<?php echo DATA_HOJE; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-center">
                                    <label class="text">Descrição</label>
                                    <textarea class="form-control text-uppercase" id="historico_movimentacao"></textarea>
                                    <div class="invalid-feedback" id="historico_movimentacao_validacao">Informe uma descrição da movimentação!
                                    </div>
                                </div>
                            </div>
                            <br />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i
                                    class="fa-solid fa-xmark"></i>
                                FECHAR</button>
                            <button type="button" class="btn btn-success" onclick="salvar_movimentacao();"><i
                                    class="fa-solid fa-floppy-disk"></i>
                                SALVAR</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = () => {
                    pesquisar_cartao();
                    pesquisar_movimentacao();
                }
            </script>
            <?php
            require_once 'includes/footer.php';
});
?>