<?php
// getSeguimientos.php
session_start();
header('Content-Type: application/json');

try {
    // Conexión directa a la base de datos
    $host = 'localhost';
    $dbname = 'sistema';     // Nombre de tu base de datos
    $username = 'root';       // Usuario de XAMPP
    $password = '';           // Contraseña de XAMPP (vacía por defecto)
    
    // Crear conexión PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Consulta para obtener todas las ODS con estado "Seguimiento"
    $sql = "SELECT 
                Idods as id, 
                fechaSeguimiento as start,
                CONCAT('ODS #', Idods, ' - Seguimiento') as title,
                clientes.Nombre as cliente
            FROM ods 
            INNER JOIN clientes ON ods.Idcliente = clientes.Idcliente
            WHERE Status = 'Seguimiento' 
            AND fechaSeguimiento IS NOT NULL 
            AND fechaSeguimiento != ''";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $seguimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear para FullCalendar
    $eventos = [];
    foreach ($seguimientos as $seg) {
        if (!empty($seg['start'])) {
            $eventos[] = [
                'id' => $seg['id'],
                'title' => $seg['title'],
                'start' => $seg['start'],
                'allDay' => true,
                'color' => '#ff6b6b',
                'textColor' => '#ffffff',
                'description' => 'Cliente: ' . $seg['cliente']
            ];
        }
    }
    
    echo json_encode($eventos);
    
} catch (PDOException $e) {
    // Error de base de datos
    echo json_encode([
        'error' => true,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Error general
    echo json_encode([
        'error' => true,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>