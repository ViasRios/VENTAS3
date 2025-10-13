<?php

	namespace app\controllers;
	use app\models\mainModel;

	class cashierController extends mainModel{

		public function mostrarFormularioCajaControlador() {
    		$ultimo_stmt = $this->ejecutarConsulta("SELECT MAX(Idini) AS ultimo_id FROM cajai");
    		$ultimo = $ultimo_stmt->fetch();

    		$ultimo_id = 0;
    		$siguiente_idini = 1;

    		if (!empty($ultimo) && isset($ultimo['ultimo_id'])) {
        		$ultimo_id = $ultimo['ultimo_id'];
        		$siguiente_idini = $ultimo_id + 1;
    		}

    		// Guardar la variable en sesión temporal
    		$_SESSION['siguiente_idini'] = $siguiente_idini;

    		// Devolver la ruta a la vista
    		return "./app/views/content/cashierNew-view.php";
		}

		/*----------  Controlador registrar caja  ----------*/
		public function registrarCajaControlador(){

    # Almacenando datos #
    $Idini = $this->limpiarCadena($_POST['Idini']);
    $Efectivo = $this->limpiarCadena($_POST['Efectivo']);
    $Fecha = $this->limpiarCadena($_POST['Fecha']);
    $Hora = $this->limpiarCadena($_POST['Hora']);
    $TipoPago = $this->limpiarCadena($_POST['TipoPago']);

    $productos = $_POST['productos']; // Suponiendo que es un array de productos con los datos

    # Verificando campos obligatorios #
    if ($Efectivo == "" || empty($productos)) {
        $alerta = [
            "tipo" => "simple",
            "titulo" => "Ocurrió un error inesperado",
            "texto" => "No has llenado todos los campos que son obligatorios",
            "icono" => "error"
        ];
        return json_encode($alerta);
    }

    # Verificando integridad de los datos #
    if ($this->verificarDatos("/^\d{1,9}(\.\d{1,2})?$/", $Efectivo)) {
        $alerta = [
            "tipo" => "simple",
            "titulo" => "Ocurrió un error inesperado",
            "texto" => "El EFECTIVO no coincide con el formato solicitado",
            "icono" => "error"
        ];
        return json_encode($alerta);
    }

    # Comprobando que el efectivo sea mayor o igual a 0 #
    $Efectivo = number_format($Efectivo, 2, '.', '');
    if ($Efectivo < 0) {
        $alerta = [
            "tipo" => "simple",
            "titulo" => "Ocurrió un error inesperado",
            "texto" => "No puedes colocar una cantidad de efectivo menor a 0",
            "icono" => "error"
        ];
        return json_encode($alerta);
    }

    # Calcular el total, subtotal y IVA #
    $subtotal = 0;
    $iva = 0;
    $total = 0;

    foreach ($productos as $producto) {
        $cantidad = $this->limpiarCadena($producto['cantidad']);
        $precio = $this->limpiarCadena($producto['precio']);
        
        if ($this->verificarDatos("/^\d{1,9}(\.\d{1,2})?$/", $cantidad) || $this->verificarDatos("/^\d{1,9}(\.\d{1,2})?$/", $precio)) {
            $alerta = [
                "tipo" => "simple",
                "titulo" => "Ocurrió un error inesperado",
                "texto" => "Uno o más de los productos no tiene los datos en el formato correcto",
                "icono" => "error"
            ];
            return json_encode($alerta);
        }

        $subtotal += $cantidad * $precio;
    }

    $iva = $subtotal * 0.16; // Asumiendo que el IVA es 16%
    $total = $subtotal + $iva;

    # Datos de la caja #
    $caja_datos_reg = [
        [
            "campo_nombre" => "caja_efectivo",
            "campo_marcador" => ":Efectivo",
            "campo_valor" => $Efectivo
        ],
        [
            "campo_nombre" => "caja_subtotal",
            "campo_marcador" => ":Subtotal",
            "campo_valor" => $subtotal
        ],
        [
            "campo_nombre" => "caja_iva",
            "campo_marcador" => ":Iva",
            "campo_valor" => $iva
        ],
        [
            "campo_nombre" => "caja_total",
            "campo_marcador" => ":Total",
            "campo_valor" => $total
		],
		[
			"campo_nombre" => "tipo_pago",
			"campo_marcador" => ":TipoPago",
			"campo_valor" => $TipoPago
		]
    ];

    $registrar_caja = $this->guardarDatos("caja", $caja_datos_reg);

    if ($registrar_caja->rowCount() == 1) {
        $alerta = [
            "tipo" => "limpiar",
            "titulo" => "Caja registrada",
            "texto" => "La caja se registró con éxito",
            "icono" => "success"
        ];
    } else {
        $alerta = [
            "tipo" => "simple",
            "titulo" => "Ocurrió un error inesperado",
            "texto" => "No se pudo registrar la caja, por favor intente nuevamente",
            "icono" => "error"
        ];
    }

    return json_encode($alerta);
}



		/*----------  Controlador listar cajas  ----------*/
		public function listarCajaControlador($pagina,$registros,$url,$busqueda){

			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);

			$url=$this->limpiarCadena($url);
			$url=APP_URL.$url."/";

			$busqueda=$this->limpiarCadena($busqueda);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

			/* 
			Ahora tomamos de:
			- movimientos: Idods, Fecha, Hora, Cantidad
			- ods: Idcliente, Tipo, Marca, IdTecnico, Status
			*/
			$select_campos = "
				m.Idods, m.Fecha, m.Hora, m.Cantidad,
				o.Idcliente, o.Tipo, o.Marca, o.IdTecnico, o.Status,
				c.Nombre AS NombreCliente,
				p.Nombre AS NombreTecnico
			";

			if(isset($busqueda) && $busqueda!=""){
				$consulta_datos="
					SELECT $select_campos
					FROM movimientos m
					INNER JOIN ods o ON o.Idods = m.Idods
					INNER JOIN clientes c ON o.Idcliente = c.Idcliente
					INNER JOIN personal p ON o.IdTecnico = p.Idasesor
					WHERE m.Idods LIKE '%$busqueda%' 
					OR c.Nombre LIKE '%$busqueda%'
					OR p.Nombre LIKE '%$busqueda%'
					ORDER BY m.Fecha DESC, m.Hora DESC
					LIMIT $inicio,$registros
				";

				$consulta_total="
					SELECT COUNT(*) 
					FROM movimientos m
					INNER JOIN ods o ON o.Idods = m.Idods
					INNER JOIN clientes c ON o.Idcliente = c.Idcliente
					INNER JOIN personal p ON o.IdTecnico = p.Idasesor
					WHERE m.Idods LIKE '%$busqueda%' 
					OR c.Nombre LIKE '%$busqueda%'
					OR p.Nombre LIKE '%$busqueda%'
				";
			}else{
				$consulta_datos="
					SELECT $select_campos
					FROM movimientos m
					INNER JOIN ods o ON o.Idods = m.Idods
					INNER JOIN clientes c ON o.Idcliente = c.Idcliente
					INNER JOIN personal p ON o.IdTecnico = p.Idasesor
					ORDER BY m.Fecha DESC, m.Hora DESC
					LIMIT $inicio,$registros
				";

				$consulta_total="
					SELECT COUNT(*) 
					FROM movimientos m
					INNER JOIN ods o ON o.Idods = m.Idods
					INNER JOIN clientes c ON o.Idcliente = c.Idcliente
					INNER JOIN personal p ON o.IdTecnico = p.Idasesor
				";
			}

			$datos = $this->ejecutarConsulta($consulta_datos)->fetchAll();
			$total = (int) $this->ejecutarConsulta($consulta_total)->fetchColumn();
			$numeroPaginas = ($registros>0) ? ceil($total/$registros) : 1;

			$tabla.='
				<div class="table-container">
				<table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
					<thead>
						<tr>
							<th class="has-text-centered">Idods</th>
							<th class="has-text-centered">Fecha</th>
							<th class="has-text-centered">Hora</th>
							<th class="has-text-centered">Cantidad</th>
							<th class="has-text-centered">Cliente</th>
							<th class="has-text-centered">Tipo</th>
							<th class="has-text-centered">Marca</th>
							<th class="has-text-centered">Tecnico</th>
							<th class="has-text-centered">Status</th>
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
								<a class="has-text-link" href="'.APP_URL.'odsView/'.(int)$rows['Idods'].'/">'.
									(int)$rows['Idods'].
								'</a>
							</td>
							<td>'.htmlspecialchars($rows['Fecha'] ?? "", ENT_QUOTES, "UTF-8").'</td>
							<td>'.htmlspecialchars($rows['Hora'] ?? "", ENT_QUOTES, "UTF-8").'</td>
							<td>'.htmlspecialchars($rows['Cantidad'] ?? "", ENT_QUOTES, "UTF-8").'</td>
							<td>'.htmlspecialchars($rows['NombreCliente'] ?? "", ENT_QUOTES, "UTF-8").'</td>
							<td>'.htmlspecialchars($rows['Tipo'] ?? "", ENT_QUOTES, "UTF-8").'</td>
							<td>'.htmlspecialchars($rows['Marca'] ?? "", ENT_QUOTES, "UTF-8").'</td>
							<td>'.htmlspecialchars($rows['NombreTecnico'] ?? "", ENT_QUOTES, "UTF-8").'</td>
							<td>'.htmlspecialchars($rows['Status'] ?? "", ENT_QUOTES, "UTF-8").'</td>
						</tr>
					';
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
						</tr>
					';
				}else{
					$tabla.='
						<tr class="has-text-centered">
							<td colspan="9">No hay registros en el sistema</td>
						</tr>
					';
				}
			}

			$tabla.='</tbody></table></div>';

			### Paginacion ###
			if($total>0 && $pagina<=$numeroPaginas){
				$tabla.='<p class="has-text-right">Mostrando movimientos <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';
				$tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url,7);
			}

			return $tabla;
		}

		/*----------  Controlador eliminar caja  ----------*/
		public function eliminarCajaControlador(){

			$id=$this->limpiarCadena($_POST['Idini']);

			if($id==1){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos eliminar la caja principal del sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

			# Verificando caja #
		    $datos=$this->ejecutarConsulta("SELECT * FROM cajai WHERE Idini='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la caja en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Verificando ventas #
		    $check_ventas=$this->ejecutarConsulta("SELECT Idini FROM venta WHERE Idini='$id' LIMIT 1");
		/*    if($check_ventas->rowCount()>0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos eliminar la caja del sistema ya que tiene ventas asociadas",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    } */

		    # Verificando usuarios #
		/*    $check_usuarios=$this->ejecutarConsulta("SELECT Idini FROM usuario WHERE Idini='$id' LIMIT 1");
		    if($check_usuarios->rowCount()>0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos eliminar la caja del sistema ya que tiene usuarios asociados",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    } */

		    $eliminarCaja=$this->eliminarRegistro("cajai","Idini",$id);

		    if($eliminarCaja->rowCount()==1){
		        $alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Caja eliminada",
					"texto"=>"La caja ha sido eliminada del sistema correctamente",
					"icono"=>"success"
				];
		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido eliminar la caja del sistema, por favor intente nuevamente",
					"icono"=>"error"
				];
		    }

		    return json_encode($alerta);
		}

		/*----------  Controlador actualizar caja  ----------*/
		public function actualizarCajaControlador(){

			$id=$this->limpiarCadena($_POST['Idini']);

			# Verificando caja #
		    $datos=$this->ejecutarConsulta("SELECT * FROM cajai WHERE Idini='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la caja en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Almacenando datos#
		    $Idini=$this->limpiarCadena($_POST['Idini']);
		    $Efectivo=$this->limpiarCadena($_POST['Efectivo']);
		 #   $caja_nombre=$this->limpiarCadena($_POST['caja_nombre']);
            $Fecha=$this->limpiarCadena($_POST['Fecha']);
		    $Hora=$this->limpiarCadena($_POST['Hora']);

		    # Verificando campos obligatorios #
		    if($Efectivo==""){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

		    # Verificando integridad de los datos #
		    if ($this->verificarDatos("/^\d{1,9}(\.\d{1,2})?$/", $Efectivo)) {
                $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El EFECTIVO no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }


		    # Comprobando numero de caja #
		 /*   if($datos['caja_numero']!=$numero){
			    $check_numero=$this->ejecutarConsulta("SELECT caja_numero FROM caja WHERE caja_numero='$numero'");
			    if($check_numero->rowCount()>0){
			    	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"El número de caja ingresado ya se encuentra registrado en el sistema",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
			    }
		    } */

		    # Comprobando nombre de caja #
		/*    if($datos['caja_nombre']!=$nombre){
			    $check_nombre=$this->ejecutarConsulta("SELECT caja_nombre FROM caja WHERE caja_nombre='$nombre'");
			    if($check_nombre->rowCount()>0){
			    	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"El nombre o código de caja ingresado ya se encuentra registrado en el sistema",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
			    }
		    } */

		    # Comprobando que el efectivo sea mayor o igual a 0 #
			$Efectivo=number_format($Efectivo,2,'.','');
			if($Efectivo<0){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No puedes colocar una cantidad de efectivo menor a 0",
					"icono"=>"error"
				];
				return json_encode($alerta);
			}

			$caja_datos_up=[
				[
					"campo_nombre"=>"caja_efectivo",
					"campo_marcador"=>":Efectivo",
					"campo_valor"=>$Efectivo
				]
			];

			$condicion=[
				"condicion_campo"=>"Idini",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("caja",$caja_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Caja actualizada",
					"texto"=>"Los datos de la caja se actualizaron correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido actualizar los datos de la caja por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

	}