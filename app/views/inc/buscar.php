<?php
// Define APP_URL. Asegúrate de que apunte a la raíz de tu proyecto.
// Ejemplo: http://localhost/VENTAS3/
define('APP_URL', 'http://localhost/VENTAS3/');

// Conexión a la base de datos
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'sistema';
$conexion = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$busqueda = isset($_GET['termino_busqueda']) ? trim($_GET['termino_busqueda']) : '';

if (!empty($busqueda)) {
    $param_busqueda = "%" . $busqueda . "%";

    $sql = "SELECT 
                ods.Idods, 
                clientes.Idcliente, 
                clientes.Nombre
            FROM 
                ods 
            INNER JOIN 
                clientes ON ods.Idcliente = clientes.Idcliente
            WHERE 
                ods.Idods LIKE ? OR clientes.Idcliente LIKE ? OR clientes.Nombre LIKE ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $param_busqueda, $param_busqueda, $param_busqueda);
    $stmt->execute();
    
    $resultado = $stmt->get_result();
    $filas = $resultado->fetch_all(MYSQLI_ASSOC);
    $numero_de_resultados = count($filas);
    
    $stmt->close();
    $conexion->close();

    if ($numero_de_resultados === 1) {
        $id_ods_unico = $filas[0]['Idods'];
        header('Location: ' . APP_URL . 'odsView/' . $id_ods_unico . '/');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Búsqueda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">Resultados para: "<?php echo htmlspecialchars($busqueda); ?>"</h1>

            <?php if (!empty($busqueda)) : ?>
                <?php if (isset($numero_de_resultados) && $numero_de_resultados > 1) : ?>
                    <div class="notification is-info">
                        Se encontraron varios resultados. Por favor, selecciona la ODS que deseas ver.
                    </div>
                    <table class="table is-fullwidth is-striped is-hoverable">
                        <thead><tr><th>ID ODS (Click para ver)</th><th>ID Cliente</th><th>Nombre del Cliente</th></tr></thead>
                        <tbody>
                            <?php foreach ($filas as $fila) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo APP_URL . 'odsView/' . htmlspecialchars($fila['Idods']) . '/'; ?>">
                                            <strong><?php echo htmlspecialchars($fila['Idods']); ?></strong>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($fila['Idcliente']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['Nombre']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif (isset($numero_de_resultados) && $numero_de_resultados === 0): ?>
                    <div class="notification is-warning"><p>No se encontraron resultados para tu búsqueda.</p></div>
                <?php endif; ?>
            <?php else: ?>
                <div class="notification"><p>Por favor, ingresa un término para buscar.</p></div>
            <?php endif; ?>
            <br>
            <a href="javascript:history.back()" class="button is-link"><span>Volver</span></a>
        </div>
    </section>
</body>
</html>