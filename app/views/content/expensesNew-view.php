<div class="container is-fluid mb-6">
	<h1 class="title">Gastos</h1>
	<h2 class="subtitle"><i class="fas fa-cash-register fa-fw"></i> &nbsp; Nuevo gasto</h2>
</div>

<div class="container pb-6 pt-6">

	<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/gastoAjax.php" method="POST" autocomplete="off" >

		<input type="hidden" name="modulo_gasto" value="registrar">

		<div class="columns">
		  	<div class="column">
		    	<div class="control">
					<label>Efectivo<?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="text" name="Efectivo"
       						pattern="^\d{1,9}(\.\d{1,2})?$"
       						maxlength="20"
       						value="0.00"
					        required>
				</div>
		  	</div>
			<div class="column">
		    	<div class="control">
					<label>Descripcion <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="text" name="Descripcion"
       						pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ., ]{4,150}"
       						maxlength="150"
					        required>
				</div>
		  	</div>
		</div>

		<p class="has-text-centered">
			<button type="reset" class="button is-link is-light is-rounded"><i class="fas fa-paint-roller"></i> &nbsp; Limpiar</button>
			<button type="submit" class="button is-info is-rounded"><i class="far fa-save"></i> &nbsp; Guardar</button>
		</p>
		<p class="has-text-centered pt-6">
            <small>Los campos marcados con <?php echo CAMPO_OBLIGATORIO; ?> son obligatorios</small>
        </p>
	</form>
</div>