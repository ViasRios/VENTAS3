<?php
require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\odsController;

// Configuración para evitar errores visibles que rompan el JSON
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING); // Ocultar advertencias
ob_start(); // Capturar cualquier salida inesperada

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['modulo_ods'])) {
        throw new Exception("Petición inválida");
    }

    $mod = $_POST['modulo_ods'];
    $ctrl = new odsController();
    $resp = ['success'=>false, 'error'=>'Módulo desconocido'];

    switch ($mod) {
        case 'registrar':      $resp = $ctrl->registrarOdsControlador(); break;
        case 'eliminar':       $resp = $ctrl->eliminarOdsControlador(); break;
        case 'actualizar':     $resp = $ctrl->actualizarOdsControlador(); break;
        case 'cambiar_status': $resp = $ctrl->cambiarStatusOdsControlador(); break;
        case 'asignar_tecnico':$resp = $ctrl->actualizar_tecnico_controlador(); break;
    }

    // Si el controlador devolvió string JSON, lo decodificamos para re-codificarlo limpio
    if (is_string($resp)) {
        $decoded = json_decode($resp, true);
        $resp = $decoded ?? ['success'=>false, 'error'=>'Error JSON Backend'];
    }
    

} catch (Throwable $e) {
    $resp = ['success'=>false, 'error'=>'Excepción: '.$e->getMessage()];
}

// Limpiar cualquier HTML/Espacio previo y enviar SOLO el JSON
ob_clean();
echo json_encode($resp);
exit;