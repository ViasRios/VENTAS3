<?php
namespace app\controllers;

use app\models\mainModel;

class notasController extends mainModel {

    /*----------  Controlador listar notas (sin filtros)  ----------*/
    public function listarNotaControlador($pagina, $registros, $url, $busqueda = "") {

        // limpiar cositas pa' que no truene
        $pagina    = $this->limpiarCadena($pagina);
        $registros = $this->limpiarCadena($registros);
        $url       = $this->limpiarCadena($url);

        // base del paginador; si no hay slug, usamos notasList
        $url = APP_URL . ($url !== "" ? $url : "notasList") . "/";

        $tabla  = "";
        $pagina = (isset($pagina) && $pagina > 0) ? (int)$pagina : 1;
        $inicio = ($pagina - 1) * $registros;
        $conexion = self::conectar();
        $campos   = "Idods, Fecha, Hora, Tecnico, Nota";
        // total registros
        $total = (int)$conexion->query("SELECT COUNT(Idnota) FROM notas")->fetchColumn();
        // datos de la página
        $stmt = $conexion->prepare("SELECT $campos
                                    FROM notas
                                    ORDER BY Idnota DESC
                                    LIMIT :inicio, :limite");
        $stmt->bindValue(':inicio', (int)$inicio, \PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$registros, \PDO::PARAM_INT);
        $stmt->execute();
        $datos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $Npaginas = $total > 0 ? (int)ceil($total / $registros) : 1;
        // helper mini p/escape
        $h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
        // tabla sin filtros (simple y bonito)
        $tabla .= '
        <div class="table-container">
            <table class="table is-fullwidth is-striped is-hoverable">
                <thead>
                    <tr>
                        <th style="width:90px">Idods</th>
                        <th style="width:120px">Fecha</th>
                        <th style="width:100px">Hora</th>
                        <th style="width:200px">Técnico</th>
                        <th>Nota</th>
                    </tr>
                </thead>
                <tbody>
        ';

        if ($total >= 1 && !empty($datos)) {
            foreach ($datos as $row) {
                $nota = (string)($row['Nota'] ?? "");
                $nota_corta = (mb_strlen($nota) > 220) ? (mb_substr($nota, 0, 220) . "…") : $nota;

                $tabla .= '
                    <tr>
                        <td>
                            <a class="has-text-link" href="'.APP_URL.'odsView/'.(int)$row['Idods'].'/">' . (int)$row['Idods'] . '</a>
                        </td>
                        <td>'.$h($row['Fecha'] ?? "").'</td>
                        <td>'.$h($row['Hora'] ?? "").'</td>
                        <td>'.$h($row['Tecnico'] ?? "").'</td>
                        <td>'.$h($nota_corta).'</td>
                    </tr>
                ';
            }
        } else {
            $tabla .= '
                <tr>
                    <td colspan="6" class="has-text-centered has-text-grey">No hay registros para mostrar.</td>
                </tr>
            ';
        }

        $tabla .= '
                </tbody>
            </table>
        </div>
        ';

        // pie y paginador
        if ($total >= 1) {
            $tabla .= '<p class="has-text-right is-size-7">Mostrando '.count($datos).' de '.$total.' registro(s).</p>';
            $tabla .= $this->paginadorTablas($pagina, $Npaginas, $url, 7);
        }

        return $tabla;
    }
}
