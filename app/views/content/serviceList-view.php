<div class="container is-fluid mb-2">
	<h1 class="title">Servicios</h1>
	<h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de servicios</h2>
</div>
<div class="container pb-1 pt-1">
	<div class="form-rest mb-1 mt-1"></div>
	<?php
		use app\controllers\serviceController;
		$insServicio = new serviceController();
		echo $insServicio->listarServicioControlador($url[1],10,$url[0],"",0);
	?>
</div>