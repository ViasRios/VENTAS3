<?php
    use app\controllers\odsController;
    if(!isset($insOds)) { 
        $insOds = new odsController();
    }
    // 1. FILTROS
    if(isset($_POST['filtro_status_ods'])){
        $_SESSION['filtro_status_me'] = $_POST['filtro_status_ods'];
    }
    $filtroActual = $_SESSION['filtro_status_me'] ?? 'Todos';
    // 2. OBTENER DATOS Y DEFINIR ESTADOS SEGÚN ROL
    $idUsuario = $_SESSION['id'] ?? 0;
    $puestoUsuario = $_SESSION['Puesto'] ?? ''; // Obtenemos el rol
    // Usamos la función híbrida para traer los conteos de la BD
    $datosBD = $insOds->obtenerEstadisticasPersonal($idUsuario);
    // --- LÓGICA DE ESTADOS DIFERENCIADOS ---
    if ($puestoUsuario == "TECNICO") {
        // A. Estados para TÉCNICOS (Solo lo operativo)
        $ordenLogico = [
            'DIAGNOSTICO', 'REPARACION', 'REFACCIONES', 
            'LISTOE'
        ];
    } else {
        // B. Estados para ASESORES / JEFES (Ciclo completo)
        $ordenLogico = [
            'RECEPCION', 'DIAGNOSTICO', 'PRESUPUESTO', 'AUTORIZACION',
            'REPARACION', 'REFACCIONES', 'LISTOE', 'ENTREGADO'
        ];
    }
    // Mapa de colores global
    $mapaColores = [
        'RECEPCION' => '#ea580c', 'DIAGNOSTICO' => '#ca8a04', 'PRESUPUESTO' => '#a855f7',
        'AUTORIZACION' => '#16a34a', 'REPARACION' => '#0ea5e9', 'REFACCIONES' => '#7c3aed',
        'STANDBY' => '#f43f5e', 'LISTOE' => '#059669', 'LENTREGAR' => '#059669', 
        'ENTREGADO' => '#facc15', 'CANCELADO' => '#374151'
    ];
    // Procesar los datos reales de la BD
    $conteoReal = [];
    if($datosBD && is_array($datosBD)){
        foreach($datosBD as $fila) {
            $stNormalizado = mb_strtoupper($fila['Status']);
            $conteoReal[$stNormalizado] = $fila['Cantidad'];
        }
    }
    // Construir los arrays para Chart.js basados en el orden definido arriba
    $labels = []; $data = []; $colores = [];
    foreach($ordenLogico as $estado) {
        // Solo agregamos la barra si queremos mostrar ceros O si tiene datos
        // (Aquí mostramos todas las del $ordenLogico aunque estén en 0 para mantener la estructura)
        $labels[]  = $estado;
        $data[]    = $conteoReal[$estado] ?? 0;
        $colores[] = $mapaColores[$estado] ?? '#cccccc';
    }
?>

