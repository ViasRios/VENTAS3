<?php
require_once "../models/mainModel.php";
use app\models\mainModel;

header('Content-Type: application/json');

if (isset($_POST['IdRefaccion'], $_POST['estado'])) {
    $id = intval($_POST['IdRefaccion']);
    $estado = mainModel::limpiarCadena($_POST['estado']);

    $db = mainModel::conectar();
    $sql = $db->prepare("UPDATE refacciones SET autorizacion = ? WHERE IdRefaccion = ?");
    $success = $sql->execute([$estado, $id]);

    echo json_encode([
        "success" => $success,
        "mensaje" => $success ? "Estado actualizado a '$estado'" : "Error al actualizar estado"
    ]);
} else {
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos"]);
}
