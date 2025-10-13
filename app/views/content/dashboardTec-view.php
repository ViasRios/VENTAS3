<?php
$modulo_actual = "dashboard"; 
// Conexión a la base de datos
$conexion = new PDO("mysql:host=localhost;dbname=sistema;charset=utf8", "root", "");

// Obtener las fechas seleccionadas desde el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Guardar las fechas seleccionadas en la sesión
    $_SESSION['start_date'] = $_POST['start_date'];
    $_SESSION['end_date'] = $_POST['end_date'];
}

// Usar las fechas de la sesión si están disponibles, o fechas por defecto
$start_date = isset($_SESSION['start_date']) ? $_SESSION['start_date'] : date('Y-m-d', strtotime('-1 week'));
$end_date = isset($_SESSION['end_date']) ? $_SESSION['end_date'] : date('Y-m-d');

// Obtener el ID del usuario desde la sesión (ajustar según tu estructura de sesión)
$id_usuario = $_SESSION['id'];  // Suponiendo que el ID de usuario está en la sesión

// Verificación de las fechas seleccionadas
// echo 'Fecha de inicio: ' . $start_date . '<br>';
// echo 'Fecha de fin: ' . $end_date . '<br>';

// Si hay una búsqueda, obtenemos el término de búsqueda de la sesión o de la variable $_POST
$busqueda = isset($_SESSION['dashboardTec']) ? $_SESSION['dashboardTec'] : (isset($_POST['txt_buscador']) ? $_POST['txt_buscador'] : '');

// ----------------------- CONSULTA PARA EL GRÁFICO -----------------------

// Aseguramos que las fechas sean del formato adecuado
$start_date = date('Y-m-d', strtotime($start_date));
$end_date = date('Y-m-d', strtotime($end_date));

// Consulta SQL para el gráfico de ODS con filtro por ID de usuario y rango de fechas
$sql_ods = "
    SELECT COUNT(Idods) AS ods, Status
    FROM ods
    WHERE Fecha BETWEEN :start_date AND :end_date
    AND Idasesor = :id  
    GROUP BY status
";

// Ejecutar la consulta para ODS
$stmt_ods = $conexion->prepare($sql_ods);
$stmt_ods->execute([':start_date' => $start_date, ':end_date' => $end_date, ':id' => $id_usuario]);

$ods_data = $stmt_ods->fetchAll(PDO::FETCH_ASSOC);

// Inicializar los arrays con valores vacíos
$labels = [];
$values = [];

// Si se encontraron datos, llenamos los arrays
if (!empty($ods_data)) {
    foreach ($ods_data as $row) {
        $labels[] = $row['Status'];
        $values[] = (int)$row['ods']; // Asegurarnos que sea un número
    }
}

// Convertir a JSON para utilizar en JavaScript
$labels_json = json_encode($labels);
$values_json = json_encode($values);

// ----------------------- CONSULTA PARA LA BÚSQUEDA -----------------------

// Consulta SQL para buscar en ODS (por Idods o Status)
$sql_buscador_ods = "
    SELECT Idods, Status
    FROM ods
    WHERE Idods LIKE :busqueda OR Status LIKE :busqueda
";

// Ejecutar la consulta de búsqueda en ODS
$stmt_buscador_ods = $conexion->prepare($sql_buscador_ods);
$busqueda_param = "%$busqueda%"; // Aseguramos que busque cualquier coincidencia parcial
$stmt_buscador_ods->execute([':busqueda' => $busqueda_param]);

$ods_resultados = $stmt_buscador_ods->fetchAll(PDO::FETCH_ASSOC);

// Consulta SQL para buscar en CLIENTES (por Nombre o Número)
$sql_buscador_clientes = "
    SELECT Idcliente AS Id, Nombre, Numero
    FROM clientes
    WHERE Nombre LIKE :busqueda OR Numero LIKE :busqueda
";

// Ejecutar la consulta de búsqueda en Clientes
$stmt_buscador_clientes = $conexion->prepare($sql_buscador_clientes);
$stmt_buscador_clientes->execute([':busqueda' => $busqueda_param]);

$clientes_resultados = $stmt_buscador_clientes->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ---------------------- HTML PARA MOSTRAR LOS RESULTADOS -------------------- -->

