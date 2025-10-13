<?php
use app\controllers\almacenController;
$ctrl = new almacenController();
$estados = $ctrl->obtenerResumenEstadoRefaccionControlador(); 
?>

<!-- ESTILOS CSS -->
    <style>
        .estado-requerido           { background-color: #0d476a; color: #000; }
        .estado-autorizado          { background-color: #116191; color: #000; }
        .estado-compra              { background-color: #1887cb; color: #000; }
        .estado-recibido            { background-color: #1b94df; color: #000; }
        .estado-entregadoaltecnico { background-color: #1da1f2; color: #000; }
        .estado-pruebas             { background-color: #a6cff4; color: #000; }
        .estado-default             { background-color: #552626ff; color: #000; }
    </style>

<?php
function claseColorEstado($estado) {
    $estado = strtolower(trim($estado));
    $estado = strtr($estado, [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n'
    ]);
    $estado = str_replace([' ', '-'], '', $estado);

    return match($estado) {
        'requerido'           => 'estado-requerido',
        'autorizado'          => 'estado-autorizado',
        'compra'              => 'estado-compra',
        'recibido'            => 'estado-recibido',
        'entregadoaltecnico'  => 'estado-entregadoaltecnico',
        'pruebas'             => 'estado-pruebas',
        default               => 'estado-default'
    };
}
?>

<div class="container is-fluid mb-3">
    <h1 class="title">Refacciones por Estado</h1>

    <div class="mb-4">
        <a class="button is-primary is-small js-modal-trigger" data-target="modalEstadoNuevo">
            <i class="fas fa-plus-circle"></i> &nbsp; Agregar Estado
        </a>
    </div>

    <div class="table-container">
        <table class="table is-bordered is-fullwidth is-striped is-hoverable">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Total Refacciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($estados as $estado): ?>
                    <?php $clase_color = claseColorEstado($estado['estado']); ?>
                    <tr>
                        <td class="<?php echo $clase_color; ?>">
                            <a href="<?php echo APP_URL; ?>refaccionListEstado/<?php echo urlencode($estado['estado']); ?>/" 
                               class="has-text-dark has-text-weight-semibold">
                               <?php echo strtoupper(htmlspecialchars($estado['estado'])); ?>
                            </a>
                        </td>
                        <td class="<?php echo $clase_color; ?>">
                            <span class="has-text-weight-bold"><?php echo $estado['total']; ?></span>
                        </td>
                        <td class="<?php echo $clase_color; ?>">
                            <button class="button is-success is-small btn-editar-estado"
                                data-id="<?php echo $estado['estado']; ?>"
                                data-nombre="<?php echo htmlspecialchars($estado['estado']); ?>"
                                title="Editar estado">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button class="button is-danger is-small btn-eliminar-estado"
                                data-nombre="<?php echo htmlspecialchars($estado['estado']); ?>"
                                title="Eliminar estado">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
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
            <input class="input" type="text" name="estado_nombre" required placeholder="Ej. Requerido" maxlength="50">
          </div>
        </div>
      </form>
    </section>
    <footer class="modal-card-foot">
      <button class="button" onclick="cerrarModalEstado()">Cerrar</button>
    </footer>
  </div>
</div>

<!-- Modal: Editar Estado -->
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

<!-- JS: Mismos scripts adaptados -->
<script>
function cerrarModalEstado() {
  document.getElementById("modalEstadoNuevo").classList.remove("is-active");
}
function cerrarModalEditar() {
  document.getElementById("modalEditarEstado").classList.remove("is-active");
}

document.querySelectorAll('.js-modal-trigger').forEach(trigger => {
  trigger.addEventListener('click', () => {
    const modal = document.getElementById(trigger.dataset.target);
    if (modal) modal.classList.add('is-active');
  });
});

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

  fetch("<?php echo APP_URL; ?>app/ajax/refaccionEstadoUpdateAjax.php", {
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

        fetch("<?php echo APP_URL; ?>app/ajax/refaccionEstadoAjax.php", {
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
