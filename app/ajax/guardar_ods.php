<?php
require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\odsController;

header('Content-Type: application/json; charset=utf-8');
ob_start();

try {
    // === INICIO DEL PLAN D: PARSEO MANUAL ===
    $raw_data = file_get_contents('php://input');
    $post_data = [];

    // 1. Separamos los pares (ej: "modulo_ods=registrar", "filtro_campo=Status")
    $pairs = explode('&', $raw_data);

    // 2. Recorremos cada par y lo separamos por "="
    foreach ($pairs as $pair) {
        $parts = explode('=', $pair, 2); // Limitar a 2 partes (clave y valor)
        if (count($parts) === 2) {
            // Limpiamos (urldecode) la clave y el valor
            $key = urldecode($parts[0]);
            $value = urldecode($parts[1]);
            $post_data[$key] = $value; // Los guardamos en nuestro array
        }
    }
    // === FIN DEL PLAN D ===

    // 3. Revisamos nuestra variable LOCAL (que acabamos de crear)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($post_data['modulo_ods'])) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'error'   => 'Petición inválida (Plan D)',
            'debug'   => $raw_data, // Seguimos mostrando qué llegó
            'parsed'  => $post_data  // ¡NUEVO! Mostramos qué logramos parsear
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. El resto del código es idéntico al Plan C
    $mod = $post_data['modulo_ods'];
    $ctrl = new odsController();

    switch ($mod) {
        case 'registrar':
            // Pasamos nuestro array local a la función
            // (Asegúrate de que odsController.php acepte este argumento)
            $resp = $ctrl->registrarOdsControlador($post_data);
            break;
        default:
            $resp = ['success'=>false,'error'=>'Módulo no soportado: '.$mod];
    }

    // Devolvemos la respuesta
    if (is_string($resp)) {
        $decoded = json_decode($resp, true);
        $resp = $decoded !== null ? $decoded : ['success'=>false,'error'=>'Respuesta no-JSON del controlador'];
    }

    ob_clean();
    echo json_encode($resp, JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['success'=>false,'error'=>'Excepción: '.$e->getMessage(), 'linea'=>$e->getLine()], JSON_UNESCAPED_UNICODE);
    exit;
}
?>