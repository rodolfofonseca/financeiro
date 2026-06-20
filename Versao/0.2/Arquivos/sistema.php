<?php
require_once 'classes/bancoDeDados.php';
require_once 'modelos/Sistema.php';

router_add('pesquisar_sistema', function () {
    $objeto_sistema = new Sistema();

    $empresa = (string) (isset($_REQUEST['empresa']) ? (string) $_REQUEST['empresa'] : '');
    $retorno = (array) [];

    if ($empresa != '') {
        $retorno = (array) $objeto_sistema->pesquisar((array) ['filtro' => (array) ['empresa', '===', model_id($empresa)]]);
    }

    echo json_encode((array) ['dados' => (array) $retorno], JSON_UNESCAPED_UNICODE);
});

router_add('salvar_dados', function () {
    $objeto_sistema = new Sistema();

    echo json_encode((array) ['status' => (bool) $objeto_sistema->salvar_dados($_REQUEST)], JSON_UNESCAPED_UNICODE);
    exit;
});

router_add('index', function () {
    require_once 'includes/head.php';
    ?>
    <script>
        let EMPRESA = "<?php echo $codigo_empresa; ?>";
        let SISTEMA = '';

        function pesquisar_sistema() {
            sistema.request.post('/sistema.php', { 'rota': 'pesquisar_sistema', 'empresa': EMPRESA }, function (retorno) {
                let sistema = retorno.dados;

                document.querySelector('#versao_sistema').value = sistema.versao_sistema;
                document.querySelector('#anexar_documentos').value = sistema.anexa_documentos;
                SISTEMA = sistema._id;
            });
        }

        function salvar_dados() {
            let versao_sistema = document.querySelector('#versao_sistema').value;
            let anexar_documentos = document.querySelector('#anexar_documentos').value;

            sistema.request.post('/sistema.php', { 'rota': 'salvar_dados', 'empresa': EMPRESA, 'versao_sistema': versao_sistema, 'anexa_documentos': anexar_documentos, 'codigo_sistema': SISTEMA }, function (retorno) {
                validar_retorno(retorno, '/sistema.php');
            });
        }
    </script>
    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Configurações do sistema
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-2 text-center">
                                    <label class="text">Versão do sistema</label>
                                </div>
                                <div class="col-2 text-center">
                                    <input type="text" class="form-control text-center" id="versao_sistema" disabled="true">
                                </div>
                                <div class="col-2 text-center">
                                    <label class="text">Anexar Comprovantes</label>
                                </div>
                                <div class="col-2 text-center">
                                    <select class="form-control" id="anexar_documentos">
                                        <option value="NAO">NÃO</option>
                                        <option value="SIM">SIM</option>
                                    </select>
                                </div>
                                <div class="col-4 text-center">
                                    <button class="btn btn-success w-100 btn-lg" id="btn_salvar_sados"
                                        onclick="salvar_dados();">SALVAR DADOS</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = () => {
                pesquisar_sistema();
            }
        </script>
        <?php
        require_once 'includes/footer.php';
        exit;
});
?>