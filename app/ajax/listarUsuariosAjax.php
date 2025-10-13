<?php
header("Content-Type: application/json");
require_once $_SERVER['DOCUMENT_ROOT'] . '/VENTAS3/config/app.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/VENTAS3/app/models/mainModel.php';

use app\models\mainModel;

$conexion = mainModel::conectar();

$consulta = $conexion->query("SELECT Idasesor, Nombre FROM personal");
$usuarios = [];

while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
    $usuarios[] = $fila;
}

echo json_encode($usuarios);
