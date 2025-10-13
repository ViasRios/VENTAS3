<?php
require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\odsController;

// Siempre JSON, sin ruido
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ERROR | E_PARSE);
ob_start();

try {
    // Validar método y módulo
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['modulo_ods'])) {
        ob_clean();
        echo json_encode(['success'=>false,'error'=>'Petición inválida'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $mod = $_POST['modulo_ods'];
    $ctrl = new odsController();

    // Llama el método según el módulo
    switch ($mod) {
        case 'registrar':
            $resp = $ctrl->registrarOdsControlador();
            break;
        case 'eliminar':
            $resp = $ctrl->eliminarOdsControlador();
            break;
        case 'actualizar':
            $resp = $ctrl->actualizarOdsControlador();
            break;
        case 'cambiar_status':
            $resp = $ctrl->cambiarStatusOdsControlador();
            break;
        default:
            $resp = ['success'=>false,'error'=>'Módulo no soportado'];
    }

    // Normalizar: permitir que el controlador devuelva array o string JSON
    if (is_string($resp)) {
        $decoded = json_decode($resp, true);
        $resp = $decoded !== null ? $decoded : ['success'=>false,'error'=>'Respuesta no-JSON del controlador'];
    }

    ob_clean();
    echo json_encode($resp, JSON_UNESCAPED_UNICODE);
    exit;

    
} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['success'=>false,'error'=>'Excepción: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
