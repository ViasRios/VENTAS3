<?php
    require_once "../../config/app.php";
    require_once "../views/inc/session_start.php";
    require_once "../../autoload.php";
    use app\controllers\movimientoController;
    if(isset($_POST['modulo_movimiento'])){
        $insMovimiento = new movimientoController();
        if($_POST['modulo_movimiento']=="registrar"){
            echo $insMovimiento->registrarMovimientoControlador();
        }
    }else{
        session_destroy();
        header("Location: ".APP_URL."login/");
    }