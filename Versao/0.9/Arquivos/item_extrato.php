<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/ItensExtratos.php';

router_add('salvar_dados', function () {
    $objeto_item_extrato = new ItensExtratos();

    echo json_encode((array) ['status' => (bool) $objeto_item_extrato->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('pesquisar_todos', function () {
    $objeto_item_extrato = new ItensExtratos();

    $empresa = (int) (isset($_REQUEST['empresa']) ? (int) intval($_REQUEST['empresa'], 10) : 0);

    $filtro_montando = (array) [];

    if ($empresa != '') {
        array_push($filtro_montando, ['codigo_empresa', '=', $empresa]);
    }

    $filtro = (array) ['filtro' => (array) ['where' => $filtro_montando], 'ordenacao' => (array) [['nome_item_extrato', 'ASC']], 'limite' => (int) 100];

    echo json_encode((array) ['dados' => (array) $objeto_item_extrato->pesquisar_todos($filtro)], JSON_UNESCAPED_UNICODE);
});

router_add('pesquisar_item_extrato', function () {
    $objeto_item_extrato = new ItensExtratos();

    $codigo_item_extrato = (int) (isset($_REQUEST['codigo_item_extrato']) ? (int) intval($_REQUEST['codigo_item_extrato'], 10) : 0);
    $empresa = (int) (isset($_REQUEST['empresa']) ? (int) intval($_REQUEST['empresa'], 10) : 0);

    $filtro_montando = (array) [];
    $retorno = (array) [];

    if ($codigo_item_extrato != '') {
        array_push($filtro_montando, (array) ['codigo_item_extrato', '=', $codigo_item_extrato]);
    }

    if ($empresa != 0) {
        array_push($filtro_montando, ['codigo_empresa', '=', $empresa]);
    }

    echo json_encode((array) ['dados' => (array) $objeto_item_extrato->pesquisar((array) ['where' => (array) $filtro_montando])]);
    exit;
});

router_add('index', function () {
    include_once 'includes/head.php';
    ?>
    <script>
        const EMPRESA = "<?php echo $codigo_empresa; ?>";

        /**
         * Função responsável por salvar os itens do extratos
         */
        function salvar_dados() {
            let codigo_item_extrato = document.querySelector('#codigo_item_extrato_cad').value;
            let nome_item_extrato = document.querySelector("#nome_item_extrato_cad").value;
            let tipo_item_extrato = document.querySelector('#tipo_item_extrato_cad').value;

            let validacao = true;

            if (nome_item_extrato == '') {
                alerta_campo_vazio('NOME ITEM');
                validacao = false;
            }

            if (tipo_item_extrato == '') {
                alerta_campo_vazio('TIPO ITEM');
                validacao = false;
            }

            let dados = { 'rota': 'salvar_dados', 'codigo_item_extrato': codigo_item_extrato, 'nome_item_extrato': nome_item_extrato, 'tipo_item_extrato': tipo_item_extrato, 'empresa': EMPRESA };

            sistema.request.post('/item_extrato.php', dados, function (retorno) {
                validar_retorno(retorno, '/item_extrato.php');
            });
        }

        /**
         * Função responsável por limpar o formulário de cadastro
         */
        function limpar_dados() {
            colocar_dados_editar('', '', '');
        }

        /**
         * Função responsável por retornar ao index do módulo
         */
        function voltar() {
            window.location.href = sistema.url('/item_extrato.php', { 'rota': 'index' });
        }

        /**
         * Função responsável por pesquisar todos os itens do extratos
         */
        function pesquisar_itens_extratos() {
            barra_progresso('Carregando itens do extrato...');
            sistema.request.post('/item_extrato.php', { 'rota': 'pesquisar_todos', 'empresa': EMPRESA }, function (retorno) {
                let item_extratos = retorno.dados;
                let tamanho_retorno = item_extratos.length;
                let tabela = document.querySelector('#tabela_item_extrato tbody');
                let index = 0;

                tabela = sistema.remover_linha_tabela(tabela);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], 'NENHUM ITEM ENCONTRADO!', 'inner', true, 3));
                    tabela.appendChild(linha);

                    Swal.fire({ icon: 'warning', title: 'Nenhuma item encontrado!' });
                    return;
                }

                function processar_item() {
                    if (index >= tamanho_retorno) {
                        Swal.close();
                        return;
                    }

                    let item_extrato = item_extratos[index];
                    let linha = document.createElement('tr');

                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], item_extrato.codigo_item_extrato, 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], item_extrato.nome_item_extrato, 'inner'));

                    if (item_extrato.tipo_item_extrato == false) {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_item_extrato_' + item_extrato.codigo_item_extrato, 'DEBIDO', ['btn', 'btn-outline-danger'], () => { }), 'append'));
                    } else {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_item_extrato_' + item_extrato.codigo_item_extrato, 'CREDITO', ['btn', 'btn-outline-success'], () => { }), 'append'));
                    }

                    let botao = document.createElement('button');
                    botao.id = 'botao_editar_item_extrato_' + item_extrato.codigo_item_extrato;
                    botao.textContent = 'EDITAR';
                    botao.classList.add('btn');
                    botao.classList.add('btn-primary');
                    botao.dataset.bsToggle = "modal";
                    botao.dataset.bsTarget = "#modal_cadastro_item_holerite";

                    botao.addEventListener('click', function () {
                        colocar_dados_editar(item_extrato.codigo_item_extrato, item_extrato.nome_item_extrato, item_extrato.tipo_item_extrato);
                    });

                    linha.appendChild(sistema.gerar_td(['text-center'], botao, 'append'));

                    tabela.appendChild(linha);

                    atualizar_barra_progresso(index, tamanho_retorno, document.querySelector('#barra_progresso'), document.querySelector('#texto_progresso'));

                    index++;
                    setTimeout(processar_item, 1);
                }

                processar_item();
            });
        }

        /**
         * Função responsável por colocar os dados no formulário para a edição dos dados
         * @param {*} codigo
         * @param {*} nome
         * @param {*} tipo
         */
        function colocar_dados_editar(codigo, nome, tipo) {
            document.querySelector('#codigo_item_extrato_cad').value = codigo;
            document.querySelector('#nome_item_extrato_cad').value = nome;

            if (tipo == true) {
                document.querySelector('#tipo_item_extrato_cad').value = '1';
            } else {
                document.querySelector('#tipo_item_extrato_cad').value = '0';
            }
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
                            data-bs-toggle="modal" data-bs-target="#modal_cadastro_item_holerite">Cadastro Itens
                            Holerites</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Pesquisa de Itens Holerites
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 text-center">
                                        <label class="text">Nome Item Extrato</label>
                                        <input type="text" class="form-control" id="nome_item_extrato">
                                    </div>
                                    <div class="col-6 text-center">
                                        <label class="text">Tipo Item Extrato</label>
                                        <select class="form-control select2" id="tipo_item_extrato">
                                            <option value="">TODOS</option>
                                            <option value="1">DEBITO</option>
                                            <option value="0">CREDITO</option>
                                        </select>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-3 push-9">
                                        <button class="btn btn-secondary w-100"
                                            onclick="pesquisar_itens_extratos();">PESQUISAR</button>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-nowrap text-nowrap table-hover"
                                                id="tabela_item_extrato">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th scope="col">#</th>
                                                        <th scope="col">NOME</th>
                                                        <th scope="col">TIPO</th>
                                                        <th scope="col">AÇÃO</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="4" class="text-center fw-bold">UTILZE O FILTRO PARA
                                                            FACILITAR SUA PESQUISA!</td>
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
        </div>
        <div class="modal fade" id="modal_cadastro_item_holerite" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Cadastro Item Holerite</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="codigo_item_extrato_cad">
                        <div class="row">
                            <div class="col-6 text-center">
                                <label class="text">Nome Item</label>
                                <input type="text" class="form-control text-uppercase" id="nome_item_extrato_cad">
                            </div>
                            <div class="col-6 text-center">
                                <label class="text">Tipo Item Holerite</label>
                                <select id="tipo_item_extrato_cad" class="form-control">
                                    <option value="">Selecione Uma Opção</option>
                                    <option value="1">CRÉDITO</option>
                                    <option value="0">DEBITO</option>
                                </select>
                            </div>
                        </div>
                        <?php
                        include_once 'includes/botao_cadastro.php';
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function () {
                pesquisar_itens_extratos();
            }
        </script>
        <?php
        include_once 'includes/footer.php';
});
?>