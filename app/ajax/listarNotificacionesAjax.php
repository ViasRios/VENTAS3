<?php
session_start();
require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../autoload.php";


use app\models\mainModel;

header('Content-Type: application/json');

$id_usuario = $_SESSION['id'] ?? $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo json_encode([]);
    exit;
}


$id_usuario = $_SESSION['id'];
$conexion = mainModel::conectar();

// Obtener las notificaciones no leídas
/* $sql = $conexion->prepare("SELECT mensaje, fecha FROM notificaciones WHERE Idasesor = ? AND leido = 0 ORDER BY fecha DESC");
$sql->execute([$id_usuario]);

$notificaciones = [];

while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
    $notificaciones[] = [
        "mensaje" => $row['mensaje'],
        "fecha" => date("d/m/Y H:i", strtotime($row['fecha']))
    ];
}
*/


$sql = $conexion->prepare("
    SELECT id, mensaje, fecha, leido 
    FROM notificaciones 
    WHERE Idasesor = ? 
    AND fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY fecha DESC
");
$sql->execute([$id_usuario]);

$notificaciones = [];

while ($row = $sql->fetch(PDO::FETCH_ASSOC)) {
    $notificaciones[] = [
    "id" => $row['id'],
    "mensaje" => $row['mensaje'],
    "fecha" => date("d/m/Y H:i", strtotime($row['fecha'])),
    "leido" => (bool)$row['leido']
    ];
}


echo json_encode($notificaciones);
exit;