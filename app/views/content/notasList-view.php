<div class="container is-fluid mb-2">
	<h1 class="title">Notas</h1>
	<h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de notas</h2>
</div>

<div class="container pb-2 pt-2">

	<div class="form-rest mb-2 mt-2"></div>

	<?php
		use app\controllers\notasController;
		$insNota = new notasController();

		// evitar el "Undefined array key 1" cuando no hay página en la ruta
		$pagina = (isset($url[1]) && is_numeric($url[1]) && $url[1] > 0) ? (int)$url[1] : 1;

		// si por alguna razón $url[0] no viene, usamos un slug por defecto
		$slug = isset($url[0]) && $url[0] !== '' ? $url[0] : 'notasList';

		// ya sin filtros ni búsqueda, solo listado y paginación
		echo $insNota->listarNotaControlador($pagina, 15, $slug, "");
	?>
</div>
