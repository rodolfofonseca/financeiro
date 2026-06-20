<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/ContasPagarReceber.php';

router_add('index', function () {
    include_once 'includes/head.php';
    $data_hoje = $data->format('Y-m-d');
    ?>
    <script>
        const EMPRESA = "<?php echo $codigo_empresa; ?>";
        const DATA_HOJE = "<?php echo $data_hoje; ?>";

        function cadastro_contas(codigo_conta_pagar_receber) {
            window.location.href = sistema.url('/contas_pagar_receber.php', {
                'rota': 'cadastro_contas',
                'codigo_conta_pagar_receber': codigo_conta_pagar_receber
            })
        }

        function pesquisar_contas() {
            let nome_conta = document.querySelector('#nome_conta').value;
            let descricao = document.querySelector('#descricao').value;
            let tipo_conta = document.querySelector('#tipo_conta').value;
            let status_conta = document.querySelector('#status_conta').value;
            let data_cadastro_inicio = document.querySelector('#data_cadastro_inicio').value;
            let data_cadastro_fim = document.querySelector('#data_cadastro_fim').value;
            let data_baixa_inicio = document.querySelector('#data_baixa_inicio').value;
            let data_baixa_fim = document.querySelector('#data_baixa_fim').value;
            let data_vencimento_inicio = document.querySelector('#data_vencimento_inicio').value;
            let data_vencimento_fim = document.querySelector('#data_vencimento_fim').value;

            sistema.request.post('/contas_pagar_receber.php', {
                'rota': 'pesquisar_contas',
                'nome_conta': nome_conta,
                'descricao': descricao,
                'tipo_conta': tipo_conta,
                'status_conta': status_conta,
                'data_cadastro_inicio': data_cadastro_inicio,
                'data_cadastro_fim': data_cadastro_fim,
                'data_baixa_inicio': data_baixa_inicio,
                'data_baixa_fim': data_baixa_fim,
                'data_vencimento_inicio': data_vencimento_inicio,
                'data_vencimento_fim': data_vencimento_fim,
                'empresa': EMPRESA
            }, function (retorno) {
                let contas = retorno.dados;
                let tabela_contas = document.querySelector('#tabela_contas tbody');
                let tamanho_retorno = contas.length;

                tabela = sistema.remover_linha_tabela(tabela_contas);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center'], 'NENHUMA CONTA ENCONTRADA, COM OS FILTROS PASSADOS!', 'inner', true, 10));
                    tabela_contas.appendChild(linha);
                } else {
                    sistema.each(contas, function (index, conta) {
                        let linha = document.createElement('tr');

                        linha.appendChild(sistema.gerar_td(['text-center'], conta.nome_conta, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], conta.descricao, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(conta.valor_conta), 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(conta.data_vencimento), 'inner'));

                        if (conta.status_conta == 'AGUARDANDO' || conta.status_conta == 'VENCIDO') {
                            linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));

                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(conta.data_baixa), 'inner'));
                        }

                        if (conta.tipo_conta == 'PAGAR') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta._id.$oid, 'PAGAR', ['btn', 'btn-outline-danger'], function tipo_conta() { }), 'append'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta._id.$oid, 'RECEBER', ['btn', 'btn-outline-success'], function tipo_conta() { }), 'append'));
                        }

                        if (conta.status_conta == 'AGUARDANDO') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_conta_' + conta._id.$oid, 'AGUARDANDO', ['btn', 'btn-outline-secondary'], function status_conta() { }), 'append'));
                        } else if (conta.status_conta == 'PAGO') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_conta_' + conta._id.$oid, 'PAGO', ['btn', 'btn-outline-success'], function status_conta() { }), 'append'));
                        } else if (conta.status_conta == 'CANCELADO') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_conta_' + conta._id.$oid, 'CANCELADO', ['btn', 'btn-outline-warning'], function status_conta() { }), 'append'));
                        } else if (conta.status_conta == 'VENCIDO') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_conta_' + conta._id.$oid, 'VENCIDO', ['btn', 'btn-outline-danger'], function status_conta() { }), 'append'));
                        }

                        let botao = document.createElement('button');
                        botao.id = 'botao_baixar_conta_' + conta._id.$oid;
                        botao.textContent = 'BAIXAR';
                        botao.classList.add('btn');
                        botao.classList.add('btn-primary');
                        botao.dataset.bsToggle = "modal";
                        botao.dataset.bsTarget = "#modal_baixar_conta";

                        botao.addEventListener('click', function () {
                            document.querySelector('#valor_conta').value = sistema.number_format(conta.valor_conta);
                            document.querySelector('#data_vencimento').value = sistema.retornar_data(conta.data_vencimento, 'AMERICANO');
                            document.querySelector('#codigo_conta_pagar_receber').value = conta._id.$oid;
                            document.querySelector('#tipo_conta_input').value = conta.tipo_conta;
                            document.querySelector('#nome_conta_input').value = conta.nome_conta;
                        });

                        linha.appendChild(sistema.gerar_td(['text-center'], botao, 'append'));

                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_editar_conta_' + conta._id.$oid, 'EDITAR', ['btn', 'btn-primary'], function baixar_conta_botao() {
                            cadastro_contas(conta._id.$oid);
                        }), 'append'));

                        tabela_contas.appendChild(linha);
                    });
                }
            });
        }

        function validar_juro_desconto() {
            let valor_conta = document.querySelector('#valor_conta').value;
            let valor_pago = document.querySelector('#valor_pago').value;
            let resultado = 0;

            valor_conta = valor_conta.replace(',', '.');
            valor_pago = valor_pago.replace(',', '.');

            if (valor_conta > valor_pago) {
                document.querySelector('#tipo_juro_desconto').value = 'DESCONTO';
                resultado = valor_conta - valor_pago;
            } else if (valor_conta < valor_pago) {
                document.querySelector('#tipo_juro_desconto').value = 'JURO';
                resultado = valor_pago - valor_conta;
            } else {
                document.querySelector('#tipo_juro_desconto').value = '';
                resultado = 0;
            }

            resultado = resultado.toFixed(2);

            document.querySelector('#valor_juro_desconto').value = resultado.replace('.', ',');
        }

        function pesquisar_conta_bancaria() {
            sistema.request.post('/contas.php', {
                'rota': 'pesquisar_contas',
                'empresa': EMPRESA,
                'status': 'ATIVO'
            }, function (retorno) {
                let contas = retorno.dados;
                let tamanho_retorno = contas.length;
                if (tamanho_retorno > 0) {
                    let select_conta = document.querySelector('#conta');

                    sistema.each(contas, function (index, conta) {
                        select_conta.appendChild(sistema.gerar_option(conta._id.$oid, conta.nome_conta + " | " + sistema.number_format(conta.saldo_conta)));
                    });
                }
            });
        }

        function baixar_conta() {
            let codigo_conta_pagar_receber = document.querySelector('#codigo_conta_pagar_receber').value;
            let valor_pago = document.querySelector('#valor_pago').value;
            let tipo_juro_desconto = document.querySelector('#tipo_juro_desconto').value;
            let valor_juro_desconto = document.querySelector('#valor_juro_desconto').value;
            let data_baixa = document.querySelector('#data_baixa').value;
            let codigo_conta_bancaria = document.querySelector('#conta').value;
            let tipo_conta = document.querySelector('#tipo_conta_input').value;
            let nome_conta = document.querySelector('#nome_conta_input').value;

            sistema.request.post('/contas_pagar_receber.php', { 'rota': 'baixar_conta', 'codigo_conta_pagar_receber': codigo_conta_pagar_receber, 'valor_pago': valor_pago, 'tipo_juro_desconto': tipo_juro_desconto, 'valor_juro_desconto': valor_juro_desconto, 'data_baixa': data_baixa, 'codigo_conta_bancaria': codigo_conta_bancaria, 'empresa': EMPRESA, 'tipo_conta': tipo_conta, 'nome_conta': nome_conta }, function (retorno) {
                validar_retorno(retorno, '/contas_pagar_receber.php');
            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Contas A Pagar E Receber</h6>
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
                            <div class="card-title">Pesquisa de Contas A Pagar Receber</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3">
                                    <label class="text">Nome Conta</label>
                                    <input type="text" class="form-control" id="nome_conta" placeholder="Nome da Conta">
                                </div>
                                <div class="col-3">
                                    <label class="text">Descrição</label>
                                    <input type="text" class="form-control" id="descricao" placeholder="Descrição da Conta">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Tipo Conta</label>
                                    <select class="form-control" id="tipo_conta">
                                        <option value="TODOS">TODOS</option>
                                        <option value="PAGAR">PAGAR</option>
                                        <option value="RECEBER">RECEBER</option>
                                    </select>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Status Conta</label>
                                    <select class="form-control" id="status_conta">
                                        <option value="TODOS">TODOS</option>
                                        <option value="AGUARDANDO">AGUARDANDO</option>
                                        <option value="PAGO">PAGO</option>
                                        <option value="CANCELADO">CANCELADO</option>
                                        <option value="VENCIDO">VENCIDO</option>
                                    </select>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-2 text-center">
                                    <label class="text">Data Cadastro Início</label>
                                    <input type="date" class="form-control" id="data_cadastro_inicio">
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Data Cadastro Fim</label>
                                    <input type="date" class="form-control" id="data_cadastro_fim">
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Dt. Venci. Início</label>
                                    <input type="date" class="form-control" id="data_vencimento_inicio">
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Data Vencimento Fim</label>
                                    <input type="date" class="form-control" id="data_vencimento_fim">
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Dt. baixa Início</label>
                                    <input type="date" class="form-control" id="data_baixa_inicio">
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Data Baixa Fim</label>
                                    <input type="date" class="form-control" id="data_baixa_fim">
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
                                                <tr class="text-center">
                                                    <th scope="col">Nome Conta</th>
                                                    <th scope="col">Descrição</th>
                                                    <th scope="col">Valor</th>
                                                    <th scope="col">Vencimento</th>
                                                    <th scope="col">baixa</th>
                                                    <th scope="col">Tipo</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col">Baixa</th>
                                                    <th scope="col">Editar</th>
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
        <div class="modal fade" id="modal_baixar_conta" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">baixa de Contas</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="codigo_conta_pagar_receber">
                        <input type="hidden" id="tipo_conta_input">
                        <input type="hidden" id="nome_conta_input">
                        <div class="row">
                            <div class="col-6 text-center">
                                <label class="text">Valor Conta</label>
                                <input type="text" class="form-control" id="valor_conta" placeholder="Valor Conta"
                                    sistema-mask="moeda" disabled="true">
                            </div>
                            <div class="col-6 text-center">
                                <label class="text">Data Vencimento</label>
                                <input type="date" class="form-control" id="data_vencimento" disabled="true">
                            </div>
                        </div>
                        <br />
                        <div class="row">
                            <div class="col-3 text-center">
                                <label class="text">Valor Pago</label>
                                <input type="text" class="form-control" id="valor_pago" sistema-mask="moeda"
                                    placeholder="Valor Pago" onblur="validar_juro_desconto();">
                            </div>
                            <div class="col-3 text-center">
                                <label class="text">Juro/Desconto</label>
                                <input type="text" class="form-control" id="valor_juro_desconto" sistema-mask="moeda"
                                    placeholder="Juro/Desconto" sistema-mask="moeda">
                            </div>
                            <div class="col-3 text-center">
                                <label class="text">Tipo Juro/Desconto</label>
                                <select class="form-control" id="tipo_juro_desconto">
                                    <option value="">Selecione uma opção</option>
                                    <option value="JURO">JURO</option>
                                    <option value="DESCONTO">DESCONTO</option>
                                </select>
                            </div>
                            <div class="col-3 text-center">
                                <label class="text">Data Baixa</label>
                                <input type="date" class="form-control" id="data_baixa">
                            </div>
                        </div>
                        <br />
                        <div class="row">
                            <div class="col-6 text-center">
                                <label class="text">Conta Movimentação</label>
                                <select class="form-control" id="conta">
                                    <option value="">Selecione uma opção</option>
                                </select>
                            </div>
                        </div>
                        <br />
                        <div class="row">
                            <div class="col-4 push-4">
                                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal"
                                    aria-label="Close">Fechar</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-success w-100" onclick="baixar_conta();">Baixar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function () {
                document.querySelector('#data_baixa').value = DATA_HOJE;
                pesquisar_contas();
                pesquisar_conta_bancaria();
            }
        </script>
        <?php
        include_once 'includes/footer.php';
        exit;
});

