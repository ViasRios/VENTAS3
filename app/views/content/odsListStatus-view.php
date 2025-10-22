<?php
use app\models\mainModel;
$status = isset($url[1]) ? mb_convert_encoding(urldecode($url[1]), 'UTF-8', 'UTF-8') : '';
#$status = isset($url[1]) ? urldecode($url[1]) : '';
if (empty($status)) {
    echo "<p class='has-text-centered has-text-danger'>No se ha especificado un estado válido.</p>";
    return;
}

$status = htmlspecialchars(trim($status));
$sql = "
    SELECT 
        ods.Idods, 
        clientes.Nombre AS NombreCliente, 
        personal.Nombre AS NombreTecnico,
        ods.Fecha,
        ods.Status,
        ods.Tipo,
        ods.Tiempo,
        ods.fechaSeguimiento
    FROM ods
    INNER JOIN clientes ON ods.Idcliente = clientes.Idcliente
    INNER JOIN personal ON ods.Idasesor = personal.Idasesor
    WHERE ods.Status = '$status'
    ORDER BY ods.Fecha DESC
";

$consulta = mainModel::ejecutarConsulta($sql);
$ods_lista = $consulta->fetchAll();
?>

<div class="container is-fluid mb-3">
    <h1 class="title">ODS con estado: "<?php echo htmlspecialchars($status); ?>"</h1>
    <?php if ($status == "Seguimiento"): ?>
    <!-- Botón (icono calendario) para mostrar/ocultar -->
    <div class="field is-grouped is-justify-content-flex-end mb-2">
        <button type="button" class="button is-small is-info" id="calendarToggleBtn" onclick="toggleCalendar()">
        <span class="icon"><i class="far fa-calendar-alt"></i></span>
        <span>Calendario</span>
        </button>
    </div>
    <!-- Wrapper con transición; inicia oculto -->
    <div id="calendarWrapper" class="calendar-collapsible is-collapsed">
        <div id="calendar"></div>
    </div>
    <?php endif; ?>
</div>

