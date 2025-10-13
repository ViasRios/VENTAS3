<?php
use app\models\mainModel;
$db = mainModel::conectar();
// con esto se captura el IdODS desde la URL
$IdODS = isset($url[1]) ? intval($url[1]) : 0;

// Obtener la refacción asociada a una ODS específica
$consulta = $db->prepare("SELECT r.*, p.Nombre AS nombre_asesor 
                          FROM refacciones r 
                          LEFT JOIN personal p ON r.IdAsesor = p.Idasesor
                          WHERE r.IdODS = :IdODS
                          ORDER BY r.IdRefaccion DESC
                          LIMIT 1");

$consulta->bindParam(':IdODS', $IdODS, PDO::PARAM_INT);
$consulta->execute();

if ($consulta->rowCount() > 0) {
    $row = $consulta->fetch(PDO::FETCH_ASSOC);
}
?>
    <div class="box" style="background-color: #fdf192ff;">
    <div class="columns is-multiline">
        <div class="column is-half">
            <p><strong>ID Refacción:</strong> <?php echo $row['IdRefaccion']; ?></p>
        </div>
        <div class="column is-half">
            <p><strong>ID ODS:</strong> <?php echo $row['IdODS']; ?></p>
        </div>
        <div class="column is-half">
            <p><strong>Producto:</strong> <?php echo $row['producto']; ?></p>
        </div>
        <div class="column is-half">
            <p><strong>Stock:</strong> <?php echo $row['stock']; ?></p>
        </div>
        <div class="column is-half">
            <p><strong>¿Requiere refacción?:</strong> <?php echo $row['refaccion'] ? 'Sí' : 'No'; ?></p>
        </div>
        <div class="column is-half">
            <p><strong>Nombre refacción:</strong> <?php echo $row['Nombre_refaccion'] ?: 'No especificado'; ?></p>
        </div>
        <div class="column is-half">
            <p><strong>Descripción:</strong> <?php echo $row['descripcion']; ?></p>
        </div>
        <div class="column is-half">
            <p><strong>Muestra texto:</strong> <?php echo $row['muestra_texto'] ?: 'No disponible'; ?></p>
        </div>
        <div class="column is-half">
            <p><strong>Asesor:</strong> <?php echo $row['nombre_asesor']; ?></p>
        </div>
        <div class="column is-half">
            <p><strong>Estado:</strong> <?php echo $row['estado']; ?></p>
        </div>
        <div class="column is-full">
            <p><strong>Muestra foto:</strong><br>
            <?php 
                if (!empty($row['muestra_foto'])) {
                    echo "<img src='" . APP_URL . "files/refacciones/" . $row['muestra_foto'] . "' alt='Foto' style='max-width: 300px;'>";
                } else {
                    echo "Sin foto";
                }
            ?>
            </p>
        </div>
    </div>
    </div>

<?php
// Obtener la refacción asociada a una ODS específica, incluyendo datos de la ODS
$consulta = $db->prepare("
    SELECT r.*, 
           p.Nombre AS nombre_asesor, 
           o.Tipo,   
           o.Marca, 
           o.Modelo,
           o.Noserie,
           o.Color,
           o.Problema
    FROM refacciones r
    LEFT JOIN personal p ON r.IdAsesor = p.Idasesor
    LEFT JOIN ods o ON r.IdODS = o.Idods
    WHERE r.IdODS = :IdODS
    ORDER BY r.IdRefaccion DESC
    LIMIT 1
");

$consulta->bindParam(':IdODS', $IdODS, PDO::PARAM_INT);
$consulta->execute();

if ($consulta->rowCount() > 0) {
    $row = $consulta->fetch(PDO::FETCH_ASSOC);
    // Aquí puedes acceder a los datos de la refacción y de la ODS
    // Ejemplo: $row['nombre_asesor'], $row['nombre_ods'], $row['descripcion_ods'], etc.
?>


    <div class="box" style="background-color: #cbfd92ff;">
        <div class="columns is-multiline">
            <div class="column is-half">
                <p><strong>Tipo aparato:</strong> <?php echo $row['Tipo']; ?></p>
            </div>
            <div class="column is-half">
                <p><strong>Marca:</strong> <?php echo $row['Marca']; ?></p>
            </div>
            <div class="column is-half">
                <p><strong>Modelo:</strong> <?php echo $row['Modelo']; ?></p>
            </div>
            <div class="column is-half">
                <p><strong>Noserie:</strong> <?php echo $row['Noserie']; ?></p>
            </div>
            <div class="column">
                <p><strong>Problema explicado por cliente:</strong> <?php echo $row['Problema']; ?></p>
            </div>
            <div class="column is-half">
                <p><strong>Color:</strong> <?php echo $row['Color']; ?></p>
            </div>
            
        </div>
    </div>

    <!-- FORMULARIO para guardar datos -->
<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/refaccionAjax.php" method="POST" autocomplete="off">
     <input type="hidden" name="modulo_refaccion" value="actualizar"> 
    <input type="hidden" name="IdRefaccion" value="<?php echo $row['IdRefaccion']; ?>">

    <div class="box" style="background-color: #92a9fdff;">
        <div class="columns is-multiline">
            <div class="column is-half">
                <label class="label">Caducidad</label>
                <input type="date" name="caducidad" class="input"
                    value="<?php echo (!empty($row['caducidad']) && $row['caducidad'] != '0000-00-00') ? $row['caducidad'] : ''; ?>">
            </div>

            <div class="column is-half">
                <label class="label">Nombre proveedor</label>
                <input
                    type="text"
                    id="proveedor_input"
                    name="proveedor"
                    class="input"
                    placeholder="Escribe para buscar..."
                    autocomplete="off"
                    value="<?php echo htmlspecialchars($row['proveedor'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                >
                <input type="hidden" id="id_proveedor" name="id_proveedor" value="">
            </div>


            <style>
                .lista-proveedores{
                position:absolute; z-index:1000; background:#fff; border:1px solid #ddd;
                width:100%; max-height:220px; overflow:auto; border-radius:8px;
                box-shadow:0 6px 20px rgba(0,0,0,.08);
                }
                .lista-proveedores .item{
                padding:.5rem .75rem; cursor:pointer;
                }
                .lista-proveedores .item:hover{
                background:#f5f5f5;
                }
            </style>
            <style>
                .lista-proveedores{
                position:absolute; z-index:1000; background:#fff; border:1px solid #ddd;
                left:0; right:0; width:100%;
                max-height:220px; overflow:auto; border-radius:8px;
                box-shadow:0 6px 20px rgba(0,0,0,.08);
                }
                .lista-proveedores .item{ padding:.5rem .75rem; cursor:pointer; }
                .lista-proveedores .item:hover{ background:#f5f5f5; }
            </style>
            <style>
                /* Capa global para sugerencias */
                #proveedor_dropdown{
                position: fixed;  /* fuera del flujo, no lo recortan los contenedores */
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 10px 28px rgba(0,0,0,.12);
                max-height: 260px;
                overflow: auto;
                z-index: 99999;   /* por encima de Bulma boxes/modals */
                display: none;
                }
                #proveedor_dropdown .item{
                padding: .5rem .75rem;
                cursor: pointer;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                }
                #proveedor_dropdown .item:hover{
                background: #f5f5f5;
                }
            </style>
            
            <div class="column is-half">
                <label class="label">Precio proveedor</label>
                <input type="number" name="precio_cliente" class="input" step="0.01" value="<?php echo $row['precio_cliente']; ?>">
            </div>
            <div class="column is-half">
                <label class="label">Precio estimado</label>
                <input type="number" name="precio_estimado" class="input" step="0.01" value="<?php echo $row['precio_estimado']; ?>">
            </div>
            <div class="column is-half">
                <label class="label">Precio compra</label>
                <input type="number" name="precio_compra" class="input" step="0.01" value="<?php echo $row['precio_compra']; ?>">
            </div>
        </div>

        <div class="buttons mt-3">
            <button type="submit" class="button is-link is-small">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </div>
</form>
<br>
<!-- BOTONES independientes fuera del formulario -->
<div class="box" style="background-color: #cdb8f3ff;">
<div class="buttons mt-3">
    <button class="button is-success is-small autorizar-btn" data-id="<?php echo $row['IdRefaccion']; ?>">
        <i class="fas fa-check"></i> Autorizar
    </button>
    <button class="button is-danger is-small cancelar-btn" data-id="<?php echo $row['IdRefaccion']; ?>">
        <i class="fas fa-times"></i> Cancelar
    </button>
</div>
</div> 

<?php
} else {
    echo "<p class='notification is-warning'>No se encontró ninguna petición de refacción para esta ODS.</p>";
}
?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
	const formularios = document.querySelectorAll(".FormularioAjax");

	formularios.forEach(formulario => {
		formulario.addEventListener("submit", function (e) {
			e.preventDefault();

			const data = new FormData(formulario);
			const action = formulario.getAttribute("action");

			fetch(action, {
				method: "POST",
				body: data
			})
			.then(res => res.json())
			.then(respuesta => {
				if (respuesta.Alerta === "simple") {
					Swal.fire({
						title: respuesta.Titulo || "Guardado",
						text: respuesta.Texto || "",
						icon: respuesta.Tipo || "success",
						timer: 2000,
						showConfirmButton: false
					});
                    
					// ✅ Borrar valores manualmente
					formulario.querySelector("input[name='caducidad']").value = "";
					formulario.querySelector("input[name='proveedor']").value = "";
					formulario.querySelector("input[name='precio_cliente']").value = "";
					formulario.querySelector("input[name='precio_estimado']").value = "";
					formulario.querySelector("input[name='precio_compra']").value = "";
                    formulario.reset();
				} else {
					Swal.fire({
						title: "¡Ups!",
						text: "Respuesta inesperada del servidor.",
						icon: "warning"
					});
				}
			})
			.catch(error => {
				console.error("Error en el fetch:", error);
				Swal.fire({
					title: "Error",
					text: "El servidor no devolvió una respuesta válida.",
					icon: "error"
				});
			});
		});
	});
});
</script>