<style>
    /* Estilos Aesthetic para el filtro y la caja */
    .box-filter { border-top: 4px solid #1d4d80; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .select.is-info select { border-color: #1d4d80; color: #1d4d80; font-weight: 600; }
    .select.is-info:not(:hover)::after { border-color: #1d4d80; }

    /* --- ESTILOS DE LA NOTIFICACIÓN TOAST --- */
.notification-toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: white;
    padding: 15px 25px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 15px;
    z-index: 9999;
    transform: translateX(120%); /* Oculto a la derecha */
    transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    border-left: 5px solid #1d4d80; /* Color por defecto */
    font-family: 'Poppins', sans-serif;
    font-size: 0.9rem;
    color: #4a4a4a;
}

.notification-toast.show {
    transform: translateX(0); /* Mostrar */
}

.notification-toast.exito { border-left-color: #10b981; } /* Verde */
.notification-toast.error { border-left-color: #ef4444; } /* Rojo */

.notification-toast i { font-size: 1.2rem; }
.notification-toast.exito i { color: #10b981; }
.notification-toast.error i { color: #ef4444; }
</style>

<div class="container is-fluid mb-2 mt-4">
    <h1 class="title" style="color: #1d4d80;">MIS ODS (Historial)</h1>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="container is-fluid mb-2">
    <div class="columns is-desktop">
        <div class="column is-9">
            <div class="box" style="height: 100%; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 class="title is-6 has-text-centered has-text-grey-light">Estadísticas Globales</h3>
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="graficoOdsTecnico"></canvas>
                </div>
            </div>
        </div>
        <div class="column is-3">
            <div class="box box-filter" style="height: 100%; border-radius: 15px;">  
                <h4 class="title is-6 mb-3" style="color: #1d4d80;"><i class="fas fa-filter"></i> Filtros</h4>
                <form action="" method="POST">
                    <div class="field">
                        <label class="label is-small">Estado a visualizar:</label>
                        <div class="control has-icons-left">
                            <div class="select is-fullwidth is-info is-rounded">
                                <select name="filtro_status_ods" onchange="this.form.submit()">
                                    <option value="Todos" <?php echo ($filtroActual == 'Todos') ? 'selected' : ''; ?>>
                                        Ver Todo
                                    </option>
                                    <?php foreach($ordenLogico as $opcion): ?>
                                        <option value="<?php echo $opcion; ?>" <?php echo ($filtroActual == $opcion) ? 'selected' : ''; ?>>
                                            <?php echo $opcion; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="icon is-small is-left">
                                <i class="fas fa-list-ul"></i>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('graficoOdsTecnico').getContext('2d');
    new Chart(ctx, {
        type: 'bar', 
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Cantidad',
                data: <?php echo json_encode($data); ?>,
                backgroundColor: <?php echo json_encode($colores); ?>,
                borderRadius: 5,
                borderSkipped: false, // Barras flotantes bonitas
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { 
                y: { beginAtZero: true, ticks: { stepSize: 1, color: '#888' }, grid: { borderDash: [5, 5], color: '#f0f0f0' } },
                x: { grid: { display: false }, ticks: { color: '#555', font: { size: 10 } } } 
            },
            plugins: { legend: { display: false } }
        }
    });
    /* --- 2. FUNCIÓN PARA MOSTRAR NOTIFICACIÓN BONITA --- */
    function mostrarNotificacion(mensaje, tipo) {
        // Tipos: 'exito', 'error'
        const icono = tipo === 'exito' ? 'fas fa-check-circle' : 'fas fa-times-circle';
        
        // Crear elemento HTML
        const notif = document.createElement('div');
        notif.className = `notification-toast ${tipo}`;
        notif.innerHTML = `
            <i class="${icono}"></i>
            <span>${mensaje}</span>
        `;

        // Agregar al cuerpo
        document.body.appendChild(notif);

        // Activar animación (timeout pequeño para que el navegador procese el CSS)
        setTimeout(() => {
            notif.classList.add('show');
        }, 10);

        // Quitar después de 3 segundos
        setTimeout(() => {
            notif.classList.remove('show');
            // Eliminar del DOM después de que termine la animación de salida
            setTimeout(() => {
                notif.remove();
            }, 500);
        }, 3000);
    }

    /* --- 3. ACTUALIZAR STATUS SIN ALERT --- */
    function actualizarStatusDirecto(idOds, nuevoStatus) {
        let data = new FormData();
        data.append('modulo_ods', 'cambiar_status');
        data.append('Idods', idOds);
        data.append('Status', nuevoStatus);

        fetch('<?php echo APP_URL; ?>app/ajax/odsAjax.php', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(respuesta => {
            if(respuesta.success) {
                // ÉXITO: Usamos la notificación bonita
                mostrarNotificacion("Estado actualizado a: " + nuevoStatus, "exito");
                
                // Opcional: Recargar después de un momento para ver colores actualizados
                setTimeout(() => location.reload(), 100); 
            } else {
                // ERROR
                mostrarNotificacion("Error: " + respuesta.msg, "error");
                // Si hay error, revertimos la selección recargando
                setTimeout(() => location.reload(), 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion("Error de conexión al servidor", "error");
        });
    }
</script>

<div class="container pb-1 pt-0">
    <div class="form-rest mb-1 mt-0"></div>
    <div style="overflow-x: auto; max-width: 100%;">
        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
            <?php
                // CAMBIO IMPORTANTE: Llamamos a la nueva función GENERAL
                echo $insOds->listarOdsGeneralMeControlador(
                    $url[1],      
                    15,           
                    $url[0],      
                    "",           
                    $filtroActual 
                );
            ?>
        </table>
    </div>
</div>