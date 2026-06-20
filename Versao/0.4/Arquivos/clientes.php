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

            sistema.request.post('/clientes.php', {
                'rota': 'pesquisar_clientes',
                'empresa': CODIGO_EMPRESA,
                'nome_usuario': nome_cliente,
                'celular': telefone_cliente,
                'email_usuario': email_cliente,
                'tipo_usuario': tipo_usuario,
                'modulo_contabil': MODULO_CONTABIL
            }, function (retorno) {
                let clientes = retorno.dados;
                let tamanho_retorno = clientes.length;
                let tabela = document.querySelector('#tabela_clientes tbody');

                tabela = sistema.remover_linha_tabela(tabela);

                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center'], 'NENHUM CLIENTE ENCONTRADO COM OS FILTROS PASSADOS!', 'inner', true, 10));
                    tabela.appendChild(linha);
                } else {
                    sistema.each(clientes, function (index, cliente) {
                        let linha = document.createElement('tr');
                        linha.appendChild(sistema.gerar_td(['text-start'], sistema.cortar_string(cliente.nome_usuario, 30), 'inner', false, '', cliente.nome_usuario));
                        linha.appendChild(sistema.gerar_td(['text-start'], cliente.celular, 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-start'], cliente.email_usuario, 'inner'));

                        if (cliente.tipo_usuario == 'CLIENTE') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_usuario_' + cliente._id.$oid, 'CLIENTE', ['btn', 'btn-outline-secondary'], () => { }), 'append'));
                        } else if (cliente.tipo_usuario == 'FORNECEDOR') {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_usuario_' + cliente._id.$oid, 'FORNECEDOR', ['btn', 'btn-outline-primary'], () => { }), 'append'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_usuario_' + cliente._id.$oid, 'FUNCIONÁRIO', ['btn', 'btn-outline-warning'], () => { }), 'append'));
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

                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(cliente.data_cadastro), 'inner'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_editar_usuario_' + cliente._id.$oid, 'EDITAR', ['btn', 'btn-secondary'], function editar_cliente() {
                            cadastro_cliente(cliente._id.$oid);
                        }), 'append'));

                        tabela.appendChild(linha);
                    });
                }
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
                                        placeholder="TELEFONE CLIENTE">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Cliente/Fornecedor</label>
                                    <select class="form-control" id="tipo_usuario">
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
                                                    <th scope="col">Telefone</th>
                                                    <th scope="col">Email</th>
                                                    <th scope="col">Tipo</th>
                                                    <?php
                                                    if ($_SESSION['modulo_contabil'] == true) {
                                                        echo "<th scope='col'>Conta Do</th>";
                                                        echo "<th scope='col'>Conta</th>";
                                                        echo "<th scope='col'>Conta Do</th>";
                                                        echo "<th scope='col'>Conta</th>";
                                                    }
                                                    ?>
                                                    <th scope="col">Cadastro</th>
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

            function salvar_dados() {
                let nome_usuario = document.querySelector('#nome_usuario').value;
                let email_usuario = document.querySelector('#email_usuario').value;
                let celular = document.querySelector('#celular').value;
                let cep = document.querySelector('#cep').value;
                let logradouro = document.querySelector('#logradouro').value;
                let bairro = document.querySelector('#bairro').value;
                let numero = document.querySelector('#numero').value;
                let uf = document.querySelector('#uf').value;
                let estado = document.querySelector('#estado').value;
                let tipo_usuario = document.querySelector("#tipo_usuario").value;

                if (email_usuario == '') {
                    Swal.fire({
                        title: "Validação",
                        text: "Não é possível salvar cliente/fornecedor sem email",
                        icon: "warning"
                    });
                } else {
                    sistema.request.post('/clientes.php', {
                        'rota': 'salvar_dados',
                        'codigo_usuario': CODIGO_CLIENTE,
                        'empresa': CODIGO_EMPRESA,
                        'nome_usuario': nome_usuario,
                        'email_usuario': email_usuario,
                        'tipo_usuario': tipo_usuario,
                        'celular': celular,
                        'cep': cep,
                        'logradouro': logradouro,
                        'bairro': bairro,
                        'uf': uf,
                        'estado': estado,
                        'numero': numero
                    }, function (retorno) {
                        validar_retorno(retorno, '/clientes.php');
                    });
                }
            }

            function limpar_dados() {
                document.querySelector('#nome_usuario').value = '';
                document.querySelector('#email_usuario').value = '';
                document.querySelector('#celular').value;
                document.querySelector('#cep').value = '';
                document.querySelector('#logradouro').value = '';
                document.querySelector('#numero').value = '';
                document.querySelector('#bairro').value = '';
                document.querySelector('#uf').value = '';
                document.querySelector('#estado').value = '';
                document.querySelector('#tipo_usuario').value;
            }

            function voltar() {
                window.location.href = sistema.url('/clientes.php', {
                    'rota': 'index'
                });
            }

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
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="text">Email</label>
                                        <input type="mail" class="form-control" placeholder="Email Cliente"
                                            id="email_usuario">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Celular</label>
                                        <input type="phone" class="form-control" placeholder="telefone Cliente"
                                            id="celular">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Tipo Usuário</label>
                                        <select class="form-control" id="tipo_usuario">
                                            <option value="CLIENTE">CLIENTE</option>
                                            <option value="FORNECEDOR">FORNECEDOR</option>
                                        </select>
                                    </div>
                                </div>
                                <br />
                                <div>
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
                                            <input type="text" class="form-control" placeholder="Número Residência"
                                                id="numero">
                                        </div>
                                        <div class="col-2">
                                            <label class="text">Bairro</label>
                                            <input type="text" class="form-control" placeholder="Bairro Cliente"
                                                id="bairro">
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