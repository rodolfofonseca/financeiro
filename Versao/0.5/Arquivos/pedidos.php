<?php
require_once 'classes/bancoDeDados.php';
require_once 'classes/CodigoBarras/EAN13.php';

require_once 'modelos/Pedidos.php';
require_once 'modelos/Usuario.php';
require_once 'modelos/Empresa.php';
require_once 'modelos/ContasPagarReceber.php';
require_once 'modelos/ItensPedidos.php';
require_once 'modelos/Produtos.php';

router_add('cancelar_pedido', function(){
    $objeto_pedido = new Pedidos();

    echo json_encode(['status' => (bool) $objeto_pedido->cancelar_pedido($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

/** 
 * Rota responsável por chamar a rota de salvar as informações do pedido
*/
router_add('salvar_pedidos', function () {
    $objeto_pedidos = new Pedidos();

    echo json_encode(['status' => (bool) $objeto_pedidos->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * Rota responsável por pesquisar os pedidos de acordo com os filtros selecionados. Ela recebe os parâmetros de empresa, tipo, data de cadastro, data de movimentação e status do pedido, e constrói um filtro para a pesquisa. Em seguida, ela chama o método 'pesquisar_todos' do objeto Pedidos, passando o filtro construído, e retorna os resultados em formato JSON. Essa rota é utilizada para atualizar a tabela de pedidos com os resultados filtrados quando o usuário realiza uma pesquisa.
 */
router_add('pesquisar_pedidos', function () {
    $objeto_pedidos = new Pedidos();
    $objeto_fornecedor = new Usuario();

    $retorno_pesquisa = (array) $objeto_pedidos->pesquisa($_REQUEST);
    $retorno_final = (array) [];

    if (empty($retorno_pesquisa) == false) {
        foreach ($retorno_pesquisa as $pedidos) {
            $filtro = ['filtro' => (array) ['_id', '===', $pedidos['fornecedor']]];
            $retorno_usuario = (array) $objeto_fornecedor->pesquisar($filtro);
            $fornecedor = (array) ['nome' => (string) ''];

            if (empty($retorno_usuario) == false) {
                $fornecedor['nome'] = (string) $retorno_usuario['nome_usuario'];
            }

            $pedidos['fornecedor_dados'] = (array) $fornecedor;

            array_push($retorno_final, $pedidos);
        }
    }

    echo json_encode((array) ['dados' => $retorno_final], JSON_UNESCAPED_UNICODE);
});

/** 
 * Rota para a página de pedidos, mas redireciona para o dashboard, pois a funcionalidade de index não existe.
 */
router_add('index', function () {
    header('Location: dashboard.php');
});

/**
 * Rota responsável por alterar o tipo de pedido
 */
router_add('editar_tipo_pedido_entrada', function () {
    $objeto_pedido = new Pedidos();

    echo json_encode(['status' => (bool) $objeto_pedido->alterar_status_pedido($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

/** 
 * Rota responsável por fazer a impressão dos pedidos de entrada
 */
router_add('imprimir_pedido_entrada', function () {
    include_once 'includes/head_sem_menu.php';

    $codigo_pedido = (string) (isset($_REQUEST['codigo_pedido']) ? (string) $_REQUEST['codigo_pedido'] : '');
    $tipo_pedido = (string) (isset($_REQUEST['tipo_pedido']) ? (string) $_REQUEST['tipo_pedido'] : '');

    if ($codigo_pedido != '') {
        $objeto_empresa = new Empresa();
        $objeto_pedido = new Pedidos();
        $objeto_itens_pedido = new ItensPedidos();
        $objeto_contas_pagar_receber = new ContasPagarReceber();
        $objeto_usuario = new Usuario();
        $objeto_produtos = new Produtos();

        $empresa = (array) $objeto_empresa->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_empresa)]]);
        $pedido = (array) $objeto_pedido->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_pedido)]]);
        $fornecedor = (array) $objeto_usuario->pesquisar((array) ['filtro' => (array) ['_id', '===', $pedido['fornecedor']]]);
        $produtos = (array) $objeto_itens_pedido->pesquisar_todos((array) ['filtro' => (array) ['pedido', '===', model_id($codigo_pedido)], 'ordenacao' => [], 'limite' => (int) 0]);

        $conta = $objeto_contas_pagar_receber->pesquisar((array) ['filtro' => (array) ['transacao', '===', $pedido['transacao']]]);

        $logo_empresa = (string) 'imagens/imagens_sistema/logo_empresa_preto.jpg';
        $cnpj = (string) '';
        $endereco = (string) '';
        $telefone = (string) '';
        $nome_empresa = (string) '';
        $cidade = (string) '';

        if (empty($empresa) == false) {
            if (array_key_exists('logo', $empresa) == true) {
                $logo_empresa = (string) $empresa['logo'];
            }

            if (array_key_exists('telefone', $empresa) == true) {
                $telefone = (string) $empresa['telefone'];
            }

            $cnpj = (string) $empresa['cnpj'];
            $endereco = (string) $empresa['endereco'];
            $nome_empresa = (string) $empresa['nome_empresa'];
            $cidade = (string) $empresa['cidade'];
        }

        ?>
        <style>
            @page {
                size: A4;
                margin: 10mm;
            }

            body {
                background: #f5f5f5;
                font-size: 13px;
            }

            .pedido {
                background: #fff;
                padding: 20px;
                border: 1px solid #ccc;
            }

            .box {
                border: 1px solid #000;
                margin-bottom: 8px;
            }

            .box-header {
                background: #e9ecef;
                font-weight: 700;
                font-size: 11px;
                padding: 4px 8px;
                border-bottom: 1px solid #000;
                text-transform: uppercase;
            }

            .box-body {
                padding: 8px;
            }

            .titulo-documento {
                font-size: 28px;
                font-weight: bold;
                letter-spacing: 1px;
            }

            .numero-pedido {
                font-size: 24px;
                font-weight: bold;
            }

            .campo-label {
                font-size: 10px;
                text-transform: uppercase;
                color: #666;
                font-weight: 600;
            }

            .campo-valor {
                font-size: 14px;
                font-weight: 500;
            }

            .table-produtos th {
                font-size: 12px;
                text-transform: uppercase;
            }

            .table-produtos td {
                font-size: 13px;
            }

            .resumo-total td {
                padding: 6px;
            }

            .total-geral {
                font-size: 18px;
                font-weight: bold;
            }

            .assinatura {
                height: 70px;
                border-bottom: 1px solid #000;
                margin-bottom: 5px;
            }

            .logo {
                width: 80px;
                height: 80px;
                border: 1px solid #ccc;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
            }

            .altura {
                height: 160px;
            }

            @media print {

                html,
                body {
                    width: 210mm;
                    font-size: 11px;
                    background: #fff !important;
                }

                .pedido {
                    border: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .box {
                    page-break-inside: avoid;
                }

                .table {
                    font-size: 10px;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                .no-print {
                    display: none !important;
                }

                .campo-label {
                    font-size: 9px;
                }

                .campo-valor {
                    font-size: 11px;
                }

                .altura {
                    height: auto !important;
                    min-height: unset !important;
                    max-height: unset !important;
                }

                .logo {
                    width: 80px;
                    height: 80px;
                    border: 1px solid #ccc;
                    display: flex;
                    align-items: right;
                    justify-content: center;
                    font-weight: bold;
                }
            }
        </style>
        <div class="container-fluid my-3">
            <div class="pedido">
                <div class="box">
                    <div class="box-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <div class="logo mx-auto"> <img src="<?php echo $logo_empresa; ?>"> </div>
                            </div>
                            <div class="col-md-7">
                                <h3 class="mb-1"> <?php echo $nome_empresa; ?> </h3>
                                <div>CNPJ: <?php echo $cnpj; ?></div>
                                <div><?php echo $endereco; ?></div>
                                <div><?php echo $cidade; ?></div>
                                <div><?php echo $telefone; ?></div>
                            </div>
                            <div class="col-md-3 text-center border">
                                <div class="titulo-documento"> PEDIDO </div>
                                <div class="numero-pedido"> Nº <?php echo $pedido['transacao']; ?> </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="box">

                    <div class="box-header">
                        Informações do Pedido
                    </div>

                    <div class="box-body">

                        <div class="row g-3">

                            <div class="col-2">
                                <div class="campo-label">Data Emissão</div>
                                <div class="campo-valor"><?php echo convert_date($pedido['data_cadastro'], 'd/m/Y'); ?></div>
                            </div>

                            <div class="col-2">
                                <div class="campo-label">Data Entrada</div>
                                <div class="campo-valor"><?php echo convert_date($pedido['data_movimentacao'], 'd/m/Y'); ?>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="campo-label">Fornecedor</div>
                                <div class="campo-valor"><?php echo $fornecedor['nome_usuario']; ?></div>
                            </div>

                            <div class="col-3">
                                <div class="campo-label">Condição PGTO</div>
                                <div class="campo-valor">Á Vista</div>
                            </div>

                            <div class="col-2">
                                <div class="campo-label">Status</div>
                                <div class="campo-valor">
                                    <?php
                                    if ($pedido['status'] == 'PEDIDO') {
                                        echo "<span class='badge bg-secondary'>Orçamento</span>";
                                    } else {
                                        echo "<span class='badge bg-success'>Finalizado</span>";
                                    }
                                    ?>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="box">

                    <div class="box-header">
                        Dados do Fornecedor
                    </div>

                    <div class="box-body">

                        <div class="row g-3">

                            <div class="col-6">
                                <div class="campo-label">Razão Social</div>
                                <div class="campo-valor">
                                    <?php echo $fornecedor['nome_usuario']; ?>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="campo-label">CPF/CNPJ</div>
                                <div class="campo-valor">
                                    <?php
                                    if (array_key_exists('cpf_cnpj', $fornecedor) == true) {
                                        echo $fornecedor['cpf_cnpj'];
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="campo-label">Telefone</div>
                                <div class="campo-valor">
                                    <?php echo $fornecedor['celular']; ?>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>


                <div class="box">

                    <div class="box-header">
                        Dados da Nota Fiscal
                    </div>

                    <div class="box-body">

                        <div class="row g-3">

                            <div class="col-2">
                                <div class="campo-label">Número NF</div>
                                <div class="campo-valor">

                                </div>
                            </div>

                            <div class="col-1">
                                <div class="campo-label">Série</div>
                                <div class="campo-valor">

                                </div>
                            </div>

                            <div class="col-2">
                                <div class="campo-label">Emissão</div>
                                <div class="campo-valor">

                                </div>
                            </div>

                            <div class="col-7">
                                <div class="campo-label">Chave NF-e</div>
                                <div class="campo-valor text-break">

                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="box">
                    <div class="box-header"> Produtos Recebidos </div>
                    <div class="box-body p-0">
                        <table class="table table-bordered table-hover table-produtos mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Código</th>
                                    <th>Descrição</th>
                                    <th>UN</th>
                                    <th class="text-center">Qtd</th>
                                    <?php if ($tipo_pedido == 'COMPLETO') {
                                        echo "<th class='text-end'>Custo Unit.</th>";
                                        echo "<th class='text-end'>Desconto</th>";
                                        echo "<th class='text-end'>Frete</th>";
                                        echo "<th class='text-end'>Total</th>";

                                    } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                foreach ($produtos as $item) {
                                    $filtro = (array) ['filtro' => ['_id', '===', $item['produto']]];
                                    $produto_pesquisa = $objeto_produtos->pesquisar($filtro);

                                    echo "<tr class='fw-bold'>";
                                    echo "<td>" . $produto_pesquisa['codigo_barras'] . "</td>";
                                    echo "<td>" . $produto_pesquisa['nome_produto'] . "</td>";
                                    echo "<td>" . $produto_pesquisa['unidade_medida'] . "</td>";
                                    echo "<td>" . formatar_numero($item['quantidade'], 3, ',', '.') . "</td>";

                                    if ($tipo_pedido == 'COMPLETO') {
                                        echo "<td>" . formatar_numero($item['valor_unitario'], 2, ',', '.') . "</td>";
                                        echo "<td>" . formatar_numero($item['valor_desconto'], 2, ',', '.') . "</td>";
                                        echo "<td>" . formatar_numero($item['valor_frete'], 2, ',', '.') . "</td>";
                                        echo "<td>" . formatar_numero($item['valor_liquido'], 2, ',', '.') . "</td>";

                                    }
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php
                if ($tipo_pedido == 'COMPLETO') {
                    ?>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="box">
                                <div class="box-header"> Observações </div>
                                <div class="box-body altura">
                                    <?php
                                    $texto = '';

                                    if (array_key_exists('observacao', $pedido) == true) {
                                        $texto = $pedido['observacao'];
                                    }

                                    if ($texto != '') {
                                        $texto = $texto . "<br/>";
                                    }

                                    if (empty($conta) == false) {
                                        if ($conta['status_conta'] == 'AGUARDANDO' || $conta['status_conta'] == 'VENCIDA' || $conta['status_conta'] == 'VENCIDO') {
                                            $texto = $texto . "Conta a Pagar - Vencimento: " . convert_date($conta['data_vencimento'], 'd/m/Y') . " - Valor: R$ " . formatar_numero($conta['valor_conta'], 2, ',', '.');
                                        } else {
                                            $texto = $texto . "Conta PAGA - Vencimento: " . convert_date($conta['data_vencimento'], 'd/m/Y') . " - Valor: R$ " . formatar_numero($conta['valor_conta'], 2, ',', '.') . " - Pagamento realizado em: " . convert_date($conta['data_baixa'], 'd/m/Y');
                                        }

                                    }

                                    echo $texto;

                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-bordered resumo-total">
                                <tr>
                                    <td>Valor Produtos</td>
                                    <td class="text-end">R$ <?php echo formatar_numero($pedido['valor_bruto'], 2, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td>Frete</td>
                                    <td class="text-end">R$ <?php echo formatar_numero($pedido['valor_frete'], 2, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td>Desconto</td>
                                    <td class="text-end">R$ <?php echo formatar_numero($pedido['valor_desconto'], 2, ',', '.'); ?>
                                    </td>
                                </tr>
                                <tr class="table-secondary">
                                    <td> <strong>TOTAL GERAL</strong> </td>
                                    <td class="text-end total-geral"> R$
                                        <?php echo formatar_numero($pedido['valor_liquido'], 2, ',', '.'); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <br />

                <div class="box">
                    <div class="box-header"> Conferência e Recebimento </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6 text-center">
                                <div class="assinatura"></div> <strong>Recebido por</strong>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="assinatura"></div> <strong>Conferido por</strong>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="text-end mt-3 no-print"> <button class="btn btn-primary" onclick="window.print()"> Imprimir Pedido
                    </button> </div>
            </div>
        </div>

        <?php
    }
    include_once 'includes/footer_sem.php';
});

router_add('imprimir_pedido_saida', function () {
    include_once 'includes/head_sem_menu.php';

    $codigo_pedido = (string) (isset($_REQUEST['codigo_pedido']) ? (string) $_REQUEST['codigo_pedido'] : '');
    $tipo_pedido = (string) (isset($_REQUEST['tipo_pedido']) ? (string) $_REQUEST['tipo_pedido'] : '');
    if ($codigo_pedido != '') {
        $objeto_empresa = new Empresa();
        $objeto_pedido = new Pedidos();
        $objeto_itens_pedido = new ItensPedidos();
        $objeto_contas_pagar_receber = new ContasPagarReceber();
        $objeto_usuario = new Usuario();
        $objeto_produtos = new Produtos();

        $empresa = (array) $objeto_empresa->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_empresa)]]);
        $pedido = (array) $objeto_pedido->pesquisar((array) ['filtro' => (array) ['_id', '===', model_id($codigo_pedido)]]);
        $fornecedor = (array) $objeto_usuario->pesquisar((array) ['filtro' => (array) ['_id', '===', $pedido['fornecedor']]]);
        $produtos = (array) $objeto_itens_pedido->pesquisar_todos((array) ['filtro' => (array) ['pedido', '===', model_id($codigo_pedido)], 'ordenacao' => [], 'limite' => (int) 0]);

        $conta = $objeto_contas_pagar_receber->pesquisar((array) ['filtro' => (array) ['transacao', '===', $pedido['transacao']]]);

        $logo_empresa = (string) 'imagens/imagens_sistema/logo_empresa_preto.jpg';
        $cnpj = (string) '';
        $endereco = (string) '';
        $telefone = (string) '';
        $nome_empresa = (string) '';
        $cidade = (string) '';

        if (empty($empresa) == false) {
            if (array_key_exists('logo', $empresa) == true) {
                $logo_empresa = (string) $empresa['logo'];
            }

            if (array_key_exists('telefone', $empresa) == true) {
                $telefone = (string) $empresa['telefone'];
            }

            $cnpj = (string) $empresa['cnpj'];
            $endereco = (string) $empresa['endereco'];
            $nome_empresa = (string) $empresa['nome_empresa'];
            $cidade = (string) $empresa['cidade'];
        }

        ?>
        <style>
            @page {
                size: A4;
                margin: 10mm;
            }

            body {
                background: #f5f5f5;
                font-size: 13px;
            }

            .pedido {
                background: #fff;
                padding: 20px;
                border: 1px solid #ccc;
            }

            .box {
                border: 1px solid #000;
                margin-bottom: 8px;
            }

            .box-header {
                background: #e9ecef;
                font-weight: 700;
                font-size: 11px;
                padding: 4px 8px;
                border-bottom: 1px solid #000;
                text-transform: uppercase;
            }

            .box-body {
                padding: 8px;
            }

            .titulo-documento {
                font-size: 28px;
                font-weight: bold;
                letter-spacing: 1px;
            }

            .numero-pedido {
                font-size: 24px;
                font-weight: bold;
            }

            .campo-label {
                font-size: 10px;
                text-transform: uppercase;
                color: #666;
                font-weight: 600;
            }

            .campo-valor {
                font-size: 14px;
                font-weight: 500;
            }

            .table-produtos th {
                font-size: 12px;
                text-transform: uppercase;
            }

            .table-produtos td {
                font-size: 13px;
            }

            .resumo-total td {
                padding: 6px;
            }

            .total-geral {
                font-size: 18px;
                font-weight: bold;
            }

            .assinatura {
                height: 70px;
                border-bottom: 1px solid #000;
                margin-bottom: 5px;
            }

            .logo {
                width: 80px;
                height: 80px;
                border: 1px solid #ccc;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
            }

            .altura {
                height: 160px;
            }

            @media print {

                html,
                body {
                    width: 210mm;
                    font-size: 11px;
                    background: #fff !important;
                }

                .pedido {
                    border: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .box {
                    page-break-inside: avoid;
                }

                .table {
                    font-size: 10px;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                .no-print {
                    display: none !important;
                }

                .campo-label {
                    font-size: 9px;
                }

                .campo-valor {
                    font-size: 11px;
                }

                .altura {
                    height: auto !important;
                    min-height: unset !important;
                    max-height: unset !important;
                }

                .logo {
                    width: 80px;
                    height: 80px;
                    border: 1px solid #ccc;
                    display: flex;
                    align-items: right;
                    justify-content: center;
                    font-weight: bold;
                }
            }
        </style>
        <div class="container-fluid my-3">
            <div class="pedido">
                <div class="box">
                    <div class="box-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <div class="logo mx-auto"> <img src="<?php echo $logo_empresa; ?>"> </div>
                            </div>
                            <div class="col-md-7">
                                <h3 class="mb-1"> <?php echo $nome_empresa; ?> </h3>
                                <div>CNPJ: <?php echo $cnpj; ?></div>
                                <div><?php echo $endereco; ?></div>
                                <div><?php echo $cidade; ?></div>
                                <div><?php echo $telefone; ?></div>
                            </div>
                            <div class="col-md-3 text-center border">
                                <div class="titulo-documento"> PEDIDO </div>
                                <div class="numero-pedido"> Nº <?php echo $pedido['transacao']; ?> </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="box">

                    <div class="box-header">
                        Informações do Pedido
                    </div>

                    <div class="box-body">

                        <div class="row g-3">

                            <div class="col-2">
                                <div class="campo-label">Data Emissão</div>
                                <div class="campo-valor"><?php echo convert_date($pedido['data_cadastro'], 'd/m/Y'); ?></div>
                            </div>

                            <div class="col-2">
                                <div class="campo-label">Data Entrada</div>
                                <div class="campo-valor"><?php echo convert_date($pedido['data_movimentacao'], 'd/m/Y'); ?>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="campo-label">Fornecedor</div>
                                <div class="campo-valor"><?php echo $fornecedor['nome_usuario']; ?></div>
                            </div>

                            <div class="col-3">
                                <div class="campo-label">Condição PGTO</div>
                                <div class="campo-valor">Á Vista</div>
                            </div>

                            <div class="col-2">
                                <div class="campo-label">Status</div>
                                <div class="campo-valor">
                                    <?php
                                    if ($pedido['status'] == 'PEDIDO') {
                                        echo "<span class='badge bg-secondary'>Orçamento</span>";
                                    } else {
                                        echo "<span class='badge bg-success'>Finalizado</span>";
                                    }
                                    ?>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="box">

                    <div class="box-header">
                        Dados do Cliente
                    </div>

                    <div class="box-body">

                        <div class="row g-3">

                            <div class="col-6">
                                <div class="campo-label">Razão Social</div>
                                <div class="campo-valor">
                                    <?php echo $fornecedor['nome_usuario']; ?>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="campo-label">CPF/CNPJ</div>
                                <div class="campo-valor">
                                    <?php
                                    if (array_key_exists('cpf_cnpj', $fornecedor) == true) {
                                        echo $fornecedor['cpf_cnpj'];
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="campo-label">Telefone</div>
                                <div class="campo-valor">
                                    <?php echo $fornecedor['celular']; ?>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>


                <div class="box">

                    <div class="box-header">
                        Dados da Nota Fiscal
                    </div>

                    <div class="box-body">

                        <div class="row g-3">

                            <div class="col-2">
                                <div class="campo-label">Número NF</div>
                                <div class="campo-valor">

                                </div>
                            </div>

                            <div class="col-1">
                                <div class="campo-label">Série</div>
                                <div class="campo-valor">

                                </div>
                            </div>

                            <div class="col-2">
                                <div class="campo-label">Emissão</div>
                                <div class="campo-valor">

                                </div>
                            </div>

                            <div class="col-7">
                                <div class="campo-label">Chave NF-e</div>
                                <div class="campo-valor text-break">

                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="box">
                    <div class="box-header"> Produtos Vendidos </div>
                    <div class="box-body p-0">
                        <table class="table table-bordered table-hover table-produtos mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Código</th>
                                    <th>Descrição</th>
                                    <th>UN</th>
                                    <th class="text-center">Qtd</th>
                                    <?php if ($tipo_pedido == 'COMPLETO') {
                                        echo "<th class='text-end'>Custo Unit.</th>";
                                        echo "<th class='text-end'>Desconto</th>";
                                        echo "<th class='text-end'>Frete</th>";
                                        echo "<th class='text-end'>Total</th>";

                                    } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                foreach ($produtos as $item) {
                                    $filtro = (array) ['filtro' => ['_id', '===', $item['produto']]];
                                    $produto_pesquisa = $objeto_produtos->pesquisar($filtro);

                                    echo "<tr class='fw-bold'>";
                                    echo "<td>" . $produto_pesquisa['codigo_barras'] . "</td>";
                                    echo "<td>" . $produto_pesquisa['nome_produto'] . "</td>";
                                    echo "<td>" . $produto_pesquisa['unidade_medida'] . "</td>";
                                    echo "<td>" . formatar_numero($item['quantidade'], 3, ',', '.') . "</td>";

                                    if ($tipo_pedido == 'COMPLETO') {
                                        echo "<td>" . formatar_numero($item['valor_unitario'], 2, ',', '.') . "</td>";
                                        echo "<td>" . formatar_numero($item['valor_desconto'], 2, ',', '.') . "</td>";
                                        echo "<td>" . formatar_numero($item['valor_frete'], 2, ',', '.') . "</td>";
                                        echo "<td>" . formatar_numero($item['valor_liquido'], 2, ',', '.') . "</td>";

                                    }
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php
                if ($tipo_pedido == 'COMPLETO') {
                    ?>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="box">
                                <div class="box-header"> Observações </div>
                                <div class="box-body altura">
                                    <?php
                                    $texto = '';

                                    if (array_key_exists('observacao', $pedido) == true) {
                                        $texto = $pedido['observacao'];
                                    }

                                    if ($texto != '') {
                                        $texto = $texto . "<br/>";
                                    }

                                    if (empty($conta) == false) {
                                        if ($conta['status_conta'] == 'AGUARDANDO' || $conta['status_conta'] == 'VENCIDA' || $conta['status_conta'] == 'VENCIDO') {
                                            $texto = $texto . "Conta a Receber - Vencimento: " . convert_date($conta['data_vencimento'], 'd/m/Y') . " - Valor: R$ " . formatar_numero($conta['valor_conta'], 2, ',', '.');
                                        } else {
                                            $texto = $texto . "Conta RECEBIDA - Vencimento: " . convert_date($conta['data_vencimento'], 'd/m/Y') . " - Valor: R$ " . formatar_numero($conta['valor_conta'], 2, ',', '.') . " - Pagamento realizado em: " . convert_date($conta['data_baixa'], 'd/m/Y');
                                        }

                                    }

                                    echo $texto;
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-bordered resumo-total">
                                <tr>
                                    <td>Valor Produtos</td>
                                    <td class="text-end">R$ <?php echo formatar_numero($pedido['valor_bruto'], 2, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td>Frete</td>
                                    <td class="text-end">R$ <?php echo formatar_numero($pedido['valor_frete'], 2, ',', '.'); ?></td>
                                </tr>
                                <tr>
                                    <td>Desconto</td>
                                    <td class="text-end">R$ <?php echo formatar_numero($pedido['valor_desconto'], 2, ',', '.'); ?>
                                    </td>
                                </tr>
                                <tr class="table-secondary">
                                    <td> <strong>TOTAL GERAL</strong> </td>
                                    <td class="text-end total-geral"> R$
                                        <?php echo formatar_numero($pedido['valor_liquido'], 2, ',', '.'); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <br />

                <div class="box">
                    <div class="box-header"> Conferência e Recebimento </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6 text-center">
                                <div class="assinatura"></div> <strong>Recebido por</strong>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="assinatura"></div> <strong>Conferido por</strong>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="text-end mt-3 no-print"> <button class="btn btn-primary" onclick="window.print()"> Imprimir Pedido
                    </button> </div>
            </div>
        </div>

        <?php
    }
    include_once 'includes/footer_sem.php';
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
            let transacao = document.querySelector('#transacao').value;
            let tipo = true;

            let dados = {
                'rota': 'pesquisar_pedidos',
                'fornecedor': fornecedor,
                'status_pedido': status_pedido,
                'data_cadastro': data_cadastro,
                'data_movimentacao': data_movimentacao,
                'tipo_pedido': tipo,
                'empresa': EMPRESA,
                'transacao': transacao
            };

            barra_progresso('Carregando pedidos de entrada...');

            sistema.request.post('/pedidos.php', dados, function (retorno) {
                let pedidos = retorno.dados;
                let tamanho_retorno = pedidos.length;
                let tabela = document.querySelector('#tabela_pedidos tbody');
                let index = 0;

                tabela = sistema.remover_linha_tabela(tabela);


                if (tamanho_retorno == 0) {
                    let linha = document.createElement('tr');
                    linha.appendChild(sistema.gerar_td(['text-center'], 'UTILIZE OS FILTROS PARA FACILITAR SUA PESQUISA!', 'inner', true, 10));
                    tabela.appendChild(linha);

                    Swal.fire({ icon: 'warning', title: 'Nenhum pedido encontrado!' });
                    return;
                }

                function processar_item() {
                    if (index >= tamanho_retorno) {
                        Swal.close();
                        return;
                    }

                    let pedido = pedidos[index];
                    let linha = document.createElement('tr');

                    linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], pedido.fornecedor_dados.nome, 'inner'));

                    if (pedido.status == 'PEDIDO') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_' + pedido._id.$oid, 'PEDIDO', ['btn', 'btn-outline-primary'], function alterar_status_pedido_botao() { alterar_status_pedido(pedido._id.$oid, pedido.status); }), 'append'));
                    } else if (pedido.status == 'PEDIDO_ESTOQUE') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_' + pedido._id.$oid, 'PEDIDO ESTOQUE', ['btn', 'btn-outline-secondary'], function alterar_status_pedido_botao() { alterar_status_pedido(pedido._id.$oid, pedido.status); }), 'append'));
                    } else if (pedido.status == 'PEDIDO_CONTA') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_' + pedido._id.$oid, 'PEDIDO CONTA', ['btn', 'btn-outline-info'], function alterar_status_pedido_botao() { alterar_status_pedido(pedido._id.$oid, pedido.status); }), 'append'));
                    } else if (pedido.status == 'PEDIDO_COMPLETO') {
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_' + pedido._id.$oid, 'PEDIDO COMPLETO', ['btn', 'btn-outline-success'], function alterar_status_pedido_botao() { alterar_status_pedido(pedido._id.$oid, pedido.status); }), 'append'));
                    }

                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], pedido.transacao, 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.retornar_data(pedido.data_cadastro, '', true), 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.retornar_data(pedido.data_movimentacao, '', true), 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-end', 'fw-bold'], sistema.number_format(pedido.valor_bruto, 2, ',', '.'), 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-end', 'fw-bold', 'text-danger'], sistema.number_format(pedido.valor_desconto, 2, ',', '.'), 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-end', 'fw-bold', 'text-warning'], sistema.number_format(pedido.valor_frete, 2, ',', '.'), 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-end', 'fw-bold', 'text-success'], sistema.number_format(pedido.valor_liquido, 2, ',', '.'), 'inner'));
                    linha.appendChild(sistema.gerar_td(['text-center',], sistema.gerar_botao('botao_impressao_' + pedido._id.$oid, 'IMPRIMIR SIMPLES', ['btn', 'btn-blue'], function modal_impressao() { abrir_modal_impressao_pedido(pedido._id.$oid, 'SIMPLE'); }), 'append'));
                    linha.appendChild(sistema.gerar_td(['text-center',], sistema.gerar_botao('botao_impressao_' + pedido._id.$oid, 'IMPRIMIR COMPLETO', ['btn', 'btn-blue'], function modal_impressao() { abrir_modal_impressao_pedido(pedido._id.$oid, 'COMPLETO'); }), 'append'));

                    tabela.appendChild(linha);

                    atualizar_barra_progresso(index, tamanho_retorno, document.querySelector('#barra_progresso'), document.querySelector('#texto_progresso'));

                    index++;
                    setTimeout(processar_item, 1);
                }

                processar_item();
            });
        }

        /**
         * Função responsável por realizar a troca do status do pedido
         * @param {string} codigo_pedido 
         * @param {string} status
         * */
        async function alterar_status_pedido(codigo_pedido, status) {
            let dados = {
                PEDIDO: "PEDIDO",
                PEDIDO_ESTOQUE: "PEDIDO + ESTOQUE",
                PEDIDO_CONTA: "PEDIDO + CONTA",
                PEDIDO_COMPLETO: "PEDIDO COMPLETO (CONTA + ESTOQUE)",
            };

            const { value: fruit } = await Swal.fire({
                title: "Selecione um status do pedido",
                input: "select",
                inputOptions: dados,
                inputPlaceholder: "Selecione um status",
                showCancelButton: true,
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    return new Promise((resolve) => {
                        if (value != "") resolve();
                        else resolve("Selecione uma status");
                    });
                }
            });
            if (fruit) {
                sistema.request.post('/pedidos.php', { 'rota': 'editar_tipo_pedido_entrada', 'codigo_pedido': codigo_pedido, 'status_pedido': fruit }, function (retorno) {
                    validar_retorno(retorno, '/pedidos.php');
                });
            }
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

        /**
         * Função responsável por abrir o modal de impressão do pedido
         * @param {*} codigo_pedido 
         * @param {*} tipo_pedido
         * */
        function abrir_modal_impressao_pedido(codigo_pedido, tipo_pedido) {
            let largura = 1200;
            let altura = 500;
            let left = (screen.width - largura) / 2;
            let top = (screen.height - altura) / 2;
            let url = sistema.url('/pedidos.php', {
                'rota': 'imprimir_pedido_entrada',
                'codigo_pedido': codigo_pedido,
                'tipo_pedido': tipo_pedido
            });

            let nome = 'Impressão de Pedidos';
            let janela = window.open(url, nome, `width=${largura}, height=${altura}, left=${left}, top=${top}`);

            if (window.focus) {
                janela.focus();
            }
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
                                    <select class="form-control select2" id="fornecedor">
                                        <option value="">Selecione um fornecedor</option>
                                    </select>
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">TIPO</label>
                                    <select class="form-control select2" id="status_pedido">
                                        <option value="">TODOS</option>
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
                                <div class="col-3 text-center">
                                    <label class="text">Transação</label>
                                    <input type="text" class="form-control" id="transacao">
                                </div>
                            </div>
                            <br />
                            <div class="row">
                                <div class="col-3 push-9">
                                    <label class="text"></label>
                                    <button class="btn btn-secondary w-100"
                                        onclick="pesquisar_pedidos();">Pesquisar</button>
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
                                                    <th scope="col">TRANSAÇÃO</th>
                                                    <th scope="col">DATA CADASTRO</th>
                                                    <th scope="col">DATA MOVIMENTAÇÃO</th>
                                                    <th scope="col">VALOR BRUTO</th>
                                                    <th scope="col">VALOR DESCONTO</th>
                                                    <th scope="col">VALOR FRETE</th>
                                                    <th scope="col">VALOR LÍQUIDO</th>
                                                    <th scope="col">IMPRIMIR</th>
                                                    <th scope="col">IMPRIMIR</th>
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

    $objeto_codigo_barras = new EAN13();
    $transacao = (string) substr($objeto_codigo_barras->getFullCode(''), 0, 12);
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
                    let fornecedores = retorno.dados;

                    sistema.each(fornecedores, function (fornecedor) {
                        select_fornecedores.appendChild(sistema.gerar_option(fornecedor._id.$oid, fornecedor.nome_usuario));
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

                            let estoque = produto.estoque;
                            let saldo_atual = estoque?.[0]?.saldo ?? 0;

                            linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], produto.nome_produto));
                            linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], saldo_atual));
                            linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.number_format(produto.valor_custo)));
                            linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.gerar_botao('botao_adicionar_item_pedido_' + produto._id.$oid, 'ADICIONAR', ['btn', 'btn-success'], function () {
                                colocar_dados_produto_pedido(produto._id.$oid, produto.nome_produto, produto.valor_custo);
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

                $('#modal_pedido_item').modal('hide');
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
                let transacao = document.querySelector('#transacao').value;
                let observacao = document.querySelector('#observacao').value;

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
                        'rota': 'salvar_pedidos',
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
                        'transacao': transacao,
                        'observacao': observacao,
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
                                        <select class="form-control select2" id="fornecedor">
                                            <option value="">Selecione um fornecedor</option>
                                        </select>
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="text">Status</label>
                                        <select class="form-control select2" id="status_pedido">
                                            <option value="PEDIDO">APENAS PEDIDO</option>
                                            <option value="PEDIDO_ESTOQUE">PEDIDO + ESTOQUE</option>
                                            <option value="PEDIDO_CONTA">PEDIDO + CONTA</option>
                                            <option value="PEDIDO_COMPLETO">PEDIDO COMPLETO</option>
                                        </select>
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="text">Tipo Pedido</label>
                                        <select class="form-control select2" id="tipo_pedido">
                                            <option value="true">PEDIDO ENTRADA</option>
                                        </select>
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-3 text-center">
                                        <label class="text">Data Cadastro</label>
                                        <input type="date" class="form-control" id="data_cadastro"
                                            value="<?php echo $hoje; ?>">
                                    </div>
                                    <div class="col-3 text-center">
                                        <label class="text">Data Movimentação</label>
                                        <input type="date" class="form-control" id="data_movimentacao"
                                            value="<?php echo $hoje; ?>">
                                    </div>
                                    <div class="col-3 text-center">
                                        <label class="text">Transação</label>
                                        <input type="text" class="form-control" id="transacao"
                                            value="<?php echo $transacao; ?>">
                                    </div>
                                </div>
                                <br />
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <label class="text">Observação</label>
                                        <textarea class="form-control" id="observacao"></textarea>
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
                                            <input type="hidden" id="fornecedor_tabela">
                                            <div class="col-6 text-center">
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
                                                    <option value="1">PRODUTO</option>
                                                    <option value="0">SERVIÇO</option>
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
            <script>
                const EMPRESA = "<?php echo $codigo_empresa; ?>";

                /** 
                 * Função para pesquisar os clientes disponíveis. Ela faz uma requisição POST para a rota 'pesquisar_clientes' no arquivo 'clientes.php', passando o código da empresa e o tipo de usuário como 'CLIENTE'. Ao receber a resposta, a função itera sobre a lista de clientes retornada e adiciona cada um como uma opção em um elemento select com o id 'cliente'. Essa função é chamada quando a página é carregada para garantir que a lista de clientes esteja atualizada e disponível para seleção no formulário de cadastro de produtos.
                 */
                function pesquisar_clientes() {
                    sistema.request.post('/clientes.php', {
                        'rota': 'pesquisar_clientes',
                        'empresa': EMPRESA,
                        'tipo_usuario': 'CLIENTE'
                    }, function (retorno) {
                        let select_clientes = document.querySelector('#cliente');
                        let clientes = retorno.dados;

                        sistema.each(clientes, function (cliente) {
                            select_clientes.appendChild(sistema.gerar_option(cliente._id.$oid, cliente.nome_usuario));
                        });

                    });
                }

                /** 
                 * Função para pesquisar os pedidos de saida com base nos filtros selecionados. Ela coleta os valores dos filtros de cliente, tipo, data de cadastro, data de movimentação e status do pedido, e faz uma requisição POST para a rota 'pesquisar_pedidos' no arquivo 'pedidos.php', passando esses valores como parâmetros. Ao receber a resposta, a função itera sobre a lista de pedidos retornada e constrói as linhas da tabela de pedidos, preenchendo as informações correspondentes em cada coluna. Essa função é chamada quando o usuário clica no botão "Pesquisar" para atualizar a tabela de pedidos com os resultados filtrados.
                 */
                function pesquisar_pedidos() {
                    let cliente = document.querySelector('#cliente').value;
                    let status_pedido = document.querySelector('#status_pedido').value;
                    let data_cadastro = document.querySelector('#data_cadastro').value;
                    let data_movimentacao = document.querySelector('#data_movimentacao').value;
                    let transacao = document.querySelector('#transacao').value;
                    let tipo = false;

                    let dados = {
                        'rota': 'pesquisar_pedidos',
                        'cliente': cliente,
                        'status_pedido': status_pedido,
                        'data_cadastro': data_cadastro,
                        'data_movimentacao': data_movimentacao,
                        'tipo_pedido': tipo,
                        'empresa': EMPRESA,
                        'transacao': transacao
                    };

                    barra_progresso('Carregando pedidos de saída...');

                    sistema.request.post('/pedidos.php', dados, function (retorno) {
                        let pedidos = retorno.dados;
                        let tamanho_retorno = pedidos.length;
                        let tabela = document.querySelector('#tabela_pedidos tbody');
                        let index = 0;

                        tabela = sistema.remover_linha_tabela(tabela);

                        if (tamanho_retorno == 0) {
                            let linha = document.createElement('tr');
                            linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], 'UTILIZE OS FILTROS PARA FACILITAR SUA PESQUISA!', 'inner', true, 10));
                            tabela.appendChild(linha);

                            Swal.fire({ icon: 'warning', title: 'Nenhum pedido encontrado!' });
                            return;
                        }

                        function processar_item() {

                            if (index >= tamanho_retorno) {
                                Swal.close();
                                return;
                            }

                            let pedido = pedidos[index];

                            let linha = document.createElement('tr');

                            linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], pedido.fornecedor_dados.nome, 'inner'));

                            if (pedido.status == 'PEDIDO') {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_' + pedido._id.$oid, 'PEDIDO', ['btn', 'btn-outline-primary'], function alterar_status_pedido_botao() { alterar_status_pedido(pedido._id.$oid, pedido.status); }), 'append'));
                            } else if (pedido.status == 'PEDIDO_ESTOQUE') {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_' + pedido._id.$oid, 'PEDIDO ESTOQUE', ['btn', 'btn-outline-secondary'], function alterar_status_pedido_botao() { alterar_status_pedido(pedido._id.$oid, pedido.status); }), 'append'));
                            } else if (pedido.status == 'PEDIDO_CONTA') {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_' + pedido._id.$oid, 'PEDIDO CONTA', ['btn', 'btn-outline-info'], function alterar_status_pedido_botao() { alterar_status_pedido(pedido._id.$oid, pedido.status); }), 'append'));
                            } else if (pedido.status == 'PEDIDO_COMPLETO') {
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_' + pedido._id.$oid, 'PEDIDO COMPLETO', ['btn', 'btn-outline-success'], function alterar_status_pedido_botao() { alterar_status_pedido(pedido._id.$oid, pedido.status); }), 'append'));
                            }else if(pedido.status == 'CANCELADO'){
                                linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_tipo_' + pedido._id.$oid, 'CANCELADO', ['btn', 'btn-outline-danger'], function alterar_status_pedido_botao() {}), 'append'));
                            }

                            linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.gerar_botao('botao_cancelar_pedido_' + pedido._id.$oid, pedido.transacao, ['btn', 'btn-light'], function botao_cancelar_venda_botao() { cancelar_venda(pedido._id.$oid, pedido.transacao); }), 'append'));


                            linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.retornar_data(pedido.data_cadastro, '', true), 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.retornar_data(pedido.data_movimentacao, '', true), 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-end', 'fw-bold'], sistema.number_format(pedido.valor_bruto, 2, ',', '.'), 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-end', 'fw-bold', 'text-danger'], sistema.number_format(pedido.valor_desconto, 2, ',', '.'), 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-end', 'fw-bold', 'text-warning'], sistema.number_format(pedido.valor_frete, 2, ',', '.'), 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-end', 'fw-bold', 'text-success'], sistema.number_format(pedido.valor_liquido, 2, ',', '.'), 'inner'));
                            linha.appendChild(sistema.gerar_td(['text-center',], sistema.gerar_botao('botao_impressao_' + pedido._id.$oid, 'IMPRIMIR SIMPLES', ['btn', 'btn-blue'], function modal_impressao() { abrir_modal_impressao_pedido(pedido._id.$oid, 'SIMPLE'); }), 'append'));
                            linha.appendChild(sistema.gerar_td(['text-center',], sistema.gerar_botao('botao_impressao_' + pedido._id.$oid, 'IMPRIMIR COMPLETO', ['btn', 'btn-blue'], function modal_impressao() { abrir_modal_impressao_pedido(pedido._id.$oid, 'COMPLETO'); }), 'append'));

                            tabela.appendChild(linha);

                            atualizar_barra_progresso(index, tamanho_retorno, document.querySelector('#barra_progresso'), document.querySelector('#texto_progresso'));

                            index++;
                            setTimeout(processar_item(), 1);
                        }
                        processar_item();
                    });
                }

                function cancelar_venda(codigo_pedido, transacao) {
                    Swal.fire({
                        title: "Tem certeza?",
                        text: "Tem certeza que deseja cancelar este pedido?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Sim, Cancelar!",
                        cancelButtonText:'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            sistema.request.post('/pedidos.php', {'rota':'cancelar_pedido', 'codigo_pedido':codigo_pedido, 'transacao':transacao}, function(retorno){
                                if(retorno.status == true){
                                    Swal.fire({
                                        title: "Cancelado!",
                                        text: "Pedido cancelado com sucesso!",
                                        icon: "success"
                                    });
                                }else{
                                    Swal.fire({
                                        title: "Erro!",
                                        text: "Erro durante o processo de cancelar o pedido!",
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                }

                /**
                 * Função responsável por realizar a troca do status do pedido
                 * @param {string} codigo_pedido 
                 * @param {string} status
                 * */
                async function alterar_status_pedido(codigo_pedido, status) {
                    let dados = {
                        PEDIDO: "PEDIDO",
                        PEDIDO_ESTOQUE: "PEDIDO + ESTOQUE",
                        PEDIDO_CONTA: "PEDIDO + CONTA",
                        PEDIDO_COMPLETO: "PEDIDO COMPLETO (CONTA + ESTOQUE)",
                    };

                    const { value: fruit } = await Swal.fire({
                        title: "Selecione um status do pedido",
                        input: "select",
                        inputOptions: dados,
                        inputPlaceholder: "Selecione um status",
                        showCancelButton: true,
                        cancelButtonText: 'Cancelar',
                        inputValidator: (value) => {
                            return new Promise((resolve) => {
                                if (value != "") resolve();
                                else resolve("Selecione uma status");
                            });
                        }
                    });
                    if (fruit) {
                        sistema.request.post('/pedidos.php', { 'rota': 'editar_tipo_pedido_saida', 'codigo_pedido': codigo_pedido, 'status_pedido': fruit }, function (retorno) {
                            validar_retorno(retorno, '/pedidos.php');
                        });
                    }
                }

                /**
                 * Função para redirecionar o usuário para a página de cadastro de pedidos de saida. Ela recebe um parâmetro 'codigo_pedido', que é o código do pedido a ser editado. Se o código do pedido for vazio, a função redireciona para a página de cadastro de um novo pedido. Caso contrário, ela redireciona para a página de edição do pedido existente, passando o código do pedido como parâmetro na URL. Essa função é chamada quando o usuário clica no botão "Cadastrar Pedidos" ou em um pedido específico na tabela de pedidos.
                 * @param {string} codigo_pedido - O código do pedido a ser editado ou vazio para cadastrar um novo pedido.
                 */
                function cadastro_pedidos_saida(codigo_pedido) {
                    window.location.href = sistema.url('/pedidos.php', {
                        'rota': 'cadastro_pedidos_saida',
                        'codigo_pedido': codigo_pedido
                    });
                }

                /**
                 * Função responsável por abrir o modal de impressão do pedido
                 * @param {*} codigo_pedido 
                 * @param {*} tipo_pedido
                 * */
                function abrir_modal_impressao_pedido(codigo_pedido, tipo_pedido) {
                    let largura = 1200;
                    let altura = 500;
                    let left = (screen.width - largura) / 2;
                    let top = (screen.height - altura) / 2;
                    let url = sistema.url('/pedidos.php', {
                        'rota': 'imprimir_pedido_saida',
                        'codigo_pedido': codigo_pedido,
                        'tipo_pedido': tipo_pedido
                    });

                    let nome = 'Impressão de Pedidos';
                    let janela = window.open(url, nome, `width=${largura}, height=${altura}, left=${left}, top=${top}`);

                    if (window.focus) {
                        janela.focus();
                    }
                }
            </script>
            <div class="page-wrapper">
                <div class="content">
                    <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                        <div>
                            <h6>Pedidos de Saida</h6>
                        </div>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                            <div class="dropdown">
                                <button class="btn btn-primary d-flex align-items-center justify-content-center"
                                    onclick="cadastro_pedidos_saida('');">Cadastrar Pedidos</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header justify-content-between">
                                    <div class="card-title">Lista de Pedidos de Saida</div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-3 text-center">
                                            <label class="text">Cliente</label>
                                            <select class="form-control select2" id="cliente">
                                                <option value="">Selecione um cliente</option>
                                            </select>
                                        </div>
                                        <div class="col-2 text-center">
                                            <label class="text">TIPO</label>
                                            <select class="form-control select2" id="status_pedido">
                                                <option value="">TODOS</option>
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
                                        <div class="col-3 text-center">
                                            <label class="text">Transação</label>
                                            <input type="text" class="form-control" id="transacao">
                                        </div>
                                    </div>
                                    <br />
                                    <div class="row">
                                        <div class="col-3 push-9">
                                            <label class="text"></label>
                                            <button class="btn btn-secondary w-100"
                                                onclick="pesquisar_pedidos();">Pesquisar</button>
                                        </div>
                                    </div>
                                    <br />
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="table-responsive">
                                                <table class="table table-nowrap text-nowrap table-hover"
                                                    id="tabela_pedidos">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th scope="col">CLIENTE</th>
                                                            <th scope="col">TIPO</th>
                                                            <th scope="col">TRANSAÇÃO</th>
                                                            <th scope="col">DATA CADASTRO</th>
                                                            <th scope="col">DATA MOVIMENTAÇÃO</th>
                                                            <th scope="col">VALOR BRUTO</th>
                                                            <th scope="col">VALOR DESCONTO</th>
                                                            <th scope="col">VALOR FRETE</th>
                                                            <th scope="col">VALOR LÍQUIDO</th>
                                                            <th scope="col">IMPRIMIR</th>
                                                            <th scope="col">IMPRIMIR</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="10" class="text-center"
                                                                onclick="pesquisar_produtos();">
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
                        pesquisar_clientes();
                        pesquisar_pedidos();
                    }
                </script>
                <?php
                include_once 'includes/footer.php';
});

router_add('cadastro_pedidos_saida', function () {
    include_once 'includes/head.php';
    $hoje = $data->format('Y-m-d');

    $objeto_codigo_barras = new EAN13();
    $transacao = (string) substr($objeto_codigo_barras->getFullCode(''), 0, 12);
    ?>
                <script>
                    const EMPRESA = "<?php echo $codigo_empresa; ?>";
                    let QUANTIDADE_ITEM_PEDIDO_SAIDA = 0;
                    let ATUALIZAR_VALORES_PEDIDO_SAIDA = false;
                    let VALOR_BRUTO_PEDIDO_SAIDA = 0;
                    let VALOR_FRETE_PEDIDO_SAIDA = 0;
                    let VALOR_DESCONTO_PEDIDO_SAIDA = 0;
                    let VALOR_LIQUIDO_PEDIDO_SAIDA = 0;
                    let QUANTIDADE_PRODUTOS_PEDIDOS_SAIDA = 0;
                    let VALOR_UNITARIO_PEDIDO_SAIDA = 0;

                    /** 
                     * Função para pesquisar os clientes disponíveis. Ela faz uma requisição POST para a rota 'pesquisar_clientes' no arquivo 'clientes.php', passando o código da empresa e o tipo de usuário como 'CLIENTE'. Ao receber a resposta, a função itera sobre a lista de clientes retornada e adiciona cada um como uma opção em um elemento select com o id 'cliente'. Essa função é chamada quando a página é carregada para garantir que a lista de clientes esteja atualizada e disponível para seleção no formulário de cadastro de produtos.
                     */
                    function pesquisar_clientes() {
                        sistema.request.post('/clientes.php', {
                            'rota': 'pesquisar_clientes',
                            'empresa': EMPRESA,
                            'tipo_usuario': 'CLIENTE'
                        }, function (retorno) {
                            let select_clientes = document.querySelector('#cliente');
                            let clientes = retorno.dados;

                            sistema.each(clientes, function (cliente) {
                                select_clientes.appendChild(sistema.gerar_option(cliente._id.$oid, cliente.nome_usuario));
                            });
                        });
                    }
                    /** 
                     * Função responsável por pesquisar os produtos cadastrados no sistema
                     */
                    function pesquisar_produtos() {
                        let cliente = document.querySelector('#cliente_tabela').value;
                        let nome_produto = document.querySelector('#nome_produto').value;
                        let codigo_barras = document.querySelector('#codigo_barras').value;
                        let tipo_produto = document.querySelector('#tipo_produto').value;
                        let status_produto = true;

                        let dados = {
                            'rota': 'pesquisar_produtos',
                            'empresa': EMPRESA,
                            'cliente': cliente,
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

                                    let estoque = produto.estoque;
                                    let saldo_atual = estoque?.[0]?.saldo ?? 0;

                                    linha.appendChild(sistema.gerar_td(['text-start', 'fw-bold'], produto.nome_produto));

                                    if (saldo_atual <= 0) {
                                        linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold', 'text-danger'], saldo_atual));
                                    } else if (saldo_atual > 0 && saldo_atual <= produto.quantidade_alerta) {
                                        linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold', 'text-warning'], saldo_atual));
                                    } else {
                                        linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold', 'text-success'], saldo_atual));
                                    }

                                    linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.number_format(produto.valor_venda)));

                                    if (saldo_atual <= 0) {
                                        linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.gerar_botao('botao_adicionar_item_pedido_' + produto._id.$oid, 'ADICIONAR', ['btn', 'btn-outline-success'], function () {

                                        }), 'append'));
                                    } else {
                                        linha.appendChild(sistema.gerar_td(['text-center', 'fw-bold'], sistema.gerar_botao('botao_adicionar_item_pedido_' + produto._id.$oid, 'ADICIONAR', ['btn', 'btn-success'], function () {
                                            colocar_dados_produto_pedido(produto._id.$oid, produto.nome_produto, produto.valor_venda);
                                        }), 'append'));
                                    }

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
                        QUANTIDADE_ITEM_PEDIDO_SAIDA++;

                        let tabela = document.querySelector('#tabela_itens_pedido tbody');
                        let linha = document.createElement('tr');
                        let id_linha = 'linha_' + QUANTIDADE_ITEM_PEDIDO_SAIDA;

                        linha.id = id_linha;

                        linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('id_produto_item_' + QUANTIDADE_ITEM_PEDIDO_SAIDA, QUANTIDADE_ITEM_PEDIDO_SAIDA, ['form-control', 'text-center'], 'text', '', false), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('codigo_produto_item_' + QUANTIDADE_ITEM_PEDIDO_SAIDA, codigo_produto, ['form-control', 'text-center'], 'text', '', false), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('nome_produto_item_' + QUANTIDADE_ITEM_PEDIDO_SAIDA, nome_produto, ['form-control', 'text-center'], 'text', '', false), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('quantidade_produto_item_' + QUANTIDADE_ITEM_PEDIDO_SAIDA, '1', ['form-control', 'text-center'], 'number', '', true), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('valor_unitario_produto_item_' + QUANTIDADE_ITEM_PEDIDO_SAIDA, sistema.number_format(valor_venda), ['form-control', 'text-center'], 'text', '', true), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('valor_bruto_produto_item_' + QUANTIDADE_ITEM_PEDIDO_SAIDA, sistema.number_format(valor_venda), ['form-control', 'text-center'], 'text', '', false), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('valor_desconto_produto_item_' + QUANTIDADE_ITEM_PEDIDO_SAIDA, sistema.number_format(0), ['form-control', 'text-center'], 'text', '', true), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_input('valor_frete_produto_item_' + QUANTIDADE_ITEM_PEDIDO_SAIDA, '0', ['form-control', 'text-center'], 'text', '', true), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center', 'disabled'], sistema.gerar_input('valor_total_produto_item_' + QUANTIDADE_ITEM_PEDIDO_SAIDA, sistema.number_format(valor_venda), ['form-control', 'text-center'], 'text', '', false), 'append'));
                        linha.appendChild(sistema.gerar_td(['text-center'], sistema.gerar_botao('botao_excluir_item_pedido_' + QUANTIDADE_ITEM_PEDIDO_SAIDA, 'EXCLUIR', ['btn', 'btn-danger'], function () {
                            document.querySelector('#' + id_linha).remove();
                        }), 'append'));

                        tabela.appendChild(linha);
                        $('#modal_pedido_item').modal('hide');
                    }

                    /** 
                     * Função responsável por atualizar os valores do pedido com base nos itens adicionados. Ela percorre as linhas da tabela de itens do pedido, coletando as informações de quantidade, valor unitário, valor de frete e valor de desconto para cada item. A função realiza os cálculos necessários para atualizar o valor bruto, valor de frete, valor de desconto e valor líquido do pedido, bem como a quantidade total de produtos e o valor unitário total. Os valores atualizados são exibidos nos campos correspondentes na interface do usuário. Essa função é chamada quando o usuário clica no botão "Atualizar Valores" para garantir que os valores do pedido estejam corretos antes de salvar os dados.
                     */
                    function atualizar_valores_pedido() {
                        let linhas = document.querySelectorAll('#tabela_itens_pedido tr');
                        let executando = true;

                        VALOR_BRUTO_PEDIDO_SAIDA = 0;
                        VALOR_FRETE_PEDIDO_SAIDA = 0;
                        VALOR_DESCONTO_PEDIDO_SAIDA = 0;
                        VALOR_LIQUIDO_PEDIDO_SAIDA = 0;
                        QUANTIDADE_PRODUTOS_PEDIDOS_SAIDA = 0;
                        VALOR_UNITARIO_PEDIDO_SAIDA = 0;

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

                                    QUANTIDADE_PRODUTOS_PEDIDOS_SAIDA = sistema.arredondar(QUANTIDADE_PRODUTOS_PEDIDOS_SAIDA, '+', quantidade);

                                    let valor_bruto = sistema.arredondar(quantidade, '*', valor_unitario);

                                    VALOR_UNITARIO_PEDIDO_SAIDA = sistema.arredondar(VALOR_UNITARIO_PEDIDO_SAIDA, '+', valor_unitario);
                                    VALOR_BRUTO_PEDIDO_SAIDA = sistema.arredondar(VALOR_BRUTO_PEDIDO_SAIDA, '+', valor_bruto);

                                    document.querySelector('#valor_bruto_produto_item_' + i).value = sistema.number_format(valor_bruto);

                                    if (valor_desconto > 0) {
                                        valor_bruto = sistema.arredondar(valor_bruto, '-', valor_desconto);
                                        VALOR_DESCONTO_PEDIDO_SAIDA = sistema.arredondar(VALOR_DESCONTO_PEDIDO_SAIDA, '+', valor_desconto);
                                    }

                                    if (valor_frete > 0) {
                                        valor_bruto = sistema.arredondar(valor_bruto, '+', valor_frete);
                                        VALOR_FRETE_PEDIDO_SAIDA = sistema.arredondar(VALOR_FRETE_PEDIDO_SAIDA, '+', valor_frete);
                                    }

                                    document.querySelector('#valor_total_produto_item_' + i).value = sistema.number_format(valor_bruto);

                                    VALOR_LIQUIDO_PEDIDO_SAIDA = sistema.arredondar(VALOR_LIQUIDO_PEDIDO_SAIDA, '+', valor_bruto);

                                    document.querySelector('#quantidade_total_itens').value = QUANTIDADE_PRODUTOS_PEDIDOS_SAIDA;
                                    document.querySelector('#valor_unitario').value = sistema.number_format(VALOR_UNITARIO_PEDIDO_SAIDA);
                                    document.querySelector('#valor_bruto').value = sistema.number_format(VALOR_BRUTO_PEDIDO_SAIDA);
                                    document.querySelector('#valor_frete').value = sistema.number_format(VALOR_FRETE_PEDIDO_SAIDA);
                                    document.querySelector('#valor_desconto').value = sistema.number_format(VALOR_DESCONTO_PEDIDO_SAIDA);
                                    document.querySelector('#valor_liquido').value = sistema.number_format(VALOR_LIQUIDO_PEDIDO_SAIDA);

                                    if (i == QUANTIDADE_ITEM_PEDIDO_SAIDA) {
                                        executando = false;
                                        document.querySelector('#btn_salvar_dados').disabled = false;
                                    }
                                }
                            }
                        });
                    }

                    function salvar_dados() {
                        let cliente = document.querySelector('#cliente').value;
                        let status_pedido = document.querySelector('#status_pedido').value;
                        let tipo_pedido = false;
                        let data_cadastro = document.querySelector('#data_cadastro').value;
                        let data_movimentacao = document.querySelector('#data_movimentacao').value;
                        let quantidade_total_itens = document.querySelector('#quantidade_total_itens').value;
                        let valor_unitario = document.querySelector('#valor_unitario').value;
                        let valor_bruto = document.querySelector('#valor_bruto').value;
                        let valor_desconto = document.querySelector('#valor_desconto').value;
                        let valor_frete = document.querySelector('#valor_frete').value;
                        let valor_liquido = document.querySelector('#valor_liquido').value;
                        let transacao = document.querySelector('#transacao').value;
                        let observacao = document.querySelector('#observacao').value;

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

                                    if (i == QUANTIDADE_ITEM_PEDIDO_SAIDA) {
                                        executando = false;
                                    }
                                }
                            }
                        });

                        let json = JSON.stringify(itens_pedido);

                        let dados = {
                            'rota': 'salvar_pedidos',
                            'empresa': EMPRESA,
                            'fornecedor': cliente,
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
                            'transacao': transacao,
                            'observacao': observacao,
                            'objeto_itens': json
                        };

                        console.log(dados);

                        if (cliente != '') {
                            sistema.request.post('/pedidos.php', dados, function (retorno) {
                                validar_retorno(retorno, '/pedidos.php', 0, 'saida');
                            });
                        } else {
                            alerta_campo_vazio('CLIENTE');
                        }
                    }

                    function limpar_dados() {
                        document.querySelector('#cliente').value = '';
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

                        QUANTIDADE_ITEM_PEDIDO_SAIDA = 0;
                    }

                    function voltar() {
                        window.location.href = sistema.url('/pedidos.php', {
                            'rota': 'saida'
                        });
                    }
                </script>
                <div class="page-wrapper">
                    <div class="content">
                        <div class="d-flex d-block align-items-center justify-content-between flex-warp gap-3 mb-3">
                            <div>
                                <h6>Cadastro de Pedidos de Saída</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-header justify-content-between">
                                        <div class="card-title">Cadastro de Pedidos de Saída</div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-4 text-center">
                                                <label class="text">Cliente</label>
                                                <select class="form-control select2" id="cliente">
                                                    <option value="">Selecione um cliente</option>
                                                </select>
                                            </div>
                                            <div class="col-4 text-center">
                                                <label class="text">Status</label>
                                                <select class="form-control select2" id="status_pedido">
                                                    <option value="PEDIDO">APENAS PEDIDO</option>
                                                    <option value="PEDIDO_ESTOQUE">PEDIDO + ESTOQUE</option>
                                                    <option value="PEDIDO_CONTA">PEDIDO + CONTA</option>
                                                    <option value="PEDIDO_COMPLETO">PEDIDO COMPLETO</option>
                                                </select>
                                            </div>
                                            <div class="col-4 text-center">
                                                <label class="text">Tipo Pedido</label>
                                                <select class="form-control select2" id="tipo_pedido">
                                                    <option value="false">PEDIDO SAÍDA</option>
                                                </select>
                                            </div>
                                        </div>
                                        <br />
                                        <div class="row">
                                            <div class="col-3 text-center">
                                                <label class="text">Data Cadastro</label>
                                                <input type="date" class="form-control" id="data_cadastro"
                                                    value="<?php echo $hoje; ?>">
                                            </div>
                                            <div class="col-3 text-center">
                                                <label class="text">Data Movimentação</label>
                                                <input type="date" class="form-control" id="data_movimentacao"
                                                    value="<?php echo $hoje; ?>">
                                            </div>
                                            <div class="col-3 text-center">
                                                <label class="text">Transação</label>
                                                <input type="text" class="form-control" id="transacao"
                                                    value="<?php echo $transacao; ?>">
                                            </div>
                                        </div>
                                        <br />
                                        <div class="row">
                                            <div class="col-12 text-center">
                                                <label class="text">Observação</label>
                                                <textarea class="form-control" id="observacao"></textarea>
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
                                                                <th scope="col">VALOR LÍQUIDO</th>
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
                                                <input type="text" class="form-control text-center"
                                                    id="quantidade_total_itens" sistema-mask="codigo" disabled value="0">
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
                                                    <input type="hidden" id="cliente_tabela">
                                                    <div class="col-6 text-center">
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
                                                            <option value="1">PRODUTO</option>
                                                            <option value="0">SERVIÇO</option>
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
                            pesquisar_clientes();
                            document.querySelector('#btn_salvar_dados').disabled = true;
                        }
                    </script>
                    <?php
                    include_once 'includes/footer.php';
});
?>