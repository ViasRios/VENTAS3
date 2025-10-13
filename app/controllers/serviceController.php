<?php
	namespace app\controllers;
	use app\models\mainModel;
	class serviceController extends mainModel{
		/*----------  Controlador registrar servicio ----------*/
		public function registrarServicioControlador(){

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

		    if($this->verificarDatos("/^\d{1,9}(\.\d{1,2})?$/",$Costo)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El PRECIO DE COMPRA no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        
		    }

    		$servicio_datos_reg=[
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
			$registrar_servicio=$this->guardarDatos("servicios",$servicio_datos_reg);

			if($registrar_servicio->rowCount()==1){
				$alerta=[
					"tipo"=>"limpiar",
					"titulo"=>"Servicio registrado",
					"texto"=>"El servicio se registro con exito",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No se pudo registrar el servicio, por favor intente nuevamente",
					"icono"=>"error"
				];
			}
			return json_encode($alerta);
		}

		/*----------  Controlador listar servicio  ----------*/
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
				// Encabezado de tabla
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

			// Paginación
			if ($total > 0 && $pagina <= $numeroPaginas) {
				$tabla .= '<p class="has-text-right">Mostrando servicios <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
				$tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);
			}
			return $tabla;
		}

		/*----------  Controlador eliminar servicio  ----------*/
		public function eliminarServicioControlador(){

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

		    # Verificando ventas #
		/*    $check_ventas=$this->ejecutarConsulta("SELECT producto_id FROM venta_detalle WHERE producto_id='$id' LIMIT 1");
		    if($check_ventas->rowCount()>0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos eliminar el producto del sistema ya que tiene ventas asociadas",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    } */

		    $eliminarServicio=$this->eliminarRegistro("servicios","Idser",$id);

		    if($eliminarServicio->rowCount()==1){

		        $alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Servicio eliminado",
					"texto"=>"El servicio ha sido eliminado del sistema correctamente",
					"icono"=>"success"
				];

		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido eliminar el servicio del sistema",
					"icono"=>"error"
				];
		    }

		    return json_encode($alerta);
		}

		/*----------  Controlador actualizar servicio  ----------*/
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

		    if($this->verificarDatos("/^\d{1,9}(\.\d{1,2})?$/",$Costo)){
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


		/*----------  Controlador eliminar foto producto  ----------*/
	/*	public function eliminarFotoProductoControlador(){

			$id=$this->limpiarCadena($_POST['producto_id']);

			# Verificando producto #
		    $datos=$this->ejecutarConsulta("SELECT * FROM producto WHERE producto_id='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el producto en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Directorio de imagenes #
    		$img_dir="../views/productos/";

    		chmod($img_dir,0777);

    		if(is_file($img_dir.$datos['producto_foto'])){

		        chmod($img_dir.$datos['producto_foto'],0777);

		        if(!unlink($img_dir.$datos['producto_foto'])){
		            $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Error al intentar eliminar la foto del producto, por favor intente nuevamente",
						"icono"=>"error"
					];
					return json_encode($alerta);
		        	exit();
		        }
		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la foto del producto en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    $producto_datos_up=[
				[
					"campo_nombre"=>"producto_foto",
					"campo_marcador"=>":Foto",
					"campo_valor"=>""
				]
			];

			$condicion=[
				"condicion_campo"=>"producto_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("producto",$producto_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto eliminada",
					"texto"=>"La foto del producto '".$datos['producto_nombre']."' se elimino correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto eliminada",
					"texto"=>"No hemos podido actualizar algunos datos del producto '".$datos['producto_nombre']."', sin embargo la foto ha sido eliminada correctamente",
					"icono"=>"warning"
				];
			}

			return json_encode($alerta);
		}
		*/

		/*----------  Controlador actualizar foto producto  ----------*/
	/*	public function actualizarFotoProductoControlador(){

			$id=$this->limpiarCadena($_POST['producto_id']);

			# Verificando producto #
		    $datos=$this->ejecutarConsulta("SELECT * FROM producto WHERE producto_id='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el producto en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Directorio de imagenes #
    		$img_dir="../views/productos/";

    		# Comprobar si se selecciono una imagen #
    		if($_FILES['producto_foto']['name']=="" && $_FILES['producto_foto']['size']<=0){
    			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No ha seleccionado una foto para el producto",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
    		}

    		# Creando directorio #
	        if(!file_exists($img_dir)){
	            if(!mkdir($img_dir,0777)){
	                $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Error al crear el directorio",
						"icono"=>"error"
					];
					return json_encode($alerta);
	                exit();
	            } 
	        }

	        # Verificando formato de imagenes #
	        if(mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/png"){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
					"icono"=>"error"
				];
				return json_encode($alerta);
	            exit();
	        }

	        # Verificando peso de imagen #
	        if(($_FILES['producto_foto']['size']/1024)>5120){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La imagen que ha seleccionado supera el peso permitido",
					"icono"=>"error"
				];
				return json_encode($alerta);
	            exit();
	        }

	        # Nombre de la foto #
	        if($datos['producto_foto']!=""){
		        $foto=explode(".", $datos['producto_foto']);
		        $foto=$foto[0];
	        }else{
	        	$foto=$datos['producto_codigo']."_".rand(0,100);
	        }
	        

	        # Extension de la imagen #
	        switch(mime_content_type($_FILES['producto_foto']['tmp_name'])){
	            case 'image/jpeg':
	                $foto=$foto.".jpg";
	            break;
	            case 'image/png':
	                $foto=$foto.".png";
	            break;
	        }

	        chmod($img_dir,0777);

	        # Moviendo imagen al directorio #
	        if(!move_uploaded_file($_FILES['producto_foto']['tmp_name'],$img_dir.$foto)){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos subir la imagen al sistema en este momento",
					"icono"=>"error"
				];
				return json_encode($alerta);
	            exit();
	        }

	        # Eliminando imagen anterior #
	        if(is_file($img_dir.$datos['producto_foto']) && $datos['producto_foto']!=$foto){
		        chmod($img_dir.$datos['producto_foto'], 0777);
		        unlink($img_dir.$datos['producto_foto']);
		    }

		    $producto_datos_up=[
				[
					"campo_nombre"=>"producto_foto",
					"campo_marcador"=>":Foto",
					"campo_valor"=>$foto
				]
			];

			$condicion=[
				"condicion_campo"=>"producto_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("producto",$producto_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto actualizada",
					"texto"=>"La foto del producto '".$datos['producto_nombre']."' se actualizo correctamente",
					"icono"=>"success"
				];
			}else{

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto actualizada",
					"texto"=>"No hemos podido actualizar algunos datos del producto '".$datos['producto_nombre']."', sin embargo la foto ha sido actualizada",
					"icono"=>"warning"
				];
			}

			return json_encode($alerta);
		}
	} */
}
