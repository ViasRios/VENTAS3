<div class="container is-fluid mb-0 mt-1">
    <h1 class="title">ODS</h1>
    <h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de ODS</h2>
</div>

<div class="container pb-19 pt-10">
    <div class="form-rest mb-11 mt-12"></div>
    <!-- Contenedor con scroll horizontal -->
    <div style="overflow-x: auto; max-width: 100%;">
        <table class="table is-bordered is-striped is-narrow is-hoverable">
            <?php
                use app\controllers\odsController;
                $insOds = new odsController();
                echo $insOds->listarOdsPersonalControlador($url[1], 15, $url[0], "");
            ?>
        </table>
    </div>
</div>

<!-- 🔽 MODAL DE NOTIFICACIÓN -->
<div class="modal" id="modalNotificacion">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">Enviar notificación</p>
      <button class="delete" aria-label="close"></button>
    </header>
    <section class="modal-card-body">
      <form id="formNotificacion">
        <input type="hidden" name="Idods" id="modal_Idods">
        <input type="hidden" name="nuevo_status" id="modal_nuevo_status">

        <p><strong>Selecciona los usuarios a notificar:</strong></p>
        <div id="lista_usuarios_checkboxes">
          <!-- Aquí se cargarán dinámicamente los usuarios -->
        </div>
      </form>
    </section>
    <footer class="modal-card-foot">
      <button class="button is-success" id="btnEnviarNotificacion">Enviar</button>
      <button class="button" id="btnCancelarNotificacion">Cancelar</button>
    </footer>
  </div>
</div>