<div class="container is-fluid">
    <div class="level">
        <div class="level-left">
            <h1 class="title">PÁGINA PRINCIPAL</h1>
        </div>
    </div>

  	<div class="columns is-flex is-justify-content-center">
    	<figure class="image is-128x128">
    		<?php
    			if(is_file("./app/views/fotos/".$_SESSION['foto'])){
    				echo '<img class="is-rounded" src="'.APP_URL.'app/views/fotos/'.$_SESSION['foto'].'">';
    			}else{
    				echo '<img class="is-rounded" src="'.APP_URL.'app/views/fotos/default.png">';
    			}
    		?>
		</figure>
  	</div>
  	<div class="columns is-flex is-justify-content-center">
  		<h2 class="subtitle">¡BIENVENIDO <?php echo $_SESSION['nombre']." "; ?>!</h2>
  	</div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</div>

<div class="container pb-6 pt-6">

    <!-- ---------------------- CAMPO DE BÚSQUEDA ---------------------- -->
    <div class="columns">
        <div class="column">
            <form class="FormularioAjax no-sweetalert" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" >
                <input type="hidden" name="modulo_url" value="<?php echo isset($modulo_actual) ? $modulo_actual : 'dashboardTec'; ?>">
                <input type="hidden" name="modulo_buscador" value="buscar">
                <input type="hidden" name="modulo_url" value="<?php echo $modulo_actual; ?>">
                <div class="columns">
                    <div class="column is-three-quarters">
                        <div class="field">
                            <p class="control">
                                <input class="input is-rounded" type="text" name="txt_buscador" placeholder="¿Qué estás buscando?" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,50}" maxlength="50" required>
                            </p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <p class="control">
                                <button class="button is-info" type="submit">
                                    Buscar
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Formulario para seleccionar fechas -->
    <form method="POST" action="">
        <label for="start_date">Fecha de Inicio:</label>
        <input type="date" name="start_date" id="start_date" value="<?php echo $start_date; ?>" required>
        
        <label for="end_date">Fecha de Fin:</label>
        <input type="date" name="end_date" id="end_date" value="<?php echo $end_date; ?>" required>
        
        <button type="submit" class="button custom-blue">Filtrar</button>
    </form>
    <style>
        .custom-blue {
            background-color: #1d4d80ff;
            color: white;
            padding: 2px 15px;
            margin-top: -8px;
        }
    </style>
    <br>
    <!-- Mostrar el gráfico de ODS -->
    <div class="columns is-flex is-justify-content-center">
        <div class="column is-half">
            <!-- tamaño en pixeles -->
            <canvas id="odsChart" width="1400" height="400"></canvas>
        </div>
    </div>

<!--    <div class="box">
        <h3 class="title is-5">Resultados en Clientes:</h3>
        <?php if (count($clientes_resultados) > 0): ?>
            <table class="table is-striped is-fullwidth">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Número</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes_resultados as $fila): ?>
                        <tr>
                            <td><?php echo $fila['Id']; ?></td>
                            <td><?php echo $fila['Nombre']; ?></td>
                            <td><?php echo $fila['Numero']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No se encontraron resultados en Clientes.</p>
        <?php endif; ?>
    </div> -->
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Verificar que hay datos antes de crear el gráfico
    <?php if (!empty($labels) && !empty($values)): ?>
        var ctx = document.getElementById('odsChart').getContext('2d');
        var odsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $labels_json; ?>,
                datasets: [{
                    label: 'Total de ODS por Estado',
                    data: <?php echo $values_json; ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)', // Color 1
                        'rgba(54, 162, 235, 0.2)', // Color 2
                        'rgba(255, 206, 86, 0.2)', // Color 3
                        'rgba(75, 192, 192, 0.2)', // Color 4
                        'rgba(153, 102, 255, 0.2)', // Color 5
                        'rgba(255, 159, 64, 0.2)', // Color 6
                        'rgba(100, 100, 255, 0.2)', // Color 7
                        'rgba(128, 128, 0, 0.2)', // Color 8
                        'rgba(255, 165, 0, 0.2)'  // Color 9
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)', // Color 1
                        'rgba(54, 162, 235, 1)', // Color 2
                        'rgba(255, 206, 86, 1)', // Color 3
                        'rgba(75, 192, 192, 1)', // Color 4
                        'rgba(153, 102, 255, 1)', // Color 5
                        'rgba(255, 159, 64, 1)', // Color 6
                        'rgba(100, 100, 255, 1)', // Color 7
                        'rgba(128, 128, 0, 1)', // Color 8
                        'rgba(255, 165, 0, 1)'  // Color 9
                    ],
                    borderWidth: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    <?php else: ?>
        console.log('No hay datos para mostrar en el gráfico');
        document.getElementById('odsChart').innerHTML = '<p class="has-text-centered">No hay datos para el periodo seleccionado</p>';
    <?php endif; ?>
});
</script>
