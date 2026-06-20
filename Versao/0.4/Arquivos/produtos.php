<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Produtos.php';
require_once 'modelos/Usuario.php';

/** 
 * Rota responsável por salvar os dados do produto. Ela cria uma instância da classe Produtos e chama o método salvar_dados, passando os dados recebidos na requisição. O resultado da operação é retornado como um JSON, indicando se a ação foi bem-sucedida ou não.
 */
router_add('salvar_dados_produto', function () {
    $objeto_produto = new Produtos();

    echo json_encode((array) ['status' => (bool) $objeto_produto->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

/** 
 * Rota responsável por pesquisar vários produtos. Ela cria uma instância da classe Produtos e chama o método pesquisar, passando os filtros recebidos na requisição. O resultado da pesquisa é retornado como um JSON, contendo os dados dos produtos encontrados.
 */
router_add('pesquisar_produtos', function () {
    $objeto_produto = new Produtos();
    $objeto_usuario = new Usuario();

    $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa'] : '');
    $nome_produto = (string) (isset($_REQUEST['nome_produto']) ? (string) strtoupper($_REQUEST['nome_produto']) : '');
    $fornecedor = (string) (isset($_REQUEST['fornecedor']) ? (string) $_REQUEST['fornecedor'] : '');
    $status_produto = (bool) (isset($_REQUEST['status_produto']) ? (bool) filter_var($_REQUEST['status_produto'], FILTER_VALIDATE_BOOLEAN) : false);
    $tipo_produto = (bool) (isset($_REQUEST['tipo_produto']) ? (bool) filter_var($_REQUEST['tipo_produto'], FILTER_VALIDATE_BOOLEAN) : false);
    $unidade_medida = (string) (isset($_REQUEST['unidade_medida']) ? (string) $_REQUEST['unidade_medida'] : '');
    $data_cadastro = (string) (isset($_REQUEST['data_cadastro']) ? (string) $_REQUEST['data_cadastro'] : '');
    $codigo_barras = (string) (isset($_REQUEST['codigo_barras']) ? (string) $_REQUEST['codigo_barras'] : '');

    $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['data_cadastro' => (bool) false], 'limite' => (int) 100];
    $array_push = (array) [];

    $produtos = (array) [];
    $produtos_final = (array) [];

    if ($empresa != '') {
        array_push($array_push, (array) ['empresa', '===', model_id($empresa)]);
    }

    if ($fornecedor != '') {
        array_push($array_push, (array) ['fornecedor', '===', model_id($fornecedor)]);
    }

    if ($nome_produto != '') {
        array_push($array_push, (array) ['nome_produto', '=', $nome_produto]);
    }

    if ($data_cadastro != '') {
        array_push($array_push, (array) ['data_cadastro', '>=', model_date($data_cadastro, '00:00:00')]);
        array_push($array_push, (array) ['data_cadastro', '<=', model_date($data_cadastro, '23:59:59')]);
    }

    if ($unidade_medida != '') {
        array_push($array_push, (array) ['unidade_medida', '===', (string) $unidade_medida]);
    }

    if ($codigo_barras != '') {
        array_push($array_push, (array) ['codigo_barras', '===', (string) $codigo_barras]);
    }

    array_push($array_push, (array) ['status', '===', (bool) $status_produto]);
    array_push($array_push, (array) ['tipo_produto', '===', (bool) $tipo_produto]);

    $filtro['filtro'] = (array) ['and' => $array_push];

    $produtos = (array) $objeto_produto->pesquisar_todos($filtro);

    if (empty($produtos) == false) {
        foreach ($produtos as $produto) {
            $fornecedor = (array) $objeto_usuario->pesquisar((array) ['filtro' => (array) ['_id', '===', $produto['fornecedor']]]);
            $produto['fornecedor'] = $fornecedor;
            array_push($produtos_final, $produto);
        }
    }

    ob_clean();

    echo json_encode(['dados' => (array) $produtos_final, 'filtro' => (array) $filtro], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * Rota responsável por pesquisar um produto específico. Ela cria uma instância da classe Produtos e chama o método pesquisar, passando o código do produto recebido na requisição. O resultado da pesquisa é retornado como um JSON, contendo os dados do produto encontrado.
 */
router_add('pesquisar_produto', function () {
    $objeto_produto = new Produtos();

    $codigo_produto = (string) (isset($_REQUEST['codigo_produto']) ? $_REQUEST['codigo_produto'] : '');

    $produto = (array) $objeto_produto->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_produto)]]);

    ob_clean();

    echo json_encode(['dados' => (array) $produto], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * Rota responsável por exibir a página de listagem de produtos. Ela inclui o arquivo de cabeçalho, define a data atual e renderiza o conteúdo HTML da página, que inclui filtros de pesquisa e uma tabela para exibir os produtos. A página também contém scripts JavaScript para lidar com as interações do usuário, como pesquisar produtos e redirecionar para a página de cadastro de produtos.
 */
router_add('index', function () {
    include_once 'includes/head.php';
    $data_hoje = $data->format('Y-m-d');
    ?>
    <script>
        const DATA_HOJE = "<?php echo $data_hoje; ?>";
        const CODIGO_EMPRESA = "<?php echo $codigo_empresa; ?>";

        /**
         * Função para redirecionar para a página de cadastro de produtos. Se um código de produto for fornecido, ele será incluído na URL para edição do produto existente. Caso contrário, a URL será para o cadastro de um novo produto.
         * @param string codigo_produto O código do produto a ser editado. Se vazio, a função redirecionará para a página de cadastro de um novo produto.
         */
        function cadastro_produtos(codigo_produto) {
            window.location.href = sistema.url('/produtos.php', {
                'rota': 'cadastro_produtos',
                'codigo_produto': codigo_produto
            });
        }

        /** 
         * Função para pesquisar os fornecedores disponíveis. Ela faz uma requisição POST para a rota 'pesquisar_clientes' no arquivo 'clientes.php', passando o código da empresa e o tipo de usuário como 'FORNECEDOR'. Ao receber a resposta, a função itera sobre a lista de fornecedores retornada e adiciona cada um como uma opção em um elemento select com o id 'fornecedor'. Essa função é chamada quando a página é carregada para garantir que a lista de fornecedores esteja atualizada e disponível para seleção no formulário de cadastro de produtos.
         */
        function pesquisar_fornecedores() {
            sistema.request.post('/clientes.php', {
                'rota': 'pesquisar_clientes',
                'empresa': CODIGO_EMPRESA,
                'tipo_usuario': 'FORNECEDOR'
            }, function (retorno) {
                let fornecedores = retorno.dados;
                let select_fornecedores = document.querySelector('#fornecedor');

                sistema.each(fornecedores, function (index, fornecedor) {
                    select_fornecedores.appendChild(sistema.gerar_option(fornecedor._id.$oid, fornecedor.nome_usuario));
                });
            });
        }

        /** 
         * Função responsável por pesquisar os produtos com base nos filtros fornecidos pelo usuário. Ela coleta os valores dos filtros, como nome do produto, fornecedor, status, tipo, unidade de medida e data de cadastro, e faz uma requisição POST para a rota 'pesquisar_produtos' no arquivo 'produtos.php', passando os filtros como parâmetros. Ao receber a resposta, a função processa os dados dos produtos retornados e atualiza a tabela na página para exibir os resultados da pesquisa. Se nenhum produto for encontrado, uma mensagem é exibida na tabela para informar o usuário.
         */
        function pesquisar_produtos() {
            let nome_produto = document.querySelector('#nome_produto').value;
            let fornecedor = document.querySelector('#fornecedor').value;
            let status_produto = document.querySelector('#status_produto').value;
            let tipo_produto = document.querySelector('#tipo_produto').value;
            let unidade_medida = document.querySelector('#unidade_medida').value;
            let data_cadastro = document.querySelector('#data_cadastro').value;
            let dados = {
                'rota': 'pesquisar_produtos',
                'empresa': CODIGO_EMPRESA,
                'nome_produto': nome_produto,
                'fornecedor': fornecedor,
                'status_produto': status_produto,
                'tipo_produto': tipo_produto,
                'unidade_medida': unidade_medida,
                'data_cadastro': data_cadastro
            };

            console.log(dados);


            sistema.request.post('/produtos.php', dados, function (retorno) {
                let produtos = retorno.dados;
                let filtro = retorno.filtro;
                let tamanho_retorno = produtos.length;
                let tabela = document.querySelector('#tabela_produtos tbody');

                tabela = sistema.remover_linha_tabela(tabela);

                console.log(produtos);
                console.log(filtro);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center'], 'UTILIZE OS FILTROS PARA FACILITAR SUA PESQUISA!', 'inner', true, 10));
                    tabela.appendChild(linha);
                } else {
                    sistema.each(produtos, function (index, produto) {
                        let linha = document.createElement('tr');

                        let div = sistema.gerar_div(['d-flex']);
                        let a = document.createElement('a');
                        a.classList.add('avatar', 'avatar-sm', 'rounded-circle', 'me-2', 'flex-shrink-0');
                        let img = document.createElement('img');
                        img.classList.add('rounded-circle');
                        img.setAttribute('alt', 'img');

                        if (produto.imagem != '') {
                            img.setAttribute('src', produto.imagem);
                        } else {
                            img.setAttribute('src', 'imagens/imagens_sistema/logo_empresa_preto.jpg');
                        }

                        a.appendChild(img);
                        div.appendChild(a);
                        let div_text = document.createElement('div');
                        let h6 = document.createElement('h6');
                        h6.classList.add('fs-14', 'fw-medium', 'mb-0');
                        let a_text = document.createElement('a');
                        // a_text.setAttribute('href', 'javascript:void(0);');
                        a_text.textContent = produto.nome_produto;
                        h6.appendChild(a_text);
                        div_text.appendChild(h6);
                        div.appendChild(div_text);
                        linha.appendChild(sistema.gerar_td(['text-center'], div, 'append'));

                        linha.appendChild(sistema.gerar_td(['text-center'], produto.fornecedor.nome_usuario, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], 'ESTOQUE_ATUAL'));
                        linha.appendChild(sistema.gerar_td(['text-center'], produto.valor_venda));
                        linha.appendChild(sistema.gerar_td(['text-center'], produto.valor_custo));
                        linha.appendChild(sistema.gerar_td(['text-center'], (produto.status ? 'ATIVO' : 'INATIVO')));

                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_cadastrar_imagem_' + produto._id.$oid, 'CADASTRAR IMAGEM', ['btn', 'btn-secondary'], function () {
                            cadastrar_imagem(produto._id.$oid);
                        }), 'append'));

                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_editar_produto_' + produto._id.$oid, 'EDITAR', ['btn', 'btn-primary'], function () {
                            cadastro_produtos(produto._id.$oid);
                        }), 'append'));

                        tabela.appendChild(linha);
                    });
                }
            });
        }

        /**
         * Função para redirecionar para a página de cadastro de imagens do produto. Ela recebe o código do produto como parâmetro e utiliza a função window.location.href para alterar a URL do navegador, direcionando o usuário para a página de cadastro de imagens, passando o código do produto como um parâmetro na URL. Essa função é útil para permitir que o usuário adicione ou gerencie as imagens associadas a um produto específico.
         * @param string codigo_produto O código do produto para o qual as imagens serão cadastradas ou gerenciadas.
         */
        function cadastrar_imagem(codigo_produto) {
            window.location.href = sistema.url('/produtos.php', {
                'rota': 'cadastrar_imagem_produto',
                'codigo_produto': codigo_produto
            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Produtos</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center"
                            onclick="cadastro_produtos('');">Cadastro Produtos</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">Lista de Produtos</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3 text-center">
                                    <label class="text">Nome do produto</label>
                                    <input type="text" class="form-control text-uppercase" placeholder="Nome do Produto"
                                        id="nome_produto">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Fornecedor</label>
                                    <select class="form-control" id="fornecedor">
                                        <option value="">Selecione uma opção</option>
                                    </select>
                                </div>
                                <div class="col-1 text-center">
                                    <label class="text">Status</label>
                                    <select class="form-control" id="status_produto">
                                        <option value="true">ATIVO</option>
                                        <option value="false">INATIVO</option>
                                    </select>
                                </div>
                                <div class="col-1 text-center">
                                    <label class="text">Tipo</label>
                                    <select class="form-control" id="tipo_produto">
                                        <option value="1">PRODUTO</option>
                                        <option value="0">SERVIÇO</option>
                                    </select>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Unidade de Medida</label>
                                    <select class="form-control" id="unidade_medida">
                                        <option value="">Selecione uma opção</option>
                                        <option value="UN">UNIDADE</option>
                                        <option value="KG">QUILOGRAMA</option>
                                        <option value="L">LITRO</option>
                                    </select>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Data Cadastro</label>
                                    <input type="date" class="form-control" id="data_cadastro">
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
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_produtos">
                                            <thead>
                                                <tr class="text-center">
                                                    <th scope="col">NOME PRODUTO</th>
                                                    <th scope="col">FORNECEDOR</th>
                                                    <th scope="col">QUANTIDADE</th>
                                                    <th scope="col">VALOR VENDA</th>
                                                    <th scope="col">CUSTO</th>
                                                    <th scope="col">STATUS</th>
                                                    <th scope="col">FOTO</th>
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
                pesquisar_produtos();
            }
        </script>
        <?php
        include_once 'includes/footer.php';
});

