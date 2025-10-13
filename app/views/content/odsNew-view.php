<div class="container is-fluid mb-4">
	<h1 class="title">ODS</h1>
	<h2 class="subtitle"><i class="fas fa-user-tie fa-fw"></i> &nbsp; Nuevo ODS</h2>
</div>

<div class="container ml-2 pb-2 pt-2 mr-2">
	<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/odsAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
		<input type="hidden" name="modulo_ods" value="registrar">
		<input type="hidden" name="filtro_campo" value="Status">
		<?php
		require_once __DIR__ . "/../../models/mainModel.php";
		use app\models\mainModel;
		// 1) Traer clientes de BD
		$pdo = mainModel::conectar();
		$sql = "SELECT Idcliente, Nombre, Numero, Email, Colonia
				FROM clientes
				WHERE TRIM(Nombre) <> ''
				ORDER BY Nombre ASC";
		$clientes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
		?>
		<!-- Contenedor azul -->
		<div class="box" style="background:#91b5cb;">
			<!-- Campos de cliente -->
			<div class="columns">
				<div class="column is-half">
				<div class="control" style="position: relative;">
					<label><strong>Nombre Cliente <?php echo CAMPO_OBLIGATORIO; ?></strong></label>
					<input class="input" type="text" id="nombre_cliente" name="NombreCliente" autocomplete="off" required>
					<input type="hidden" id="id_cliente" name="Idcliente">
					<div id="sug_clientes" class="box" 
						style="position:absolute; top:100%; left:0; right:0; display:none; max-height:220px; overflow:auto; z-index:1000;">
					</div>
				</div>
				</div>
				<div class="column">
				<div class="control">
					<label><strong>Número <?php echo CAMPO_OBLIGATORIO; ?></strong></label>
					<input class="input" type="text" id="numero" name="Numero" required>
				</div>
				</div>
			</div>

			<div class="columns">
				<div class="column">
				<div class="control">
					<label><strong>Email</strong></label>
					<input class="input" type="email" id="email" name="Email" required>
				</div>
				</div>
				<div class="column">
				<div class="control" style="position: relative;">
					<label><strong>Colonia</strong></label>
					<input class="input" type="text" id="colonia" name="Colonia" required autocomplete="off">
					<div id="sug_colonia" class="box"
						style="position:absolute; top:100%; left:0; right:0; display:none; max-height:220px; overflow:auto; z-index:1000;">
					</div>
					<p class="help" id="colonia_info"></p>
				</div>
				</div>
				<div class="level-right pt-5">
					<button type="button" id="btn_guardar_cliente" class="button is-link is-small">
					<i class="fas fa-user-plus"></i>&nbsp; Guardar cliente
					</button>
				</div>
			</div>
		</div>

		<?php
			//use app\models\mainModel;
			$pdo = mainModel::conectar();
			$sql = "SELECT DISTINCT Tipo 
					FROM ods 
					WHERE Tipo IS NOT NULL AND TRIM(Tipo) <> '' 
					ORDER BY Tipo ASC";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$tipos = $stmt->fetchAll(PDO::FETCH_COLUMN);
			// eliminamos duplicados en PHP
			$tipos = array_unique($tipos);
		?>
	<div class="box" style="background:#71bdeb;">
		<div class="columns">
			<div class="column">
		    	<div class="field">
					<label><strong>Fecha de Ingreso</strong></label>
					<!-- Visible solo para el usuario -->
					<input class="input" type="text" id="fecha_registro_mostrar" readonly>

					<!-- Oculto, es el que se manda al servidor -->
					<input type="hidden" id="fecha_registro" name="Fecha">
				</div>
		  	</div>
			<div class="column">
				<div class="field">
					<label><strong>Asesor</strong></label>
					<input class="input" type="text" 
						name="NombreAsesor" 
						value="<?php echo isset($_SESSION['nombre']) ? $_SESSION['nombre'] : ''; ?>" 
						readonly>
					<input type="hidden" name="Idasesor" 
						value="<?php echo isset($_SESSION['id']) ? $_SESSION['id'] : ''; ?>">
				</div>
			</div>
		</div>
		<div class="columns">
			<div class="column">
		    	<div class="field">
					<label><strong>Sucursal</strong></label>
					<input class="input" type="text" name="Sucursal" id="sucursal"
							list="lista_sucursales" value="Centro" autocomplete="off"
							pattern="Centro|Sur" title="Elige Centro o Sur" required>
					<datalist id="lista_sucursales">
						<option value="Centro"></option>
						<option value="Sur"></option>
					</datalist>
				</div>
		  	</div>
			<div class="column">
			<div class="field">
				<label><strong>Tipo ODS</strong></label>
				<div class="control">
				<select class="input" id="tipo_ods">
					<option value="Nueva" selected>ODS Nueva</option>
					<option value="Garantia">Garantía</option>
				</select>
				</div>
			</div>
			</div>

			<!-- Campo oculto que SÍ se guarda en BD -->
			<input type="hidden" id="garantia" name="Garantia" value="0">
			<?php
				require_once __DIR__ . "/../../models/mainModel.php";
				$pdo = mainModel::conectar();
				//  Idods
				$idods = $pdo->query("
				SELECT DISTINCT Idods
				FROM ods
				WHERE Idods IS NOT NULL AND TRIM(Idods) <> ''
				ORDER BY Idods DESC
				")->fetchAll(PDO::FETCH_COLUMN);
			?>
			<div class="column">
			<div class="control" style="position: relative;">
				<label><strong>ODS Anterior</strong></label>
				<input class="input" type="text" id="odsanterior" name="Odsanterior"
					maxlength="30" autocomplete="off" readonly>
				<div id="sug_odsanterior" class="box"
					style="position:absolute; top:100%; left:0; right:0; display:none; max-height:220px; overflow:auto; z-index:1000;">
				</div>
			</div>
			</div>
		</div>
		<div class="columns">
			<div class="column">
				<div class="control">
				<label><strong>Tipo <?php echo CAMPO_OBLIGATORIO; ?></strong></label>
				<input class="input" list="lista_tipos"
						type="text" name="Tipo"
						maxlength="40" required>
				<datalist id="lista_tipos">
					<?php foreach ($tipos as $t): ?>
					<option value="<?php echo htmlspecialchars($t, ENT_QUOTES, 'UTF-8'); ?>">
					<?php endforeach; ?>
				</datalist>
				</div>
			</div>
		</div>
    

		
		<?php
			require_once __DIR__ . "/../../models/mainModel.php";
			$pdo = mainModel::conectar();
			// Listas únicas desde ODS
			$marcas = $pdo->query("
			SELECT DISTINCT TRIM(Marca) AS Marca
			FROM ods
			WHERE Marca IS NOT NULL AND TRIM(Marca) <> ''
			ORDER BY Marca ASC
			")->fetchAll(PDO::FETCH_COLUMN);
			$modelos = $pdo->query("
			SELECT DISTINCT TRIM(Modelo) AS Modelo
			FROM ods
			WHERE Modelo IS NOT NULL AND TRIM(Modelo) <> ''
			ORDER BY Modelo ASC
			")->fetchAll(PDO::FETCH_COLUMN);
		?>
		<div class="columns">
			<div class="column">
				<div class="control" style="position: relative;">
				<label><strong>Marca <?php echo CAMPO_OBLIGATORIO; ?></strong></label>
				<input class="input" type="text" id="marca" name="Marca" maxlength="40" autocomplete="off" required>
				<div id="sug_marca" class="box" style="position:absolute; top:100%; left:0; right:0; display:none; max-height:220px; overflow:auto; z-index:1000;"></div>
				</div>
			</div>
			<div class="column">
				<div class="control" style="position: relative;">
				<label><strong>Modelo <?php echo CAMPO_OBLIGATORIO; ?></strong></label>
				<input class="input" type="text" id="modelo" name="Modelo" maxlength="40" autocomplete="off" required>
				<div id="sug_modelo" class="box" style="position:absolute; top:100%; left:0; right:0; display:none; max-height:220px; overflow:auto; z-index:1000;"></div>
				</div>
			</div>
		</div>

		<div class="columns">
		  	<div class="column">
		    	<div class="control">
					<label><strong>No. Serie</strong></label>
				  	<input class="input" type="text" name="Noserie" maxlength="30" required >
				</div>
		  	</div>
		  	<div class="column">
		    	<div class="control">
					<label><strong>Color</strong></label>
				  	<input class="input" type="text" name="Color" maxlength="30" >
				</div>
		  	</div>
		</div>
		<div class="columns">
		  	<div class="column">
		    	<div class="control">
					<label><strong>Contraseña</strong></label>
				  	<input class="input" type="text" name="Contrasena" maxlength="30" >
				</div>
		  	</div>
			<div class="column">
				<div class="control">
					<label><strong>Respaldo</strong></label>
					<div class="select is-fullwidth">
					<select name="Respaldo" required>
						<option value="">-- Selecciona --</option>
						<option value="Si">Sí</option>
						<option value="No">No</option>
					</select>
					</div>
				</div>
			</div>
		</div>
		<div class="columns">
			<?php
				require_once __DIR__ . "/../../models/mainModel.php";
				$pdo = mainModel::conectar();
				$usos = $pdo->query("
				SELECT DISTINCT TRIM(Uso) AS Uso
				FROM ods
				WHERE Uso IS NOT NULL AND TRIM(Uso) <> ''
				ORDER BY Uso ASC
				")->fetchAll(PDO::FETCH_COLUMN);
			?>
		  	<div class="column">
				<div class="control" style="position: relative;">
					<label><strong>Uso</strong></label>
					<input class="input" type="text" id="uso" name="Uso" maxlength="30" autocomplete="off">
					<div id="sug_uso" class="box" 
						style="position:absolute; top:100%; left:0; right:0; display:none; 
								max-height:220px; overflow:auto; z-index:1000;">
					</div>
				</div>
			</div>
			<div class="column">
		    	<div class="control">
					<label><strong>Carpeta</strong></label>
				  	<input class="input" type="text" name="Carpeta" maxlength="30" >
				</div>
		  	</div>
		</div>
		<div class="columns">
			<div class="column is-13"> <!-- más ancho -->
				<div class="control">
					<label><strong>Problema reportado por cliente</strong></label>
					<textarea class="textarea" name="Problema" maxlength="200"></textarea>
				</div>
			</div>
		</div>

		<div class="columns">
			<div class="column is-13">
				<div class="control">
					<label><strong>Inspeccion rápida técnica</strong></label>
					<textarea class="textarea" name="Inspeccion" maxlength="200"></textarea>
				</div>
			</div>
		</div>
		
		<div class="columns">
			<div class="column">
		    	<div class="control">
					<label><strong>Accesorios</strong></label>
				  	<input class="input" type="text" name="Accesorios" maxlength="30" >
				</div>
		  	</div>
		</div>
	</div>	
	<div class="box" style="background:#7cb77b;">
		<!-- Respuesta en: Días u Horas -->
      <div class="columns">
        <div class="column">
          <div class="control">
            <label><strong>Respuesta en:</strong></label>
            <div class="select is-fullwidth mt-1">
              <select id="tipo_tiempo" name="Tipo_tiempo" required>
                <option value="" selected disabled>— Selecciona —</option>
                <option value="dias">Días</option>
                <option value="horas">Horas</option>
              </select>
            </div>
            <!-- calendario -->
            <div id="grupo_fecha" class="mt-2" style="display:none;">
              <label class="label is-small">Elige el día</label>
              <input class="input" type="date" id="fecha_respuesta" name="Fecha_respuesta">
              <p class="help">Selecciona una fecha del calendario</p>
            </div>
            <!-- reloj -->
            <div id="grupo_hora" class="mt-2" style="display:none;">
              <label class="label is-small">Elige la hora</label>
              <input class="input" type="time" id="hora_respuesta" name="Hora_respuesta" step="300">
              <p class="help">Selecciona una hora (intervalos de 5 min)</p>
            </div>
            <!-- back-->
            <input type="hidden" id="campo_tiempo_unificado" name="Tiempo" value="">
          </div>
        </div>
      </div>

      <div class="columns">
        <div class="column">
        <div class="control">
          <label><strong>Status:</strong></label>
          <span class="select">
            <select name="Status" required>
              <?php
              require_once __DIR__ . "/../../models/mainModel.php";
              $pdo = mainModel::conectar();
              $orden_fijo = ["Recepcion", "Diagnostico", "Reparacion"];
              // Buscar status existentes en BD
              $status = $pdo->query("
                  SELECT DISTINCT TRIM(Status) AS Status
                  FROM ods
                  WHERE TRIM(Status) <> ''
              ")->fetchAll(PDO::FETCH_COLUMN);
              // Recorremos en el orden que quieres
              foreach ($orden_fijo as $st) {
                  if (in_array($st, $status)) {
                      $selected = ($st === "Recepcion") ? "selected" : "";
                      echo "<option value='$st' $selected>$st</option>";
                  }
              }
              ?>
            </select>
          </span>
        </div>
        </div>

        <?php
// Suponiendo que ya tienes una conexión a la base de datos
// Establece la conexión (puedes modificarlo según tu configuración)
$pdo = new PDO('mysql:host=localhost;dbname=sistema', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Consulta para obtener los técnicos
$query = "SELECT Idasesor, Nombre FROM personal
          ORDER BY FIELD(Nombre, 'ALEJANDRO', 'FERNANDO', 'ARTURO M') DESC, Nombre ASC";

$stmt = $pdo->prepare($query);
$stmt->execute();

// Guardar los resultados
$tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="column">
    <div class="control">
        <label><strong>Técnico</strong></label>
        <div class="select is-fullwidth mt-1">
            <select id="id_tecnico" name="IdTecnico" required>
                <option value="" selected disabled>— Selecciona un técnico —</option>
                <?php
                // Genera las opciones de técnicos dinámicamente
                foreach ($tecnicos as $tecnico) {
                    echo "<option value='" . htmlspecialchars($tecnico['Idasesor'], ENT_QUOTES, 'UTF-8') . "'>"
                         . htmlspecialchars($tecnico['Nombre'], ENT_QUOTES, 'UTF-8') . "</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>
      </div>
	</div>	

  <div class="box" style="background:#cbe9a2;">
  <div class="columns">
    <div class="column is-5">
      <div class="control">
        <label><strong>Servicio</strong></label>
        <input class="input" id="servicio" name="ServicioBusqueda" list="serviciosList" placeholder="Buscar servicio" autocomplete="off">
        <datalist id="serviciosList"></datalist>
      </div>
    </div>
    <div class="column is-3">
      <div class="control">
        <label><strong>Costo</strong></label>
        <input class="input" id="costo" placeholder="Costo" autocomplete="off">
      </div>
    </div>
    <div class="column is-2">
      <div class="control">
        <label><strong>Cantidad</strong></label>
        <input class="input" id="cantidad_serv" type="number" value="1" min="1" step="1">
      </div>
    </div>
    <div class="column is-2">
      <label>&nbsp;</label>
      <button type="button" id="btn_agregar_serv" class="button is-success is-fullwidth">
        <i class="fas fa-plus"></i>&nbsp; Agregar
      </button>
    </div>
  </div>

  <div class="table-container">
    <table class="table is-fullwidth is-striped is-hoverable" id="tabla_servicios">
      <thead>
        <tr>
          <th>#</th>
          <th>Servicio</th>
          <th>Costo</th>
          <th>Cant.</th>
          <th>Subtotal</th>
          <th></th>
        </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
        <tr>
          <th colspan="4" class="has-text-right">Subtotal</th>
          <th id="subtotal_general">$0.00</th>
          <th></th>
        </tr>
        <tr>
          <th colspan="4" class="has-text-right">
            Descuento (<span id="lbl_desc">0%</span>)
          </th>
          <th id="monto_descuento">-$0.00</th>
          <th></th>
        </tr>
        <tr>
          <th colspan="4" class="has-text-right">A cuenta</th>
          <th id="monto_acuenta">-$0.00</th>
          <th></th>
        </tr>
        <tr>
          <th colspan="4" class="has-text-right">Total</th>
          <th id="total_pagar">$0.00</th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>

  <!-- Fila de Descuento % y A cuenta -->
  <div class="columns">
    <div class="column is-3">
      <div class="control">
        <label><strong>Descuento %</strong></label>
        <input class="input" id="descuento" name="Descuento" type="number" min="0" max="100" step="0.01" value="0">
      </div>
    </div>
    <div class="column is-3">
      <div class="control">
        <label><strong>A cuenta</strong></label>
        <input class="input" id="acuenta" name="Cuenta" type="number" min="0" step="0.01" value="0">
      </div>
    </div>
  </div>

  <!-- JSON de servicios que viaja al backend -->
  <input type="hidden" id="servicios_json" name="Servicios">
  <!-- Total calculado (por si lo quieres en el POST) -->
  <input type="hidden" id="total_hidden" name="Total">
</div>

    <!-- Botón para generar ODS -->
    <button type="submit" class="button is-info is-rounded" id="btn-generar">
      <i class="far fa-save"></i>&nbsp; Generar ODS
    </button>


		<p class="has-text-centered pt-6">
            <small>Los campos marcados con <?php echo CAMPO_OBLIGATORIO; ?> son obligatorios</small>
        </p>
	</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const BASE = "<?php echo APP_URL; ?>"; // ej: /VENTAS3/
  const form = document.querySelector('form.FormularioAjax');
  const btn  = document.getElementById('btn-generar');

  let previewWin = null;

  btn.addEventListener('click', () => {
    // abrir antes de la petición para evitar bloqueos
    previewWin = window.open('about:blank', '_blank');
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    try {
      const res  = await fetch(form.action, { method:'POST', body:fd, credentials:'include' });
      const text = await res.text();
      if (!res.ok) throw new Error(text.slice(0,400));
      const json = JSON.parse(text);

      if (json.success && json.id) {
        const urlPrint = `${BASE}odsPrint.php?id=${encodeURIComponent(json.id)}&auto=1`;
        if (previewWin && !previewWin.closed) {
          previewWin.location.replace(urlPrint);
        } else {
          window.open(urlPrint, '_blank'); // por si el usuario canceló el popup previo
        }
      } else {
        throw new Error(json.error || 'No se pudo guardar');
      }
    } catch (err) {
      console.error(err);
      if (previewWin && !previewWin.closed) previewWin.close();
      alert('Error al generar la ODS.');
    }
  });
});
</script>


<!-- buscar servicio -->
<script>
(function(){
  const BASE = "<?php echo APP_URL; ?>";

  const inpServicio = document.getElementById('servicio');
  const dlServicios = document.getElementById('serviciosList');
  const inpCosto    = document.getElementById('costo');
  const inpCant     = document.getElementById('cantidad_serv');
  const btnAgregar  = document.getElementById('btn_agregar_serv');

  const tbody       = document.querySelector('#tabla_servicios tbody');
  const lblSubtotal = document.getElementById('subtotal_general');
  const lblDescPct  = document.getElementById('lbl_desc');
  const lblDesc     = document.getElementById('monto_descuento');
  const lblACuenta  = document.getElementById('monto_acuenta');
  const lblTotal    = document.getElementById('total_pagar');

  const inpDesc     = document.getElementById('descuento'); // %
  const inpACuenta  = document.getElementById('acuenta');    // $
  const hiddenJson  = document.getElementById('servicios_json');
  const hiddenTotal = document.getElementById('total_hidden');

  let cacheBusqueda = []; // {id, servicio, costo}
  let items = [];         // {id, servicio, costo, cantidad}

  // fetch robusto
  async function fetchJSON(url, opts){
    const res  = await fetch(url, { credentials:'include', ...(opts||{}) });
    const text = await res.text();
    if (!res.ok){ console.error(text); throw new Error('HTTP '+res.status); }
    try { return JSON.parse(text); } catch(e){ console.error(text); throw e; }
  }

  // Buscar servicios
  async function buscarServicios(q){
    if (!q || q.trim().length < 2){ dlServicios.innerHTML = ''; cacheBusqueda = []; return; }
    const url = `${BASE}app/ajax/buscaServicioYReparacion.php?termino=${encodeURIComponent(q.trim())}`;
    let data = [];
    try { data = await fetchJSON(url); } catch{ dlServicios.innerHTML=''; return; }

    cacheBusqueda = (Array.isArray(data) ? data : []).map(x => ({
      id:       x.id ?? x.Idser ?? '',
      servicio: x.servicio ?? x.Descripcion ?? x.nombre ?? '',
      costo:    x.costo ?? x.Costo ?? x.precio ?? ''
    })).filter(x => x.servicio);

    dlServicios.innerHTML = cacheBusqueda.map(s =>
      `<option value="${s.servicio}" data-id="${s.id}" data-costo="${s.costo}">${s.servicio}</option>`
    ).join('');
  }

  inpServicio.addEventListener('input', e => buscarServicios(e.target.value));
  inpServicio.addEventListener('change', e => {
    const val = e.target.value;
    const opt = Array.from(dlServicios.options).find(o => o.value === val);
    let costo = opt?.dataset?.costo;
    if (!costo){
      const hit = cacheBusqueda.find(s => s.servicio === val);
      costo = hit?.costo ?? '';
    }
    inpCosto.value = costo || '';
  });

  const money = n => isNaN(n) ? '$0.00' : '$' + Number(n).toFixed(2);

  function renderTabla(){
    // Subtotal de líneas
    const subtotal = items.reduce((acc, it) =>
      acc + (Number(it.costo||0) * Number(it.cantidad||1)), 0);

    // Descuento %
    let descPct = Number(inpDesc.value || 0);
    if (isNaN(descPct) || descPct < 0) descPct = 0;
    if (descPct > 100) descPct = 100;

    const descuento = subtotal * (descPct / 100);

    // A cuenta $
    let acuenta = Number((inpACuenta.value || '0').toString().replace(/,/g,''));
    if (isNaN(acuenta) || acuenta < 0) acuenta = 0;

    // Total
    let total = subtotal - descuento - acuenta;
    if (total < 0) total = 0;

    // Pintar totales
    lblSubtotal.textContent = money(subtotal);
    lblDescPct.textContent  = `${descPct.toFixed(2)}%`;
    lblDesc.textContent     = '-'+money(descuento);
    lblACuenta.textContent  = '-'+money(acuenta);
    lblTotal.textContent    = money(total);

    // Tabla
    tbody.innerHTML = items.map((it, i) => {
      const sub = Number(it.costo||0) * Number(it.cantidad||1);
      return `
      <tr>
        <td>${i+1}</td>
        <td>${it.servicio}</td>
        <td>${money(it.costo)}</td>
        <td>
          <input type="number" min="1" step="1" value="${it.cantidad}" class="input is-small" 
                 data-idx="${i}" data-role="qty">
        </td>
        <td>${money(sub)}</td>
        <td>
          <button type="button" class="button is-small is-danger" data-idx="${i}" data-role="del">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>`;
    }).join('');

    // Hidden para backend
    hiddenJson.value  = JSON.stringify(items);
    hiddenTotal.value = Number(total).toFixed(2);
  }

  // Cambios en cantidad / eliminar filas
  tbody.addEventListener('input', e => {
    if (e.target.dataset.role === 'qty'){
      const idx = Number(e.target.dataset.idx);
      const val = Math.max(1, Number(e.target.value || 1));
      items[idx].cantidad = val;
      renderTabla();
    }
  });
  tbody.addEventListener('click', e => {
    const btn = e.target.closest('[data-role="del"]');
    if (btn){
      const idx = Number(btn.dataset.idx);
      items.splice(idx, 1);
      renderTabla();
    }
  });

  // Agregar servicio
  btnAgregar.addEventListener('click', () => {
    const nombre = (inpServicio.value || '').trim();
    const costo  = Number((inpCosto.value || '').toString().replace(/,/g,''));
    const cant   = Math.max(1, Number(inpCant.value || 1));

    if (!nombre){ alert('Elige un servicio.'); return; }
    if (isNaN(costo) || costo < 0){ alert('Costo inválido.'); return; }

    const opt = Array.from(dlServicios.options).find(o => o.value === nombre);
    const id  = opt?.dataset?.id || (cacheBusqueda.find(s => s.servicio === nombre)?.id ?? '');

    const i = items.findIndex(x => x.servicio === nombre && String(x.costo) === String(costo));
    if (i >= 0) items[i].cantidad += cant;
    else items.push({ id, servicio: nombre, costo, cantidad: cant });

    inpServicio.value = '';
    inpCosto.value    = '';
    inpCant.value     = 1;
    dlServicios.innerHTML = '';

    renderTabla();
  });

  // Recalcular cuando cambien Descuento % o A cuenta
  inpDesc.addEventListener('input', renderTabla);
  inpACuenta.addEventListener('input', renderTabla);

  // Inicial
  renderTabla();
})();
</script>
<!--para clientes-->
<script>
// 2) Inyectamos clientes en JS (sin fetch)
const CLIENTES = <?php echo json_encode($clientes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
(function(){
  const inpNombre  = document.getElementById('nombre_cliente');
  const inpId      = document.getElementById('id_cliente');
  const inpNumero  = document.getElementById('numero');
  const inpEmail   = document.getElementById('email');
  const inpColonia = document.getElementById('colonia');
  const box        = document.getElementById('sug_clientes');

  function normaliza(s){ return (s || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
  function renderSugerencias(items){
    box.innerHTML = '';
    if (!items.length) { box.style.display = 'none'; return; }
    items.slice(0, 15).forEach(cli => {
      const row = document.createElement('div');
      row.className = 'p-2';
      row.style.cursor = 'pointer';
      row.style.borderBottom = '1px solid #eee';
      row.innerHTML = `<div><strong>${cli.Nombre}</strong></div>
                       <small>${cli.Numero ?? ''} ${cli.Email ? ' · ' + cli.Email : ''}</small>`;
      row.onclick = () => {
        inpNombre.value  = cli.Nombre;
        inpId.value      = cli.Idcliente;
        inpNumero.value  = cli.Numero || '';
        inpEmail.value   = cli.Email || '';
        inpColonia.value = cli.Colonia || '';
        box.style.display = 'none';
      };
      box.appendChild(row);
    });
    box.style.display = 'block';
  }
  inpNombre.addEventListener('input', () => {
    const term = normaliza(inpNombre.value.trim());
    inpId.value = ''; // resetea selección
    if (term.length < 2) { box.style.display = 'none'; return; }
    // 3) Filtrado en vivo en el cliente (sin llamadas a servidor)
    const resultados = CLIENTES.filter(c => normaliza(c.Nombre).includes(term));
    renderSugerencias(resultados);
  });
  document.addEventListener('click', (e) => {
    if (!box.contains(e.target) && e.target !== inpNombre) box.style.display = 'none';
  });
  // 4) Al salir, si coincide exactamente un nombre, autollenar
  inpNombre.addEventListener('blur', () => {
    if (inpId.value) return;
    const term = normaliza(inpNombre.value.trim());
    if (!term) return;
    const exacto = CLIENTES.find(c => normaliza(c.Nombre) === term);
    if (exacto) {
      inpId.value      = exacto.Idcliente;
      inpNumero.value  = exacto.Numero || '';
      inpEmail.value   = exacto.Email || '';
      inpColonia.value = exacto.Colonia || '';
    }
  });
})();
</script>

<script>
(function(){
  const BASE      = "<?php echo APP_URL; ?>";
  const btn       = document.getElementById('btn_guardar_cliente');

  const inpId     = document.getElementById('id_cliente');
  const inpNombre = document.getElementById('nombre_cliente');
  const inpNumero = document.getElementById('numero');
  const inpEmail  = document.getElementById('email');
  const inpColonia= document.getElementById('colonia');

  inpNombre.addEventListener('input', () => {
  inpNombre.value = inpNombre.value.toUpperCase();
});

  inpColonia.addEventListener('input', () => {
  inpColonia.value = inpColonia.value.toUpperCase();
});

  function validarCampos(){
    const nombre  = inpNombre.value.trim();
    const numero  = inpNumero.value.trim();
    const email   = inpEmail.value.trim();
    const colonia = inpColonia.value.trim();

    if(!nombre){ alert('Escribe el nombre del cliente.'); return null; }
    if(!numero){ alert('Escribe el número del cliente.'); return null; }
    if(!email){  alert('Escribe el email del cliente.');  return null; }
    if(!colonia){alert('Escribe la colonia.');            return null; }

    return { nombre, numero, email, colonia };
  }

  btn.addEventListener('click', async () => {
  if (inpId.value) {
    Swal.fire({
      icon: 'info',
      title: 'Cliente ya seleccionado',
      text: 'Este formulario ya tiene un cliente asignado.'
    });
    return;
  }

  const datos = validarCampos();
  if(!datos) return;

  try {
  btn.disabled = true;

  const res = await fetch(`${BASE}app/ajax/guardarClienteAjax.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({
      Nombre:  datos.nombre,
      Numero:  datos.numero,
      Email:   datos.email,
      Colonia: datos.colonia
    })
  });

  const text = await res.text();
  if (!res.ok) {
    console.error('Respuesta no OK:', res.status, text);
    Swal.fire({ icon:'error', title:`Error ${res.status}`, text:'No se pudo contactar con el servidor.' });
    return;
  }

  let json;
  try { json = JSON.parse(text); }
  catch(e){
    console.error('No es JSON:', text);
    Swal.fire({ icon:'error', title:'Respuesta inválida', text:'El servidor no devolvió JSON.' });
    return;
  }

  if (json.success){
    inpId.value = json.Idcliente;
    // opcional: bloquear campos después de guardar
    inpNumero.readOnly = inpEmail.readOnly = inpColonia.readOnly = true;

    // Añade al arreglo CLIENTES para que aparezca en el autocomplete
    if (Array.isArray(CLIENTES)) {
      CLIENTES.push({
        Idcliente: json.Idcliente,
        Nombre: inpNombre.value,
        Numero: inpNumero.value,
        Email: inpEmail.value,
        Colonia: inpColonia.value
      });
    }

    Swal.fire({
      icon: 'success',
      title: 'Cliente guardado',
      text: `Se registró correctamente con ID ${json.Idcliente}`,
      confirmButtonColor: '#3085d6',
      confirmButtonText: 'OK'
    });
  } else {
    Swal.fire({ icon:'error', title:'Error al guardar', text: json.error || 'No se pudo guardar.' });
  }
} catch (e) {
  console.error(e);
  Swal.fire({ icon:'error', title:'Error de red', text:'No se pudo contactar con el servidor.' });
} finally {
  btn.disabled = false;
}
});
})();
</script>

<!--para domicilio en clientes -->
<script>
(function(){
  // Normaliza texto (minúsculas, sin acentos)
  const norm = s => (s || '').toString().toLowerCase()
                   .normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  // 1) Construimos catálogo único y conteo por colonia desde CLIENTES
  const coloniasAll = CLIENTES
    .map(c => (c.Colonia || '').trim())
    .filter(Boolean);
  // Únicas (mantén como se muestran en BD)
  const COLONIAS = Array.from(new Set(coloniasAll))
    .sort((a,b)=>a.localeCompare(b,'es'));
  // Conteo por colonia (normalizado)
  const COLONIA_COUNT = coloniasAll.reduce((acc, col) => {
    const k = norm(col);
    acc[k] = (acc[k] || 0) + 1;
    return acc;
  }, {});
  // 2) Elementos del DOM
  const inColonia = document.getElementById('colonia');
  const box       = document.getElementById('sug_colonia');
  const info      = document.getElementById('colonia_info');
  // Mayúsculas en vivo (como ya querías)
  inColonia.addEventListener('input', () => {
    const pos = inColonia.selectionStart;           // conserva caret
    inColonia.value = inColonia.value.toUpperCase();
    inColonia.setSelectionRange(pos, pos);
  });
  function setInfoByValue(value){
    const k = norm(value);
    const count = COLONIA_COUNT[k] || 0;
    if (value && count > 0) {
      info.textContent = `Hay ${count} cliente(s) registrados en “${value}”.`;
    } else if (value) {
      info.textContent = `No hay clientes registrados en “${value}”.`;
    } else {
      info.textContent = '';
    }
  }
  function renderLista(items){
  box.innerHTML = '';
  if (!items || !items.length){ box.style.display='none'; return; }

  items.slice(0, 15).forEach(txt => {
    const row = document.createElement('div');
    row.className = 'p-2';
    row.style.cursor = 'pointer';
    row.style.borderBottom = '1px solid #eee';
    row.textContent = txt; // o `${txt} (${count})` si muestras conteo

    // Usar mousedown evita el blur antes de tiempo
    row.addEventListener('mousedown', (ev) => {
      ev.preventDefault();   // evita perder el foco antes de asignar
      ev.stopPropagation();  // no dispares el document click
      inColonia.value = txt.toUpperCase();
      setInfoByValue(inColonia.value); // si usas el helper de info
      box.style.display = 'none';
      inColonia.dispatchEvent(new Event('change'));
    });

    box.appendChild(row);
  });
  box.style.zIndex = '2000';
  box.style.display = 'block';
}
  function mostrarDefault(){
    renderLista([...COLONIAS]);
  }
  inColonia.addEventListener('focus', () => {
    if (!inColonia.value.trim()){
      mostrarDefault();
    } else {
      setInfoByValue(inColonia.value.trim());
    }
  });
  // Filtrado mientras se escribe 
  let t = null;
  inColonia.addEventListener('input', () => {
    const q = norm(inColonia.value.trim());
    setInfoByValue(inColonia.value.trim());
    if (t) clearTimeout(t);
    if (!q){ mostrarDefault(); return; }
    t = setTimeout(() => {
      const res = COLONIAS.filter(c => norm(c).includes(q));
      renderLista(res);
    }, 120);
  });
  // Al salir, muestra info del match exacto (si existe)
  inColonia.addEventListener('blur', () => {
    setTimeout(() => { box.style.display='none'; }, 150);
    setInfoByValue(inColonia.value.trim());
  });
  // Cierra al hacer click fuera
  document.addEventListener('click', (e) => {
  if (!box.contains(e.target) && e.target !== inColonia) {
    box.style.display='none';
  }
});
  // Esc para cerrar, no es tan necesario
  inColonia.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape'){ box.style.display='none'; }
  });
})();
</script>

<!--para marcas y modelos-->
<script>
const MARCAS  = <?php echo json_encode($marcas,  JSON_UNESCAPED_UNICODE); ?>;
const MODELOS = <?php echo json_encode($modelos, JSON_UNESCAPED_UNICODE); ?>;
const norm = s => (s || '').toString().toLowerCase()
  .normalize('NFD').replace(/[\u0300-\u036f]/g,'');
function renderLista(box, items, onPick){
  box.innerHTML = '';
  if (!items || !items.length){ box.style.display='none'; return; }
  items.slice(0, 15).forEach(txt => {
    const row = document.createElement('div');
    row.className = 'p-2';
    row.style.cursor = 'pointer';
    row.style.borderBottom = '1px solid #eee';
    row.textContent = txt;
    row.onclick = () => { onPick(txt); box.style.display='none'; };
    box.appendChild(row);
  });
  box.style.display = 'block';
}

(function(){
  // MARCA (independiente)
  const inMarca = document.getElementById('marca');
  const boxMarca = document.getElementById('sug_marca');
  let t1 = null;

  inMarca.addEventListener('input', () => {
    const q = norm(inMarca.value.trim());
    if (t1) clearTimeout(t1);
    if (q.length < 1){ boxMarca.style.display='none'; return; }
    t1 = setTimeout(() => {
      const res = Array.from(new Set(MARCAS))
        .filter(m => norm(m).includes(q))
        .sort((a,b)=>a.localeCompare(b,'es'));
      renderLista(boxMarca, res, pick => { inMarca.value = pick; });
    }, 150);
  });
  // MODELO (independiente)
  const inModelo = document.getElementById('modelo');
  const boxModelo = document.getElementById('sug_modelo');
  let t2 = null;
  inModelo.addEventListener('input', () => {
    const q = norm(inModelo.value.trim());
    if (t2) clearTimeout(t2);
    if (q.length < 1){ boxModelo.style.display='none'; return; }
    t2 = setTimeout(() => {
      const res = Array.from(new Set(MODELOS))
        .filter(mo => norm(mo).includes(q))
        .sort((a,b)=>a.localeCompare(b,'es'));
      renderLista(boxModelo, res, pick => { inModelo.value = pick; });
    }, 150);
  });
  // Ocultar listas al hacer click fuera
  document.addEventListener('click', (e) => {
    if (!boxMarca.contains(e.target) && e.target !== inMarca) boxMarca.style.display='none';
    if (!boxModelo.contains(e.target) && e.target !== inModelo) boxModelo.style.display='none';
  });
})();
</script>

<!--para ODS Anterior-->
<script>
const IDODS = <?php echo json_encode($idods, JSON_UNESCAPED_UNICODE); ?>;

(function(){
  const inOds  = document.getElementById('odsanterior');
  const box    = document.getElementById('sug_odsanterior');
  let timer = null;
  function renderLista(items){
    box.innerHTML = '';
    if (!items || !items.length){ box.style.display='none'; return; }
    items.slice(0, 15).forEach(val => {
      const row = document.createElement('div');
      row.className = 'p-2';
      row.style.cursor = 'pointer';
      row.style.borderBottom = '1px solid #eee';
      row.textContent = val;
      row.onclick = () => { 
        inOds.value = val; 
        box.style.display = 'none'; 
      };
      box.appendChild(row);
    });
    box.style.display = 'block';
  }

  inOds.addEventListener('input', () => {
    const q = inOds.value.trim();
    if (timer) clearTimeout(timer);
    if (q.length < 1){ box.style.display='none'; return; }

    timer = setTimeout(() => {
      const res = IDODS.filter(v => v.toString().includes(q));
      renderLista(res);
    }, 150);
  });

  document.addEventListener('click', (e) => {
    if (!box.contains(e.target) && e.target !== inOds) box.style.display='none';
  });
})();
</script>

<!-- para Uso -->
<script>
const USOS = <?php echo json_encode($usos, JSON_UNESCAPED_UNICODE); ?>;
(function(){
  const inUso = document.getElementById('uso');
  const box   = document.getElementById('sug_uso');
  let timer = null;
  const norm = s => (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  function renderLista(items){
    box.innerHTML = '';
    if (!items || !items.length){ box.style.display='none'; return; }
    items.slice(0, 5).forEach(val => {
      const row = document.createElement('div');
      row.className = 'p-2';
      row.style.cursor = 'pointer';
      row.style.borderBottom = '1px solid #eee';
      row.textContent = val;
      row.onclick = () => {
        inUso.value = val;
        box.style.display = 'none';
        inUso.dispatchEvent(new Event('change')); // por si quieres reaccionar en otros scripts
      };
      box.appendChild(row);
    });
    box.style.display = 'block';
  }
  // Mostrar lista default (primeros 5) al enfocar/click si no hay texto
  function mostrarListaDefault(){
    const lista = Array.from(new Set(USOS)) // únicos
      .sort((a,b)=>a.localeCompare(b,'es'));
    renderLista(lista);
  }
  inUso.addEventListener('focus', () => {
    if (!inUso.value.trim()){
      mostrarListaDefault();
    }
  });
  inUso.addEventListener('click', () => {
    if (!inUso.value.trim()){
      mostrarListaDefault();
    }
  });
  // Filtrar mientras escribe
  inUso.addEventListener('input', () => {
    const q = norm(inUso.value.trim());
    if (timer) clearTimeout(timer);
    // si está vacío, muestra default
    if (!q){
      mostrarListaDefault();
      return;
    }
    timer = setTimeout(() => {
      const res = USOS.filter(v => norm(v).includes(q))
        .sort((a,b)=>a.localeCompare(b,'es'));
      renderLista(res);
    }, 150);
  });
  // Ocultar al hacer click fuera
  document.addEventListener('click', (e) => {
    if (!box.contains(e.target) && e.target !== inUso) {
      box.style.display='none';
    }
  });
  // (Opcional) Navegación con teclado: Esc para cerrar
  inUso.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape'){ box.style.display='none'; }
  });
})();
</script>

<!--Fecha de respuesta -->
<script>
document.addEventListener("DOMContentLoaded", function() {
  const tipoTiempo = document.getElementById('tipo_tiempo');
  const grupoFecha = document.getElementById('grupo_fecha');
  const grupoHora  = document.getElementById('grupo_hora');
  const campoTiempoUnificado = document.getElementById('campo_tiempo_unificado');

  // Función para mostrar/ocultar según selección
  function mostrarElementos() {
    const tipoSeleccionado = tipoTiempo.value;

    // Si se selecciona "Días", mostramos el calendario y ocultamos el reloj
    if (tipoSeleccionado === 'dias') {
      grupoFecha.style.display = 'block';
      grupoHora.style.display = 'none';
      campoTiempoUnificado.value = `dias:${document.getElementById('fecha_respuesta').value}`;
    }
    // Si se selecciona "Horas", mostramos el reloj y ocultamos el calendario
    else if (tipoSeleccionado === 'horas') {
      grupoFecha.style.display = 'none';
      grupoHora.style.display = 'block';
      campoTiempoUnificado.value = `horas:${document.getElementById('hora_respuesta').value}`;
    } else {
      grupoFecha.style.display = 'none';
      grupoHora.style.display = 'none';
      campoTiempoUnificado.value = '';
    }
  }

  // Al cambiar la opción de "Respuesta en"
  tipoTiempo.addEventListener('change', mostrarElementos);

  // Al cargar la página, ejecutar la función para el valor por defecto
  mostrarElementos();
});
</script>

<!-- Fecha Registro ODS -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Obtén la fecha de hoy
    const hoy = new Date();
    const dd = String(hoy.getDate()).padStart(2, '0'); 
    const mm = String(hoy.getMonth() + 1).padStart(2, '0'); // Los meses empiezan desde 0
    const yyyy = hoy.getFullYear();

    // Mostrar la fecha en formato dd/mm/yyyy en el campo visible
    document.getElementById('fecha_registro_mostrar').value = `${dd}/${mm}/${yyyy}`;

    // Guardar la fecha en formato yyyy-mm-dd en el campo oculto para el servidor
    document.getElementById('fecha_registro').value = `${yyyy}-${mm}-${dd}`;
});
</script>


<script>
document.addEventListener("DOMContentLoaded", () => {
  const selTipo = document.getElementById('tipo_ods');
  const inpG    = document.getElementById('garantia');   // hidden para BD
  const inpAnt  = document.getElementById('odsanterior');

  function aplicar(){
    if (selTipo.value === 'Garantia') {
      inpG.value = '1';
      inpAnt.removeAttribute('readonly');
    } else {
      inpG.value = '0';
      inpAnt.setAttribute('readonly', true);
      inpAnt.value = ''; // opcional: limpiar
    }
  }
  selTipo.addEventListener('change', aplicar);
  aplicar(); // estado inicial
});
</script>

<!-- para abrir la hoja de ods -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const BASE = "<?php echo APP_URL; ?>";
    const form = document.querySelector('form.FormularioAjax');
    const btnGenerar = document.getElementById('btn-generar');
    const btnImprimir = document.getElementById('btn-imprimir');
    let pendingPrintWin = null;

    // Evento para manejar el envío del formulario (generar la ODS)
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        try {
            const res = await fetch(form.action, { method: 'POST', body: fd, credentials: 'include' });
            const text = await res.text();
            
            if (!res.ok) throw new Error(text.slice(0,400));
            let json;
            try { json = JSON.parse(text); } catch { throw new Error('Respuesta no JSON: '+text.slice(0,400)); }

            if (json.success && json.id) {
                // Cuando se guarda la ODS, redirige a la página de impresión
                document.dispatchEvent(new CustomEvent('ods:guardada', { detail: { id: json.id } }));
            } else {
                throw new Error(json.error || 'No se pudo guardar');
            }
        } catch (err) {
            console.error(err);
        }
    });

    // Cuando la ODS se ha generado, abrimos la página y habilitamos el botón de impresión
    document.addEventListener('ods:guardada', (ev) => {
        const { id } = ev.detail || {};
        if (!id) return;

        // Redirige a la página de ODS generada
        const urlPrint = `${BASE}ods/odsPrint.php?id=${encodeURIComponent(id)}&auto=1`;

        if (pendingPrintWin && !pendingPrintWin.closed) {
            pendingPrintWin.location.replace(urlPrint);
        } else {
            pendingPrintWin = window.open(urlPrint, '_blank');
        }

        // Mostrar botón de impresión después de que la ODS se ha generado
        btnImprimir.style.display = 'block';

        // Cuando se hace clic en Imprimir
        btnImprimir.addEventListener('click', () => {
            if (pendingPrintWin && !pendingPrintWin.closed) {
                pendingPrintWin.print(); // Imprime la ODS en la nueva pestaña
            } else {
                window.open(urlPrint, '_blank').print();
            }
        });
    });
});

</script>
