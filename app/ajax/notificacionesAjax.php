<?php
session_start();
require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../autoload.php";

use app\models\mainModel;

header("Content-Type: application/json");

// Asegúrate de que la sesión tiene el ID del usuario logueado
$id_usuario = $_SESSION['id'] ?? $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    echo json_encode(["count" => 0]);
    exit;
}


$id_usuario = $_SESSION['id'];

$conexion = mainModel::conectar();

$sql = $conexion->prepare("SELECT COUNT(*) FROM notificaciones WHERE Idasesor = ? AND leido = 0");
$sql->execute([$id_usuario]);

$count = (int) $sql->fetchColumn();

echo json_encode(["count" => $count]);