/** 
 * Rota responsável por exibir a página de cadastro de produtos. Ela inclui o arquivo de cabeçalho, define a data atual e renderiza o conteúdo HTML da página, que inclui um formulário para cadastro ou edição de produtos. A página também contém scripts JavaScript para lidar com as interações do usuário, como salvar os dados do produto, limpar o formulário e redirecionar para a página de listagem de produtos.
 */
router_add('cadastro_produtos', function () {
    include_once 'includes/head.php';
    $data_hoje = $data->format('Y-m-d');

    $codigo_produto = (string) (isset($_REQUEST['codigo_produto']) ? $_REQUEST['codigo_produto'] : '');
    ?>
        <script src="https://cdn.tiny.cloud/1/n23bhx6xyo7d1ycpo3rb8u5iwxz1wf5371z3f1yalxz91bop/tinymce/8/tinymce.min.js"
            referrerpolicy="origin" crossorigin="anonymous"></script>
        <script>
            const CODIGO_EMPRESA = "<?php echo $codigo_empresa; ?>";
            const DATA_HOJE = "<?php echo $data_hoje; ?>";

            let CODIGO_PRODUTO = "<?php echo $codigo_produto; ?>";

            /** 
             * Função para salvar os dados do produto. Ela coleta os valores dos campos do formulário, incluindo o nome do produto, fornecedor, valor de venda, valor de custo, quantidade de alerta, status do produto, tipo de produto, unidade de medida, data de cadastro, descrição e código de barras. Em seguida, faz uma requisição POST para a rota 'salvar_dados_produto' com os dados coletados. Após receber a resposta, a função 'validar_retorno' é chamada para processar o resultado da operação.
             */
            function salvar_dados() {
                let nome_produto = document.querySelector('#nome_produto').value;
                let fornecedor = document.querySelector('#fornecedor').value;
                let valor_venda = document.querySelector('#valor_venda').value;
                let valor_custo = document.querySelector('#valor_custo').value;
                let quantidade_alerta = document.querySelector('#quantidade_alerta').value;
                let status_produto = document.querySelector('#status_produto').value;
                let tipo_produto = document.querySelector('#tipo_produto').value;
                let unidade_medida = document.querySelector('#unidade_medida').value;
                let data_cadastro = document.querySelector('#data_cadastro').value;
                let descricao = tinymce.get('editor').getContent();
                let codigo_barra = document.querySelector('#codigo_barras').value;

                sistema.request.post('/produtos.php', {
                    'rota': 'salvar_dados_produto',
                    'codigo_produto': CODIGO_PRODUTO,
                    'empresa': CODIGO_EMPRESA,
                    'fornecedor': fornecedor,
                    'nome_produto': nome_produto,
                    'descricao': descricao,
                    'codigo_barras': codigo_barra,
                    'quantidade_alerta': quantidade_alerta,
                    'data_cadastro': data_cadastro,
                    'valor_venda': valor_venda,
                    'valor_custo': valor_custo,
                    'unidade_medida': unidade_medida,
                    'status_produto': status_produto,
                    'tipo_produto': tipo_produto
                }, function (retorno) {
                    validar_retorno(retorno, '/produtos.php');
                });
            }

            /** 
             * Função para limpar os dados do formulário de cadastro de produtos. Ela seleciona cada campo do formulário usando querySelector e define seu valor como uma string vazia ou, no caso do editor de texto, limpa seu conteúdo usando a função setContent. Essa função é útil para resetar o formulário após o cadastro de um produto ou quando o usuário deseja limpar os campos manualmente.
             */
            function limpar_dados() {
                document.querySelector('#nome_produto').value = '';
                document.querySelector('#fornecedor').value = '';
                document.querySelector('#valor_venda').value = '';
                document.querySelector('#valor_custo').value = '';
                document.querySelector('#quantidade_alerta').value = '';
                document.querySelector('#status_produto').value = '';
                document.querySelector('#tipo_produto').value = '';
                document.querySelector('#unidade_medida').value = '';
                document.querySelector('#data_cadastro').value = '';
                tinymce.get('editor').setContent('');
            }

            /** 
             * Função para redirecionar o usuário de volta para a página de listagem de produtos. Ela utiliza a função window.location.href para alterar a URL do navegador, direcionando o usuário para '/produtos.php' com a rota 'index'. Essa função é útil para permitir que o usuário retorne facilmente à lista de produtos após concluir uma ação no formulário de cadastro ou edição.
             */
            function voltar() {
                window.location.href = sistema.url('/produtos.php', {
                    'rota': 'index'
                });
            }

            /** 
             * Função para pesquisar os fornecedores disponíveis. Ela faz uma requisição POST para a rota 'pesquisar_clientes' no arquivo 'clientes.php', passando o código da empresa e o tipo de usuário como 'FORNECEDOR'. Ao receber a resposta, a função itera sobre a lista de fornecedores retornada e adiciona cada um como uma opção em um elemento select com o id 'fornecedor'. Essa função é chamada quando a página é carregada para garantir que a lista de fornecedores esteja atualizada e disponível para seleção no formulário de cadastro de produtos.
             */
            function pesquisar_fornecedores() {
                sistema.request.post('/clientes.php', {
                    'rota': 'pesquisar_clientes',
                    'empresa': CODIGO_EMPRESA,
                    'tipo_usuario': 'FORNECEDOR'
                }, function (retorno) {
                    let fornecedores = retorno.dados;
                    let select_fornecedores = document.querySelector('#fornecedor');

                    sistema.each(fornecedores, function (index, fornecedor) {
                        select_fornecedores.appendChild(sistema.gerar_option(fornecedor._id.$oid, fornecedor.nome_usuario));
                    });
                });
            }

            function pesquisar_produto() {
                sistema.request.post('/produtos.php', {
                    'rota': 'pesquisar_produto',
                    'codigo_produto': CODIGO_PRODUTO
                }, function (retorno) {
                    let produto = retorno.dados;
                    let tipo = 1;

                    if (produto.tipo_produto == false) {
                        tipo = 0;
                    }

                    document.querySelector('#nome_produto').value = produto.nome_produto;
                    document.querySelector('#fornecedor').value = produto.fornecedor.$oid;
                    document.querySelector('#valor_venda').value = produto.valor_venda;
                    document.querySelector('#valor_custo').value = produto.valor_custo;
                    document.querySelector('#quantidade_alerta').value = produto.quantidade_alerta;
                    document.querySelector('#status_produto').value = produto.status;
                    document.querySelector('#tipo_produto').value = tipo;
                    document.querySelector('#unidade_medida').value = produto.unidade_medida;
                    document.querySelector('#data_cadastro').value = sistema.retornar_data(produto.data_cadastro, 'AMERICANO');
                    tinymce.get('editor').setContent(produto.descricao);
                    document.querySelector('#codigo_barras').value = produto.codigo_barras;
                });
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <div class="card-title">Cadastro de Produtos</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <label class="text">Nome do produto</label>
                                        <input type="text" class="form-control text-uppercase" placeholder="Nome do Produto"
                                            id="nome_produto">
                                    </div>
                                    <div class="col-3 text-center">
                                        <label class="text">Fornecedor</label>
                                        <select class="form-control" id="fornecedor">
                                            <option value="">Selecione uma opção</option>
                                        </select>
                                    </div>
                                    <div class="col-1 text-center">
                                        <label class="text">Valor de Venda</label>
                                        <input type="text" class="form-control" placeholder="Valor de Venda"
                                            id="valor_venda" sistema-mask="moeda">
                                    </div>
                                    <div class="col-1 text-center">
                                        <label class="text">Valor de Custo</label>
                                        <input type="text" class="form-control" placeholder="Valor de Custo"
                                            id="valor_custo" sistema-mask="moeda">
                                    </div>
                                    <div class="col-1 text-center">
                                        <label class="text">Quantidade Alerta</label>
                                        <input type="text" class="form-control" placeholder="Quantidade Alerta"
                                            id="quantidade_alerta" sistema-mask="codigo">
                                    </div>
                                    <div class="col-1 text-center">
                                        <label class="text">Status</label>
                                        <select class="form-control" id="status_produto">
                                            <option value="">Selecione uma opção</option>
                                            <option value="true">ATIVO</option>
                                            <option value="false">INATIVO</option>
                                        </select>
                                    </div>
                                    <div class="col-1 text-center">
                                        <label class="text">Tipo</label>
                                        <select class="form-control" id="tipo_produto">
                                            <option value="">Selecione uma opção</option>
                                            <option value="1">PRODUTO</option>
                                            <option value="0">SERVIÇO</option>
                                        </select>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <div class="mb-3">
                                            <label class="text">Descrição do Produto</label>
                                            <textarea id="editor"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-2 text-center">
                                        <div class="mb-3">
                                            <label class="text">Código de Barras</label>
                                            <div class="position-relative">
                                                <input type="text" class="form-control" id="codigo_barras">
                                                <button type="button"
                                                    class="btn btn-sm btn-dark position-absolute end-0 top-0 bottom-0 mx-2 my-1 d-inline-flex align-items-center">Gerar</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Unidade de Medida</label>
                                        <select class="form-control" id="unidade_medida">
                                            <option value="">Selecione uma opção</option>
                                            <option value="UN">UNIDADE</option>
                                            <option value="KG">QUILOGRAMA</option>
                                            <option value="L">LITRO</option>
                                        </select>
                                    </div>
                                    <div class="col-3 text-center">
                                        <label class="text">Data Cadastro</label>
                                        <input type="date" class="form-control" id="data_cadastro"
                                            value="<?php echo $data_hoje; ?>">
                                    </div>
                                </div>
                                <?php
                                include_once 'includes/botao_cadastro.php';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>

                tinymce.init({
                    selector: '#editor',
                    plugins: [
                        'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
                        'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen', 'insertdatetime',
                        'media', 'table', 'emoticons', 'help'
                    ],
                    toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | ' +
                        'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
                        'forecolor backcolor emoticons | help',
                    menu: {
                        favs: { title: 'My Favorites', items: 'code visualaid | searchreplace | emoticons' }
                    },
                    menubar: 'favs file edit view insert format tools table help',
                    content_css: 'css/content.css'
                });

                window.onload = function () {
                    pesquisar_fornecedores();

                    if (CODIGO_PRODUTO != '') {
                        pesquisar_produto();
                    }
                }
            </script>
            <?php
            include_once 'includes/footer.php';
});

/** 
 * Rota responsável por exibir a página de cadastro de imagens do produto. Ela inclui o arquivo de cabeçalho, define a data atual e renderiza o conteúdo HTML da página, que inclui um formulário para cadastro ou edição de imagens do produto. A página também contém scripts JavaScript para lidar com as interações do usuário, como salvar os dados da imagem, limpar o formulário e redirecionar para a página de listagem de produtos.
 */
router_add('cadastrar_imagem_produto', function () {
    include_once 'includes/head.php';
    ?>

            <?php
            include_once 'includes/footer.php';
});
?>