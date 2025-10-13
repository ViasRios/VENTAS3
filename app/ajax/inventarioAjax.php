<?php
/**
 * app/ajax/inventarioAjax.php
 * Endpoint AJAX exclusivo para INVENTARIO:
 * - buscar:   autocomplete por término
 * - registrar: alta de producto (vía almacenController)
 * - eliminar:  baja por IdProducto (vía almacenController)
 */

require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\almacenController;
use app\models\mainModel;

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

// Helper para responder y terminar
function json_end($arr) {
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

// Validación de módulo
if (!isset($_POST['modulo_inventario'])) {
    json_end(['ok' => false, 'error' => 'Sin módulo (modulo_inventario no especificado)']);
}

$mod = $_POST['modulo_inventario'];

// RUTA: BUSCAR (autocomplete inventario)
if ($mod === 'buscar') {
    try {
        $termino = mainModel::limpiarCadena($_POST['termino'] ?? '');
        if ($termino === '') {
            json_end(['ok' => true, 'items' => []]); // vacío, sin error
        }

        $db = mainModel::conectar();
        $sql = $db->prepare("
            SELECT 
                IdProducto       AS id,
                producto         AS producto,
                codigo           AS codigo,
                stock            AS stock,
                precio_venta     AS precio_venta,
                caracteristica1  AS caracteristica1,
                caracteristica2  AS caracteristica2,
                caracteristica3  AS caracteristica3,
                caracteristica4  AS caracteristica4
            FROM inventario
            WHERE producto LIKE :q OR codigo LIKE :q
            ORDER BY producto ASC
            LIMIT 10
        ");
        $like = "%{$termino}%";
        $sql->execute([':q' => $like]);
        $items = $sql->fetchAll(PDO::FETCH_ASSOC);

        json_end(['ok' => true, 'items' => $items]);
    } catch (Throwable $e) {
        json_end(['ok' => false, 'error' => 'Error en búsqueda: '.$e->getMessage()]);
    }
}
// RUTA: REGISTRAR (alta de inventario)
// Debe llegar por POST; delega a almacenController
elseif ($mod === 'registrar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_end(['ok' => false, 'error' => 'Método no permitido']);
    }
    try {
        $ins_almacen = new almacenController();
        // El controlador debe devolver JSON
        $resp = $ins_almacen->registrarInventarioControlador();
        // Por si el controlador devuelve string JSON, respetamos su salida
        if (is_string($resp)) {
            echo $resp;
            exit;
        }
        json_end(['ok' => true, 'data' => $resp]);
    } catch (Throwable $e) {
        json_end(['ok' => false, 'error' => 'Error al registrar: '.$e->getMessage()]);
    }
}
// RUTA: ELIMINAR (baja en inventario)
// Se elimina por IdProducto (NO refacciones)
elseif ($mod === 'eliminar') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_end(['ok' => false, 'error' => 'Método no permitido']);
    }

    // Validar parámetro correcto para inventario
    if (!isset($_POST['IdProducto'])) {
        // Defensa: si mandaron por error IdRefaccion, avisar claramente
        if (isset($_POST['IdRefaccion'])) {
            json_end([
                'ok' => false,
                'error' => 'Este endpoint es solo para INVENTARIO. Para refacciones usa refaccionAjax.php',
                'hint' => 'Envía IdProducto aquí. Para refacciones, mueve tu fetch a app/ajax/refaccionAjax.php'
            ]);
        }
        json_end(['ok' => false, 'error' => 'Falta IdProducto']);
    }

    $id_producto = (int) $_POST['IdProducto'];
    if ($id_producto <= 0) {
        json_end(['ok' => false, 'error' => 'IdProducto inválido']);
    }

    try {
        $ins_almacen = new almacenController();
        // Se espera que el controlador devuelva JSON
        $resp = $ins_almacen->eliminarInventarioControlador($id_producto);

        if (is_string($resp)) {
            echo $resp; // string JSON del controlador
            exit;
        }
        json_end(['ok' => true, 'data' => $resp]);
    } catch (Throwable $e) {
        json_end(['ok' => false, 'error' => 'Error al eliminar: '.$e->getMessage()]);
    }
}
// Módulo desconocido
else {
    json_end(['ok' => false, 'error' => 'Módulo inválido para inventario']);
}
