<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/ContaPagarReceberHistorico.php';

/**
 !* Rota responsável por pesquisar as informações do histórico.
 */
router_add('pesquisar_historico_contas', function(){
    $codigo_conta = (int) ( isset($_REQUEST['codigo_conta']) ? intval($_REQUEST['codigo_conta'], 10):0);
    $retorno = (array) ['status' => (bool) false, 'dados' => (array) []];

    if($codigo_conta != 0){
        $objeto_conta_pagar_receber_historico = new ContaPagarReceberHistorico();

        $retorno['dados'] =  (array) $objeto_conta_pagar_receber_historico->pesquisar_historico($codigo_conta);
        $retorno['status'] = (bool) true;
    }

    echo json_encode($retorno, JSON_UNESCAPED_UNICODE);
    exit;
});
?>