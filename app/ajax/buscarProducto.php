<?php
require_once __DIR__ . '/../models/mainModel.php';
use app\models\mainModel;

$query = isset($_GET['query']) ? $_GET['query'] : '';

if ($query) {
    $pdo = mainModel::conectar();
    $stmt = $pdo->prepare("SELECT producto, precio_venta, caracteristica1, caracteristica2, caracteristica3, caracteristica4 FROM inventario WHERE producto LIKE :query LIMIT 10");
    $stmt->execute([':query' => "%$query%"]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Concatenar las características
foreach ($productos as &$producto) {
    // Concatenar las características
    $producto['descripcion'] = $producto['caracteristica1'] . ' ' . $producto['caracteristica2'] . ' ' . $producto['caracteristica3'] . ' ' . $producto['caracteristica4'];
}

    echo json_encode($productos);
}
