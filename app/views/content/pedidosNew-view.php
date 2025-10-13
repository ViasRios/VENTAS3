<div class="container is-fluid mb-6">
	<h1 class="title">Pedidos</h1>
	<h2 class="subtitle"><i class="fas fa-truck-loading fa-fw"></i> &nbsp; Nuevo pedido</h2>
</div>

<div class="container pb-6 pt-6">

<?php
use app\models\mainModel;
$conexion = new PDO("mysql:host=localhost;dbname=sistema", "root", "");
$conexion->exec("SET CHARACTER SET utf8");

// Refacciones para la lista
$refacciones = $conexion->query("
	SELECT IdRefaccion, producto 
	FROM refacciones 
	ORDER BY producto ASC
");
?>

<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/pedidoAjax.php" method="POST" autocomplete="off">

	<input type="hidden" name="modulo_pedido" value="registrar">

	<div class="columns">
	 <!--	<div class="column">
	    	<div class="control">
				<label>ID ODS <?php echo CAMPO_OBLIGATORIO; ?></label>
			  	<input class="input" type="text" name="IdODS"
   						pattern="[0-9]{1,10}"
   						maxlength="10"
				        required>
			</div>
	  	</div> -->

		<div class="column">
	    	<div class="control">
				<label>Refacción</label>
				<div class="select is-fullwidth">
					<select name="IdRefaccion">
						<option value="" selected>Sin refacción</option>
						<?php while($ref = $refacciones->fetch()){ ?>
							<option value="<?php echo htmlspecialchars($ref['IdRefaccion']); ?>">
								<?php echo htmlspecialchars($ref['producto']); ?>
							</option>
						<?php } ?>
					</select>
				</div>
			</div>
		</div>
	</div>

	<div class="columns">
	  	<div class="column">
	    	<div class="control">
				<label>Descripción <?php echo CAMPO_OBLIGATORIO; ?></label>
			  	<input class="input" type="text" name="descripcion"
   						pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ., ]{4,150}"
   						maxlength="150"
				        required>
			</div>
	  	</div>
	</div>

	<div class="columns">
		<div class="column">
	    	<div class="control">
				<label>Precio compra <?php echo CAMPO_OBLIGATORIO; ?></label>
			  	<input class="input" type="text" name="precio_compra"
   						pattern="^\d{1,9}(\.\d{1,2})?$"
   						maxlength="20"
   						value="0.00"
				        required>
			</div>
	  	</div>
	  	<div class="column">
	    	<div class="control">
				<label>Proveedor <?php echo CAMPO_OBLIGATORIO; ?></label>
			  	<input class="input" type="text" name="proveedor"
   						pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ., ]{2,100}"
   						maxlength="100"
				        required>
			</div>
	  	</div>
	</div>

	<div class="columns">
		<div class="column">
	    	<div class="control">
				<label>Fecha llegada aproximada <?php echo CAMPO_OBLIGATORIO; ?></label>
			  	<input class="input" type="date" name="fecha_llegada_aprox" required>
			</div>
	  	</div>
	  	<div class="column">
	    	<div class="control">
				<label>Fecha caducidad</label>
			  	<input class="input" type="date" name="fecha_caducidad">
			</div>
	  	</div>
	</div>

	<div class="columns">
	  	<div class="column">
	    	<div class="control">
				<label>Link seguimiento</label>
			  	<input class="input" type="url" name="link_seguimiento" maxlength="255">
			</div>
	  	</div>
		<div class="column">
			<label>Status </label>
			<div class="select is-fullwidth"> 
				<select name="status" required>
					<option value="pedido" selected>Pedido</option>
					<option value="en_camino">En camino</option>
					<option value="entregado">Entregado</option>
					<option value="cancelado">Cancelado</option>
				</select>
			</div>
		</div>
	</div>

	<div class="column">
		<div class="column">
			<div class="control">
				<label class="checkbox">
					<input type="checkbox" name="entregado_tecnico" value="1">
					Entregado a técnico
				</label>
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
