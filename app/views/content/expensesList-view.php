<div class="container is-fluid mb-6">
	<h1 class="title">Gastos</h1>
	<h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de gastos</h2>
</div>

<!-- EJEMPLO DE POWER BI -->
	<div class="columns pt-2">
		<div class="column">
			<h2 class="title is-4" style="margin-left: 40px;" >Visualización de Gastos</h2>
			<div class="box" style="overflow-x: auto;">
				<iframe 
					title="egresos" 
					width="1210"
					height="541.25"
					src="https://app.powerbi.com/reportEmbed?reportId=2b016982-fe84-42e7-8fee-08e3c3248d35&autoAuth=true&embeddedDemo=true"
					frameborder="0"
					allowFullScreen="true">
				</iframe>
			</div>
		</div>
	</div>


<div class="container pb-1 pt-1">

	<div class="form-rest mb-1 mt-1"></div>

	<?php
		use app\controllers\expensesController;

		$insGasto = new expensesController();

		echo $insGasto->listarGastoControlador($url[1], 10, $url[0], $busqueda,$Clasificacion);
?>
</div>

