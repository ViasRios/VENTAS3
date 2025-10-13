<?php
require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\almacenController;

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

// --- Helper uniforme de respuesta ---
function json_end($arr, int $code = 200) {
    http_response_code($code);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

// --- Solo POST: evita crear en GET / refresh ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_end(['ok'=>false,'error'=>'Método no permitido'], 405);
}

// --- Módulo requerido ---
if (!isset($_POST['modulo_refaccion'])) {
    json_end(['ok'=>false,'error'=>'Sin módulo (modulo_refaccion)'], 400);
}

  if (empty($_POST['form_token']) || !hash_equals($_SESSION['form_token'] ?? '', $_POST['form_token'])) {
    json_end(['ok'=>false,'error'=>'Token inválido'], 400);
 }
 unset($_SESSION['form_token']); // consumir token

$mod  = $_POST['modulo_refaccion'];
$ctrl = new almacenController();

// --- Validadores simples ---
function require_fields(array $fields) {
    foreach ($fields as $f) {
        if (!isset($_POST[$f]) || trim((string)$_POST[$f]) === '') {
            json_end(['ok'=>false,'error'=>"Falta o vacío: $f"], 422);
        }
    }
}
function require_idref() {
    if (!isset($_POST['IdRefaccion'])) {
        json_end(['ok'=>false,'error'=>'Falta IdRefaccion'], 422);
    }
    $id = (int) $_POST['IdRefaccion'];
    if ($id <= 0) {
        json_end(['ok'=>false,'error'=>'IdRefaccion inválido'], 422);
    }
    return $id;
}

/* =================== RUTAS =================== */

// CREAR (producto NO requerido)
if ($mod === 'registrar') {
    // Mínimos obligatorios: AJUSTADOS a tu decisión
    require_fields(['IdODS','IdAsesor','descripcion']);

    // Si quisieras exigir al menos uno (producto o IdProducto), descomenta:
/*
    if (
        (!isset($_POST['producto']) || trim($_POST['producto'])==='') &&
        (!isset($_POST['IdProducto']) || (int)$_POST['IdProducto']<=0)
    ) {
        json_end(['ok'=>false,'error'=>'Proporciona producto (texto) o IdProducto'], 422);
    }
*/

    try {
        $resp = $ctrl->registrarRefaccionControlador(); // debe devolver JSON (string) o array
        if (is_string($resp)) { echo $resp; exit; }
        json_end(['ok'=>true,'data'=>$resp]);
    } catch (Throwable $e) {
        json_end(['ok'=>false,'error'=>'Error al registrar: '.$e->getMessage()], 500);
    }
}

// ACTUALIZAR
elseif ($mod === 'actualizar') {
    require_idref();
    try {
        $resp = $ctrl->actualizarRefaccionControlador();
        if (is_string($resp)) { echo $resp; exit; }
        json_end(['ok'=>true,'data'=>$resp]);
    } catch (Throwable $e) {
        json_end(['ok'=>false,'error'=>'Error al actualizar: '.$e->getMessage()], 500);
    }
}

// ELIMINAR (lo usa tu botón .cancelar-btn)
elseif ($mod === 'eliminar') {
    $id = require_idref();
    try {
        // Implementa este método en tu controller si aún no existe
        $resp = $ctrl->eliminarRefaccionControlador($id);
        if (is_string($resp)) { echo $resp; exit; }
        json_end(['ok'=>true,'data'=>$resp]);
    } catch (Throwable $e) {
        json_end(['ok'=>false,'error'=>'Error al eliminar: '.$e->getMessage()], 500);
    }
}

// (Opcional) AUTORIZAR
elseif ($mod === 'autorizar') {
    $id = require_idref();
    try {
        $resp = $ctrl->autorizarRefaccionControlador($id);
        if (is_string($resp)) { echo $resp; exit; }
        json_end(['ok'=>true,'data'=>$resp]);
    } catch (Throwable $e) {
        json_end(['ok'=>false,'error'=>'Error al autorizar: '.$e->getMessage()], 500);
    }
}

// (Opcional) CANCELAR (cambiar estado a cancelada)
elseif ($mod === 'cancelar') {
    $id = require_idref();
    try {
        $resp = $ctrl->cancelarRefaccionControlador($id);
        if (is_string($resp)) { echo $resp; exit; }
        json_end(['ok'=>true,'data'=>$resp]);
    } catch (Throwable $e) {
        json_end(['ok'=>false,'error'=>'Error al cancelar: '.$e->getMessage()], 500);
    }
}

// Módulo desconocido
else {
    json_end(['ok'=>false,'error'=>'Módulo inválido para refacciones'], 400);
}
