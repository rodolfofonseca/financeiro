<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/MovimentacaoCartao.php';

/**
 * Rota responsável por salvar a movimentação do cartão de crédito no banco de dados
 */
router_add('salvar_dados', function(){
    $movimentacao_cartao = new MovimentacaoCartao();

    echo json_encode((array) ['status' => (bool) $movimentacao_cartao->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
});

/**
 * Rota responsável por pesquisar as movimentações do cartão de crédito
 */
router_add('pesquisar_movimentacoes', function(){
    $codigo_cartao_credito = (int) (isset($_REQUEST['codigo_cartao_credito']) ? (int) intval($_REQUEST['codigo_cartao_credito'], 10) : 0);  

    $retorno = (array) [];
    $filtro = (array) ['filtro' => (array) [], 'ordenacao' => (array) [(array) ['data_movimentacao', 'DESC']], 'limite' => (int) 50];
    $filtro_montacao = (array) [];

    if($codigo_cartao_credito != 0){
        $movimentacao_cartao = new MovimentacaoCartao();
        array_push($filtro_montacao, (array) ['codigo_cartao_credito', '==', (int) $codigo_cartao_credito]);

        $filtro['filtro'] = (array) ['where' => (array) $filtro_montacao];

        $retorno = (array) $movimentacao_cartao->pesquisar_todos($filtro);
    }

    echo json_encode((array) ['dados' => (array) $retorno], JSON_UNESCAPED_UNICODE);
});
?>