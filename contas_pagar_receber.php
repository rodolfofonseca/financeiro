<?php
require_once 'classes/bancoDeDados.php';
require_once 'classes/Extenso.php';
require_once 'classes/CodigoBarras/EAN13.php';

require_once 'modelos/ContasPagarReceber.php';
require_once 'modelos/Empresa.php';
require_once 'modelos/DocumentosComprovantes.php';
require_once 'modelos/Usuario.php';
require_once 'modelos/ContasFornecedores.php';

include_once 'classes/PHPSpreadsheet/PHPSpreadsheet.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Função responsável por montar o corpo do excell das contas
 * @param mixed $sheet - objeto do excell
 * @param mixed $contas - array contendo as informações das contas
 * @param int $linha - Variável contendo o valor da linha do excell
 * @return mixed - Retorna o objeto do excell com o corpo montado
 */
function montar_corpo_excell_contas($sheet, $contas, $linha)
{
    $sheet->getStyle('A' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    if (array_key_exists('nome_usuario', $contas['pessoa']) == true) {
        $sheet->setCellValue('A' . $linha, (string) $contas['pessoa']['nome_usuario']);
    } else {
        $sheet->setCellValue('A' . $linha, (string) $contas['pessoa']['nome_usuario']);
    }

    $sheet->getStyle('B' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    $sheet->setCellValue('B' . $linha, (string) $contas['nome_conta']);

    $sheet->getStyle('C' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    $sheet->setCellValue('C' . $linha, (string) $contas['descricao']);

    $sheet->getStyle('D' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    if (array_key_exists('transacao', $contas) == true) {
        $sheet->setCellValue('D' . $linha, (string) $contas['transacao']);
    } else {
        $sheet->setCellValue('D' . $linha, (string) '');
    }

    $sheet->getStyle('E' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    if($contas['valor_conta'] == null){
        $sheet->getCell('E' . $linha)->setValue((string) '0')->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
    }else{
        $sheet->getCell('E' . $linha)->setValue((string) $contas['valor_conta'])->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
    }

    $sheet->getStyle('F' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    if ($contas['status_conta'] == 'PAGO') {
        $sheet->getCell('F' . $linha)->setValue((string) $contas['valor_pago'])->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
    } else {
        $sheet->setCellValue('F' . $linha, (string) '');
    }

    $sheet->getStyle('G' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    if ($contas['tipo_juro_desconto'] != '') {
        $sheet->getCell('G' . $linha)->setValue((string) $contas['valor_juro_desconto'])->getStyle()->getNumberFormat()->setFormatCode('#,##0.00');
    } else {
        $sheet->setCellValue('G' . $linha, (string) '');
    }

    $sheet->getStyle('H' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    if ($contas['tipo_juro_desconto'] != '') {
        $sheet->setCellValue('H' . $linha, (string) $contas['tipo_juro_desconto']);
    } else {
        $sheet->setCellValue('H' . $linha, (string) '');
    }

    $sheet->getStyle('I' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->setCellValue('I' . $linha, (string) convert_date($contas['data_vencimento']));

    $sheet->getStyle('J' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    if ($contas['status_conta'] == 'VENCIDA' || $contas['status_conta'] == 'AGUARDANDO') {

    } else {
        $sheet->setCellValue('J' . $linha, (string) convert_date($contas['data_baixa']));
    }

    $sheet->getStyle('K' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    if ($contas['tipo_conta'] == true) {
        $sheet->getStyle('K' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('K' . $linha, (string) 'RECEBER');
    } else {
        $sheet->getStyle('K' . $linha)->getFont()->setBold(false);
        $sheet->setCellValue('K' . $linha, (string) 'PAGAR');
    }

    $sheet->getStyle('L' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    if ($contas['status_conta'] == 'VENCIDA') {
        $sheet->getStyle('L' . $linha)->getFont()->setBold(true);
    } else {
        $sheet->getStyle('L' . $linha)->getFont()->setBold(false);
    }

    $sheet->setCellValue('L' . $linha, (string) $contas['status_conta']);

    return $sheet;
}

/** 
 * !Função responsável por verificar se a rota ativa é a de anexar comprovante
 * 
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST)) {
        if (array_key_exists('rota', $_POST) == true) {
            $objeto_documentos_comprovantes = new DocumentosComprovantes();

            if ($_POST['rota'] == 'anexar_documentos') {

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

/** 
 * !Rota responsável por pesquisar apenas uma conta no banco de dados
 */
router_add('pesquisar_conta', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();

    $codigo_conta = (int) (isset($_REQUEST['codigo_conta_pagar_receber']) ? (int) intval($_REQUEST['codigo_conta_pagar_receber'], 10) : 0);
    $filtro = (array) ['filtro' => (array) []];

    if ($codigo_conta != 0) {
        $filtro['filtro'] = (array) ['where' => [['codigo_conta_pagar_receber', '=', $codigo_conta]]];
    }

    echo json_encode((array) ['dados' => (array) $objeto_contas_pagar_receber->pesquisar($filtro)], JSON_UNESCAPED_UNICODE);
    exit;
});

/**
 * !Rota responsável por gerar um arquivo do excell
 */
router_add('gerar_excell', function () {
    $login_usuario = (string) (isset($_REQUEST['login_usuario']) ? (string) strtoupper($_REQUEST['login_usuario']) : '');
    $vencimento_anterior = (bool) (isset($_REQUEST['vencimentos_anteriores']) ? (bool) filter_var($_REQUEST['vencimentos_anteriores'], FILTER_VALIDATE_BOOLEAN) : false);
    $retorno_vencimentos_anteriores = (array) [];

    $objeto_contas_pagar_receber = new ContasPagarReceber();
    $retorno_contas = (array) $objeto_contas_pagar_receber->pesquisar_contas($_REQUEST);

    if ($vencimento_anterior == true) {
        $filtro_montando = (array) [];
        $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) ['data_vencimento' => (bool) true], 'limite' => (int) 0];

        $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa'] : '');
        $data_vencimento = (string) (isset($_REQUEST['data_vencimento_inicio']) ? (string) $_REQUEST['data_vencimento_inicio'] : '');

        if ($empresa != '') {
            array_push($filtro_montando, ['empresa', '===', model_id($empresa)]);
        }

        if ($data_vencimento != '') {
            array_push($filtro_montando, ['data_vencimento', '<=', model_date($data_vencimento, '00:00:00')]);
        }

        array_push($filtro_montando, ['status_conta', '===', (string) 'VENCIDA']);
        $filtro['filtro'] = (array) ['and' => (array) $filtro_montando];
        $retorno_vencimento_anteriores = (array) $objeto_contas_pagar_receber->pesquisar_todos($filtro);
    }

    if (empty($retorno_contas) == true) {
        echo json_encode((array) ['status' => false, 'link' => ''], JSON_UNESCAPED_UNICODE);
    } else {
        $pasta = (string) 'anexos/excell';

        if (is_dir($pasta) == false) {
            mkdir($pasta, 0777, true);
        }

        $spreadsheet = new Spreadsheet();
        $planilha = $spreadsheet->getActiveSheet();

        foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K') as $coluna) {
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

        $sheet->getStyle('A' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('A' . $linha, 'FORNECEDOR');
        $sheet->getStyle('B' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('B' . $linha, 'NOME CONTA');
        $sheet->getStyle('C' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('C' . $linha, 'DESCRIÇÃO');
        $sheet->getStyle('D' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('D' . $linha, 'TRANSAÇÃO');
        $sheet->getStyle('E' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('E' . $linha, 'VALOR CONTA');
        $sheet->getStyle('E' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('F' . $linha, 'VALOR PAGO');
        $sheet->getStyle('F' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('G' . $linha, 'VALOR JURO DESCONTO');
        $sheet->getStyle('G' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('H' . $linha, 'TIPO JURO DESCONTO');
        $sheet->getStyle('H' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('I' . $linha, 'DATA VENCIMENTO');
        $sheet->getStyle('I' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('J' . $linha, 'DATA BAIXA');
        $sheet->getStyle('J' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('K' . $linha, 'TIPO CONTA');
        $sheet->getStyle('K' . $linha)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L' . $linha)->getFont()->setBold(true);
        $sheet->setCellValue('L' . $linha, 'STATUS CONTA');

        if ($vencimento_anterior == true) {
            if (empty($retorno_vencimento_anteriores) == false) {
                foreach ($retorno_vencimento_anteriores as $contas) {
                    $linha++;
                    $sheet = montar_corpo_excell_contas($sheet, $contas, $linha);
                }
            }
        }

        foreach ($retorno_contas as $contas) {
            $linha++;
            $sheet = montar_corpo_excell_contas($sheet, $contas, $linha);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($pasta . '/relatorio_contas_pagar_receber.xlsx');
        echo json_encode([
            'status' => true,
            'resposta' => $pasta . '/relatorio_contas_pagar_receber.xlsx'
        ]);
    }
    exit;
});

/** 
 * !Rota responsáel por salvar os dados da conta no banco de dados
 */
router_add('salvar_dados', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();

    echo json_encode((array) ['status' => (bool) $objeto_contas_pagar_receber->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * !Rota responsável por montar o filtro de pesquisa de contas e retornar todas as contas encontradas com o filtro passado
 */
router_add('pesquisar_contas', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();

    echo json_encode((array) ['dados' => (array) $objeto_contas_pagar_receber->pesquisar_contas($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * !Rota responsável por excluir uma conta no banco de dados
 */
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

/** 
 * !Rota responsável por dar baixa na conta selecionada
 */
router_add('baixar_conta', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();
    echo json_encode(['status' => (bool) $objeto_contas_pagar_receber->baixar_contas($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * !Rota reponsável por cadastrar contas recorrentes no banco de dados.
 */
router_add('cadastro_contas_recorrentes', function () {
    $objeto_contas_pagar_receber = new ContasPagarReceber();

    $json_contas = (array) (isset($_REQUEST['objeto_json']) ? (array) json_decode($_REQUEST['objeto_json'], true) : []);

    $retorno = (bool) false;

    foreach ($json_contas as $contas) {
        $retorno = $objeto_contas_pagar_receber->salvar_dados($contas);
    }

    echo json_encode(['status' => (bool) $retorno], JSON_UNESCAPED_UNICODE);
});

/**
 * !Rota responsável por fazer o download do arquivo que o usuário realizou o upload
 */
router_add('imprimir_arquivo', function () {
    require_once 'includes/head_sem_menu.php';
    $objeto_documentos_comprovantes = new DocumentosComprovantes();
    $codigo_arquivo = (string) (isset($_REQUEST['codigo_arquivo']) ? (string) $_REQUEST['codigo_arquivo'] : '');
    $local_documento = (string) (isset($_REQUEST['local_documento']) ? (string) $_REQUEST['local_documento'] : '');

    $filtro['filtro'] = ['and' => [['codigo_local', '===', model_id($codigo_arquivo)], ['local_documento', '===', (string) $local_documento]]];
    $filtro['ordenacao'] = (array) [];
    $filtro['limite'] = (int) 0;

    $retorno = (array) $objeto_documentos_comprovantes->pesquisar($filtro);
    $arquivo = (string) $retorno['nome_arquivo'];

    $caminho = realpath(__DIR__ . '/' . $arquivo);

    if ($caminho && file_exists($caminho)) {

        $nomeDownload = basename($caminho);

        if (ob_get_level())
            ob_end_clean();

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: inline; filename="' . basename($caminho) . '"');
        header('Content-Length: ' . filesize($caminho));
        header('Content-Transfer-Encoding: binary');

        readfile($caminho);
    }

    exit;
});

/**
 * !Rota responsável por realizar o vínculo de um fornecedor a conta
 */
router_add('salvar_dados_conta_fornecedor', function () {
    $objeto_conta_fornecedores = new ContasFornecedores();

    echo json_encode(['status' => (bool) $objeto_conta_fornecedores->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * !Rota responsável por pesquisar a conta que está vinculada ao fornecedor
 */
router_add('pesquisar_conta_fornecedor', function () {
    $objeto_conta_fornecedores = new ContasFornecedores();

    $codigo_conta = (int) (isset($_REQUEST['codigo_conta']) ? (int) intval($_REQUEST['codigo_conta'], 10)  : 0);
    $filtro = (array) ['filtro' => (array) []];
    $retorno = (array) [];

    if ($codigo_conta != '') {
        $filtro['filtro'] = (array) ['where' => [['codigo_conta_fornecedor', '=', $codigo_conta]]];

        $retorno = (array) $objeto_conta_fornecedores->pesquisar($filtro);
    }

    echo json_encode(['dados' => (array) $retorno]);
    exit;
});

/**
 * !Rota responsável por pesquisar todas as contas vinculadas ao fornecedor (DE ACORDO COM O FILTRO PASSADO)
 */
router_add('pesquisar_contas_fornecedores', function () {
    $objeto_conta_fornecedores = new ContasFornecedores();

    $empresa = (int) (isset($_REQUEST['empresa']) ? (int) $_REQUEST['empresa'] : 0);
    $fornecedor = (int) (isset($_REQUEST['fornecedor']) ? (int) $_REQUEST['fornecedor'] : 0);
    $nome_conta = (string) (isset($_REQUEST['nome_conta']) ? (string) strtoupper($_REQUEST['nome_conta']) : '');
    $status_conta_string = (string) (isset($_REQUEST['status_conta']) ? (string) $_REQUEST['status_conta'] : 'TODOS');
    $data_cadastro = (string) (isset($_REQUEST['data_cadastro']) ? (string) $_REQUEST['data_cadastro'] : '');

    $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) [['nome_conta','ASC']], 'limite' => (int) 100];
    $filtro_montando = (array) [];
    $retorno = (array) [];
    $retorno_final = (array) [];

    if ($empresa != 0) {
        array_push($filtro_montando, ['codigo_empresa', '=', $empresa]);
    }

    if ($fornecedor != 0) {
        array_push($filtro_montando, ['codigo_usuario', '=', $fornecedor]);
    }

    if ($nome_conta != '') {
        array_push($filtro_montando, ['nome_conta', '=', (string) $nome_conta]);
    }

    if ($status_conta_string == 'ATIVO') {
        array_push($filtro_montando, ['status_conta', '=', (bool) true]);
    } else if ($status_conta_string == 'INATIVO') {
        array_push($filtro_montando, ['status_conta', '=', (bool) false]);
    }

    if ($data_cadastro != '') {
        array_push($filtro_montando, ['data_cadastro', '>=', model_date($data_cadastro, '00:00:00')]);
        array_push($filtro_montando, ['data_cadastro', '<=', model_date($data_cadastro, '23:59:59')]);
    }

    $filtro['filtro'] = (array) ['where' => $filtro_montando];

    $retorno = (array) $objeto_conta_fornecedores->pesquisar_todos($filtro);

    if (empty($retorno) == false) {
        $objeto_fornecedor = new Usuario();

        foreach ($retorno as $contas_clientes_fornecedor) {
            $filtro_fornecedor = (array) ['filtro' => (array) ['where' => [['codigo_usuario', '=', $contas_clientes_fornecedor['codigo_usuario']]]]];
            $fornecedor_array = ['nome_fornecedor' => (string) ''];

            $retorno_fornecedor = (array) $objeto_fornecedor->pesquisar($filtro_fornecedor);

            if (empty($retorno_fornecedor) == false) {
                $fornecedor_array['nome_fornecedor'] = (string) $retorno_fornecedor['nome_usuario'];
                $contas_clientes_fornecedor['fornecedor'] = (array) $fornecedor_array;
            } else {
                $contas_clientes_fornecedor['fornecedor'] = (array) $fornecedor_array;
            }

            array_push($retorno_final, $contas_clientes_fornecedor);
        }
    }

    echo json_encode(['dados' => (array) $retorno_final], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * TODO Rota responsável por montar e visualizar as notas promissórias.
 */
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
            $cidade = (string) $retorno_empresa['cidade'];
        }
    }

    if (empty($retorno_conta) == false) {
        $data_vencimento_conta = (string) convert_date($retorno_conta['data_vencimento'], 'd/m/Y');
        $valor_conta = (string) str_replace('.', ',', $retorno_conta['valor_conta']);
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
                <strong>R$ <?php echo $valor_conta; ?></strong> Valor por extenso:
                <strong><?php echo $valor_extenso; ?></strong> à
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

/** 
 * TODO Rota index, rota raiz do módulo
 */
router_add('index', function () {
    include_once 'includes/head.php';

    $data_hoje = $data->format('Y-m-d');
    $data_inicio = $data->format('Y-m-01');
    $data_fim = $data->format('Y-m-t');

    $data_anterior = new DateTime($data_inicio);
    $data_anterior->modify('-1 day');

    $objeto_contas_pagar_receber = new ContasPagarReceber();
    $dados_contas_pagar_receber = (array) ['empresa' => (int) EMPRESA, 'data_vencimento' => $data_anterior->format('Y-m-d'), 'status_conta' => (string) 'VENCIDA'];

    $retorno_contas_vencidas = $objeto_contas_pagar_receber->pesquisar_contas_vencidas($dados_contas_pagar_receber);

    $mensagem = (bool) false;

    if (empty($retorno_contas_vencidas) == false) {
        $mensagem = (bool) true;
    }
    ?>
        <script>
            const EMPRESA = "<?php echo $codigo_empresa; ?>";
            const DATA_HOJE = "<?php echo $data_hoje; ?>";
            const DATA_INICIO = "<?php echo $data_inicio; ?>";
            const DATA_FIM = "<?php echo $data_fim; ?>";
            const ANEXA_DOCUMENTOS = <?php echo ($anexa_documentos == true) ? 1 : 0; ?>;
            const LOGIN_USUARIO = "<?php echo $login_usuario; ?>";

            /** 
             * Função responsável por abrir o módulo de cadastro de contas
             * @param {string} codigo_conta_pagar_receber - código da conta vazio caso não tenha
             */
            function cadastro_contas(codigo_conta_pagar_receber) {
                window.location.href = sistema.url('/contas_pagar_receber.php', {
                    'rota': 'cadastro_contas',
                    'codigo_conta_pagar_receber': codigo_conta_pagar_receber
                })
            }

            function gerar_excell() {
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
                let cliente_fornecedor = document.querySelector('#cliente_fornecedor').value;
                let vencimentos_anteriores = document.querySelector('#visualizar_vencidas_anteriores').checked;

                if (data_vencimento_inicio == '') {
                    data_vencimento_inicio = DATA_INICIO;
                }

                if (data_vencimento_fim == '') {
                    data_vencimento_fim = DATA_FIM;
                }

                if (LOGIN_USUARIO == '') {
                    this.Swal.fire('ATENÇÃO', 'Nome de usuário inválido!', 'warning');
                    return;
                }

                let dados = { 'rota': 'gerar_excell', 'empresa': EMPRESA, 'nome_conta': nome_conta, 'descricao': descricao, 'tipo_conta': tipo_conta, 'status_conta': status_conta, 'data_cadastro_inicio': data_cadastro_inicio, 'data_cadastro_fim': data_cadastro_fim, 'data_baixa_inicio': data_baixa_inicio, 'data_baixa_fim': data_baixa_fim, 'data_vencimento_inicio': data_vencimento_inicio, 'data_vencimento_fim': data_vencimento_fim, 'cliente_fornecedor': cliente_fornecedor, 'vencimentos_anteriores': vencimentos_anteriores, 'login_usuario': LOGIN_USUARIO };

                sistema.request.post('/contas_pagar_receber.php', dados, function (retorno) {
                    if (retorno.status == false) {
                        this.Swal.fire('ATENÇÃO', 'Nenhum conta encontrada, com os filtros passsados!', 'warning');
                    } else {
                        sistema.download('', retorno.resposta);
                    }
                });
            }

            /** 
             * Função responsável por pesquisar as contas e adicioanr na tabela
             */
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
                let cliente_fornecedor = document.querySelector('#cliente_fornecedor').value;

                if (data_vencimento_inicio == '') {
                    data_vencimento_inicio = DATA_INICIO;
                }

                if (data_vencimento_fim == '') {
                    data_vencimento_fim = DATA_FIM;
                }

                barra_progresso('Carregando contas...');

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
                    'empresa': EMPRESA,
                    'cliente_fornecedor': cliente_fornecedor
                }, function (retorno) {
                    let contas = retorno.dados;
                    let tabela_contas = document.querySelector('#tabela_contas tbody');
                    let tamanho_retorno = contas.length;

                    let total_contas_pagar_aguardando = 0;
                    let total_contas_pagar_vencidas = 0;
                    let total_contas_pagar_pagas = 0;
                    let total_contas_pagar_canceladas = 0;

                    let total_contas_receber_aguardando = 0;
                    let total_contas_receber_vencidas = 0;
                    let total_contas_receber_pagas = 0;
                    let total_contas_receber_canceladas = 0;
                    let index = 0;

                    tabela = sistema.remover_linha_tabela(tabela_contas);

                    if (tamanho_retorno == 0) {
                        let linha = document.createElement('tr');
                        linha.appendChild(sistema.gerar_td(['text-center'], 'NENHUMA CONTA ENCONTRADA, COM OS FILTROS PASSADOS!', 'inner', true, 15));
                        tabela_contas.appendChild(linha);
                        Swal.fire({ icon: 'warning', title: 'Nenhuma conta encontrada!' });
                        return;
                    }

                    function processar_item() {
                        if (index >= tamanho_retorno) {
                            let linha = document.createElement('tr');
                            
                            linha.appendChild(sistema.gerar_td(['text-center']), '', 'inner');

                            linha.appendChild(sistema.gerar_td(['text-start', 'text-info'], 'PG. AGUAR.: R$: <strong>' + sistema.number_format(total_contas_pagar_aguardando, 2, ',') + '</strong>', 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-start', 'text-warning'], 'PG. CANCE.: R$: <strong>' + sistema.number_format(total_contas_pagar_canceladas, 2, ',') + '</strong>', 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-start', 'text-danger'], 'PG. VENCI.: R$: <strong>' + sistema.number_format(total_contas_pagar_vencidas, 2, ',') + '</strong>', 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-start', 'text-success'], 'PG. PAGAS: R$: <strong>' + sistema.number_format(total_contas_pagar_pagas, 2, ',') + '</strong>', 'inner'));

                            linha.appendChild(sistema.gerar_td(['text-start'], '', 'inner'));

                            linha.appendChild(sistema.gerar_td(['text-start', 'text-info'], 'RB. AGUAR.: R$: <strong>' + sistema.number_format(total_contas_receber_aguardando, 2, ',') + '</strong>', 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-start', 'text-warning'], 'RB. CANCE.: R$: <strong>' + sistema.number_format(total_contas_receber_canceladas, 2, ',') + '</strong>', 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-start', 'text-danger'], 'RB. VENCI.: R$:<strong> ' + sistema.number_format(total_contas_receber_vencidas, 2, ',') + '</strong>', 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-start', 'text-success'], 'RB. PAGAS: R$: <strong>' + sistema.number_format(total_contas_receber_pagas, 2, ',') + '</strong>', 'inner'));

                            linha.appendChild(sistema.gerar_td(['text-start'], '', 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-start'], '', 'inner'));

                            tabela_contas.appendChild(linha);

                            Swal.close();
                            return;
                        }

                        let conta = contas[index];

                        let linha = document.createElement('tr');
                        let nome_usuario = '';

                        if (conta.tipo_conta == false) {
                            if (conta.status_conta == 'AGUARDANDO') {
                                total_contas_pagar_aguardando = total_contas_pagar_aguardando + parseFloat(conta.valor_conta);
                            } else if (conta.status_conta == 'PAGO') {
                                total_contas_pagar_pagas = total_contas_pagar_pagas + parseFloat(conta.valor_conta);
                            } else if (conta.status_conta == 'CANCELADO') {
                                total_contas_pagar_canceladas = total_contas_pagar_canceladas + parseFloat(conta.valor_conta);
                            } else if (conta.status_conta == 'VENCIDA' || conta.status_conta == 'VENCIDO') {
                                total_contas_pagar_vencidas = total_contas_pagar_vencidas + parseFloat(conta.valor_conta);
                            }
                        } else {
                            if (conta.status_conta == 'AGUARDANDO') {
                                total_contas_receber_aguardando = total_contas_receber_aguardando + parseFloat(conta.valor_conta);
                            } else if (conta.status_conta == 'PAGO') {
                                total_contas_receber_pagas = total_contas_receber_pagas + parseFloat(conta.valor_conta);
                            } else if (conta.status_conta == 'CANCELADO') {
                                total_contas_receber_canceladas = total_contas_receber_canceladas + parseFloat(conta.valor_conta);
                            } else if (conta.status_conta == 'VENCIDA' || conta.status_conta == 'VENCIDO') {
                                total_contas_receber_vencidas = total_contas_receber_vencidas + parseFloat(conta.valor_conta);
                            }
                        }

                        
                        linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], conta.codigo_conta_pagar_receber, 'inner', false, '', conta.codigo_conta_pagar_receber));

                        if (conta.hasOwnProperty('pessoa') == true) {
                            nome_usuario = conta.pessoa.nome_usuario;
                        }

                        linha.appendChild(sistema.gerar_td(['text-left'], sistema.cortar_string(conta.nome_conta, 15), 'inner', false, '', 'CLIENTE/FORNECEDOR('+nome_usuario+')'));

                        if (conta.hasOwnProperty('transacao') == true) {
                            linha.appendChild(sistema.gerar_td(['text-left', 'fw-bold'], conta.transacao, 'inner', false, '', conta.descricao));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-left'], '', 'inner', false, '', ''));
                        }

                        if (conta.tipo_conta == false) {
                            linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold', 'text-danger'], sistema.number_format(conta.valor_conta), 'inner'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold', 'text-success'], sistema.number_format(conta.valor_conta), 'inner'));
                        }

                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(conta.data_vencimento), 'inner'));

                        if (conta.status_conta == 'AGUARDANDO' || conta.status_conta == 'VENCIDA' || conta.status_conta == 'VENCIDO') {
                            linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));

                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.retornar_data(conta.data_baixa), 'inner'));
                        }

                        if (conta.tipo_conta == false) {
                            if(conta.status_conta == 'AGUARDANDO'){
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta.codigo_conta_pagar_receber, 'PAGAR | AGUARDANDO', ['btn', 'btn-outline-secondary'], function tipo_conta() { }), 'append'));
                            }else if(conta.status_conta == 'PAGO'){
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta.codigo_conta_pagar_receber, 'PAGAR | PAGO', ['btn', 'btn-outline-success'], function tipo_conta() { }),  'append'));
                            }else if(conta.status_conta == 'CANCELADO'){
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta.codigo_conta_pagar_receber, 'PAGAR | CANCELADO', ['btn', 'btn-outline-warning'], function tipo_conta() { }),  'append'));
                            }else if(conta.status_conta == 'VENCIDA' || conta.status_conta == 'VENCIDO'){
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta.codigo_conta_pagar_receber, 'PAGAR | VENCIDA', ['btn', 'btn-outline-danger'], function tipo_conta() { }),  'append'));
                            }
                        } else {
                            if(conta.status_conta == 'AGUARDANDO'){
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta.codigo_conta_pagar_receber, 'RECEBER | AGUARDANDO', ['btn', 'btn-outline-secondary'], function imprimir_conta_botao() {
                                    abrir_modal_impressao_promissoria(conta.codigo_conta_pagar_receber);
                                }), 'append'));
                            }else if(conta.status_conta == 'PAGO'){
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta.codigo_conta_pagar_receber, 'RECEBER | PAGO', ['btn', 'btn-outline-success'], function tipo_conta() { }),  'append'));
                            }else if(conta.status_conta == 'CANCELADO'){
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta.codigo_conta_pagar_receber, 'RECEBER | CANCELADO', ['btn', 'btn-outline-warning'], function tipo_conta() { }),  'append'));
                            }else if(conta.status_conta == 'VENCIDA' || conta.status_conta == 'VENCIDO'){
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_conta_' + conta.codigo_conta_pagar_receber, 'RECEBER | VENCIDA', ['btn', 'btn-outline-danger'], function imprimir_conta_botao() {
                                    abrir_modal_impressao_promissoria(conta.codigo_conta_pagar_receber);
                                }),  'append'));
                            }
                        }

                        let botao = document.createElement('button');
                        botao.id = 'botao_baixar_conta_' + conta.codigo_conta_pagar_receber;
                        botao.textContent = 'BAIXAR';
                        botao.classList.add('btn');
                        botao.classList.add('btn-primary');

                        if (conta.status_conta == 'PAGO') {
                            botao.disabled = true;
                        }

                        botao.addEventListener('click', function () {
                            if (nome_usuario == '' || conta.transacao == '') {
                                Swal.fire({ 'title': 'Campos faltando', 'text': 'Antes de dar baixar, edite e adicione os campos que falta', 'icon': 'warning' });
                            } else {
                                botao.dataset.bsToggle = "modal";
                                botao.dataset.bsTarget = "#modal_baixar_conta";
                                document.querySelector('#valor_conta').value = sistema.number_format(conta.valor_conta);
                                document.querySelector('#data_vencimento').value = sistema.retornar_data(conta.data_vencimento, false, 'us');
                                document.querySelector('#codigo_conta_pagar_receber').value = conta.codigo_conta_pagar_receber;
                                document.querySelector('#tipo_conta_input').value = conta.tipo_conta;
                                document.querySelector('#nome_conta_input').value = conta.nome_conta;
                            }
                        });

                        if (ANEXA_DOCUMENTOS == 1) {
                            if (conta.hasOwnProperty('boleto') == true) {
                                if (conta.boleto == 'NAO') {
                                    let botao_boleto = document.createElement('button');
                                    botao_boleto.id = 'botao_anexar_boleto_' + conta.codigo_conta_pagar_receber;
                                    botao_boleto.textContent = 'ANEXAR CONTA';
                                    botao_boleto.classList.add('btn');
                                    botao_boleto.classList.add('btn-success');
                                    botao_boleto.dataset.bsToggle = 'modal';
                                    botao_boleto.dataset.bsTarget = '#modal_anexar_documentos';

                                    botao_boleto.addEventListener('click', function () {
                                        document.querySelector('#codigo_local').value = conta.codigo_conta_pagar_receber;
                                        document.querySelector('#nome_conta_anexo_documentos').value = conta.nome_conta;
                                        document.querySelector('#empresa_anexo_documento').value = EMPRESA;
                                    });

                                    linha.appendChild(sistema.gerar_td(['text-center'], botao_boleto, 'append'));
                                } else if (conta.boleto == 'SIM') {
                                    let botao_baixar_arquivo = document.createElement('button');
                                    botao_baixar_arquivo.id = 'botao_baixar_comprovante_boleto_' + conta.codigo_conta_pagar_receber;
                                    botao_baixar_arquivo.textContent = 'BAIXAR BOLETO';
                                    botao_baixar_arquivo.classList.add('btn');
                                    botao_baixar_arquivo.classList.add('btn-info');

                                    botao_baixar_arquivo.onclick = function () {
                                        abrir_modal_download_arquivo(conta.codigo_conta_pagar_receber, 'CONTAS_PAGAR_RECEBER_BOLETOS');
                                    }

                                    linha.appendChild(sistema.gerar_td(['text-center'], botao_baixar_arquivo, 'append'));
                                }
                            } else {
                                let botao_boleto = document.createElement('button');
                                botao_boleto.id = 'botao_anexar_boleto_' + conta.codigo_conta_pagar_receber;
                                botao_boleto.textContent = 'ANEXAR CONTA';
                                botao_boleto.classList.add('btn');
                                botao_boleto.classList.add('btn-success');
                                botao_boleto.dataset.bsToggle = 'modal';
                                botao_boleto.dataset.bsTarget = '#modal_anexar_documentos';

                                botao_boleto.addEventListener('click', function () {
                                    document.querySelector('#codigo_local').value = conta.codigo_conta_pagar_receber;
                                    document.querySelector('#nome_conta_anexo_documentos').value = conta.nome_conta;
                                    document.querySelector('#empresa_anexo_documento').value = EMPRESA;
                                });

                                linha.appendChild(sistema.gerar_td(['text-center'], botao_boleto, 'append'));
                            }
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));
                        }

                        if (conta.status_conta == 'PAGO') {
                            if (ANEXA_DOCUMENTOS == 1) {
                                if (conta.comprovante == 'NAO') {
                                    let botao_documentos = document.createElement('button');
                                    botao_documentos.id = 'botao_anexo_documentos_' + conta.codigo_conta_pagar_receber;
                                    botao_documentos.textContent = 'COMPROVANTE';
                                    botao_documentos.classList.add('btn');
                                    botao_documentos.classList.add('btn-success');
                                    botao_documentos.dataset.bsToggle = 'modal';
                                    botao_documentos.dataset.bsTarget = '#modal_anexar_documentos';
                                    botao_documentos.addEventListener('click', function () {
                                        document.querySelector('#codigo_local').value = conta.codigo_conta_pagar_receber;
                                        document.querySelector('#nome_conta_anexo_documentos').value = conta.nome_conta;
                                        document.querySelector('#empresa_anexo_documento').value = EMPRESA;
                                    });

                                    linha.appendChild(sistema.gerar_td(['text-center'], botao_documentos, 'append'));
                                } else {
                                    let botao_baixar_arquivo = document.createElement('button');
                                    botao_baixar_arquivo.id = 'botao_baixar_comprovante_' + conta.codigo_conta_pagar_receber;
                                    botao_baixar_arquivo.textContent = 'BAIXAR COMPROVANTE';
                                    botao_baixar_arquivo.classList.add('btn');
                                    botao_baixar_arquivo.classList.add('btn-info');

                                    botao_baixar_arquivo.onclick = function () {
                                        abrir_modal_download_arquivo(conta.codigo_conta_pagar_receber, 'CONTAS_PAGAR_RECEBER');
                                    }

                                    linha.appendChild(sistema.gerar_td(['text-center'], botao_baixar_arquivo, 'append'));
                                }
                            }

                            linha.appendChild(sistema.gerar_td(['text-center'], botao, 'append'));
                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_editar_conta_' + conta.codigo_conta_pagar_receber, 'EDITAR', ['btn', 'btn-secondary', 'disabled'], function baixar_conta_botao() { }), 'append'));
                        } else {
                            linha.appendChild(sistema.gerar_td(['text-center'], '', 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-center'], botao, 'append'));

                            linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_editar_conta_' + conta.codigo_conta_pagar_receber, 'EDITAR', ['btn', 'btn-secondary'], function baixar_conta_botao() {
                                cadastro_contas(conta.codigo_conta_pagar_receber);
                            }), 'append'));
                        }

                        tabela_contas.appendChild(linha);

                        atualizar_barra_progresso(index, tamanho_retorno, document.querySelector('#barra_progresso'), document.querySelector('#texto_progresso'));

                        index++;
                        setTimeout(processar_item, 1);
                    }

                    processar_item();

                });
            }

            /**
             * Função responsável por abrir o modal de visualização e impressão de nota promissória
             * @param {string} codigo_conta - Código da conta que deseja visualizar a nota promissória
             */
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

            /** 
             * Função responsável por abrir o modal para a visualização dos comprovantes, assim como a opção para fazer o download do mesmo
             * @param {string} codigo_arquivo - Código do arquivo que será visualizado
             * @para {string} local_documento - Tipo de arquivo ex: comprovante de pagamento, boleto etc.
             */
            function abrir_modal_download_arquivo(codigo_arquivo, local_documento) {
                let largura = 1200;
                let altura = 500;
                let left = (screen.width - largura) / 2;
                let top = (screen.height - altura) / 2;
                let url = sistema.url('/contas_pagar_receber.php', {
                    'rota': 'imprimir_arquivo',
                    'codigo_arquivo': codigo_arquivo,
                    'codigo_empresa': EMPRESA,
                    'local_documento': local_documento
                });
                let nome = 'Impressão de Arquivos';
                let janela = window.open(url, nome, `width=${largura}, height=${altura}, left=${left}, top=${top}`);

                if (window.focus) {
                    janela.focus();
                }
            }

            /** 
             * Função responsável por verificar e calcular se a conta teve juros ou desconto
             */
            function validar_juro_desconto() {
                let valor_conta = document.querySelector('#valor_conta').value;
                let valor_pago = document.querySelector('#valor_pago').value;
                let resultado = 0;

                valor_conta = valor_conta.replace(',', '.');
                valor_pago = valor_pago.replace(',', '.');

                if (valor_conta > valor_pago) {
                    document.querySelector('#tipo_juro_desconto').value = '0';
                    resultado = valor_conta - valor_pago;
                } else if (valor_conta < valor_pago) {
                    document.querySelector('#tipo_juro_desconto').value = '1';
                    resultado = valor_pago - valor_conta;
                } else {
                    document.querySelector('#tipo_juro_desconto').value = '';
                    resultado = 0;
                }

                resultado = resultado.toFixed(2);

                document.querySelector('#valor_juro_desconto').value = resultado.replace('.', ',');
            }

            /** 
             * Função responsável por pesquisar as contas bancárias para que possa ser dado a baixa 
             */
            function pesquisar_conta_bancaria() {
                sistema.request.post('/contas.php', {
                    'rota': 'pesquisar_contas',
                    'empresa': EMPRESA,
                    'status': true
                }, function (retorno) {
                    let contas = retorno.dados;
                    let tamanho_retorno = contas.length;
                    if (tamanho_retorno > 0) {
                        let select_conta = document.querySelector('#conta');

                        sistema.each(contas, function (index, conta) {
                            select_conta.appendChild(sistema.gerar_option(conta.codigo_conta, conta.nome_conta + " | " + sistema.number_format(conta.saldo_conta)));
                        });
                    }
                });
            }

            /** 
             * Função responsável por baixar a conta.
             */
            function baixar_conta() {
                let codigo_conta_pagar_receber = document.querySelector('#codigo_conta_pagar_receber').value;
                let valor_pago = document.querySelector('#valor_pago').value;
                let tipo_juro_desconto = document.querySelector('#tipo_juro_desconto').value;
                let valor_juro_desconto = document.querySelector('#valor_juro_desconto').value;
                let data_baixa = document.querySelector('#data_baixa').value;
                let codigo_conta_bancaria = document.querySelector('#conta').value;
                let tipo_conta = document.querySelector('#tipo_conta_input').value;
                let nome_conta = document.querySelector('#nome_conta_input').value;

                if(tipo_juro_desconto == ''){
                    tipo_juro_desconto = false;
                }

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

                sistema.request.post('/contas_pagar_receber.php', objeto_json, function (retorno) {
                    validar_retorno(retorno, '/contas_pagar_receber.php');
                });

            }

            /** 
             * Função responsável por retornar ao index do módulo
             * @param {object} parametro
             * @paraam {bool} sair 
             */
            function retornar(parametro, sair) {
                parametro.preventDefault();
                if (sair == true) {
                    window.location.href = sistema.url('/contas_pagar_receber.php', {
                        'rota': 'index'
                    });
                }
            }

            /** 
             * Função responsável por pesquisar os clientes/fornecedores e adicionar ao select
             */
            function pesquisar_cliente_fornecedor() {
                sistema.request.post('/clientes.php', {
                    'rota': 'pesquisar_clientes',
                    'empresa': EMPRESA,
                    'tipo_usuario': ''
                }, function (retorno) {
                    let select = document.querySelector('#cliente_fornecedor');
                    let cliente_fornecedor = retorno.dados;

                    sistema.each(cliente_fornecedor, function (index, cliente) {
                        select.appendChild(sistema.gerar_option(cliente.codigo_usuario, cliente.nome_usuario));
                    });
                });
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
                            <button class="btn btn-primary d-flex align-items-center justify-content-center"
                                onclick="cadastro_contas('');">
                                Cadastrar Conta
                            </button>
                        </div>
                    </div>
                </div>
                <?php
                if ($mensagem == true) {
                    ?>
                    <div class="alert alert-danger" role="alert">
                        <h5 class="alert-heading text-dark">⚠️ Atenção</h5>
                        <p class="text-dark mb-2">Você possui contas vencidas de meses anteriores.</p>
                        <p class="mb-0">
                            <button class="btn btn-primary d-flex align-items-center justify-content-center"
                                data-bs-toggle="modal" data-bs-target="#modal_contas_vencidas_anteriores">VER CONTAS
                                VENCIDAS</button>
                        </p>
                    </div>
                    <?php
                }
                ?>
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <div class="card-title">Pesquisa de Contas A Pagar Receber</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-2">
                                        <label class="text">Cliente/Fornecedor</label>
                                        <select class="form-control select2" id="cliente_fornecedor">
                                            <option value="">Selecione uma opção</option>
                                        </select>
                                    </div>
                                    <div class="col-3">
                                        <label class="text">Nome Conta</label>
                                        <input type="text" class="form-control" id="nome_conta" placeholder="Nome da Conta">
                                    </div>
                                    <div class="col-3">
                                        <label class="text">Descrição</label>
                                        <input type="text" class="form-control" id="descricao"
                                            placeholder="Descrição da Conta">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Tipo Conta</label>
                                        <select class="form-control" id="tipo_conta">
                                            <option value="TODOS">TODOS</option>
                                            <option value="PAGAR">PAGAR</option>
                                            <option value="RECEBER">RECEBER</option>
                                        </select>
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Status Conta</label>
                                        <select class="form-control select2" id="status_conta">
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
                                        <input type="date" class="form-control" id="data_vencimento_inicio"
                                            value="<?php echo $data_inicio; ?>">
                                    </div>
                                    <div class="col-2 text-center">
                                        <label class="text">Data Vencimento Fim</label>
                                        <input type="date" class="form-control" id="data_vencimento_fim"
                                            value="<?php echo $data_fim; ?>">
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
                                    <div class="col-2 text-center push-4">
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                id="visualizar_vencidas_anteriores">
                                            <label class="form-check-label" for="visualizar_vencidas_anteriores">Visualizar
                                                Venc. Anteriores</label>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <button class="btn btn-light w-100 text-uppercase" onclick="gerar_excell();">Gerar
                                            Excell</button>
                                    </div>
                                    <div class="col-3">
                                        <button class="btn btn-secondary w-100"
                                            onclick="pesquisar_contas();">Pesquisar</button>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-12">
                                        <div class="table-responsive">
                                            <table class="table table-nowrap text-nowrap table-hover" id="tabela_contas">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th scope="col">#</th>
                                                        <th scope="col">Nome Conta</th>
                                                        <th scope="col">Transação</th>
                                                        <th scope="col">Valor</th>
                                                        <th scope="col">Vencimento</th>
                                                        <th scope="col">baixa</th>
                                                        <th scope="col">Tipo/Status</th>
                                                        <th scope="col">Boleto</th>
                                                        <th scope="col">Comprovante</th>
                                                        <th scope="col">Baixar</th>
                                                        <th scope="col">Editar</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="15" class="text-center">UTILIZE O FILTRO PARA FACILITAR
                                                            A PESQUISA!</td>
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
            <div class="modal fade" id="modal_baixar_conta" aria-labelledby="myLargeModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Baixa de Contas</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="codigo_conta_pagar_receber">
                            <input type="hidden" id="tipo_conta_input">
                            <input type="hidden" id="nome_conta_input">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <label class="text">Valor Conta</label>
                                    <input type="text" class="form-control" id="valor_conta" placeholder="Valor Conta"
                                        sistema-mask="moeda" disabled="true">
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
                                    <input type="text" class="form-control" id="valor_pago" sistema-mask="moeda"
                                        placeholder="Valor Pago" onblur="validar_juro_desconto();">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Juro/Desconto</label>
                                    <input type="text" class="form-control" id="valor_juro_desconto" sistema-mask="moeda"
                                        placeholder="Juro/Desconto" sistema-mask="moeda">
                                </div>
                                <div class="col-3 text-center">
                                    <label class="text">Tipo Juro/Desconto</label>
                                    <select class="form-control" id="tipo_juro_desconto">
                                        <option value="">Selecione uma opção</option>
                                        <option value="1">JURO</option>
                                        <option value="0">DESCONTO</option>
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
                                    <button type="button" class="btn btn-danger w-100" data-bs-dismiss="modal"
                                        aria-label="Close">Fechar</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-success w-100" onclick="baixar_conta();">Baixar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modal_anexar_documentos"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
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
                                <div class="row">
                                    <div class="col-6">
                                        <label class="text">Nome Conta</label>
                                        <input type="text" class="form-control" name="nome_conta"
                                            id="nome_conta_anexo_documentos" disabled="true">
                                    </div>
                                    <div class="col-6">
                                        <label class="text">Tipo</label>
                                        <select class="form-control" name="local_documento" id="local_documento">
                                            <option value="CONTAS_PAGAR_RECEBER">CONTAS PAGAR RECEBER</option>
                                            <option value="CONTAS_PAGAR_RECEBER_BOLETOS">BOLETOS</option>
                                        </select>
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
                                        <button class="btn btn-danger btn-lg w-100"
                                            onclick="retornar(event, true);">Voltar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modal_contas_vencidas_anteriores" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myLargeModalLabel">Contas Vencidas Meses Anteriores</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
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
                                                    <th scope="col">Tipo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($mensagem == true) {
                                                    foreach ($retorno_contas_vencidas as $contas) {
                                                        echo "<tr>";

                                                        echo "<td class='text-left'>" . $contas['nome_conta'] . "</td>";
                                                        echo "<td class='text-left'>" . $contas['descricao'] . "</td>";
                                                        echo "<td class='text-left'>" . formatar_numero($contas['valor_conta'], 2, ',') . "</td>";
                                                        echo "<td class='text-left'>" . convert_date($contas['data_vencimento']) . "</td>";

                                                        if ($contas['tipo_conta'] == false) {
                                                            echo "<td class='text-left text-danger fw-bold'>PAGAR</td>";
                                                        } else {
                                                            echo "<td class='text-left text-success fw-bold'>RECEBER</td>";
                                                        }
                                                        echo "</tr>";
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = function () {
                    document.querySelector('#data_baixa').value = DATA_HOJE;
                    pesquisar_contas();
                    pesquisar_conta_bancaria();
                    pesquisar_cliente_fornecedor();
                }
            </script>
            <?php
            include_once 'includes/footer.php';
            exit;
});

/** 
 * TODO Rota responsável por cadastrar no banco de dados novas contas.
 */
router_add('cadastro_contas', function () {
    include_once 'includes/head.php';

    $objeto_codigo_barras = new EAN13();
    $transacao = (string) $objeto_codigo_barras->getFullCode('');

    $data_hoje = $data->format('Y-m-d');
    $data->add(new DateInterval('P30D'));
    $data_vencimento = $data->format('Y-m-d');

    $codigo_conta_pagar_receber = (int) (isset($_REQUEST['codigo_conta_pagar_receber']) ? (int) (intval($_REQUEST['codigo_conta_pagar_receber'], 10)) : 0);
    ?>
            <script>
                const HOJE = "<?php echo $data_hoje; ?>";
                const DATA_VENCIMENTO = "<?php echo $data_vencimento; ?>";
                const EMPRESA = "<?php echo $codigo_empresa; ?>";
                const CODIGO_CONTA_PAGAR_RECEBER = "<?php echo $codigo_conta_pagar_receber; ?>";
                const TRANSACAO = "<?php echo $transacao; ?>";

                let QUANTIDADE_PARCELAS_RECORRENTES = 0;

                /**
                 * Função responsável por salvar os dados das contas no banco de dados
                 */
                function salvar_dados() {
                    if (QUANTIDADE_PARCELAS_RECORRENTES == 0) {
                        let cliente_fornecedor = document.querySelector('#cliente_fornecedor').value;
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
                        let conta_fornecedor = document.querySelector('#conta_fornecedor').value;
                        let transacao = document.querySelector('#transacao').value;

                        if (cliente_fornecedor == '') {
                            Swal.fire({ 'title': 'Falha de validacao', 'text': 'Nome do cliente/fornecedor não pode ser vazio!', 'icon': 'error' });
                            return;
                        }

                        if (nome_conta == '') {
                            Swal.fire({ 'title': 'Falha de validacao', 'text': 'Nome da conta não pode ser vazio!', 'icon': 'error' });
                            return;
                        }

                        if (valor_conta == '') {
                            Swal.fire({ 'title': 'Falha de validacao', 'text': 'Valor da conta não pode ser vazio!', 'icon': 'error' });
                            return;
                        }

                        if(tipo_juro_desconto == ''){
                            tipo_juro_desconto = false;
                        }

                        let dados = {
                            'rota': 'salvar_dados',
                            'empresa': EMPRESA,
                            'cliente_fornecedor': cliente_fornecedor,
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
                            'status_conta': status_conta,
                            'cliente_fornecedor': cliente_fornecedor,
                            'conta_fornecedor': conta_fornecedor,
                            'transacao': transacao
                        };

                        sistema.request.post('/contas_pagar_receber.php', dados, function (retorno) {
                            validar_retorno(retorno, '/contas_pagar_receber.php');
                        });
                    } else {
                        let linhas = document.querySelectorAll('#tabela_contas_recorrentes tr');

                        let contas = [];
                        let executando = true;
                        let transacao = document.querySelector('#transacao').value;

                        linhas.forEach(function (linha, index) {
                            let i = index + 1;

                            if (executando == true) {
                                let elemento = document.querySelector('#tipo_conta_' + i);

                                if (!elemento) { } else {
                                    let conta = {};
                                    conta.empresa = EMPRESA;

                                    conta.cliente_fornecedor = document.querySelector('#cliente_fornecedor_' + i).value;
                                    conta.nome_conta = document.querySelector('#nome_conta_' + i).value;
                                    conta.descricao = document.querySelector('#descricao_' + i).value;
                                    conta.valor_conta = document.querySelector('#valor_conta_' + i).value;
                                    conta.data_vencimento = document.querySelector('#data_vencimento_' + i).value;
                                    conta.tipo_conta = document.querySelector("#tipo_conta_" + i).value;
                                    conta.conta_fornecedor = document.querySelector('#conta_fornecedor').value;
                                    conta.transacao = transacao;

                                    contas.push(conta);

                                    if (i == QUANTIDADE_PARCELAS_RECORRENTES) {
                                        executando = false;
                                    }
                                }
                            }
                        });

                        let json = JSON.stringify(contas);

                        sistema.request.post('/contas_pagar_receber.php', {
                            'rota': 'cadastro_contas_recorrentes',
                            'objeto_json': json
                        }, function (retorno) {
                            validar_retorno(retorno, '/contas_pagar_receber.php');
                        });
                    }
                }

                /**
                 * Função responsável por limpar os campos
                 */
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
                    document.querySelector('#cliente_fornecedor').value = '';
                }

                /** 
                 * Função responsável por retornar a raiz do módulo.
                 */
                function voltar() {
                    window.location.href = sistema.url('/contas_pagar_receber.php', {
                        'rota': 'index'
                    });
                }

                /** 
                 * Função responsável por montar a tabela de contas recorrentes
                 */
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
                    let cliente_fornecedor = document.querySelector('#cliente_fornecedor').value;

                    let tabela = document.querySelector("#tabela_contas_recorrentes tbody");
                    let linha = document.createElement('tr');
                    let id_linha = 'linha_' + QUANTIDADE_PARCELAS_RECORRENTES;

                    linha.id = id_linha

                    linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('id_parcela_conta_' + QUANTIDADE_PARCELAS_RECORRENTES, QUANTIDADE_PARCELAS_RECORRENTES, ['form-control', 'text-center'], 'text', '', false), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('cliente_fornecedor_' + QUANTIDADE_PARCELAS_RECORRENTES, cliente_fornecedor, ['form-control'], 'text'), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('nome_conta_' + QUANTIDADE_PARCELAS_RECORRENTES, nome_conta, ['form-control'], 'text'), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('descricao_' + QUANTIDADE_PARCELAS_RECORRENTES, descricao, ['form-control'], 'text'), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('tipo_conta_' + QUANTIDADE_PARCELAS_RECORRENTES, tipo_conta, ['form-control', 'text-center'], 'text', '', false), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('valor_conta_' + QUANTIDADE_PARCELAS_RECORRENTES, valor_conta, ['form-control'], 'text'), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('data_vencimento_' + QUANTIDADE_PARCELAS_RECORRENTES, data_vencimento, ['form-control'], 'date'), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_excluir_linha_' + QUANTIDADE_PARCELAS_RECORRENTES, 'EXCLUIR', ['btn', 'btn-danger'], () => {
                        excluir_linha_tabela_contas_recorrentes(id_linha);
                    }), 'append'));

                    tabela.appendChild(linha);
                }

                /** 
                 * Função responsável por pesquuisar os clientes_fornecedores no banco de dados e adicioanr no select
                 */
                function pesquisar_cliente_fornecedor() {
                    sistema.request.post('/clientes.php', {
                        'rota': 'pesquisar_clientes',
                        'empresa': EMPRESA,
                        'tipo_usuario': ''
                    }, function (retorno) {
                        let select = document.querySelector('#cliente_fornecedor');
                        let cliente_fornecedor = retorno.dados;

                        sistema.each(cliente_fornecedor, function (index, cliente) {
                            select.appendChild(sistema.gerar_option(cliente.codigo_usuario, cliente.nome_usuario));
                        });
                    });
                }

                /**
                 * Função responsável por excluir uma linha da tabela de contas recorrentes
                 * @paraam {string} LINHA - contem o id da linha que deve ser excluída da tabela
                 */
                function excluir_linha_tabela_contas_recorrentes(LINHA) {
                    let linha_tabela = document.querySelector('#' + LINHA);
                    linha_tabela.remove();
                }

                function pesquisar_contas_fornecedor() {
                    let fornecedor = document.querySelector('#cliente_fornecedor').value;
                    sistema.request.post('/contas_pagar_receber.php', {
                        'rota': 'pesquisar_contas_fornecedores',
                        'empresa': EMPRESA,
                        'fornecedor': fornecedor,
                        'status_conta': 'ATIVO'
                    }, (retorno) => {
                        let contas = retorno.dados;
                        let tamanho_retorno = contas.length;
                        let conta_fornecedor_select = document.querySelector('#conta_fornecedor');

                        if (tamanho_retorno == 0) {
                            this.Swal.fire({
                                'title': 'Sem dados',
                                'text': 'Nenhuma conta encontrada para o Fornecedor!',
                                'icon': 'warning'
                            });

                            conta_fornecedor_select = sistema.remover_option(conta_fornecedor_select);
                        } else {
                            conta_fornecedor_select = sistema.remover_option(conta_fornecedor_select);

                            sistema.each(contas, (index, conta) => {
                                conta_fornecedor_select.appendChild(sistema.gerar_option(conta.codigo_conta_fornecedor, conta.nome_conta));
                            });
                        }
                    }, false);
                }

                function colocar_nome_conta() {
                    let conta_fornecedor_select = document.querySelector('#conta_fornecedor');
                    let nome_conta = conta_fornecedor_select.options[conta_fornecedor_select.selectedIndex].text;
                    document.querySelector('#nome_conta').value = nome_conta;
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
                                    <input type="hidden" class="form-control text-uppercase" id="nome_conta"
                                        placeholder="Nome da Conta">
                                    <div class="row">
                                        <div class="col-3 text-center">
                                            <label class="text">Cliente/Fornecedor</label>
                                            <select class="form-control select2" id="cliente_fornecedor"
                                                onchange="pesquisar_contas_fornecedor();">
                                                <option value="">Selecione uma opção</option>
                                            </select>
                                        </div>
                                        <div class="col-3 text-center">
                                            <label class="text">Nome Conta</label>
                                            <select class="form-control select2" id="conta_fornecedor"
                                                onchange="colocar_nome_conta();">
                                                <option value="">Selecione Uma Opção</option>
                                            </select>
                                        </div>
                                        <div class="col-6 text-center">
                                            <label class="text">Descrição</label>
                                            <input type="text" class="form-control text-uppercase" id="descricao"
                                                placeholder="Descrição da Conta">
                                        </div>
                                    </div>
                                    <br />
                                    <div class="row">
                                        <div class="col-3 text-center">
                                            <label class="text">Valor Conta</label>
                                            <input type="text" class="form-control" id="valor_conta"
                                                placeholder="Valor Conta" sistema-mask="moeda">
                                        </div>
                                        <div class="col-3 tex-center">
                                            <label class="text">Valor Pago</label>
                                            <input type="text" class="form-control" id="valor_pago" placeholder="Valor pago"
                                                sistema-mask="moeda">
                                        </div>
                                        <div class="col-3 text-center">
                                            <label class="text">Valor Juro/Desconto</label>
                                            <input type="text" class="form-control" id="valor_juro_desconto"
                                                placeholder="Valor Juro/Desconto" sistema-mask="moeda">
                                        </div>
                                        <div class="col-3 text-center">
                                            <label class="text">Tipo Juro/Desconto</label>
                                            <select class="form-control select2" id="tipo_juro_desconto">
                                                <option value="">Selecione uma opção</option>
                                                <option value="1">JURO</option>
                                                <option value="0">DESCONTO</option>
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
                                        <div class="col-4 text-center">
                                            <label class="text">Tipo Conta</label>
                                            <select class="form-control select2" id="tipo_conta">
                                                <option value="0">PAGAR</option>
                                                <option value="1">RECEBER</option>
                                            </select>
                                        </div>
                                        <div class="col-4 text-center">
                                            <label class="text">Status Conta</label>
                                            <select class="form-control select2" id="status_conta">
                                                <option value="AGUARDANDO">AGUARDANDO</option>
                                                <option value="PAGO">PAGO</option>
                                                <option value="CANCELADO">CANCELADO</option>
                                                <option value="VENCIDA">VENCIDA</option>
                                            </select>
                                        </div>
                                        <div class="col-4 text-center">
                                            <label class="text">Transação</label>
                                            <input type="text" class="form-control" id="transacao">
                                        </div>
                                    </div>
                                    <br />
                                    <div class="row">
                                        <div class="col-3 push-9">
                                            <button class="btn btn-primary w-100 bg-lg"
                                                onclick="cadastro_conta_recorrente();">CONTA RECORRENTE</button>
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
                                                <table class="table table-nowrap text-nowrap table-hover"
                                                    id="tabela_contas_recorrentes">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th scope="col">#</th>
                                                            <th scope="col">Cliente/Fornecedor</th>
                                                            <th scope="col">Nome Conta</th>
                                                            <th scope="col">Descrição</th>
                                                            <th scope="col">Tipo</th>
                                                            <th scope="col">Valor</th>
                                                            <th scope="col">Vencimento</th>
                                                            <th scope="col">Excluir</th>
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
                    window.onload = function () {
                        document.querySelector('#data_cadastro').value = HOJE;
                        document.querySelector('#data_vencimento').value = DATA_VENCIMENTO;
                        document.querySelector('#transacao').value = TRANSACAO;

                        pesquisar_cliente_fornecedor();

                        if (CODIGO_CONTA_PAGAR_RECEBER != 0) {
                            sistema.request.post('/contas_pagar_receber.php', {
                                'rota': 'pesquisar_conta',
                                'codigo_conta_pagar_receber': CODIGO_CONTA_PAGAR_RECEBER
                            }, function (retorno) {
                                let conta = retorno.dados;

                                document.querySelector('#nome_conta').value = conta.nome_conta;
                                document.querySelector('#descricao').value = conta.descricao;
                                document.querySelector('#tipo_juro_desconto').value = conta.tipo_juro_desconto;
                                document.querySelector('#tipo_conta').value = conta.tipo_conta;
                                document.querySelector('#data_cadastro').value = sistema.retornar_data(conta.data_cadastro, false, 'us');
                                document.querySelector('#data_vencimento').value = sistema.retornar_data(conta.data_vencimento, false, 'us');
                                document.querySelector('#status_conta').value = conta.status_conta;

                                if (conta.valor_conta != 0) {
                                    document.querySelector('#valor_conta').value = sistema.number_format(conta.valor_conta);
                                }

                                if (conta.valor_pago != 0) {
                                    document.querySelector('#valor_pago').value = sistema.number_format(conta.valor_pago);
                                }

                                if (conta.valor_juro_desconto != 0) {
                                    document.querySelector('#valor_juro_desconto').value = sistema.number_format(conta.valor_juro_desconto);
                                }

                                if (conta.status_conta != 'AGUARDANDO' && conta.status_conta != 'VENCIDO') {
                                    document.querySelector('#data_baixa').value = sistema.retornar_data(conta.data_baixa, false, 'us');
                                }

                                if (conta.hasOwnProperty('cliente_fornecedor') == true) {
                                    document.querySelector('#cliente_fornecedor').value = conta.cliente_fornecedor.codigo_usuario;
                                }

                                if (conta.hasOwnProperty('transacao') == true) {
                                    document.querySelector('#transacao').value = conta.transacao;
                                }
                            });
                        }
                    }
                </script>
                <?php
                include_once 'includes/footer.php';
});

