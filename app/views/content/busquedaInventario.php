<?php
// busquedaInventario.php

use app\controllers\almacenController;

$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : ''; // Captura el valor de búsqueda de la URL
$insServicio = new almacenController();
?>

<div class="container is-fluid mb-2">
    <h1 class="title">Inventario</h1>
    <h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Resultados de la búsqueda</h2>
</div>
<div class="container pb-2 pt-2">
    <!-- Botón para agregar al inventario -->
    <div class="has-text-right mb-2">
        <a href="<?php echo APP_URL; ?>inventarioNew/" class="button is-primary is-rounded">
            <i class="fas fa-plus"></i> &nbsp; Agregar al inventario
        </a>
    </div>

    <!-- Formulario para filtrar -->
    <div class="field has-addons mb-1">
        <div class="control">
            <input class="input is-rounded" type="text" name="buscar" placeholder="Buscar producto..." id="buscar" value="<?php echo htmlspecialchars($busqueda); ?>">
        </div>
        <div class="control">
            <button class="button is-info is-rounded" id="btnBuscar">
                <i class="fas fa-search"></i> &nbsp; Filtrar
            </button>
        </div>
    </div>

    <!-- Mostrar mensaje si no hay resultados -->
    <div id="mensajeNoResultados" class="is-hidden has-text-centered has-text-danger">
        <p>No se encontraron resultados.</p>
    </div>

    <!-- Llamado al controlador para obtener los productos filtrados -->
    <div class="form-rest mb-2 mt-2"></div>
    <div id="tablaInventario">
        <?php
        echo $insServicio->listarInventarioControlador(1, 10, 'busquedaInventario', $busqueda); // Mostrar los productos filtrados
        ?>
    </div>
</div>

<script>
    // Agregar funcionalidad al botón de búsqueda para redirigir a la misma página con los resultados
    document.getElementById('btnBuscar').addEventListener('click', function () {
        let busqueda = document.getElementById('buscar').value.trim();
        let url = new URL(window.location);
        if (busqueda) {
            url.searchParams.set('buscar', busqueda);  // Agregar el parámetro de búsqueda
        } else {
            url.searchParams.delete('buscar');  // Eliminar el parámetro si no hay búsqueda
        }
        window.location.href = url.toString();  // Redirige a la misma página con el parámetro 'buscar'
    });
</script>
