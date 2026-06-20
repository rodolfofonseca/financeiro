
<footer class="bg-white border-top py-3 px-4">
    <div class="d-flex justify-content-between">
        <?php
        $ano_atual = $data->format('Y');
        if($ano_atual == 2026){
            echo "<p class='text-dark mb-0'>&copy; 2026 <a href='javascript:void(0);' class='link-primary'>RAVF Corp</a>, Todos os direitos reservados</p>";
        }else{
            echo "<p class='text-dark mb-0'>&copy; 2026-".$ano_atual." <a href='javascript:void(0);' class='link-primary'>RAVF Corp</a>, Todos os direitos reservados</p>";
        }
        ?>
        
        <p class="text-dark">Versão : Alfa 0.0</p>
    </div>

</footer>
</div>
</div>
<script src="js/jquery-3.7.1.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
<script src="js/alerta.js?v=<?php echo fileatime('js/alerta.js'); ?>"></script>
<script src="js/simplebar.min.js"></script>
<script src="js/feather.min.js"></script>
<script src="js/select2.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/daterangepicker.js"></script>
<script src="js/chart.min.js"></script>
<script src="js/chart-data.js"></script>
<script src="js/apexcharts.min.js"></script>
<script src="js/chart-dataapex.js"></script>
<script src="js/bootstrap-tagsinput.js"></script>
<script src="js/c3.min.js"></script>
<script src="js/d3.v5.min.js"></script>
<script>
    document.querySelector("#loader").style.display = "none";
</script>

</body>

</html>