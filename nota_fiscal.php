<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/NotaFiscal.php';
require_once 'modelos/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST)) {
        if (array_key_exists('rota', $_POST) == true) {
            if ($_POST['rota'] == 'salvar_dados_nota') {
                $objeto_nota_fiscal = new NotaFiscal();
                $retorno = (bool) $objeto_nota_fiscal->salvar_dados_arquivo($_POST, $_FILES);

                if ($retorno == true) {
                    header('Location: nota_fiscal.php?cadastro_nota=true&retorno=true');
                } else {
                    header('Location: nota_fiscal.php?cadastro_nota=true&retorno=false');
                }
            }
        }
    }
}

router_add('pesquisar_nota', function () {
    $objeto_nota_fiscal = new NotaFiscal();

    $codigo_nota_fiscal = (int) (isset($_REQUEST['codigo_nota_fiscal']) ? (int) $_REQUEST['codigo_nota_fiscal'] : 0);

    echo json_encode((array) ['dados' => (array) $objeto_nota_fiscal->pesquisar((array) ['filtro' => (array) ['where' => [['codigo_nota_fiscal', '=', $codigo_nota_fiscal]]]])], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('pesquisar_nota_fiscal', function () {
    $objeto_nota_fiscal = new NotaFiscal();
    $objeto_cliente_fornecedor = new Usuario();

    $empresa = (int) (isset($_REQUEST['empresa']) ? (int) intval($_REQUEST['empresa'], 10) : 0);
    $data_nota_inicial = (string) (isset($_REQUEST['data_nota_inicial']) ? (string) $_REQUEST['data_nota_inicial'] : '');
    $data_nota_final = (string) (isset($_REQUEST['data_nota_final']) ? (string) $_REQUEST['data_nota_final'] : '');
    $valor_nota = (float) (isset($_REQUEST['valor_nota']) ? (float) doubleval(str_replace(',', '.', $_REQUEST['valor_nota'])) : 0);
    $chave_nota = (string) (isset($_REQUEST['chave_nota']) ? (string) $_REQUEST['chave_nota'] : '');

    $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) [['data_nota','ASC']], 'limite' => (int) 100];
    $filtro_montando = (array) [];
    $notas_a_retornar = (array) [];

    if ($empresa != '') {
        array_push($filtro_montando, (array) ['codigo_empresa', '=', $empresa]);
    }

    if ($data_nota_inicial != '') {
        array_push($filtro_montando, (array) ['data_nota', '>=', model_date($data_nota_inicial, '00:00:00')]);
    }

    if ($data_nota_final != '') {
        array_push($filtro_montando, (array) ['data_nota', '<=', model_date($data_nota_final, '23:59:59')]);
    }

    if ($valor_nota != 0) {
        array_push($filtro_montando, (array) ['valor_nota', '=', (float) $valor_nota]);
    }

    if ($chave_nota != '') {
        array_push($filtro_montando, (array) ['chave_nota', '=', (string) $chave_nota]);
    }

    if (empty($filtro_montando) == false) {
        $filtro['filtro'] = (array) ['and' => (array) $filtro_montando];
    }

    $retorno_nota = $objeto_nota_fiscal->pesquisar_todos($filtro);

    if (empty($retorno_nota) == false) {
        foreach ($retorno_nota as $notas) {
            $retorno_cliente_fornecedor = (array) $objeto_cliente_fornecedor->pesquisar((array) ['filtro' => (array) ['where' => [['codigo_usuario', '=', $notas['codigo_usuario']]]]]);

            $notas['cliente_fornecedor'] = (array) $retorno_cliente_fornecedor;
            array_push($notas_a_retornar, $notas);
        }
    }

    echo json_encode((array) ['dados' => (array) $notas_a_retornar], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('index', function () {
    include_once 'includes/head.php';
    $retorno = (string) (isset($_REQUEST['retorno']) ? (string) $_REQUEST['retorno'] : 'false');
    $cadastro_nota = (string) (isset($_REQUEST['cadastro_nota']) ? (string) $_REQUEST['cadastro_nota'] : 'false');
    ?>
    <script>
        const CODIGO_EMPRESA = "<?php echo $codigo_empresa; ?>";
        const RETORNO = "<?php echo $retorno; ?>";
        const CADASTRO_NOTA = "<?php echo $cadastro_nota; ?>";

        /**
         * Função responsável por abrir o módulo de alterção de dados da nota fiscal
         */
        function cadastrar_notas(codigo_nota) {
            window.location.href = sistema.url('/nota_fiscal.php', {
                'rota': 'cadastro_nota',
                'codigo_nota_fiscal': codigo_nota
            });
        }

        /**
         * Função responsável por pesquisar os clientes e fornecedores do sistema
         */
        function pesquisar_cliente_fornecedor() {
            sistema.request.post('/clientes.php', {
                'rota': 'pesquisar_clientes',
                'empresa': CODIGO_EMPRESA,
                'tipo_usuario': 'CLIENTE_FORNECEDOR',
                'status_usuario': true
            }, function (retorno) {
                let select = document.querySelector("#cliente_fornecedor");
                let cliente_fornecedor = retorno.dados;

                sistema.each(cliente_fornecedor, function (index, cliente) {
                    select.appendChild(sistema.gerar_option(cliente.codigo_usuario, cliente.nome_usuario));
                });
            });
        }

        /**
         * Função responsável por pesquisar as notas fiscais
         */
        function pesquisar_nota() {
            barra_progresso('Carregando notas fiscais...');

            let cliente_fornecedor = document.querySelector('#cliente_fornecedor').value;
            let data_nota_inicial = document.querySelector('#data_nota_inicial').value;
            let data_nota_final = document.querySelector('#data_nota_final').value;
            let valor_nota = document.querySelector('#valor_nota').value;
            let chave_nota = document.querySelector('#chave_nota').value;

            sistema.request.post('/nota_fiscal.php', {
                'rota': 'pesquisar_nota_fiscal',
                'empresa': CODIGO_EMPRESA,
                'cliente_fornecedor': cliente_fornecedor,
                'data_nota_inicial': data_nota_inicial,
                'data_nota_final': data_nota_inicial,
                'valor_nota': valor_nota,
                'chave_nota': chave_nota
            }, function (retorno) {
                let notas_fiscais = retorno.dados;
                let tabela = document.querySelector('#tabela_notas_fiscais tbody');
                let tamanho_retorno = notas_fiscais.length;
                let index = 0;

                tabela = sistema.remover_linha_tabela(tabela);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], 'NENHUMA NOTA ENCONTRADA, COM OS FILTROS PASSADOS!', 'inner', true, 15));
                    tabela.appendChild(linha);

                    Swal.fire({ icon: 'warning', title: 'Nenhuma nota encontrada!' });
                    return;
                }

                function processar_item() {
                    if (index >= tamanho_retorno) {
                        Swal.close();
                        return;
                    }

                    let nota = notas_fiscais[index];
                    let linha = document.createElement('tr');

                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], nota.cliente_fornecedor.nome_usuario, 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.retornar_data(nota.data_nota), 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.number_format(nota.valor_nota), 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_visualizar_tipo_servico_' + nota.codigo_nota_fiscal, nota.tipo_nota, ['btn', 'btn-secondary'], () => { }), 'append'));

                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_baixar_nota_' + nota.codigo_nota_fiscal, 'BAIXAR', ['btn', 'btn-info'], function () {
                        window.open(sistema.url('/anexos/notas_fiscais/') + nota.chave_nota + '.pdf', '_blank');
                    }), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_editar_nota_' + nota.codigo_nota_fiscal, 'EDITAR', ['btn', 'btn-primary'], function editar_conta() {
                        cadastrar_notas(nota.codigo_nota_fiscal);
                    }), 'append'));

                    tabela.appendChild(linha);

                    atualizar_barra_progresso(index, tamanho_retorno, document.querySelector('#barra_progresso'), document.querySelector('#texto_progresso'));

                    index++;
                    setTimeout(processar_item, 1);
                }

                processar_item();

            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Notas Fiscais</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center"
                            onclick="cadastrar_notas('');">
                            Cadastrar Nota
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">Notas Fiscais</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3 text-center">
                                    <label class="text">Cliente/Fornecedor</label>
                                    <select class="form-control select2" id="cliente_fornecedor" id="cliente_fornecedor">
                                        <option value="">Selecione uma Opção</option>
                                    </select>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Data Nota</label>
                                    <input type="date" id="data_nota_inicial" class="form-control">
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Data Nota</label>
                                    <input type="date" id="data_nota_final" class="form-control">
                                </div>
                                <div class="col-1 text-center">
                                    <label class="text">Valor Nota</label>
                                    <input type="text" id="valor_nota" class="form-control" sistema-mask="moeda">
                                </div>
                                <div class="col-4 text-center">
                                    <label class="text">Chave Nota</label>
                                    <input type="text" class="form-control" id="chave_nota">
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-3 push-9">
                                    <button class="btn btn-secondary w-100" onclick="pesquisar_nota();">Pesquisar</button>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_notas_fiscais">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-center">Nome Fornecedor</th>
                                                    <th scope="col" class="text-center">Data Nota</th>
                                                    <th scope="col" class="text-center">Valor</th>
                                                    <th scope="col" class="text-center">Tipo</th>
                                                    <th scope="col" class="text-center">Baixar</th>
                                                    <th scope="col" class="text-center">Editar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="6" class="text-center">UTILIZE O FILTRO PARA FACILITAR A
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
            window.onload = () => {
                if (CADASTRO_NOTA == true) {
                    if (RETORNO == true) {
                        Swal.fire('Sucesso!', 'Operação realizada com sucesso!', 'success');

                        setTimeout(pesquisar_documento(), 5000);
                    } else {
                        Swal.fire('Erro', 'Erro durante a operação!', 'error');
                    }
                }

                pesquisar_cliente_fornecedor();
                pesquisar_nota();
            }
        </script>
        <?php
        include_once 'includes/footer.php';
        exit;
});

