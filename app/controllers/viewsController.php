<?php

	namespace app\controllers;
	use app\models\viewsModel;

	class viewsController extends viewsModel{

	/*---------- Controlador obtener vistas ----------*/
	public function obtenerVistasControlador($vista){
		if($vista!=""){
			// Caso especial para cashierNew
			if ($vista == "cashierNew") {
    			$cashier = new cashierController();
    			return $cashier->mostrarFormularioCajaControlador();
			}
			$respuesta = $this->obtenerVistasModelo($vista);
		}else{
			$respuesta = "login";
		}
		return $respuesta;
	}
}