router_add('cadastro_contas', function () {
    include_once 'includes/head.php';

    $data_hoje = $data->format('Y-m-d');
    $data->add(new DateInterval('P30D'));
    $data_vencimento = $data->format('Y-m-d');

    $codigo_conta_pagar_receber = (string) (isset($_REQUEST['codigo_conta_pagar_receber']) ? (string) $_REQUEST['codigo_conta_pagar_receber'] : '');
    ?>
        <script>
            const HOJE = "<?php echo $data_hoje; ?>";
            const DATA_VENCIMENTO = "<?php echo $data_vencimento; ?>";
            const EMPRESA = "<?php echo $codigo_empresa; ?>";

            const CODIGO_CONTA_PAGAR_RECEBER = "<?php echo $codigo_conta_pagar_receber; ?>";

            function salvar_dados() {
                let nome_conta = document.querySelector('#nome_conta').value;
                let descricao = document.querySelector('#descricao').value;
                let valor_conta = document.querySelector('#valor_conta').value;
                let valor_pago = document.querySelector('#valor_pago').value;
                let valor_juro_desconto = document.querySelector("#valor_juro_desconto").value;
                let tipo_juro_desconto = document.querySelector('#tipo_juro_desconto').value;
                let data_cadastro = document.querySelector('#data_cadastro').value;
                let data_vencimento = document.querySelector('#data_vencimento').value;
                let data_baixa = document.querySelector('#data_baixa').value;
                let tipo_conta = document.querySelector('#tipo_conta').value;
                let status_conta = document.querySelector('#status_conta').value;

                sistema.request.post('/contas_pagar_receber.php', {
                    'rota': 'salvar_dados',
                    'empresa': EMPRESA,
                    'codigo_conta_pagar_receber': CODIGO_CONTA_PAGAR_RECEBER,
                    'nome_conta': nome_conta,
                    'descricao': descricao,
                    'valor_conta': valor_conta,
                    'valor_pago': valor_pago,
                    'valor_juro_desconto': valor_juro_desconto,
                    'tipo_juro_desconto': tipo_juro_desconto,
                    'data_cadastro': data_cadastro,
                    'data_vencimento': data_vencimento,
                    'data_baixa': data_baixa,
                    'tipo_conta': tipo_conta,
                    'status_conta': status_conta
                }, function (retorno) {
                    validar_retorno(retorno, '/contas_pagar_receber.php');
                });
            }

            function limpar_dados() {
                document.querySelector('#nome_conta').value = '';
                document.querySelector('#descricao').value = '';
                document.querySelector('#valor_conta').value = '';
                document.querySelector('#valor_pago').value = '';
                document.querySelector('#valor_juro_desconto').value = '';
                document.querySelector('#tipo_juro_desconto').value = '';
                document.querySelector('#data_cadastro').value = HOJE;
                document.querySelector('#data_vencimento').value = DATA_VENCIMENTO;
                document.querySelector('#data_baixa').value = '';
                document.querySelector('#tipo_conta').value = '';
                document.querySelector('#status_conta').value = 'AGUARDANDO';
            }

            function voltar() {
                window.location.href = sistema.url('/contas_pagar_receber.php', {
                    'rota': 'index'
                });
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Cadastro de Contas A Pagar E Receber</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <label class="text">Nome Conta</label>
                                        <input type="text" class="form-control" id="nome_conta" placeholder="Nome da Conta">
                                    </div>
                                    <div class="col-8 text-center">
                                        <label class="text">Descrição</label>
                                        <input type="text" class="form-control" id="descricao"
                                            placeholder="Descrição da Conta">
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-3 text-center">
                                        <label class="text">Valor Conta</label>
                                        <input type="text" class="form-control" id="valor_conta" placeholder="Valor Conta"
                                            sistema-mask="moeda">
                                    </div>
                                    <div class="col-3 tex-center">
                                        <label class="text">Valor Pago</label>
                                        <input type="text" class="form-control" id="valor_pago" placeholder="Valor pago"
                                            sistema-mask="moeda">
                                    </div>
                                    <div class="col-3 text-center">
                                        <label class="text">Valor Juro/Desconto</label>
                                        <input type="text" class="form-control" id="valor_juro_desconto"
                                            placeholder="Valor Juro/Desconto" sistema-mask="moeda">
                                    </div>
                                    <div class="col-3 text-center">
                                        <label class="text">Tipo Juro/Desconto</label>
                                        <select class="form-control" id="tipo_juro_desconto">
                                            <option value="">Selecione uma opção</option>
                                            <option value="JURO">JURO</option>
                                            <option value="DESCONTO">DESCONTO</option>
                                        </select>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <label class="text">Data Cadastro</label>
                                        <input type="date" class="form-control" id="data_cadastro">
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="text">Data Vencimento</label>
                                        <input type="date" class="form-control" id="data_vencimento">
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="text">Data Baixa</label>
                                        <input type="date" class="form-control" id="data_baixa">
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-6 text-center">
                                        <label class="text">Tipo Conta</label>
                                        <select class="form-control" id="tipo_conta">
                                            <option value="PAGAR">PAGAR</option>
                                            <option value="RECEBER">RECEBER</option>
                                        </select>
                                    </div>
                                    <div class="col-6 text-center">
                                        <label class="text">Status Conta</label>
                                        <select class="form-control" id="status_conta">
                                            <option value="AGUARDANDO">AGUARDANDO</option>
                                            <option value="PAGO">PAGO</option>
                                            <option value="CANCELADO">CANCELADO</option>
                                            <option value="VENCIDO">VENCIDO</option>
                                        </select>
                                    </div>
                                </div>
                                <?php include_once 'includes/botao_cadastro.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = function () {
                    document.querySelector('#data_cadastro').value = HOJE;
                    document.querySelector('#data_vencimento').value = DATA_VENCIMENTO;

                    if (CODIGO_CONTA_PAGAR_RECEBER != '') {
                        sistema.request.post('/contas_pagar_receber.php', {
                            'rota': 'pesquisar_conta',
                            'codigo_conta_pagar_receber': CODIGO_CONTA_PAGAR_RECEBER
                        }, function (retorno) {
                            let conta = retorno.dados;
                            document.querySelector('#nome_conta').value = conta.nome_conta;
                            document.querySelector('#descricao').value = conta.descricao;

                            if (conta.valor_conta != 0) {
                                document.querySelector('#valor_conta').value = sistema.number_format(conta.valor_conta);
                            }

                            if (conta.valor_pago != 0) {
                                document.querySelector('#valor_pago').value = sistema.number_format(conta.valor_pago);
                            }

                            if (conta.valor_juro_desconto != 0) {
                                document.querySelector('#valor_juro_desconto').value = sistema.number_format(conta.valor_juro_desconto);
                            }

                            document.querySelector('#tipo_juro_desconto').value = conta.tipo_juro_desconto;
                            document.querySelector('#tipo_conta').value = conta.tipo_conta;
                            document.querySelector('#data_cadastro').value = sistema.retornar_data(conta.data_cadastro, 'AMERICANO');
                            document.querySelector('#data_vencimento').value = sistema.retornar_data(conta.data_vencimento, 'AMERICANO');

                            if (conta.status_conta != 'AGUARDANDO' && conta.status_conta != 'VENCIDO') {
                                document.querySelector('#data_baixa').value = sistema.retornar_data(conta.data_baixa, 'AMERICANO');
                            }
                            document.querySelector('#status_conta').value = conta.status_conta;
                        });
                    }
                }
            </script>
            <?php
            include_once 'includes/footer.php';
});

