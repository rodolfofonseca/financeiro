<?php
require_once 'classes/bancoDeDados.php';
require_once 'classes/Extenso.php';
require_once 'modelos/ContasPagarReceber.php';
require_once 'modelos/Empresa.php';
require_once 'modelos/DocumentosComprovantes.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST)) {
        if (array_key_exists('rota', $_POST) == true) {
            if ($_POST['rota'] == 'anexar_documentos') {
                $objeto_documentos_comprovantes = new DocumentosComprovantes();

                $retorno = (bool) $objeto_documentos_comprovantes->salvar_dados_arquivos($_POST, $_FILES);

                if ($retorno == true) {
                    header('Location: contas_pagar_receber.php?comprovante=true&retorno=true');
                } else {
                    header('Location: contas_pagar_receber.php?comprovante=true&retorno=false');
                }
            }
        }
    }
}

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

router_add('excluir_conta', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();
    $filtro = (array) [];

    $codigo_conta = (string) (isset($_REQUEST['codigo_conta']) ? (string) $_REQUEST['codigo_conta'] : '');

    if ($codigo_conta != '') {
        $filtro['filtro'] = (array) ['_id', '===', model_id($codigo_conta)];
    }

    echo json_encode((array) ['status' => (bool) $objeto_contas_pagar_receber->deletar_conta((array) $filtro), JSON_UNESCAPED_UNICODE]);
    exit;
});

router_add('baixar_conta', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();
    echo json_encode(['status' => (bool) $objeto_contas_pagar_receber->baixar_contas($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('cadastro_contas_recorrentes', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();

    $json_contas = (array) (isset($_REQUEST['objeto_json']) ? (array) json_decode($_REQUEST['objeto_json'], true) : []);

    $retorno = (bool) false;

    foreach ($json_contas as $contas) {
        $retorno = $objeto_contas_pagar_receber->salvar_dados($contas);
    }

    echo json_encode(['status' => (bool) $retorno], JSON_UNESCAPED_UNICODE);
});

router_add('imprimir_promissoria', function () {
    require_once 'includes/head_sem_menu.php';

    $objeto_empresa = new Empresa();
    $objeto_conta = new ContasPagarReceber();
    $extesao = new Extenso();

    $codigo_conta = (string) (isset($_REQUEST['codigo_conta']) ? (string) $_REQUEST['codigo_conta'] : '');
    $codigo_empresa = (string) (isset($_REQUEST['codigo_empresa']) ? (string) $_REQUEST['codigo_empresa'] : '');

    $retorno_empresa = (array) $objeto_empresa->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_empresa)]]);
    $retorno_conta = (array) $objeto_conta->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_conta)]]);

    $nome_empresa = (string) '';
    $data_vencimento_conta = (string) '';
    $valor_conta = (string) '0,00';
    $valor_extenso = (string) '';
    $cidade = (string) 'SÃO JOSÉ DO RIO PRETO-SP';
    $numero_conta = (string) '';

    $dia = (string) $data->format('d');
    $mes = (string) $data->format('m');
    $ano = (string) $data->format('Y');

    if (empty($retorno_empresa) == false) {
        $nome_empresa = (string) $retorno_empresa['nome_empresa'];

        if (array_key_exists('cidade', $retorno_empresa) == true) {
            $cidade = (string) $retorno_conta['cidade'];
        }
    }

    if (empty($retorno_conta) == false) {
        $data_vencimento_conta = (string) convert_date($retorno_conta['data_vencimento'], 'd/m/Y');
        $valor_conta = (string) $retorno_conta['valor_conta'];
        $numero_conta = (string) $retorno_conta['_id'];
    }

    $valor_extenso = $extesao->converte($valor_conta, true, false);