<div class="container pb-1 pt-15">
    <div class="buttons mb-1">
        <button type="button" class="button is-small" id="btnTodos">
            <span class="icon"><i class="fas fa-list"></i></span><span>Todos</span>
        </button>
        <button type="button" class="button is-small is-danger" id="btnVencidas">
            <span class="icon"><i class="fas fa-calendar-times"></i></span><span>Vencidas</span>
        </button>
        <button type="button" class="button is-small is-warning" id="btnPendientes">
            <span class="icon"><i class="fas fa-hourglass-half"></i></span><span>Pendientes</span>
        </button>
        <button type="button" class="button is-small is-success" id="btnEntregado">
            <span class="icon"><i class="fas fa-check-circle"></i></span><span>Entregado</span>
        </button>
    </div>

    <?php if (count($ods_lista) > 0): ?>
        <div class="table-container" style="max-height: 500px; overflow-y: auto;">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <!-- FullCalendar CSS -->
            <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.2.0/fullcalendar.min.css" rel="stylesheet">
            <!-- <div class="table-container"> -->
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th>Ver</th> 
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Calendario</th>
                        <th>Tipo</th>
                        <th>Asesor</th>
                      <!--  <th>Status</th> -->
                      <!--  <th>Tiempo</th> -->
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ods_lista as $od): ?>
                        <tr>
                            <td>
                                <a href="<?php echo APP_URL; ?>odsView/<?php echo $od['Idods']; ?>/" class="button is-small is-link" title="Ver ODS">
                                <i class="fas fa-eye"></i>
                                </a>
                            </td>
                            <td><?php echo $od['Idods']; ?></td>
                            <td><?php echo $od['NombreCliente']; ?></td>
                            <td><?php echo $od['Fecha']; ?></td>
                            <?php
                                // Elegir la fecha a mostrar: primero la de seguimiento si existe
                                $valorFechaSeg = '';
                                if (!empty($od['fechaSeguimiento'])) {
                                    $ts = strtotime($od['fechaSeguimiento']);
                                    if ($ts !== false) { $valorFechaSeg = date('Y-m-d', $ts); }
                                } else if (!empty($od['Fecha'])) {
                                    // fallback a la fecha original (si quieres dejarlo vacío, quita este else if)
                                    $ts = strtotime($od['Fecha']);
                                    if ($ts !== false) { $valorFechaSeg = date('Y-m-d', $ts); }
                                }
                                ?>
                            <td>
                                <div class="field has-addons">
                                    <div class="control">
                                    <input
                                        type="date"
                                        class="input is-small seg-fecha"
                                        data-ods-id="<?= (int)$od['Idods']; ?>"
                                        value="<?= htmlspecialchars($valorFechaSeg, ENT_QUOTES, 'UTF-8'); ?>" 
                                        title="Fecha de seguimiento"
                                    >
                                    </div>
                                    <div class="control">
                                    <button
                                        type="button"
                                        class="button is-small is-success"
                                        title="Guardar fecha de seguimiento"
                                        onclick="guardarSeguimientoFecha(<?= (int)$od['Idods']; ?>, this)"
                                    >
                                        <span class="icon"><i class="far fa-calendar-check"></i></span>
                                    </button>
                                    </div>
                                </div>
                                <p class="help is-success is-hidden" id="segHelp<?= (int)$od['Idods']; ?>">Guardado ✓</p>
                                <p class="help is-danger is-hidden" id="segErr<?= (int)$od['Idods']; ?>">Error al guardar</p>
                            </td>
                            <td><?php echo $od['Tipo']; ?></td>
                            <td><?php echo $od['NombreTecnico']; ?></td>
                        <!--    <td><?php echo $od['Status']; ?></td> -->
                        <!--    <td>
                                <?php
                                    $tiempoODS = new DateTime($od['Tiempo']);
                                    $ahora = new DateTime();
                                    $diferencia = $ahora->diff($tiempoODS);

                                    if ($diferencia->y > 0) {
                                        echo $diferencia->y . ' año(s)';
                                    } elseif ($diferencia->m > 0) {
                                        echo $diferencia->m . ' mes(es)';
                                    } elseif ($diferencia->d >= 7) {
                                        echo floor($diferencia->d / 7) . ' semana(s)';
                                    } elseif ($diferencia->d > 0) {
                                        echo $diferencia->d . ' día(s)';
                                    } elseif ($diferencia->h > 0) {
                                        echo $diferencia->h . ' hora(s)';
                                    } elseif ($diferencia->i > 0) {
                                        echo $diferencia->i . ' minuto(s)';
                                    } else {
                                        echo 'Justo ahora';
                                    }
                                ?>
                            </td> -->
                            <td>
                                <a href="<?php echo APP_URL; ?>odsUpdate/<?php echo $od['Idods']; ?>/" class="button is-small is-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo APP_URL; ?>odsDelete/<?php echo $od['Idods']; ?>/" class="button is-small is-danger" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </a>

                                <!-- Botones de acción según el estado -->  
                                <?php if ($od['Status'] === 'ListoE'): ?>
                                    <button 
                                        class="entregaBtn button is-small is-success" 
                                        data-ods-id="<?php echo $od['Idods']; ?>" 
                                        title="Enviar mensaje por WhatsApp"
                                        onclick="sendWhatsAppMessage('<?php echo $od['Idods']; ?>')"
                                    >
                                        LEntrega WhatsApp
                                    </button>
                                    
                                <?php elseif ($od['Status'] === 'Seguimiento'): ?> 
                                    <br>
                                    <button 
                                        class="seguimientoBtn button is-small is-info" 
                                        data-ods-id="<?php echo $od['Idods']; ?>" 
                                        title="Enviar correo de seguimiento"
                                    >
                                        <i class="fas fa-envelope"></i> <!-- Icono de correo -->
                                    </button>
                                    
                                    <button 
                                        class="seguimientoBtn button is-small is-info" 
                                        data-ods-id="<?php echo $od['Idods']; ?>" 
                                        title="Enviar mensaje de seguimiento por WhatsApp"
                                        onclick="sendWhatsAppMessage('<?php echo $od['Idods']; ?>')"
                                    >
                                        <i class="fab fa-whatsapp"></i> <!-- Icono de WhatsApp -->

                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="has-text-centered has-text-grey">No hay ODS con el estado "<?php echo htmlspecialchars($status); ?>".</p>
    <?php endif; ?>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- FullCalendar JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.2.0/fullcalendar.min.js"></script>

