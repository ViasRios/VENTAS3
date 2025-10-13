<?php
require_once "../models/mainModel.php";
use app\models\mainModel;

header('Content-Type: application/json');

$archivo_nombre = null;

// Procesar archivo antes del INSERT
if (isset($_FILES['Evidencia']) && $_FILES['Evidencia']['error'] === UPLOAD_ERR_OK) {
    $directorio = "../files/reportes/";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $nombre_archivo = time() . "_" . basename($_FILES["Evidencia"]["name"]);
    $ruta_destino = $directorio . $nombre_archivo;

    if (move_uploaded_file($_FILES["Evidencia"]["tmp_name"], $ruta_destino)) {
        $archivo_nombre = $nombre_archivo;
    }
}

if (isset($_POST['Idods']) && isset($_POST['Tecnico']) && isset($_POST['Reporte'])) {
    date_default_timezone_set('America/Mexico_City');
    
    $Idods = intval($_POST['Idods']);
    $Tecnico = mainModel::limpiarCadena($_POST['Tecnico']);
    $Reporte = mainModel::limpiarCadena($_POST['Reporte']);
    $Fecha = date("Y-m-d");
    $Hora = date("H:i:s");
    $mostrarCliente = isset($_POST['MostrarCliente']) ? 1 : 0;

    $db = mainModel::conectar();

    $sql = $db->prepare("INSERT INTO reportetec (Idods, Tecnico, Reporte, Fecha, Hora, Evidencia) VALUES (?, ?, ?, ?, ?, ?)");
    $success = $sql->execute([$Idods, $Tecnico, $Reporte, $Fecha, $Hora, $archivo_nombre]);

    if ($success) {
        // Si se marcó "mostrar al cliente", se crea nota
        if ($mostrarCliente == 1) {
            $notaStmt = $db->prepare("INSERT INTO notas (Idods, Tecnico, Nota, Fecha, Hora) VALUES (?, ?, ?, ?, ?)");
            $notaStmt->execute([$Idods, $Tecnico, $Reporte, $Fecha, $Hora]);
        }

        echo json_encode(["success" => true, "mensaje" => "Reporte guardado correctamente"]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "Error al guardar el reporte"]);
    }

    } else {
        echo json_encode(["success" => false, "mensaje" => "Faltan datos"]);
    }
