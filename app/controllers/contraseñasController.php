<?php
	namespace app\controllers;
	use app\models\mainModel;
	header('Content-Type: application/json');

	class contraseñasController extends mainModel{ 
    public function listarContraseñasControlador($pagina,$registros,$url,$busqueda){

			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);

			$url=$this->limpiarCadena($url);
			$url=APP_URL.$url."/";

			$busqueda=$this->limpiarCadena($busqueda);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

			$campo = $_SESSION['userSearch_campo'] ?? 'Idasesor';

			// validar campo permitido
			$campos_validos = ['Idasesor', 'Nombre'];
				if (!in_array($campo, $campos_validos)) {
    		$campo = 'Idasesor';
			}

			if (isset($busqueda) && $busqueda != "") {

    		// Se realiza la consulta buscando tanto por Idcliente como por Nombre
    		$consulta_datos = "SELECT * FROM usuarios WHERE Idasesor LIKE '%$busqueda%' OR Nombre LIKE '%$busqueda%' ORDER BY Idasesor DESC LIMIT $inicio,$registros";
    
    		// Consulta para contar los registros encontrados
    		$consulta_total = "SELECT COUNT(Idasesor) FROM usuarios WHERE Idasesor LIKE '%$busqueda%' OR Nombre LIKE '%$busqueda%'";

			} else {
    		// Si no hay búsqueda, mostrar todos los clientes
    		$consulta_datos = "SELECT * FROM usuarios ORDER BY Idasesor DESC LIMIT $inicio,$registros";
    		$consulta_total = "SELECT COUNT(Idasesor) FROM usuarios";
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
    }