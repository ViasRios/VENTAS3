<div class="container is-fluid mb-6">
	<h1 class="title">Proveedores</h1>
	<h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de proveedores</h2>
</div>
<div class="container pb-1 pt-1">
	<div class="has-text-right mb-4">
		<a href="<?php echo APP_URL; ?>proveedorNew/" class="button is-primary is-rounded">
			<i class="fas fa-plus"></i> &nbsp; Nuevo proveedor
		</a>
	</div>

	<div class="form-rest mb-4 mt-4"></div>
	<?php
		use app\controllers\proveedorController;

		$insProveedor = new proveedorController();
		echo $insProveedor->listarProveedorControlador($url[1],10,$url[0],"");
	?>
</div>