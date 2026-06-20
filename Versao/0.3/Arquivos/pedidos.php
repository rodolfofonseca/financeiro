<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Pedidos.php';

router_add('salvar_pedidos_entrada', function () {
    $objeto_pedidos = new Pedidos();

    echo json_encode(['status' => (bool) $objeto_pedidos->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * Rota responsável por pesquisar os pedidos de acordo com os filtros selecionados. Ela recebe os parâmetros de empresa, tipo, data de cadastro, data de movimentação e status do pedido, e constrói um filtro para a pesquisa. Em seguida, ela chama o método 'pesquisar_todos' do objeto Pedidos, passando o filtro construído, e retorna os resultados em formato JSON. Essa rota é utilizada para atualizar a tabela de pedidos com os resultados filtrados quando o usuário realiza uma pesquisa.
 */
router_add('pesquisar_pedidos', function () {
    $objeto_pedidos = new Pedidos();

    $empresa = (string) (isset($_REQUEST['empresa']) ? $_REQUEST['empresa'] : '');
    $tipo = (bool) (isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : false);
    $data_cadastro = (string) (isset($_REQUEST['data_cadastro']) ? $_REQUEST['data_cadastro'] : '');
    $data_movimentacao = (string) (isset($_REQUEST['data_movimentacao']) ? $_REQUEST['data_movimentacao'] : '');
    $status_pedido = (string) (isset($_REQUEST['status_pedido']) ? $_REQUEST['status_pedido'] : '');

    $filtro = (array) ['filtro' => (array) [], 'orndenacao' => (array) ['data_cadastro' => (bool) false], 'limite' => (int) 0];
    $filtro_montando = (array) [];

    if ($empresa != '') {
        array_push($filtro_montando, (array) ['empresa', '===', model_id($empresa)]);
    }

    if ($data_cadastro != '') {
        array_push($filtro_montando, (array) ['data_cadastro', '>=', model_date($data_cadastro, '00:00:00')]);
        array_push($filtro_montando, (array) ['data_cadastro', '<=', model_date($data_cadastro, '23:59:59')]);
    }

    if ($data_movimentacao != '') {
        array_push($filtro_montando, (array) ['data_movimentacao', '>=', model_date($data_movimentacao, '00:00:00')]);
        array_push($filtro_montando, (array) ['data_movimentacao', '<=', model_date($data_movimentacao, '23:59:59')]);
    }

    if ($status_pedido != '') {
        array_push($filtro_montando, (array) ['status_pedido', '===', $status_pedido]);
    }

    array_push($filtro_montando, (array) ['tipo', '===', (bool) $tipo]);

    $filtro['filtro'] = (array) ['and' => $filtro_montando];

    echo json_encode((array) ['dados' => $objeto_pedidos->pesquisar_todos($filtro)], JSON_UNESCAPED_UNICODE);
});

/** 
 * Rota para a página de pedidos, mas redireciona para o dashboard, pois a funcionalidade de index não existe.
 */
router_add('index', function () {
    header('Location: dashboard.php');
});

/** 
 * Rota responsável por exibir os pedidos de entrada.
 */
router_add('entrada', function () {
    include_once 'includes/head.php';
    ?>
    <script>
        const EMPRESA = "<?php echo $codigo_empresa; ?>";

        /** 
         * Função para pesquisar os fornecedores disponíveis. Ela faz uma requisição POST para a rota 'pesquisar_clientes' no arquivo 'clientes.php', passando o código da empresa e o tipo de usuário como 'FORNECEDOR'. Ao receber a resposta, a função itera sobre a lista de fornecedores retornada e adiciona cada um como uma opção em um elemento select com o id 'fornecedor'. Essa função é chamada quando a página é carregada para garantir que a lista de fornecedores esteja atualizada e disponível para seleção no formulário de cadastro de produtos.
         */
        function pesquisar_fornecedores() {
            sistema.request.post('/clientes.php', {
                'rota': 'pesquisar_clientes',
                'empresa': EMPRESA,
                'tipo_usuario': 'FORNECEDOR'
            }, function (retorno) {
                let select_fornecedores = document.querySelector('#fornecedor');
                let fornecedores = retorno.dados;

                sistema.each(fornecedores, function (fornecedor) {
                    select_fornecedores.appendChild(sistema.gerar_option(fornecedor._id.$oid, fornecedor.nome_usuario));
                });

            });
        }

        /** 
         * Função para pesquisar os pedidos de entrada com base nos filtros selecionados. Ela coleta os valores dos filtros de fornecedor, tipo, data de cadastro, data de movimentação e status do pedido, e faz uma requisição POST para a rota 'pesquisar_pedidos' no arquivo 'pedidos.php', passando esses valores como parâmetros. Ao receber a resposta, a função itera sobre a lista de pedidos retornada e constrói as linhas da tabela de pedidos, preenchendo as informações correspondentes em cada coluna. Essa função é chamada quando o usuário clica no botão "Pesquisar" para atualizar a tabela de pedidos com os resultados filtrados.
         */
        function pesquisar_pedidos() {
            let fornecedor = document.querySelector('#fornecedor').value;
            let status_pedido = document.querySelector('#status_pedido').value;
            let data_cadastro = document.querySelector('#data_cadastro').value;
            let data_movimentacao = document.querySelector('#data_movimentacao').value;
            let tipo = true;

            sistema.request.post('/pedidos.php', {
                'rota': 'pesquisar_pedidos',
                'fornecedor': fornecedor,
                'status_pedido': status_pedido,
                'data_cadastro': data_cadastro,
                'data_movimentacao': data_movimentacao,
                'tipo': tipo,
                'empresa': EMPRESA
            }, function (retorno) {
                let pedidos = retorno.dados;
                let tamanho_retorno = pedidos.length;
                let tabela = document.querySelector('#tabela_pedidos tbody');

                tabela = sistema.remover_linha_tabela(tabela);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center'], 'UTILIZE OS FILTROS PARA FACILITAR SUA PESQUISA!', 'inner', true, 10));
                    tabela.appendChild(linha);
                } else {
                    sistema.each(pedidos, function (index, pedido) {
                        let linha = document.createElement('tr');

                        linha.appendChild(sistema.gerar_td(['text-center'], 'TROUXE PEDIDOS DE ENTRADA!', 'inner', true, 10));

                        tabela.appendChild(linha);
                    });
                }
            });
        }

        /**
         * Função para redirecionar o usuário para a página de cadastro de pedidos de entrada. Ela recebe um parâmetro 'codigo_pedido', que é o código do pedido a ser editado. Se o código do pedido for vazio, a função redireciona para a página de cadastro de um novo pedido. Caso contrário, ela redireciona para a página de edição do pedido existente, passando o código do pedido como parâmetro na URL. Essa função é chamada quando o usuário clica no botão "Cadastrar Pedidos" ou em um pedido específico na tabela de pedidos.
         * @param {string} codigo_pedido - O código do pedido a ser editado ou vazio para cadastrar um novo pedido.
         */
        function cadastro_pedidos_entrada(codigo_pedido) {
            window.location.href = sistema.url('/pedidos.php', {
                'rota': 'cadastro_pedidos_entrada',
                'codigo_pedido': codigo_pedido
            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Pedidos de Entrada</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center"
                            onclick="cadastro_pedidos_entrada('');">Cadastrar Pedidos</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">Lista de Pedidos de Entrada</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3 text-center">
                                    <label class="text">Fornecedor</label>
                                    <select class="form-control" id="fornecedor">
                                        <option value="">Selecione um fornecedor</option>
                                    </select>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">TIPO</label>
                                    <select class="form-control" id="status_pedido">
                                        <option value="PEDIDO">APENAS PEDIDO</option>
                                        <option value="PEDIDO_ESTOQUE">PEDIDO + ESTOQUE</option>
                                        <option value="PEDIDO_CONTA">PEDIDO + CONTA</option>
                                        <option value="PEDIDO_COMPLETO">PEDIDO COMPLETO</option>
                                    </select>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Data Cadastro</label>
                                    <input type="date" class="form-control" id="data_cadastro">
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Data Movimentação</label>
                                    <input type="date" class="form-control" id="data_movimentacao">
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-3 push-9">
                                    <button class="btn btn-secondary w-100">Pesquisar</button>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_pedidos">
                                            <thead>
                                                <tr class="text-center">
                                                    <th scope="col">FORNECEDOR</th>
                                                    <th scope="col">TIPO</th>
                                                    <th scope="col">DATA CADASTRO</th>
                                                    <th scope="col">DATA MOVIMENTAÇÃO</th>
                                                    <th scope="col">VALOR BRUTO</th>
                                                    <th scope="col">VALOR DESCONTO</th>
                                                    <th scope="col">VALOR FRETE</th>
                                                    <th scope="col">VALOR LÍQUIDO</th>
                                                    <th scope="col">AÇÃO</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="10" class="text-center" onclick="pesquisar_produtos();">
                                                        UTILIZE OS FILTROS PARA FACILITAR SUA PESQUISA!</td>
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
                pesquisar_fornecedores();
                pesquisar_pedidos();
            }
        </script>
        <?php
        include_once 'includes/footer.php';
});

/** 
 * Rota responsável por cadastrar os pedidos de entrada.
 * Esta rota é acessada quando o usuário clica no botão "Cadastrar Pedidos"
 */
router_add('cadastro_pedidos_entrada', function () {
    include_once 'includes/head.php';
    $hoje = $data->format('Y-m-d');
    ?>
        <script>
            const EMPRESA = "<?php echo $codigo_empresa; ?>";
            let QUANTIDADE_ITEM_PEDIDO = 0;
            let ATUALIZAR_VALORES_PEDIDO = false;
            let VALOR_BRUTO_PEDIDO = 0;
            let VALOR_FRETE_PEDIDO = 0;
            let VALOR_DESCONTO_PEDIDO = 0;
            let VALOR_LIQUIDO_PEDIDO = 0;
            let QUANTIDADE_PRODUTOS_PEDIDOS = 0;
            let VALOR_UNITARIO_PEDIDO = 0;

            /** 
             * Função para pesquisar os fornecedores disponíveis. Ela faz uma requisição POST para a rota 'pesquisar_clientes' no arquivo 'clientes.php', passando o código da empresa e o tipo de usuário como 'FORNECEDOR'. Ao receber a resposta, a função itera sobre a lista de fornecedores retornada e adiciona cada um como uma opção em um elemento select com o id 'fornecedor'. Essa função é chamada quando a página é carregada para garantir que a lista de fornecedores esteja atualizada e disponível para seleção no formulário de cadastro de produtos.
             */
            function pesquisar_fornecedores() {
                sistema.request.post('/clientes.php', {
                    'rota': 'pesquisar_clientes',
                    'empresa': EMPRESA,
                    'tipo_usuario': 'FORNECEDOR'
                }, function (retorno) {
                    let select_fornecedores = document.querySelector('#fornecedor');
                    let select_fornecedores_tabela = document.querySelector('#fornecedor_tabela');
                    let fornecedores = retorno.dados;

                    sistema.each(fornecedores, function (fornecedor) {
                        select_fornecedores.appendChild(sistema.gerar_option(fornecedor._id.$oid, fornecedor.nome_usuario));
                        select_fornecedores_tabela.appendChild(sistema.gerar_option(fornecedor._id.$oid, fornecedor.nome_usuario));

                    });
                });
            }
            /** 
             * Função responsável por pesquisar os produtos cadastrados no sistema
             */
            function pesquisar_produtos() {
                let fornecedor = document.querySelector('#fornecedor_tabela').value;
                let nome_produto = document.querySelector('#nome_produto').value;
                let codigo_barras = document.querySelector('#codigo_barras').value;
                let tipo_produto = document.querySelector('#tipo_produto').value;
                let status_produto = true;

                let dados = {
                    'rota': 'pesquisar_produtos',
                    'empresa': EMPRESA,
                    'fornecedor': fornecedor,
                    'nome_produto': nome_produto,
                    'codigo_barras': codigo_barras,
                    'tipo_produto': tipo_produto,
                    'status_produto': status_produto
                };

                sistema.request.post('/produtos.php', dados, function (retorno) {
                    let produtos = retorno.dados;
                    let tabela = document.querySelector('#tabela_produtos_pedido tbody');

                    tabela = sistema.remover_linha_tabela(tabela);

                    if (produtos.length == 0) {
                        let linha = document.createElement('tr');
                        linha.appendChild(sistema.gerar_td(['text-center'], 'NENHUM PRODUTO ENCONTRADO!', 'inner', true, 10));
                        tabela.appendChild(linha);
                    } else {
                        sistema.each(produtos, function (index, produto) {
                            let linha = document.createElement('tr');

                            linha.appendChild(sistema.gerar_td(['text-center'], produto.nome_produto));
                            linha.appendChild(sistema.gerar_td(['text-center'], '1000'));
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.number_format(produto.valor_venda)));
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_adicionar_item_pedido_' + produto._id.$oid, 'ADICIONAR', ['btn', 'btn-success'], function () {
                                colocar_dados_produto_pedido(produto._id.$oid, produto.nome_produto, produto.valor_venda);
                            }), 'append'));

                            tabela.appendChild(linha);
                        });
                    }
                });
            }

            /** 
             * Função para adicionar um item de produto ao pedido. Ela recebe o código do produto, o nome do produto e o valor de venda como parâmetros. A função incrementa a quantidade de itens do pedido e cria uma nova linha na tabela de itens do pedido, preenchendo as informações correspondentes em cada coluna, como código do produto, nome do produto, quantidade, valor unitário, valor bruto, valor de frete e valor total. Além disso, a função adiciona um botão de exclusão para cada item adicionado, permitindo que o usuário remova o item da tabela se necessário. Essa função é chamada quando o usuário clica no botão "ADICIONAR" na tabela de produtos pesquisados.
             * @param {string} codigo_produto - O código do produto a ser adicionado ao pedido.
             * @param {string} nome_produto - O nome do produto a ser adicionado ao pedido.
             * @param {number} valor_venda - O valor de venda do produto a ser adicionado ao pedido.
             */
            function colocar_dados_produto_pedido(codigo_produto, nome_produto, valor_venda) {
                QUANTIDADE_ITEM_PEDIDO++;

                let tabela = document.querySelector('#tabela_itens_pedido tbody');
                let linha = document.createElement('tr');
                let id_linha = 'linha_' + QUANTIDADE_ITEM_PEDIDO;

                linha.id = id_linha;

                linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('id_produto_item_' + QUANTIDADE_ITEM_PEDIDO, QUANTIDADE_ITEM_PEDIDO, ['form-control', 'text-center'], 'text', '', false), 'append'));
                linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('codigo_produto_item_' + QUANTIDADE_ITEM_PEDIDO, codigo_produto, ['form-control', 'text-center'], 'text', '', false), 'append'));
                linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('nome_produto_item_' + QUANTIDADE_ITEM_PEDIDO, nome_produto, ['form-control', 'text-center'], 'text', '', false), 'append'));
                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('quantidade_produto_item_' + QUANTIDADE_ITEM_PEDIDO, '1', ['form-control', 'text-center'], 'number', '', true), 'append'));
                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('valor_unitario_produto_item_' + QUANTIDADE_ITEM_PEDIDO, sistema.number_format(valor_venda), ['form-control', 'text-center'], 'text', '', true), 'append'));
                linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('valor_bruto_produto_item_' + QUANTIDADE_ITEM_PEDIDO, sistema.number_format(valor_venda), ['form-control', 'text-center'], 'text', '', false), 'append'));
                linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('valor_desconto_produto_item_' + QUANTIDADE_ITEM_PEDIDO, sistema.number_format(0), ['form-control', 'text-center'], 'text', '', true), 'append'));
                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('valor_frete_produto_item_' + QUANTIDADE_ITEM_PEDIDO, '0', ['form-control', 'text-center'], 'text', '', true), 'append'));
                linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('valor_total_produto_item_' + QUANTIDADE_ITEM_PEDIDO, sistema.number_format(valor_venda), ['form-control', 'text-center'], 'text', '', false), 'append'));
                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_excluir_item_pedido_' + QUANTIDADE_ITEM_PEDIDO, 'EXCLUIR', ['btn', 'btn-danger'], function () {
                    document.querySelector('#' + id_linha).remove();
                }), 'append'));

                tabela.appendChild(linha);
            }

            /** 
             * Função responsável por atualizar os valores do pedido com base nos itens adicionados. Ela percorre as linhas da tabela de itens do pedido, coletando as informações de quantidade, valor unitário, valor de frete e valor de desconto para cada item. A função realiza os cálculos necessários para atualizar o valor bruto, valor de frete, valor de desconto e valor líquido do pedido, bem como a quantidade total de produtos e o valor unitário total. Os valores atualizados são exibidos nos campos correspondentes na interface do usuário. Essa função é chamada quando o usuário clica no botão "Atualizar Valores" para garantir que os valores do pedido estejam corretos antes de salvar os dados.
             */
            function atualizar_valores_pedido() {
                let linhas = document.querySelectorAll('#tabela_itens_pedido tr');
                let executando = true;

                VALOR_BRUTO_PEDIDO = 0;
                VALOR_FRETE_PEDIDO = 0;
                VALOR_DESCONTO_PEDIDO = 0;
                VALOR_LIQUIDO_PEDIDO = 0;
                QUANTIDADE_PRODUTOS_PEDIDOS = 0;
                VALOR_UNITARIO_PEDIDO = 0;

                linhas.forEach(function (linhas, index) {
                    let i = index + 1;

                    if (executando == true) {
                        let elemento = document.querySelector('#id_produto_item_' + i);

                        if (!elemento) { } else {

                            let quantidade = document.querySelector('#quantidade_produto_item_' + i).value;
                            let valor_unitario = document.querySelector('#valor_unitario_produto_item_' + i).value;
                            let valor_frete = document.querySelector('#valor_frete_produto_item_' + i).value;
                            let valor_desconto = document.querySelector('#valor_desconto_produto_item_' + i).value;

                            quantidade = quantidade.replace(',', '.');
                            valor_unitario = valor_unitario.replace(',', '.');
                            valor_frete = valor_frete.replace(',', '.');
                            valor_desconto = valor_desconto.replace(',', '.');

                            QUANTIDADE_PRODUTOS_PEDIDOS = sistema.arredondar(QUANTIDADE_PRODUTOS_PEDIDOS, '+', quantidade);

                            let valor_bruto = sistema.arredondar(quantidade, '*', valor_unitario);

                            VALOR_UNITARIO_PEDIDO = sistema.arredondar(VALOR_UNITARIO_PEDIDO, '+', valor_unitario);
                            VALOR_BRUTO_PEDIDO = sistema.arredondar(VALOR_BRUTO_PEDIDO, '+', valor_bruto);

                            document.querySelector('#valor_bruto_produto_item_' + i).value = sistema.number_format(valor_bruto);

                            if (valor_desconto > 0) {
                                valor_bruto = sistema.arredondar(valor_bruto, '-', valor_desconto);
                                VALOR_DESCONTO_PEDIDO = sistema.arredondar(VALOR_DESCONTO_PEDIDO, '+', valor_desconto);
                            }

                            if (valor_frete > 0) {
                                valor_bruto = sistema.arredondar(valor_bruto, '+', valor_frete);
                                VALOR_FRETE_PEDIDO = sistema.arredondar(VALOR_FRETE_PEDIDO, '+', valor_frete);
                            }

                            document.querySelector('#valor_total_produto_item_' + i).value = sistema.number_format(valor_bruto);

                            VALOR_LIQUIDO_PEDIDO = sistema.arredondar(VALOR_LIQUIDO_PEDIDO, '+', valor_bruto);

                            document.querySelector('#quantidade_total_itens').value = QUANTIDADE_PRODUTOS_PEDIDOS;
                            document.querySelector('#valor_unitario').value = sistema.number_format(VALOR_UNITARIO_PEDIDO);
                            document.querySelector('#valor_bruto').value = sistema.number_format(VALOR_BRUTO_PEDIDO);
                            document.querySelector('#valor_frete').value = sistema.number_format(VALOR_FRETE_PEDIDO);
                            document.querySelector('#valor_desconto').value = sistema.number_format(VALOR_DESCONTO_PEDIDO);
                            document.querySelector('#valor_liquido').value = sistema.number_format(VALOR_LIQUIDO_PEDIDO);

                            if (i == QUANTIDADE_ITEM_PEDIDO) {
                                executando = false;
                                document.querySelector('#btn_salvar_dados').disabled = false;
                            }
                        }
                    }
                });
            }

            function salvar_dados() {
                let fornecedor = document.querySelector('#fornecedor').value;
                let status_pedido = document.querySelector('#status_pedido').value;
                let tipo_pedido = document.querySelector('#tipo_pedido').value;
                let data_cadastro = document.querySelector('#data_cadastro').value;
                let data_movimentacao = document.querySelector('#data_movimentacao').value;
                let quantidade_total_itens = document.querySelector('#quantidade_total_itens').value;
                let valor_unitario = document.querySelector('#valor_unitario').value;
                let valor_bruto = document.querySelector('#valor_bruto').value;
                let valor_desconto = document.querySelector('#valor_desconto').value;
                let valor_frete = document.querySelector('#valor_frete').value;
                let valor_liquido = document.querySelector('#valor_liquido').value;

                let linhas = document.querySelectorAll('#tabela_itens_pedido tr');
                let itens_pedido = [];
                let executando = true;

                linhas.forEach(function (linha, index) {
                    let i = index + 1;
                    if (executando == true) {
                        let elemento = document.querySelector('#id_produto_item_' + i);

                        if (!elemento) { } else {
                            let id_produto = document.querySelector('#codigo_produto_item_' + i).value;
                            let quantidade = document.querySelector('#quantidade_produto_item_' + i).value;
                            let valor_unitario_produto = document.querySelector('#valor_unitario_produto_item_' + i).value;
                            let valor_bruto_produto = document.querySelector('#valor_bruto_produto_item_' + i).value;
                            let valor_desconto_produto = document.querySelector('#valor_desconto_produto_item_' + i).value;
                            let valor_frete_produto = document.querySelector('#valor_frete_produto_item_' + i).value;
                            let valor_total_produto = document.querySelector('#valor_total_produto_item_' + i).value;

                            itens_pedido.push({
                                'id_produto': id_produto,
                                'quantidade': quantidade,
                                'valor_unitario_produto': valor_unitario_produto,
                                'valor_bruto_produto': valor_bruto_produto,
                                'valor_desconto_produto': valor_desconto_produto,
                                'valor_frete_produto': valor_frete_produto,
                                'valor_total_produto': valor_total_produto
                            });

                            if (i == QUANTIDADE_ITEM_PEDIDO) {
                                executando = false;
                            }
                        }
                    }
                });

                let json = JSON.stringify(itens_pedido);

                if (fornecedor != '') {
                    sistema.request.post('/pedidos.php', {
                        'rota': 'salvar_pedidos_entrada',
                        'empresa': EMPRESA,
                        'fornecedor': fornecedor,
                        'status_pedido': status_pedido,
                        'tipo_pedido': tipo_pedido,
                        'data_cadastro': data_cadastro,
                        'data_movimentacao': data_movimentacao,
                        'quantidade_total_itens': quantidade_total_itens,
                        'valor_unitario': valor_unitario,
                        'valor_bruto': valor_bruto,
                        'valor_desconto': valor_desconto,
                        'valor_frete': valor_frete,
                        'valor_liquido': valor_liquido,
                        'objeto_itens': json
                    }, function (retorno) {
                        validar_retorno(retorno, '/pedidos.php', 0, 'entrada');
                    });
                } else {
                    alerta_campo_vazio('FORNECEDOR');
                }
            }

            function limpar_dados() {
                document.querySelector('#fornecedor').value = '';
                document.querySelector('#status_pedido').value = 'PEDIDO';
                document.querySelector('#data_cadastro').value = '<?php echo $hoje; ?>';
                document.querySelector('#data_movimentacao').value = '<?php echo $hoje; ?>';

                let tabela = document.querySelector('#tabela_itens_pedido tbody');
                tabela = sistema.remover_linha_tabela(tabela);

                document.querySelector('#quantidade_total_itens').value = '0';
                document.querySelector('#valor_unitario').value = '0';
                document.querySelector('#valor_bruto').value = '0';
                document.querySelector('#valor_frete').value = '0';
                document.querySelector('#valor_desconto').value = '0';
                document.querySelector('#valor_liquido').value = '0';

                QUANTIDADE_ITEM_PEDIDO = 0;
            }

            function voltar() {
                window.location.href = sistema.url('/pedidos.php', {
                    'rota': 'entrada'
                });
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                    <div>
                        <h6>Cadastro de Pedidos de Entrada</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <div class="card-title">Cadastro de Pedidos de Entrada</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <label class="text">Fornecedor</label>
                                        <select class="form-control" id="fornecedor">
                                            <option value="">Selecione um fornecedor</option>
                                        </select>
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Status</label>
                                        <select class="form-control" id="status_pedido">
                                            <option value="PEDIDO">APENAS PEDIDO</option>
                                            <option value="PEDIDO_ESTOQUE">PEDIDO + ESTOQUE</option>
                                            <option value="PEDIDO_CONTA">PEDIDO + CONTA</option>
                                            <option value="PEDIDO_COMPLETO">PEDIDO COMPLETO</option>
                                        </select>
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Tipo Pedido</label>
                                        <select class="form-control" id="tipo_pedido">
                                            <option value="true">PEDIDO ENTRADA</option>
                                        </select>
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Data Cadastro</label>
                                        <input type="date" class="form-control" id="data_cadastro"
                                            value="<?php echo $hoje; ?>">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Data Movimentação</label>
                                        <input type="date" class="form-control" id="data_movimentacao"
                                            value="<?php echo $hoje; ?>">
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-nowrap text-nowrap table-hover"
                                                id="tabela_itens_pedido">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th scope="col">#</th>
                                                        <th scope="col">CÓDIGO</th>
                                                        <th scope="col">PRODUTO</th>
                                                        <th scope="col">QUANTIDADE</th>
                                                        <th scope="col">VALOR UNITÁRIO</th>
                                                        <th scope="col">VALOR BRUTO</th>
                                                        <th scope="col">VALOR DESCONTO</th>
                                                        <th scope="col">VALOR FRETE</th>
                                                        <th scope="col">VALOR TOTAL</th>
                                                        <th scope="col">EXCLUIR</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-2 text-center">
                                        <label class="text">Quantidade Itens</label>
                                        <input type="text" class="form-control text-center" id="quantidade_total_itens"
                                            sistema-mask="codigo" disabled value="0">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Valor Unitário</label>
                                        <input type="text" class="form-control text-center" id="valor_unitario"
                                            sistema-mask="moeda" value="0">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Valor Bruto</label>
                                        <input type="text" class="form-control text-center" id="valor_bruto"
                                            sistema-mask="moeda" value="0">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Valor Desconto</label>
                                        <input type="text" class="form-control text-center" id="valor_desconto"
                                            sistema-mask="moeda" value="0">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Valor Frete</label>
                                        <input type="text" class="form-control text-center" id="valor_frete"
                                            sistema-mask="moeda" value="0">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Valor Líquido</label>
                                        <input type="text" class="form-control text-center" id="valor_liquido"
                                            sistema-mask="moeda" value="0">
                                    </div>
                                </div>
                                <br />
                                <?php
                                include_once 'includes/botao_cadastro.php';
                                ?>
                                <br />
                                <div class="row">
                                    <div class="col-4 push-4">
                                        <button class="btn btn-secondary w-100 btn-lg"
                                            onclick="atualizar_valores_pedido();">Atualizar Valores</button>
                                    </div>
                                    <div class="col-4">
                                        <button
                                            class="btn btn-primary d-flex align-items-center justify-content-center w-100 btn-lg"
                                            data-bs-toggle="modal" data-bs-target="#modal_pedido_item">Pesquisar
                                            Produtos</button>
                                    </div>
                                </div>
                                <br />
                            </div>
                        </div>
                        <div class="modal fade" id="modal_pedido_item" tabindex="-1" role="dialog"
                            aria-labelledby="myLargeModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="myLargeModalLabel">Adicionar Item Pedido</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-3 text-center">
                                                <label class="text">Fornecedor</label>
                                                <select class="form-control" id="fornecedor_tabela">
                                                    <option value="">Selecione Um Fornecedor</option>
                                                </select>
                                            </div>
                                            <div class="col-3 text-center">
                                                <label class="text">Produto</label>
                                                <input type="text" class="form-control" id="nome_produto">
                                            </div>
                                            <div class="col-3 text-center">
                                                <label class="text">Código Barras</label>
                                                <input type="text" class="form-control" id="codigo_barras">
                                            </div>
                                            <div class="col-3 text-center">
                                                <label class="text">Tipo Produto</label>
                                                <select class="form-control" id="tipo_produto">
                                                    <option value="PRODUTO">PRODUTO</option>
                                                    <option value="SERVIÇO">SERVIÇO</option>
                                                </select>
                                            </div>
                                        </div>
                                        <br />
                                        <div class="row">
                                            <div class="col-4 push-8">
                                                <button class="btn btn-secondary w-100"
                                                    onclick="pesquisar_produtos();">Pesquisar</button>
                                            </div>
                                        </div>
                                        <br />
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="table-responsive">
                                                    <table class="table table-nowrap text-nowrap table-hover"
                                                        id="tabela_produtos_pedido">
                                                        <thead>
                                                            <tr class="text-center">
                                                                <th scope="col">PRODUTO</th>
                                                                <th scope="col">QUANTIDADE</th>
                                                                <th scope="col">VALOR UNITÁRIO</th>
                                                                <th scope="col">AÇÃO</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <br />
                                        <div class="row">
                                            <div class="col-4">
                                                <button type="button" class="btn btn-danger w-100 btn-lg"
                                                    data-bs-dismiss="modal" aria-label="Close">Fechar</button>
                                            </div>
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
                    pesquisar_fornecedores();
                    document.querySelector('#btn_salvar_dados').disabled = true;
                }
            </script>
            <?php
            include_once 'includes/footer.php';
});

router_add('saida', function () {
    include_once 'includes/head.php';
    ?>
            <div class="page-wrapper">
                <div class="content">
                    <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                        <div>
                            <h6>Pedidos de Saída</h6>
                        </div>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                            <div class="dropdown">
                                <button class="btn btn-primary d-flex align-items-center justify-content-center"
                                    onclick="cadastro_pedidos_saida('');">Cadastrar Pedidos</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                include_once 'includes/footer.php';
});
?>