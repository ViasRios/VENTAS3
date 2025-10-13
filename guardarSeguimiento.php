<?php
// guardarSeguimiento.php (versión final)
session_start();
header('Content-Type: application/json');

// Desactivar errores para respuesta JSON limpia
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $odsId = isset($_POST['odsId']) ? intval($_POST['odsId']) : 0;
        $fechaSeguimiento = isset($_POST['fechaSeguimiento']) ? $_POST['fechaSeguimiento'] : '';

        // Validar datos
        if ($odsId <= 0) {
            throw new Exception('ID de ODS inválido');
        }
        
        if (empty($fechaSeguimiento)) {
            throw new Exception('La fecha de seguimiento está vacía');
        }

        // Validar formato de fecha
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fechaSeguimiento);
        if (!$fechaObj) {
            throw new Exception('Formato de fecha inválido. Use YYYY-MM-DD');
        }

        // CONEXIÓN A LA BASE DE DATOS
        $host = 'localhost';
        $dbname = 'sistema'; // Cambia si tu base de datos tiene otro nombre
        $username = 'root';   // Usuario por defecto de XAMPP
        $password = '';       // Contraseña por defecto de XAMPP (vacía)
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verificar que la ODS existe
        $stmtCheck = $pdo->prepare("SELECT Idods FROM ods WHERE Idods = ?");
        $stmtCheck->execute([$odsId]);
        
        if ($stmtCheck->rowCount() === 0) {
            throw new Exception("La ODS #$odsId no existe");
        }

        // Actualizar la fecha de seguimiento y el estado
        $sql = "UPDATE ods SET FechaSeguimiento = ?, Status = 'Seguimiento' WHERE Idods = ?";
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute([$fechaSeguimiento, $odsId]);

        if ($resultado) {
            // Verificar si realmente se actualizó
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true, 
                    'message' => "✅ Seguimiento programado para el $fechaSeguimiento"
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'message' => "ℹ️ La ODS ya estaba en seguimiento con esta fecha"
                ]);
            }
        } else {
            throw new Exception('Error al ejecutar la consulta en la base de datos');
        }

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false, 
            'message' => '❌ Error de base de datos: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => '❌ Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Método no permitido'
    ]);
}
?>