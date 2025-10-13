<?php
require_once "../models/mainModel.php";
use app\models\mainModel;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = mainModel::limpiarCadena($_POST['estado_nombre']);
    $color = mainModel::limpiarCadena($_POST['estado_color']);

    $db = mainModel::conectar();

    // Verificar si ya existe ese estado en la tabla de colores
    $verificar = $db->prepare("SELECT * FROM ods WHERE Status = :estado");
    $verificar->bindParam(":estado", $estado);
    $verificar->execute();

    if ($verificar->rowCount() == 0) {
        $insertar = $db->prepare("INSERT INTO estado_colores (nombre_estado, color_estado) VALUES (:estado, :color)");
        $insertar->bindParam(":estado", $estado);
        $insertar->bindParam(":color", $color);
        $insertar->execute();

        echo json_encode(["success" => true, "mensaje" => "Estado registrado correctamente."]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "Este estado ya existe."]);
    }
}
