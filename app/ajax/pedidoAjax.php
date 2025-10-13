<?php

require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\almacenController;

if(isset($_POST['modulo_pedido'])){

	$insAlmacen = new almacenController();

	if($_POST['modulo_pedido']=="registrar"){
		echo $insAlmacen->registrarPedidoControlador();
	}

}else{
	session_destroy();
	header("Location: ".APP_URL."login/");
}