router_add('cadastro_nota', function () {
    include_once 'includes/head.php';
    $codigo_nota_fiscal = (int) (isset($_REQUEST['codigo_nota_fiscal']) ? (int) intval($_REQUEST['codigo_nota_fiscal'], 10) : 0);
    ?>
        <script>
            const CODIGO_EMPRESA = "<?php echo $codigo_empresa; ?>";
            let CODIGO_NOTA_FISCAL = "<?php echo $codigo_nota_fiscal; ?>";

            /**
             * Função responsável por retornar ao formulário de pesquisa de notas fiscais
             * @param {*} parametro
             * @param {*} sair
             */
            function retornar(parametro, sair) {
                parametro.preventDefault();
                if (sair == true) {
                    window.location.href = sistema.url('/nota_fiscal.php', {
                        'rota': 'index'
                    });
                }
            }

            /**
             * Função responsável por pesquisar os clientes e fornecedores cadastrados no sistema
             */
            function pesquisar_cliente_fornecedor() {
                sistema.request.post('/clientes.php', {
                    'rota': 'pesquisar_clientes',
                    'empresa': CODIGO_EMPRESA,
                    'tipo_usuario': 'CLIENTE_FORNECEDOR',
                    'status_usuario': true
                }, function (retorno) {
                    let select = document.querySelector("#cliente_fornecedor");
                    let cliente_fornecedor = retorno.dados;

                    sistema.each(cliente_fornecedor, function (index, cliente) {
                        select.appendChild(sistema.gerar_option(cliente.codigo_usuario, cliente.nome_usuario));
                    });
                });
            }

            /**
             * Função responsável por pesquisar as notas fiscais cadastradas no sistema
             */
            function pesquisar_nota() {
                sistema.request.post('/nota_fiscal.php', {
                    'rota': 'pesquisar_nota',
                    'codigo_nota_fiscal': CODIGO_NOTA_FISCAL
                }, function (retorno) {
                    let nota = retorno.dados;

                    document.querySelector('#codigo_nota_fiscal').valor_nota = CODIGO_NOTA_FISCAL;
                    document.querySelector('#cliente_fornecedor').value = nota.cliente_fornecedor.$oid;
                    document.querySelector('#data_nota').value = sistema.retornar_data(nota.data_nota, 'AMERICANO');
                    document.querySelector('#valor_nota').value = nota.valor_nota;
                    document.querySelector('#chave_nota').value = nota.chave_nota;
                    document.querySelector('#tipo_nota').value = nota.tipo_nota;
                });
            }

            /**
             * Função responsável por limpar os campos
             */
            function limpar_campos() {
                document.querySelector('#cliente_fornecedor').value = '';
                document.querySelector('#data_nota_inicial').value = '';
                document.querySelector('#data_nota_final').value = '';
                document.querySelector('#valor_nota').value = '';
                document.querySelector('#chave_nota').value = '';
                CODIGO_NOTA_FISCAL = '';
            }

            /**
             * Função responsável por retornar ao módulo de pesquisa de notas fiscais
             */
            function voltar() {
                window.location.href = sistema.url('/nota_fiscal.php', {
                    'rota': 'index'
                });
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <div class="card-title">Notas Fiscais</div>
                            </div>
                            <div class="card-body">
                                <form method="POST" accept="nota_fiscal.php" enctype="multipart/form-data">
                                    <input type="hidden" name="rota" value="salvar_dados_nota">
                                    <input type="hidden" name="codigo_nota_fiscal"
                                        value="<?php echo $codigo_nota_fiscal; ?>" id="codigo_nota_fiscal">
                                    <input type="hidden" name="empresa" value="<?php echo $codigo_empresa; ?>">
                                    <div class="row">
                                        <div class="col-3 text-center">
                                            <label class="text">Cliente/Fornecedor</label>
                                            <select class="form-control select2" name="cliente_fornecedor"
                                                id="cliente_fornecedor">
                                                <option value="">Selecione uma Opção</option>
                                            </select>
                                        </div>
                                        <div class="col-2 text-center">
                                            <label class="text">Data Nota</label>
                                            <input type="date" name="data_nota"
                                                value="<?php echo $data->format('Y-m-d'); ?>" class="form-control"
                                                id="data_nota">
                                        </div>
                                        <div class="col-1 text-center">
                                            <label class="text">Valor Nota</label>
                                            <input type="text" name="valor_nota" class="form-control" sistema-mask="moeda"
                                                id="valor_nota">
                                        </div>
                                        <div class="col-6 text-center">
                                            <label class="text">Chave Nota</label>
                                            <input type="text" class="form-control" name="chave_nota" id="chave_nota">
                                        </div>
                                    </div>
                                    <br />
                                    <div class="row">
                                        <div class="col-2">
                                            <label class="text">Tipo Nota</label>
                                            <select class="form-control" name="tipo_nota" id="tipo_nota">
                                                <option value="">Selecione ma Opção</option>
                                                <option value="ENTRADA">ENTRADA</option>
                                                <option value="ENTRADA_SERVICOS">ENTRADA SERVIÇOS</option>
                                                <option value="SAIDA">SAÍDA</option>
                                                <option value="SAIDA_SERVICOS">SAÍDA SERVIÇOS</option>
                                                <option value="OUTROS">OUTRAS NOTAS</option>
                                            </select>
                                        </div>
                                        <div class="col-10 text-center">
                                            <label class="text">Arquivo</label>
                                            <input type="file" name="arquivo_nota" class="form-control">
                                        </div>
                                    </div>
                                    <br />
                                    <div class="row">
                                        <div class="col-4">
                                            <input type="submit" class="btn btn-success btn-lg w-100"
                                                value="Salvar Dados" />
                                        </div>
                                        <div class="col-4">
                                            <input type="reset" class="btn btn-info btn-lg w-100" value="Limpar Campos" />
                                        </div>
                                        <div class="col-4">
                                            <button class="btn btn-danger btn-lg w-100"
                                                onclick="retornar(event, true);">Voltar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = () => {
                    pesquisar_cliente_fornecedor();

                    if (CODIGO_NOTA_FISCAL != 0) {
                        pesquisar_nota();
                    }
                }
            </script>
            <?php
            include_once 'includes/footer.php';
            exit;
});
?>