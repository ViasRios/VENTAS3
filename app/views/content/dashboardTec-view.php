<?php
use app\controllers\odsController;

$modulo_actual = "dashboardTec"; 
$conexion = new PDO("mysql:host=localhost;dbname=sistema;charset=utf8", "root", "");

// ---------------------------------------------------------
// 1. LÓGICA DE FILTROS (FECHAS Y ESTADO)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['start_date'])) $_SESSION['start_date'] = $_POST['start_date'];
    if(isset($_POST['end_date']))   $_SESSION['end_date'] = $_POST['end_date'];
    
    // Capturamos el estado si se envía
    if(isset($_POST['filtro_status_ods'])) {
        $_SESSION['filtro_dashboard_status'] = $_POST['filtro_status_ods'];
    }

    // 2. REDIRECCIÓN SEGURA (JS) - Para evitar error al regresar
    echo '<script> window.location.href = "' . APP_URL . $modulo_actual . '/"; </script>';
    exit();
}

if (!isset($_SESSION['start_date'])) $_SESSION['start_date'] = date('Y-m-d', strtotime('-3 weeks'));
if (!isset($_SESSION['end_date'])) $_SESSION['end_date'] = date('Y-m-d');

$start_date = $_SESSION['start_date'];
$end_date   = $_SESSION['end_date'];
$id_usuario = $_SESSION['id'];
$filtroActual = $_SESSION['filtro_dashboard_status'] ?? 'Todos';

// ---------------------------------------------------------
// 2. LISTA REDUCIDA DE ESTADOS
// ---------------------------------------------------------
$estadosTecnico = ['DIAGNOSTICO', 'REPARACION', 'REFACCIONES'];

// ---------------------------------------------------------
// 3. DATOS DEL GRÁFICO
// ---------------------------------------------------------
$start_date_fmt = date('Y-m-d', strtotime($start_date));
$end_date_fmt   = date('Y-m-d', strtotime($end_date));

// Colores
$colores_status = [
    'RECEPCION' => '#f97316', 'DIAGNOSTICO' => '#eab308', 'PRESUPUESTO' => '#a855f7', 
    'AUTORIZACION' => '#22c55e', 'STANDBY' => '#f43f5e', 'REPARACION' => '#0ea5e9', 
    'REFACCIONES' => '#8b5cf6', 'LISTOE' => '#10b981', 'ALMACEN' => '#ec4899', 
    'SEGUIMIENTO' => '#6366f1', 'ENTREGADO' => '#facc15', 'CANCELADO' => '#6b7280'
];

$sql_raw = "SELECT Idods, Status, Fecha, DATEDIFF(NOW(), Fecha) as dias_antiguedad
            FROM ods 
            WHERE Fecha BETWEEN :start_date AND :end_date 
            AND IdTecnico = :id  
            AND (UPPER(Status) LIKE '%REPARACI%' OR UPPER(Status) LIKE '%DIAGN%' OR UPPER(Status) LIKE '%REFACCIONES%')
            ORDER BY Status, Idods";

$stmt = $conexion->prepare($sql_raw);
$stmt->execute([':start_date' => $start_date_fmt, ':end_date' => $end_date_fmt, ':id' => $id_usuario]);
$raw_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesamiento Gráfico
$inner_values = []; $inner_colors = []; $inner_labels = []; 
$inner_ids = []; // <--- 1. NUEVO: Array para guardar los IDs
$outer_groups = [];

function get_time_color($d) { if ($d <= 7) return '#10b981'; if ($d <= 30) return '#f59e0b'; return '#ef4444'; }
function get_time_text($d) { if ($d <= 7) return $d . "d"; if ($d <= 30) return round($d/7) . "sem"; return round($d/30) . "mes"; }

foreach ($raw_data as $row) {
    $dias = (int)$row['dias_antiguedad'];
    $inner_values[] = 1; 
    $inner_colors[] = get_time_color($dias);
    $inner_labels[] = "ODS #" . $row['Idods'] . " (" . get_time_text($dias) . ")";
    $inner_ids[]    = $row['Idods']; // <--- 2. NUEVO: Guardamos el ID
    
    $st = strtoupper($row['Status']);
    if (!isset($outer_groups[$st])) { $outer_groups[$st] = 0; }
    $outer_groups[$st]++;
}

