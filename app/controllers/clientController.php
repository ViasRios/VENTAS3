<?php
	namespace app\controllers;
	use app\models\mainModel;
	class clientController extends mainModel{
		/*----------  Controlador registrar cliente  ----------*/
		public function registrarClienteControlador(){

			# Almacenando datos#
		    $Idcliente=$this->limpiarCadena($_POST['Idcliente']);
		    $Nombre=$this->limpiarCadena($_POST['Nombre']);
		    $Numero=$this->limpiarCadena($_POST['Numero']);
		    $Colonia=$this->limpiarCadena($_POST['Colonia']);
		    $Email=$this->limpiarCadena($_POST['Email']);

		    # Verificando campos obligatorios #
            if($Idcliente=="" || $Nombre=="" || $Numero=="" || $Colonia=="" || $Email=="" ){
            	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-Z0-9-]{7,30}",$Idcliente)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El ID del cliente no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$Nombre)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NOMBRE no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		     if($this->verificarDatos("[a-zA-Z0-9-]{7,30}",$Numero)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NÚMERO no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{4,30}",$Colonia)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La COLONIA no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{4,30}",$Email)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El EMAIL no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }



		    # Verificando email #
		    if($Email!=""){
				if(filter_var($Email, FILTER_VALIDATE_EMAIL)){
					$check_email=$this->ejecutarConsulta("SELECT Email FROM clientes WHERE Email='$Email'");
					if($check_email->rowCount()>0){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error inesperado",
							"texto"=>"El EMAIL que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente",
							"icono"=>"error"
						];
						return json_encode($alerta);
						exit();
					}
				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Ha ingresado un correo electrónico no valido",
						"icono"=>"error"
					];
					return json_encode($alerta);
					exit();
				}
            }

		    $cliente_datos_reg=[
				[
					"campo_nombre"=>"Idcliente",
					"campo_marcador"=>":Id",
					"campo_valor"=>$Idcliente
				],
				[
					"campo_nombre"=>"Nombre",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$Nombre
				],
				[
					"campo_nombre"=>"Numero",
					"campo_marcador"=>":Apellido",
					"campo_valor"=>$Numero
				],
				[
					"campo_nombre"=>"Colonia",
					"campo_marcador"=>":Colonia",
					"campo_valor"=>$Colonia
				],
				[
					"campo_nombre"=>"Email",
					"campo_marcador"=>":Ciudad",
					"campo_valor"=>$Email
				]
			];

			$registrar_cliente=$this->guardarDatos("clientes",$cliente_datos_reg);

			if($registrar_cliente->rowCount()==1){
				$alerta=[
					"tipo"=>"limpiar",
					"titulo"=>"Cliente registrado",
					"texto"=>"El cliente ".$Nombre." se registro con exito",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No se pudo registrar el cliente, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}


		/*----------  Controlador listar cliente  ----------*/
		public function listarClienteControlador($pagina,$registros,$url,$busqueda){

			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);

			$url=$this->limpiarCadena($url);
			$url=APP_URL.$url."/";

			$busqueda=$this->limpiarCadena($busqueda);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

		/*	if(isset($busqueda) && $busqueda!=""){

				$consulta_datos="SELECT * FROM clientes WHERE ((Idcliente!='0') AND (Nombre LIKE '%$busqueda%' OR Idcliente LIKE '%$busqueda%')) ORDER BY Idcliente ASC LIMIT $inicio,$registros";

				$consulta_total="SELECT COUNT(Idcliente) FROM clientes WHERE ((Idcliente!='0') AND (Nombre LIKE '%$busqueda%' OR Idcliente LIKE '%$busqueda%'))";

			}else{

				$consulta_datos="SELECT * FROM clientes WHERE Idcliente!='0' ORDER BY Idcliente ASC LIMIT $inicio,$registros";

				$consulta_total="SELECT COUNT(Idcliente) FROM clientes WHERE Idcliente!='0'";

			}  */

			$campo = $_SESSION['clientSearch_campo'] ?? 'Idcliente';

			// validar campo permitido
			$campos_validos = ['Idcliente', 'Nombre'];
				if (!in_array($campo, $campos_validos)) {
    		$campo = 'Idcliente';
			}

			if (isset($busqueda) && $busqueda != "") {

    		// Se realiza la consulta buscando tanto por Idcliente como por Nombre
			$consulta_datos = "SELECT * FROM clientes WHERE $campo LIKE '%$busqueda%' ORDER BY Idcliente DESC LIMIT $inicio,$registros";

    		// Consulta para contar los registros encontrados
    		$consulta_total = "SELECT COUNT(Idcliente) FROM clientes WHERE $campo LIKE '%$busqueda%'";

			} else {
    		// Si no hay búsqueda, mostrar todos los clientes
    		$consulta_datos = "SELECT * FROM clientes ORDER BY Idcliente DESC LIMIT $inicio,$registros";
    		$consulta_total = "SELECT COUNT(Idcliente) FROM clientes";
			}

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();

			$total = $this->ejecutarConsulta($consulta_total);
			$total = (int) $total->fetchColumn();

			$numeroPaginas =ceil($total/$registros);

			$tabla.='
		        <div class="table-container">
		        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
		            <thead>
		                <tr>
							
		                    <th class="has-text-centered">ID</th>
		                    <th class="has-text-centered">Nombre</th>
		                    <th class="has-text-centered">Numero</th>
							<th class="has-text-centered">Colonia</th>
		                    <th class="has-text-centered">Email</th>
							
		                    <th class="has-text-centered">Actualizar</th>
		                    <th class="has-text-centered">Eliminar</th>
		                </tr>
		            </thead>
		            <tbody>
		    ';

		    if($total>=1 && $pagina<=$numeroPaginas){
				$contador=$inicio+1;
				$pag_inicio=$inicio+1;
				foreach($datos as $rows){
					$tabla.='
						<tr class="has-text-centered" >
							
							<td>'.$rows['Idcliente'].'</td>
							<td>'.$rows['Nombre'].' </td>
							<td>'.$rows['Numero'].' </td>
							<td>'.$rows['Colonia'].' </td>
							<td>'.$rows['Email'].' </td>
			                <td>
			                    <a href="'.APP_URL.'clientUpdate/'.$rows['Idcliente'].'/" class="button is-success is-rounded is-small">
			                    	<i class="fas fa-sync fa-fw"></i>
			                    </a>
			                </td>
			                <td>
			                	<form class="FormularioAjax" action="'.APP_URL.'app/ajax/clienteAjax.php" method="POST" autocomplete="off" >

			                		<input type="hidden" name="modulo_cliente" value="eliminar">
			                		<input type="hidden" name="Idcliente" value="'.$rows['Idcliente'].'">

			                    	<button type="submit" class="button is-danger is-rounded is-small">
			                    		<i class="far fa-trash-alt fa-fw"></i>
			                    	</button>
			                    </form>
			                </td>
						</tr>
					';
					$contador++;
				}
				$pag_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='
						<tr class="has-text-centered" >
			                <td colspan="6">
			                    <a href="'.$url.'1/" class="button is-link is-rounded is-small mt-4 mb-4">
			                        Haga clic acá para recargar el listado
			                    </a>
			                </td>
			            </tr>
					';
				}else{
					$tabla.='
						<tr class="has-text-centered" >
			                <td colspan="6">
			                    No hay registros en el sistema
			                </td>
			            </tr>
					';
				}
			}

			$tabla.='</tbody></table></div>';

			### Paginacion ###
			if($total>0 && $pagina<=$numeroPaginas){
				$tabla.='<p class="has-text-right">Mostrando clientes <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';

				$tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url,7);
			}

			return $tabla;
		}


		/*----------  Controlador eliminar cliente  ----------*/
		public function eliminarClienteControlador(){

			$id=$this->limpiarCadena($_POST['Idcliente']);

			if($id==1){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos eliminar el cliente principal del sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
			}

			# Verificando cliente #
		    $datos=$this->ejecutarConsulta("SELECT * FROM clientes WHERE Idcliente='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el cliente en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Verificando ventas #
		    $check_ventas=$this->ejecutarConsulta("SELECT Idcliente FROM sistema WHERE Idcliente='$id' LIMIT 1");
		    if($check_ventas->rowCount()>0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos eliminar el cliente del sistema ya que tiene ventas asociadas",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    $eliminarCliente=$this->eliminarRegistro("clientes","Idcliente",$id);

		    if($eliminarCliente->rowCount()==1){

		        $alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Cliente eliminado",
					"texto"=>"El cliente ".$datos['Nombre']."  ha sido eliminado del sistema correctamente",
					"icono"=>"success"
				];

		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido eliminar el cliente ".$datos['Nombre']." del sistema, por favor intente nuevamente",
					"icono"=>"error"
				];
		    }

		    return json_encode($alerta);
		}


		/*----------  Controlador actualizar cliente  ----------*/
		public function actualizarClienteControlador(){

			$id=$this->limpiarCadena($_POST['Idcliente']);

			# Verificando cliente #
		    $datos=$this->ejecutarConsulta("SELECT * FROM clientes WHERE Idcliente='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el cliente en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Almacenando datos#
		    $Idcliente=$this->limpiarCadena($_POST['Idcliente']);
			$Nombre=$this->limpiarCadena($_POST['Nombre']);
		    $Numero=$this->limpiarCadena($_POST['Numero']);
		    $Colonia=$this->limpiarCadena($_POST['Colonia']);  
		    $Email=$this->limpiarCadena($_POST['Email']);

		    # Verificando campos obligatorios #
			if($Idcliente=="" || $Nombre=="" || $Numero=="" || $Colonia=="" || $Email=="" ){
			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-Z0-9-]{7,30}",$Idcliente)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El ID del cliente no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$Nombre)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NOMBRE no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		     if($this->verificarDatos("[a-zA-Z0-9-]{7,30}",$Numero)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NÚMERO no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{4,30}",$Colonia)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La COLONIA no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }

		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{4,30}",$Email)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El EMAIL no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }



		    # Verificando email #
		    if($Email!=""){
				if(filter_var($Email, FILTER_VALIDATE_EMAIL)){
					$check_email=$this->ejecutarConsulta("SELECT Email FROM clientes WHERE Email='$Email'");
					if($check_email->rowCount()>0){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error inesperado",
							"texto"=>"El EMAIL que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente",
							"icono"=>"error"
						];
						return json_encode($alerta);
						exit();
					}
				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Ha ingresado un correo electrónico no valido",
						"icono"=>"error"
					];
					return json_encode($alerta);
					exit();
				}
            }

		    if($Numero!=""){
		    	if($this->verificarDatos("[0-9()+]{8,20}",$Numero)){
			    	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"El TELEFONO no coincide con el formato solicitado",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
			    }
		    }


			# Verificando email #
		    if($Email!="" && $datos['Email']!=$Email){
				if(filter_var($Email, FILTER_VALIDATE_EMAIL)){
					$check_email=$this->ejecutarConsulta("SELECT Email FROM clientes WHERE Email='$Email'");
					if($check_email->rowCount()>0){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error inesperado",
							"texto"=>"El EMAIL que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente",
							"icono"=>"error"
						];
						return json_encode($alerta);
						exit();
					}
				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Ha ingresado un correo electrónico no valido",
						"icono"=>"error"
					];
					return json_encode($alerta);
					exit();
				}
            }

            $cliente_datos_up=[
					[
					"campo_nombre"=>"Idcliente",
					"campo_marcador"=>":Id",
					"campo_valor"=>$Idcliente
				],
				[
					"campo_nombre"=>"Nombre",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$Nombre
				],
				[
					"campo_nombre"=>"Numero",
					"campo_marcador"=>":Apellido",
					"campo_valor"=>$Numero
				],
				[
					"campo_nombre"=>"Colonia",
					"campo_marcador"=>":Colonia",
					"campo_valor"=>$Colonia
				],
				[
					"campo_nombre"=>"Email",
					"campo_marcador"=>":Ciudad",
					"campo_valor"=>$Email
				]
			];

			$condicion=[
				"condicion_campo"=>"Idcliente",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("clientes",$cliente_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Cliente actualizado",
					"texto"=>"Los datos del cliente ".$datos['Nombre']." se actualizaron correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido actualizar los datos del cliente ".$datos['Nombre']." por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

	}