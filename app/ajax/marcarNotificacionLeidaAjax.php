<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../autoload.php';

use app\models\mainModel;

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'Falta el ID']);
    exit;
}

$id_notif = $_POST['id'];
$id_usuario = $_SESSION['id'] ?? $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

try {
    $conexion = mainModel::conectar();
    $sql = $conexion->prepare("UPDATE notificaciones SET leido = 1 WHERE id = ? AND Idasesor = ?");
    $resultado = $sql->execute([$id_notif, $id_usuario]);

    echo json_encode(['success' => $resultado]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
