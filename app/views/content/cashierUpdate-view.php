<div class="container is-fluid mb-6">
	<h1 class="title">Cajas</h1>
	<h2 class="subtitle"><i class="fas fa-sync-alt"></i> &nbsp; Actualizar caja</h2>
</div>

<div class="container pb-6 pt-6">
	<?php
	
		include "./app/views/inc/btn_back.php";

		$id = $insLogin->limpiarCadena($url[1]);

		$datos = $insLogin->seleccionarDatos("Unico", "cajai", "Idini", $id);

		if ($datos->rowCount() == 1) {
			$datos = $datos->fetch();
	?>

	<h2 class="title has-text-centered">
		Caja <?php echo htmlspecialchars($datos['Idini']); ?> 
		- Efectivo: <?php echo htmlspecialchars(number_format($datos['Efectivo'], 2)); ?>
	</h2>

	<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/cajaAjax.php" method="POST" autocomplete="off">

		<input type="hidden" name="modulo_caja" value="actualizar">
		<input type="hidden" name="Idini" value="<?php echo htmlspecialchars($datos['Idini']); ?>">

		<div class="columns">
		  	<div class="column">
		    	<div class="control">
					<label>ID caja <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="text" name="Idini_visible"
       						readonly
       						value="<?php echo htmlspecialchars($datos['Idini']); ?>">
				</div>
		  	</div>
		  	<div class="column">
		    	<div class="control">
					<label>Efectivo en caja <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="text" name="Efectivo"
       						pattern="^\d{1,9}(\.\d{1,2})?$"
       						maxlength="20"
       						value="<?php echo htmlspecialchars(number_format($datos['Efectivo'], 2)); ?>"
					        required>
				</div>
		  	</div>
		</div>

		<p class="has-text-centered">
			<button type="submit" class="button is-success is-rounded">
				<i class="fas fa-sync-alt"></i> &nbsp; Actualizar
			</button>
		</p>

		<p class="has-text-centered pt-6">
            <small>Los campos marcados con <?php echo CAMPO_OBLIGATORIO; ?> son obligatorios</small>
        </p>
	</form>

	<?php
		} else {
			include "./app/views/inc/error_alert.php";
		}
	?>
</div>
