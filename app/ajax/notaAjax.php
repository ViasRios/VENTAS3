<?php
require_once "../models/mainModel.php";
use app\models\mainModel;

header('Content-Type: application/json');

if(isset($_POST['Idods']) && isset($_POST['Tecnico']) && isset($_POST['Nota'])){
    date_default_timezone_set('America/Mexico_City');
    $Idods = intval($_POST['Idods']);
    $Tecnico = mainModel::limpiarCadena($_POST['Tecnico']);
    $Nota = mainModel::limpiarCadena($_POST['Nota']);
    $Fecha = date("Y-m-d");
    $Hora = date("H:i:s");

    try {
        $db = mainModel::conectar();
        $sql = $db->prepare("INSERT INTO notas (Idods, Tecnico, Nota, Fecha, Hora) VALUES (?, ?, ?, ?, ?)");
        $success = $sql->execute([$Idods, $Tecnico, $Nota, $Fecha, $Hora]);

        if($success){
            echo json_encode(["success" => true, "mensaje" => "Nota guardada correctamente"]);
            
        } else {
            echo json_encode(["success" => false, "mensaje" => "Error al guardar la nota"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "mensaje" => "Error: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos"]);
}
