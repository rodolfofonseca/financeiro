<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Contas.php';
require_once 'modelos/ContasPagarReceber.php';

router_add('index', function () {
    require_once 'includes/head.php';

    $objeto_conta = new Contas();
    $objeto_contas_pagar_receber = new ContasPagarReceber();

    $retorno_alterar_status_conta = (bool) $objeto_contas_pagar_receber->alterar_status(['empresa' => (string) $codigo_empresa]);

    $retorno_conta = (array) $objeto_conta->pesquisar_todos((array) ['filtro' => (array) ['empresa', '===', model_id($codigo_empresa)], 'ordenacao' => ['nome_conta' => (bool) true], 'limite' => 0]);
    $retorno_contas_pagar = (array) $objeto_contas_pagar_receber->relatorio_contas_pagar($codigo_empresa);
    ?>
    <script>
        const CORES = { data1: '#664dc9', data2: '#44c4fa', data3: '#2dce89', data4: '#ff5b51', data5: '#f7b924', data6: '#20c997', data7: '#6f42c1', data8: '#e83e8c', data9: '#fd7e14', data10: '#17a2b8', data11: '#6610f2', data12: '#28a745', data13: '#dc3545', data14: '#ffc107', data15: '#343a40', data16: '#007bff', data17: '#6c757d', data18: '#ff6f61', data19: '#845ef7', data20: '#339af0', ata21: '#22b8cf', data22: '#51cf66', data23: '#fcc419', data24: '#ff922b', data25: '#ff6b6b', data26: '#f06595', data27: '#cc5de8', data28: '#5c7cfa', data29: '#4dabf7', data30: '#15aabf', data31: '#12b886', data32: '#40c057', data33: '#82c91e', data34: '#fab005', data35: '#fd7e14', data36: '#e03131', data37: '#c2255c', data38: '#9c36b5', data39: '#6741d9', data40: '#3b5bdb', data41: '#364fc7', data42: '#1864ab', data43: '#0b7285', data44: '#087f5b', data45: '#2f9e44', data46: '#5c940d', data47: '#e67700', data48: '#d9480f', data49: '#c92a2a', data50: '#a61e4d' };

        const CONTAS = <?php echo json_encode($retorno_conta, JSON_UNESCAPED_UNICODE); ?>;
        const CONTAS_PAGAR_RECEBER = <?php echo json_encode($retorno_contas_pagar, JSON_UNESCAPED_UNICODE); ?>;

        function montar_relatorio_contas() {
            let saldo_contas = [];
            let nomes = {};

            sistema.each(CONTAS, function (contador, conta) {
                let soma_contador = (contador + 1);
                nomes['data' + soma_contador] = conta.nome_conta + '-' + conta.saldo_conta;
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

        function montar_relatorio_contas_pagar() {
            let valor_contas = [];
            let nomes = {};

            sistema.each(CONTAS_PAGAR_RECEBER, function (contador, conta) {
                console.log(conta['status_conta']);
                console.log(conta['COUNT(*)']);
                console.log(conta['SUM(valor_conta)']);
                let soma_contador = (contador + 1);

                console.log(soma_contador);

                nomes['data' + soma_contador] = conta['status_conta'] + '-' + sistema.number_format(conta['SUM(valor_conta)']);
                valor_contas.push(['data' + soma_contador, conta['COUNT(*)']]);
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
                            <div class="card-title">Contas A Pagar</div>
                        </div>
                        <div class="card-body">
                            <div id="contas_a_pagar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function () {
                montar_relatorio_contas();
                montar_relatorio_contas_pagar();
            }
        </script>
        <?php
        require_once 'includes/footer.php';
});
?>