<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Contas.php';
require_once 'modelos/ContasPagarReceber.php';
require_once 'modelos/FechamentoContabilGeral.php';

router_add('index', function () {
    require_once 'includes/head.php';

    $ano_atual = $data->format('Y');
    $data_atual = $data->format('Y-m-01');
    $ano_conta_futura = $ano_atual - 1;
    $mes_atual = $data->format('m');
    $mes_anterior = $mes_atual - 1;
    $mes_proximo = $mes_atual + 1;

    $data_inicio_mes_anterior = $ano_atual . '-' . str_pad($mes_anterior, 2, '0', STR_PAD_LEFT) . '-01';
    $data_final_mes_anterior = $ano_atual . '-' . str_pad($mes_anterior, 2, '0', STR_PAD_LEFT) . '-' . cal_days_in_month(CAL_GREGORIAN, $mes_anterior, $ano_atual);
    $data_inicio_mes_proximo = $ano_atual . '-' . str_pad($mes_proximo, 2, '0', STR_PAD_LEFT) . '-01';
    $data_final_mes_proximo = $ano_atual . '-' . str_pad($mes_proximo, 2, '0', STR_PAD_LEFT) . '-' . cal_days_in_month(CAL_GREGORIAN, $mes_proximo, $ano_atual);

    $objeto_conta = new Contas();
    $objeto_contas_pagar_receber = new ContasPagarReceber();
    $objeto_fechamento_contabil_geral = new FechamentoContabilGeral();

    $retorno_alterar_status_conta = (bool) $objeto_contas_pagar_receber->alterar_status(['empresa' => (string) $codigo_empresa]);

    $retorno_conta = (array) $objeto_conta->pesquisar_todos((array) ['filtro' => (array) ['and' => (array) [(array) ['empresa', '===', model_id($codigo_empresa)], (array) ['status', '===', (string) 'ATIVO']]], 'ordenacao' => ['nome_conta' => (bool) true], 'limite' => 0]);
    $retorno_contas_pagar = (array) $objeto_contas_pagar_receber->relatorio_contas_pagar($codigo_empresa);
    $retorno_contas_pagar_mes = (array) $objeto_contas_pagar_receber->relatorio_conta_pagar_mes($codigo_empresa, DATA_INICIO, DATA_FINAL);
    $retorno_contas_pagar_mes_anterior = (array) $objeto_contas_pagar_receber->relatorio_conta_pagar_mes($codigo_empresa, $data_inicio_mes_anterior, $data_final_mes_anterior);
    $retorno_contas_pagar_mes_proximo = (array) $objeto_contas_pagar_receber->relatorio_conta_pagar_mes($codigo_empresa, $data_inicio_mes_proximo, $data_final_mes_proximo);
    $retorno_contas_futuras = (array) $objeto_contas_pagar_receber->relatorio_contas_pagar_mensal($codigo_empresa, $ano_conta_futura . '-01-01', '00:00:00');
    $retorno_contas_vencidas = (array) $objeto_contas_pagar_receber->contar_contas_vencidas((array) ['empresa' => $codigo_empresa]);

    $retorno_fechamento_contabil = (array) $objeto_fechamento_contabil_geral->pesquisar_todos(['filtro' => (array) ['and' => (array) [['ano_referencia', '===', (int) $ano_atual], ['empresa', '===', model_id($codigo_empresa)]]], 'ordenacao' => (array) ['mes_referencia' => (bool) true], 'limite' => (int) 12]);
    ?>
    <script>
        const CORES = {
            data1: '#664dc9',
            data2: '#44c4fa',
            data3: '#2dce89',
            data4: '#ff5b51',
            data5: '#f7b924',
            data6: '#20c997',
            data7: '#6f42c1',
            data8: '#e83e8c',
            data9: '#fd7e14',
            data10: '#17a2b8',
            data11: '#6610f2',
            data12: '#28a745',
            data13: '#dc3545',
            data14: '#ffc107',
            data15: '#343a40',
            data16: '#007bff',
            data17: '#6c757d',
            data18: '#ff6f61',
            data19: '#845ef7',
            data20: '#339af0',
            data21: '#22b8cf',
            data22: '#51cf66',
            data23: '#fcc419',
            data24: '#ff922b',
            data25: '#ff6b6b',
            data26: '#f06595',
            data27: '#cc5de8',
            data28: '#5c7cfa',
            data29: '#4dabf7',
            data30: '#15aabf',
            data31: '#12b886',
            data32: '#40c057',
            data33: '#82c91e',
            data34: '#fab005',
            data35: '#fd7e14',
            data36: '#e03131',
            data37: '#c2255c',
            data38: '#9c36b5',
            data39: '#6741d9',
            data40: '#3b5bdb',
            data41: '#364fc7',
            data42: '#1864ab',
            data43: '#0b7285',
            data44: '#087f5b',
            data45: '#2f9e44',
            data46: '#5c940d',
            data47: '#e67700',
            data48: '#d9480f',
            data49: '#c92a2a',
            data50: '#a61e4d'
        };

        const CONTAS = <?php echo json_encode($retorno_conta, JSON_UNESCAPED_UNICODE); ?>;
        const CONTAS_PAGAR_RECEBER = <?php echo json_encode($retorno_contas_pagar, JSON_UNESCAPED_UNICODE); ?>;
        const FECHAMENTO_CONTABIL = <?php echo json_encode($retorno_fechamento_contabil, JSON_UNESCAPED_UNICODE); ?>;
        const CONTAS_FUTURAS = <?php echo json_encode($retorno_contas_futuras, JSON_UNESCAPED_UNICODE); ?>;
        const CONTAS_PAGAR_MES = <?php echo json_encode($retorno_contas_pagar_mes, JSON_UNESCAPED_UNICODE) ?>;
        const CONTAS_PAGAR_MES_ANTERIOR = <?php echo json_encode($retorno_contas_pagar_mes_anterior, JSON_UNESCAPED_UNICODE) ?>;
        const CONTAS_PAGAR_MES_PROXIMO = <?php echo json_encode($retorno_contas_pagar_mes_proximo, JSON_UNESCAPED_UNICODE) ?>;
        /** 
         * Função responsável por montar o relatório de saldo nas contas
        */
        function montar_relatorio_contas() {
            let saldo_contas = [];
            let nomes = {};

            sistema.each(CONTAS, function (contador, conta) {
                let soma_contador = (contador + 1);

                if (conta.saldo_conta == 0) {
                    nomes['data' + soma_contador] = conta.nome_conta + ' ( = ) ' + conta.saldo_conta;
                } else if (conta.saldo_conta < 0) {
                    nomes['data' + soma_contador] = conta.nome_conta + ' ( - ) ' + conta.saldo_conta;
                } else {
                    nomes['data' + soma_contador] = conta.nome_conta + ' ( + ) ' + conta.saldo_conta;
                }

                saldo_contas.push(['data' + soma_contador, conta.saldo_conta]);
            });

            var chart = c3.generate({
                bindto: '#saldo_nas_contas',
                data: {
                    columns: saldo_contas,
                    type: 'pie',
                    colors: CORES,
                    names: nomes
                },
                axis: {},
                legend: {
                    show: true,
                },
                padding: {
                    bottom: 0,
                    top: 0
                },
            });
        }

        /** 
         * Função responsável por montar o relatório de contas a pagar e receber
        */
        function montar_relatorio_contas_pagar() {
            let valor_contas = [];
            let nomes = {};

            sistema.each(CONTAS_PAGAR_RECEBER, function (contador, conta) {
                let soma_contador = (contador + 1);

                nomes['data' + soma_contador] = conta['tipo_conta'] + ' ' + conta['status_conta'] + ' ' + sistema.number_format(conta['SUM(valor_conta)']);
                valor_contas.push(['data' + soma_contador, conta['SUM(valor_conta)']]);
            });

            var chart = c3.generate({
                bindto: '#contas_a_pagar',
                data: {
                    columns: valor_contas,
                    type: 'pie',
                    colors: CORES,
                    names: nomes
                },
                axis: {},
                legend: {
                    show: true,
                },
                padding: {
                    bottom: 0,
                    top: 0
                },
            });
        }

        /** 
         * Função responsável por montar o relatório de contas do mês atual
        */
        function montar_relatorio_contas_pagar_mes() {
            let valor_contas = [];
            let nomes = {};

            sistema.each(CONTAS_PAGAR_MES, function (contador, conta) {
                let soma_contador = (contador + 1);

                nomes['data' + soma_contador] = conta['tipo_conta'] + ' ' + conta['status_conta'] + ' ' + sistema.number_format(conta['SUM(valor_conta)']);
                valor_contas.push(['data' + soma_contador, conta['SUM(valor_conta)']]);
            });

            var chart = c3.generate({
                bindto: '#contas_mes_atual',
                data: {
                    columns: valor_contas,
                    type: 'pie',
                    colors: CORES,
                    names: nomes
                },
                axis: {},
                legend: {
                    show: true,
                },
                padding: {
                    bottom: 0,
                    top: 0
                },
            });
        }

        /**  
         * Função responsável por montar o relatório de contas do mês anterior
        */
        function montar_relatorio_contas_pagar_mes_anterior() {
            let valor_contas = [];
            let nomes = {};

            sistema.each(CONTAS_PAGAR_MES_ANTERIOR, function (contador, conta) {
                let soma_contador = (contador + 1);
                nomes['data' + soma_contador] = conta['tipo_conta'] + ' ' + conta['status_conta'] + ' ' + sistema.number_format(conta['SUM(valor_conta)']);
                valor_contas.push(['data' + soma_contador, conta['SUM(valor_conta)']]);
            });

            var chart = c3.generate({
                bindto: '#contas_mes_anterior',
                data: {
                    columns: valor_contas,
                    type: 'pie',
                    colors: CORES,
                    names: nomes
                },
                axis: {},
                legend: {
                    show: true,
                },
                padding: {
                    bottom: 0,
                    top: 0
                },
            });
        }

        /** 
         * Função responsável por montar o relatório de contas do próximo mês
        */
        function montar_relatorio_contas_pagar_mes_proximo() {
            let valor_contas = [];
            let nomes = {};

            sistema.each(CONTAS_PAGAR_MES_PROXIMO, function (contador, conta) {
                let soma_contador = (contador + 1);

                nomes['data' + soma_contador] = conta['tipo_conta'] + ' ' + conta['status_conta'] + ' ' + sistema.number_format(conta['SUM(valor_conta)']);
                valor_contas.push(['data' + soma_contador, conta['SUM(valor_conta)']]);
            });

            var chart = c3.generate({
                bindto: '#contas_mes_proximo',
                data: {
                    columns: valor_contas,
                    type: 'pie',
                    colors: CORES,
                    names: nomes
                },
                axis: {},
                legend: {
                    show: true,
                },
                padding: {
                    bottom: 0,
                    top: 0
                },
            });
        }

        /** 
         * Função responsável por montar o relatório de fechamento contábil
        */
        function relatorio_fechamento_contabil() {
            let debito = ['data1'];
            let credito = ['data2'];
            let resultado = ['data3'];
            let names = {
                'data1': 'DEBITO',
                'data2': 'CRÉDITO',
                'data3': 'RESULTADO'
            };

            sistema.each(FECHAMENTO_CONTABIL, function (contador, fechamento) {
                debito.push(fechamento.total_debito);
                credito.push(fechamento.total_credito);
                resultado.push(fechamento.valor_resultado);
            });

            var chart = c3.generate({
                bindto: '#fechamento_contabil',
                data: {
                    columns: [
                        debito,
                        credito,
                        resultado
                    ],
                    labels: true,
                    type: 'spline',
                    colors: {
                        CORES
                    },
                    names
                },
                axis: {
                    x: {
                        type: 'category',
                        categories: ['JANEIRO', 'FEVEREIRO', 'MARÇO', 'ABRIL', 'MAIO', 'JUNHO', 'JULHO', 'AGOSTO', 'SETEMBRO', 'OUTUBRO', 'NOVEMBRO', 'DEZEMBRO']
                    },
                },
                legend: {
                    show: false,
                },
                padding: {
                    bottom: 0,
                    top: 0
                },
            });
        }

        /** 
         * Função responsável por montar o relatório de histório de contas
        */
        function historico_contas() {

            let series = {};
            let categories = [];
            let mesesUnicos = [];

            sistema.each(CONTAS_FUTURAS, function (contador, contas) {

                let chaveMes = contas.ano + '-' + ('0' + contas.mes).slice(-2);

                let chaveSerie = contas.tipo_conta + ' - ' + contas.status_conta;

                if (!mesesUnicos.includes(chaveMes)) {
                    mesesUnicos.push(chaveMes);
                }

                if (!series[chaveSerie]) {
                    series[chaveSerie] = [chaveSerie];
                }

                if (!series[chaveSerie + '_' + chaveMes]) {
                    series[chaveSerie + '_' + chaveMes] = contas.total_valor;
                }
            });

            mesesUnicos.sort();

            let columns = [];

            Object.keys(series).forEach(function (key) {

                if (key.indexOf('_') === -1) {

                    let arr = [key];

                    mesesUnicos.forEach(function (mes) {

                        let valor = series[key + '_' + mes] || 0;
                        arr.push(valor);

                    });

                    columns.push(arr);
                }
            });

            var chart = c3.generate({
                bindto: '#historico_contas',
                data: {
                    columns: columns,
                    type: 'spline'
                },
                axis: {
                    x: {
                        type: 'category',
                        categories: mesesUnicos
                    }
                },
                legend: {
                    show: true
                },
                padding: {
                    bottom: 0,
                    top: 0
                }
            });
        }
    </script>

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">

                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Dashboard</h3>
                    </div>
                </div>
            </div>
            <?php
            if ($retorno_contas_vencidas['quantidade_contas'] > 0) {
                $texto = (string) '';

                if (HORA >= 0 && HORA < 12) {
                    $texto = (string) 'BOM DIA ' . $login_usuario;
                } else if (HORA >= 12 && HORA < 18) {
                    $texto = (string) 'BOA TARDE ' . $login_usuario;
                } else if (HORA >= 18 && HORA <= 23) {
                    $texto = (string) 'BOA NOITE ' . $login_usuario;
                }

                if ($retorno_contas_vencidas['quantidade_contas'] >= 1) {
                    $texto = $texto . '<br/> ATENÇÃO, VOCÊ POSSUI CONTAS VENCIDAS!';
                }

                ?>
                <div class="bg-primary rounded welcome-wrap position-relative mb-3">
                    <div class="row">
                        <div class="col-lg-8 col-md-9 col-sm-7">
                            <div>
                                <h5 class="text-white mb-1"><?php echo $texto; ?></h5>
                                <p class="text-white mb-3">Você possui o total de (<strong>
                                        <?php echo $retorno_contas_vencidas['quantidade_contas']; ?> </strong>) vencidas no
                                    valor total de R$:
                                    <strong><?php echo formatar_numero($retorno_contas_vencidas['valor_total_contas'], 2, ',', '.'); ?></strong>
                                </p>
                                <!-- <div class="d-flex align-items-center flex-wrap gap-3">
                                    <p class="d-flex align-items-center fs-13 text-white mb-0"><i
                                            class="isax isax-calendar5 me-1"></i>Friday, 24 Mar 2025</p>
                                    <p class="d-flex align-items-center fs-13 text-white mb-0"><i
                                            class="isax isax-clock5 me-1"></i>11:24 AM</p>
                                </div> -->
                            </div>
                        </div>
                    </div>
                    <div class="position-absolute end-0 top-50 translate-middle-y p-2 d-none d-sm-block">
                        <img src="imagens/icones/dashboard.svg" alt="img">
                    </div>
                </div>
                <?php
            }
            ?>
            <div class="row">

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Contas A Pagar | Receber <?php echo MES_NOME_ANTERIOR; ?></div>
                        </div>
                        <div class="card-body">
                            <div id="contas_mes_anterior"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Contas A Pagar | Receber <?php echo MES_NOME; ?></div>
                        </div>
                        <div class="card-body">
                            <div id="contas_mes_atual"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Contas A Pagar | Receber <?php echo MES_NOME_PROXIMO_MES; ?></div>
                        </div>
                        <div class="card-body">
                            <div id="contas_mes_proximo"></div>
                        </div>
                    </div>
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Saldo nas Contas</div>
                        </div>
                        <div class="card-body">
                            <div id="saldo_nas_contas"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Contas A Pagar | Contas A Receber</div>
                        </div>
                        <div class="card-body">
                            <div id="contas_a_pagar"></div>
                        </div>
                    </div>
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Histórico de Contas</div>
                        </div>
                        <div class="card-body">
                            <div id="historico_contas"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Fechamento Contábil</div>
                        </div>
                        <div class="card-body">
                            <div id="fechamento_contabil"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function () {
                montar_relatorio_contas();
                montar_relatorio_contas_pagar();
                relatorio_fechamento_contabil();
                historico_contas();
                montar_relatorio_contas_pagar_mes();
                montar_relatorio_contas_pagar_mes_anterior();
                montar_relatorio_contas_pagar_mes_proximo();
            }
        </script>
        <?php
        require_once 'includes/footer.php';
});
?>