<script>
    function sendWhatsAppMessage(orderId) {
        // Número desde base de datos
        var phoneNumber = "+527713635486"; // Número de ejemplo
        var message = encodeURIComponent("Tu orden con ID: " + orderId + " está lista para entrega.");
        // Usar el enlace de WhatsApp
        var url = "https://wa.me/" + phoneNumber + "?text=" + message;
        // Abrir el enlace en una nueva pestaña o ventana
        window.open(url, "_blank");
    }
</script>

<script>
$(document).ready(function() {
    // Variable global para almacenar los seguimientos
    var seguimientosData = [];
    
    // Primero cargar los datos de seguimientos
    function cargarSeguimientos() {
        return $.get('<?php echo APP_URL; ?>getSeguimientos.php?tipo=conteo_mensual')
            .done(function(data) {
                if (data && !data.error) {
                    seguimientosData = data;
                }
            });
    }

    // Inicializar el calendario después de cargar los datos
    cargarSeguimientos().then(function() {
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            defaultView: 'month',
            editable: false,
            eventLimit: true,
            
            // Cargar eventos desde getSeguimientos.php
            events: {
                url: '<?php echo APP_URL; ?>getSeguimientos.php',
                type: 'GET',
                error: function() {
                    console.error('Error al cargar los seguimientos');
                }
            },
            
            // AGREGAR: Personalizar la renderización de los días
            dayRender: function(date, cell) {
                var año = date.year();
                var mes = date.month() + 1;
                var dia = date.date();
                
                // Verificar si este día tiene seguimientos
                var tieneSeguimientos = verificarSeguimientosEnFecha(año, mes, dia);
                
                if (tieneSeguimientos) {
                    // Agregar indicador visual al día
                    cell.addClass('dia-con-seguimiento');
                    
                    // Agregar contador pequeño
                    var contador = $('<div class="contador-dia">' + tieneSeguimientos + '</div>');
                    cell.append(contador);
                    
                    // Tooltip
                    cell.attr('title', tieneSeguimientos + ' seguimiento(s)');
                }
                
                // Verificar si es el primer día del mes para agregar contador mensual
                if (dia === 1) {
                    var seguimientosMes = contarSeguimientosEnMes(año, mes);
                    if (seguimientosMes > 0) {
                        var contadorMes = $('<div class="contador-mes">' + seguimientosMes + '</div>');
                        cell.append(contadorMes);
                    }
                }
            },
            
            // AGREGAR: Personalizar el encabezado del mes
            viewRender: function(view) {
                if (view.name === 'month') {
                    var año = view.intervalStart.year();
                    var mes = view.intervalStart.month() + 1;
                    var seguimientosMes = contarSeguimientosEnMes(año, mes);
                    
                    // Agregar contador al título del mes
                    if (seguimientosMes > 0) {
                        $('.fc-center h2').append(
                            '<span class="contador-titulo">' + seguimientosMes + ' seguimiento(s)</span>'
                        );
                    }
                }
            },
            
            // Cuando se hace clic en un evento
            eventClick: function(calEvent, jsEvent, view) {
                var odsId = calEvent.id;
                window.open('<?php echo APP_URL; ?>odsView/' + odsId + '/', '_blank');
            },
            
            // Personalizar la apariencia de eventos
            eventRender: function(event, element) {
                element.attr('title', 'Haz clic para ver detalles de la ODS');
                element.css({
                    'font-size': '12px',
                    'padding': '3px 6px',
                    'border-radius': '4px',
                    'cursor': 'pointer',
                    'background-color': '#ff6b6b',
                    'border-color': '#ff5252'
                });
            },
            
            // Textos en español
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana',
                day: 'Día'
            },
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
        });
    });

    // AGREGAR: Función para verificar seguimientos en una fecha específica
    function verificarSeguimientosEnFecha(año, mes, dia) {
        var fechaStr = año + '-' + (mes < 10 ? '0' + mes : mes) + '-' + (dia < 10 ? '0' + dia : dia);
        var count = 0;
        
        // Buscar en los eventos cargados
        var eventos = $('#calendar').fullCalendar('clientEvents');
        eventos.forEach(function(evento) {
            if (evento.start.format('YYYY-MM-DD') === fechaStr) {
                count++;
            }
        });
        
        return count;
    }
    
    // AGREGAR: Función para contar seguimientos en un mes
    function contarSeguimientosEnMes(año, mes) {
        var count = 0;
        var eventos = $('#calendar').fullCalendar('clientEvents');
        
        eventos.forEach(function(evento) {
            var eventAño = evento.start.year();
            var eventMes = evento.start.month() + 1;
            
            if (eventAño === año && eventMes === mes) {
                count++;
            }
        });
        
        return count;
    }

    // Botón para refrescar calendario
    $('#refreshCalendar').click(function() {
        // Recargar datos primero
        cargarSeguimientos().then(function() {
            $('#calendar').fullCalendar('refetchEvents');
        });
    });
    
    // AGREGAR: Selector de año integrado en el header
    function agregarSelectorAnio() {
        var añoActual = new Date().getFullYear();
        var selectorHTML = `
        <div class="field has-addons" style="display: flex; justify-content: flex-end; margin-left: auto; margin-top: -25px;">
            <div class="control">
                <button class="button is-small" style="margin-right: -8px;" id="prevYearBtn">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>
            <div class="control">
                <input type="number" class="input is-small" id="yearInput" value="${añoActual}" style="width: 80px; text-align: center;">
            </div>
            <div class="control">
                <button class="button is-small" style="margin-left: -8px; margin-right: 20px;" id="nextYearBtn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        `;
        
        $('.fc-center').after(selectorHTML);
        
        // Eventos del selector de año
        $('#prevYearBtn').click(function() {
            var año = parseInt($('#yearInput').val()) - 1;
            $('#yearInput').val(año);
            $('#calendar').fullCalendar('gotoDate', año + '-01-01');
        });
        
        $('#nextYearBtn').click(function() {
            var año = parseInt($('#yearInput').val()) + 1;
            $('#yearInput').val(año);
            $('#calendar').fullCalendar('gotoDate', año + '-01-01');
        });
        
        $('#yearInput').change(function() {
            var año = parseInt($(this).val());
            if (año >= 2000 && año <= 2100) {
                $('#calendar').fullCalendar('gotoDate', año + '-01-01');
            } else {
                $(this).val(añoActual);
            }
        });
    }
    
    // Agregar selector de año después de inicializar
    setTimeout(agregarSelectorAnio, 100);
});
</script>
<script>
function toggleCalendar() {
  var wrap = document.getElementById('calendarWrapper');
  if (!wrap) return;

  // toggle clase
  wrap.classList.toggle('is-collapsed');

  // si se acaba de mostrar => forzar render de FullCalendar
  if (!wrap.classList.contains('is-collapsed')) {
    // pequeño delay para que termine la animación y tenga ancho/alto correctos
    setTimeout(function() {
      if (typeof jQuery !== 'undefined' && $('#calendar').length && $('#calendar').fullCalendar) {
        $('#calendar').fullCalendar('render');
      }
    }, 250);
  }
}
</script>

