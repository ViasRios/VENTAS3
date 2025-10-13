<div class="container is-fluid mb-3">
	<h1 class="title">Inventario</h1>
	<h2 class="subtitle"><i class="fas fa-boxes fa-fw"></i> &nbsp; Nuevo producto</h2>
</div>

<div class="container pb-2 pt-2">
	<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/inventarioAjax.php" method="POST" autocomplete="off">
		<input type="hidden" name="modulo_inventario" value="registrar">

		<div class="columns">
		  	<div class="column">
				<div class="control">
					<label>Producto</label>
				  	<input class="input" type="text" name="producto" required>
				</div>
		  	</div>

			<div class="column">
				<div class="control">
					<label>Código</label>
					<input class="input" type="text" name="codigo" placeholder="Opcional">
				</div>
		  	</div>
		</div>
		<input type="hidden" name="refaccion" id="refaccion_hidden" value="0">


		<div class="columns">
			<div class="column">
		    	<div class="control">
					<label>Proveedor</label>
				  	<input class="input" type="text" name="proveedor">
				</div>
		  	</div>

		  	<div class="column">
				<div class="control">
					<label>Stock</label>
				  	<input class="input" type="number" name="stock" min="0" value="0">
				</div>
		  	</div>
		</div>

		<div class="columns">
			<div class="column">
		    	<div class="control">
					<label>Precio compra</label>
				  	<input class="input" type="number" name="precio_compra" min="0" step="0.01">
				  </div>
		  </div>

		  <div class="column">
				<div class="control">
					  <label>Precio venta</label>
				  	<input class="input" type="number" name="precio_venta" min="0" step="0.01">
				</div>
		  </div>

      <div class="column">
				<div class="control">
					  <label>Precio sugerido</label>
				  	<input class="input" type="number" name="precio_sugerido" min="0" step="0.01">
				</div>
		  </div>
		</div>

		<div class="columns">
		<!--	<div class="column">
				<div class="control">
					<label>Descripción</label>
				  	<textarea class="textarea" name="descripcion"></textarea>
				</div>
		  </div> -->

      <div class="column">
				<div class="control">
					<label>Característica 1: </label>
				  	<textarea class="textarea" name="caracteristica1"></textarea>
				</div>
		  </div>

      <div class="column">
				<div class="control">
					<label>Característica 2: </label>
				  	<textarea class="textarea" name="caracteristica2"></textarea>
				</div>
		  </div>

      <div class="column">
				<div class="control">
					<label>Característica 3: </label>
				  	<textarea class="textarea" name="caracteristica3"></textarea>
				</div>
		  </div>

      <div class="column">
				<div class="control">
					<label>Característica 4: </label>
				  	<textarea class="textarea" name="caracteristica4"></textarea>
				</div>
		  </div>
		</div>

		<p class="has-text-centered">
			<button type="reset" class="button is-link is-light is-rounded"><i class="fas fa-paint-roller"></i> &nbsp; Limpiar</button>
			<button type="submit" class="button is-info is-rounded">
				<i class="far fa-save"></i> &nbsp; Guardar
			</button>
		</p>
	</form>
	<div id="ajax-response" class="pt-4"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.FormularioAjax');
  const out  = document.getElementById('ajax-response');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    try {
      const res = await fetch(form.action, {
        method: form.method,
        body: new FormData(form),
        credentials: 'include'
      });

      const text = await res.text();
      let data;

      try { data = JSON.parse(text); } catch {
        Swal.fire({
          title: 'Error',
          html: `<pre>${text.replace(/</g, '&lt;')}</pre>`,
          icon: 'error'
        });
        return;
      }

      // Detectar tu formato específico
      if (data.alert && data.alert.toLowerCase() === 'success') {
        Swal.fire({
          title: data.title || '¡Guardado!',
          text: data.message || 'Producto registrado correctamente.',
          icon: 'success'
        }).then(() => {
          form.reset();
          if (out) out.innerHTML = '';
        });
      } else {
        Swal.fire({
          title: data.title || 'No se pudo guardar',
          text: data.message || 'Ocurrió un problema.',
          icon: data.alert || 'error'
        });
      }

    } catch (err) {
      Swal.fire({
        title: 'Error de red',
        text: 'No se pudo conectar con el servidor.',
        icon: 'error'
      });
    }
  });
});
</script>


