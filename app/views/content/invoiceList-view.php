<?php
use app\controllers\invoiceController;

$h = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

// Evitar notices por índices faltantes
$pagina = (isset($url[1]) && is_numeric($url[1]) && (int)$url[1] > 0) ? (int)$url[1] : 1;
$slug   = isset($url[0]) && $url[0] !== '' ? $url[0] : 'invoiceList';

// Instancia única del controlador
$insInvoice = new invoiceController();

// Si hay término guardado en sesión para este slug, úsalo como filtro
$filtro = isset($_SESSION[$slug]) ? $_SESSION[$slug] : "";
?>
<!-- Encabezado -->
<div class="container is-fluid mb-1">
  <div class="level">
    <div class="level-left">
      <div>
        <h1 class="title">Facturas</h1>
        <h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de facturas</h2>
      </div>
    </div>
  </div>
</div>

<!-- Buscador -->
<div class="container pb-2 pt-2">
  <?php if (!isset($_SESSION[$slug]) || empty($_SESSION[$slug])): ?>
    <form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off">
      <input type="hidden" name="modulo_buscador" value="buscar">
      <input type="hidden" name="modulo_url" value="<?php echo $slug; ?>">
      <div class="field is-grouped">
        <p class="control is-expanded">
          <input class="input is-rounded" type="text" name="txt_buscador"
                 placeholder="¿Qué estás buscando?"
                 pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,30}" maxlength="30" required>
        </p>
        <p class="control">
          <button class="button is-info" type="submit">
            <i class="fas fa-search"></i>&nbsp; Buscar
          </button>
        </p>
      </div>
    </form>
  <?php else: ?>
    
    <form class="has-text-centered mt-1 mb-1 FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off">
      <input type="hidden" name="modulo_buscador" value="eliminar">
      <input type="hidden" name="modulo_url" value="<?php echo $slug; ?>">
      <!--<p><i class="fas fa-search fa-fw"></i> &nbsp; Estás buscando <strong>“<?php echo $h($_SESSION[$slug] ?? ''); ?>”</strong></p>-->
      <br>
      <button type="submit" class="button is-danger is-rounded">
        <i class="fas fa-trash-restore"></i>&nbsp; Eliminar búsqueda
      </button>
    </form>
  <?php endif; ?>
</div>

<!-- Listado (usa el filtro si existe) -->
<div class="container pb-1 pt-1">
  <?php
    echo $insInvoice->listarFacturaControlador($pagina, 15, $slug, $filtro);
  ?>
</div>