router_add('pesquisar_conta', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();

    $codigo_conta = (string) (isset($_REQUEST['codigo_conta_pagar_receber']) ? (string) $_REQUEST['codigo_conta_pagar_receber'] : '');
    $filtro = (array) ['filtro' => (array) []];

    if ($codigo_conta != '') {
        $filtro['filtro'] = (array) ['_id', '===', model_id($codigo_conta)];
    }

    echo json_encode((array) ['dados' => (array) $objeto_contas_pagar_receber->pesquisar($filtro)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('salvar_dados', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();

    echo json_encode((array) ['status' => (bool) $objeto_contas_pagar_receber->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('pesquisar_contas', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();
    $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['data_vencimento' => (bool) true], 'limite' => (int) 0];
    $filtro_montando = (array) [];

    $nome_conta = (string) (isset($_REQUEST['nome_conta']) ? (string) $_REQUEST['nome_conta'] : '');
    $descricao = (string) (isset($_REQUEST['descricao']) ? (string) $_REQUEST['descricao'] : '');
    $tipo_conta = (string) (isset($_REQUEST['tipo_conta']) ? (string) $_REQUEST['tipo_conta'] : 'TODOS');
    $status_conta = (string) (isset($_REQUEST['status_conta']) ? (string) $_REQUEST['status_conta'] : 'TODOS');
    $data_inicio_cadastro = (string) (isset($_REQUEST['data_cadastro_inicio']) ? (string) $_REQUEST['data_cadastro_inicio'] : '');
    $data_fim_cadastro = (string) (isset($_REQUEST['data_cadastro_fim']) ? (string) $_REQUEST['data_cadastro_fim'] : '');
    $data_vencimento_inicio = (string) (isset($_REQUEST['data_vencimento_inicio']) ? (string) $_REQUEST['data_vencimento_inicio'] : '');
    $data_vencimento_fim = (string) (isset($_REQUEST['data_vencimento_fim']) ? (string) $_REQUEST['data_vencimento_fim'] : '');
    $data_baixa_inicio = (string) (isset($_REQUEST['data_baixa_inicio']) ? (string) $_REQUEST['data_baixa_inicio'] : '');
    $data_baixa_fim = (string) (isset($_REQUEST['data_baixa_fim']) ? (string) $_REQUEST['data_baixa_fim'] : '');
    $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa'] : '');

    if ($nome_conta != '') {
        array_push($filtro_montando, (array) ['nome_conta', '=', (string) strtoupper($nome_conta)]);
    }

    if ($descricao != '') {
        array_push($filtro_montando, (array) ['descricao', '=', (string) $descricao]);
    }

    if ($tipo_conta != 'TODOS') {
        array_push($filtro_montando, (array) ['tipo_conta', '===', (string) $tipo_conta]);
    }

    if ($status_conta != 'TODOS') {
        array_push($filtro_montando, (array) ['status_conta', '===', (string) $status_conta]);
    }

    if ($empresa != '') {
        array_push($filtro_montando, (array) ['empresa', '===', model_id($empresa)]);
    }

    if ($data_inicio_cadastro != '') {
        array_push($filtro_montando, (array) ['data_cadastro', '>=', model_date($data_inicio_cadastro)]);
    }

    if ($data_fim_cadastro != '') {
        array_push($filtro_montando, (array) ['data_cadastro', '<=', model_date($data_fim_cadastro)]);
    }

    if ($data_vencimento_inicio != '') {
        array_push($filtro_montando, (array) ['data_vencimento', '>=', model_date($data_vencimento_inicio)]);
    }

    if ($data_vencimento_fim != '') {
        array_push($filtro_montando, (array) ['data_vencimento', '<=', model_date($data_vencimento_fim)]);
    }

    if ($data_baixa_inicio != '') {
        array_push($filtro_montando, (array) ['data_baixa', '>=', model_date($data_baixa_inicio)]);
    }

    if ($data_baixa_fim != '') {
        array_push($filtro_montando, (array) ['data_baixa', '<=', model_date($data_baixa_fim)]);
    }

    $filtro['filtro'] = (array) ['and' => (array) $filtro_montando];
    echo json_encode((array) ['dados' => (array) $objeto_contas_pagar_receber->pesquisar_todos($filtro)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('baixar_conta', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();

    // file_put_contents('json.json', json_encode($_REQUEST, JSON_UNESCAPED_UNICODE));
    // echo json_encode(['status' => (bool) true], JSON_UNESCAPED_UNICODE);
    echo json_encode(['status' => (bool) $objeto_contas_pagar_receber->baixar_contas($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});
?>