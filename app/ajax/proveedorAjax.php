<?php

require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\proveedorController;
use app\models\mainModel;
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

if(isset($_POST['modulo_proveedor'])){
	$insProveedor = new proveedorController();
	if($_POST['modulo_proveedor']=="listar"){
		echo $insProveedor->listarProveedorControlador($pagina, $registros, $url, $busqueda);
	}
	
	if($_POST['modulo_proveedor']=="registrar"){
		$db = mainModel::conectar();

		$proveedor = trim($_POST['proveedor'] ?? '');
		$telefono  = trim($_POST['telefono'] ?? '');
		$email     = trim($_POST['email'] ?? '');
		$direccion = trim($_POST['direccion'] ?? '');
		$web       = trim($_POST['web'] ?? '');

		if ($proveedor == "") {
			echo json_encode([
				"Alerta" => "simple",
				"Titulo" => "Error",
				"Texto"  => "El campo Nombre proveedor es obligatorio",
				"Tipo"   => "error"
			]);
			exit;
		}

		$sql = "INSERT INTO proveedor (proveedor, telefono, email, direccion, web)
				VALUES (:proveedor, :telefono, :email, :direccion, :web)";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':proveedor', $proveedor);
		$stmt->bindParam(':telefono', $telefono);
		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':direccion', $direccion);
		$stmt->bindParam(':web', $web);

		if ($stmt->execute()) {
			echo json_encode([
				"Alerta" => "simple",
				"Titulo" => "Proveedor guardado",
				"Texto"  => "El proveedor se registró correctamente",
				"Tipo"   => "success"
			]);
		} else {
			echo json_encode([
				"Alerta" => "simple",
				"Titulo" => "Error",
				"Texto"  => "No se pudo guardar el proveedor",
				"Tipo"   => "error"
			]);
		}
	}

}else{
	session_destroy();
	header("Location: ".APP_URL."dashboard2/");
}
