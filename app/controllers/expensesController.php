<?php
	namespace app\controllers;
	use app\models\mainModel;
	class expensesController extends mainModel{
		/*----------  Controlador registrar gasto  ----------*/
		public function registrarGastoControlador(){
			# Almacenando datos#
		    $Idegreso=$this->limpiarCadena($_POST['Idegreso']);
		    $Efectivo=$this->limpiarCadena($_POST['Efectivo']);
		    $Descripcion=$this->limpiarCadena($_POST['Descripcion']);
			$Clasificacion=$this->limpiarCadena($_POST['Clasificacion']);
		    $Fecha=$this->limpiarCadena($_POST['Fecha']);
            $Hora=$this->limpiarCadena($_POST['Hora']);
            $Medio=$this->limpiarCadena($_POST['Medio']);
            $Estado=$this->limpiarCadena($_POST['Estado']);
		    # Verificando campos obligatorios #
            if($Efectivo=="" || $Descripcion=="" || $Clasificacion=="" ){
            	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
            }

            if($this->verificarDatos("/^\d{1,9}(\.\d{1,2})?$/",$Efectivo)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El EFECTIVO no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

            # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ., ]{4,150}",$Descripcion)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La DESCRIPCIÓN no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

			if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ., ]{4,150}",$Clasificacion)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La CLASIFICACIÓN no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

            $gastos_datos_reg=[
				[
					"campo_nombre"=>"Efectivo",
					"campo_marcador"=>":Efectivo",
					"campo_valor"=>$Efectivo
				],
				[
					"campo_nombre"=>"Descripcion",
					"campo_marcador"=>":Descripcion",
					"campo_valor"=>$Descripcion
				],
				[
					"campo_nombre"=>"Clasificacion",
					"campo_marcador"=>":Clasificacion",
					"campo_valor"=>$Clasificacion
				]
			];
			$registrar_gastos=$this->guardarDatos("egresos",$gastos_datos_reg);

			if($registrar_gastos->rowCount()==1){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Gasto registrado",
					"texto"=>"Los datos del gasto se registraron con exito",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No se pudo registrar los datos del gasto, por favor intente nuevamente",
					"icono"=>"error"
				];
			}
			return json_encode($alerta);
		}

        /*----------  Controlador listar gasto  ----------*/
		public function listarGastoControlador($pagina,$registros,$url,$busqueda,$Clasificacion){
			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);
			$url=$this->limpiarCadena($url);
			$url=APP_URL.$url."/";

			$busqueda=$this->limpiarCadena($busqueda);
			$Clasificacion = isset($_GET['Clasificacion']) ? $this->limpiarCadena($_GET['Clasificacion']) : '';
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

			$campo = $_SESSION['expensesSearch_campo'] ?? 'Idegreso';
			$campos_validos = ['Idegreso', 'Descripcion'];
			if (!in_array($campo, $campos_validos)) {
				$campo = 'Idegreso';
			}

			$where = "1=1"; // condición base
			if ($busqueda != "") {
				$where .= " AND $campo LIKE '%$busqueda%'";
			}
			if ($Clasificacion != "") {
				$where .= " AND Clasificacion LIKE '%$Clasificacion%'";
			}

			$consulta_datos = "SELECT * FROM egresos WHERE $where ORDER BY Idegreso DESC LIMIT $inicio, $registros";
			$consulta_total = "SELECT COUNT(Idegreso) FROM egresos WHERE $where";

			$datos = $this->ejecutarConsulta($consulta_datos)->fetchAll();
			$total = (int) $this->ejecutarConsulta($consulta_total)->fetchColumn();
			$numeroPaginas = ceil($total/$registros);

			### Filtro HTML
			$tabla .= '
			<form method="GET" action="'.$url.'" class="mb-4">
				<div class="field is-grouped">
					<div class="control">
						<div class="select is-small">
							<select name="Clasificacion">
								<option value="">-- Clasificación --</option>
								<option value="pago" '.((@$_GET['Clasificacion']=='pago') ? 'selected' : '').'>Pago</option>
								<option value="compra" '.((@$_GET['Clasificacion']=='compra') ? 'selected' : '').'>Compra</option>
								<option value="servicio" '.((@$_GET['Clasificacion']=='servicio') ? 'selected' : '').'>Servicio</option>
							</select>
						</div>
					</div>
					<div class="control">
						<button type="submit" class="button is-link is-small">
							<i class="fas fa-filter"></i>&nbsp; Filtrar
						</button>
					</div>
				</div>
			</form>';

			### Tabla de resultados
			$tabla .= '
			<div class="table-container">
			<table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
				<thead>
					<tr>
						<th class="has-text-centered">Efectivo</th>
						<th class="has-text-centered">Descripción</th>
						<th class="has-text-centered">Fecha</th>
						<th class="has-text-centered">Hora</th>
						<th class="has-text-centered">Clasificación</th>
						<th class="has-text-centered">Medio</th>
						<th class="has-text-centered">Estado</th>
						<th class="has-text-centered">Actualizar</th>
						<th class="has-text-centered">Eliminar</th>
					</tr>
				</thead>
				<tbody>';

			if($total>=1 && $pagina<=$numeroPaginas){
				$contador=$inicio+1;
				$pag_inicio=$inicio+1;
				foreach($datos as $rows){
					$tabla.='
					<tr class="has-text-centered">
						<td>'.$rows['Efectivo'].' </td>
						<td>'.$rows['Descripcion'].'</td>
						<td>'.$rows['Fecha'].'</td>
						<td>'.$rows['Hora'].'</td>
						<td>'.$rows['Clasificacion'].'</td>
						<td>'.$rows['Medio'].'</td>
						<td>'.$rows['Estado'].'</td>
						<td>
							<a href="'.APP_URL.'expensesUpdate/'.$rows['Idegreso'].'/" class="button is-success is-rounded is-small">
								<i class="fas fa-sync fa-fw"></i>
							</a>
						</td>
						<td>
							<form class="FormularioAjax" action="'.APP_URL.'app/ajax/expensesAjax.php" method="POST" autocomplete="off">
								<input type="hidden" name="modulo_gasto" value="eliminar">
								<input type="hidden" name="Idegreso" value="'.$rows['Idegreso'].'">
								<button type="submit" class="button is-danger is-rounded is-small">
									<i class="far fa-trash-alt fa-fw"></i>
								</button>
							</form>
						</td>
					</tr>';
					$contador++;
				}
				$pag_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='
					<tr class="has-text-centered">
						<td colspan="9">
							<a href="'.$url.'1/" class="button is-link is-rounded is-small mt-4 mb-4">
								Haga clic acá para recargar el listado
							</a>
						</td>
					</tr>';
				}else{
					$tabla.='
					<tr class="has-text-centered">
						<td colspan="9">No hay registros en el sistema</td>
					</tr>';
				}
			}
			$tabla .= '</tbody></table></div>';

			### Paginación
			if($total>0 && $pagina<=$numeroPaginas){
				$tabla.='<p class="has-text-right">Mostrando registros <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un total de <strong>'.$total.'</strong></p>';
				$tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url,7);
			}
			return $tabla;
		}



		/*----------  Controlador eliminar gasto  ----------*/
		public function eliminarGastoControlador(){
			$id=$this->limpiarCadena($_POST['Idegreso']);
			if($id==1){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos eliminar el gasto del sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

			# Verificando gasto #
		    $datos=$this->ejecutarConsulta("SELECT * FROM egresos WHERE Idegreso='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el gasto en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos=$datos->fetch();
		    }

		    $eliminarGasto=$this->eliminarRegistro("egresos","Idegreso",$id);

		    if($eliminarGasto->rowCount()==1){
		        $alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Gasto eliminado",
					"texto"=>"El gasto ha sido eliminado del sistema correctamente",
					"icono"=>"success"
				];
		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido eliminar el gasto del sistema, por favor intente nuevamente",
					"icono"=>"error"
				];
		    }

		    return json_encode($alerta);
		}

		/*----------  Controlador actualizar gasto  ----------*/
		public function actualizarGastoControlador(){
			$id=$this->limpiarCadena($_POST['Idegreso']);
			# Verificando gasto #
		    $datos=$this->ejecutarConsulta("SELECT * FROM egresos WHERE Idegreso='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el gasto en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos=$datos->fetch();
		    }
		    # Almacenando datos#
            $Idegreso=$this->limpiarCadena($_POST['Idegreso']);
		    $Efectivo=$this->limpiarCadena($_POST['Efectivo']);
		    $Descripcion=$this->limpiarCadena($_POST['Descripcion']);
			$Clasificacion=$this->limpiarCadena($_POST['Clasificacion']);
		    $Fecha=$this->limpiarCadena($_POST['Fecha']);
            $Hora=$this->limpiarCadena($_POST['Hora']);
            $Medio=$this->limpiarCadena($_POST['Medio']);
            $Estado=$this->limpiarCadena($_POST['Estado']);

		    # Verificando campos obligatorios #
            if($Efectivo=="" || $Descripcion==""|| $Clasificacion==""){
            	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
            }

            if($this->verificarDatos("/^\d{1,9}(\.\d{1,2})?$/",$Efectivo)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El EFECTIVO no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

            # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ., ]{4,150}",$Descripcion)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La DESCRIPCION no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

			if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ., ]{4,150}",$Clasificacion)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La CLASIFICACIÓN no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

            $gastos_datos_up=[
				[
					"campo_nombre"=>"Efectivo",
					"campo_marcador"=>":Efectivo",
					"campo_valor"=>$Efectivo
				],
				[
					"campo_nombre"=>"Descripcion",
					"campo_marcador"=>":Descripcion",
					"campo_valor"=>$Descripcion
				],
				[
					"campo_nombre"=>"Clasificacion",
					"campo_marcador"=>":Clasificacion",
					"campo_valor"=>$Clasificacion
				]
			];

			$condicion=[
				"condicion_campo"=>"empresa_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("egresos",$gastos_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Gasto actualizado",
					"texto"=>"Los datos del gasto se actualizaron correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido actualizar los datos del gasto, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

	}