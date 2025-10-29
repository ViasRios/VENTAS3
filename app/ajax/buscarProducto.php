<?php
require_once __DIR__ . '/../models/mainModel.php';
use app\models\mainModel;

//$query = isset($_GET['query']) ? $_GET['query'] : '';

if (isset($_REQUEST['query'])) {
    $query = $_REQUEST['query'];

    try {
        $pdo = mainModel::conectar();

        $sql = "
            (SELECT
                producto COLLATE utf8mb4_general_ci AS producto, 
                precio_venta,
                
                -- CAMBIO 1: Si todas las características son nulas, devuelve ''
                COALESCE(CONCAT_WS(' ', caracteristica1, caracteristica2, caracteristica3, caracteristica4), '') COLLATE utf8mb4_general_ci AS descripcion,
                
                'producto' AS tipo
             FROM inventario
             WHERE producto LIKE :query)
            
            UNION
            
            (SELECT
                Descripcion COLLATE utf8mb4_general_ci AS producto,
                Costo AS precio_venta, 
                
                -- CAMBIO 2: Devuelve '' (vacío) en lugar de NULL
                '' AS descripcion,
                
                'servicio' AS tipo
             FROM servicios
             WHERE Descripcion LIKE :query)
            
            LIMIT 10";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':query' => "%$query%"]); 
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($resultados);

    } catch (PDOException $e) {
        http_response_code(500); 
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }

} else {
    http_response_code(400); 
    echo json_encode(['error' => 'No se proporcionó ningún término de búsqueda.']);
}
