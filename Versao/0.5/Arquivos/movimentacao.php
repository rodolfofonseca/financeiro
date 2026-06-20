<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Movimentacao.php';
require_once 'modelos/Contas.php';

include_once 'classes/PHPSpreadsheet/PHPSpreadsheet.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


/** 
 * TODO Rota responsável por salvar os dados da movimentação
 */
router_add('salvar_dados', function () {
    $objeto_movimentacao = new Movimentacao();

    echo json_encode((array) ['status' => (bool) $objeto_movimentacao->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

/** 
 * TODO Rota responsável por pesquisar as movimentações
 */
router_add('pesquisar_contas', function () {
    $objeto_movimentacao = new Movimentacao();

    echo json_encode((array) ['dados' => (array) $objeto_movimentacao->pesquisar_movimentacoes($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/**
 * TODO Rota responsável por excluir uma movimentação do banco de dados
 */
router_add('deletar_movimentacao', function () {
    $objeto_movimentacao = new Movimentacao();
    $codigo_movimentacao = (string) (isset($_REQUEST['codigo_movimentacao']) ? (string) $_REQUEST['codigo_movimentacao'] : '');
    $filtro = (array) [];
    $retorno = (bool) false;

    if ($codigo_movimentacao != '') {
        $filtro['filtro'] = (array) ['_id', '===', model_id($codigo_movimentacao)];
        $retorno = (bool) $objeto_movimentacao->deletar_movimentacao($filtro);
    }

    echo json_encode(['status' => (bool) $retorno]);
});

router_add('gerar_excell', function () {
    $objeto_movimentacao = new Movimentacao();
    $login_usuario = (string) (isset($_REQUEST['login_usuario']) ? (string) $_REQUEST['login_usuario'] : '');

    $retorno_movimentacaoo = (array) $objeto_movimentacao->pesquisar_movimentacoes($_REQUEST);
    $json_retorno = (array) ['status' => (bool) false, 'link' => (string) ''];

    if (empty($retorno_movimentacaoo) == true) {
        echo json_encode((array) $json_retorno, JSON_UNESCAPED_UNICODE);
    } else {
        $pasta = 'anexos/excell';

        if (is_dir($pasta) == false) {
            mkdir($pasta, 0777, true);
        }

        $spreadsheet = new Spreadsheet();
        $planilha = $spreadsheet->getActiveSheet();
        foreach (array('A', 'B', 'C', 'D', 'E') as $coluna) {
            $planilha->getColumnDimension($coluna)->setAutoSize(true);
        }

        $spreadsheet->getProperties()->setCreator("Usuario")->setLastModifiedBy(strval($login_usuario))->setTitle("MOVIMENTACAO");

        $linha = (int) 1;

        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getStyle('A' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('A' . $linha, 'NOME CONTA');
        $sheet->getStyle('B' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('B' . $linha, 'DESCRIÇÃO');
        $sheet->getStyle('C' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $linha, 'VALOR');
        $sheet->getStyle('D' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('D' . $linha, 'DATA');
        $sheet->getStyle('E' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('E' . $linha, 'TIPO LANÇAMENTO');

        foreach ($retorno_movimentacaoo as $movimentacao) {
            $linha++;
            $sheet->getStyle('A' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('A' . $linha, (string) $movimentacao['nome_conta']);

            $sheet->getStyle('B' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('B' . $linha, (string) $movimentacao['descricao']);

            $sheet->getStyle('C' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getCell('C' . $linha)->setValue((string) $movimentacao['valor_lancamento'])->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->getStyle('D' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('D' . $linha, (string) convert_date($movimentacao['data_lancamento'], 'd/m/Y H:i:s'));

            $sheet->getStyle('E' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('E' . $linha, (string) $movimentacao['tipo_lancamento']);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($pasta . '/relatorio_movimentacao.xlsx');
        echo json_encode([
            'status' => true,
            'resposta' => $pasta . '/relatorio_movimentacao.xlsx'
        ]);
    }
    exit;
});

/** 
 * ! Rota index, onde contém a tabela de pesquisa de movimentações
 */
router_add('index', function () {
    require_once 'includes/head.php';

    $data_inicio = $data->format('Y-m-01');
    $ultimo_dia = $data->format('Y-m-t');
    ?>
    <script>
        const DATA_INICIAL = "<?php echo $data_inicio; ?>";
        const DATA_FINAL = "<?php echo $ultimo_dia; ?>";
        const EMPRESA = "<?php echo $codigo_empresa; ?>";
        const LOGIN_USUARIO = "<?php echo $login_usuario; ?>";


        /** 
         * Função responsável por abrir o formulário para o cadastro de novas movimentações.
         * @param {string} codigo_movimentacao - Código da movimentação que será alterada no caso de uma alteração
        */
        function cadastro_movimentacao(codigo_movimentacao) {
            window.location.href = sistema.url('/movimentacao.php', {
                'rota': 'cadastro_movimentacao',
                'codigo_movimentacao': codigo_movimentacao
            });
        }

        function pesquisar_movimentacao() {
            let conta = document.querySelector('#conta').value;
            let tipo_lancamento = document.querySelector('#tipo_lancamento').value;
            let data_inicio = document.querySelector('#data_inicio').value;
            let data_final = document.querySelector('#data_final').value;

            Swal.fire({
                title: 'Carregando movimentações...',
                html: `
            <div style="width:100%; background:#e9ecef; border-radius:10px; overflow:hidden;">
                <div id="barra_progresso"
                    style="
                        width:0%;
                        height:25px;
                        background:#198754;
                        text-align:center;
                        line-height:25px;
                        color:#fff;
                        font-weight:bold;
                        transition: width .2s;
                    ">
                    0%
                </div>
            </div>

            <div id="texto_progresso" style="margin-top:10px;">
                Iniciando...
            </div>
        `,
                allowOutsideClick: false,
                showConfirmButton: false
            });

            sistema.request.post('/movimentacao.php', {
                'rota': 'pesquisar_contas',
                'conta': conta,
                'tipo_lancamento': tipo_lancamento,
                'data_inicio': data_inicio,
                'data_final': data_final,
                'empresa': EMPRESA
            }, function (retorno) {
                let movimentacoes = retorno.dados;
                let total = movimentacoes.length;
                let tabela = document.querySelector('#tabela_movimentacoes tbody');
                tabela = sistema.remover_linha_tabela(tabela);

                if (total == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], 'NENHUMA MOVIMENTAÇÃO ENCONTRADA COM OS FILTROS PASSADOS!', 'inner', true, '10'));
                    tabela.appendChild(linha);
                    Swal.fire({ icon: 'warning', title: 'Nenhuma movimentação encontrada' });
                    return;
                }

                let index = 0;

                function processarItem() {

                    if (index >= total) {
                        Swal.close();
                        return;
                    }

                    let movimentacao = movimentacoes[index];

                    let linha = document.createElement('tr');

                    linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], movimentacao.nome_conta, 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-start'], movimentacao.descricao, 'inner'));

                    if (movimentacao.tipo_lancamento == 'CREDITO' || movimentacao.tipo_lancamento == 'TRANSFERENCIA_CREDITO') {
                        linha.appendChild(sistema.gerar_td(['text-center', 'text-success', 'fw-bold'], sistema.number_format(movimentacao.valor_lancamento), 'inner'));
                    } else {
                        linha.appendChild(sistema.gerar_td(['text-center', 'text-danger', 'fw-bold'], sistema.number_format(movimentacao.valor_lancamento), 'inner'));
                    }

                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(movimentacao.data_lancamento, 'BRASIL', true), 'inner'));

                    if (movimentacao.tipo_lancamento == 'CREDITO' || movimentacao.tipo_lancamento == 'TRANSFERENCIA_CREDITO') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_lancamento_' + movimentacao._id.$oid, 'CREDITO', ['btn', 'btn-success'], function visualizar() { }), 'append'));
                    } else {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_lancamento_' + movimentacao._id.$oid, 'DEBITO', ['btn', 'btn-danger'], function visualizar() { }), 'append'));
                    }

                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_deletar_movimentacao_' + movimentacao._id.$oid, 'EXCLUIR', ['btn', 'btn-danger'], function deletar_movimentacao_botao() {
                        deletar_movimentacao(movimentacao._id.$oid);
                    }), 'append'));

                    tabela.appendChild(linha);

                    let percentual = Math.round(((index + 1) / total) * 100);
                    let barra = document.querySelector('#barra_progresso');
                    let texto = document.querySelector('#texto_progresso');
                    barra.style.width = percentual + '%';
                    barra.innerHTML = percentual + '%';

                    texto.innerHTML = 'Processando ' + (index + 1) + ' de ' + total;
                    index++;
                    setTimeout(processarItem, 1);
                }

                processarItem();

            });
        }

        /** 
         * Função responsável por deletar uma movimentação no banco de dados, caso o usuário selecione a opção confirmando a exclusão.
         * @param {string} codigo_movimentacao - Código da movimentação que será excluída.
        */
        function deletar_movimentacao(codigo_movimentacao) {
            Swal.fire({
                title: "Confirmar Exclusão!",
                text: "Excluir a movimentação não retornar o valor para a conta. Confirma a exclusão?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, deletar movimentação!"
            }).then((result) => {
                if (result.isConfirmed) {
                    sistema.request.post('/movimentacao.php', {
                        'rota': 'deletar_movimentacao',
                        'codigo_movimentacao': codigo_movimentacao
                    }, function (retorno) {
                        validar_retorno(retorno, '/movimentacao.php');
                    });
                }
            });
        }

        /** 
         * Função responsável por pesquisar as contas que estão cadastradas no banco de dados e retornar,
         * adicionando ao campo select
        */
        function pesquisa_contas_select() {
            let codigo_empresa = "<?php echo $_SESSION['codigo_empresa']; ?>";
            sistema.request.post('/contas.php', {
                'rota': 'pesquisar_contas',
                'empresa': codigo_empresa,
                'status': 'ATIVO'
            }, function (retorno) {
                let select = document.querySelector('#conta');
                let conta = retorno.dados;

                sistema.each(conta, function (index, contas) {
                    let option = sistema.gerar_option(contas._id.$oid, contas.nome_conta + ' | ' + contas.saldo_conta);
                    select.appendChild(option);
                });
            });
        }

        /** 
         * Função responsável por gerar um relatório do excell com as movimentações.
        */
        function gerar_excell() {
            let conta = document.querySelector('#conta').value;
            let tipo_lancamento = document.querySelector('#tipo_lancamento').value;
            let data_inicio = document.querySelector('#data_inicio').value;
            let data_final = document.querySelector('#data_final').value;

            let dados = { 'rota': 'gerar_excell', 'empresa': EMPRESA, 'conta': conta, 'data_inicio': data_inicio, 'data_final': data_final, 'tipo_lancamento': tipo_lancamento, 'login_usuario': LOGIN_USUARIO };

            sistema.request.post('/movimentacao.php', dados, function (retorno) {
                if (retorno.status == false) {
                    this.Swal.fire('ATENÇÃO', 'Nenhum movimentação encontrada, com os filtros passsados!', 'warning');
                } else {
                    sistema.download('', retorno.resposta);
                }
            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Movimentação</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center"
                            onclick="cadastro_movimentacao('');">
                            Cadastrar Movimentação
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Pesquisa de Movimentação
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3 text-center">
                                    <label class="text">Conta</label>
                                    <select id="conta" class="form-control">
                                        <option value="TODOS">Todas as Contas</option>
                                    </select>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Tipo Lançamento</label>
                                    <select class="form-control" id="tipo_lancamento">
                                        <option value="TODOS">Todos os Lançamentos</option>
                                        <option value="CREDITO">CRÉDITO</option>
                                        <option value="DEBITO">DÉBITO</option>
                                        <option value="TRANSFERENCIA">TRANSFERÊNCIA</option>
                                    </select>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Data Inícial</label>
                                    <input type="date" id="data_inicio" class="form-control">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Data Final</label>
                                    <input type="date" id="data_final" class="form-control">
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-3 push-6">
                                    <button class="btn btn-light w-100 text-uppercase" onclick="gerar_excell();">Gerar
                                        Excell</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn btn-secondary w-100 text-uppercase"
                                        onclick="pesquisar_movimentacao();">Pesquisar</button>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_movimentacoes">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-center">Nome Conta</th>
                                                    <th scope="col" class="text-center">Descrição</th>
                                                    <th scope="col" class="text-center">Valor</th>
                                                    <th scope="col" class="text-center">Data</th>
                                                    <th scope="col" class="text-center">Tipo</th>
                                                    <th scope="col" class="text-center">Ação</th>
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
        <script>

            window.onload = function () {
                document.querySelector('#data_inicio').value = DATA_INICIAL;
                document.querySelector('#data_final').value = DATA_FINAL;
                pesquisar_movimentacao();
                pesquisa_contas_select();
            }
        </script>
        <?php
        require_once 'includes/footer.php';
        exit;
});

/** 
 * ! Rota de cadastro de movimentações.
 */
router_add('cadastro_movimentacao', function () {
    require_once 'includes/head.php';
    $hoje = $data->format('Y-m-d');
    ?>
        <script>
            const HOJE = "<?php echo $hoje; ?>";
            const EMPRESA = "<?php echo $codigo_empresa; ?>";

            function pesquisar_contas() {
                sistema.request.post('/contas.php', {
                    'rota': 'pesquisar_contas',
                    'status': 'ATIVO',
                    'empresa': EMPRESA
                }, function (retorno) {
                    let contas = retorno.dados;
                    let select = document.querySelector('#conta');
                    let select_destino = document.querySelector('#conta_destino');

                    sistema.each(contas, function (index, conta) {
                        let option = sistema.gerar_option(conta._id.$oid, conta.nome_conta + ' | ' + conta.saldo_conta);
                        select.appendChild(option);
                    });
                    sistema.each(contas, function (index, conta) {
                        let option = sistema.gerar_option(conta._id.$oid, conta.nome_conta + ' | ' + conta.saldo_conta);
                        select_destino.appendChild(option);
                    });
                });
            }

            function salvar_dados() {
                // let conta = document.querySelector('#conta');
                // let descricao = document.querySelector('#descricao');
                // let data_lancamento = document.querySelector('#data_lancamento');
                // let tipo_lancamento = document.querySelector('#tipo_lancamento');
                // let valor_lancamento = document.querySelector('#valor_lancamento');
                // let conta_destino = document.querySelector('#conta_destino');
                let conta = document.querySelector('#conta').value;
                let descricao = document.querySelector('#descricao').value;
                let data_lancamento = document.querySelector('#data_lancamento').value;
                let tipo_lancamento = document.querySelector('#tipo_lancamento').value;
                let valor_lancamento = document.querySelector('#valor_lancamento').value;
                let conta_destino = document.querySelector('#conta_destino').value;

                // if(conta.value == ''){
                //     validar_campo(conta, document.querySelector('#conta_validacao'), false, 'Selecione uma conta de origem!');
                // }else{
                //     validar_campo(conta, document.querySelector('#conta_validacao'));
                // }

                let valida_conta = true;
                let valida_descricao = true;
                let valida_tipo_lancamento = true;
                let valida_valor_lancamento = true;

                if (conta == '') {
                    alerta_campo_vazio('CONTA');
                    valida_conta = false;
                }

                if (descricao == '') {
                    alerta_campo_vazio('DESCRIÇÃO');
                    valida_descricao = false;
                }

                if (tipo_lancamento == '') {
                    alerta_campo_vazio('TIPO LANÇAMENTO');
                    valida_tipo_lancamento = false;
                }

                if (valor_lancamento == '') {
                    alerta_campo_vazio('VALOR');
                    valida_valor_lancamento = false;
                }

                if (valida_conta == true && valida_descricao == true && valida_tipo_lancamento == true && valida_valor_lancamento == true) {

                    if (conta_destino != '') {
                        sistema.request.post('/movimentacao.php', {
                            'rota': 'salvar_dados',
                            'conta': conta,
                            'descricao': descricao,
                            'data_lancamento': data_lancamento,
                            'tipo_lancamento': tipo_lancamento,
                            'valor_lancamento': valor_lancamento,
                            'empresa': EMPRESA,
                            'conta_destino': conta_destino
                        }, function (retorno) {
                            validar_retorno(retorno, '/movimentacao.php');
                        });
                    } else {
                        sistema.request.post('/movimentacao.php', {
                            'rota': 'salvar_dados',
                            'conta': conta,
                            'descricao': descricao,
                            'data_lancamento': data_lancamento,
                            'tipo_lancamento': tipo_lancamento,
                            'valor_lancamento': valor_lancamento,
                            'empresa': EMPRESA
                        }, function (retorno) {
                            validar_retorno(retorno, '/movimentacao.php');
                        });
                    }
                }

            }

            function limpar_dados() {
                document.querySelector('#conta').value = '';
                document.querySelector('#conta_destino').value = '';
                document.querySelector('#tipo_lancamento').value = '';
                document.querySelector('#data_lancamento').value = HOJE;
                document.querySelector('#descricao').value = '';
                document.querySelector('#valor_lancamento').value = 0;
            }

            function voltar() {
                window.location.href = sistema.url('/movimentacao.php', {
                    'rota': 'index'
                });
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Cadastro Movimentação
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-4 text-center">
                                    <label class="text">Conta Origem</label>
                                    <select class="form-control" id="conta">
                                        <option value="">Selecione uma opção</option>
                                    </select>
                                    <div class="invalid-feedback text-start" id="conta_validacao" style="display: none;">Por
                                        favor, selecione uma conta!</div>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Tipo de Lançamento</label>
                                    <select class="form-control" id="tipo_lancamento">
                                        <option value="">Selecione uma Opção</option>
                                        <option value="CREDITO">CREDITO</option>
                                        <option value="DEBITO">DÉBITO</option>
                                        <option value="TRANSFERENCIA">TRANSFÊNCIA</option>
                                    </select>
                                    <div class="invalid-feedback text-start" id="tipo_lancamento_validacao"
                                        style="display: none;">Por favor, selecione um tipo de lançamento!</div>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Valor Lançamento</label>
                                    <input type="text" class="form-control" id="valor_lancamento" sistema-mask="moeda">
                                    <div class="invalid-feedback text-start" id="valor_lancamento_validacao"
                                        style="display: none;">Por favor, informe um valor válido!</div>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Data Lançamento</label>
                                    <input type="date" class="form-control" id="data_lancamento">
                                    <div class="invalid-feedback text-start" id="data_lancamento_validacao"
                                        style="display: none;">Por favor, selecione uma data válida!</div>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Tipo de Lançamento</label>
                                    <select class="form-control" id="conta_destino">
                                        <option value="">Selecione uma Opção</option>
                                    </select>
                                    <div class="invalid-feedback text-start" id="conta_destino_validacao"
                                        style="display: none;">Por favor, selecione uma conta de destino!</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-center">
                                    <label class="text">Descrição</label>
                                    <textarea id="descricao" class="form-control text-uppercase"></textarea>
                                    <div class="invalid-feedback text-start" id="descricao_validacao"
                                        style="display: none;">Por favor, informe uma descrição válida!</div>
                                </div>
                            </div>
                            <br />
                            <?php require_once 'includes/botao_cadastro.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = function () {
                    document.querySelector('#data_lancamento').value = HOJE;
                    pesquisar_contas();
                }
            </script>
            <?php
            require_once 'includes/footer.php';
            exit;
});
?>