?>
    <style>
        .nota {
            max-width: 800px;
            margin: auto;
            border: 2px solid #000;
            padding: 30px;
        }

        .linha {
            border-bottom: 1px solid #000;
            height: 25px;
        }

        .assinatura {
            margin-top: 80px;
            border-top: 1px solid #000;
            text-align: center;
            width: 300px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
    <div class="container mt-5">

        <div class="nota bg-white">

            <h3 class="text-center mb-4">NOTA PROMISSÓRIA</h3>
            <button class="btn btn-primary no-print" onclick="window.print()">Imprimir</button>

            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Nº:</strong> <?php echo $numero_conta; ?>
                </div>
                <div class="col-md-6 text-end">
                    <strong>Valor:</strong> R$ <?php echo $valor_conta; ?>
                </div>
            </div>

            <p>
                Ao(s) <span class="linha d-inline-block w-25"><?php echo $nome_empresa; ?></span> na data de
                <span class="linha d-inline-block w-25"> <?php echo $data_vencimento_conta; ?></span>,
                pagarei por esta única via de <strong>NOTA PROMISSÓRIA</strong> a quantia de
                <strong>R$ <?php echo $valor_conta; ?></strong> Valor por extenso: <strong><?php echo $valor_extenso; ?></strong> à
            </p>

            <p>
                <span class="linha d-inline-block w-75"><?php echo $nome_empresa; ?></span>
            </p>

            <p>
                ou à sua ordem, a importância acima mencionada em moeda corrente deste país.
            </p>

            <div class="row mt-5">
                <div class="col-md-6">
                    <p><strong>Local:</strong> <?php echo $cidade; ?></p>
                </div>

                <div class="col-md-6 text-end">
                    <p><strong>Data:</strong> <?php echo $dia; ?> / <?php echo $mes; ?> / <?php echo $ano; ?></p>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <div class="assinatura">
                    Assinatura do Emitente
                </div>
            </div>

        </div>

    <?php

    require_once 'includes/footer_sem.php';
});

