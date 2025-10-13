<?php
	header('Content-Type: application/json');
	require_once "../../config/app.php";
	require_once "../views/inc/session_start.php";
	require_once "../../autoload.php";
	
	use app\controllers\personalController;

	if(isset($_POST['modulo_personal'])){

		$insUsuario = new personalController();

		if($_POST['modulo_personal']=="registrar"){ 
			echo $insUsuario->registrarPersonalControlador();
		}

		if($_POST['modulo_personal']=="eliminar"){
			echo $insUsuario->eliminarPersonalControlador();
		}

		if($_POST['modulo_personal']=="actualizar"){
			echo $insUsuario->actualizarPersonalControlador();
		}

		if($_POST['modulo_personal']=="eliminarFoto"){
			echo $insUsuario->eliminarFotoPersonalControlador();
		}

		if($_POST['modulo_personal']=="actualizarFoto"){
			echo $insUsuario->actualizarFotoPersonalControlador();
		}
		
	}else{
		session_destroy();
		header("Location: ".APP_URL."login/");
	}