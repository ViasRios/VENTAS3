<div class="container is-fluid mb-6">
	<h1 class="title">Refacciones</h1>
	<h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de refacciones</h2>
</div>
<div class="container pb-1 pt-1">
	<div class="has-text-right mb-4">
		<a href="<?php echo APP_URL; ?>refaccionNew/" class="button is-primary is-rounded">
			<i class="fas fa-plus"></i> &nbsp; Nueva refacción
		</a>
	</div>

	<div class="form-rest mb-4 mt-4"></div>
	<?php
		use app\controllers\almacenController;

		$insServicio = new almacenController();
		echo $insServicio->listarRefaccionControlador($url[1],10,$url[0],"");
	?>
</div>