router_add('index', function () {
    include_once 'includes/head.php';
    $data_hoje = $data->format('Y-m-d');
    $data_inicio = $data->format('Y-m-01');
    $data_fim = $data->format('Y-m-t');
    ?>
        <script>
            const EMPRESA = "<?php echo $codigo_empresa; ?>";
            const DATA_HOJE = "<?php echo $data_hoje; ?>";
            const DATA_INICIO = "<?php echo $data_inicio; ?>";
            const DATA_FIM = "<?php echo $data_fim; ?>";
            const ANEXA_DOCUMENTOS = <?php echo ($anexa_documentos == true) ? 1 : 0; ?>;

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

                if (data_vencimento_inicio == '') {
                    data_vencimento_inicio = DATA_INICIO;
                }

                if (data_vencimento_fim == '') {
                    data_vencimento_fim = DATA_FIM;
                }

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
                }, function(retorno) {
                    let contas = retorno.dados;
                    let tabela_contas = document.querySelector('#tabela_contas tbody');
                    let tamanho_retorno = contas.length;

                    tabela = sistema.remover_linha_tabela(tabela_contas);

                    if (tamanho_retorno == 0) {
                        let linha = document.createElement('tr');
                        linha.appendChild(sistema.gerar_td(['text-center'], 'NENHUMA CONTA ENCONTRADA, COM OS FILTROS PASSADOS!', 'inner', true, 15));
                        tabela_contas.appendChild(linha);
                    } else {
                        sistema.each(contas, function(index, conta) {
                            let linha = document.createElement('tr');

                            linha.appendChild(sistema.gerar_td(['text-center'], conta.nome_conta, 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-center'], conta.descricao, 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(conta.valor_conta), 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(conta.data_vencimento), 'inner'));

                            if (conta.status_conta == 'AGUARDANDO' || conta.status_conta == 'VENCIDA') {
                                linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));

                            } else {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(conta.data_baixa), 'inner'));
                            }

                            if (conta.tipo_conta == 'PAGAR') {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta._id.$oid, 'PAGAR', ['btn', 'btn-outline-danger'], function tipo_conta() {}), 'append'));
                            } else {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta._id.$oid, 'RECEBER', ['btn', 'btn-outline-success'], function tipo_conta() {}), 'append'));
                            }

                            if (conta.status_conta == 'AGUARDANDO') {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_conta_' + conta._id.$oid, 'AGUARDANDO', ['btn', 'btn-outline-secondary'], function status_conta() {}), 'append'));
                            } else if (conta.status_conta == 'PAGO') {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_conta_' + conta._id.$oid, 'PAGO', ['btn', 'btn-outline-success'], function status_conta() {}), 'append'));
                            } else if (conta.status_conta == 'CANCELADO') {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_conta_' + conta._id.$oid, 'CANCELADO', ['btn', 'btn-outline-warning'], function status_conta() {}), 'append'));
                            } else if (conta.status_conta == 'VENCIDA') {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_conta_' + conta._id.$oid, 'VENCIDA', ['btn', 'btn-outline-danger'], function status_conta() {}), 'append'));
                            }

                            let botao = document.createElement('button');
                            botao.id = 'botao_baixar_conta_' + conta._id.$oid;
                            botao.textContent = 'BAIXAR';
                            botao.classList.add('btn');
                            botao.classList.add('btn-primary');
                            botao.dataset.bsToggle = "modal";
                            botao.dataset.bsTarget = "#modal_baixar_conta";

                            if (conta.status_conta == 'PAGO') {
                                botao.disabled = true;
                            }

                            botao.addEventListener('click', function() {
                                document.querySelector('#valor_conta').value = sistema.number_format(conta.valor_conta);
                                document.querySelector('#data_vencimento').value = sistema.retornar_data(conta.data_vencimento, 'AMERICANO');
                                document.querySelector('#codigo_conta_pagar_receber').value = conta._id.$oid;
                                document.querySelector('#tipo_conta_input').value = conta.tipo_conta;
                                document.querySelector('#nome_conta_input').value = conta.nome_conta;
                            });
                            if (conta.status_conta == 'PAGO') {
                                if (ANEXA_DOCUMENTOS == 1) {
                                    if (conta.comprovante == 'NAO') {
                                        let botao_documentos = document.createElement('button');
                                        botao_documentos.id = 'botao_anexo_documentos_' + conta._id.$oid;
                                        botao_documentos.textContent = 'COMPROVANTE';
                                        botao_documentos.classList.add('btn');
                                        botao_documentos.classList.add('btn-success');
                                        botao_documentos.dataset.bsToggle = 'modal';
                                        botao_documentos.dataset.bsTarget = '#modal_anexar_documentos';
                                        botao_documentos.addEventListener('click', function() {
                                            document.querySelector('#codigo_local').value = conta._id.$oid;
                                            document.querySelector('#nome_conta_anexo_documentos').value = conta.nome_conta;
                                            document.querySelector('#empresa_anexo_documento').value = EMPRESA;
                                        });

                                        linha.appendChild(sistema.gerar_td(['text-center'], botao_documentos, 'append'));
                                    } else {
                                        let botao_baixar_arquivo = document.createElement('button');
                                        botao_baixar_arquivo.id = 'botao_baixar_comprovante_' + conta._id.$oid;
                                        botao_baixar_arquivo.textContent = 'BAIXAR COMPROVANTE';
                                        botao_baixar_arquivo.classList.add('btn');
                                        botao_baixar_arquivo.classList.add('btn-info');

                                        botao_baixar_arquivo.onclick = function() {
                                            window.open(sistema.url('/anexos/comprovantes/contas_pagar_receber/') + conta._id.$oid + ".pdf", "_blank");
                                        }

                                        linha.appendChild(sistema.gerar_td(['text-center'], botao_baixar_arquivo, 'append'));
                                    }
                                }

                                linha.appendChild(sistema.gerar_td(['text-center'], botao, 'append'));
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_imprimir_conta_' + conta._id.$oid, 'IMPRIMIR', ['btn', 'btn-secondary', 'disabled'], function imprimir_conta_botao() {}), 'append'));
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_excluir_conta_' + conta._id.$oid, 'EXCLUIR', ['btn', 'btn-danger', 'disabled'], function imprimir_conta_botao() {}), 'append'));
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_editar_conta_' + conta._id.$oid, 'EDITAR', ['btn', 'btn-primary', 'disabled'], function baixar_conta_botao() {}), 'append'));
                            } else {
                                linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));
                                linha.appendChild(sistema.gerar_td(['text-center'], botao, 'append'));
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_imprimir_conta_' + conta._id.$oid, 'IMPRIMIR', ['btn', 'btn-secondary'], function imprimir_conta_botao() {
                                    abrir_modal_impressao_promissoria(conta._id.$oid);
                                }), 'append'));

                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_excluir_conta_' + conta._id.$oid, 'EXCLUIR', ['btn', 'btn-danger'], function excluir_conta_botao() {
                                    Swal.fire({
                                        title: "Quer mesmo deletar?",
                                        text: "Essa operação é irreversível!",
                                        icon: "warning",
                                        showCancelButton: true,
                                        confirmButtonColor: "#3085d6",
                                        cancelButtonColor: "#d33",
                                        confirmButtonText: "Sim, Deletar agora!"
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            sistema.request.post('/contas_pagar_receber.php', {
                                                'rota': 'excluir_conta',
                                                'codigo_conta': conta._id.$oid
                                            }, function(retorno) {
                                                if (retorno.status == true) {
                                                    Swal.fire({
                                                        title: "Deletado!",
                                                        text: "A conta foi deletetada.",
                                                        icon: "success"
                                                    });
                                                } else {
                                                    Swal.fire({
                                                        title: "ERRO!",
                                                        text: "Erro durante o processo de exclusão.",
                                                        icon: "error"
                                                    });
                                                }
                                                window.location.href = sistema.url('/contas_pagar_receber.php', { 'rota': 'index' });
                                            });
                                        }

                                    });
                                }), 'append'));

                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_editar_conta_' + conta._id.$oid, 'EDITAR', ['btn', 'btn-primary'], function baixar_conta_botao() {
                                    cadastro_contas(conta._id.$oid);
                                }), 'append'));
                            }


                            tabela_contas.appendChild(linha);
                        });
                    }
                });
            }

            function abrir_modal_impressao_promissoria(codigo_conta) {
                let largura = 1200;
                let altura = 500;
                let left = (screen.width - largura) / 2;
                let top = (screen.height - altura) / 2;
                let url = sistema.url('/contas_pagar_receber.php', {
                    'rota': 'imprimir_promissoria',
                    'codigo_conta': codigo_conta,
                    'codigo_empresa': EMPRESA
                });
                let nome = 'Impressão de Promissória';
                let janela = window.open(url, nome, `width=${largura}, height=${altura}, left=${left}, top=${top}`);

                if (window.focus) {
                    janela.focus();
                }
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
                }, function(retorno) {
                    let contas = retorno.dados;
                    let tamanho_retorno = contas.length;
                    if (tamanho_retorno > 0) {
                        let select_conta = document.querySelector('#conta');

                        sistema.each(contas, function(index, conta) {
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
                let objeto_json = {
                    'rota': 'baixar_conta',
                    'codigo_conta_pagar_receber': codigo_conta_pagar_receber,
                    'valor_pago': valor_pago,
                    'tipo_juro_desconto': tipo_juro_desconto,
                    'valor_juro_desconto': valor_juro_desconto,
                    'data_baixa': data_baixa,
                    'codigo_conta_bancaria': codigo_conta_bancaria,
                    'empresa': EMPRESA,
                    'tipo_conta': tipo_conta,
                    'nome_conta': nome_conta,
                    'anexa_documentos': 'NAO'
                };

                sistema.request.post('/contas_pagar_receber.php', objeto_json, function(retorno) {
                    validar_retorno(retorno, '/contas_pagar_receber.php');
                });

            }

            function retornar(parametro, sair) {
                parametro.preventDefault();
                if (sair == true) {
                    window.location.href = sistema.url('/contas_pagar_receber.php', { 'rota': 'index' });
                }
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
                            <button class="btn btn-primary d-flex align-items-center justify-content-center" onclick="cadastro_contas('');">
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
                                            <option value="VENCIDA">VENCIDA</option>
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
                                        <input type="date" class="form-control" id="data_vencimento_inicio" value="<?php echo $data_inicio; ?>">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Data Vencimento Fim</label>
                                        <input type="date" class="form-control" id="data_vencimento_fim" value="<?php echo $data_fim; ?>">
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
                                                        <th scope="col">Comprovante</th>
                                                        <th scope="col">Baixar</th>
                                                        <th scope="col">Primissória</th>
                                                        <th scope="col">Excluir</th>
                                                        <th scope="col">Editar</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="15" class="text-center">UTILIZE O FILTRO PARA FACILITAR A PESQUISA!</td>
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
                                    <input type="text" class="form-control" id="valor_conta" placeholder="Valor Conta" sistema-mask="moeda" disabled="true">
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
                                    <input type="text" class="form-control" id="valor_pago" sistema-mask="moeda" placeholder="Valor Pago" onblur="validar_juro_desconto();">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Juro/Desconto</label>
                                    <input type="text" class="form-control" id="valor_juro_desconto" sistema-mask="moeda" placeholder="Juro/Desconto" sistema-mask="moeda">
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
                                    <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal" aria-label="Close">Fechar</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-success w-100" onclick="baixar_conta();">Baixar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modal_anexar_documentos" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Anexo Documentos</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" accept="contas_pagar_receber.php" enctype="multipart/form-data">
                                <input type="hidden" name="codigo_conta_pagar_receber" id="codigo_conta_pagar_receber">
                                <input type="hidden" name="empresa_anexo_documento" id="empresa_anexo_documento">
                                <input type="hidden" name="rota" value="anexar_documentos">
                                <input type="hidden" name="codigo_local" id="codigo_local">
                                <input type="hidden" name="local_documento" value="CONTAS_PAGAR_RECEBER">
                                <div class="row">
                                    <div class="col-12">
                                        <label class="text">Nome Conta</label>
                                        <input type="text" class="form-control" name="nome_conta" id="nome_conta_anexo_documentos" disabled="true">
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-12">
                                        <input type="file" name="arquivo" id="arquivo" class="form-control">
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-4">
                                        <input type="submit" class="btn btn-success btn-lg w-100" value="Salvar Dados" />
                                    </div>
                                    <div class="col-4">
                                        <input type="reset" class="btn btn-info btn-lg w-100" value="Limpar Campos" />
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-danger btn-lg w-100" onclick="retornar(event, true);">Voltar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = function() {
                    document.querySelector('#data_baixa').value = DATA_HOJE;
                    pesquisar_contas();
                    pesquisar_conta_bancaria();

                    let params = new URLSearchParams(window.location.search);

                    let comprovante = params.get("comprovante");
                    let retorno = params.get("retorno");

                    if (comprovante !== null && retorno !== null) {

                        if (comprovante === "true" && retorno === "true") {
                            alert("Comprovante enviado com sucesso!");
                        } else {
                            alert("Erro ao enviar comprovante!");
                        }

                    }
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
                let QUANTIDADE_PARCELAS_RECORRENTES = 0;

                const CODIGO_CONTA_PAGAR_RECEBER = "<?php echo $codigo_conta_pagar_receber; ?>";

                function salvar_dados() {
                    if (QUANTIDADE_PARCELAS_RECORRENTES == 0) {
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
                        }, function(retorno) {
                            validar_retorno(retorno, '/contas_pagar_receber.php');
                        });
                    } else {
                        let linhas = document.querySelectorAll('#tabela_contas_recorrentes tr');

                        let contas = [];
                        let executando = true;

                        linhas.forEach(function(linha, index) {
                            let i = index + 1;

                            if (executando == true) {
                                let conta = {};
                                conta.empresa = EMPRESA;

                                conta.nome_conta = document.querySelector('#nome_conta_' + i).value;
                                conta.descricao = document.querySelector('#descricao_' + i).value;
                                conta.valor_conta = document.querySelector('#valor_conta_' + i).value;
                                conta.data_vencimento = document.querySelector('#data_vencimento_' + i).value;
                                conta.tipo_conta = document.querySelector("#tipo_conta_"+i).value;

                                contas.push(conta);

                                if (i == QUANTIDADE_PARCELAS_RECORRENTES) {
                                    executando = false;
                                }
                            }
                        });

                        let json = JSON.stringify(contas);

                        sistema.request.post('/contas_pagar_receber.php', {
                            'rota': 'cadastro_contas_recorrentes',
                            'objeto_json': json
                        }, function(retorno) {
                            validar_retorno(retorno, '/contas_pagar_receber.php');
                        });
                    }
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

                function cadastro_conta_recorrente() {
                    QUANTIDADE_PARCELAS_RECORRENTES++;
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

                    let tabela = document.querySelector("#tabela_contas_recorrentes tbody");
                    let linha = document.createElement('tr');

                    linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('id_parcela_conta_' + QUANTIDADE_PARCELAS_RECORRENTES, QUANTIDADE_PARCELAS_RECORRENTES, ['form-control', 'text-center'], 'text', '', false), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('nome_conta_' + QUANTIDADE_PARCELAS_RECORRENTES, nome_conta, ['form-control'], 'text'), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('descricao_' + QUANTIDADE_PARCELAS_RECORRENTES, descricao, ['form-control'], 'text'), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('tipo_conta_' + QUANTIDADE_PARCELAS_RECORRENTES, tipo_conta, ['form-control', 'text-center'], 'text','', false), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('valor_conta_' + QUANTIDADE_PARCELAS_RECORRENTES, valor_conta, ['form-control'], 'text'), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('data_vencimento_' + QUANTIDADE_PARCELAS_RECORRENTES, data_vencimento, ['form-control'], 'date'), 'append'));

                    tabela.appendChild(linha);
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
                                            <input type="text" class="form-control text-uppercase" id="nome_conta" placeholder="Nome da Conta">
                                        </div>
                                        <div class="col-8 text-center">
                                            <label class="text">Descrição</label>
                                            <input type="text" class="form-control text-uppercase" id="descricao" placeholder="Descrição da Conta">
                                        </div>
                                    </div>
                                    <br />
                                    <div class="row">
                                        <div class="col-3 text-center">
                                            <label class="text">Valor Conta</label>
                                            <input type="text" class="form-control" id="valor_conta" placeholder="Valor Conta" sistema-mask="moeda">
                                        </div>
                                        <div class="col-3 tex-center">
                                            <label class="text">Valor Pago</label>
                                            <input type="text" class="form-control" id="valor_pago" placeholder="Valor pago" sistema-mask="moeda">
                                        </div>
                                        <div class="col-3 text-center">
                                            <label class="text">Valor Juro/Desconto</label>
                                            <input type="text" class="form-control" id="valor_juro_desconto" placeholder="Valor Juro/Desconto" sistema-mask="moeda">
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
                                    <br />
                                    <div class="row">
                                        <div class="col-3 push-9">
                                            <button class="btn btn-primary w-100" onclick="cadastro_conta_recorrente();">CONTA RECORRENTE</button>
                                        </div>
                                    </div>
                                    <br />
                                    <?php include_once 'includes/botao_cadastro.php'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br />
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">Contas Recorrentes</div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="table-responsive">
                                                <table class="table table-nowrap text-nowrap table-hover" id="tabela_contas_recorrentes">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th scope="col">#</th>
                                                            <th scope="col">Nome Conta</th>
                                                            <th scope="col">Descrição</th>
                                                            <th scope="col">Tipo</th>
                                                            <th scope="col">Valor</th>
                                                            <th scope="col">Vencimento</th>
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
                <script>
                    window.onload = function() {
                        document.querySelector('#data_cadastro').value = HOJE;
                        document.querySelector('#data_vencimento').value = DATA_VENCIMENTO;

                        if (CODIGO_CONTA_PAGAR_RECEBER != '') {
                            sistema.request.post('/contas_pagar_receber.php', {
                                'rota': 'pesquisar_conta',
                                'codigo_conta_pagar_receber': CODIGO_CONTA_PAGAR_RECEBER
                            }, function(retorno) {
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
            ?>