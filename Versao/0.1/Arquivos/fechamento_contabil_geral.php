<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/FechamentoContabilGeral.php';

router_add('index', function () {
    require_once 'includes/head.php';
?>
    <script>
        const EMPRESA = "<?php echo $codigo_empresa; ?>";

        function fazer_fechamento(){
            let ano_referencia = document.querySelector('#ano_referencia').value;
            let mes_referencia = document.querySelector('#mes_referencia').value;
            let validacao = true;

            if(ano_referencia == ''){
                alerta_campo_vazio('ANO REFEÊNCIA');
                validacao = false;
            }

            if(mes_referencia == ''){
                alerta_campo_vazio('MÊS REFERENCIA');
                validacao = false;
            }

            if(validacao == true){
                sistema.request.post('/fechamento_contabil_geral.php', {'rota':'cadastro_fechamento', 'empresa':EMPRESA, 'mes_referencia':mes_referencia, 'ano_referencia':ano_referencia}, function(retorno){
                    validar_retorno(retorno, '/fechamento_contabil_geral.php');
                });
            }
        }

        function pesquisar_fechamento_contabil(){
            let mes_referencia = document.querySelector('#mes_referencia_pesquisa').value;
            let ano_referencia = document.querySelector('#ano_referencia_pesquisa').value;

            sistema.request.post('/fechamento_contabil_geral.php', {'rota':'pesquisar_fechamento', 'empresa':EMPRESA, 'mes_referencia':mes_referencia, 'ano_referencia':ano_referencia}, function(retorno){
                let fechamentos = retorno.dados;
                let tamanho_retorno = fechamentos.length;
                let tabela = document.querySelector('#tabela_fechamento_contabil tbody');

                tabela = sistema.remover_linha_tabela(tabela);
                
                if(tamanho_retorno == 0){
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center'], 'NENHUM FECHAMENTO ENCONTRADO COM O FILTRO PASSADO!', 'inner', true, 8));
                    tabela.appendChild(linha);
                }else{
                    sistema.each(fechamentos, function(index, fechamento){
                        let mes = {1: "JANEIRO", 2: "FEVEREIRO", 3: "MARÇO", 4: "ABRIL", 5: "MAIO", 6: "JUNHO", 7: "JULHO", 8: "AGOSTO", 9: "SETEMBRO", 10: "OUTUBRO", 11: "NOVEMBRO", 12: "DEZEMBRO"};
                        let linha = document.createElement('tr');

                        linha.appendChild(sistema.gerar_td(['text-center'], mes[fechamento.mes_referencia], 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], fechamento.ano_referencia, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(fechamento.total_credito), 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(fechamento.total_debito), 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(fechamento.valor_resultado), 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], fechamento.resultado, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(fechamento.data_fechamento), 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_excluir_fechamento_'+fechamento._id.$oid, 'EXCLUIR', ['btn', 'btn-danger'], function excluir_fechamento_botao(){excluir_fechamento(fechamento._id.$oid);}), 'append'));

                        tabela.appendChild(linha);
                    });
                }
            });
        }

        function excluir_fechamento(codigo_fechamento){
            sistema.request.post('/fechamento_contabil_geral.php', {'rota':'excluir_fechamento_contabil', 'codigo_fechamento':codigo_fechamento}, function(retorno){
                validar_retorno(retorno, '/fechamento_contabil_geral.php');
            });
        }
    </script>

<div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Fechamento Contábil Geral</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#modal_baixar_conta">Fazer Fechamento</button>
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
                                <div class="col-6 text-center">
                                    <label class="text">Selecione um mês</label>
                                    <select id="mes_referencia_pesquisa" class="form-control">
                                        <option value="0">TODOS</option>
                                        <option value="1">JANEIRO</option>
                                        <option value="2">FEVEREIRO</option>
                                        <option value="3">MARÇO</option>
                                        <option value="4">ABRIL</option>
                                        <option value="5">MAIO</option>
                                        <option value="6">JUNHO</option>
                                        <option value="7">JULHO</option>
                                        <option value="8">AGOSTO</option>
                                        <option value="9">SETEMBRO</option>
                                        <option value="10">OUTUBRO</option>
                                        <option value="11">NOVEMBRO</option>
                                        <option value="12">DEZEMBRO</option>
                                    </select>
                                </div>
                                <div class="col-6 text-center">
                                    <label class="text">Selecione um ano</label>
                                    <select class="form-control" id="ano_referencia_pesquisa">
                                        <option value="0">TODOS</option>
                                        <option value="2026">2026</option>
                                    </select>
                                </div>
                            </div>
                            <br/>
                            <div class="col-3 push-9">
                                <button class="btn btn-secondary w-100" onclick="pesquisar_fechamento_contabil();">Pesquisar</button>
                            </div>
                            <br/>
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_fechamento_contabil">
                                            <thead>
                                                <tr class="text-center">
                                                    <th scope="col">Mês</th>
                                                    <th scope="col">Ano Referência</th>
                                                    <th scope="col">Crédito</th>
                                                    <th scope="col">Débito</th>
                                                    <th scope="col">Diferênça</th>
                                                    <th scope="col">Resultado</th>
                                                    <th scope="col">Data</th>
                                                    <th scope="col">Excluir</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="8" class="text-center">NENHUM FECHAMENTO ENCONTRADO!</td>
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
        <div class="modal fade" id="modal_baixar_conta" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Fachemento Contábil</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <label class="text">Mês Referência</label>
                                <select id="mes_referencia" class="form-control">
                                    <option value="">Selecione uma Opção</option>
                                    <option value="1">JANEIRO</option>
                                    <option value="2">FEVEREIRO</option>
                                    <option value="3">MARÇO</option>
                                    <option value="4">ABRIL</option>
                                    <option value="5">MAIO</option>
                                    <option value="6">JUNHO</option>
                                    <option value="7">JULHO</option>
                                    <option value="8">AGOSTO</option>
                                    <option value="9">SETEMBRO</option>
                                    <option value="10">OUTUBRO</option>
                                    <option value="11">NOVEMBRO</option>
                                    <option value="12">DEZEMBRO</option>
                                </select>
                            </div>
                            <div class="col-6 text-center">
                                <label class="text">Ano Referência</label>
                                <select id="ano_referencia" class="form-control">
                                    <option value="">Selecione uma Opção</option>
                                    <option value="2026">2026</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <div class="row">
                            <div class="col-4 push-4">
                                <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal" aria-label="Close">Fechar</button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-success w-100" onclick="fazer_fechamento();">Fazer Fechamento</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function(){
                pesquisar_fechamento_contabil();
            }
        </script>
    <?php
    require_once 'includes/footer.php';
    exit;
});

