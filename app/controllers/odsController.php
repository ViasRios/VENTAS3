<?php

	namespace app\controllers;
	use app\models\mainModel;

	class odsController extends mainModel{

		/*----------  Controlador registrar ODS  ----------*/
		public function registrarOdsControlador(){
                $get = function($key, $default = '') {
				return isset($_POST[$key]) ? $_POST[$key] : $default;
			};
			// === Campos que tu form envía hoy ===
			$Idcliente    = $this->limpiarCadena($get('Idcliente',''));
			$Idasesor     = $this->limpiarCadena($get('Idasesor',''));

			$Tipo         = $this->limpiarCadena($get('Tipo',''));
			$Marca        = $this->limpiarCadena($get('Marca',''));
			$Modelo       = $this->limpiarCadena($get('Modelo',''));
			$Noserie      = $this->limpiarCadena($get('Noserie',''));
			$Color        = $this->limpiarCadena($get('Color',''));
			$Contrasena   = $this->limpiarCadena($get('Contrasena',''));

			$Respaldo     = $this->limpiarCadena($get('Respaldo',''));
			$Uso          = $this->limpiarCadena($get('Uso',''));
			$Carpeta      = $this->limpiarCadena($get('Carpeta',''));

			$Problema     = $this->limpiarCadena($get('Problema',''));
			$Inspeccion   = $this->limpiarCadena($get('Inspeccion',''));
			$Accesorios   = $this->limpiarCadena($get('Accesorios',''));

			// Fecha del form y hora actual (tu form no envía hora)
			$Fecha        = $this->limpiarCadena($get('Fecha', date('Y-m-d')));
			$Hora         = date('H:i:s');

			// “Respuesta en” unificado
			$Tiempo       = $this->limpiarCadena($get('Tiempo',''));

			// Status con validación
			$Status       = $this->limpiarCadena($get('Status','Recepcion'));
			$permitidos   = ['Recepcion','Diagnostico','Reparacion'];
			if (!in_array($Status, $permitidos)) { $Status = 'Recepcion'; }

			// Garantía / ODS anterior
			$Garantia     = $this->limpiarCadena($get('Garantia','0'));
			$Garantia     = ($Garantia==='1' || $Garantia===1) ? 1 : 0;
			$Odsanterior  = $this->limpiarCadena($get('Odsanterior',''));

			$Sucursal     = $this->limpiarCadena($get('Sucursal',''));
			$Componentes  = $this->limpiarCadena($get('Componentes',''));

			$Reparacion	 = $this->limpiarCadena($get('Reparacion',''));

			// === Validaciones mínimas (ajusta si quieres) ===
			if($Tipo==="" || $Marca==="" || $Modelo==="" || $Noserie===""){
				return [
					"success"=>false, "tipo"=>"simple", "titulo"=>"Datos incompletos",
					"texto"=>"Completa Tipo, Marca, Modelo, No. Serie.", "icono"=>"error"
				];
			}
			if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}",$Tipo)){
				return [
					"success"=>false, "tipo"=>"simple", "titulo"=>"Formato inválido",
					"texto"=>"El campo Tipo no coincide con el formato solicitado.", "icono"=>"error"
				];
			}

			// === Garantía / Odsanterior ===
			$pdo = \app\models\mainModel::conectar();

			if($Garantia===1){
				if($Odsanterior===""){
					return ["success"=>false,"tipo"=>"simple","titulo"=>"Falta ODS Anterior","texto"=>"Para garantía debes indicar la ODS anterior.","icono"=>"warning"];
				}
				$stmtChk = $pdo->prepare("SELECT Idods FROM ods WHERE Idods = :id LIMIT 1");
				$stmtChk->execute([":id"=>$Odsanterior]);
				if(!$stmtChk->fetchColumn()){
					return ["success"=>false,"tipo"=>"simple","titulo"=>"ODS Anterior no válida","texto"=>"La ODS anterior indicada ($Odsanterior) no existe.","icono"=>"error"];
				}
				$Odsanterior = (int)$Odsanterior;
			} else {
				// Tu BD no permite NULL y usa 0 cuando no aplica
				$Odsanterior = 0;
			}

			// === INSERT DIRECTO con la MISMA conexión (para tener lastInsertId correcto) ===
			$sql = "INSERT INTO ods
				(Idcliente, Idasesor, Tipo, Marca, Modelo, Noserie, Color, Contrasena,
				Respaldo, Uso, Carpeta, Problema, Inspeccion, Accesorios,
				Fecha, Hora, Tiempo, Status, Garantia, Odsanterior, Sucursal, Componentes, Reparacion)
				VALUES
				(:Idcliente,:Idasesor,:Tipo,:Marca,:Modelo,:Noserie,:Color,:Contrasena,
				:Respaldo,:Uso,:Carpeta,:Problema,:Inspeccion,:Accesorios,
				:Fecha,:Hora,:Tiempo,:Status,:Garantia,:Odsanterior,:Sucursal,:Componentes, :Reparacion)";

			$stmt = $pdo->prepare($sql);
			$ok = $stmt->execute([
				// Si Idcliente/Idasesor NO admiten NULL en tu BD, reemplaza null por 0 o quítalos del INSERT
				':Idcliente'    => ($Idcliente !== '' ? $Idcliente : null),
				':Idasesor'     => ($Idasesor  !== '' ? $Idasesor  : null),

				':Tipo'         => $Tipo,
				':Marca'        => $Marca,
				':Modelo'       => $Modelo,
				':Noserie'      => $Noserie,
				':Color'        => $Color,
				':Contrasena'   => $Contrasena,

				':Respaldo'     => $Respaldo,
				':Uso'          => $Uso,
				':Carpeta'      => $Carpeta,

				':Problema'     => $Problema,
				':Inspeccion'   => $Inspeccion,
				':Accesorios'   => $Accesorios,

				':Fecha'        => $Fecha,
				':Hora'         => $Hora,
				':Tiempo'       => $Tiempo,

				':Status'       => $Status,
				':Garantia'     => $Garantia,
				':Odsanterior'  => $Odsanterior,
				':Sucursal'     => $Sucursal,
				':Componentes'  => $Componentes,
				':Reparacion'   => $Reparacion
			]);

			if(!$ok){
				return ["success"=>false,"tipo"=>"simple","titulo"=>"Error","texto"=>"No se pudo registrar la ODS.","icono"=>"error"];
			}

			$idInsertado = (int)$pdo->lastInsertId();               // ← ID correcto
			$pdf_url = APP_URL . "app/views/content/odsPrint.php?id=" . $idInsertado;


			header("Location: " . $pdf_url);
			exit();

		}
        //return json_encode($alerta);
		
		/*----------  Controlador listar ODS  ----------*/
		public function listarOdsControlador($pagina,$registros,$url,$busqueda){

			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);

			$url=$this->limpiarCadena($url);
			$url=APP_URL.$url."/";

			$busqueda=$this->limpiarCadena($busqueda);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

			$campo = $_SESSION['odsSearch_campo'] ?? 'Idods';

			// validar campo permitido
			$campos_validos = ['Idods'];
				if (!in_array($campo, $campos_validos)) {
    		$campo = 'Idods';
			}

			if(isset($busqueda) && $busqueda!=""){
				$consulta_datos = "
					SELECT  o.*,
							c.Nombre AS cliente_nombre,
							p.Nombre         AS asesor_nombre,
							p2.Nombre        AS tecnico_nombre
					FROM ods o
					LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
					LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
					LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
					WHERE o.$campo LIKE '%$busqueda%'
					ORDER BY o.Idods DESC
					LIMIT $inicio,$registros
				";
				$consulta_total = "SELECT COUNT(o.Idods)
								FROM ods o
								WHERE o.$campo LIKE '%$busqueda%'";
			}else{
				$consulta_datos = "
					SELECT  o.*,
							c.Nombre AS cliente_nombre,
							p.Nombre         AS asesor_nombre,
							p2.Nombre        AS tecnico_nombre
					FROM ods o
					LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
					LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
					LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
					ORDER BY o.Idods DESC
					LIMIT $inicio,$registros
				";
				$consulta_total = "SELECT COUNT(o.Idods) FROM ods o";
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
		                    <th class="has-text-centered">ID ODS</th>
		                    <th class="has-text-centered">Cliente</th>
		                    <th class="has-text-centered">Asesor</th>
							<th class="has-text-centered">Tecnico</th>
		                    <th class="has-text-centered">Status</th>
		                    <th class="has-text-centered">Tipo</th>
							<th class="has-text-centered">Marca</th>
							<th class="has-text-centered">Modelo</th>
							<th class="has-text-centered">Noserie</th>
							<th class="has-text-centered">Color</th>
							<th class="has-text-centered">Contrasena</th>
							<th class="has-text-centered">Ods Anterior</th>
							<th class="has-text-centered">Respaldo</th>
							<th class="has-text-centered">Uso</th>
							<th class="has-text-centered">Carpeta</th>
							<th class="has-text-centered">Problema</th>
							<th class="has-text-centered">Inspeccion</th>
							<th class="has-text-centered">Accesorios</th>
							<th class="has-text-centered">Fecha</th>
							<th class="has-text-centered">Hora</th>
							<th class="has-text-centered">Tiempo</th>
							<th class="has-text-centered">Total</th>
							<th class="has-text-centered">Descuento</th>
							<th class="has-text-centered">Autorizo</th>
							<th class="has-text-centered">Cuenta</th>
							<th class="has-text-centered">Resto</th>
							<th class="has-text-centered">Reparacion</th>
							<th class="has-text-centered">Costorep</th>
							<th class="has-text-centered">Presupuesto</th>
							<th class="has-text-centered">Iva</th>
							<th class="has-text-centered">Entrego</th>
							<th class="has-text-centered">Fechaentrega</th>
							<th class="has-text-centered">Recordatorio</th>
							<th class="has-text-centered">Garantia</th>
							<th class="has-text-centered">Sucursal</th>
							<th class="has-text-centered">Componentes</th>
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
							<td>
								'.$rows['Idods'].'
								<a href="'.APP_URL.'odsView/'.$rows['Idods'].'/" class="button is-small is-link" title="Ver ODS">
									<i class="fas fa-eye"></i>
								</a>
							</td>
							<td>'.$rows['cliente_nombre'].'</td>
							<td>'.$rows['asesor_nombre'].'</td>
							<td>'.$rows['tecnico_nombre'].'</td>
							<td>
                        	<div class="select is-rounded">
							<select name="Status" class="status-dropdown" onchange="abrirModalNotificacion('.$rows['Idods'].', this.value)">
								<option value="Recepcion" '.($rows['Status'] == 'Recepcion' ? 'selected' : '').'>Recepcion</option>
                                <option value="Presupuesto" '.($rows['Status'] == 'Presupuesto' ? 'selected' : '').'>Presupuesto</option>
                                <option value="Autorizacion" '.($rows['Status'] == 'Autorizacion' ? 'selected' : '').'>Autorizacion</option>
								<option value="Reparacion" '.($rows['Status'] == 'Reparacion' ? 'selected' : '').'>Reparacion</option>
                                <option value="StandBy" '.($rows['Status'] == 'StandBy' ? 'selected' : '').'>StandBy</option>
								<option value="LEntregar" '.($rows['Status'] == 'LEntregar' ? 'selected' : '').'>LEntregar</option>
                                <option value="Entregado" '.($rows['Status'] == 'Entregado' ? 'selected' : '').'>Entregado</option>
                            </select>
                        	</div>
							<!-- 🔘 Botón para abrir el modal de notificación -->
							<button class="button is-small is-info mt-2 js-modal-trigger"
								data-target="modalNotificacion"
								onclick="prepararModalODS('.$rows['Idods'].', this.closest(\'td\').querySelector(\'select\'))"
								disabled>
								Notificar
							</button>
                    		</td>
							<td>'.$rows['Tipo'].'</td>
							<td>'.$rows['Marca'].'</td>
							<td>'.$rows['Modelo'].'</td>
							<td>'.$rows['Noserie'].'</td>
							<td>'.$rows['Color'].'</td>
							<td>'.$rows['Contrasena'].'</td>
							<td>'.$rows['Odsanterior'].'</td>
							<td>'.$rows['Respaldo'].'</td>
							<td>'.$rows['Uso'].'</td>
							<td>'.$rows['Carpeta'].'</td>
							<td>'.$rows['Problema'].'</td>
							<td>'.$rows['Inspeccion'].'</td>
							<td>'.$rows['Accesorios'].'</td>
							<td>'.$rows['Fecha'].'</td>
							<td>'.$rows['Hora'].'</td>
							<td>'.$rows['Tiempo'].'</td>
							<td>'.$rows['Total'].'</td>
							<td>'.$rows['Descuento'].'</td>
							<td>'.$rows['Autorizo'].'</td>
							<td>'.$rows['Cuenta'].'</td>
							<td>'.$rows['Resto'].'</td>
							<td>'.$rows['Reparacion'].'</td>
							<td>'.$rows['Costorep'].'</td>
							<td>'.$rows['Presupuesto'].'</td>
							<td>'.$rows['Iva'].'</td>
							<td>'.$rows['Entrego'].'</td>
							<td>'.$rows['Fechaentrega'].'</td>
							<td>'.$rows['Recordatorio'].'</td>
							<td>'.$rows['Garantia'].'</td>
							<td>'.$rows['Sucursal'].'</td>
							<td>'.$rows['Componentes'].'</td>
							
			                <td>
			                    <a href="'.APP_URL.'odsUpdate/'.$rows['Idods'].'/" class="button is-success is-rounded is-small">
			                    	<i class="fas fa-sync fa-fw"></i>
			                    </a>
			                </td>
			                <td>
			                	<form class="FormularioAjax" action="'.APP_URL.'app/ajax/categoriaAjax.php" method="POST" autocomplete="off" >

			                		<input type="hidden" name="modulo_ods" value="eliminar">
			                		<input type="hidden" name="Idods" value="'.$rows['Idods'].'">

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
				$tabla.='<p class="has-text-right">Mostrando ODS <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';

				$tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url,7);
			}
			return $tabla;
		}
 		/* para hacer la búsqueda desde dashboard*/
		public function listarDashboardControlador($pagina,$registros,$url,$busqueda){

			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);

			$url=$this->limpiarCadena($url);
			$url=APP_URL.$url."/";

			$busqueda=$this->limpiarCadena($busqueda);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

			$campo = $_SESSION['dashboard_campo'] ?? 'Idods';

			$consulta_datos = "";
			$consulta_total = "";

			if(isset($busqueda) && $busqueda!=""){

				$consulta_datos = "
					SELECT 
						o.*, 
						c.Nombre AS NombreCliente, 
						p.Nombre AS NombreAsesor
					FROM ods o
					INNER JOIN clientes c ON o.Idcliente = c.Idcliente
					LEFT JOIN personal p ON o.Idasesor = p.Idasesor
					WHERE o.Idods = '$busqueda' 
					OR c.Nombre = '$busqueda' 
					OR c.Numero = '$busqueda' 
					ORDER BY o.Idods DESC 
					LIMIT $inicio, $registros
				";

				$consulta_total="
					SELECT COUNT(*) 
					FROM ods o
					INNER JOIN clientes c ON o.Idcliente = c.Idcliente
					WHERE o.Idods = '$busqueda' 
					OR c.Nombre = '$busqueda' 
					OR c.Numero = '$busqueda' 
					ORDER BY o.Idods DESC 
					LIMIT $inicio, $registros
				";

				$consulta_total="
					SELECT COUNT(*) 
					FROM ods o
					INNER JOIN clientes c ON o.Idcliente = c.Idcliente
					WHERE o.Idods = '$busqueda' 
					OR c.Nombre = '$busqueda' 
					OR c.Numero = '$busqueda'
				";

			}else{
				$consulta_datos="SELECT * FROM ods ORDER BY Idods ASC LIMIT $inicio,$registros";
				$consulta_total="SELECT COUNT(Idods) FROM ods";
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
							<th class="has-text-centered">Ver ODS</th>
							<th class="has-text-centered">ID ODS</th>
							<th class="has-text-centered">ID Cliente</th>
							<th class="has-text-centered">ID Asesor</th>
							<th class="has-text-centered">Status</th>
							<th class="has-text-centered">Tipo</th>
							<th class="has-text-centered">Marca</th>
							<th class="has-text-centered">Problema</th>
							<th class="has-text-centered">Fecha</th>
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
						<tr class="has-text-centered">
							<td>
								<a href="'.APP_URL.'odsView/'.$rows['Idods'].'/" class="button is-small is-link" title="Ver ODS">
									<i class="fas fa-eye"></i>
								</a>
							</td>
							<td>'.$rows['Idods'].'</td>
							<td>'.$rows['Idcliente'].'</td>
							<td>'.$rows['NombreAsesor'].'</td>
							<td>'.$rows['Status'].'</td>
							<td>'.$rows['Tipo'].'</td>
							<td>'.$rows['Marca'].'</td>
							<td>'.$rows['Problema'].'</td>
							<td>'.$rows['Fecha'].'</td>
							<td>
								<a href="'.APP_URL.'odsUpdate/'.$rows['Idods'].'/" class="button is-success is-rounded is-small">
									<i class="fas fa-sync fa-fw"></i>
								</a>
							</td>
							<td>
								<form class="FormularioAjax" action="'.APP_URL.'app/ajax/categoriaAjax.php" method="POST" autocomplete="off">
									<input type="hidden" name="modulo_ods" value="eliminar">
									<input type="hidden" name="Idods" value="'.$rows['Idods'].'">
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
						<tr class="has-text-centered">
							<td colspan="13">
								<a href="'.$url.'1/" class="button is-link is-rounded is-small mt-4 mb-4">
									Haga clic acá para recargar el listado
								</a>
							</td>
						</tr>
					';
				}else{
					$tabla.='
						<tr class="has-text-centered">
							<td colspan="13">
								No hay registros en el sistema
							</td>
						</tr>
					';
				}
			}

			$tabla.='</tbody></table></div>';

			if($total>0 && $pagina<=$numeroPaginas){
				$tabla.='<p class="has-text-right">Mostrando ODS <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';
				$tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url,7);
			}

			return $tabla;
		}

		/*----------  Controlador eliminar ods  ----------*/
		public function eliminarOdsControlador(){

			$id=$this->limpiarCadena($_POST['Idods']);

			# Verificando ods #
		    $datos=$this->ejecutarConsulta("SELECT * FROM ods WHERE Idods='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la ODS en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos=$datos->fetch();
		    }

		    $eliminarOds=$this->eliminarRegistro("ods","Idods",$id);

		    if($eliminarOds->rowCount()==1){

		        $alerta=[
					"tipo"=>"recargar",
					"titulo"=>"ODS eliminada",
					"texto"=>"La ODS ".$datos['Idods']." ha sido eliminada del sistema correctamente",
					"icono"=>"success"
				];

		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido eliminar la ODS ".$datos['Idods']." del sistema, por favor intente nuevamente",
					"icono"=>"error"
				];
		    }

		    return json_encode($alerta);
		}


		/*----------  Controlador actualizar ods  ----------*/
		public function actualizarOdsControlador(){

			$id=$this->limpiarCadena($_POST['Idods']);

			# Verificando ods #
		    $datos=$this->ejecutarConsulta("SELECT * FROM ods WHERE Idods='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la ODS en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Almacenando datos#
		    $Idods=$this->limpiarCadena($_POST['Idods']);
			$Idcliente=$this->limpiarCadena($_POST['Idcliente']);
			$Idasesor=$this->limpiarCadena($_POST['Idasesor']);
		    $Tipo=$this->limpiarCadena($_POST['Tipo']);
		    $Marca=$this->limpiarCadena($_POST['Marca']);
			$Modelo=$this->limpiarCadena($_POST['Modelo']);
			$Noserie=$this->limpiarCadena($_POST['Noserie']);
			$Color=$this->limpiarCadena($_POST['Color']);
			$Contrasena=$this->limpiarCadena($_POST['Contrasena']);
			$Odsanterior=$this->limpiarCadena($_POST['Odsanterior']);
			$Respaldo=$this->limpiarCadena($_POST['Respaldo']);
			$Uso=$this->limpiarCadena($_POST['Uso']);
			$Carpeta=$this->limpiarCadena($_POST['Carpeta']);
			$Problema=$this->limpiarCadena($_POST['Problema']);
			$Inspeccion=$this->limpiarCadena($_POST['Inspeccion']);
			$Accesorios=$this->limpiarCadena($_POST['Accesorios']);
			$Fecha=$this->limpiarCadena($_POST['Fecha']);
			$Hora=$this->limpiarCadena($_POST['Hora']);
			$Tiempo=$this->limpiarCadena($_POST['Tiempo']);
			$Total=$this->limpiarCadena($_POST['Total']);
			$Descuento=$this->limpiarCadena($_POST['Descuento']);
			$Autorizo=$this->limpiarCadena($_POST['Autorizo']);
			$Cuenta=$this->limpiarCadena($_POST['Cuenta']);
			$Resto=$this->limpiarCadena($_POST['Resto']);
			$Reparacion=$this->limpiarCadena($_POST['Reparacion']);
			$Costorep=$this->limpiarCadena($_POST['Costorep']);
			$Presupuesto=$this->limpiarCadena($_POST['Presupuesto']);
			$Iva=$this->limpiarCadena($_POST['Iva']);
			$Entrego=$this->limpiarCadena($_POST['Entrego']);
			$Fechaentrega=$this->limpiarCadena($_POST['Fechaentrega']);
			$Recordatorio=$this->limpiarCadena($_POST['Recordatorio']);
			$Garantia=$this->limpiarCadena($_POST['Garantia']);
			$Sucursal=$this->limpiarCadena($_POST['Sucursal']);
			$Componentes=$this->limpiarCadena($_POST['Componentes']);

		    # Verificando campos obligatorios #
            if($Tipo==""){
            	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
            }

            # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}",$Tipo)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NOMBRE no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

		    $ods_datos_up=[
				[
					"campo_nombre"=>"Tipo",
					"campo_marcador"=>":Tipo",
					"campo_valor"=>$Tipo
				],
				[
					"campo_nombre"=>"Marca",
					"campo_marcador"=>":Marca",
					"campo_valor"=>$Marca
				],
				[
					"campo_nombre"=>"Modelo",
					"campo_marcador"=>":Modelo",
					"campo_valor"=>$Modelo
				],
				[
					"campo_nombre"=>"Noserie",
					"campo_marcador"=>":Noserie",
					"campo_valor"=>$Noserie
				],
				[
					"campo_nombre"=>"Color",
					"campo_marcador"=>":Color",
					"campo_valor"=>$Color
				],
				[
					"campo_nombre"=>"Contrasena",
					"campo_marcador"=>":Contrasena",
					"campo_valor"=>$Contrasena
				],
				[
					"campo_nombre"=>"Odsanterior",
					"campo_marcador"=>":Odsanterior",
					"campo_valor"=>$Odsanterior
				],
				[
					"campo_nombre"=>"Respaldo",
					"campo_marcador"=>":Respaldo",
					"campo_valor"=>$Respaldo
				],
				[
					"campo_nombre"=>"Uso",
					"campo_marcador"=>":Uso",
					"campo_valor"=>$Uso
				],
				[
					"campo_nombre"=>"Carpeta",
					"campo_marcador"=>":Carpeta",
					"campo_valor"=>$Carpeta
				],
				[
					"campo_nombre"=>"Problema",
					"campo_marcador"=>":Problema",
					"campo_valor"=>$Problema
				],
				[
					"campo_nombre"=>"Inspeccion",
					"campo_marcador"=>":Inspeccion",
					"campo_valor"=>$Inspeccion
				],
				[
					"campo_nombre"=>"Accesorios",
					"campo_marcador"=>":Accesorios",
					"campo_valor"=>$Accesorios
				],
				[
					"campo_nombre"=>"Fecha",
					"campo_marcador"=>":Fecha",
					"campo_valor"=>$Fecha
				],
				[
					"campo_nombre"=>"Hora",
					"campo_marcador"=>":Hora",
					"campo_valor"=>$Hora
				],
				[
					"campo_nombre"=>"Tiempo",
					"campo_marcador"=>":Tiempo",
					"campo_valor"=>$Tiempo
				],
				[
					"campo_nombre"=>"Total",
					"campo_marcador"=>":Total",
					"campo_valor"=>$Total
				],
				[
					"campo_nombre"=>"Descuento",
					"campo_marcador"=>":Descuento",
					"campo_valor"=>$Descuento
				],
				[
					"campo_nombre"=>"Autorizo",
					"campo_marcador"=>":Autorizo",
					"campo_valor"=>$Autorizo
				],
				[
					"campo_nombre"=>"Cuenta",
					"campo_marcador"=>":Cuenta",
					"campo_valor"=>$Cuenta
				],
				[
					"campo_nombre"=>"Resto",
					"campo_marcador"=>":Resto",
					"campo_valor"=>$Resto
				],
				[
					"campo_nombre"=>"Reparacion",
					"campo_marcador"=>":Reparacion",
					"campo_valor"=>$Reparacion
				],
				[
					"campo_nombre"=>"Costorep",
					"campo_marcador"=>":Costorep",
					"campo_valor"=>$Costorep
				],
				[
					"campo_nombre"=>"Presupuesto",
					"campo_marcador"=>":Presupuesto",
					"campo_valor"=>$Presupuesto
				],
				[
					"campo_nombre"=>"Iva",
					"campo_marcador"=>":Iva",
					"campo_valor"=>$Iva
				],
				[
					"campo_nombre"=>"Entrego",
					"campo_marcador"=>":Entrego",
					"campo_valor"=>$Entrego
				],
				[
					"campo_nombre"=>"Fechaentrega",
					"campo_marcador"=>":Fechaentrega",
					"campo_valor"=>$Fechaentrega
				],
				[
					"campo_nombre"=>"Recordatorio",
					"campo_marcador"=>":Recordatorio",
					"campo_valor"=>$Recordatorio
				],
				[
					"campo_nombre"=>"Garantia",
					"campo_marcador"=>":Garantia",
					"campo_valor"=>$Garantia
				],
				[
					"campo_nombre"=>"Sucursal",
					"campo_marcador"=>":Sucursal",
					"campo_valor"=>$Sucursal
				],
				[
					"campo_nombre"=>"Componentes",
					"campo_marcador"=>":Componentes",
					"campo_valor"=>$Componentes
				]
			];

			$condicion=[
				"condicion_campo"=>"categoria_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];
			
			if($this->actualizarDatos("ods",$ods_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Categoría actualizada",
					"texto"=>"Los datos de se actualizaron correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido actualizar los datos, por favor intente nuevamente",
					"icono"=>"error"
				];
			}
			
			return json_encode($alerta);
		}

		// Cambiar status
		public function cambiarStatusOdsControlador(): array {
        try {
            $pdo   = self::conectar();
            $idods = (int)($_POST['Idods'] ?? 0);
            $nuevo = trim((string)($_POST['Status'] ?? ''));

            if ($idods<=0 || $nuevo==='') {
                return ['success'=>false,'msg'=>'Datos incompletos'];
            }

            // 1) Estado actual
            $q = $pdo->prepare("SELECT TRIM(Status) AS s FROM ods WHERE Idods=:id");
            $q->execute([':id'=>$idods]);
            $actual = $q->fetchColumn();

            if ($actual === false) return ['success'=>false,'msg'=>'ODS no encontrada'];
            if ($actual === $nuevo) return ['success'=>true,'msg'=>'Sin cambios'];

            // 2) Reglas de transición (ajusta a tu flujo)
            $permitidas = [
            'RECEPCION'   => ['RECEPCION','DIAGNOSTICO','REPARACION','CANCELADO'],
            'DIAGNOSTICO' => ['DIAGNOSTICO','PRESUPUESTO','AUTORIZACION','REPARACION','STANDBY','CANCELADO'],
            'PRESUPUESTO' => ['PRESUPUESTO','DIAGNOSTICO','STANDBY','AUTORIZACION','CANCELADO'],
            'STANDBY'     => ['STANDBY','AUTORIZACION','CANCELADO'],
            'AUTORIZACION'=> ['AUTORIZACION','PRESUPUESTO','REPARACION','CANCELADO'],
            'REPARACION'  => ['REPARACION','REFACCIONES','STANDBY','LISTOE','CANCELADO'],
            'REFACCIONES' => ['REFACCIONES','REPARACION','STANDBY','CANCELADO'],
            'LISTOE'      => ['LISTOE','REPARACION','ENTREGADO','ALMACEN','SEGUIMIENTO','CANCELADO'],
            'ENTREGADO'   => ['ENTREGADO','SEGUIMIENTO'],
            'SEGUIMIENTO' => [],
            'ALMACEN'     => [],
            'CANCELADO'   => []
          ];
            $ok = isset($permitidas[$actual]) ? in_array($nuevo, $permitidas[$actual], true) : true;
            if (!$ok) return ['success'=>false,'msg'=>"Transición no permitida ($actual → $nuevo)"];

            // 3) Guardar en transacción + auditar en reportetec
            $pdo->beginTransaction();

            $up = $pdo->prepare("UPDATE ods SET Status=:st WHERE Idods=:id");
            $up->execute([':st'=>$nuevo, ':id'=>$idods]);

            // (Opcional) auditoría/bitácora: reportetec
            // Ajusta columnas a tu esquema real. Ejemplo minimalista:
            $usuarioId = $_SESSION['id'] ?? null; // o $_SESSION['id_usuario']
            if ($usuarioId) {
                $log = $pdo->prepare("
                    INSERT INTO reportetec (Idods, Reporte, Tecnico, Fecha)
                    VALUES (:idods, :reporte, :uid, NOW())
                ");
                $texto = "Cambio de status: {$actual} → {$nuevo}";
                $log->execute([
                    ':idods'=>$idods,
                    ':reporte'=>$texto,
                    ':uid'=>$usuarioId
                ]);
            }

            $pdo->commit();
            return ['success'=>true,'msg'=>'Status actualizado','status'=>$nuevo];

        } catch (\Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            return ['success'=>false,'msg'=>'Error: '.$e->getMessage()];
        }
    }

		public function listarOdsPersonalControlador($pagina, $registros, $url, $busqueda) {
		$pagina = $this->limpiarCadena($pagina);
		$registros = $this->limpiarCadena($registros);

		$url = $this->limpiarCadena($url);
		$url = APP_URL . $url . "/";

		$busqueda = $this->limpiarCadena($busqueda);
		$tabla = "";

		$pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
		$inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

		$campo = $_SESSION['odsSearch_campo'] ?? 'Idods';

		// validar campo permitido
		$campos_validos = ['Idods'];
		if (!in_array($campo, $campos_validos)) {
			$campo = 'Idods';
		}

		// Obtener el Idasesor de la sesión
		//$idAsesorSesion = $_SESSION['id'] ?? 0; // Ajusta según el nombre de tu variable de sesión

		// Agregar condición para filtrar por el asesor en sesión
		//$filtroAsesor = " AND o.Idasesor = $idAsesorSesion";

		// Obtener el Id del técnico de la sesión (usando el mismo ID de sesión)
		$idTecnicoSesion = $_SESSION['id'] ?? 0;

		// Agregar condición para filtrar por el técnico en sesión
		$filtroTecnico = " AND o.IdTecnico = $idTecnicoSesion";

		// Obtener lista de técnicos disponibles - COLOCA ESTO AL INICIO DE TU FUNCIÓN
			$consulta_tecnicos = "
				SELECT Idasesor, Nombre 
				FROM personal
				WHERE Puesto = 'TECNICO' OR Puesto LIKE '%TECNIC%' OR Puesto LIKE '%tecnico%' or Puesto LIKE '%JEFE DE PRODUCCION%'
				ORDER BY Nombre
			";

			try {
				$resultado_tecnicos = $this->ejecutarConsulta($consulta_tecnicos);
				$tecnicos = $resultado_tecnicos->fetchAll();
				
				// Si no hay técnicos, inicializa como array vacío
				if (!$tecnicos) {
					$tecnicos = [];
				}
				
			} catch (\Exception $e) {
				// En caso de error, inicializar como array vacío
				$tecnicos = [];
				error_log("Error al cargar técnicos: " . $e->getMessage());
			}

		if (isset($busqueda) && $busqueda != "") {
		
			$consulta_datos = "
				SELECT  o.*,
						c.Nombre AS cliente_nombre,
						p.Nombre         AS asesor_nombre,
						p2.Nombre        AS tecnico_nombre
				FROM ods o
				LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
				LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
				LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
				WHERE o.$campo LIKE '%$busqueda%'
				$filtroTecnico
				ORDER BY o.Idods DESC
				LIMIT $inicio,$registros
			";

			$consulta_total = "SELECT COUNT(o.Idods)
							FROM ods o
							WHERE o.$campo LIKE '%$busqueda%'
							$filtroTecnico";
		} else {
			$consulta_datos = "
				SELECT  o.*,
						c.Nombre AS cliente_nombre,
						p.Nombre         AS asesor_nombre,
						p2.Nombre        AS tecnico_nombre
				FROM ods o
				LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
				LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
				LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
				WHERE 1=1
				$filtroTecnico
				ORDER BY o.Idods DESC
				LIMIT $inicio,$registros
			";
			$consulta_total = "SELECT COUNT(o.Idods) 
							FROM ods o 
							WHERE 1=1
							$filtroTecnico";
		}

		$datos = $this->ejecutarConsulta($consulta_datos);
		$datos = $datos->fetchAll();

		$total = $this->ejecutarConsulta($consulta_total);
		$total = (int) $total->fetchColumn();

		$numeroPaginas = ceil($total / $registros);
		
		$tabla .= '
			<div class="table-container">
			<table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
				<thead>
					<tr>
						<th class="has-text-centered">ID ODS</th>
						<th class="has-text-centered">Cliente</th>
						<th class="has-text-centered">Asesor</th>
						<th class="has-text-centered">Tecnico</th>
						<th class="has-text-centered">Status</th>
						<th class="has-text-centered">Tipo</th>
						<th class="has-text-centered">Marca</th>
						<th class="has-text-centered">Modelo</th>
						<th class="has-text-centered">Noserie</th>
						<th class="has-text-centered">Color</th>
						<th class="has-text-centered">Contrasena</th>
						<th class="has-text-centered">Ods Anterior</th>
						<th class="has-text-centered">Respaldo</th>
						<th class="has-text-centered">Uso</th>
						<th class="has-text-centered">Carpeta</th>
						<th class="has-text-centered">Problema</th>
						<th class="has-text-centered">Inspeccion</th>
						<th class="has-text-centered">Accesorios</th>
						<th class="has-text-centered">Fecha</th>
						<th class="has-text-centered">Hora</th>
						<th class="has-text-centered">Tiempo</th>
						<th class="has-text-centered">Total</th>
						<th class="has-text-centered">Descuento</th>
						<th class="has-text-centered">Autorizo</th>
						<th class="has-text-centered">Cuenta</th>
						<th class="has-text-centered">Resto</th>
						<th class="has-text-centered">Reparacion</th>
						<th class="has-text-centered">Costorep</th>
						<th class="has-text-centered">Presupuesto</th>
						<th class="has-text-centered">Iva</th>
						<th class="has-text-centered">Entrego</th>
						<th class="has-text-centered">Fechaentrega</th>
						<th class="has-text-centered">Recordatorio</th>
						<th class="has-text-centered">Garantia</th>
						<th class="has-text-centered">Sucursal</th>
						<th class="has-text-centered">Componentes</th>
						<th class="has-text-centered">Actualizar</th>
						<th class="has-text-centered">Eliminar</th>
					</tr>
				</thead>
				<tbody>
		';

		if ($total >= 1 && $pagina <= $numeroPaginas) {
			$contador = $inicio + 1;
			$pag_inicio = $inicio + 1;
			foreach ($datos as $rows) {

				// Construir las opciones de técnicos para esta fila CON VERIFICACIÓN
				$opciones_tecnicos = '<option value="">Sin asignar</option>';
				
				if (is_array($tecnicos) && count($tecnicos) > 0) {
					foreach ($tecnicos as $tecnico) {
						$selected = ($rows['IdTecnico'] == $tecnico['Idasesor']) ? 'selected' : '';
						$opciones_tecnicos .= '<option value="' . $tecnico['Idasesor'] . '" ' . $selected . '>' . $tecnico['Nombre'] . '</option>';
					}
				} else {
					// Si no hay técnicos, mostrar solo el técnico actual si existe
					if (!empty($rows['tecnico_nombre'])) {
						$opciones_tecnicos .= '<option value="' . $rows['IdTecnico'] . '" selected>' . $rows['tecnico_nombre'] . '</option>';
					}
				}

				$tabla .= '
					<tr class="has-text-centered" >
						<td>
							' . $rows['Idods'] . '
							<a href="' . APP_URL . 'odsView/' . $rows['Idods'] . '/" class="button is-small is-link" title="Ver ODS">
								<i class="fas fa-eye"></i>
							</a>
						</td>
						<td>' . $rows['cliente_nombre'] . '</td>
						<td>' . $rows['asesor_nombre'] . '</td>
					
						<!-- NUEVO DROPDOWN DE TÉCNICOS -->
						<td>
							<div class="select is-rounded">
								<select name="Tecnico" class="tecnico-dropdown" onchange="actualizarTecnico(' . $rows['Idods'] . ', this.value)">
									<option value="">Sin asignar</option>
									' . $opciones_tecnicos . '
								</select>
							</div>
						</td>
						<td>
						<div class="select is-rounded">
						<select name="Status" class="status-dropdown" onchange="abrirModalNotificacion(' . $rows['Idods'] . ', this.value)">
							<option value="Recepcion" ' . ($rows['Status'] == 'Recepcion' ? 'selected' : '') . '>Recepcion</option>
							<option value="Diagnostico" ' . ($rows['Status'] == 'Diagnostico' ? 'selected' : '') . '>Diagnostico</option>
							<option value="Presupuesto" ' . ($rows['Status'] == 'Presupuesto' ? 'selected' : '') . '>Presupuesto</option>
							<option value="StandBy" ' . ($rows['Status'] == 'StandBy' ? 'selected' : '') . '>StandBy</option>
							<option value="Autorizacion" ' . ($rows['Status'] == 'Autorizacion' ? 'selected' : '') . '>Autorizacion</option>
							<option value="Reparacion" ' . ($rows['Status'] == 'Reparacion' ? 'selected' : '') . '>Reparacion</option>
							<option value="Refacciones" ' . ($rows['Status'] == 'Refacciones' ? 'selected' : '') . '>Refacciones</option>
							<option value="ListoE" ' . ($rows['Status'] == 'ListoE' ? 'selected' : '') . '>ListoE</option>
							<option value="Entregado" ' . ($rows['Status'] == 'Entregado' ? 'selected' : '') . '>Entregado</option>
							<option value="Seguimiento" ' . ($rows['Status'] == 'Seguimiento' ? 'selected' : '') . '>Seguimiento</option>
							<option value="Almacen" ' . ($rows['Status'] == 'Almacen' ? 'selected' : '') . '>Almacen</option>
							<option value="Cancelado" ' . ($rows['Status'] == 'Cancelado' ? 'selected' : '') . '>Cancelado</option>
						</select>
						</div>
						<!-- 🔘 Botón para abrir el modal de notificación -->
						<button class="button is-small is-info mt-2 js-modal-trigger"
							data-target="modalNotificacion"
							onclick="prepararModalODS(' . $rows['Idods'] . ', this.closest(\'td\').querySelector(\'select\'))"
							disabled>
							Notificar
						</button>
						</td>
						<td>' . $rows['Tipo'] . '</td>
						<td>' . $rows['Marca'] . '</td>
						<td>' . $rows['Modelo'] . '</td>
						<td>' . $rows['Noserie'] . '</td>
						<td>' . $rows['Color'] . '</td>
						<td>' . $rows['Contrasena'] . '</td>
						<td>' . $rows['Odsanterior'] . '</td>
						<td>' . $rows['Respaldo'] . '</td>
						<td>' . $rows['Uso'] . '</td>
						<td>' . $rows['Carpeta'] . '</td>
						<td>' . $rows['Problema'] . '</td>
						<td>' . $rows['Inspeccion'] . '</td>
						<td>' . $rows['Accesorios'] . '</td>
						<td>' . $rows['Fecha'] . '</td>
						<td>' . $rows['Hora'] . '</td>
						<td>' . $rows['Tiempo'] . '</td>
						<td>' . $rows['Total'] . '</td>
						<td>' . $rows['Descuento'] . '</td>
						<td>' . $rows['Autorizo'] . '</td>
						<td>' . $rows['Cuenta'] . '</td>
						<td>' . $rows['Resto'] . '</td>
						<td>' . $rows['Reparacion'] . '</td>
						<td>' . $rows['Costorep'] . '</td>
						<td>' . $rows['Presupuesto'] . '</td>
						<td>' . $rows['Iva'] . '</td>
						<td>' . $rows['Entrego'] . '</td>
						<td>' . $rows['Fechaentrega'] . '</td>
						<td>' . $rows['Recordatorio'] . '</td>
						<td>' . $rows['Garantia'] . '</td>
						<td>' . $rows['Sucursal'] . '</td>
						<td>' . $rows['Componentes'] . '</td>
						
						<td>
							<a href="' . APP_URL . 'odsUpdate/' . $rows['Idods'] . '/" class="button is-success is-rounded is-small">
								<i class="fas fa-sync fa-fw"></i>
							</a>
						</td>
						<td>
							<form class="FormularioAjax" action="' . APP_URL . 'app/ajax/categoriaAjax.php" method="POST" autocomplete="off" >

								<input type="hidden" name="modulo_ods" value="eliminar">
								<input type="hidden" name="Idods" value="' . $rows['Idods'] . '">

								<button type="submit" class="button is-danger is-rounded is-small">
									<i class="far fa-trash-alt fa-fw"></i>
								</button>
							</form>
						</td>
					</tr>
				';
				$contador++;
			}
			$pag_final = $contador - 1;
		} else {
			if ($total >= 1) {
				$tabla .= '
					<tr class="has-text-centered" >
						<td colspan="6">
							<a href="' . $url . '1/" class="button is-link is-rounded is-small mt-4 mb-4">
								Haga clic acá para recargar el listado
							</a>
						</td>
					</tr>
				';
			} else {
				$tabla .= '
					<tr class="has-text-centered" >
						<td colspan="6">
							No hay registros en el sistema
						</td>
					</tr>
				';
			}
		}
		$tabla .= '</tbody></table></div>';
		### Paginacion ###
		if ($total > 0 && $pagina <= $numeroPaginas) {
			$tabla .= '<p class="has-text-right">Mostrando ODS <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';

			$tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);
		}
		return $tabla;
	}

	}

// Sanitizamos
$productoNombre = mainModel::limpiarCadena($_POST['producto'] ?? '');

// Conexión a la BD
$db = mainModel::conectar();

// Verificar si el producto existe en inventario
$stmt = $db->prepare("SELECT COUNT(*) FROM inventario WHERE producto = :prod LIMIT 1");
$stmt->execute([':prod' => $productoNombre]);
$existe = $stmt->fetchColumn();

// Si existe, refaccion = 1; si no, refaccion = 0
$refaccion = $existe > 0 ? 1 : 0;

// Ahora actualizas/inserta en la tabla ODS o refacciones según corresponda
$sql = $db->prepare("INSERT INTO refacciones (producto, refaccion) VALUES (:prod, :refaccion)");
$sql->execute([
    ':prod'      => $productoNombre,
    ':refaccion' => $refaccion
]);
?>

<script>
function actualizarTecnico(idods, idTecnico) {
    if (confirm('¿Estás seguro de que deseas asignar este técnico?')) {
        // Subir un nivel desde odsMe/ a VENTAS3/
        fetch('../actualizar_tecnico.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'idods=' + idods + '&idTecnico=' + idTecnico
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Técnico actualizado correctamente');
            } else {
                alert('Error al actualizar el técnico: ' + data.message);
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión');
            location.reload();
        });
    } else {
        location.reload();
    }
}
</script>