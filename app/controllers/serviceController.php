<?php
    namespace app\controllers;
    use app\models\mainModel;

    class serviceController extends mainModel{

        /*----------  1. REGISTRAR SERVICIO (INTELIGENTE: ODS vs CATÁLOGO)  ----------*/
        public function registrarServicioControlador(){
            
            $Idods = isset($_POST['Idods']) ? $this->limpiarCadena($_POST['Idods']) : "";
            $Descripcion = $this->limpiarCadena($_POST['Descripcion']);
            $Costo = $this->limpiarCadena($_POST['Costo']);

            if($Descripcion=="" || $Costo==""){
                return json_encode(["tipo"=>"simple", "titulo"=>"Error", "texto"=>"Campos vacíos", "icono"=>"error"]);
            }

            // Validación de precio flexible
            if($this->verificarDatos("[0-9.]{1,25}",$Costo)){
                return json_encode(["tipo"=>"simple", "titulo"=>"Error", "texto"=>"Formato de precio incorrecto", "icono"=>"error"]);
            }

            /* --- CASO A: GUARDAR EN ODS (Lista separada por comas) --- */
            if($Idods != "") {
                $check_ods = $this->ejecutarConsulta("SELECT Reparacion, Costorep FROM ods WHERE Idods='$Idods'");
                if($check_ods->rowCount() > 0){
                    $datos_ods = $check_ods->fetch();
                    
                    $reparaciones_actuales = trim($datos_ods['Reparacion']);
                    $costos_actuales = trim($datos_ods['Costorep']);

                    // Agregar nuevo servicio a la lista (Pendiente por defecto)
                    if($reparaciones_actuales != ""){
                        $nueva_reparacion = $reparaciones_actuales . "," . $Descripcion;
                        $nuevo_costo = $costos_actuales . "," . $Costo;
                    } else {
                        $nueva_reparacion = $Descripcion;
                        $nuevo_costo = $Costo;
                    }

                    $actualizar = $this->actualizarDatos("ods", [
                        ["campo_nombre"=>"Reparacion", "campo_marcador"=>":Rep", "campo_valor"=>$nueva_reparacion],
                        ["campo_nombre"=>"Costorep",   "campo_marcador"=>":Cos", "campo_valor"=>$nuevo_costo]
                    ], [
                        "condicion_campo"=>"Idods", "condicion_marcador"=>":ID", "condicion_valor"=>$Idods
                    ]);

                    return json_encode(["tipo"=>"recargar", "titulo"=>"Éxito", "texto"=>"Servicio agregado", "icono"=>"success"]);
                }
                return json_encode(["tipo"=>"simple", "titulo"=>"Error", "texto"=>"ODS no encontrada", "icono"=>"error"]);
            
            /* --- CASO B: GUARDAR EN CATÁLOGO GENERAL --- */
            } else {
                $servicio_datos_reg=[
                    ["campo_nombre"=>"Descripcion", "campo_marcador"=>":Descripcion", "campo_valor"=>$Descripcion],
                    ["campo_nombre"=>"Costo", "campo_marcador"=>":Costo", "campo_valor"=>$Costo]
                ];
                $registrar_servicio=$this->guardarDatos("servicios",$servicio_datos_reg);
                
                if($registrar_servicio->rowCount()==1){
                    return json_encode(["tipo"=>"limpiar", "titulo"=>"Éxito", "texto"=>"Servicio creado en catálogo", "icono"=>"success"]);
                } else {
                    return json_encode(["tipo"=>"simple", "titulo"=>"Error", "texto"=>"Error al registrar", "icono"=>"error"]);
                }
            }
        }

        /*----------  2. BORRADO MASIVO DE SERVICIOS (CHECKBOXES)  ----------*/
        public function eliminarServiciosMasivosControlador(){
            $Idods = $this->limpiarCadena($_POST['Idods']);
            $indicesStr = $_POST['indices']; // Recibe string "0,2,5"

            $datos = $this->ejecutarConsulta("SELECT Reparacion, Costorep FROM ods WHERE Idods='$Idods'")->fetch();
            if(!$datos) return json_encode(["success"=>false, "error"=>"ODS no encontrada"]);

            $servicios = explode(',', $datos['Reparacion']);
            $costos    = explode(',', $datos['Costorep']);
            $indices   = explode(',', $indicesStr);

            // Eliminar elementos en las posiciones marcadas
            foreach($indices as $idx){
                $i = intval($idx);
                if(isset($servicios[$i])) {
                    unset($servicios[$i]);
                    unset($costos[$i]);
                }
            }

            // Reconstruir listas sin huecos
            $nuevo_servicios = implode(',', array_values($servicios));
            $nuevo_costos    = implode(',', array_values($costos));

            $this->actualizarDatos("ods", [
                ["campo_nombre"=>"Reparacion", "campo_marcador"=>":Rep", "campo_valor"=>$nuevo_servicios],
                ["campo_nombre"=>"Costorep",   "campo_marcador"=>":Cos", "campo_valor"=>$nuevo_costos]
            ], [
                "condicion_campo"=>"Idods", "condicion_marcador"=>":ID", "condicion_valor"=>$Idods
            ]);

            return json_encode(["success"=>true, "tipo"=>"recargar"]);
        }

        /*----------  3. CAMBIAR ESTADO (ACEPTAR/RECHAZAR SERVICIO)  ----------*/
        public function toggleEstadoServicioControlador(){
            $Idods = $this->limpiarCadena($_POST['Idods']);
            $Index = intval($_POST['Index']);
            $Accion = $_POST['Accion'];

            $datos = $this->ejecutarConsulta("SELECT Reparacion FROM ods WHERE Idods='$Idods'")->fetch();
            $servicios = explode(',', $datos['Reparacion']);

            if(isset($servicios[$Index])){
                $nombre = trim($servicios[$Index]);
                // Quitamos la marca si ya la tenía
                $nombreLimpio = str_replace(' [OK]', '', $nombre);

                if($Accion == 'aceptar'){
                    $servicios[$Index] = $nombreLimpio . " [OK]";
                } else {
                    $servicios[$Index] = $nombreLimpio; // Vuelve a pendiente
                }

                $nuevo_servicios = implode(',', $servicios);
                
                $this->actualizarDatos("ods", [
                    ["campo_nombre"=>"Reparacion", "campo_marcador"=>":Rep", "campo_valor"=>$nuevo_servicios]
                ], [
                    "condicion_campo"=>"Idods", "condicion_marcador"=>":ID", "condicion_valor"=>$Idods
                ]);

                return json_encode(["success"=>true, "tipo"=>"recargar"]);
            }
            return json_encode(["success"=>false, "error"=>"Índice no válido"]);
        }

        /*----------  4. FUNCIONES ESTÁNDAR (CATÁLOGO)  ----------*/
        
        public function listarServicioControlador($pagina, $registros, $url, $busqueda, $categoria) {
             $pagina = $this->limpiarCadena($pagina);
            $registros = $this->limpiarCadena($registros);
            $categoria = $this->limpiarCadena($categoria);
            $url = $this->limpiarCadena($url);

            $url = ($categoria > 0) ? APP_URL . $url . "/" . $categoria . "/" : APP_URL . $url . "/";
            $busqueda = $this->limpiarCadena($busqueda);
            $tabla = "";

            $pagina = (isset($pagina) && $pagina > 0) ? (int)$pagina : 1;
            $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

            $campos = "Idser,Descripcion,Costo";

            if (!empty($busqueda)) {
                $consulta_datos = "SELECT $campos FROM servicios WHERE Descripcion LIKE '%$busqueda%' ORDER BY Descripcion ASC LIMIT $inicio, $registros";
                $consulta_total = "SELECT COUNT(Idser) FROM servicios WHERE Descripcion LIKE '%$busqueda%'";
            } elseif ($categoria > 0) {
                $consulta_datos = "SELECT $campos FROM servicios WHERE Idser='$categoria' ORDER BY Descripcion ASC LIMIT $inicio, $registros";
                $consulta_total = "SELECT COUNT(Idser) FROM servicios WHERE Idser='$categoria'";
            } else {
                $consulta_datos = "SELECT $campos FROM servicios ORDER BY Descripcion ASC LIMIT $inicio, $registros";
                $consulta_total = "SELECT COUNT(Idser) FROM servicios";
            }
            
            $datos = $this->ejecutarConsulta($consulta_datos)->fetchAll();
            $total = (int) $this->ejecutarConsulta($consulta_total)->fetchColumn();
            $numeroPaginas = ceil($total / $registros);

            if ($total >= 1 && $pagina <= $numeroPaginas) {
                $pag_inicio = $inicio + 1;
                $pag_final = $inicio + count($datos);
                
                $tabla .= '
                <div class="table-container">
                <table class="table is-striped is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th>Costo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                ';

                foreach ($datos as $rows) {
                    $tabla .= '
                    <tr>
                        <td>' . $rows['Idser'] . '</td>
                        <td>' . $rows['Descripcion'] . '</td>
                        <td>$' . number_format($rows['Costo'], 2) . '</td>
                        <td>
                            <a href="' . APP_URL . 'serviceUpdate/' . $rows['Idser'] . '/" class="button is-success is-rounded is-small">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                            <form class="FormularioAjax is-inline-block" action="' . APP_URL . 'app/ajax/servicioAjax.php" method="POST" autocomplete="off">
                                <input type="hidden" name="modulo_servicio" value="eliminar">
                                <input type="hidden" name="Idser" value="' . $rows['Idser'] . '">
                                <button type="submit" class="button is-danger is-rounded is-small">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>';
                }

                $tabla .= '
                    </tbody>
                </table>
                </div>';
            } else {
                $tabla .= ($total >= 1)
                    ? '<p class="has-text-centered"><a href="' . $url . '1/" class="button is-link is-rounded is-small mt-4 mb-4">Haga clic acá para recargar el listado</a></p>'
                    : '<p class="has-text-centered">No hay servicios registrados</p>';
            }

            if ($total > 0 && $pagina <= $numeroPaginas) {
                $tabla .= '<p class="has-text-right">Mostrando servicios <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
                $tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);
            }
            return $tabla;
        }

        public function eliminarServicioControlador(){
            $id=$this->limpiarCadena($_POST['Idser']);
            $datos=$this->ejecutarConsulta("SELECT * FROM servicios WHERE Idser='$id'");
            if($datos->rowCount()<=0){
                return json_encode(["tipo"=>"simple", "titulo"=>"Error", "texto"=>"Servicio no encontrado", "icono"=>"error"]);
            }
            $eliminar=$this->eliminarRegistro("servicios","Idser",$id);
            if($eliminar->rowCount()==1){
                return json_encode(["tipo"=>"recargar", "titulo"=>"Eliminado", "texto"=>"Eliminado correctamente", "icono"=>"success"]);
            }
            return json_encode(["tipo"=>"simple", "titulo"=>"Error", "texto"=>"No se pudo eliminar", "icono"=>"error"]);
        }

        public function actualizarServicioControlador(){
             $id=$this->limpiarCadena($_POST['Idser']);

            # Verificando servicio #
            $datos=$this->ejecutarConsulta("SELECT * FROM servicios WHERE Idser='$id'");
            if($datos->rowCount()<=0){
                $alerta=[
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"No hemos encontrado el servicio en el sistema",
                    "icono"=>"error"
                ];
                return json_encode($alerta);
            }else{
                $datos=$datos->fetch();
            }

            # Almacenando datos#
            $Idser=$this->limpiarCadena($_POST['Idser']);
            $Descripcion=$this->limpiarCadena($_POST['Descripcion']);
            $Costo=$this->limpiarCadena($_POST['Costo']);

            # Verificando campos obligatorios #
            if($Descripcion=="" || $Costo==""){
                $alerta=[
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"No has llenado todos los campos que son obligatorios",
                    "icono"=>"error"
                ];
                return json_encode($alerta);
            }

            # Verificando integridad de los datos #
            if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,100}",$Descripcion)){
                $alerta=[
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"El NOMBRE no coincide con el formato solicitado",
                    "icono"=>"error"
                ];
                return json_encode($alerta);
            }

            /* --- CORRECCIÓN AQUÍ TAMBIÉN --- */
            if($this->verificarDatos("[0-9.]{1,25}",$Costo)){
                $alerta=[
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"El PRECIO DE COMPRA no coincide con el formato solicitado",
                    "icono"=>"error"
                ];
                return json_encode($alerta);
            }

            $servicio_datos_up=[
                [
                    "campo_nombre"=>"Descripcion",
                    "campo_marcador"=>":Descripcion",
                    "campo_valor"=>$Descripcion
                ],
                [
                    "campo_nombre"=>"Costo",
                    "campo_marcador"=>":Costo",
                    "campo_valor"=>$Costo
                ]
            ];

            $condicion=[
                "condicion_campo"=>"Idser",
                "condicion_marcador"=>":ID",
                "condicion_valor"=>$id
            ];

            if($this->actualizarDatos("servicios",$servicio_datos_up,$condicion)){
                $alerta=[
                    "tipo"=>"recargar",
                    "titulo"=>"Servicio actualizado",
                    "texto"=>"Los datos del servicio se actualizaron correctamente",
                    "icono"=>"success"
                ];
            }else{
                $alerta=[
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"No hemos podido actualizar los datos del servicio",
                    "icono"=>"error"
                ];
            }
            return json_encode($alerta);
        }
    }