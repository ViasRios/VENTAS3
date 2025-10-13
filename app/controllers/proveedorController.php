<?php
namespace app\controllers;

use app\models\mainModel;
use \PDO;

class proveedorController extends mainModel {

    public function listarProveedorControlador($pagina, $registros, $url, $busqueda) {

        // Limpiar las entradas
        $pagina = $this->limpiarCadena($pagina);
        $registros = $this->limpiarCadena($registros);
        $url = $this->limpiarCadena($url);
        $url = APP_URL . $url . "/";

        $busqueda = $this->limpiarCadena($busqueda);
        $tabla = "";

        // Paginación
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        // Definir los campos que se mostrarán
        $campos = "IdProveedor, proveedor, telefono, email, direccion, web, fecha_registro";

        // Realizar la consulta con búsqueda si hay
        if (!empty($busqueda)) {
            $consulta_datos = "
                SELECT $campos
                FROM proveedor
                WHERE proveedor LIKE '%$busqueda%' OR email LIKE '%$busqueda%' OR telefono LIKE '%$busqueda%'
                ORDER BY proveedor ASC
                LIMIT $inicio, $registros";

            $consulta_total = "
                SELECT COUNT(IdProveedor)
                FROM proveedor
                WHERE proveedor LIKE '%$busqueda%' OR email LIKE '%$busqueda%' OR telefono LIKE '%$busqueda%'";
        } else {
            // Si no hay búsqueda, mostrar todos los proveedores
            $consulta_datos = "
                SELECT $campos
                FROM proveedor
                ORDER BY proveedor ASC
                LIMIT $inicio, $registros";

            $consulta_total = "
                SELECT COUNT(IdProveedor)
                FROM proveedor";
        }

        // Ejecutar las consultas
        $datos = $this->ejecutarConsulta($consulta_datos)->fetchAll();
        $total = (int) $this->ejecutarConsulta($consulta_total)->fetchColumn();
        $numeroPaginas = ceil($total / $registros);

        // Generar la tabla de proveedores
        if ($total >= 1 && $pagina <= $numeroPaginas) {
            $pag_inicio = $inicio + 1;
            $pag_final = $inicio + count($datos);

            $tabla .= '
            <div class="table-container">
            <table class="table is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th>ID Proveedor</th>
                        <th>Proveedor</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th>Web</th>
                        <th>Fecha de Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
            ';

            $contador = $inicio + 1;
            foreach ($datos as $rows) {
                $tabla .= '
                    <tr>
                        <td>' . htmlspecialchars($rows['IdProveedor']) . '</td>
                        <td>' . htmlspecialchars($rows['proveedor']) . '</td>
                        <td>' . htmlspecialchars($rows['telefono']) . '</td>
                        <td>' . htmlspecialchars($rows['email']) . '</td>
                        <td>' . htmlspecialchars($rows['direccion']) . '</td>
                        <td>' . htmlspecialchars($rows['web']) . '</td>
                        <td>' . htmlspecialchars($rows['fecha_registro']) . '</td>
                        <td>
                            <a href="' . APP_URL . 'proveedorUpdate/' . $rows['IdProveedor'] . '/" class="button is-success is-small is-rounded">
                                <i class="fas fa-sync fa-fw"></i>
                            </a>
                            <form class="FormularioAjax is-inline-block" action="' . APP_URL . 'app/ajax/proveedorAjax.php" method="POST" autocomplete="off">
                                <input type="hidden" name="modulo_proveedor" value="eliminar">
                                <input type="hidden" name="IdProveedor" value="' . $rows['IdProveedor'] . '">
                                <button type="submit" class="button is-danger is-small is-rounded">
                                    <i class="far fa-trash-alt fa-fw"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                ';
                $contador++;
            }

            $tabla .= '
                </tbody>
            </table>
            </div>
            ';

            // Mostrar información de la paginación
            $tabla .= '<p class="has-text-right">Mostrando proveedores <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
            $tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);

        } else {
            if ($total >= 1) {
                $tabla .= '
                <p class="has-text-centered pb-6"><i class="far fa-hand-point-down fa-5x"></i></p>
                <p class="has-text-centered">
                    <a href="' . $url . '1/" class="button is-link is-rounded is-small mt-4 mb-4">
                        Haga clic acá para recargar el listado
                    </a>
                </p>';
            } else {
                $tabla .= '
                <p class="has-text-centered pb-6"><i class="far fa-grin-beam-sweat fa-5x"></i></p>
                <p class="has-text-centered">No hay proveedores registrados</p>';
            }
        }

        return $tabla;
    }
}
