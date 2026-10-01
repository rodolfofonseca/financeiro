<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/ModalAlerta.php';

/**
 * Função responsável por fechar o modal de alerta que está aparecendo para o usuário
 */
router_add('fechar_alerta', function(){
    $codigo_alerta = (int) (isset($_REQUEST['codigo_alerta']) ? (int) intval($_REQUEST['codigo_alerta'], 10):0);
    
    $objeto_modal_alerta = new ModalAlerta();

    echo json_encode((array) ['status' => (bool) $objeto_modal_alerta->excluir_alerta($codigo_alerta)], JSON_UNESCAPED_UNICODE);
    exit;
});
?>