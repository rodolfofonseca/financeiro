<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/ItensExtratos.php';

router_add('salvar_dados', function(){
    $objeto_item_extrato = new ItensExtratos();

    echo json_encode((array) ['status' => (bool) $objeto_item_extrato->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('pesquisar_todos', function(){
    $objeto_item_extrato = new ItensExtratos();

    $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa']:'');

    $filtro_montando = (array) [];
    
    if($empresa != ''){
        array_push($filtro_montando, ['empresa', '===', model_id($empresa)]);
    }

    $filtro = (array) ['filtro' => (array) ['and' => $filtro_montando], 'ordenacao' => (array) ['nome_item_extrato' => (bool) true], 'limite' => (int) 0];

    echo json_encode((array) ['dados' => (array) $objeto_item_extrato->pesquisar_todos($filtro)], JSON_UNESCAPED_UNICODE);
});

router_add('pesquisar_item_extrato', function(){
    $objeto_item_extrato = new ItensExtratos();

    $codigo_item_extrato = (string) (isset($_REQUEST['codigo_item_extrato']) ? (string) $_REQUEST['codigo_item_extrato']:'');
    $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa']:'');

    $filtro_montando = (array) [];
    $retorno = (array) [];

    if($codigo_item_extrato != ''){
        array_push($filtro_montando, (array) ['_id', '===', model_id($codigo_item_extrato)]);
    }

    if($empresa != ''){
        array_push($filtro_montando, ['empresa', '===', model_id($empresa)]);
    }
    
    echo json_encode((array) ['dados' => (array) $objeto_item_extrato->pesquisar((array) ['and' => (array) $filtro_montando])]);
    exit;
});

router_add('index', function(){
    include_once 'includes/head.php';
    ?>
    <script>
        const EMPRESA = "<?php echo $codigo_empresa; ?>";
        function salvar_dados(){
            let codigo_item_extrato = document.querySelector('#codigo_item_extrato').value;
            let nome_item_extrato = document.querySelector("#nome_item_extrato").value;
            let tipo_item_extrato = document.querySelector('#tipo_item_extrato').value;

            let validacao = true;

            if(nome_item_extrato == ''){
                alerta_campo_vazio('NOME ITEM');
                validacao = false;
            }

            if(tipo_item_extrato == ''){
                alerta_campo_vazio('TIPO ITEM');
                validacao = false;
            }

            sistema.request.post('/item_extrato.php', {'rota':'salvar_dados', 'codigo_item_extrato':codigo_item_extrato, 'nome_item_extrato':nome_item_extrato, 'tipo_item_extrato':tipo_item_extrato, 'empresa':EMPRESA}, function(retorno){
                validar_retorno(retorno, '/item_extrato.php');
            });
        }

        function limpar_dados(){
            colocar_dados_editar('', '', '');
        }

        function voltar(){
            window.location.href = sistema.url('/item_extrato.php', {'rota':'index'});
        }

        function pesquisar_itens_extratos(){
            sistema.request.post('/item_extrato.php', {'rota':'pesquisar_todos', 'empresa':EMPRESA}, function(retorno){
                let item_extratos = retorno.dados;
                let tamanho_retorno = item_extratos.length;
                let tabela = document.querySelector('#tabela_item_extrato tbody');

                tabela = sistema.remover_linha_tabela(tabela);

                if(tamanho_retorno == 0){
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center'], 'NENHUM ITEM ENCONTRADO!', 'inner', true, 3));
                    tabela.appendChild(linha);
                }else{
                    sistema.each(item_extratos, function(index, item_extrato){
                        let linha = document.createElement('tr');

                        linha.appendChild(sistema.gerar_td(['text-center'], item_extrato.nome_item_extrato, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], item_extrato.tipo_item_extrato, 'inner'));

                        let botao = document.createElement('button');
                        botao.id = 'botao_editar_item_extrato_'+item_extrato._id.$oid;
                        botao.textContent = 'EDITAR';
                        botao.classList.add('btn');
                        botao.classList.add('btn-primary');
                        botao.dataset.bsToggle = "modal";
                        botao.dataset.bsTarget = "#modal_cadastro_item_holerite";

                        botao.addEventListener('click', function(){
                            colocar_dados_editar(item_extrato._id.$oid, item_extrato.nome_item_extrato, item_extrato.tipo_item_extrato);
                        });

                        linha.appendChild(sistema.gerar_td(['text-center'], botao, 'append'));

                        tabela.appendChild(linha);
                    });
                }
            });
        }

        function colocar_dados_editar(codigo, nome, tipo){
            document.querySelector('#codigo_item_extrato').value = codigo;
            document.querySelector('#nome_item_extrato').value = nome;
            document.querySelector('#tipo_item_extrato').value = tipo;
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
                        <button class="btn btn-primary d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#modal_cadastro_item_holerite">Cadastro Itens Holerites</button>
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
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-nowrap text-nowrap table-hover" id="tabela_item_extrato">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th scope="col">NOME</th>
                                                        <th scope="col">TIPO</th>
                                                        <th scope="col">AÇÃO</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="3" class="text-center">UTILZE O FILTRO PARA FACILITAR SUA PESQUISA!</td>
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
        <div class="modal fade" id="modal_cadastro_item_holerite" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myLargeModalLabel">Cadastro Item Holerite</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="codigo_item_extrato">
                        <div class="row">
                            <div class="col-6 text-center">
                                <label class="text">Nome Item</label>
                                <input type="text" class="form-control text-uppercase" id="nome_item_extrato">
                            </div>
                            <div class="col-6 text-center">
                                <label class="text">Tipo Item Holerite</label>
                                <select id="tipo_item_extrato" class="form-control">
                                    <option value="">Selecione Uma Opção</option>
                                    <option value="CREDITO">CRÉDITO</option>
                                    <option value="DEBITO">DEBITO</option>
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
            window.onload = function(){
                pesquisar_itens_extratos();
            }
        </script>
    <?php
    include_once 'includes/footer.php';
});
?>