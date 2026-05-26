<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Contas.php';
require_once 'modelos/Movimentacao.php';
require_once 'modelos/ContasContabeis.php';

include_once 'classes/PHPSpreadsheet/PHPSpreadsheet.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/** 
 * TODO Rota responsável por salvar os dados no banco de dados, seja ela alteração ou inserção de novas contas.
 * 
 */
router_add('salvar_dados', function () {
    $objeto_contas = new Contas();

    echo json_encode((array) ['status' => (bool) $objeto_contas->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/**
 * TODO Rota responsável por pesquisar os dados no banco de dados, pesquisa todas as contas encontradas com os filtros que foram passados
 */
router_add('pesquisar_contas', function () {
    $objeto_conta = new Contas();

    echo json_encode(['dados' => (array) $objeto_conta->pesquisar_contas($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

/** 
 * TODO Rota responsável por pesquisar apenas uma conta no banco de dados
 */
router_add('pesquisa_conta', function () {
    $objeto_conta = new Contas();
    $codigo_conta = (string) (isset($_REQUEST['codigo_conta']) ? (string) $_REQUEST['codigo_conta'] : '');
    $retorno = (array) [];

    if ($codigo_conta != '') {
        $retorno = (array) $objeto_conta->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_conta)]]);
    }

    echo json_encode((array) ['dados' => (array) $retorno], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * TODO Rota responsável por deletar a conta do banco de dados, caso o usuário deseje realmente excluir, mantendo a movimentação que já foi lançado
 */
router_add('deletar_conta', function () {
    $objeto_conta = new Contas();

    $codigo_conta = (string) (isset($_REQUEST['codigo_conta']) ? (string) $_REQUEST['codigo_conta'] : '');
    $retorno_operacao = (bool) false;

    if ($codigo_conta != '') {
        $retorno_operacao = (bool) $objeto_conta->deletar_conta($codigo_conta);
    }

    echo json_encode((array) ['status' => (bool) $retorno_operacao], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * TODO Rota responsável por pesquisar as informações da conta no banco de dados e montar o relatório em excell
 */
router_add('gerar_excell', function (){
    $objeto_contas = new Contas();
    
    $retorno_contas = $objeto_contas->pesquisar_contas($_REQUEST);
    
    $visulizar_movimentacao = (bool) (isset($_REQUEST['visualizar_movimentacao']) ? (bool) filter_var($_REQUEST['visualizar_movimentacao'], FILTER_VALIDATE_BOOLEAN) : false);
    $modulo_contabil = (bool) (isset($_REQUEST['modulo_contabil']) ? (bool) filter_var($_REQUEST['modulo_contabil'], FILTER_VALIDATE_BOOLEAN) : false);
    $login_usuario = (string) (isset($_REQUEST['login_usuario']) ? (string) strtoupper($_REQUEST['login_usuario']) : '');
    
    if (empty($retorno_contas) == true) {
        echo json_encode((array) ['status' => (bool) false, 'link' => (string) ''], JSON_UNESCAPED_UNICODE);
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

        $spreadsheet->getProperties()->setCreator("Usuario")->setLastModifiedBy(strval($login_usuario))->setTitle("RELATORIO");

        $negrito = (array) ['font' => ['bold' => true]];
        $borda = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ],
        ];

        $linha = (int) 1;

        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($retorno_contas as $contas) {
            if ($linha == 1 || $visulizar_movimentacao == true) {

                $sheet->getStyle('A' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $linha)->getFont()->setBold(true);
                $sheet->setCellValue('A' . $linha, 'NOME');
                $sheet->getStyle('B' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $linha)->getFont()->setBold(true);
                $sheet->setCellValue('B' . $linha, 'DESCRIÇÃO');
                $sheet->getStyle('C' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $linha)->getFont()->setBold(true);
                $sheet->setCellValue('C' . $linha, 'STATUS');
                $sheet->getStyle('D' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $linha)->getFont()->setBold(true);
                $sheet->setCellValue('D' . $linha, 'SALDO');

                if ($modulo_contabil == true) {
                    $sheet->getStyle('E' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('E' . $linha)->getFont()->setBold(true);
                    $sheet->setCellValue('E' . $linha, 'CONTA CONTABIL');
                }
            }

            $linha++;
            $sheet->getStyle('A' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('A' . $linha, (string) $contas['nome_conta']);
            
            $sheet->getStyle('B' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('B' . $linha, (string) $contas['descricao']);
            
            $sheet->getStyle('C' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('C' . $linha, (string) $contas['status']);
            
            $sheet->getStyle('D' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            if($contas['saldo_conta'] <= 0){
                $sheet->getStyle('D' . $linha)->getFont()->setBold(true);
            }

            $sheet->getCell('D' . $linha)->setValue((string) $contas['saldo_conta'])->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');

            if ($modulo_contabil == true) {
                $sheet->getStyle('E' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->setCellValue('E' . $linha, (string) $contas['modulo_contabil']['conta_contabil']);
            }

            

            if ($visulizar_movimentacao == true) {
                $linha = $linha + 2;

                $sheet->getStyle('A' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $linha)->getFont()->setBold(true);
                $sheet->setCellValue('A' . $linha, 'DESCRIÇÃO');
                
                $sheet->getStyle('B' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $linha)->getFont()->setBold(true);
                $sheet->setCellValue('B' . $linha, 'VALOR');
                
                $sheet->getStyle('C' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $linha)->getFont()->setBold(true);
                $sheet->setCellValue('C' . $linha, 'DATA');
                
                $sheet->getStyle('D' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $linha)->getFont()->setBold(true);
                $sheet->setCellValue('D' . $linha, 'TIPO LANÇAMENTO');

                $objeto_movimentacao = new Movimentacao();

                $retorno_movimentacao = $objeto_movimentacao->pesquisar_movimentacoes($_REQUEST);

                if (empty($retorno_movimentacao) == false) {
                    foreach ($retorno_movimentacao as $movimentacao) {
                        $linha++;

                        $sheet->getStyle('A' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->setCellValue('A' . $linha, (string) $movimentacao['descricao']);
                        
                        $sheet->getStyle('B' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getCell('B' . $linha)->setValue((string) $movimentacao['valor_lancamento'])->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
                        
                        $sheet->getStyle('C' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->setCellValue('C' . $linha, (string) convert_date($movimentacao['data_lancamento']));
                        
                        $sheet->getStyle('D' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->setCellValue('D' . $linha, (string) $movimentacao['tipo_lancamento']);
                    }
                    $linha = $linha + 2;
                }
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($pasta . '/relatorio_contas.xlsx');
        echo json_encode([
            'status' => true,
            'resposta' => $pasta . '/relatorio_contas.xlsx'
        ]);
    }

    exit;
});

/** 
 * ! Rota responsável por abrir o modal onde o usuário pode Fazer o download dos lançamentos realizados na conta
 */
router_add('relatorio_download', function () {
    require_once 'includes/head_sem_menu.php';

    $objeto_conta = new Contas();
    $objeto_movimentacao = new Movimentacao();

    $codigo_conta = (string) (isset($_REQUEST['codigo_conta']) ? (string) $_REQUEST['codigo_conta'] : '');
    $nome_conta = (string) '';
    $saldo_conta = (string) '';
    $saldo_boolean = (bool) false;
    $retorno_conta = (array) [];
    $retorno_lancamentos = (array) [];

    if ($codigo_conta != '') {
        $retorno_conta = (array) $objeto_conta->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_conta)]]);

        if (empty($retorno_conta) == false) {
            if (array_key_exists('nome_conta', $retorno_conta) == true) {
                $nome_conta = (string) $retorno_conta['nome_conta'];
            }

            if (array_key_exists('saldo_conta', $retorno_conta) == true) {
                $saldo_conta = (string) formatar_numero($retorno_conta['saldo_conta'], 2, ',', '.');

                if ($retorno_conta['saldo_conta'] > 0) {
                    $saldo_boolean = (bool) true;
                } else {
                    $saldo_boolean = (bool) false;
                }
            }
        }

        $filtro = (array) ['filtro' => (array) ['conta', '===', model_id($codigo_conta)], 'ordenacao' => (array) ['data_lancamento' => (bool) true], 'limite' => (int) 0];

        $retorno_lancamentos = (array) $objeto_movimentacao->pesquisar_todos((array) $filtro);
    }
?>
    <div class="container mt-4">
        <button class="btn btn-primary no-print" onclick="window.print()">Imprimir</button>
        <h4>Conta Bancária</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nome da Conta</th>
                    <th>Saldo Atual</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    echo '<td>' . $nome_conta . '</td>';

                    if ($saldo_boolean == true) {
                        echo '<td class="text-success fw-bold">R$ ' . $saldo_conta . '</td>';
                    } else {
                        echo '<td class="text-danger fw-bold">R$ ' . $saldo_conta . '</td>';
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="container mt-4">
        <h4>Lançamentos</h4>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Tipo</th>
                    <th class="text-end">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($retorno_lancamentos) == true) {
                    echo '<tr><td colspan="10" class="text-center">NENHUM LANÇAMENTO ENCONTRADO</td></tr>';
                } else {
                    foreach ($retorno_lancamentos as $lancamento) {
                        echo '<tr>';
                        echo '<td>' . convert_date($lancamento['data_lancamento'], 'd/m/Y') . '</td>';
                        echo '<td>' . $lancamento['descricao'] . '</td>';

                        if ($lancamento['tipo_lancamento'] == 'CREDITO') {
                            echo '<td><span class="badge bg-success">CRÉDITO</span></td>';
                            echo '<td><span class="text-end text-success">+ R$ ' . formatar_numero($lancamento['valor_lancamento'], 2, ',', '.') . '</span></td>';
                        } else {
                            echo '<td><span class="badge bg-danger">DÉBITO</span></td>';
                            echo '<td><span class="text-end text-danger">- R$ ' . formatar_numero($lancamento['valor_lancamento'], 2, ',', '.') . '</span></td>';
                        }
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
<?php

    require_once 'includes/footer_sem.php';
    exit;
});

/** 
 * ! Rota index, onde normalmente fica a tabela com os as informações pesquisadas
 */
router_add('index', function () {
    require_once 'includes/head.php';
?>
    <script>
        const CODIGO_EMPRESA = "<?php echo $_SESSION['codigo_empresa']; ?>";
        const MODULO_CONTABIL = <?php echo $_SESSION['modulo_contabil'] ? 'true' : 'false'; ?>;
        const LOGIN_USUARIO = "<?php echo $login_usuario; ?>";

        /**
         * Função responsável por abrir o formulário de cadastro de novas contas
         * @param {string} codigo_conta - Código da conta que será editada
         */
        function cadastro_contas(codigo_conta) {
            window.location.href = sistema.url('/contas.php', {
                'rota': 'cadastro_contas',
                'codigo_conta': codigo_conta
            });
        }

        /** 
         * Função responsável por montar o filtro de pesquisa de contas, pesquisar e colocar o resultado na tabela.
         */
        function pesquisar_contas() {
            let nome_conta = document.querySelector('#nome_conta').value;
            let status = document.querySelector("#status_conta").value;
            let descricao = document.querySelector('#descricao').value;
            sistema.request.post('/contas.php', {
                'rota': 'pesquisar_contas',
                'empresa': CODIGO_EMPRESA,
                'nome_conta': nome_conta,
                'descricao': descricao,
                'status': status,
                'modulo_contabil': MODULO_CONTABIL
            }, function(retorno) {
                let contas = retorno.dados;
                let tamanho_retorno = contas.length;
                let tabela = document.querySelector('#tabela_contas tbody');

                tabela = sistema.remover_linha_tabela(tabela);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');

                    linha.appendChild(sistema.gerar_td(['text-center'], 'NENHUMA CONTA ENCONTRADA COM OS FILTROS PASSADOS', 'inner', true, 10));
                    tabela.appendChild(linha);
                } else {
                    sistema.each(contas, function(index, conta) {
                        let linha = document.createElement('tr');

                        linha.appendChild(sistema.gerar_td(['text-start'], conta.nome_conta, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-start'], conta.descricao, 'inner'));

                        if (conta.saldo_conta <= 0) {
                            linha.appendChild(sistema.gerar_td(['text-center', 'text-danger', 'fw-bold'], sistema.number_format(conta.saldo_conta), 'inner'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center', 'text-success', 'fw-bold'], sistema.number_format(conta.saldo_conta), 'inner'));
                        }

                        if (MODULO_CONTABIL == 'true' || MODULO_CONTABIL == true) {
                            linha.appendChild(sistema.gerar_td(['text-center'], conta.modulo_contabil.conta_contabil, 'inner'));
                        }

                        if (conta.status == 'ATIVO') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_' + conta._id.$oid, 'ATIVO', ['btn', 'btn-outline-success'], function visualizar() {}), 'append'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_' + conta._id.$oid, 'INATIVO', ['btn', 'btn-outline-danger'], function visualizar() {}), 'append'));
                        }

                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_downlaods_' + conta._id.$oid, 'DOWNLOAD', ['btn', 'btn-primary'], () => {
                            abrir_modal_download(conta._id.$oid);
                        }), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_excluir_conta_' + conta._id.$oid, 'EXCLUIR', ['btn', 'btn-danger'], () => {
                            deletar_conta(conta._id.$oid);
                        }), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_visualizar_' + conta._id.$oid, 'EDITAR', ['btn', 'btn-secondary'], () => {
                            cadastro_contas(conta._id.$oid);
                        }), 'append'));

                        tabela.appendChild(linha);
                    });
                }
            });
        }

        /**
         * Função responsável por perguntar ao usuário se ele deseja excluir mesmo a conta, caso o resultado seja positivo exclui a conta do banco de dados
         * @param {string} codigo_conta - Código da contaa que será deletada 
         * */
        function deletar_conta(codigo_conta) {
            Swal.fire({
                title: "Tem certeza?",
                text: "A exclusão é irreversível! E não exclui as movimentações já realizadas! E mesmo recadastrando a conta com o mesmo nome, não tem como revincular as movimentações",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sim, deletar conta!"
            }).then((result) => {
                if (result.isConfirmed) {
                    sistema.request.post('/contas.php', {
                        'rota': 'deletar_conta',
                        'codigo_conta': codigo_conta
                    }, (retorno) => {
                        if (retorno.status == true) {
                            validar_retorno(retorno, '/contas.php');
                        } else {
                            Swal.fire({
                                title: "Erro ao deletar!",
                                text: "Erro ao deletar a conta.",
                                icon: "error"
                            });
                        }
                    });

                }
            });
        }

        /**
         * Função responsável por abrir o formulário de downlaod de movimentações, onde o usuário pode baixar ou realizar a impressão
         * @param {string} codigo_conta - Código da conta que deseja visualizar as movimentações.
         * 
         */
        function abrir_modal_download(codigo_conta) {
            let url = sistema.url('/contas.php', {
                'rota': 'relatorio_download',
                'codigo_conta': codigo_conta
            });
            sistema.abrir_modal(1200, 500, url, 'Relatório de Movimentações');
        }

        function gerar_excell() {
            let nome_conta = document.querySelector('#nome_conta').value;
            let descricao = document.querySelector('#descricao').value;
            let status_conta = document.querySelector('#status_conta').value;
            let visualizar_movimentacao = document.querySelector('#visualizar_movimentacao').checked;

            let dados = {
                'rota': 'gerar_excell',
                'empresa': CODIGO_EMPRESA,
                'modulo_contabil': MODULO_CONTABIL,
                'nome_conta': nome_conta,
                'descricao': descricao,
                'status': status_conta,
                'visualizar_movimentacao': visualizar_movimentacao,
                'login_usuario': LOGIN_USUARIO,
                'pesquisar_conta': false
            }

            if (LOGIN_USUARIO == '') {
                this.Swal.fire('ATENÇÃO', 'Nome de usuário inválido!', 'warning');
                return;
            }

            sistema.request.post('/contas.php', dados, function(retorno) {
                if (retorno.status == false) {
                    this.Swal.fire('ATENÇÃO', 'Nenhum conta encontrada, com os filtros passsados!', 'warning');
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
                    <h6>Contas</h6>
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
                            <div class="card-title">
                                Pesquisa de Contas
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <label class="text">Nome da Conta</label>
                                    <input type="text" class="form-control text-uppercase" placeholder="Nome Conta" id="nome_conta">
                                </div>
                                <div class="col-6 text-center">
                                    <label class="text">Status</label>
                                    <select class="form-control" id="status_conta">
                                        <option value="TODOS">TODOS</option>
                                        <option value="ATIVO">ATIVO</option>
                                        <option value="INATIVO">INATIVO</option>
                                    </select>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-12">
                                    <textarea class="form-control text-uppercase" id="descricao" placeholder="Descrição da conta"></textarea>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-2 text-center push-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="visualizar_movimentacao">
                                        <label class="form-check-label" for="visualizar_movimentacao">Visualizar Movimentação</label>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <button class="btn btn-light w-100 text-uppercase" onclick="gerar_excell();">Gerar Excell</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn btn-secondary w-100 text-uppercase" onclick="pesquisar_contas();">Pesquisar</button>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_contas">
                                            <thead>
                                                <tr class="text-uppercase">
                                                    <th scope="col" class="text-center">Nome Conta</th>
                                                    <th scope="col" class="text-center">Descrição</th>
                                                    <th scope="col" class="text-center">Saldo</th>
                                                    <?php
                                                    if ($_SESSION['modulo_contabil'] == true) {
                                                        echo "<th scope='col' class='text-center'>conta contábil</th>";
                                                    }
                                                    ?>
                                                    <th scope="col" class="text-center">Status</th>
                                                    <th scope="col" class="text-center">Download</th>
                                                    <th scope="col" class="text-center">Excluir</th>
                                                    <th scope="col" class="text-center">Editar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="10" class="text-center">UTILIZE O FILTRO PARA FACILITAR A PESQUISA!</td>
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
            window.onload = function() {
                pesquisar_contas();
            }
        </script>
    <?php
    require_once 'includes/footer.php';
});

/** 
 * ! Rota responsável por cadastrar novas contas no banco de dados
 */
router_add('cadastro_contas', function () {
    $codigo_conta = (string) (isset($_REQUEST['codigo_conta']) ? (string) $_REQUEST['codigo_conta'] : '');

    require_once 'includes/head.php';
    ?>
        <script>
            const EMPRESA = "<?php echo $_SESSION['codigo_empresa']; ?>";
            const CODIGO_CONTA = "<?php echo $codigo_conta; ?>";

            function salvar_dados() {
                let nome_conta = document.querySelector('#nome_conta').value;
                let saldo_conta = document.querySelector("#saldo_conta").value;
                let descricao = document.querySelector("#descricao").value;
                let status_conta = document.querySelector('#status_conta').value;

                let valida_nome_conta = true;
                let valida_saldo_conta = true;
                let valida_descricao = true;

                if (nome_conta == '') {
                    alerta_campo_vazio('NOME CONTA');
                    valida_nome_conta = false;
                }

                if (valida_saldo_conta == '') {
                    alerta_campo_vazio('SALDO CONTA');
                    valida_saldo_conta = false;
                }

                if (valida_descricao == '') {
                    alerta_campo_vazio('DESCRIÇÃO CONTA');
                    valida_descricao = false;
                }

                if (valida_descricao == true && valida_nome_conta == true && valida_saldo_conta == true) {
                    sistema.request.post('/contas.php', {
                        'rota': 'salvar_dados',
                        'codigo_conta': CODIGO_CONTA,
                        'empresa': EMPRESA,
                        'nome_conta': nome_conta,
                        'descricao': descricao,
                        'saldo_conta': saldo_conta,
                        'status':status_conta
                    }, function(retorno) {
                        validar_retorno(retorno, '/contas.php');
                    });
                }

            }

            function voltar() {
                window.location.href = sistema.url('/contas.php', {
                    'rota': 'index'
                });
            }

            function limpar_dados() {
                document.querySelector('#nome_conta').value = '';
                document.querySelector('#saldo_conta').value = '';
                document.querySelector('#status_conta').value = '';
                document.querySelector('#descricao').value = '';
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">
                                    Cadastro de Contas
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <label class="text">Nome da Conta</label>
                                        <input type="text" class="form-control text-uppercase" id="nome_conta" placeholder="Nome da conta">
                                    </div>
                                    <div class="col-4">
                                        <label class="text">Saldo Conta</label>
                                        <input type="text" class="form-control" id="saldo_conta" sistema-mask="moeda" placeholder="Saldo da Conta">
                                    </div>
                                    <div class="col-4">
                                        <label class="text">Status</label>
                                        <select class="form-control" id="status_conta">
                                            <option value="">Selecione uma opção</option>
                                            <option value="ATIVO">ATIVO</option>
                                            <option value="INATIVO">INATIVO</option>
                                        </select>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-12">
                                        <label class="text">Descrição</label>
                                        <textarea class="form-control text-uppercase" id="descricao" placeholder="Informa a descrição"></textarea>
                                    </div>
                                </div>
                                <?php require_once 'includes/botao_cadastro.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = function() {
                    if (CODIGO_CONTA != '') {
                        sistema.request.post('/contas.php', {
                            'rota': 'pesquisa_conta',
                            'codigo_conta': CODIGO_CONTA
                        }, function(retorno) {
                            let conta = retorno.dados;

                            document.querySelector('#nome_conta').value = conta.nome_conta;
                            document.querySelector('#saldo_conta').value = conta.saldo_conta;
                            document.querySelector('#status_conta').value = conta.status;
                            document.querySelector('#descricao').value = conta.descricao;
                        });
                    }
                }
            </script>
        <?php
        require_once 'includes/footer.php';
    });
        ?>