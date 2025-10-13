<?php 
require_once "../models/mainModel.php";
use app\models\mainModel; 
session_start();
header('Content-Type: application/json');
$archivo_nombre = null;

// Procesar archivo opcional (muestra_foto)
if (isset($_FILES['muestra_foto']) && $_FILES['muestra_foto']['error'] === UPLOAD_ERR_OK) {
    $directorio = "../files/refacciones/";
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }
    $nombre_archivo = time() . "_" . basename($_FILES["muestra_foto"]["name"]);
    $ruta_destino = $directorio . $nombre_archivo;
    if (move_uploaded_file($_FILES["muestra_foto"]["tmp_name"], $ruta_destino)) {
        $archivo_nombre = $nombre_archivo;
    }
}
// Validar campos obligatorios
if (
    isset($_POST['Idods']) &&
    isset($_POST['Producto']) &&
    isset($_POST['stock']) &&
    isset($_POST['refaccion']) &&
    isset($_POST['Nombre_refaccion']) &&
    isset($_POST['descripcion']) &&
    isset($_SESSION['id']) 
) {
    // Limpiar y convertir valores
    $IdODS = intval($_POST['Idods']);
    $IdAsesor = intval($_SESSION['id']); 
    $producto = mainModel::limpiarCadena($_POST['Producto']);
    $stock = intval($_POST['stock']);
    $refaccion = mainModel::limpiarCadena($_POST['refaccion']);
    $Nombre_refaccion = mainModel::limpiarCadena($_POST['Nombre_refaccion']);
    $descripcion = mainModel::limpiarCadena($_POST['descripcion']);
    $muestra_texto = isset($_POST['muestra_texto']) ? mainModel::limpiarCadena($_POST['muestra_texto']) : null;

    $db = mainModel::conectar();

    // Insertar refacción
    $sql = $db->prepare("INSERT INTO refacciones (
        IdProducto, IdODS, IdAsesor, producto, stock, refaccion, Nombre_refaccion, descripcion, muestra_texto, muestra_foto
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $success = $sql->execute([
        null, // recuerda modificar esto para que se busque de la tabla inventario
        $IdODS,
        $IdAsesor,
        $producto,
        $stock,
        $refaccion,
        $Nombre_refaccion,
        $descripcion,
        $muestra_texto,
        $archivo_nombre
    ]);

    if ($success) {
        echo json_encode(["success" => true, "mensaje" => "Refacción guardada correctamente"]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "Error al guardar la refacción"]);
    }

} else {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos obligatorios o sesión no válida"]);
}