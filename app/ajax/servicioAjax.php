<?php
require_once "../../config/app.php";
require_once "../views/inc/session_start.php";
require_once "../../autoload.php";

use app\controllers\serviceController;
use app\models\mainModel; // Importamos el modelo

// 1. CONFIGURACIÓN DE SEGURIDAD JSON
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ob_start(); 
$resp = ['tipo' => 'error', 'texto' => 'Acción no procesada'];
try {
    if (isset($_POST['modulo_servicio'])) {
        $insServicio = new serviceController();
        $accion = $_POST['modulo_servicio'];
        // --- CASO 1: REGISTRAR ---
        if ($accion == "registrar") {
            $resp = $insServicio->registrarServicioControlador();
        }
        // --- CASO 2: ELIMINAR (SERVICIO DEL SISTEMA) ---
        elseif ($accion == "eliminar") {
            $resp = $insServicio->eliminarServicioControlador();
        }
        // --- CASO 3: ACTUALIZAR ---
        elseif ($accion == "actualizar") {
            $resp = $insServicio->actualizarServicioControlador();
        }
        // --- CASO 4: BUSCAR (AUTOCOMPLETADO) ---
        elseif ($accion == "buscar") {
            $mainModel = new mainModel();
            $termino = $mainModel->limpiarCadena($_POST['termino']);
            $sql = "SELECT Descripcion, Costo FROM servicios WHERE Descripcion LIKE '$termino%' LIMIT 10";
            $resultados = $mainModel->ejecutarConsulta($sql);
            $items = $resultados->fetchAll(PDO::FETCH_ASSOC);
            $resp = ['ok' => true, 'items' => $items];
        }
        // NUEVO: CASO 5 - CAMBIAR ESTADO (ACEPTAR/RECHAZAR EN ODS)
        elseif ($accion == "cambiar_estado") {
            $mainModel = new mainModel();
            $idOds = $mainModel->limpiarCadena($_POST['Idods']);
            $index = (int)$_POST['Index']; // Asegurar que sea entero
            $tipoAccion = $_POST['Accion'];
            // 1. Obtener datos actuales
            $sql = "SELECT Reparacion, Costorep FROM ods WHERE Idods = '$idOds'";
            $result = $mainModel->ejecutarConsulta($sql);
            $data = $result->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                $arrServicios = explode(',', $data['Reparacion']);
                $arrCostos    = explode(',', $data['Costorep']);
                if (isset($arrServicios[$index])) {
                    $descripcion = trim($arrServicios[$index]);
                    if ($tipoAccion == 'aceptar') {
                        if (strpos($descripcion, '[OK]') === false) {
                            $arrServicios[$index] = $descripcion . " [OK]";
                        }
                    } elseif ($tipoAccion == 'rechazar') {
                        $arrServicios[$index] = str_replace(" [OK]", "", $descripcion);
                    }
                    // Reconstruir strings
                    $strServicios = implode(',', $arrServicios);
                    // Los costos no cambian, pero debemos pasarlos igual
                    $strCostos = implode(',', $arrCostos);
                    // Actualizar BD
                    $upSql = "UPDATE ods SET Reparacion = '$strServicios', Costorep = '$strCostos' WHERE Idods = '$idOds'";
                    $upResult = $mainModel->ejecutarConsulta($upSql);

                    if ($upResult->rowCount() >= 0) {
                        $resp = ['success' => true];
                    } else {
                        $resp = ['success' => false, 'error' => 'No se pudo actualizar la BD'];
                    }
                } else {
                    $resp = ['success' => false, 'error' => 'Índice inválido'];
                }
            } else {
                $resp = ['success' => false, 'error' => 'ODS no encontrada'];
            }
        }
        // NUEVO: CASO 6 - ELIMINAR MASIVO (DE LA ODS)
        elseif ($accion == "eliminar_masivo") {
            $mainModel = new mainModel();
            $idOds = $mainModel->limpiarCadena($_POST['Idods']);
            $indicesRaw = $_POST['indices']; // "0,2,5"
            $indicesBorrar = explode(',', $indicesRaw);

            // 1. Obtener datos
            $sql = "SELECT Reparacion, Costorep FROM ods WHERE Idods = '$idOds'";
            $result = $mainModel->ejecutarConsulta($sql);
            $data = $result->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $arrServicios = explode(',', $data['Reparacion']);
                $arrCostos    = explode(',', $data['Costorep']);
                $nuevoServicios = [];
                $nuevoCostos    = [];
                // 2. Filtrar
                foreach ($arrServicios as $i => $serv) {
                    // Si el índice NO está en la lista de borrar, lo guardamos
                    if (!in_array($i, $indicesBorrar)) {
                        $nuevoServicios[] = trim($serv);
                        $nuevoCostos[]    = isset($arrCostos[$i]) ? trim($arrCostos[$i]) : '0';
                    }
                }
                $strServicios = implode(',', $nuevoServicios);
                $strCostos    = implode(',', $nuevoCostos);
                // 3. Actualizar
                $upSql = "UPDATE ods SET Reparacion = '$strServicios', Costorep = '$strCostos' WHERE Idods = '$idOds'";
                $upResult = $mainModel->ejecutarConsulta($upSql);
                $resp = ['success' => true];
            } else {
                $resp = ['success' => false, 'error' => 'ODS no encontrada'];
            }
        }
    } else {
        $resp = ['tipo' => 'error', 'texto' => 'No se envió el módulo'];
    }

    // VALIDACIÓN FINAL DE JSON
    if (is_string($resp)) {
        $decoded = json_decode($resp, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $resp = $decoded;
        }
    }

} catch (Throwable $e) {
    $resp = ['tipo' => 'error', 'texto' => 'Error del servidor: ' . $e->getMessage()];
}

// 2. LIMPIEZA Y ENVÍO FINAL
ob_clean(); 
echo json_encode($resp);
exit;