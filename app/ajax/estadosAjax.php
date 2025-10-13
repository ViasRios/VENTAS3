<?php
require_once "../config/app.php";
require_once "../autoload.php";

use app\controllers\statusController;

if(isset($_POST['Status'])){
    $ctrl = new statusController();
    $ods = $ctrl->obtenerOdsPorStatusControlador($_POST['Status']);

    if(count($ods) > 0){
        echo "<div class='table-container'><table class='table is-fullwidth is-striped'>
                <thead>
                    <tr><th>ID</th><th>Cliente</th><th>Fecha</th><th>Técnico</th></tr>
                </thead><tbody>";
        foreach($ods as $od){
            echo "<tr>
                    <td>{$od['Idods']}</td>
                    <td>{$od['Cliente']}</td>
                    <td>{$od['Fecha']}</td>
                    <td>{$od['Tecnico']}</td>
                  </tr>";
        }
        echo "</tbody></table></div>";
    } else {
        echo "<p class='has-text-centered has-text-grey'>No hay ODS con este estado.</p>";
    }
}
