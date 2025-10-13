<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/VENTAS3/config/app.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/VENTAS3/app/models/mainModel.php';

use app\models\mainModel;

header('Content-Type: application/json');

$conexion = mainModel::conectar();

$consulta = $conexion->prepare("SELECT Idasesor AS id, Nombre AS nombre FROM personal ORDER BY nombre ASC");
$consulta->execute();

$usuarios = [];

while ($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
    $usuarios[] = [
        "id" => $row['id'],
        "nombre" => $row['nombre']
    ];
}

echo json_encode($usuarios);