<style>
/* Indicador para días con seguimientos */
.dia-con-seguimiento {
    background-color: #fff5f5 !important;
    border: 1px solid #ff6b6b !important;
    position: relative;
}

.dia-con-seguimiento .fc-day-number {
    color: #d63031 !important;
    font-weight: bold;
}

/* Contador pequeño en días */
.contador-dia {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #ff6b6b;
    color: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    font-size: 9px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

/* Contador en el título del mes */
.contador-titulo {
    background: #ff6b6b;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    margin-left: 10px;
    font-weight: normal;
}


/* Mejorar visibilidad de eventos */
.fc-event {
    background-color: #ff6b6b !important;
    border-color: #ff5252 !important;
}

.fc-event:hover {
    background-color: #ff5252 !important;
}

/* Selector de año integrado */
.fc-center {
    display: flex;
    align-items: center;
}

.fc-center h2 {
    margin-right: 10px;
    margin-bottom: 0;
}

/* Contenedor colapsable del calendario con transición suave */
.calendar-collapsible {
  overflow: hidden;
  transition: max-height 0.25s ease;
  max-height: 0;           /* colapsado */
}

.calendar-collapsible:not(.is-collapsed) {
  max-height: 1200px;      /* expandido (suficiente para month/week/day) */
}

</style>

<script>
async function guardarSeguimientoFecha(idOds, btnEl) {
  try {
    const cell = btnEl.closest('td');
    const input = cell.querySelector('.seg-fecha');
    const fecha = input ? input.value : '';

    const okMsg  = document.getElementById('segHelp' + idOds);
    const errMsg = document.getElementById('segErr' + idOds);
    if (okMsg) okMsg.classList.add('is-hidden');
    if (errMsg) errMsg.classList.add('is-hidden');

    if (!fecha) {
      if (errMsg) { errMsg.textContent = 'Selecciona una fecha.'; errMsg.classList.remove('is-hidden'); }
      return;
    }

    btnEl.classList.add('is-loading');

    const form = new FormData();
    form.append('odsId', idOds);        
    form.append('fechaSeguimiento', fecha);  

    const res  = await fetch('<?= APP_URL; ?>guardarSeguimiento.php', {
      method: 'POST',
      body: form,
      credentials: 'same-origin'
    });

    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch(e) { data = { success:false, message:'Respuesta no válida: ' + text }; }

    const ok  = !!(data && (data.ok === true || data.success === true));
    const msg = (data && (data.msg || data.message)) || (ok ? 'Guardado ✓' : 'No se pudo guardar.');

    if (ok) {
      if (okMsg) { okMsg.textContent = msg; okMsg.classList.remove('is-hidden'); }
      // Actualiza visualmente la columna "Status" a Seguimiento (opcional)
      const fila = btnEl.closest('tr');
      if (fila) {
        const celdas = fila.querySelectorAll('td');
        const celdaStatus = celdas[7]; // Ver, ID, Cliente, Fecha, Calendario, Tipo, Técnico, Status, ...
        if (celdaStatus) celdaStatus.textContent = 'Seguimiento';
      }
      // Refresca FullCalendar si existe
      if (typeof jQuery !== 'undefined' && $('#calendar').length && $('#calendar').fullCalendar) {
        $('#calendar').fullCalendar('refetchEvents');
      }
    } else {
      if (errMsg) { errMsg.textContent = msg; errMsg.classList.remove('is-hidden'); }
      console.error('Guardar seguimiento (server):', data);
    }
  } catch (e) {
    const errMsg = document.getElementById('segErr' + idOds);
    if (errMsg) { errMsg.textContent = 'Error de red/servidor.'; errMsg.classList.remove('is-hidden'); }
    console.error(e);
  } finally {
    btnEl.classList.remove('is-loading');
  }
}
</script>


<script>
// util: parsea 'YYYY-MM-DD' a Date local (00:00)
function parseISODate(val) {
  if (!val) return null;
  const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(val);
  if (!m) return null;
  const y = parseInt(m[1],10), mo = parseInt(m[2],10)-1, d = parseInt(m[3],10);
  return new Date(y, mo, d);
}
function todayAtMidnight() {
  const t = new Date();
  return new Date(t.getFullYear(), t.getMonth(), t.getDate());
}

// indices de columnas (0-based):
// 0 Ver, 1 ID, 2 Cliente, 3 Fecha, 4 Calendario(input), 5 Tipo, 6 Técnico, 7 Tiempo, 8 Acciones
const IDX_CAL = 4;
const IDX_STATUS = 7;

function filtrarTabla(modo) {
  const tbody = document.querySelector('table.table tbody');
  if (!tbody) return;
  const rows = tbody.querySelectorAll('tr');
  const hoy = todayAtMidnight();

  rows.forEach(tr => {
    const tds = tr.children;
    const tdCal = tds[IDX_CAL];
    const tdStatus = tds[IDX_STATUS];

    const status = tdStatus ? (tdStatus.textContent || '').trim() : '';
    const input  = tdCal ? tdCal.querySelector('input.seg-fecha') : null;
    const fecha  = parseISODate(input ? input.value : '');

    let show = true;

    if (modo === 'vencidas') {
      // No entregadas y con fecha pasada
      show = (status !== 'Entregado') && (fecha instanceof Date) && (fecha < hoy);
    } else if (modo === 'pendientes') {
      // Todo excepto Entregado y Vencidas
      show = (status !== 'Entregado') && ( !fecha || fecha >= hoy );
    } else if (modo === 'entregado') {
      show = (status === 'Entregado');
    } else {
      show = true; // 'todos'
    }

    tr.style.display = show ? '' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', function() {
  const btnTodos = document.getElementById('btnTodos');
  const btnVencidas = document.getElementById('btnVencidas');
  const btnPendientes = document.getElementById('btnPendientes');
  const btnEntregado = document.getElementById('btnEntregado');

  if (btnTodos)      btnTodos.addEventListener('click', () => filtrarTabla('todos'));
  if (btnVencidas)   btnVencidas.addEventListener('click', () => filtrarTabla('vencidas'));
  if (btnPendientes) btnPendientes.addEventListener('click', () => filtrarTabla('pendientes'));
  if (btnEntregado)  btnEntregado.addEventListener('click', () => filtrarTabla('entregado'));
});
</script>
<style>
.buttons .button.is-small.is-active {
  box-shadow: inset 0 0 0 1px rgba(10,10,10,.2);
}
</style>
<script>
// marcar botón activo (opcional)
document.addEventListener('DOMContentLoaded', function(){
  const group = document.querySelector('.buttons');
  if (!group) return;
  group.addEventListener('click', (e) => {
    const btn = e.target.closest('.button');
    if (!btn) return;
    group.querySelectorAll('.button').forEach(b => b.classList.remove('is-active'));
    btn.classList.add('is-active');
  });
});
</script>

<script>
document.addEventListener('click', async (ev) => {
  const btn = ev.target.closest('.seguimientoBtn');
  if (!btn) return;

  const odsId = btn.getAttribute('data-ods-id');
  if (!odsId) return;

  // Evita doble envío
  if (btn.classList.contains('is-loading') || btn.disabled) return;

  btn.classList.add('is-loading');

  try {
    // Construye el payload (agrega tu token CSRF si usas uno)
    const fd = new FormData();
    fd.append('odsId', odsId);

    const res = await fetch('<?= APP_URL; ?>enviarCorreoSeguimiento.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    });

    const raw = await res.text();
    let data;
    try { data = JSON.parse(raw); } catch(e) { data = { ok:false, msg:'Respuesta no válida: ' + raw }; }

    if (data.ok) {
      // Cambia el icono a "sobre abierto" y desactiva el botón
      btn.innerHTML = '<i class="fas fa-envelope-open"></i> Enviado';
      btn.classList.remove('is-info');
      btn.classList.add('is-light');
      btn.disabled = true;
      btn.title = 'Correo enviado';

      // Marca visualmente la fila y actualiza estado a "Entregado"
      const tr = btn.closest('tr');
      if (tr) {
        tr.dataset.status = 'Entregado'; // útil para filtros
        // Si tu tabla NO muestra la columna Status, al menos marca algo:
        tr.classList.add('has-background-success-light');

        // Si estás en la vista filtrada por "Seguimiento", quita la fila
        // (este listado llega por URL con $status="Seguimiento")
        <?php if ($status === 'Seguimiento'): ?>
        setTimeout(() => { 
          tr.style.transition = 'opacity .25s';
          tr.style.opacity = '0';
          setTimeout(() => tr.remove(), 250);
        }, 350);
        <?php endif; ?>
      }

      // Mensaje rápido (opcional)
      if (window.bulmaToast) {
        bulmaToast.toast({ message: 'Correo enviado y ODS marcada como Entregado ✓', type: 'is-success' });
      } else {
        console.log('Correo enviado y ODS marcada como Entregado ✓');
      }
    } else {
      alert(data.msg || 'No se pudo enviar el correo.');
      console.error(data);
    }
  } catch (err) {
    console.error(err);
    alert('Error de red/servidor al enviar el correo.');
  } finally {
    btn.classList.remove('is-loading');
  }
});
</script>