router_add('cadastro_fechamento', function () {
    $objeto_fechamento_contabil_geral = new FechamentoContabilGeral();

    echo json_encode(['status' => (bool) $objeto_fechamento_contabil_geral->fazer_fechamento($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('pesquisar_fechamento', function(){
    $objeto_fechamento_contabil_geral = new FechamentoContabilGeral();

    $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa']:'');
    $mes_referencia = (int) (isset($_REQUEST['mes_referencia']) ? (int) intval($_REQUEST['mes_referencia'], 10):0);
    $ano_referencia = (int) (isset($_REQUEST['ano_referencia']) ? (int) intval($_REQUEST['ano_referencia'], 10):0);

    $filtro_montando = (array) [];

    if($empresa != ''){
        array_push($filtro_montando, ['empresa', '===', model_id($empresa)]);
    }

    if($ano_referencia != 0){
        array_push($filtro_montando, ['ano_referencia', '===', (int) $ano_referencia]);
    }

    if($mes_referencia != 0){
        array_push($filtro_montando, ['mes_referencia', '===', (int) $mes_referencia]);
    }

    $filtro = (array) ['filtro' => (array) ['and' => $filtro_montando], 'ordenacao' => (bool) ['_id' => (bool) false], 'limite' => (int) 0];

    echo json_encode(['dados' => (array) $objeto_fechamento_contabil_geral->pesquisar_todos($filtro)], JSON_UNESCAPED_UNICODE);
});

router_add('excluir_fechamento_contabil', function(){
    $objeto_fechamento_contabil_geral = new FechamentoContabilGeral();

    $codigo_fechamento = (string) (isset($_REQUEST['codigo_fechamento']) ? (string) $_REQUEST['codigo_fechamento']:'');

    $filtro = (array) [];
    $retorno_exclusao = (bool) false;

    if($codigo_fechamento != ''){
        $filtro = (array) ['_id', '===', model_id($codigo_fechamento)];
    }

    if(empty($filtro) == false){
        $retorno_exclusao = (bool) $objeto_fechamento_contabil_geral->excluir_fechamento($filtro);
    }

    echo json_encode(['status' => (bool) $retorno_exclusao], JSON_UNESCAPED_UNICODE);
}); 
?>