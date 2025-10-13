<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// CONFIGURACIÓN DE TU BASE DE DATOS - CAMBIA ESTOS VALORES
$host = 'localhost';
$dbname = 'sistema'; // o el nombre de tu base de datos
$username = 'root';   // usuario de MySQL
$password = '';       // contraseña de MySQL (normalmente vacía en XAMPP)

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idods = $_POST['idods'] ?? '';
    $idTecnico = $_POST['idTecnico'] ?? '';
    
    if (empty($idods)) {
        echo json_encode(['success' => false, 'message' => 'ID de ODS requerido']);
        exit;
    }
    
    try {
        // Si idTecnico está vacío, asignar NULL
        if (empty($idTecnico)) {
            $query = "UPDATE ods SET IdTecnico = NULL WHERE Idods = ?";
            $stmt = $conexion->prepare($query);
            $stmt->execute([$idods]);
        } else {
            $query = "UPDATE ods SET IdTecnico = ? WHERE Idods = ?";
            $stmt = $conexion->prepare($query);
            $stmt->execute([$idTecnico, $idods]);
        }
        
        echo json_encode(['success' => true, 'message' => '✅ Técnico actualizado correctamente']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '❌ Error al actualizar: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => '⚠️ Método no permitido']);
}
