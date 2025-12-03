<?php
require_once "../models/mainModel.php";
use app\models\mainModel;

// IMPORTANTE: Iniciar sesión para tener acceso a $_SESSION por si falla el POST
session_start();

header('Content-Type: application/json');

$archivo_nombre = null;

// 1. Procesar imagen (Evidencia)
if (isset($_FILES['Evidencia']) && $_FILES['Evidencia']['error'] === UPLOAD_ERR_OK) {
    $directorio = "../files/reportes/";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }
    // Limpiar nombre de archivo para evitar caracteres raros
    $ext = pathinfo($_FILES["Evidencia"]["name"], PATHINFO_EXTENSION);
    $nombre_archivo = time() . "_" . uniqid() . "." . $ext;
    $ruta_destino = $directorio . $nombre_archivo;

    if (move_uploaded_file($_FILES["Evidencia"]["tmp_name"], $ruta_destino)) {
        $archivo_nombre = $nombre_archivo;
    }
}

// 2. Verificar datos obligatorios
if (isset($_POST['Idods']) && isset($_POST['Reporte'])) {
    
    date_default_timezone_set('America/Mexico_City');
    
    $Idods   = intval($_POST['Idods']);
    $Reporte = mainModel::limpiarCadena($_POST['Reporte']);
    
    // --- CORRECCIÓN TÉCNICO ---
    // Intentamos obtenerlo del POST. Si viene vacío o cero, usamos la SESIÓN.
    $Tecnico = isset($_POST['Tecnico']) ? intval($_POST['Tecnico']) : 0;
    
    if ($Tecnico <= 0) {
        // Plan B: Usar la sesión si el POST falló
        $Tecnico = isset($_SESSION['Idasesor']) ? intval($_SESSION['Idasesor']) : (isset($_SESSION['id']) ? intval($_SESSION['id']) : 0);
    }

    $Fecha = date("Y-m-d");
    $Hora  = date("H:i:s");
    $mostrarCliente = isset($_POST['MostrarCliente']) ? 1 : 0;

    $db = mainModel::conectar();

    // 3. Insertar en Reportes
    // Asegúrate de que tu tabla realmente se llame 'reportetec' y las columnas coincidan
    $sql = $db->prepare("INSERT INTO reportetec (Idods, Tecnico, Reporte, Fecha, Hora, Evidencia) VALUES (?, ?, ?, ?, ?, ?)");
    $success = $sql->execute([$Idods, $Tecnico, $Reporte, $Fecha, $Hora, $archivo_nombre]);

    if ($success) {
        // 4. Insertar Nota opcional
        if ($mostrarCliente == 1) {
            // OJO: En notas solemos guardar el NOMBRE, no el ID. Buscamos el nombre en sesión.
            $nombreTecnico = $_SESSION['nombre'] ?? 'Técnico';
            $notaStmt = $db->prepare("INSERT INTO notas (Idods, Tecnico, Nota, Fecha, Hora) VALUES (?, ?, ?, ?, ?)");
            $notaStmt->execute([$Idods, $nombreTecnico, $Reporte, $Fecha, $Hora]);
        }

        echo json_encode(["success" => true, "mensaje" => "Reporte guardado correctamente"]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "Error SQL al guardar"]);
    }

} else {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos (ID ODS o Reporte)"]);
}
?>