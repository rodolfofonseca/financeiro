<?php
require_once 'classes/bancoDeDados.php';

router_add('index', function () {

    require_once 'includes/head.php';
?>
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex d-block align-items-center justify-content-between flex-wrap gap-3 mb-3">
                <div>
                    <h6>Dashboard</h6>
                </div>
                <!-- <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-primary d-flex align-items-center justify-content-center">
                            Create New
                        </button>
                    </div>
                </div> -->
            </div>
        </div>
    <?php
    require_once 'includes/footer.php';
});
    ?>