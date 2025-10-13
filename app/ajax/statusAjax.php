<?php
session_start();

file_put_contents("log.txt", "POST:\n" . print_r($_POST, true) . "\nSESSION:\n" . print_r($_SESSION, true), FILE_APPEND);

require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../autoload.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/VENTAS3/app/models/mainModel.php';

use app\models\mainModel;

header("Content-Type: application/json");

// Validar datos requeridos
if (!isset($_POST['Idods']) || !isset($_POST['nuevo_status']) || !isset($_POST['destinatarios'])) {
    echo json_encode([
        "tipo" => "error",
        "titulo" => "Datos incompletos",
        "texto" => "Faltan datos para procesar la solicitud."
    ]);
    exit;
}

// Conexión y sanitización
$conexion = mainModel::conectar();
$Idods = mainModel::limpiarCadena($_POST['Idods']);
$nuevo_status = mainModel::limpiarCadena($_POST['nuevo_status']);
$destinatarios = $_POST['destinatarios'];

// Validación de destinatarios
if (empty($destinatarios) || !is_array($destinatarios)) {
    echo json_encode([
        "tipo" => "error",
        "titulo" => "Destinatarios inválidos",
        "texto" => "Los destinatarios están vacíos o no en formato array."
    ]);
    exit;
}

// Actualizar estado del ODS
$update = $conexion->prepare("UPDATE ods SET Status = ? WHERE Idods = ?");
if (!$update->execute([$nuevo_status, $Idods])) {
    file_put_contents("log.txt", "ERROR al actualizar ODS\n", FILE_APPEND);
    echo json_encode([
        "tipo" => "error",
        "titulo" => "Error al actualizar",
        "texto" => "No se pudo cambiar el estado del ODS."
    ]);
    exit;
}

// Insertar notificaciones sin remitente
$mensaje = "El ODS #$Idods ha cambiado su estado a: $nuevo_status";
foreach ($destinatarios as $id_usuario) {
    $id_usuario = mainModel::limpiarCadena($id_usuario);

    $insert = $conexion->prepare("INSERT INTO notificaciones (Idasesor, mensaje, leido, fecha) VALUES (?, ?, 0, NOW())");
    if (!$insert->execute([$id_usuario, $mensaje])) {
        file_put_contents("log.txt", "ERROR al insertar notificación para usuario $id_usuario\n", FILE_APPEND);
    }
}

// Respuesta exitosa
echo json_encode([
    "tipo" => "exito",
    "titulo" => "Estado actualizado",
    "texto" => "Se cambió el estado y se notificó a los usuarios."
]);
exit;