<script>
(function(){
  const input    = document.getElementById('proveedor_input');
  const idHidden = document.getElementById('id_proveedor');
  if(!input){ console.warn('No existe #proveedor_input'); return; }

  // Crea el dropdown global una sola vez
  let dropdown = document.getElementById('proveedor_dropdown');
  if(!dropdown){
    dropdown = document.createElement('div');
    dropdown.id = 'proveedor_dropdown';
    document.body.appendChild(dropdown);
  }

  let debounceId = null;

  function hide(){
    dropdown.style.display = 'none';
    dropdown.innerHTML = '';
  }

  function positionDropdown(){
    const r = input.getBoundingClientRect();
    dropdown.style.left = r.left + 'px';
    dropdown.style.top  = (r.bottom + window.scrollY) + 'px';
    dropdown.style.width = r.width + 'px';
  }

  function render(rows){
    dropdown.innerHTML = '';
    if(!Array.isArray(rows) || rows.length === 0){ hide(); return; }
    rows.forEach(item=>{
      const div = document.createElement('div');
      div.className = 'item';
      div.textContent = item.proveedor; // cambia si tu columna se llama distinto
      div.addEventListener('click', ()=>{
        input.value = item.proveedor;
        idHidden.value = item.IdProveedor || '';
        hide();
      });
      dropdown.appendChild(div);
    });
    positionDropdown();
    dropdown.style.display = 'block';
  }

  async function buscar(q){
    const url = '<?php echo APP_URL; ?>app/ajax/buscarProveedor.php?proveedor=' + encodeURIComponent(q || '');
    try{
      const res = await fetch(url, { credentials:'same-origin' });
      if(!res.ok){ hide(); return; }
      const data = await res.json(); // si el servidor manda HTML, esto fallará
      render(data);
    }catch(e){
      console.error('No es JSON válido o error de red:', e);
      hide();
    }
  }

  // Al escribir
  input.addEventListener('input', ()=>{
    idHidden.value = '';
    clearTimeout(debounceId);
    const q = input.value.trim();
    debounceId = setTimeout(()=> {
      if(q.length < 1){ hide(); return; }
      buscar(q);
    }, 250);
  });

  // Al enfocar, si está vacío, muestra top 10
  input.addEventListener('focus', ()=>{
    if(input.value.trim() === ''){ buscar(''); }
    else { positionDropdown(); }
  });

  // Reposiciona al hacer scroll/resize
  window.addEventListener('scroll', ()=> { if(dropdown.style.display==='block') positionDropdown(); }, true);
  window.addEventListener('resize', ()=> { if(dropdown.style.display==='block') positionDropdown(); });

  // Clic fuera para cerrar
  document.addEventListener('click', (e)=>{
    if(e.target !== input && !dropdown.contains(e.target)){ hide(); }
  });
})();
</script>

