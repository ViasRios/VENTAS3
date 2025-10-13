<?php
// app/ajax/buscaServicioYReparacion.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// ⚠️ Importa desde app/ajax → app/models
require_once __DIR__ . '/../models/mainModel.php';

use app\models\mainModel;

ini_set('display_errors', '0'); // no escupas HTML en endpoints JSON
ini_set('log_errors', '1');

try {
    if (!isset($_GET['termino'])) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'Falta parámetro "termino"'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $q = trim((string)$_GET['termino']);
    if ($q === '') { echo json_encode([]); exit; }

    $pdo = mainModel::conectar();
    if (method_exists($pdo, 'setAttribute')) {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Tu tabla real: servicios(Idser, Descripcion, Costo)
    $sql = "
        SELECT 
          s.Idser             AS id,
          TRIM(s.Descripcion) AS servicio,
          s.Costo             AS costo
        FROM servicios s
        WHERE TRIM(s.Descripcion) <> ''
          AND s.Descripcion LIKE :t
        ORDER BY s.Descripcion ASC
        LIMIT 50
    ";

    $stmt = $pdo->prepare($sql);
    $like = '%'.$q.'%';
    $stmt->bindParam(':t', $like, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $out = array_map(static function(array $r): array {
        return [
            'id'       => (string)($r['id'] ?? ''),
            'servicio' => (string)($r['servicio'] ?? ''),
            'costo'    => (string)($r['costo'] ?? '')
        ];
    }, $rows);

    echo json_encode($out, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('buscaServicioYReparacion: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Error interno'], JSON_UNESCAPED_UNICODE);
}