$outer_labels = []; $outer_values = []; $outer_colors = [];
foreach ($outer_groups as $st => $count) {
    $outer_labels[] = $st;
    $outer_values[] = $count;
    $color = '#ccc';
    foreach($colores_status as $key => $val){
        if(strpos($st, $key) !== false) { $color = $val; break; }
    }
    $outer_colors[] = $color;
}

$js_out_val = json_encode($outer_values);
$js_out_col = json_encode($outer_colors);
$js_out_lab = json_encode($outer_labels);
$js_in_val = json_encode($inner_values);
$js_in_col = json_encode($inner_colors);
$js_in_lab = json_encode($inner_labels);
$js_in_ids = json_encode($inner_ids); // <--- 3. NUEVO: Enviamos IDs a JS
$total_ods = count($raw_data);
?>

<style>
    .dashboard-card { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); overflow: hidden; height: 100%; display: flex; flex-direction: column; border: 1px solid #eee; }
    .dashboard-card-header { background-color: #1d4d80; color: white; padding: 12px 20px; font-weight: 700; font-size: 1rem; display: flex; align-items: center; justify-content: space-between; }
    .dashboard-card-body { padding: 15px; background-color: #fff; flex-grow: 1; }
    
    .filter-box { background: white; padding: 5px 15px; border-radius: 50px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #eee; display: inline-flex; align-items: center; gap: 8px; }
    .custom-date { border: 1px solid #ddd; border-radius: 5px; padding: 3px 8px; color: #555; font-size: 0.85rem; height: 30px; }
    .custom-select-filter { border: 1px solid #ddd; border-radius: 5px; padding: 0 8px; color: #555; font-size: 0.85rem; height: 30px; font-weight: 600; cursor: pointer; }
    .btn-filter { background-color: #1d4d80; color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-size: 0.9rem; transition: background 0.2s; display: flex; align-items: center; justify-content: center; }
    .btn-filter:hover { background-color: #153a61; }
    
    .welcome-title { color: #1d4d80; font-weight: 800; font-size: 1.4rem; margin-bottom: 0 !important; }
    .welcome-subtitle { color: #888; font-size: 0.85rem; margin-top: 2px; }
    .chart-wrapper { position: relative; height: 320px; width: 100%; display: flex; justify-content: center; align-items: center; }
    .custom-legend { display: flex; justify-content: center; gap: 15px; margin-top: 10px; font-size: 0.8rem; color: #666; }
    .legend-item { display: flex; align-items: center; gap: 5px; }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container is-fluid mb-6">
    
    <div class="columns is-vcentered mb-4 mt-4">
        <div class="column">
            <h1 class="welcome-title"><i class="fas fa-user-cog mr-2"></i> Hola, <?php echo $_SESSION['nombre']; ?></h1>
            <p class="welcome-subtitle">Actividad del: <strong><?php echo date('d/m/Y', strtotime($start_date)); ?></strong> al <strong><?php echo date('d/m/Y', strtotime($end_date)); ?></strong></p>
        </div>

        <div class="column is-narrow">
            <form method="POST" action="">
                <div class="filter-box">
                    
                    <span class="has-text-weight-bold is-size-7 has-text-grey">Ver:</span>
                    <select name="filtro_status_ods" class="custom-select-filter">
                        <option value="Todos" <?php echo ($filtroActual == 'Todos') ? 'selected' : ''; ?>>Todos</option>
                        <?php foreach($estadosTecnico as $estado): ?>
                            <option value="<?php echo $estado; ?>" <?php echo ($filtroActual == $estado) ? 'selected' : ''; ?>>
                                <?php echo ucfirst(strtolower($estado)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <span style="border-right: 1px solid #ddd; height: 20px; margin: 0 5px;"></span>

                    <span class="has-text-weight-bold is-size-7 has-text-grey">Desde:</span>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="custom-date" required>
                    
                    <span class="has-text-weight-bold is-size-7 has-text-grey">Hasta:</span>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="custom-date" required>
                    
                    <button type="submit" class="btn-filter" title="Aplicar Filtros">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="columns is-variable is-4">
        
        <div class="column is-4-desktop is-12-tablet">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <span><i class="fas fa-chart-pie"></i> Métricas (Activas)</span>
                </div>
                <div class="dashboard-card-body">
                    <div class="chart-wrapper">
                        <canvas id="odsChartTec"></canvas>
                    </div>
                    
                    <div class="custom-legend">
                        <div class="legend-item"><div class="legend-dot" style="background:#10b981"></div> < 1 Sem</div>
                        <div class="legend-item"><div class="legend-dot" style="background:#f59e0b"></div> 1-4 Sem</div>
                        <div class="legend-item"><div class="legend-dot" style="background:#ef4444"></div> > 1 Mes</div>
                    </div>

                    <?php if ($total_ods == 0): ?>
                        <div class="notification is-light has-text-centered mt-3 is-size-7"><small>No hay órdenes activas en este rango.</small></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="column is-8-desktop is-12-tablet">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <span><i class="fas fa-list-ul"></i> Mis ODS Asignadas</span>
                    <?php if($filtroActual != 'Todos'): ?>
                        <span class="tag is-warning is-light is-rounded is-small">
                            Filtro: <?php echo $filtroActual; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="dashboard-card-body p-0">
                    <div style="width: 100%;">
                        <?php
                            $insOds = new odsController();
                            $pagina_actual = isset($url[1]) ? (int)$url[1] : 1;
                            $ruta_actual   = isset($url[0]) ? $url[0] : 'dashboardTec';
                            
                            // Pasamos el filtro al controlador
                            echo $insOds->listarOdsPersonalControlador($pagina_actual, 10, $ruta_actual, "", $filtroActual);
                        ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    <?php if ($total_ods > 0): ?>
        var ctx = document.getElementById('odsChartTec').getContext('2d');
        var curText = "<?php echo $total_ods; ?>";
        var curSub  = "Equipos";
        var curColor = "#1d4d80";
        
        // --- 4. NUEVO: Obtenemos los IDs para el clic ---
        var innerIds = <?php echo $js_in_ids; ?>;

        const centerTextPlugin = {
            id: 'centerText',
            beforeDraw: function(chart) {
                var width = chart.width, height = chart.height, ctx = chart.ctx;
                ctx.restore();
                var fontSize = (height / 110).toFixed(2);
                ctx.font = "bold " + fontSize + "em 'Segoe UI', sans-serif";
                ctx.textBaseline = "middle";
                ctx.fillStyle = curColor;
                var textX = Math.round((width - ctx.measureText(curText).width) / 2);
                var textY = height / 2;
                ctx.fillText(curText, textX, textY - 15);
                var fontSizeSmall = (height / 400).toFixed(2);
                ctx.font = fontSizeSmall + "em 'Segoe UI', sans-serif";
                ctx.fillStyle = "#666";
                var textX2 = Math.round((width - ctx.measureText(curSub).width) / 2);
                ctx.fillText(curSub, textX2, textY + 20);
                ctx.save();
            }
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo $js_in_lab; ?>, 
                datasets: [
                    { data: <?php echo $js_in_val; ?>, backgroundColor: <?php echo $js_in_col; ?>, borderWidth: 2, borderColor: '#fff', weight: 1, cutout: '30%' },
                    { data: <?php echo $js_out_val; ?>, backgroundColor: <?php echo $js_out_col; ?>, borderWidth: 4, borderColor: '#fff', weight: 2, cutout: '50%' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                
                // --- 5. NUEVO: Evento OnClick ---
                onClick: (evt, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const datasetIndex = elements[0].datasetIndex;
                        
                        // Solo dataset 0 (anillo interno)
                        if (datasetIndex === 0) {
                            const selectedId = innerIds[index];
                            if(selectedId) {
                                window.location.href = '<?php echo APP_URL; ?>odsView/' + selectedId + '/';
                            }
                        }
                    }
                },
                // ---------------------------------

                onHover: function(event, elements) {
                    if (elements && elements.length > 0) {
                        var datasetIndex = elements[0].datasetIndex;
                        var index = elements[0].index;
                        
                        // Cursor Pointer solo en anillo interno
                        event.native.target.style.cursor = (datasetIndex === 0) ? 'pointer' : 'default';

                        if(datasetIndex === 0) {
                            var labelsIn = <?php echo $js_in_lab; ?>; curText = ""; curSub  = labelsIn[index]; curColor = "#555";
                        } else {
                            var labelsOut = <?php echo $js_out_lab; ?>; var valsOut = <?php echo $js_out_val; ?>; curText = valsOut[index]; curSub  = labelsOut[index]; curColor = "#1d4d80";
                        }
                    } else {
                        event.native.target.style.cursor = 'default';
                        curText = "<?php echo $total_ods; ?>"; curSub  = "Total Equipos"; curColor = "#1d4d80";
                    }
                    this.draw();
                },
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                layout: { padding: 10 },
                cutout: '60%'
            },
            plugins: [centerTextPlugin]
        });
    <?php endif; ?>
});
</script>