<?php
	namespace app\controllers;
	use app\models\mainModel;
	header('Content-Type: application/json');

	class personalController extends mainModel{
		/*----------  Controlador registrar personal  ----------*/
		public function registrarPersonalControlador(){
			# Almacenando datos#
		 #   $Idasesor=$this->limpiarCadena($_POST['Idasesor']);
		    $Nombre=$this->limpiarCadena($_POST['Nombre']);
		    $Telefono=$this->limpiarCadena($_POST['Telefono']);
		    $Puesto=$this->limpiarCadena($_POST['Puesto']);
		 //   $Prioridad=$this->limpiarCadena($_POST['Prioridad']);
			$usuario=$this->limpiarCadena($_POST['usuario']);
		    $email=$this->limpiarCadena($_POST['email']);
			$clave1=$this->limpiarCadena($_POST['usuario_clave_1']);
		    $clave2=$this->limpiarCadena($_POST['usuario_clave_2']);
	
		    # Verificando campos obligatorios #
		    if( $Nombre=="" || $Telefono=="" || $Puesto=="" || $usuario=="" || $clave1=="" || $clave2==""){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$Nombre)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NOMBRE no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		      #  exit(); #
		    }

		    if($this->verificarDatos("^\d{4,20}$",$Telefono)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El TELEFONO no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

			if($this->verificarDatos("[a-zA-Z0-9]{4,20}",$usuario)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El USUARIO no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

			if($this->verificarDatos("[a-zA-Z0-9$@.-]{5,100}",$clave1) || $this->verificarDatos("[a-zA-Z0-9$@.-]{5,100}",$clave2)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"Las CLAVES no coinciden con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

		    # Verificando email #
		    if($email!=""){
				if(filter_var($email, FILTER_VALIDATE_EMAIL)){
					$check_email=$this->ejecutarConsulta("SELECT email FROM personal WHERE email='$email'");
					if($check_email->rowCount()>0){
						$alerta=[
							"tipo"=>"simple",
							"titulo"=>"Ocurrió un error inesperado",
							"texto"=>"El EMAIL que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente",
							"icono"=>"error"
						];
						return json_encode($alerta);
					}
				}else{
					$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Ha ingresado un correo electrónico no valido",
						"icono"=>"error"
					];
					return json_encode($alerta);
				}
            } 

            # Verificando claves #
            if($clave1!=$clave2){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"Las contraseñas que acaba de ingresar no coinciden, por favor verifique e intente nuevamente",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}else{
				$clave=password_hash($clave1,PASSWORD_BCRYPT,["cost"=>10]);
            }

            # Verificando usuario #
		    $check_usuario=$this->ejecutarConsulta("SELECT usuario FROM personal WHERE usuario='$usuario'");
		    if($check_usuario->rowCount()>0){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El USUARIO ingresado ya se encuentra registrado, por favor elija otro",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

		    # Directorio de imagenes #
    		$img_dir="../views/fotos/";

    		# Comprobar si se selecciono una imagen #
    		if($_FILES['personal_foto']['name']!="" && $_FILES['personal_foto']['size']>0){

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
		            } 
		        }

		        # Verificando formato de imagenes #
		        if(mime_content_type($_FILES['personal_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['personal_foto']['tmp_name'])!="image/png"){
		        	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
		          #  exit(); #
		        }

		        # Verificando peso de imagen #
		        if(($_FILES['personal_foto']['size']/1024)>5120){
		        	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"La imagen que ha seleccionado supera el peso permitido",
						"icono"=>"error"
					];
					return json_encode($alerta);
		          #  exit(); #
		        }

		        # Nombre de la foto #
		        $foto=str_ireplace(" ","_",$Nombre);
		        $foto=$foto."_".rand(0,100);

		        # Extension de la imagen #
		        switch(mime_content_type($_FILES['personal_foto']['tmp_name'])){
		            case 'image/jpeg':
		                $foto=$foto.".jpg";
		            break;
		            case 'image/png':
		                $foto=$foto.".png";
		            break;
		        }

		        chmod($img_dir,0777);

		        # Moviendo imagen al directorio #
		        if(!move_uploaded_file($_FILES['personal_foto']['tmp_name'],$img_dir.$foto)){
		        	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"No podemos subir la imagen al sistema en este momento",
						"icono"=>"error"
					];
					return json_encode($alerta);
		          #  exit(); #
		        }

    		}else{
    			$foto="";
    		}


		    $usuario_datos_reg=[
				
				[
					"campo_nombre"=>"Nombre",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$Nombre
				],
				[
					"campo_nombre"=>"Telefono",
					"campo_marcador"=>":Telefono",
					"campo_valor"=>$Telefono
				],
				[
					"campo_nombre"=>"Puesto",
					"campo_marcador"=>":Puesto",
					"campo_valor"=>$Puesto
				],
				[
					"campo_nombre"=>"usuario",
					"campo_marcador"=>":usuario",
					"campo_valor"=>$usuario
				],
				[
					"campo_nombre"=>"email",
					"campo_marcador"=>":email",
					"campo_valor"=>$email
				],
				[
					"campo_nombre"=>"clave1",
					"campo_marcador"=>":clave1",
					"campo_valor"=>$clave
				],
				[
					"campo_nombre"=>"habilitado",
					"campo_marcador"=>":habilitado",
					"campo_valor"=>1
				]
			];

			$registrar_personal=$this->guardarDatos("personal",$usuario_datos_reg);

			if($registrar_personal->rowCount()==1){
				$alerta=[
					"tipo"=>"limpiar",
					"titulo"=>"Usuario registrado",
					"texto"=>"El usuario ".$Nombre." se registro con exito",
					"icono"=>"success"
				];
			}else{
				
				if(is_file($img_dir.$foto)){
		            chmod($img_dir.$foto,0777);
		            unlink($img_dir.$foto);
		        }

				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No se pudo registrar el usuario, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

		/*----------  Controlador listar usuario  ----------*/
		public function listarPersonalControlador($pagina,$registros,$url,$busqueda){

			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);

			$url=$this->limpiarCadena($url);
			$url=APP_URL.$url."/";

			$busqueda=$this->limpiarCadena($busqueda);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

		/*	if(isset($busqueda) && $busqueda!=""){

				$consulta_datos="SELECT * FROM personal WHERE ((Idasesor!='".$_SESSION['id']."' AND Idasesor!='0') AND (Nombre LIKE '%$busqueda%' OR usuario LIKE '%$busqueda%' OR Telefono LIKE '%$busqueda%')) ORDER BY Nombre ASC LIMIT $inicio,$registros";

				$consulta_total="SELECT COUNT(Idasesor) FROM personal WHERE ((Idasesor!='".$_SESSION['id']."' AND Idasesor!='0') AND (Nombre LIKE '%$busqueda%' OR usuario LIKE '%$busqueda%' OR Telefono LIKE '%$busqueda%'))";

			}else{

				$consulta_datos="SELECT * FROM personal WHERE Idasesor!='".$_SESSION['id']."' AND Idasesor!='0' ORDER BY Nombre ASC LIMIT $inicio,$registros";

				$consulta_total="SELECT COUNT(Idasesor) FROM personal WHERE Idasesor!='".$_SESSION['id']."' AND Idasesor!='0'";

			} */

			$campo = $_SESSION['userSearch_campo'] ?? 'Idasesor';

			// validar campo permitido
			$campos_validos = ['Idasesor', 'Nombre'];
				if (!in_array($campo, $campos_validos)) {
    		$campo = 'Idasesor';
			}

			if (isset($busqueda) && $busqueda != "") {

    		// Se realiza la consulta buscando tanto por Idcliente como por Nombre
    		$consulta_datos = "SELECT * FROM personal WHERE Idasesor LIKE '%$busqueda%' OR Nombre LIKE '%$busqueda%' ORDER BY Idasesor DESC LIMIT $inicio,$registros";
    
    		// Consulta para contar los registros encontrados
    		$consulta_total = "SELECT COUNT(Idasesor) FROM personal WHERE Idasesor LIKE '%$busqueda%' OR Nombre LIKE '%$busqueda%'";

			} else {
    		// Si no hay búsqueda, mostrar todos los clientes
    		$consulta_datos = "SELECT * FROM personal ORDER BY Idasesor DESC LIMIT $inicio,$registros";
    		$consulta_total = "SELECT COUNT(Idasesor) FROM personal";
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
		                    <th class="has-text-centered">Teléfono</th>
		                    <th class="has-text-centered">Puesto</th>
							<th class="has-text-centered">Prioridad</th>
							<th class="has-text-centered">Usuario</th>
							<th class="has-text-centered">Email</th>
		                    <th class="has-text-centered">Foto</th>
		                    <th class="has-text-centered">Actualizar</th>
		                    <th class="has-text-centered">Eliminar</th>
							<th class="has-text-centered">Habilitado</th>
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
							<td>'.$rows['Idasesor'].' </td>
							<td>'.$rows['Nombre'].' </td>
							<td>'.$rows['Telefono'].'</td>
							<td>'.$rows['Puesto'].'</td>
							<td>'.$rows['Prioridad'].'</td>
							<td>'.$rows['usuario'].'</td>
							<td>'.$rows['email'].'</td>
							<td>
			                    <a href="'.APP_URL.'userPhoto/'.$rows['Idasesor'].'/" class="button is-info is-rounded is-small">
			                    	<i class="fas fa-camera fa-fw"></i>
			                    </a>
			                </td>
			                <td>
			                    <a href="'.APP_URL.'userUpdate/'.$rows['Idasesor'].'/" class="button is-success is-rounded is-small">
			                    	<i class="fas fa-sync fa-fw"></i>
			                    </a>
			                </td>
			                <td>
								<form class="FormularioAjax" action="'.APP_URL.'app/ajax/personalAjax.php" method="POST" autocomplete="off" >
									<input type="hidden" name="modulo_personal" value="eliminar">
									<input type="hidden" name="Idasesor" value="'.$rows['Idasesor'].'">
									<button type="submit" class="button is-danger is-rounded is-small">
										<i class="far fa-trash-alt fa-fw"></i>
									</button>
								</form>
			                </td>
							<td>
								<form class="FormularioAjax" action="'.APP_URL.'app/ajax/personalAjax.php" method="POST" autocomplete="off" style="margin-top:5px;">
									<input type="hidden" name="modulo_personal" value="eliminar">
									<input type="hidden" name="Idasesor" value="'.$rows['Idasesor'].'">
									<input type="hidden" name="accion" value="'.($rows['habilitado'] ? 'deshabilitar' : 'habilitar').'">
									<button type="submit" class="button is-'.($rows['habilitado'] ? 'warning' : 'primary').' is-rounded is-small">
										'.($rows['habilitado'] ? '<i class="fas fa-user-slash"></i> Deshabilitar' : '<i class="fas fa-user-check"></i> Habilitar').'
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
			                <td colspan="7">
			                    <a href="'.$url.'1/" class="button is-link is-rounded is-small mt-4 mb-4">
			                        Haga clic acá para recargar el listado
			                    </a>
			                </td>
			            </tr>
					';
				}else{
					$tabla.='
						<tr class="has-text-centered" >
			                <td colspan="7">
			                    No hay registros en el sistema
			                </td>
			            </tr>
					';
				}
			}

			$tabla.='</tbody></table></div>';

			### Paginacion ###
			if($total>0 && $pagina<=$numeroPaginas){
				$tabla.='<p class="has-text-right">Mostrando usuarios <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';

				$tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url,7);
			}

			return $tabla;
		}


		/*----------  Controlador eliminar usuario  ----------*/
		public function eliminarPersonalControlador(){

			$id=$this->limpiarCadena($_POST['Idasesor']);

			if($id==1){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos eliminar el personal principal del sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		     #   exit(); #
			}

			# Verificando usuario #
		    $datos=$this->ejecutarConsulta("SELECT * FROM personal WHERE Idasesor='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el usuario en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		      #  exit(); #
		    }else{
		    	$datos=$datos->fetch();
		    }

			# Cambiar estado de habilitado/deshabilitado si se solicita
			if (isset($_POST['accion']) && ($_POST['accion'] == 'habilitar' || $_POST['accion'] == 'deshabilitar')) {
				$nuevo_estado = ($_POST['accion'] == 'habilitar') ? 1 : 0;
				$usuario_datos_up = [
					[
						"campo_nombre" => "habilitado",
						"campo_marcador" => ":habilitado",
						"campo_valor" => $nuevo_estado
					]
				];
				$condicion = [
					"condicion_campo" => "Idasesor",
					"condicion_marcador" => ":ID",
					"condicion_valor" => $id
				];
				$this->actualizarDatos("personal", $usuario_datos_up, $condicion);
				if ($nuevo_estado === 0 && $id == $_SESSION['id']) {
					session_unset();
					setcookie(session_name(), '', time() - 3600, '/');
					session_destroy();
				}
				$alerta = [
					"tipo" => "recargar",
					"titulo" => $nuevo_estado ? "Usuario habilitado" : "Usuario deshabilitado",
					"texto" => "El usuario " . $datos['usuario'] . " ha sido " . ($nuevo_estado ? "habilitado" : "deshabilitado") . " correctamente.",
					"icono" => "success"
				];
				return json_encode($alerta);
			}

		    $eliminarPersonal=$this->eliminarRegistro("personal","Idasesor",$id);

		    if($eliminarPersonal->rowCount()==1){

		    	if(is_file("../views/fotos/".$datos['personal_foto'])){
		            chmod("../views/fotos/".$datos['personal_foto'],0777);
		            unlink("../views/fotos/".$datos['personal_foto']);
		        }

		        $alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Usuario eliminado",
					"texto"=>"El usuario ".$datos['usuario']." ha sido eliminado del sistema correctamente",
					"icono"=>"success"
				];

		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido eliminar el usuario ".$datos['usuario']." del sistema, por favor intente nuevamente",
					"icono"=>"error"
				];
		    }

		    return json_encode($alerta);
		}


		/*----------  Controlador actualizar usuario  ----------*/
		public function actualizarPersonalControlador() {
			$id = $this->limpiarCadena($_POST['Idasesor']);
			// Obtenemos los datos actuales del usuario
			$datos = $this->ejecutarConsulta("SELECT * FROM personal WHERE Idasesor='$id'")->fetchAll(\PDO::FETCH_ASSOC);
			// Verificar si el usuario fue encontrado
			if (empty($datos)) {
				$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "No se encontraron datos para este asesor.",
					"icono" => "error"
				];
				return json_encode($alerta);
			}

			// Si el usuario no ha cambiado, mantenemos el valor actual
			$usuario = isset($_POST['usuario']) ? $this->limpiarCadena($_POST['usuario']) : $datos[0]['usuario'];
			$email = isset($_POST['email']) ? $this->limpiarCadena($_POST['email']) : $datos[0]['email'];  // Usamos el valor actual si no se cambia
			$Telefono = isset($_POST['Telefono']) ? $this->limpiarCadena($_POST['Telefono']) : '';
			$clave1 = isset($_POST['usuario_clave_1']) ? $this->limpiarCadena($_POST['usuario_clave_1']) : '';

			// Verificar campos obligatorios
			if ($id == "" || $Telefono == "" || $usuario == "") {
				$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "No has llenado todos los campos que son obligatorios",
					"icono" => "error"
				];
				return json_encode($alerta);
			}

			// Verificar si el teléfono tiene el formato adecuado
			if ($this->verificarDatos("^\d{10}$", $Telefono)) {
				$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "El TELEFONO no coincide con el formato solicitado",
					"icono" => "error"
				];
				return json_encode($alerta);
			}

			// Verificar si el email ya está registrado en otro usuario
			if ($email != "" && $datos[0]['email'] != $email) {
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$check_email = $this->ejecutarConsulta("SELECT email FROM personal WHERE email='$email'");
					if ($check_email->rowCount() > 0) {
						$alerta = [
							"tipo" => "simple",
							"titulo" => "Ocurrió un error inesperado",
							"texto" => "El EMAIL que acaba de ingresar ya se encuentra registrado en el sistema, por favor verifique e intente nuevamente",
							"icono" => "error"
						];
						return json_encode($alerta);
					}
				} else {
					$alerta = [
						"tipo" => "simple",
						"titulo" => "Ocurrió un error inesperado",
						"texto" => "Ha ingresado un correo electrónico no válido",
						"icono" => "error"
					];
					return json_encode($alerta);
				}
			}

			// Si el usuario ha cambiado, validamos si el nuevo usuario ya existe
			if ($usuario != $datos[0]['usuario']) {
				$check_usuario = $this->ejecutarConsulta("SELECT usuario FROM personal WHERE usuario='$usuario'");
				if ($check_usuario->rowCount() > 0) {
					$alerta = [
						"tipo" => "simple",
						"titulo" => "Ocurrió un error inesperado",
						"texto" => "El USUARIO ingresado ya se encuentra registrado, por favor elija otro",
						"icono" => "error"
					];
					return json_encode($alerta);  // Si el usuario ya existe, muestra un error
				}
			}

			// Si se ingresa una nueva clave, la encriptamos
			if ($clave1 != "") {
				// Validar si la clave1 tiene el formato adecuado
				if ($this->verificarDatos("[a-zA-Z0-9$@.-]{7,100}", $clave1)) {
					$alerta = [
						"tipo" => "simple",
						"titulo" => "Ocurrió un error inesperado",
						"texto" => "La CLAVE no coincide con el formato solicitado",
						"icono" => "error"
					];
					return json_encode($alerta);
				}

				// Si se ingresa una nueva clave, la encriptamos
				$clave = password_hash($clave1, PASSWORD_BCRYPT, ["cost" => 10]);
			} else {
				// Si no se ingresa una nueva clave, mantenemos la antigua
				$clave = $datos[0]['clave'];
			}

			// Ahora, creamos el arreglo con los nuevos datos que se van a actualizar
			$usuario_datos_up = [
				[
					"campo_nombre" => "Telefono",
					"campo_marcador" => ":Telefono",
					"campo_valor" => $Telefono
				],
				[
					"campo_nombre" => "usuario",
					"campo_marcador" => ":usuario",
					"campo_valor" => $usuario
				],
				[
					"campo_nombre" => "email",
					"campo_marcador" => ":email",
					"campo_valor" => $email
				],
				[
					"campo_nombre" => "clave1",
					"campo_marcador" => ":clave1",
					"campo_valor" => $clave
				]
			];

			// Condición para la actualización (usamos el Id del asesor)
			$condicion = [
				"condicion_campo" => "Idasesor",
				"condicion_marcador" => ":ID",
				"condicion_valor" => $id
			];

			// Llamada a la función para actualizar los datos
			if ($this->actualizarDatos("personal", $usuario_datos_up, $condicion)) {
				$alerta = [
					"tipo" => "recargar",
					"titulo" => "Usuario actualizado",
					"texto" => "Los datos del usuario se actualizaron correctamente",
					"icono" => "success"
				];
			} else {
				$alerta = [
					"tipo" => "simple",
					"titulo" => "Ocurrió un error inesperado",
					"texto" => "No hemos podido actualizar los datos del usuario, por favor intente nuevamente",
					"icono" => "error"
				];
			}

			return json_encode($alerta);

		}


		/*----------  Controlador eliminar foto personal  ----------*/
		public function eliminarFotoPersonalControlador(){

			$id=$this->limpiarCadena($_POST['Idasesor']);

			# Verificando usuario #
		    $datos=$this->ejecutarConsulta("SELECT * FROM personal WHERE Idasesor='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el usuario en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Directorio de imagenes #
    		$img_dir="../views/fotos/";

    		chmod($img_dir,0777);

    		if(is_file($img_dir.$datos['personal_foto'])){

		        chmod($img_dir.$datos['personal_foto'],0777);

		        if(!unlink($img_dir.$datos['personal_foto'])){
		            $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Error al intentar eliminar la foto del usuario, por favor intente nuevamente",
						"icono"=>"error"
					];
					return json_encode($alerta);
		        	
		        }
		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la foto del usuario en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        
		    }

		    $usuario_datos_up=[
				[
					"campo_nombre"=>"personal_foto",
					"campo_marcador"=>":Foto",
					"campo_valor"=>""
				]
			];

			$condicion=[
				"condicion_campo"=>"Idasesor",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("personal",$usuario_datos_up,$condicion)){

				if($id==$_SESSION['id']){
					$_SESSION['foto']="";
				}

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto eliminada",
					"texto"=>"La foto del usuario ".$datos['usuario']." se elimino correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto eliminada",
					"texto"=>"No hemos podido actualizar algunos datos del usuario ".$datos['usuario'].", sin embargo la foto ha sido eliminada correctamente",
					"icono"=>"warning"
				];
			}

			return json_encode($alerta);
		}


		/*----------  Controlador actualizar foto personal  ----------*/
		public function actualizarFotoPersonalControlador(){

			$id=$this->limpiarCadena($_POST['Idasesor']);

			# Verificando usuario #
		    $datos=$this->ejecutarConsulta("SELECT * FROM personal WHERE Idasesor='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el usuario en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Directorio de imagenes #
    		$img_dir="../views/fotos/";

    		# Comprobar si se selecciono una imagen #
    		if($_FILES['personal_foto']['name']=="" && $_FILES['personal_foto']['size']<=0){
    			$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No ha seleccionado una foto para el usuario",
					"icono"=>"error"
				];
				return json_encode($alerta);
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
	            } 
	        }

	        # Verificando formato de imagenes #
	        if(mime_content_type($_FILES['personal_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['personal_foto']['tmp_name'])!="image/png"){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La imagen que ha seleccionado es de un formato no permitido",
					"icono"=>"error"
				];
				return json_encode($alerta);
	        }

	        # Verificando peso de imagen #
	        if(($_FILES['personal_foto']['size']/1024)>5120){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La imagen que ha seleccionado supera el peso permitido",
					"icono"=>"error"
				];
				return json_encode($alerta);
	        }

	        # Nombre de la foto #
	        if($datos['personal_foto']!=""){
		        $foto=explode(".", $datos['personal_foto']);
		        $foto=$foto[0];
	        }else{
	        	$foto=str_ireplace(" ","_",$datos['Nombre']);
	        	$foto=$foto."_".rand(0,100);
	        }
	        

	        # Extension de la imagen #
	        switch(mime_content_type($_FILES['personal_foto']['tmp_name'])){
	            case 'image/jpeg':
	                $foto=$foto.".jpg";
	            break;
	            case 'image/png':
	                $foto=$foto.".png";
	            break;
	        }

	        chmod($img_dir,0777);

	        # Moviendo imagen al directorio #
	        if(!move_uploaded_file($_FILES['personal_foto']['tmp_name'],$img_dir.$foto)){
	            $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos subir la imagen al sistema en este momento",
					"icono"=>"error"
				];
				return json_encode($alerta);
	        }

	        # Eliminando imagen anterior #
	        if(is_file($img_dir.$datos['personal_foto']) && $datos['personal_foto']!=$foto){
		        chmod($img_dir.$datos['personal_foto'], 0777);
		        unlink($img_dir.$datos['personal_foto']);
		    }

		    $usuario_datos_up=[
				[
					"campo_nombre"=>"personal_foto",
					"campo_marcador"=>":Foto",
					"campo_valor"=>$foto
				]
			];

			$condicion=[
				"condicion_campo"=>"Idasesor",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("personal",$usuario_datos_up,$condicion)){

				if($id==$_SESSION['id']){
					$_SESSION['foto']=$foto;
				}

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto actualizada",
					"texto"=>"La foto del usuario ".$datos['usuario']."  se actualizo correctamente",
					"icono"=>"success"
				];
			}else{

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Foto actualizada",
					"texto"=>"No hemos podido actualizar algunos datos del usuario ".$datos['usuario'].", sin embargo la foto ha sido actualizada",
					"icono"=>"warning"
				];
			}

			return json_encode($alerta);
		}

	}