/**
 * ! Rota responsável por realizar o vinculo do fornecedor a conta
 */
router_add('contas_fornecedores', function () {
    include_once 'includes/head.php';
    $codigo_conta = (int) (isset($_REQUEST['codigo_conta']) ? (int) $_REQUEST['codigo_conta'] : 0);
    ?>
                <script>
                    const DATA_HOJE = "<?php echo DATA_HOJE; ?>";
                    const CODIGO_CONTA = "<?php echo $codigo_conta; ?>";
                    const EMPRESA = "<?php echo $codigo_empresa; ?>";

                    function pesquisar_conta() {
                        sistema.request.post('/contas_pagar_receber.php', {
                            'rota': 'pesquisar_conta_fornecedor',
                            'codigo_conta': CODIGO_CONTA
                        }, (retorno) => {
                            let conta = retorno.dados;
                            console.log(conta);

                            document.querySelector('#nome_conta').value = conta.nome_conta;
                            document.querySelector('#descricao_conta').value = conta.descricao_conta;
                            
                            if (conta.status_conta == true) {
                                document.querySelector('#status_conta').value = 1;
                            } else {
                                document.querySelector('#status_conta').value = 0;
                            }
                            
                            document.querySelector('#data_cadastro').value = sistema.retornar_data(conta.data_cadastro, false,'us');
                            document.querySelector('#fornecedor').value = conta.codigo_usuario;
                        });
                    }

                    function pesquisar_fornecedores() {
                        sistema.request.post('/clientes.php', {
                            'rota': 'pesquisar_clientes',
                            'empresa': EMPRESA,
                            'tipo_usuario': ''
                        }, (retorno) => {
                            let fornecedores = retorno.dados;
                            let tamanho_retorno = fornecedores.length;

                            if (tamanho_retorno > 0) {
                                let select = document.querySelector('#fornecedor');

                                sistema.each(fornecedores, (index, fornecedor) => {
                                    select.appendChild(sistema.gerar_option(fornecedor.codigo_usuario, fornecedor.nome_usuario));
                                });
                            }
                        });
                    }

                    function salvar_dados() {
                        let fornecedor = document.querySelector('#fornecedor').value;
                        let nome_conta = document.querySelector('#nome_conta').value;
                        let descricao_conta = document.querySelector('#descricao_conta').value;
                        let status_conta = document.querySelector('#status_conta').value;
                        let data_cadastro = document.querySelector('#data_cadastro').value;

                        if (fornecedor == '') {
                            alerta_campo_vazio('FORNECEDOR');
                            return;
                        }

                        if (nome_conta == '') {
                            alerta_campo_vazio('NOME DA CONTA');
                            return;
                        }

                        if (descricao_conta == '') {
                            alerta_campo_vazio('DESCRIÇÃO');
                            return;
                        }

                        if (status_conta == '') {
                            alerta_campo_vazio = 'STATUS';
                            return;
                        }

                        sistema.request.post('/contas_pagar_receber.php', {
                            'rota': 'salvar_dados_conta_fornecedor',
                            'codigo_conta_fornecedor': CODIGO_CONTA,
                            'empresa': EMPRESA,
                            'fornecedor': fornecedor,
                            'nome_conta': nome_conta,
                            'descricao_conta': descricao_conta,
                            'status_conta': status_conta,
                            'data_cadastro': data_cadastro
                        }, function (retorno) {
                            validar_retorno(retorno, '/contas_pagar_receber.php', 0, 'contas_fornecedores_pesquisa');
                        });
                    }

                    function limpar_dados() {
                        document.querySelector('#fornecedor').value = '';
                        document.querySelector('#nome_conta').value = '';
                        document.querySelector('#descricao_conta').value = '';
                        document.querySelector('#status_conta').value = '';
                        document.querySelector('#data_cadastro').value = DATA_HOJE;
                    }

                    function voltar() {
                        window.location.href = sistema.url('/contas_pagar_receber.php', {
                            'rota': 'contas_fornecedores_pesquisa'
                        });
                    }
                </script>
                <div class="page-wrapper">
                    <div class="content">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title">Primeiro Cadastro de Contas</div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-3 text-center">
                                                <label class="text">Fornecedor</label>
                                                <select class="form-control select2" id="fornecedor">
                                                    <option value="">Selecione Um Opção</option>
                                                </select>
                                            </div>
                                            <div class="col-3 text-center">
                                                <label class="text">Nome da Conta</label>
                                                <input type="text" class="form-control text-uppercase" id="nome_conta">
                                            </div>
                                            <div class="col-3 text-center">
                                                <label class="text">Status</label>
                                                <select class="form-control select2" id="status_conta">
                                                    <option value="">Selecione uma Opção</option>
                                                    <option value="1">ATIVO</option>
                                                    <option value="0">INATIVO</option>
                                                </select>
                                            </div>
                                            <div class="col-3 text-center">
                                                <label class="text">Data Cadastro</label>
                                                <input type="date" class="form-control" id="data_cadastro">
                                            </div>
                                        </div>
                                        <br />
                                        <div class="row">
                                            <div class="col-12">
                                                <label class="text">Descrição Conta</label>
                                                <input type="text" class="form-control text-uppercase" id="descricao_conta">
                                            </div>
                                        </div>
                                        <br />
                                        <?php
                                        include_once 'includes/botao_cadastro.php';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        window.onload = () => {
                            pesquisar_fornecedores();

                            document.querySelector('#data_cadastro').value = DATA_HOJE;

                            if (CODIGO_CONTA != 0) {
                                pesquisar_conta();
                            }
                        }
                    </script>
                    <?php
                    include_once 'includes/footer.php';
});

