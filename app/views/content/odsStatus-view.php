<?php
use app\controllers\statusController;
$ctrl = new statusController();
$estados = $ctrl->obtenerResumenStatusControlador();

// Definir qué estados van en cada tabla
$estadosAsesor = ['Recepcion', 'Presupuesto','Autorizacion', 'Entregado','Almacen', 'Seguimiento', 'Dbaja']; // Ejemplo
$estadosTecnico = ['Diagnostico', 'StandBy', 'Reparacion', 'Refacciones', 'ListoE', 'Default'];

// Filtrar los estados según corresponda
$asesores = array_filter($estados, fn($e) => in_array($e['Status'], $estadosAsesor));
$tecnicos = array_filter($estados, fn($e) => in_array($e['Status'], $estadosTecnico));

// Función para obtener el estado completo del arreglo original
function obtenerEstadoPorNombre($nombre, $estados) {
    foreach($estados as $estado) {
        if ($estado['Status'] === $nombre) return $estado;
    }
    return null;
}
?>

<!-- ESTILOS CSS -->
<style>
.estado-recepcion     { background-color: #0d476a; color: white; } 
.estado-diagnostico   { background-color: #f6b555ff; color: black; }  
.estado-presupuesto   { background-color: #116191; color: white; } 
.estado-autorizacion  { background-color: #146da5; color: white; }  
.estado-standby       { background-color: #ff8d70ff; color: black; } 
.estado-reparacion    { background-color: #3facdfff; color: black; }  
.estado-refacciones   { background-color: #9365e9ff; }  
.estado-listoe        { background-color: #49c551ff; } 
.estado-almacen       { background-color: #71bdeb; }  
.estado-entregado     { background-color: #1887cb; }  
.estado-seguimiento   { background-color: #cbe9a2; }  
.estado-default       { background-color: #c7c5c5ff; }  
</style>

<?php
function claseColorEstado($status) {
    $status = strtolower(trim($status));
    $status = strtr($status, [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n'
    ]);
    $status = str_replace(' ', '', $status);

    return match($status) {
        'recepcion'     => 'estado-recepcion',
        'diagnostico'   => 'estado-diagnostico',
        'presupuesto'   => 'estado-presupuesto',
        'autorizacion'  => 'estado-autorizacion',
        'standby'       => 'estado-standby',
        'reparacion'    => 'estado-reparacion',
        'refacciones'   => 'estado-refacciones',
        'listoe'        => 'estado-listoe',
        'almacen'       => 'estado-almacen',
        'entregado'     => 'estado-entregado',
        'seguimiento'   => 'estado-seguimiento',
        default         => 'estado-default'
    };
}
?>

<div class="container is-fluid mb-3">
    <h1 class="title">ODS por Estado</h1>

    <!-- Botón -->
    <div class="mb-4">
        <a class="button is-primary is-small js-modal-trigger" data-target="modalEstadoNuevo">
            <i class="fas fa-plus-circle"></i> &nbsp; Agregar Estado
        </a>
    </div>

    <h2 class="subtitle">ASESORES</h2>
<div class="table-container">
    <table class="table is-bordered is-fullwidth is-striped is-hoverable">
        <thead>
            <tr>
                <th>Estado</th>
                <th>Total ODS</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($estadosAsesor as $nombreEstado): ?>
                <?php $estado = obtenerEstadoPorNombre($nombreEstado, $estados); ?>
                <?php if($estado): ?>
                    <?php $clase_color = claseColorEstado($estado['Status']); ?>
                    <tr>
                        <td class="<?php echo $clase_color; ?>">
                            <a href="<?php echo APP_URL; ?>odsListStatus/<?php echo urlencode($estado['Status']); ?>/" 
                               class="has-text-<?php echo ($estado['Status'] == 'Recepcion' || $estado['Status'] == 'Presupuesto' || $estado['Status'] == 'Autorizacion') ? 'light' : 'dark'; ?> has-text-weight-semibold">
                               <?php echo strtoupper(htmlspecialchars($estado['Status'])); ?>
                            </a>
                        </td>
                        <td class="<?php echo $clase_color; ?>">
                            <span class="has-text-weight-bold"><?php echo $estado['total']; ?></span>
                        </td>
                        <td class="<?php echo $clase_color; ?>">
                            <button class="button is-success is-small btn-editar-estado"
                                    data-id="<?php echo $estado['Status']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($estado['Status']); ?>"
                                    title="Editar estado">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="button is-danger is-small btn-eliminar-estado"
                                    data-nombre="<?php echo htmlspecialchars($estado['Status']); ?>"
                                    title="Eliminar estado">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h2 class="subtitle">TÉCNICOS</h2>
<div class="table-container">
    <table class="table is-bordered is-fullwidth is-striped is-hoverable">
        <thead>
            <tr>
                <th>Estado</th>
                <th>Total ODS</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($estadosTecnico as $nombreEstado): ?>
                <?php $estado = obtenerEstadoPorNombre($nombreEstado, $estados); ?>
                <?php if($estado): ?>
                    <?php $clase_color = claseColorEstado($estado['Status']); ?>
                    <tr>
                        <td class="<?php echo $clase_color; ?>">
                            <a href="<?php echo APP_URL; ?>odsListStatus/<?php echo urlencode($estado['Status']); ?>/" 
                               class="has-text-dark has-text-weight-semibold">
                               <?php echo strtoupper(htmlspecialchars($estado['Status'])); ?>
                            </a>
                        </td>
                        <td class="<?php echo $clase_color; ?>">
                            <span class="has-text-weight-bold"><?php echo $estado['total']; ?></span>
                        </td>
                        <td class="<?php echo $clase_color; ?>">
                            <button class="button is-success is-small btn-editar-estado"
                                    data-id="<?php echo $estado['Status']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($estado['Status']); ?>"
                                    title="Editar estado">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="button is-danger is-small btn-eliminar-estado"
                                    data-nombre="<?php echo htmlspecialchars($estado['Status']); ?>"
                                    title="Eliminar estado">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<!-- Modal: Agregar Estado -->
<div id="modalEstadoNuevo" class="modal">
  <div class="modal-background" onclick="cerrarModalEstado()"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">Agregar Nuevo Estado</p>
      <button class="delete" aria-label="close" onclick="cerrarModalEstado()"></button>
    </header>
    <section class="modal-card-body">
      <form id="formNuevoEstado">
        <div class="field">
          <label class="label">Nombre del Estado</label>
          <div class="control">
            <input class="input" type="text" name="estado_nombre" required placeholder="Ej. Diagnóstico" maxlength="50">
          </div>
        </div>
      </form>
    </section>
    <footer class="modal-card-foot">
      <button class="button" onclick="cerrarModalEstado()">Cerrar</button>
    </footer>
  </div>
</div>

<!-- MODAL: Editar Estado -->
<div id="modalEditarEstado" class="modal">
  <div class="modal-background" onclick="cerrarModalEditar()"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">Editar Nombre del Estado</p>
      <button class="delete" aria-label="close" onclick="cerrarModalEditar()"></button>
    </header>
    <section class="modal-card-body">
      <form id="formEditarEstado">
        <input type="hidden" name="estado_original" id="estado_original">
        <div class="field">
          <label class="label">Nuevo nombre</label>
          <div class="control">
            <input class="input" type="text" name="estado_nuevo" id="estado_nuevo" required maxlength="50">
          </div>
        </div>
      </form>
    </section>
    <footer class="modal-card-foot">
      <button class="button is-success" onclick="guardarEdicionEstado()">Guardar</button>
      <button class="button" onclick="cerrarModalEditar()">Cancelar</button>
    </footer>
  </div>
</div>


<script>
// Abrir el modal desde el botón
document.querySelectorAll('.js-modal-trigger').forEach(trigger => {
  trigger.addEventListener('click', () => {
    const modal = document.getElementById(trigger.dataset.target);
    if (modal) modal.classList.add('is-active');
  });
});

// Función para cerrar el modal
function cerrarModalEstado() {
  document.getElementById("modalEstadoNuevo").classList.remove("is-active");
}
</script>

<!--MODAL ESTADOS -->
<script>
function cerrarModalEditar() {
  document.getElementById("modalEditarEstado").classList.remove("is-active");
}

document.querySelectorAll(".btn-editar-estado").forEach(btn => {
  btn.addEventListener("click", () => {
    const nombre = btn.dataset.nombre;
    document.getElementById("estado_original").value = nombre;
    document.getElementById("estado_nuevo").value = nombre;
    document.getElementById("modalEditarEstado").classList.add("is-active");
  });
});

function guardarEdicionEstado() {
  const form = document.getElementById("formEditarEstado");
  const formData = new FormData(form);

  fetch("<?php echo APP_URL; ?>app/ajax/estadoUpdateAjax.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      Swal.fire("¡Actualizado!", data.mensaje, "success").then(() => location.reload());
    } else {
      Swal.fire("Error", data.mensaje, "error");
    }
  })
  .catch(() => {
    Swal.fire("Error", "No se pudo editar el estado", "error");
  });
}

document.querySelectorAll(".btn-eliminar-estado").forEach(btn => {
  btn.addEventListener("click", () => {
    const estado = btn.dataset.nombre;

    Swal.fire({
      title: `¿Eliminar "${estado}"?`,
      text: "Esta acción no se puede deshacer",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar"
    }).then(result => {
      if (result.isConfirmed) {
        const formData = new FormData();
        formData.append("modulo_estado", "eliminar");
        formData.append("estado_nombre", estado);

        fetch("<?php echo APP_URL; ?>app/ajax/estadoAjax.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            Swal.fire("Eliminado", data.mensaje, "success").then(() => location.reload());
          } else {
            Swal.fire("Error", data.mensaje, "error");
          }
        })
        .catch(() => {
          Swal.fire("Error", "No se pudo eliminar", "error");
        });
      }
    });
  });
});
</script>