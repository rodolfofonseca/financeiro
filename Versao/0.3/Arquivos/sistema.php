<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Sistema.php';
require_once 'modelos/ContasContabeis.php';

router_add('excluir_conta_contabil', function () {
    $objeto_conta_contabil = new ContasContabeis();

    echo json_encode((array) ['status' => (bool) $objeto_conta_contabil->excluir_conta($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

/** 
 * Rota resposável por salvar os dados da conta contábil
 */
router_add('salvar_dados_conta_contabil', function () {
    $objeto_conta_contabil = new ContasContabeis();
    echo json_encode((array) ['status' => (bool) $objeto_conta_contabil->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

/**
 * Rota responsável por pesquisar os tipos ed contas contábeis e adicionar no select
 */
router_add('pesquisar_informacoes_conta', function () {
    $objeto_conta_contabil = new ContasContabeis();

    echo json_encode($objeto_conta_contabil->pesquisar_informacoes_conta($_REQUEST), JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * Rota responsável por pesquisar a conta contábil
 */
router_add('pesquisar_conta_contabil', function () {
    $objeto_conta_contabil = new ContasContabeis();

    $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa'] : '');
    $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['conta_contabil' => (bool) true], 'limite' => (int) 0];
    $filtro_montando = (array) [];
    $retorno_pesquisa = (array) [];

    if ($empresa != '') {
        array_push($filtro_montando, (array) ['empresa', '===', model_id($empresa)]);

        $filtro['filtro'] = (array) ['and' => (array) $filtro_montando];
        $retorno_pesquisa = (array) $objeto_conta_contabil->pesquisar_todos($filtro);
    }

    echo json_encode((array) ['dados' => $retorno_pesquisa], JSON_UNESCAPED_UNICODE);
});

/** 
 * Rota responsável por pesquisar as informações do sistema de acordo com a empresa
 */
router_add('pesquisar_sistema', function () {
    $objeto_sistema = new Sistema();

    $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa'] : '');
    $retorno = (array) [];

    if ($empresa != '') {
        $retorno = (array) $objeto_sistema->pesquisar((array) ['filtro' => (array) ['empresa', '===', model_id($empresa)]]);
    }

    echo json_encode((array) ['dados' => (array) $retorno], JSON_UNESCAPED_UNICODE);
});

/** 
 * Rota resposável por salvar os dadso do sistema, conforme a configuração realizada pelo usuário administrador
 */
router_add('salvar_dados', function () {
    $objeto_sistema = new Sistema();

    echo json_encode((array) ['status' => (bool) $objeto_sistema->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * Rota index, onde o usuário pode configurar o sistema da forma como deseja
 */
router_add('index', function () {
    require_once 'includes/head.php';
    ?>
    <script>
        const EMPRESA = "<?php echo $codigo_empresa; ?>";
        let SISTEMA = '';
        const DATA_HOJE = "<?php echo DATA_HOJE; ?>";
        const MODULO_CONTABIL = "<?php echo $_SESSION['modulo_contabil'] ? 'true' : 'false'; ?>";

        /** 
         * Função responsável por pesquisar as informações  do sistema e adicioanr aos campos correspondenttes
         */
        function pesquisar_sistema() {
            sistema.request.post('/sistema.php', {
                'rota': 'pesquisar_sistema',
                'empresa': EMPRESA
            }, function (retorno) {
                let sistema_usuario = retorno.dados;
                let modulo_contabil = 0;
                let pedidos = 0;
                let cloudinary = 0;
                let google_agenda = 0;
                let conta_apuracao_resultado = "";
                let conta_capital_social = "";
                let conta_custo_mercadorias_vendidas = "";
                let conta_custo_servicos_prestados = "";
                let conta_lucros_apropriar = "";
                let conta_prejuizos_acumulados = "";
                let conta_servicos_a_prazo = "";
                let conta_servicos_a_vista = "";
                let conta_vendas_a_prazo = "";
                let conta_vendas_a_vista = "";
                let endereco_json_google = "";

                if (sistema_usuario.hasOwnProperty('modulo_contabil') == true) {
                    if (sistema_usuario.modulo_contabil == true) {
                        modulo_contabil = 1;
                    }
                }

                if (sistema_usuario.hasOwnProperty('pedidos') == true) {
                    if (sistema_usuario.pedidos == true) {
                        pedidos = 1;
                    }
                }

                if (sistema_usuario.hasOwnProperty('cloudinary') == true) {
                    if (sistema_usuario.cloudinary == true) {
                        cloudinary = 1;
                    }
                }

                if (sistema_usuario.hasOwnProperty('google_agenda') == true) {
                    if (sistema_usuario.google_agenda == true) {
                        google_agenda = 1;
                    }
                }

                if (sistema_usuario.hasOwnProperty('conta_apuracao_resultado') == true) {
                    conta_apuracao_resultado = sistema_usuario.conta_apuracao_resultado;
                }

                if (sistema_usuario.hasOwnProperty('conta_capital_social') == true) {
                    conta_capital_social = sistema_usuario.conta_capital_social;
                }

                if (sistema_usuario.hasOwnProperty('conta_custo_mercadorias_vendidas') == true) {
                    conta_custo_mercadorias_vendidas = sistema_usuario.conta_custo_mercadorias_vendidas;
                }

                if (sistema_usuario.hasOwnProperty('conta_custo_servicos_prestados') == true) {
                    conta_custo_servicos_prestados = sistema_usuario.conta_custo_servicos_prestados;
                }

                if (sistema_usuario.hasOwnProperty('conta_lucros_apropriar') == true) {
                    conta_lucros_apropriar = sistema_usuario.conta_lucros_apropriar;
                }

                if (sistema_usuario.hasOwnProperty('conta_prejuizos_acumulados') == true) {
                    conta_prejuizos_acumulados = sistema_usuario.conta_prejuizos_acumulados;
                }

                if (sistema_usuario.hasOwnProperty('conta_servicos_a_prazo') == true) {
                    conta_servicos_a_prazo = sistema_usuario.conta_servicos_a_prazo;
                }

                if (sistema_usuario.hasOwnProperty('conta_servicos_a_vista') == true) {
                    conta_servicos_a_vista = sistema_usuario.conta_servicos_a_vista;
                }

                if (sistema_usuario.hasOwnProperty('conta_vendas_a_prazo') == true) {
                    conta_vendas_a_prazo = sistema_usuario.conta_vendas_a_prazo;
                }

                if (sistema_usuario.hasOwnProperty('conta_vendas_a_vista') == true) {
                    conta_vendas_a_vista = sistema_usuario.conta_vendas_a_vista;
                }

                document.querySelector('#versao_sistema').value = sistema_usuario.versao_sistema;
                document.querySelector('#anexar_documentos').value = sistema_usuario.anexa_documentos;
                document.querySelector('#pedidos').value = pedidos;
                document.querySelector('#modulo_contabil').value = modulo_contabil;
                document.querySelector('#cloudinary').value = cloudinary;
                document.querySelector('#google_agenda').value = google_agenda;
                document.querySelector('#conta_apuracao_resultado').value = conta_apuracao_resultado;
                document.querySelector('#conta_capital_social').value = conta_capital_social;
                document.querySelector('#conta_custo_mercadorias_vendidas').value = conta_custo_mercadorias_vendidas;
                document.querySelector('#conta_custo_servicos_prestados').value = conta_custo_servicos_prestados;
                document.querySelector('#conta_lucros_apropriar').value = conta_lucros_apropriar;
                document.querySelector('#conta_prejuizos_acumulados').value = conta_prejuizos_acumulados;
                document.querySelector('#conta_servicos_a_prazo').value = conta_servicos_a_prazo;
                document.querySelector('#conta_servicos_a_vista').value = conta_servicos_a_vista;
                document.querySelector('#conta_vendas_a_prazo').value = conta_vendas_a_prazo;
                document.querySelector('#conta_vendas_a_vista').value = conta_vendas_a_vista;

                SISTEMA = sistema_usuario._id;
            });
        }

        /** 
         * Função responsável por salvar as informações básicas do sistema
         */
        function salvar_dados_comprovantes() {
            let versao_sistema = document.querySelector('#versao_sistema').value;
            let anexar_documentos = document.querySelector('#anexar_documentos').value;
            let pedidos = document.querySelector('#pedidos').value;
            let modulo_contabil = document.querySelector('#modulo_contabil').value;
            let cloudinary = document.querySelector('#cloudinary').value;
            let google_agenda = document.querySelector('#google_agenda').value;
            let conta_capital_social = document.querySelector('#conta_capital_social').value;
            let conta_lucros_apropriar = document.querySelector('#conta_lucros_apropriar').value;
            let conta_prejuizos_acumulados = document.querySelector('#conta_prejuizos_acumulados').value;
            let conta_vendas_a_vista = document.querySelector('#conta_vendas_a_vista').value;
            let conta_vendas_a_prazo = document.querySelector('#conta_vendas_a_prazo').value;
            let conta_servicos_a_vista = document.querySelector('#conta_servicos_a_vista').value;
            let conta_servicos_a_prazo = document.querySelector('#conta_servicos_a_prazo').value;
            let conta_custo_mercadorias_vendidas = document.querySelector('#conta_custo_mercadorias_vendidas').value;
            let conta_custo_servicos_prestados = document.querySelector('#conta_custo_servicos_prestados').value;
            let conta_apuracao_resultado = document.querySelector('#conta_apuracao_resultado').value;

            let dados = {
                'rota': 'salvar_dados',
                'empresa': EMPRESA,
                'versao_sistema': versao_sistema,
                'anexa_documentos': anexar_documentos,
                'codigo_sistema': SISTEMA,
                'modulo_contabil': modulo_contabil,
                'pedidos': pedidos,
                'cloudinary': cloudinary,
                'google_agenda': google_agenda,
                'conta_capital_social': conta_capital_social,
                'conta_lucros_apropriar': conta_lucros_apropriar,
                'conta_prejuizos_acumulados': conta_prejuizos_acumulados,
                'conta_vendas_a_vista': conta_vendas_a_vista,
                'conta_vendas_a_prazo': conta_vendas_a_prazo,
                'conta_servicos_a_vista': conta_servicos_a_vista,
                'conta_servicos_a_prazo': conta_servicos_a_prazo,
                'conta_custo_mercadorias_vendidas': conta_custo_mercadorias_vendidas,
                'conta_custo_servicos_prestados': conta_custo_servicos_prestados,
                'conta_apuracao_resultado': conta_apuracao_resultado
            };

            sistema.request.post('/sistema.php', dados, function (retorno) {
                validar_retorno(retorno, '/sistema.php');
            });
        }

        /** 
         * Função responsável por pesquisar as informações das contas e adicionar no select correspondente
         */
        function pesquisar_informacoes_conta() {
            let local_conta_id = document.querySelector('#local_conta_id').value;

            sistema.request.post('/sistema.php', {
                'rota': 'pesquisar_informacoes_conta',
                'empresa': EMPRESA,
                'local_conta_id': local_conta_id
            }, function (retorno) {
                if (retorno.status == true) {
                    let codigo_local = document.querySelector('#codigo_local');

                    codigo_local = sistema.remover_option(codigo_local);

                    sistema.each(retorno.dados, function (index, objeto) {
                        let option = sistema.gerar_option(objeto.codigo.$oid, objeto.descricao);

                        codigo_local.appendChild(option);
                    });
                } else {
                    if (local_conta_id != 'PATRIMONIO_LIQUIDO' || local_conta_id != 'SERVICOS' || local_conta_id != 'CUSTOS' || local_conta_id != 'RESULTADO') {
                        this.Swal.fire({ title: "ERRO!", text: "Erro durante o processo de pesquisa. Não foi possível retornar nenhum resultado!", icon: "error" });
                    }
                }
            }, false);
        }

        /** 
         * Função resposável por limpar os campos do formulário de cadastro de contas contáveis
         */
        function limpar_campos() {
            location.reload();
        }

        /** 
         * Função responsável por salvar no banco de dados as contas contáveis
         */
        function salvar_dados() {
            let local_conta_id = document.querySelector('#local_conta_id').value;
            let codigo_local = document.querySelector('#codigo_local').value;
            let grau_conta = document.querySelector('#grau_conta').value;
            let tipo_conta = document.querySelector('#tipo_conta').value;
            let local_conta = document.querySelector('#local_conta').value;
            let nome_conta = document.querySelector('#nome_conta').value;
            let conta_contabil = document.querySelector('#conta_contabil').value;
            let conta_tipo = document.querySelector('#conta_tipo').value;
            let data_cadastro = document.querySelector('#data_cadastro').value;

            if (conta_tipo == false || conta_tipo == 'false' || conta_tipo == 0) {
                codigo_local = EMPRESA;
            }

            if (conta_tipo == true || conta_tipo == 'true' || conta_contabil == 1) {
                if (codigo_local == 0) {
                    alerta_campo_vazio('LOCAL CONTA');
                    return;
                }
            }

            if (grau_conta == 0) {
                alerta_campo_vazio('GRAU DA CONTA');
                return;
            }

            if (local_conta == '') {
                alerta_campo_vazio('LOCAL CONTA');
                return;
            }

            if (nome_conta == '') {
                alerta_campo_vazio('NOME DA CONTA');
                return;
            }

            if (conta_contabil == '') {
                alerta_campo_vazio('CONTA CONTÁBIL');
                return;
            }

            let dados = {
                'rota': 'salvar_dados_conta_contabil',
                'empresa': EMPRESA,
                'codigo_local': codigo_local,
                'local_conta_id': local_conta_id,
                'grau_conta': grau_conta,
                'conta_contabil': conta_contabil,
                'local_conta': local_conta,
                'tipo_conta': tipo_conta,
                'nome_conta': nome_conta,
                'data_cadastro': data_cadastro,
                'conta_tipo': conta_tipo
            }

            sistema.request.post('/sistema.php', dados, function (retorno) {
                validar_retorno(retorno, '/sistema.php');
            });
        }

        /** 
         * Função responsável por pesquisar as contas contábeis cadastradas no sistema.
         */
        function pesquisar_contas_contabeis() {
            sistema.request.post('/sistema.php', {
                'rota': 'pesquisar_conta_contabil',
                'empresa': EMPRESA
            }, function (retorno) {
                let contas_contabeis = retorno.dados;
                let tamanho_contas = contas_contabeis.length;
                let tabela = document.querySelector('#tabela_conta_contabil tbody');

                tabela = sistema.remover_linha_tabela(tabela);

                if (tamanho_contas == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center'], 'NENHUMA CONTA ENCONTRADA! CADASTRE CONSTAS E ELAS APARECERAM AQUI!', 'inner', true, 10));
                    tabela.appendChild(linha);
                } else {
                    sistema.each(contas_contabeis, function (index, conta) {
                        let linha = document.createElement('tr');

                        linha.appendChild(sistema.gerar_td(['text-center'], conta.nome_conta, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], validar_grau_conta(conta.grau_conta, conta.conta_contabil), 'inner'));

                        if (conta.grau_conta == 1) {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_grau_' + conta._id.$oid, conta.grau_conta, ['btn', 'btn-soft-primary'], () => { }), 'append'));
                        } else if (conta.grau_conta == 2) {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_grau_' + conta._id.$oid, conta.grau_conta, ['btn', 'btn-soft-secondary'], () => { }), 'append'));
                        } else if (conta.grau_conta == 3) {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_grau_' + conta._id.$oid, conta.grau_conta, ['btn', 'btn-soft-success'], () => { }), 'append'));
                        } else if (conta.grau_conta == 4) {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_grau_' + conta._id.$oid, conta.grau_conta, ['btn', 'btn-soft-info'], () => { }), 'append'));
                        } else if (conta.grau_conta == 5) {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_grau_' + conta._id.$oid, conta.grau_conta, ['btn', 'btn-soft-light'], () => { }), 'append'));
                        } else if (conta.grau_conta == 6) {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_grau_' + conta._id.$oid, conta.grau_conta, ['btn', 'btn-soft-dark'], () => { }), 'append'));
                        }

                        if (conta.local_conta == 'ATIVO') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('conta_local_conta_' + conta._id.$oid, conta.local_conta, ['btn', 'btn-white'], () => { }), 'append'));

                            if (conta.tipo_conta == false) {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta._id.$oid, 'DÉBITO', ['btn', 'btn-soft-success'], () => { }), 'append'));
                            } else {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta._id.$oid, 'CRÉDITO', ['btn', 'btn-soft-danger'], () => { }), 'append'));
                            }
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('conta_local_conta_' + conta._id.$oid, conta.local_conta, ['btn', 'btn-outline-dark'], () => { }), 'append'));

                            if (conta.tipo_conta == false) {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta._id.$oid, 'DÉBITO', ['btn', 'btn-soft-danger'], () => { }), 'append'));
                            } else {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta._id.$oid, 'CRÉDITO', ['btn', 'btn-soft-success'], () => { }), 'append'));
                            }
                        }

                        if (conta.conta_tipo == false) {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_conta_tipo_' + conta._id.$oid, 'SINTÉTICA', ['btn', 'btn-white'], () => { }), 'append'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_conta_tipo_' + conta._id.$oid, 'ANALÍTICA', ['btn', 'btn-dark'], () => { }), 'append'));
                        }

                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(conta.data_cadastro), 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_excluir_conta_' + conta._id.$oid, 'EXCLUIR CONTA', ['btn', 'btn-danger'], function excluir_conta_botao() {
                            excluir_conta(conta._id.$oid);
                        }), 'append'));

                        tabela.appendChild(linha);
                    });
                }
            });
        }

        function excluir_conta(codigo_conta) {
            Swal.fire({
                title: "Quer mesmo deletar?",
                text: "Essa operação é irreversível!\n\rExcluir a conta não exclui os lançamentos feitos!\n\rPara isso é necessário acessar o menu de movimentações!!!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, Deletar agora!"
            }).then((result) => {
                if (result.isConfirmed) {
                    sistema.request.post('/sistema.php', {
                        'rota': 'excluir_conta_contabil',
                        'codigo_conta_contabil': codigo_conta
                    }, function (retorno) {
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

                        pesquisar_contas_contabeis();
                    });
                }

            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Configurações do sistema
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-1 text-center">
                                    <label class="text">Versão do sistema</label>
                                    <input type="text" class="form-control text-center" id="versao_sistema" disabled="true">
                                </div>
                                <div class="col-1 text-center">
                                    <label class="text">Comprovantes</label>
                                    <select class="form-control" id="anexar_documentos">
                                        <option value="NAO">NÃO</option>
                                        <option value="SIM">SIM</option>
                                    </select>
                                </div>
                                <div class="col-1 text-center" style="display: none;">
                                    <label class="text">Pedidos</label>
                                    <select class="form-control" id="pedidos" disabled="true">
                                        <option value="0">NÃO</option>
                                        <option value="1">SIM</option>
                                    </select>
                                </div>
                                <div class="col-1 text-center" style="display: none;">
                                    <label class="text">Módulo Contábil</label>
                                    <select class="form-control" id="modulo_contabil" disabled="true">
                                        <option value="0">NÃO</option>
                                        <option value="1">SIM</option>
                                    </select>
                                </div>
                                <div class="col-1 text-center" style="display:none;">
                                    <label class="text">Cloudinary</label>
                                    <select class="form-control" id="cloudinary" disabled="true">
                                        <option value="0">NÃO</option>
                                        <option value="1">SIM</option>
                                    </select>
                                </div>
                                <div class="col-1 text-center" style="display:none;">
                                    <label class="text">Google Agenda</label>
                                    <select class="form-control" id="google_agenda" disabled="true">
                                        <option value="0">NÃO</option>
                                        <option value="1">SIM</option>
                                    </select>
                                </div>
                            </div>
                            <br />
                            <div class="row" style="display: none;">
                                <div class="col-3 text-center">
                                    <label class="text">Capital Social</label>
                                    <input type="text" class="form-control" id="conta_capital_social"
                                        sistema-maks="conta-contabil">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Lucros a apropriar</label>
                                    <input type="text" class="form-control" id="conta_lucros_apropriar"
                                        sistema-mask="conta-contabil">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Projuízos Acumulados</label>
                                    <input type="text" class="form-control" id="conta_prejuizos_acumulados"
                                        sistema-mask="conta-contabil">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Vendas a Vista</label>
                                    <input type="text" class="form-control" id="conta_vendas_a_vista"
                                        sistema-mask="conta-contabil">
                                </div>
                            </div>
                            <br />
                            <div class="row" style="display: none;">
                                <div class="col-3 text-center">
                                    <label class="text">Vendas a Prazo</label>
                                    <input type="text" class="form-control" id="conta_vendas_a_prazo"
                                        sistema-mask="conta-contabil">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Serviços Prestados a Vidas</label>
                                    <input type="text" class="form-control" id="conta_servicos_a_vista"
                                        sistema-mask="conta-contabil">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Serviços Prestados a Prazo</label>
                                    <input type="text" class="form-control" id="conta_servicos_a_prazo"
                                        sistema-mask="conta-contabil">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Custo das Mercadorias Vendidas</label>
                                    <input type="text" class="form-control" id="conta_custo_mercadorias_vendidas"
                                        sistema-mask="conta-contabil">
                                </div>
                            </div>
                            <br />
                            <div class="row" style="display: none;">
                                <div class="col-3 text-center">
                                    <label class="text">Custo dos Serviços Prestados</label>
                                    <input type="text" class="form-control" id="conta_custo_servicos_prestados"
                                        sistema-mask="conta-contabil">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Apuração do Resultado</label>
                                    <input type="text" class="form-control" id="conta_apuracao_resultado"
                                        sistema-mask="conta-contabil">
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-3 push-9">
                                    <button class="btn btn-success w-100 btn-lg" id="btn_salvar_sados"
                                        onclick="salvar_dados_comprovantes();">SALVAR DADOS</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            if ($_SESSION['modulo_contabil'] == true) {
                ?>
                <br />
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <div class="card-title">
                                    Configurações Contábeis
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <p>
                                            O nosso sistema, trabalha com o plano de constas conforme configurado pelo usuário
                                            do sistema.
                                        </p>
                                        <p>
                                            Todas as contas tem que ser previamente configurada.
                                        </p>
                                        <p>
                                            Lembrando que configurações erradas levam a resultados errados
                                        </p>
                                        <p>
                                            Portanto é de responsabilidade do cliente fazer as detidas configurações.
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-3 push-9 tex-center">
                                        <button class="btn btn-info text-uppercase w-100 btn-lg" data-bs-toggle="modal"
                                            data-bs-target="#modal_cadastro_conta_contabil">Cadastrar Conta Contábil</button>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="table-responsive">
                                            <table class="table table-nowrap text-nowrap table-hover"
                                                id="tabela_conta_contabil">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th scope="col">NOME</th>
                                                        <th scope="col">CONTA CONTÁBIL</th>
                                                        <th scope="col">GRAU</th>
                                                        <th scope="col">CONTA DO</th>
                                                        <th scope="col">NATUREZA</th>
                                                        <th scope="col">TIPO</th>
                                                        <th scope="col">DATA CADASTRO</th>
                                                        <th scope="col">AÇÃO</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="10" class="text-center">NENHUMA CONTA ENCONTRADA! CADASTRE
                                                            CONSTAS E ELAS APARECERAM AQUI!</td>
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
                <?php
            }
            ?>
        </div>
        <div class="modal fade" id="modal_cadastro_conta_contabil" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Cadastro Conta Contábil</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-3 text-center">
                                <label class="text">Local Conta</label>
                                <select class="form-control" id="local_conta_id" onchange="pesquisar_informacoes_conta();">
                                    <option value="">Selecione Uma Opção</option>
                                    <option value="ATIVO_CIRCULANTE_CAIXA">ATIVO CIRCULANTE CAIXA</option>
                                    <option value="ATIVO_CIRCULANTE_CLIENTE">ATIVO CIRCULANTE CLIENTE</option>
                                    <option value="ATIVO_CIRCULANTE_ESTOQUE">ATIVO CIRCULANTE ESTOQUE</option>
                                    <option value="ATIVO_NAO_CIRCULANTE_CLIENTE">ATIVO NÃO CIRCULANTE CLIENTE</option>
                                    <option value="PASSIVO_CIRCULANTE_FORNECEDOR">PASSIVO CIRCULANTE FORNECEDOR</option>
                                    <option value="PASSIVO_CIRCULANTE_CONTAS">PASSIVO CIRCULANTE CONTAS</option>
                                    <option value="PASSIVO_NAO_CIRCULANTE_FORNECEDOR">PASSIVO NÃO CIRCULANTE FORNECEDOR
                                    </option>
                                    <option value="PATRIMONIO_LIQUIDO">PATRIMONIO_LIQUIDO</option>
                                    <option value="SERVICOS">SERVIÇOS</option>
                                    <option value="CUSTOS">CUSTOS</option>
                                    <option value="DESPESAS">DESPESAS</option>
                                    <option value="RESULTADO">APURAÇÃO DO RESULTADO</option>
                                </select>
                            </div>
                            <div class="col-3 text-center">
                                <label class="text">Local Conta</label>
                                <select class="form-control" id="codigo_local">
                                    <option value="">Selecione Uma Opção</option>
                                </select>
                            </div>
                            <div class="col-3 text-center">
                                <label class="text">Grau conta</label>
                                <select class="form-control" id="grau_conta">
                                    <option value="0">Selecione Uma Opção</option>
                                    <option value="1">GRAU 1</option>
                                    <option value="2">GRAU 2</option>
                                    <option value="3">GRAU 3</option>
                                    <option value="4">GRAU 4</option>
                                    <option value="5">GRAU 5</option>
                                    <option value="6">GRAU 6</option>
                                </select>
                            </div>
                            <div class="col-3 text-center">
                                <label class="text">Tipo De Conta</label>
                                <select class="form-control" id="tipo_conta">
                                    <option value="0">Selecione Uma Opção</option>
                                    <option value="1">CRÉDITO</option>
                                    <option value="0">DÉBITO</option>
                                </select>
                            </div>
                        </div>
                        <br />
                        <div class="row">
                            <div class="col-3 text-center">
                                <label class="text">Loca Conta</label>
                                <select class="form-control" id="local_conta">
                                    <option value="">Selecione Uma Opção</option>
                                    <option value="ATIVO">ATIVO</option>
                                    <option value="PASSIVO">PASSIVO</option>
                                    <option value="RESULTADO">RESULTADO</option>
                                </select>
                            </div>
                            <div class="col-5 text-center">
                                <label class="text">Nome da Conta</label>
                                <input type="text" id="nome_conta" class="form-control text-uppercase">
                            </div>
                            <div class="col-4 text-center">
                                <label class="text">Conta Contábil</label>
                                <input type="text" id="conta_contabil" class="form-control" sistema-mask="conta-contabil">
                            </div>
                        </div>
                        <br />
                        <div class="row">
                            <div class="col-3 text-center">
                                <label class="text">Tipo de Conta</label>
                                <select class="form-control" id="conta_tipo">
                                    <option value="0">Selecione Uma Opção</option>
                                    <option value="0">SINTÉTICA</option>
                                    <option value="1">ANALÍTICA</option>
                                </select>
                            </div>
                            <div class="col-3 text-center">
                                <label class="text">Data Cadastro</label>
                                <input type="date" id="data_cadastro" class="form-control" value="<?php echo DATA_HOJE; ?>">
                            </div>
                        </div>
                        <br />
                        <?php
                        include_once 'includes/botao_cadastro_modal.php';
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = () => {
                pesquisar_sistema();

                if (MODULO_CONTABIL == 'true') {
                    pesquisar_contas_contabeis();
                }
            }
        </script>
        <?php
        require_once 'includes/footer.php';
        exit;
});
?>