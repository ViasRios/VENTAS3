<div class="container is-fluid mb-2">
	<h1 class="title">Personal</h1>
	<h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de personal</h2>
</div>
<div class="container pb-2 pt-2">

	<div class="form-rest mb-2 mt-2"></div>

	<?php
		use app\controllers\personalController;

		$insPersonal = new personalController();

		echo $insPersonal->listarPersonalControlador($url[1],15,$url[0],"");
	?>
</div>