<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Usuario.php';
include_once 'classes/PHPSpreadsheet/PHPSpreadsheet.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
/**
 * TODO Rota responsável por pesquisar os cliente no banco de dados,
 * TODO montar o arquivo do excell
 * TODO retornar ele para o front para iniciar o download
 */

router_add('gerar_excell', function () {
    $objeto_usuario = new Usuario();
    $objeto_contabil = new ContasContabeis();

    $modulo_contabil = (bool) (isset($_REQUEST['modulo_contabil']) ? (bool) filter_var($_REQUEST['modulo_contabil'], FILTER_VALIDATE_BOOLEAN) : false);

    $retorno_usuario = (array) $objeto_usuario->pesquisar_cliente($_REQUEST);

    if (empty($retorno_usuario) == false) {
        $pasta = 'anexos/excell';

        if (is_dir($pasta) == false) {
            mkdir($pasta, 0777, true);
        }

        $spreadsheet = new Spreadsheet();
        $planilha = $spreadsheet->getActiveSheet();

        foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I') as $coluna) {
            $planilha->getColumnDimension($coluna)->setAutoSize(true);
        }

        $spreadsheet->getProperties()->setCreator("Usuario")->setLastModifiedBy(strval('USUARIO'))->setTitle("RELATORIO");

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

        $sheet->getStyle('A' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('A' . $linha, 'NOME');
        $sheet->getStyle('B' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('B' . $linha, 'TELEFONE');
        $sheet->getStyle('C' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $linha, 'EMAIL');
        $sheet->getStyle('D' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('D' . $linha, 'TIPO');
        $sheet->getStyle('E' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('E' . $linha, 'DATA CADASTRO');

        if ($modulo_contabil == true) {
            $sheet->getStyle('F' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $linha)->getFont()->setBold(true);
            $sheet->setCellValue('F' . $linha, 'CONTA DO');
            $sheet->getStyle('G' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $linha)->getFont()->setBold(true);
            $sheet->setCellValue('G' . $linha, 'CONTA');
            $sheet->getStyle('H' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $linha)->getFont()->setBold(true);
            $sheet->setCellValue('H' . $linha, 'CONTA DO');
            $sheet->getStyle('I' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $linha)->getFont()->setBold(true);
            $sheet->setCellValue('I' . $linha, 'CONTA');
        }

        $linha++;

        foreach ($retorno_usuario as $usuario) {
            $sheet->getStyle('A' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('A' . $linha, (string) $usuario['nome_usuario']);
            $sheet->getStyle('B' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('B' . $linha, (string) $usuario['celular']);
            $sheet->getStyle('C' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('C' . $linha, (string) $usuario['email_usuario']);
            $sheet->getStyle('D' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('D' . $linha, (string) $usuario['tipo_usuario']);
            $sheet->getStyle('E' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->setCellValue('E' . $linha, (string) convert_date($usuario['data_cadastro']));

            if ($modulo_contabil == true) {
                $sheet->getStyle('F' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->setCellValue('F' . $linha, (string) $usuario['modulo_contabil']['local_conta_id_1']);
                $sheet->getStyle('G' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->setCellValue('G' . $linha, (string) $usuario['modulo_contabil']['conta_contabil_1']);

                $sheet->getStyle('H' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->setCellValue('H' . $linha, (string) $usuario['modulo_contabil']['local_conta_id_2']);
                $sheet->getStyle('I' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $sheet->setCellValue('I' . $linha, (string) $usuario['modulo_contabil']['conta_contabil_2']);
            }

            $linha++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($pasta . '/relatorio_clientes.xlsx');
        echo json_encode([
            'status' => true,
            'resposta' => $pasta . '/relatorio_clientes.xlsx'
        ]);
    } else {
        echo json_encode((array) ['status' => (bool) false, 'link' => (string) ''], JSON_UNESCAPED_UNICODE);
    }
});

/** 
 * TODO Rota responsável por salvar os dados no banco de dados
 */
router_add('salvar_dados', function () {
    $objeto_usuario = new Usuario();

    echo json_encode((array) ['status' => (bool) $objeto_usuario->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/**
 * TODO Rota responsável por pesquisar os clientes no banco de dados e retornar as informações,
 * TODO vinculando com o módulo contábil caso o sistema esteja configurado
 */
router_add('pesquisar_clientes', function () {
    $objeto_usuario = new Usuario();

    echo json_encode((array) ['dados' => (array) $objeto_usuario->pesquisar_cliente($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/**
 * TODO Rota responsável por pesquisar um cliente específico no banco de dados
 */
router_add('pesquisar_cliente', function () {
    $objeto_usuario = new Usuario();
    $codigo_cliente = (string) (isset($_REQUEST['codigo_cliente']) ? (string) $_REQUEST['codigo_cliente'] : '');
    $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) [], 'limite' => (int) 0];

    if ($codigo_cliente != '') {
        $filtro['filtro'] = (array) ['_id', '===', model_id($codigo_cliente)];
    }

    echo json_encode((array) ['dados' => (array) $objeto_usuario->pesquisar($filtro)], JSON_UNESCAPED_UNICODE);
    exit;
});

/**
 * TODO Rota responsável por alterar o status do usuário, juntamente com as contas vinculadas a ele
 */
router_add('alterar_status_usuario', function(){
    $objeto_usuario = new Usuario();

    echo json_encode(['status' => (bool) $objeto_usuario->alterar_status_usuario($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * !Rota responsável por aprensentar a tabela de pesquisa de clientes no banco de dados
 */
router_add('index', function () {
    require_once 'includes/head.php';
    ?>
    <script>
        const CODIGO_EMPRESA = "<?php echo $codigo_empresa; ?>";
        const MODULO_CONTABIL = "<?php echo $_SESSION['modulo_contabil'] ? 'true' : 'false'; ?>";

        /**
         * Função responsável por abrir a rota de cadastro de clientes, passando o código do cliente, 
         * se o código for != de vazio significa que é uma alteração 
         * @param {*} codigo_cliente 
         * */
        function cadastro_cliente(codigo_cliente) {
            window.location.href = sistema.url('/clientes.php', {
                'rota': 'cadastro_clientes',
                'codigo_clientes': codigo_cliente
            })
        }

        function gerar_excell() {
            let nome_cliente = document.querySelector('#nome_cliente').value;
            let email_cliente = document.querySelector('#email_cliente').value;
            let telefone_cliente = document.querySelector('#telefone_cliente').value;
            let tipo_usuario = document.querySelector('#tipo_usuario').value;

            let dados = { 'rota': 'gerar_excell', 'empresa': CODIGO_EMPRESA, 'nome_cliente': nome_cliente, 'email_cliente': email_cliente, 'telefone_cliente': telefone_cliente, 'tipo_usuario': tipo_usuario, 'modulo_contabil': MODULO_CONTABIL };

            sistema.request.post('/clientes.php', dados, function (retorno) {
                if (retorno.status == false) {
                    this.Swal.fire('ATENÇÃO', 'Nenhum CLIENTE/FORNECEDOR encontrado com os filtros passados', 'warning');
                } else {
                    sistema.download('', retorno.resposta);
                }
            });
        }

        /** 
         * Rota responsável por pesquisar os clientes no banco de dados e colocar as informações na tabela
        */
        function pesquisar_cliente() {
            let nome_cliente = document.querySelector('#nome_cliente').value;
            let email_cliente = document.querySelector('#email_cliente').value;
            let telefone_cliente = document.querySelector('#telefone_cliente').value;
            let tipo_usuario = document.querySelector('#tipo_usuario').value;
            let cpf_cnpj = document.querySelector('#cpf_cnpj').value;
            let status_usuario = document.querySelector('#status_usuario').value;
            let data_inicial = document.querySelector('#data_inicial').value;
            let data_final = document.querySelector('#data_final').value;

            barra_progresso('Carregando clientes/fornecedores...');

            sistema.request.post('/clientes.php', {
                'rota': 'pesquisar_clientes',
                'empresa': CODIGO_EMPRESA,
                'nome_usuario': nome_cliente,
                'celular': telefone_cliente,
                'email_usuario': email_cliente,
                'tipo_usuario': tipo_usuario,
                'modulo_contabil': MODULO_CONTABIL,
                'cpf_cnpj': cpf_cnpj,
                'data_inicial':data_inicial,
                'data_final':data_final,
                'status_usuario_pesquisa':status_usuario
            }, function (retorno) {
                let clientes = retorno.dados;
                let tamanho_retorno = clientes.length;
                let tabela = document.querySelector('#tabela_clientes tbody');
                let index = 0;

                tabela = sistema.remover_linha_tabela(tabela);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], 'NENHUM CLIENTE ENCONTRADO COM OS FILTROS PASSADOS!', 'inner', true, 10));
                    tabela.appendChild(linha);
                    Swal.fire({ icon: 'warning', title: 'Nenhuma cliente/fornecedor encontrado!' });
                    return;
                }

                function processar_item() {
                    if (index >= tamanho_retorno) {
                        Swal.close();
                        return;
                    }

                    let cliente = clientes[index];
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], sistema.cortar_string(cliente.nome_usuario, 30), 'inner', false, '', cliente.nome_usuario));

                    if (cliente.hasOwnProperty('cpf_cnpj') == true) {
                        linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], cliente.cpf_cnpj, 'inner'));
                    } else {
                        linha.appendChild(sistema.gerar_td(['text-start'], '', 'inner'));
                    }

                    linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], cliente.celular, 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], cliente.email_usuario, 'inner'));

                    if (cliente.tipo_usuario == 'CLIENTE') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_usuario_' + cliente._id.$oid, 'CLIENTE', ['btn', 'btn-outline-secondary'], () => { }), 'append'));
                    } else if (cliente.tipo_usuario == 'FORNECEDOR') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_usuario_' + cliente._id.$oid, 'FORNECEDOR', ['btn', 'btn-outline-primary'], () => { }), 'append'));
                    } else {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_usuario_' + cliente._id.$oid, 'FUNCIONÁRIO', ['btn', 'btn-outline-warning'], () => { }), 'append'));
                    }

                    if (cliente.hasOwnProperty('status_usuario') == true) {
                        if (cliente.status_usuario == true) {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_usuario_' + cliente._id.$oid, 'ATIVO', ['btn', 'btn-outline-success'], () => {alterar_status_usuario(cliente._id.$oid, cliente.status_usuario);}), 'append'));
                        } else if (cliente.status_usuario == false) {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_usuario_' + cliente._id.$oid, 'INATIVO', ['btn', 'btn-outline-danger'], () => {alterar_status_usuario(cliente._id.$oid, cliente.status_usuario); }), 'append'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_usuario_' + cliente._id.$oid, 'SEM STATUS', ['btn', 'btn-outline-secondary'], () => {alterar_status_usuario(cliente._id.$oid, false); }), 'append'));
                        }
                    } else {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_status_usuario_' + cliente._id.$oid, 'SEM STATUS', ['btn', 'btn-outline-secondary'], () => { alterar_status_usuario(cliente._id.$oid, false);}), 'append'));
                    }

                    if (MODULO_CONTABIL == 'true' || MODULO_CONTABIL == true) {
                        if (cliente.modulo_contabil.local_conta_id_1 == 'ATIVO_CIRCULANTE_CLIENTE') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_usuario_' + cliente._id.$oid, 'ATIVO CIRCULANTE CLIENTE', ['btn', 'btn-outline-dark'], () => { }), 'append'));
                        } else if (cliente.modulo_contabil.local_conta_id_1 == 'PASSIVO_CIRCULANTE_FORNECEDOR') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_usuario_' + cliente._id.$oid, 'PASSIVO CIRCULANTE FORNECEDOR', ['btn', 'btn-outline-primary'], () => { }), 'append'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));
                        }

                        linha.appendChild(sistema.gerar_td(['text-center'], cliente.modulo_contabil.conta_contabil_1, 'inner'));

                        if (cliente.modulo_contabil.local_conta_id_2 == 'ATIVO_NAO_CIRCULANTE_CLIENTE') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_usuario_' + cliente._id.$oid, 'ATIVO NÃO CIRCULANTE CLIENTE', ['btn', 'btn-outline-dark'], () => { }), 'append'));
                        } else if (cliente.modulo_contabil.local_conta_id_2 == 'PASSIVO_NAO_CIRCULANTE_FORNECEDOR') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_usuario_' + cliente._id.$oid, 'PASSIVO NÃO CIRCULANTE FORNECEDOR', ['btn', 'btn-outline-primary'], () => { }), 'append'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));
                        }

                        linha.appendChild(sistema.gerar_td(['text-center'], cliente.modulo_contabil.conta_contabil_2, 'inner'));
                    }

                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.retornar_data(cliente.data_cadastro), 'inner'));

                    let data_atualizacao = diferenca_datas(cliente.ultimo_login, 60);

                    if (data_atualizacao == false) {
                        linha.appendChild(sistema.gerar_td(['text-center', 'text-warning', 'fw-bold'], sistema.retornar_data(cliente.ultimo_login), 'inner'));
                    } else {
                        let data_atualizacao_90 = diferenca_datas(cliente.ultimo_login, 90);
                        if (data_atualizacao_90 == false) {
                            linha.appendChild(sistema.gerar_td(['text-center', 'text-danger', 'fw-bold'], sistema.retornar_data(cliente.ultimo_login), 'inner'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold', 'text-success'], sistema.retornar_data(cliente.ultimo_login), 'inner'));
                        }
                    }


                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_editar_usuario_' + cliente._id.$oid, 'EDITAR', ['btn', 'btn-secondary'], function editar_cliente() {
                        cadastro_cliente(cliente._id.$oid);
                    }), 'append'));

                    tabela.appendChild(linha);

                    atualizar_barra_progresso(index, tamanho_retorno, document.querySelector('#barra_progresso'), document.querySelector('#texto_progresso'));
                    index++;
                    setTimeout(processar_item, 1);
                }

                processar_item();
            });
        }

        /**
         * Função responsável por alterar o status do usuário
         * @param {*} codigo_usuario
         * @param {*} status_atual
         *  */
        function alterar_status_usuario(codigo_usuario, status_atual){
            let status_usuario = true;

            if(status_atual == true){
                status_usuario = false;
            }else{
                status_usuario = true;
            }

            sistema.request.post('/clientes.php', {'rota':'alterar_status_usuario', 'codigo_usuario':codigo_usuario, 'empresa':CODIGO_EMPRESA, 'status_usuario':status_usuario}, function(retorno){
                validar_retorno( retorno,'/clientes.php',);
            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                <div>
                    <h6>Clientes</h6>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center"
                            onclick="cadastro_cliente('');">
                            Cadastrar Cliente/Fornecedor
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">Pesquisa de Clientes/Fornecedores</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3 text-center">
                                    <label class="text">Nome Cliente/Fornecedor</label>
                                    <input type="text" class="form-control text-uppercase" id="nome_cliente"
                                        placeholder="NOME CLIENTE">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Email</label>
                                    <input type="mail" class="form-control" id="email_cliente" placeholder="EMAIL CLIENTE">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Telefone Cliente/Fornecedor</label>
                                    <input type="phone" class="form-control" id="telefone_cliente"
                                        placeholder="TELEFONE CLIENTE" sistema-mask="telefone">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">CPF/CNPJ</label>
                                    <input type="text" class="form-control" id="cpf_cnpj" placeholder="CPF/CNPJ CLIENTE" sistema-mask="cpf-cnpj">
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-3 text-center">
                                    <label class="text">Data Inícial</label>
                                    <input type="date" class="form-control" id="data_inicial">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Data Final</label>
                                    <input type="date" class="form-control" id="data_final">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Status</label>
                                    <select class="form-control select2" id="status_usuario">
                                        <option value="TODOS">Selecione uma opção</option>
                                        <option value="ATIVO">ATIVO</option>
                                        <option value="INATIVO">INATIVO</option>
                                    </select>
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Cliente/Fornecedor</label>
                                    <select class="form-control select2" id="tipo_usuario">
                                        <option value="">Selecione uma opção</option>
                                        <option value="TODOS">TODOS</option>
                                        <option value="CLIENTE">CLIENTE</option>
                                        <option value="FORNECEDOR">FORNECEDOR</option>
                                        <option value="CLIENTE_FORNECEDOR">CLIENTE E FORNECEDOR</option>
                                    </select>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-3 push-6">
                                    <button class="btn btn-light w-100 text-uppercase" onclick="gerar_excell();">Gerar
                                        excell</button>
                                </div>
                                <div class="col-3">
                                    <button class="btn btn-secondary w-100"
                                        onclick="pesquisar_cliente();">PESQUISAR</button>
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap text-nowrap table-hover" id="tabela_clientes">
                                            <thead>
                                                <tr class="text-center text-uppercase">
                                                    <th scope="col">Nome Cliente/Fornecedor</th>
                                                    <th scope="col">CPF/CNPJ</th>
                                                    <th scope="col">Telefone</th>
                                                    <th scope="col">Email</th>
                                                    <th scope="col">Tipo</th>
                                                    <th scope="col">Status</th>
                                                    <?php
                                                    if ($_SESSION['modulo_contabil'] == true) {
                                                        echo "<th scope='col'>Conta Do</th>";
                                                        echo "<th scope='col'>Conta</th>";
                                                        echo "<th scope='col'>Conta Do</th>";
                                                        echo "<th scope='col'>Conta</th>";
                                                    }
                                                    ?>
                                                    <th scope="col">Cadastro</th>
                                                    <th scope="col">Atualização</th>
                                                    <th scope="col">Editar</th>
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
            window.onload = () => {
                pesquisar_cliente();
            }
        </script>
        <?php
        require_once 'includes/footer.php';
});

/**
 * ! Rota responsável por cadastrar novos cliente sno banco de dados
 */
router_add('cadastro_clientes', function () {
    require_once 'includes/head.php';
    $codigo_cliente = (string) (isset($_REQUEST['codigo_clientes']) ? (string) $_REQUEST['codigo_clientes'] : '');
    ?>
        <script>
            let CODIGO_CLIENTE = "<?php echo $codigo_cliente; ?>";
            let CODIGO_EMPRESA = "<?php echo $codigo_empresa; ?>";

            /**
             * Função responsável por pesquisar o cep do cliente, utilizando a API ViaCep, e preencher os campos de endereço automaticamente
             * @param {*} valor 
             * */
            function pesquisar_cep(valor) {
                let cep = valor.replace(/\D/g, '');

                if (cep != '') {
                    let valida_cep = /^[0-9]{8}$/;
                    document.querySelector('#logradouro').value = '...';
                    document.querySelector('#bairro').value = '...';
                    document.querySelector('#uf').value = '...';
                    document.querySelector('#estado').value = '...';

                    var script = document.createElement('script');
                    script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';
                    document.body.appendChild(script);

                    if (valida_cep.test(cep)) { }
                } else {
                    Swal.fire({
                        title: "FALHA NA OPERAÇÃO!",
                        text: "Não é possível pesquisar um CEP vazio!",
                        icon: "error"
                    });
                }
            }

            /**
             * Função callback para tratar a resposta da API ViaCep
             * @param {*} conteudo 
             */
            function meu_callback(conteudo) {
                if (!("erro" in conteudo)) {
                    document.querySelector('#logradouro').value = (conteudo.logradouro);
                    document.querySelector('#bairro').value = (conteudo.bairro);
                    document.querySelector('#uf').value = (conteudo.localidade);
                    document.querySelector('#estado').value = (conteudo.uf);
                } else {
                    Swal.fire({
                        title: "FALHA NA OPERAÇÃO!",
                        text: "Cep não encontrado!",
                        icon: "error"
                    });
                }
            }

            /** 
             * Função responsável por salvar os dados do cliente no banco de dados, se o código do cliente for vazio, ele cadastra um novo cliente, se o código do cliente for diferente de vazio, ele atualiza o cliente existente
            */
            function salvar_dados() {
                let nome_usuario_objeto = document.querySelector('#nome_usuario');
                let email_usuario_objeto = document.querySelector('#email_usuario');
                let cpf_cnpj_objeto = document.querySelector('#cpf_cnpj');
                let celular_objeto = document.querySelector('#celular');
                let cep_objeto = document.querySelector('#cep');
                let logradouro_objeto = document.querySelector('#logradouro');
                let bairro_objeto = document.querySelector('#bairro');
                let numero_objeto = document.querySelector('#numero');
                let uf_objeto = document.querySelector('#uf');
                let estado_objeto = document.querySelector('#estado');
                let tipo_usuario_objeto = document.querySelector("#tipo_usuario");
                let status_usuario_objeto = document.querySelector("#status_usuario");

                let nome_usuario = '';
                let email_usuario = '';
                let cpf_cnpj = '';
                let celular = '';
                let logradouro = '';
                let cep = '';
                let bairro = '';
                let numero = '';
                let uf = '';
                let estado = '';
                let tipo_usuario = '';
                let status_usuario = '';

                let validacao = true;

                if (nome_usuario_objeto.value == '') {
                    validar_campo(nome_usuario_objeto, document.querySelector('#nome_usuario_validacao'), false, 'Informe o nome do cliente!');
                    validacao = false;
                } else {
                    validar_campo(nome_usuario_objeto, document.querySelector('#nome_usuario_validacao'), true);
                    nome_usuario = nome_usuario_objeto.value;
                }

                if (email_usuario_objeto.value == '') {
                    validar_campo(email_usuario_objeto, document.querySelector('#email_usuario_validacao'), false, 'Informe o email do cliente!');
                    validacao = false;
                } else {
                    validar_campo(email_usuario_objeto, document.querySelector('#email_usuario_validacao'), true);
                    email_usuario = email_usuario_objeto.value;
                }

                if (cpf_cnpj_objeto.value != '') {
                    let retorno_validacao = validarCpfCnpj(cpf_cnpj_objeto.value);
                    if (!retorno_validacao) {
                        validar_campo(cpf_cnpj_objeto, document.querySelector('#cpf_cnpj_validacao'), false, 'CPF/CNPJ inválido!');
                        validacao = false;
                    } else {
                        validar_campo(cpf_cnpj_objeto, document.querySelector('#cpf_cnpj_validacao'), true);
                        cpf_cnpj = cpf_cnpj_objeto.value;
                    }
                }

                celular = celular_objeto.value;
                logradouro = logradouro_objeto.value;
                cep = cep_objeto.value;
                bairro = bairro_objeto.value;
                uf = uf_objeto.value;
                estado = estado_objeto.value;
                tipo_usuario = tipo_usuario_objeto.value;
                status_usuario = status_usuario_objeto.value;
                numero = numero_objeto.value;

                let dados = { 'rota': 'salvar_dados', 'codigo_usuario': CODIGO_CLIENTE, 'empresa': CODIGO_EMPRESA, 'nome_usuario': nome_usuario, 'email_usuario': email_usuario, 'tipo_usuario': tipo_usuario, 'celular': celular, 'cep': cep, 'logradouro': logradouro, 'bairro': bairro, 'uf': uf, 'estado': estado, 'numero': numero, 'status_usuario': status_usuario, 'cpf_cnpj': cpf_cnpj, 'atualizacao_completa': false };

                if (validacao == true) {
                    sistema.request.post('/clientes.php', dados, function (retorno) {
                        validar_retorno(retorno, '/clientes.php');
                    });
                }
            }

            function limpar_dados() {
                document.querySelector('#nome_usuario').value = '';
                document.querySelector('#email_usuario').value = '';
                document.querySelector('#celular').value = '';
                document.querySelector('#cep').value = '';
                document.querySelector('#logradouro').value = '';
                document.querySelector('#numero').value = '';
                document.querySelector('#bairro').value = '';
                document.querySelector('#uf').value = '';
                document.querySelector('#estado').value = '';
                document.querySelector('#tipo_usuario').value = '';
            }

            /** 
             * Função responsável por voltar para a página de pesquisa de clientes
            */
            function voltar() {
                window.location.href = sistema.url('/clientes.php', {
                    'rota': 'index'
                });
            }

            /** 
             * Função responsável por pesquisar o cliente pelo código
            */
            function pesquisar_cliente() {
                sistema.request.post('/clientes.php', {
                    'rota': 'pesquisar_cliente',
                    'codigo_cliente': CODIGO_CLIENTE
                }, function (retorno) {
                    let cliente = retorno.dados;

                    document.querySelector('#nome_usuario').value = cliente.nome_usuario;
                    document.querySelector('#email_usuario').value = cliente.email_usuario;
                    document.querySelector('#celular').value = cliente.celular;
                    document.querySelector('#cep').value = cliente.cep;
                    document.querySelector('#logradouro').value = cliente.logradouro;
                    document.querySelector('#uf').value = cliente.uf;
                    document.querySelector('#estado').value = cliente.estado;
                    document.querySelector('#bairro').value = cliente.bairro;
                    document.querySelector('#numero').value = cliente.numero;
                    document.querySelector('#tipo_usuario').value = cliente.tipo_usuario;

                    if (cliente.hasOwnProperty('status_usuario') == true){
                        if(cliente.status_usuario == true){
                            document.querySelector('#status_usuario').value = 1;
                        }else{
                            document.querySelector('#status_usuario').value = 0;
                        }
                    }

                    if(cliente.hasOwnProperty('cpf_cnpj') == true){
                        document.querySelector('#cpf_cnpj').value = cliente.cpf_cnpj;
                    }

                    document.querySelector('#status_usuario').disabled = true;
                });
            }
        </script>
        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <div class="card-title">Cadastro de Clientes/Fornecedores</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <label class="text">Nome Cliente/Fornecedor</label>
                                        <input type="text" class="form-control text-uppercase"
                                            placeholder="Nome Cliente/Fornecedor" id="nome_usuario">
                                        <div class="invalid-feedback" id="nome_usuario_validacao">Informe o nome do cliente!
                                        </div>
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="text">Email</label>
                                        <input type="mail" class="form-control" placeholder="Email Cliente"
                                            id="email_usuario">
                                        <div class="invalid-feedback" id="email_usuario_validacao">Informe o email do
                                            cliente!</div>
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">CPF/CNPJ</label>
                                        <input type="text" class="form-control" placeholder="CPF/CNPJ Cliente" id="cpf_cnpj"
                                            sistema-mask="cpf-cnpj">
                                        <div class="invalid-feedback" id="cpf_cnpj_validacao">Informe o CPF/CNPJ do cliente!
                                        </div>
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Celular</label>
                                        <input type="phone" class="form-control" placeholder="telefone Cliente" id="celular"
                                            sistema-mask="telefone">
                                        <div class="invalid-feedback" id="celular_validacao">Informe o celular do cliente!
                                        </div>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-2">
                                        <label class="text">Cep</label>
                                        <input type="text" class="form-control" placeholder="Cep Cliente/Fornecedor"
                                            id="cep" onblur="pesquisar_cep(this.value);">
                                    </div>
                                    <div class="col-2">
                                        <label class="text">Logradouro</label>
                                        <input type="text" class="form-control" placeholder="Logradouro Cliente"
                                            id="logradouro">
                                    </div>
                                    <div class="col-2">
                                        <label class="text">Número</label>
                                        <input type="text" class="form-control" placeholder="Número Residência" id="numero">
                                    </div>
                                    <div class="col-2">
                                        <label class="text">Bairro</label>
                                        <input type="text" class="form-control" placeholder="Bairro Cliente" id="bairro">
                                    </div>
                                    <div class="col-2">
                                        <label class="text">Estado</label>
                                        <input type="text" class="form-control" placeholder="Estado Cliente" id="uf">
                                    </div>
                                    <div class="col-2">
                                        <label class="text">UF</label>
                                        <input type="text" class="form-control" placeholder="UF" id="estado">
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-2 text-center">
                                        <label class="text">Tipo Usuário</label>
                                        <select class="form-control select2" id="tipo_usuario">
                                            <option value="CLIENTE">CLIENTE</option>
                                            <option value="FORNECEDOR">FORNECEDOR</option>
                                            <option value="Administrador">ADMINISTRADOR</option>
                                        </select>
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Status Usuário</label>
                                        <select class="form-control select2" id="status_usuario">
                                            <option value="1">ATIVO</option>
                                            <option value="0">INATIVO</option>
                                        </select>
                                    </div>
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
            window.onload = function () {
                if (CODIGO_CLIENTE != '') {
                    pesquisar_cliente();
                }
            }
        </script>
        <?php
        require_once 'includes/footer.php';
});
?>