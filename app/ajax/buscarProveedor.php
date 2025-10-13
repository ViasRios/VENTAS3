<?php
require_once "../models/mainModel.php";
use app\models\mainModel;

header('Content-Type: application/json; charset=utf-8');

try{
    $db   = mainModel::conectar();
    $term = isset($_GET['proveedor']) ? trim($_GET['proveedor']) : '';

    if ($term === '') {
        // Sugerencias por default (top 10)
        $sql  = "SELECT IdProveedor, proveedor
                 FROM proveedor
                 ORDER BY proveedor ASC
                 LIMIT 10";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Búsqueda (insensible a mayúsculas)
    $sql = "SELECT IdProveedor, proveedor
            FROM proveedor
            WHERE proveedor LIKE :term COLLATE utf8mb4_general_ci
            ORDER BY proveedor ASC
            LIMIT 10";
    $stmt = $db->prepare($sql);
    $like = "%{$term}%";
    $stmt->bindParam(':term', $like, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e){
    // Loguea $e->getMessage() si quieres, pero no rompas el JSON
    echo json_encode([]);
}