/**
 * !Rota responsável por realizar a pesqusia do fornecedor a conta
 */
router_add('contas_fornecedores_pesquisa', function () {
    include_once 'includes/head.php';
    ?>
                    <script>
                        const EMPRESA = "<?php echo $codigo_empresa; ?>";

                        /**
                         * Função responsável por abrir a página para o cadastro de novas contas vinculadas a fornecedores
                         * @param {string} codigo_conta 
                         * */
                        function cadastro_contas(codigo_conta) {
                            window.location.href = sistema.url('/contas_pagar_receber.php', {
                                'rota': 'contas_fornecedores',
                                'codigo_conta': codigo_conta
                            });
                        }

                        function pesquisar_fornecedores() {
                            sistema.request.post('/clientes.php', {
                                'rota': 'pesquisar_clientes',
                                'empresa': EMPRESA,
                                'tipo_usuario': ''
                            }, (retorno) => {
                                let fornecedores = retorno.dados;
                                let tamanho_retorno = fornecedores.length;

                                if (tamanho_retorno > 0) {
                                    let select = document.querySelector('#fornecedor');

                                    sistema.each(fornecedores, (index, fornecedor) => {
                                        select.appendChild(sistema.gerar_option(fornecedor.codigo_usuario, fornecedor.nome_usuario));
                                    });
                                }
                            });
                        }

                        /** 
                         * Função responsável por fazer a pesquisa de contas com fornecedores vinculados
                         */
                        function pesquisar_contas_fornecedores() {
                            barra_progresso('Carregando contas fornecedores...');

                            let fornecedor = document.querySelector('#fornecedor').value;
                            let nome_conta = document.querySelector('#nome_conta').value;
                            let status_conta = document.querySelector('#status_conta').value;
                            let data_cadastro = document.querySelector('#data_cadastro').value;

                            sistema.request.post('/contas_pagar_receber.php', {
                                'rota': 'pesquisar_contas_fornecedores',
                                'empresa': EMPRESA,
                                'fornecedor': fornecedor,
                                'nome_conta': nome_conta,
                                'status_conta': status_conta,
                                'data_cadastro': data_cadastro
                            }, (retorno) => {
                                let contas = retorno.dados;
                                let tamanho_retorno = contas.length;
                                let tabela = document.querySelector('#tabela_fornecedor_conta tbody');
                                let index = 0;

                                tabela = sistema.remover_linha_tabela(tabela);

                                if (tamanho_retorno == 0) {
                                    let linha = document.createElement('tr');
                                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], 'NENHUMA CONTA ENCONTRADA, COM OS FILTROS PASSADOS!', 'inner', true, 15));
                                    tabela.appendChild(linha);

                                    Swal.fire({ icon: 'warning', title: 'Nenhuma conta encontrada!' });
                                    return;
                                }

                                function processar_item() {
                                    if (index >= tamanho_retorno) {
                                        Swal.close();
                                        return;
                                    }

                                    let conta = contas[index];

                                    let linha = document.createElement('tr');

                                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], conta.codigo_conta_fornecedor, 'inner'));
                                    linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], conta.fornecedor.nome_fornecedor, 'inner'));
                                    linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], conta.nome_conta, 'inner'));
                                    linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], sistema.cortar_string(conta.descricao_conta, 30), 'inner', false, '', conta.descricao_conta));

                                    if (conta.status_conta == true) {
                                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_conta_' + conta.codigo_conta_fornecedor, 'ATIVO', ['btn', 'btn-outline-success'], () => { }), 'append'));
                                    } else {
                                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_conta_' + conta.codigo_conta_fornecedor, 'INATIVO', ['btn', 'btn-outline-danger'], () => { }), 'append'));
                                    }

                                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.retornar_data(conta.data_cadastro), 'inner'));
                                    linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_editar_conta_' + conta.codigo_conta_fornecedor, 'EDITAR', ['btn', 'btn-secondary'], () => {
                                        cadastro_contas(conta.codigo_conta_fornecedor);
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
                                    <h6>Vincular Contas aos Fornecedores</h6>
                                </div>
                                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                                    <div class="dropdown">
                                        <button class="btn btn-primary d-flex align-items-center justify-content-center"
                                            onclick="cadastro_contas('');">
                                            Fazer Vínculo
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="card">
                                        <div class="card-header justify-content-between">
                                            <div class="card-title">Pesquisa de Fornecedores Vinculados</div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-3 text-center">
                                                    <label class="text">Fornecedor</label>
                                                    <select class="form-control select2" id="fornecedor">
                                                        <option value="">Selecione Um Opção</option>
                                                    </select>
                                                </div>
                                                <div class="col-3 text-center">
                                                    <label class="text">Nome da Conta</label>
                                                    <input type="text" class="form-control" id="nome_conta">
                                                </div>
                                                <div class="col-3 text-center">
                                                    <label class="text">Status</label>
                                                    <select class="form-control select2" id="status_conta">
                                                        <option value="TODOS">TODOS</option>
                                                        <option value="ATIVO">ATIVO</option>
                                                        <option value="INATIVO">INATIVO</option>
                                                    </select>
                                                </div>
                                                <div class="col-3 text-center">
                                                    <label class="text">Data Cadastro</label>
                                                    <input type="date" class="form-control" id="data_cadastro">
                                                </div>
                                            </div>
                                            <br />
                                            <div class="row">
                                                <div class="col-3 push-9">
                                                    <button class="btn btn-secondary w-100"
                                                        onclick="pesquisar_contas_fornecedores();">PESQUISAR</button>
                                                </div>
                                            </div>
                                            <br />
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="table-responsive">
                                                        <table class="table table-nowrap text-nowrap table-hover"
                                                            id="tabela_fornecedor_conta">
                                                            <thead>
                                                                <tr class="text-center text-uppercase">
                                                                    <th scope="col">#</th>
                                                                    <th scope="col">Nome Cliente/Fornecedor</th>
                                                                    <th scope="col">Nome conta</th>
                                                                    <th scope="col">Descrição</th>
                                                                    <th scope="col">Status</th>
                                                                    <th scope="col">Data Cadastro</th>
                                                                    <th scope="col">Editar</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td colspan="10" class="text-center">UTILIZE O FILTRO
                                                                        PARA FACILITAR A PESQUISA!</td>
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
                                pesquisar_contas_fornecedores();
                            }
                        </script>
                        <?php
                        include_once 'includes/footer.php';
                        exit;
});
?>