<?php
	namespace app\controllers;
	use app\models\mainModel;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
	class odsController extends mainModel{
		public function registrarOdsControlador(){
        
        // --- INICIO: FORZAR ERRORES (Solo para depurar) ---
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        // --- FIN: FORZAR ERRORES ---

        $get = function($key, $default = '') {
            return isset($_POST[$key]) ? $_POST[$key] : $default;
        };

        // === Leer TODOS los campos del formulario ===
        $Idcliente   = $this->limpiarCadena($get('Idcliente',''));
        $Idasesor    = $this->limpiarCadena($get('Idasesor',''));
        $IdTecnico   = $this->limpiarCadena($get('IdTecnico','')); // <-- CAMBIO 1: Leer el Técnico

        $Tipo        = $this->limpiarCadena($get('Tipo',''));
        $Marca       = $this->limpiarCadena($get('Marca',''));
        $Modelo      = $this->limpiarCadena($get('Modelo',''));
        $Noserie     = $this->limpiarCadena($get('Noserie',''));
        $Color       = $this->limpiarCadena($get('Color',''));
        $Contrasena  = $this->limpiarCadena($get('Contrasena',''));

        $Respaldo    = $this->limpiarCadena($get('Respaldo',''));
        $Uso         = $this->limpiarCadena($get('Uso',''));
        $Carpeta     = $this->limpiarCadena($get('Carpeta',''));

        $Problema    = $this->limpiarCadena($get('Problema',''));
        $Inspeccion  = $this->limpiarCadena($get('Inspeccion',''));
        $Accesorios  = $this->limpiarCadena($get('Accesorios',''));

        $Fecha       = $this->limpiarCadena($get('Fecha', date('Y-m-d')));
        $Hora        = date('H:i:s');

        $Tiempo      = $this->limpiarCadena($get('Tiempo',''));
        $Status      = $this->limpiarCadena($get('Status','Recepcion'));
        
        $Garantia    = $this->limpiarCadena($get('Garantia','0'));
        $Garantia    = ($Garantia==='1' || $Garantia===1) ? 1 : 0;
        $Odsanterior = $this->limpiarCadena($get('Odsanterior',''));

        $Sucursal    = $this->limpiarCadena($get('Sucursal',''));
        $Componentes = $this->limpiarCadena($get('Componentes',''));
     //   $Reparacion  = $this->limpiarCadena($get('Reparacion',''));
		// === INICIO: PROCESAR SERVICIOS ===
$servicios_json = $get('Servicios', '[]'); // Obtiene el JSON de servicios
$servicios_data = json_decode($servicios_json, true);

$reparacion_items = [];
$costorep_items = [];

// Verificamos que sea un array y lo recorremos
if (is_array($servicios_data) && !empty($servicios_data)) {
    foreach ($servicios_data as $item) {
        // Asumo que tu JSON tiene "servicio" y "costo"
        if (isset($item['servicio'])) {
            $reparacion_items[] = $this->limpiarCadena($item['servicio']);
        }
        if (isset($item['costo'])) {
            // Limpiamos comas por si el usuario escribe "1,000"
            $costo_limpio = str_replace(',', '', $item['costo']);
            $costorep_items[] = $this->limpiarCadena($costo_limpio);
        }
    }
}

// Convertimos los arrays a strings separadas por comas
$Reparacion = implode(', ', $reparacion_items);
$Costorep = implode(', ', $costorep_items);
// === FIN: PROCESAR SERVICIOS ===
        // === Validaciones (Devolviendo JSON para el script local) ===
        if($Tipo==="" || $Marca==="" || $Modelo==="" || $Noserie===""){
            return [
                "success"=>false, 
                "error"=>"Datos incompletos: Completa Tipo, Marca, Modelo y No. Serie."
            ];
        }
        if($IdTecnico===""){
             return [
                "success"=>false, 
                "error"=>"Dato requerido: El campo Técnico es obligatorio."
            ];
        }
        
        $pdo = \app\models\mainModel::conectar();

        // === Garantía / Odsanterior ===
        if($Garantia===1){
            if($Odsanterior===""){
                return ["success"=>false, "error"=>"Para garantía debes indicar la ODS anterior."];
            }
            $stmtChk = $pdo->prepare("SELECT Idods FROM ods WHERE Idods = :id LIMIT 1");
            $stmtChk->execute([":id"=>$Odsanterior]);
            if(!$stmtChk->fetchColumn()){
                return ["success"=>false, "error"=>"La ODS anterior indicada ($Odsanterior) no existe."];
            }
            $Odsanterior = (int)$Odsanterior;
        } else {
            $Odsanterior = 0;
        }

        // === INSERT (Con IdTecnico) ===
        $sql = "INSERT INTO ods
            (Idcliente, Idasesor, IdTecnico, Tipo, Marca, Modelo, Noserie, Color, Contrasena,
            Respaldo, Uso, Carpeta, Problema, Inspeccion, Accesorios,
            Fecha, Hora, Tiempo, Status, Garantia, Odsanterior, Sucursal, Componentes, Reparacion, Costorep)
            VALUES
            (:Idcliente,:Idasesor,:IdTecnico,:Tipo,:Marca,:Modelo,:Noserie,:Color,:Contrasena,
            :Respaldo,:Uso,:Carpeta,:Problema,:Inspeccion,:Accesorios,
            :Fecha,:Hora,:Tiempo,:Status,:Garantia,:Odsanterior,:Sucursal,:Componentes,:Reparacion,:Costorep)";

        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            ':Idcliente'   => ($Idcliente !== '' ? $Idcliente : null),
            ':Idasesor'    => ($Idasesor  !== '' ? $Idasesor  : null),
            ':IdTecnico'   => ($IdTecnico !== '' ? $IdTecnico : null), // <-- CAMBIO 2: Añadir al execute

            ':Tipo'        => $Tipo,
            ':Marca'       => $Marca,
            ':Modelo'      => $Modelo,
            ':Noserie'     => $Noserie,
            ':Color'       => $Color,
            ':Contrasena'  => $Contrasena,
            ':Respaldo'    => $Respaldo,
            ':Uso'         => $Uso,
            ':Carpeta'     => $Carpeta,
            ':Problema'    => $Problema,
            ':Inspeccion'  => $Inspeccion,
            ':Accesorios'  => $Accesorios,
            ':Fecha'       => $Fecha,
            ':Hora'        => $Hora,
            ':Tiempo'      => $Tiempo,
            ':Status'      => $Status,
            ':Garantia'    => $Garantia,
            ':Odsanterior' => $Odsanterior,
            ':Sucursal'    => $Sucursal,
            ':Componentes' => $Componentes,
            ':Reparacion'  => $Reparacion,
			':Costorep'    => $Costorep
        ]);

        // --- CAMBIO 3: Devolver el error de SQL real ---
        if(!$ok){
            $errorInfo = $stmt->errorInfo();
            $sqlError = $errorInfo[2]; // Error de SQL

            return [
                "success" => false,
                "error" => "Error de SQL: " . $sqlError 
            ];
        }

        // --- CAMBIO 4: Devolver el JSON correcto al tener éxito ---
        $idInsertado = (int)$pdo->lastInsertId();
        return [
            "success" => true,
            "id" => $idInsertado
        ];
    }
		/*----------  Controlador listar ODS ----------*/
		public function listarOdsControlador($pagina,$registros,$url,$busqueda){

			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);
			$url=$this->limpiarCadena($url);
			$url=APP_URL.$url."/";
			$busqueda=$this->limpiarCadena($busqueda);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

			// 1. CONSULTAS SQL
			if(isset($busqueda) && $busqueda!=""){
				$consulta_datos = "SELECT o.*, c.Nombre AS cliente_nombre, p.Nombre AS asesor_nombre, p2.Nombre AS tecnico_nombre
								FROM ods o
								LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
								LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
								LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
								WHERE (o.Idods LIKE '%$busqueda%' OR c.Nombre LIKE '%$busqueda%')
								ORDER BY o.Idods DESC LIMIT $inicio,$registros";

				$consulta_total = "SELECT COUNT(o.Idods) FROM ods o 
								LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
								WHERE (o.Idods LIKE '%$busqueda%' OR c.Nombre LIKE '%$busqueda%')";
			}else{
				$consulta_datos = "SELECT o.*, c.Nombre AS cliente_nombre, p.Nombre AS asesor_nombre, p2.Nombre AS tecnico_nombre
								FROM ods o
								LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
								LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
								LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
								ORDER BY o.Idods DESC LIMIT $inicio,$registros";
				$consulta_total = "SELECT COUNT(o.Idods) FROM ods o";
			}

			$datos = $this->ejecutarConsulta($consulta_datos);
			$datos = $datos->fetchAll();
			$total = $this->ejecutarConsulta($consulta_total);
			$total = (int) $total->fetchColumn();
			$numeroPaginas =ceil($total/$registros);

			// 2. ESTILOS CSS (Solo para el status)
			$tabla.='
			<style>
				.estado-recepcion   { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important; color: white !important; }
				.estado-diagnostico { background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%) !important; color: white !important; }
				.estado-presupuesto { background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%) !important; color: white !important; }
				.estado-autorizacion{ background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important; color: white !important; }
				.estado-standby     { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%) !important; color: white !important; }
				.estado-reparacion  { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important; color: white !important; }
				.estado-refacciones { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important; color: white !important; }
				.estado-listoe      { background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; color: white !important; }
				.estado-almacen     { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%) !important; color: white !important; }
				.estado-entregado   { background: linear-gradient(135deg, #facc15 0%, #eab308 100%) !important; color: #444 !important; }
				.estado-seguimiento { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; color: white !important; }
				.estado-cancelado   { background: linear-gradient(135deg, #6b7280 0%, #374151 100%) !important; color: white !important; }
				
				select.status-dropdown {
					width: 135px !important;
					height: 30px !important; 
					font-weight: 600;
					font-size: 0.85rem !important;
					padding-top: 0; padding-bottom: 0; padding-left: 10px;
					border: none; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
					transition: all 0.3s ease;
				}
				select.status-dropdown option { background-color: white; color: #333; font-weight: normal; }
				.select.is-rounded { height: 30px !important; width: auto !important; }
			</style>

			<div class="table-container">
				<table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth" style="font-family: \'Segoe UI\', system-ui, sans-serif; font-size: 1.05rem;">
					<thead>
						<tr style="background-color: #f9fafb; color: #6b7280; text-transform: uppercase; font-size: 1rem;">
							<th class="has-text-centered">ODS</th>
							<th class="has-text-centered">Cliente</th>
							<th class="has-text-centered">Asesor</th>
							<th class="has-text-centered">Tecnico</th>
							<th class="has-text-centered">Status</th>
							<th class="has-text-centered">Tipo</th>
							<th class="has-text-centered">Marca</th>
							<th class="has-text-centered">Fecha</th>
							<th class="has-text-centered">Total</th>
							<th class="has-text-centered">Cuenta</th>
							<th class="has-text-centered">Fecha Entrega</th>
						</tr>
					</thead>
					<tbody>
			';

			if($total>=1 && $pagina<=$numeroPaginas){
				$contador=$inicio+1;
				$pag_inicio=$inicio+1;
				
				foreach($datos as $rows){
					
					// PREPARAR STATUS (Colores)
					$st = mb_strtoupper($rows['Status'], 'UTF-8');
					$claseColor = '';
					if($st == "RECEPCION")     $claseColor = 'estado-recepcion';
					elseif($st == "DIAGNOSTICO") $claseColor = 'estado-diagnostico';
					elseif($st == "PRESUPUESTO") $claseColor = 'estado-presupuesto';
					elseif($st == "AUTORIZACION")$claseColor = 'estado-autorizacion';
					elseif($st == "REPARACION")  $claseColor = 'estado-reparacion';
					elseif($st == "REFACCIONES") $claseColor = 'estado-refacciones';
					elseif($st == "STANDBY")     $claseColor = 'estado-standby';
					elseif($st == "ALMACEN")     $claseColor = 'estado-almacen';
					elseif($st == "LISTOE" || $st == "LENTREGAR") $claseColor = 'estado-listoe';
					elseif($st == "ENTREGADO")   $claseColor = 'estado-entregado';
					elseif($st == "SEGUIMIENTO") $claseColor = 'estado-seguimiento';
					elseif($st == "CANCELADO")   $claseColor = 'estado-cancelado';
					
					$tabla.='
						<tr class="has-text-centered" >
							<td>
								'.$rows['Idods'].'
								<a href="'.APP_URL.'odsView/'.$rows['Idods'].'/" target="_blank" class="button is-small is-link" title="Ver ODS">
									<i class="fas fa-eye"></i>
								</a>
							</td>
							<td>'.$rows['cliente_nombre'].'</td>
							<td>'.$rows['asesor_nombre'].'</td>
							
							<td>'.$rows['tecnico_nombre'].'</td>

							<td>
								<div class="select is-rounded">
									<select name="Status" class="status-dropdown '.$claseColor.'" 
											onchange="actualizarStatusDirecto('.$rows['Idods'].', this.value)">
										<option value="Recepcion"    '.($st == "RECEPCION"    ? 'selected' : '').'>Recepción</option>
										<option value="Diagnostico"  '.($st == "DIAGNOSTICO"  ? 'selected' : '').'>Diagnóstico</option>
										<option value="Presupuesto"  '.($st == "PRESUPUESTO"  ? 'selected' : '').'>Presupuesto</option>
										<option value="Autorizacion" '.($st == "AUTORIZACION" ? 'selected' : '').'>Autorización</option>
										<option value="Reparacion"   '.($st == "REPARACION"   ? 'selected' : '').'>Reparación</option>
										<option value="Refacciones"  '.($st == "REFACCIONES"  ? 'selected' : '').'>Refacciones</option>
										<option value="StandBy"      '.($st == "STANDBY"      ? 'selected' : '').'>StandBy</option>
										<option value="Almacen"      '.($st == "ALMACEN"      ? 'selected' : '').'>Almacén</option>
										<option value="Listoe"       '.($st == "LISTOE" || $st == "LENTREGAR" ? 'selected' : '').'>Listo para Entregar</option>
										<option value="Entregado"    '.($st == "ENTREGADO"    ? 'selected' : '').'>Entregado</option>
										<option value="Seguimiento"  '.($st == "SEGUIMIENTO"  ? 'selected' : '').'>Seguimiento</option>
										<option value="Cancelado"    '.($st == "CANCELADO"    ? 'selected' : '').'>Cancelado</option>
									</select>
								</div>
							</td>

							<td>'.$rows['Tipo'].'</td>
							<td>'.$rows['Marca'].'</td>
							<td>'.$rows['Fecha'].'</td>
							<td>$'.$rows['Total'].'</td>
							<td>$'.$rows['Cuenta'].'</td>
							<td>'.$rows['Fechaentrega'].'</td>
						</tr>
					';
					$contador++;
				}
				$pag_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='
						<tr class="has-text-centered" >
							<td colspan="13">
								<a href="'.$url.'1/" class="button is-link is-rounded is-small mt-4 mb-4">
									Haga clic acá para recargar el listado
								</a>
							</td>
						</tr>
					';
				}else{
					$tabla.='
						<tr class="has-text-centered" >
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
		
 		/* para hacer la búsqueda desde dashboard*/
		public function listarDashboardControlador($pagina, $registros, $url, $busqueda, $filtroEstado = "") {
    
    // 1. LIMPIEZA
    $pagina = $this->limpiarCadena($pagina);
    $registros = $this->limpiarCadena($registros);
    $url = $this->limpiarCadena($url);
    $url = APP_URL . $url . "/";
    $busqueda = $this->limpiarCadena($busqueda);
    $filtroEstado = $this->limpiarCadena($filtroEstado);
    $tabla = "";

    $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
    $inicio = (isset($pagina) && $pagina > 0) ? (($pagina * $registros) - $registros) : 0;
    
    // --- [FILTROS DE SESIÓN] ---
    $idUser = $_SESSION['id'] ?? 0;
    
    // FILTRO ESTRICTO: Solo donde SOY EL ASESOR (ni técnico ni nada más)
    $condicionUsuario = " AND o.Idasesor = '$idUser' ";

    // Filtro Estatus
    $condicionStatus = "";
    if($filtroEstado != "" && $filtroEstado != "Todos"){
        $condicionStatus = " AND o.Status = '$filtroEstado' ";
    }

    // 2. CONSULTAS SQL
    if (isset($busqueda) && $busqueda != "") {
        $consulta_datos = "SELECT o.*, c.Nombre AS cliente_nombre, p.Nombre AS asesor_nombre, p2.Nombre AS tecnico_nombre
                           FROM ods o
                           LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
                           LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
                           LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
                           WHERE (o.Idods LIKE '%$busqueda%' OR c.Nombre LIKE '%$busqueda%') 
                           $condicionUsuario 
                           $condicionStatus
                           ORDER BY o.Idods DESC LIMIT $inicio,$registros";

        $consulta_total = "SELECT COUNT(o.Idods) FROM ods o
                           LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
                           WHERE (o.Idods LIKE '%$busqueda%' OR c.Nombre LIKE '%$busqueda%')
                           $condicionUsuario 
                           $condicionStatus";
    } else {
        $consulta_datos = "SELECT o.*, c.Nombre AS cliente_nombre, p.Nombre AS asesor_nombre, p2.Nombre AS tecnico_nombre
                           FROM ods o
                           LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
                           LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
                           LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
                           WHERE 1=1 
                           $condicionUsuario 
                           $condicionStatus
                           ORDER BY o.Idods DESC LIMIT $inicio,$registros";

        $consulta_total = "SELECT COUNT(o.Idods) FROM ods o 
                           WHERE 1=1 
                           $condicionUsuario 
                           $condicionStatus";
    }

    $datos = $this->ejecutarConsulta($consulta_datos);
    $datos = $datos->fetchAll();
    $total = $this->ejecutarConsulta($consulta_total);
    $total = (int) $total->fetchColumn();
    $numeroPaginas = ceil($total / $registros);
    
    // 3. ESTILOS CSS (Copiado EXACTO de tu dashboardTec)
    $tabla.='
    <style>
        .estado-recepcion    { background-image: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important; color: white !important; }
        .estado-diagnostico  { background-image: linear-gradient(135deg, #eab308 0%, #ca8a04 100%) !important; color: white !important; }
        .estado-presupuesto  { background-image: linear-gradient(135deg, #a855f7 0%, #9333ea 100%) !important; color: white !important; }
        .estado-autorizacion { background-image: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important; color: white !important; }
        .estado-standby      { background-image: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%) !important; color: white !important; }
        .estado-reparacion   { background-image: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important; color: white !important; }
        .estado-refacciones  { background-image: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important; color: white !important; }
        .estado-listoe       { background-image: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; color: white !important; }
        .estado-almacen      { background-image: linear-gradient(135deg, #ec4899 0%, #db2777 100%) !important; color: white !important; }
        .estado-entregado    { background-image: linear-gradient(135deg, #facc15 0%, #eab308 100%) !important; color: #374151 !important; }
        .estado-cancelado    { background-image: linear-gradient(135deg, #6b7280 0%, #374151 100%) !important; color: white !important; }
        
        select.status-dropdown { width: 135px !important; height: 30px !important; font-weight: 600; font-size: 0.85rem !important; padding-left: 10px; border: none; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: all 0.3s ease; }
        select.status-dropdown option { background-color: white; color: #333; font-weight: normal; }
        .select.is-rounded { height: 30px !important; width: auto !important; }

        .badge-tiempo { font-size: 0.8rem; font-weight: bold; padding: 3px 8px; border-radius: 12px; white-space: nowrap; display: inline-block; width: 100%; }
        .tiempo-verde { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .tiempo-amarillo { background-color: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .tiempo-rojo { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .tiempo-finalizado { background-color: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
    </style>

    <div class="table-container">
        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth" style="font-family: \'Segoe UI\', system-ui, sans-serif; font-size: 0.95rem;">
            <thead>
                <tr style="background-color: #f9fafb; color: #6b7280; text-transform: uppercase; font-size: 0.85rem;">
                    <th class="has-text-centered">ODS</th>
                    <th class="has-text-centered">Cliente</th>
                    <th class="has-text-centered">Asesor</th>
                    <th class="has-text-centered">Tecnico</th>
                    <th class="has-text-centered">Status</th>
                    <th class="has-text-centered">Antigüedad</th>
                    <th class="has-text-centered">Tipo</th>
                    <th class="has-text-centered">Marca</th>
                    <th class="has-text-centered">Fecha Ingreso</th>
                </tr>
            </thead>
            <tbody>
    ';

    if ($total >= 1 && $pagina <= $numeroPaginas) {
        $contador = $inicio + 1;
        $pag_inicio = $inicio + 1;
        
        foreach ($datos as $rows) {
            $st = mb_strtoupper($rows['Status'], 'UTF-8');
            $claseColor = '';
            if(strpos($st, "RECEPC") !== false) $claseColor = 'estado-recepcion';
            elseif(strpos($st, "DIAGN") !== false) $claseColor = 'estado-diagnostico';
            elseif(strpos($st, "PRESUP") !== false) $claseColor = 'estado-presupuesto';
            elseif(strpos($st, "AUTORIZ")!== false)$claseColor = 'estado-autorizacion';
            elseif(strpos($st, "REPARA") !== false)  $claseColor = 'estado-reparacion';
            elseif(strpos($st, "REFACC") !== false) $claseColor = 'estado-refacciones';
            elseif(strpos($st, "STAND")  !== false) $claseColor = 'estado-standby';
            elseif(strpos($st, "ALMAC")  !== false) $claseColor = 'estado-almacen';
            elseif(strpos($st, "LIST")   !== false) $claseColor = 'estado-listoe';
            elseif(strpos($st, "ENTREG") !== false) $claseColor = 'estado-entregado';
            elseif(strpos($st, "SEGUIM") !== false) $claseColor = 'estado-seguimiento';
            elseif(strpos($st, "CANCEL") !== false) $claseColor = 'estado-cancelado';

            // Antigüedad
            $textoTiempo = ""; $claseTiempo = "";
            if ($rows['Fechaentrega'] != "" && $rows['Fechaentrega'] != "0000-00-00" && $rows['Fechaentrega'] != NULL) {
                $textoTiempo = '<i class="fas fa-check"></i> Entregado'; $claseTiempo = "tiempo-finalizado";
            } else {
                $fechaIngreso = new \DateTime($rows['Fecha']);
                $fechaHoy     = new \DateTime();
                $diferencia   = $fechaIngreso->diff($fechaHoy);
                $dias = $diferencia->days;
                if ($dias <= 7) { $textoTiempo = $dias . " días"; $claseTiempo = "tiempo-verde"; } 
                elseif ($dias <= 30) { $textoTiempo = floor($dias / 7) . " sem"; $claseTiempo = "tiempo-amarillo"; } 
                else { $textoTiempo = floor($dias / 30) . " mes"; $claseTiempo = "tiempo-rojo"; }
            }

            $tabla .= '
                <tr class="has-text-centered" >
                    <td>
                        ' . $rows['Idods'] . '
                        <a href="' . APP_URL . 'odsView/' . $rows['Idods'] . '/" target="_blank" class="button is-small is-link" title="Ver ODS">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                    <td>' . $rows['cliente_nombre'] . '</td>
                    <td>' . $rows['asesor_nombre'] . '</td>
                    <td>' . $rows['tecnico_nombre'] . '</td>
                    <td>
                        <div class="select is-rounded">
                            <select name="Status" class="status-dropdown '.$claseColor.'" 
                                    onchange="actualizarStatusDirecto('.$rows['Idods'].', this.value)">
                                <option value="Recepcion"    '.($st == "RECEPCION"    ? 'selected' : '').'>Recepción</option>
                                <option value="Diagnostico"  '.($st == "DIAGNOSTICO"  ? 'selected' : '').'>Diagnóstico</option>
                                <option value="Presupuesto"  '.($st == "PRESUPUESTO"  ? 'selected' : '').'>Presupuesto</option>
                                <option value="Autorizacion" '.($st == "AUTORIZACION" ? 'selected' : '').'>Autorización</option>
                                <option value="Reparacion"   '.($st == "REPARACION"   ? 'selected' : '').'>Reparación</option>
                                <option value="Refacciones"  '.($st == "REFACCIONES"  ? 'selected' : '').'>Refacciones</option>
                                <option value="StandBy"      '.($st == "STANDBY"      ? 'selected' : '').'>StandBy</option>
                                <option value="Almacen"      '.($st == "ALMACEN"      ? 'selected' : '').'>Almacén</option>
                                <option value="Listoe"       '.($st == "LISTOE" || $st == "LENTREGAR" ? 'selected' : '').'>Listo para Entregar</option>
                                <option value="Entregado"    '.($st == "ENTREGADO"    ? 'selected' : '').'>Entregado</option>
                                <option value="Seguimiento"  '.($st == "SEGUIMIENTO"  ? 'selected' : '').'>Seguimiento</option>
                                <option value="Cancelado"    '.($st == "CANCELADO"    ? 'selected' : '').'>Cancelado</option>
                            </select>
                        </div>
                    </td>
                    <td><div class="badge-tiempo '.$claseTiempo.'"><i class="far fa-clock"></i> '.$textoTiempo.'</div></td>
                    <td>' . $rows['Tipo'] . '</td>
                    <td>' . $rows['Marca'] . '</td>
                    <td>' . $rows['Fecha'] . '</td>
                </tr>
            ';
            $contador++;
        }
        $pag_final = $contador - 1;
    } else {
        if ($total >= 1) {
            $tabla .= '<tr class="has-text-centered"><td colspan="14"><a href="' . $url . '1/" class="button is-link is-rounded is-small mt-4 mb-4">Recargar listado</a></td></tr>';
        } else {
            $tabla .= '<tr class="has-text-centered"><td colspan="14"><div class="has-text-grey p-4"><i class="fas fa-folder-open fa-2x mb-2"></i><br>No se encontraron registros.</div></td></tr>';
        }
    }
    $tabla .= '</tbody></table></div>';
    
    if ($total > 0 && $pagina <= $numeroPaginas) {
        $tabla .= '<p class="has-text-right">Mostrando ODS <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
        $tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);
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
		/* Copia esta función y reemplaza la que tienes en:
       app/controllers/odsController.php
    */
    /* Reemplaza tu función cambiarStatusOdsControlador con esta */
    public function cambiarStatusOdsControlador(): array {
        try {
            // 1. Asegurar sesión iniciada
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            $pdo   = self::conectar();
            $idods = (int)($_POST['Idods'] ?? 0);
            
            // CORRECCIÓN: Usamos $this->limpiarCadena correctamente
            // (Con $this-> y respetando las mayúsculas de tu método)
            $statusRaw = $_POST['Status'] ?? '';
            $nuevo = strtoupper($this->limpiarCadena($statusRaw));

            if ($idods <= 0 || $nuevo === '') {
                return ['success' => false, 'msg' => 'Datos incompletos'];
            }

            // 2. Obtener estado actual
            $q = $pdo->prepare("SELECT TRIM(Status) AS s FROM ods WHERE Idods=:id");
            $q->execute([':id' => $idods]);
            $actualRaw = $q->fetchColumn();

            if ($actualRaw === false) return ['success' => false, 'msg' => 'ODS no encontrada'];
            $actual = strtoupper($actualRaw);

            if ($actual === $nuevo) return ['success' => true, 'msg' => 'Sin cambios'];

            // 3. Validar Transición (Tus reglas)
            $permitidas = [
                'RECEPCION'    => ['RECEPCION','DIAGNOSTICO','PRESUPUESTO','STANDBY','AUTORIZACION','REPARACION','REFACCIONES','LISTOE','ENTREGADO','SEGUIMIENTO','ALMACEN','DBAJA','CANCELADO'],
                'DIAGNOSTICO'  => ['DIAGNOSTICO','RECEPCION','PRESUPUESTO','STANDBY','AUTORIZACION','REPARACION','REFACCIONES','LISTOE','ENTREGADO','SEGUIMIENTO','ALMACEN','DBAJA','CANCELADO'],
                'PRESUPUESTO'  => ['PRESUPUESTO','DIAGNOSTICO','STANDBY','AUTORIZACION','REPARACION','REFACCIONES','LISTOE','ENTREGADO','ALMACEN','DBAJA','CANCELADO'],
                'STANDBY'      => ['STANDBY','PRESUPUESTO','AUTORIZACION','REPARACION','REFACCIONES','LISTOE','ENTREGADO','DBAJA','CANCELADO'],
                'AUTORIZACION' => ['AUTORIZACION','STANDBY','PRESUPUESTO','REPARACION','REFACCIONES','LISTOE','ENTREGADO','SEGUIMIENTO','ALMACEN','DBAJA','CANCELADO'],
                'REPARACION'   => ['REPARACION','REFACCIONES','DIAGNOSTICO','PRESUPUESTO','AUTORIZACION','LISTOE','ENTREGADO','ALMACEN','DBAJA','CANCELADO'],
                'REFACCIONES'  => ['REFACCIONES','REPARACION','STANDBY','LISTOE','ENTREGADO','ALMACEN','DBAJA','CANCELADO'],
                'LISTOE'       => ['LISTOE','STANDBY','REPARACION','ENTREGADO','ALMACEN','DBAJA','CANCELADO'],
                'ENTREGADO'    => ['ENTREGADO','REPARACION','SEGUIMIENTO'],
                'SEGUIMIENTO'  => ['ENTREGADO'],
                'ALMACEN'      => ['REFACCIONES'],
                'DBAJA'        => ['DBAJA','SEGUIMIENTO'],
                'CANCELADO'    => []
            ];

            $ok = isset($permitidas[$actual]) ? in_array($nuevo, $permitidas[$actual], true) : true;
            if (!$ok) return ['success' => false, 'msg' => "Transición no permitida ($actual → $nuevo)"];

            // 4. INICIAR TRANSACCIÓN
            $pdo->beginTransaction();

            // A. Actualizar ODS
            $sqlUpdate = "UPDATE ods SET Status=:st";
            $parametros = [':st' => $nuevo, ':id' => $idods];

            // Detectamos si es una Entrega con datos extra
            if ($nuevo === 'ENTREGADO' && isset($_POST['Entrego']) && isset($_POST['Fechaentrega'])) {
                $sqlUpdate .= ", Entrego=:ent, Fechaentrega=:fec";
                
                // CORRECCIÓN: Usamos $this->limpiarCadena aquí también
                $parametros[':ent'] = $this->limpiarCadena($_POST['Entrego']);
                $parametros[':fec'] = $this->limpiarCadena($_POST['Fechaentrega']);
            }

            $sqlUpdate .= " WHERE Idods=:id";
            $up = $pdo->prepare($sqlUpdate);
            $up->execute($parametros);

            // B. Auditoría / Reportetec
            $usuarioId = $_SESSION['Idasesor'] ?? $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? null;
            $nombreTecnico = $_SESSION['nombre'] ?? $_SESSION['Nombre'] ?? 'Sistema'; 
            
            if(isset($_SESSION['apellido'])) $nombreTecnico .= ' ' . $_SESSION['apellido'];

            if ($usuarioId) {
                $texto = "Cambio de status: {$actual} → {$nuevo}";
                $horaActual = date('H:i:s');
                $log = $pdo->prepare("INSERT INTO reportetec (Idods, Reporte, Tecnico, Fecha, Hora) VALUES (:idods, :reporte, :uid, CURDATE(), :hora)");
                $log->execute([':idods' => $idods, ':reporte' => $texto, ':uid' => $usuarioId, ':hora' => $horaActual]);
            }

            // 5. CONFIRMAR CAMBIOS
            $pdo->commit();

            return [
                'success' => true,
                'msg'     => 'Status actualizado',
                'status'  => $nuevo,
                'tecnico' => $nombreTecnico,
                'hora'    => date('H:i:s'),
                'fecha'   => date('Y-m-d')
            ];

        } catch (\Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            return ['success' => false, 'msg' => 'Error: ' . $e->getMessage()];
        }
    }
    /*----------  Controlador listar ODS Personal (CON FILTRO DE FECHAS Y STATUS ROBUSTO)  ----------*/
	public function listarOdsPersonalControlador($pagina, $registros, $url, $busqueda, $filtroEstado = "") {
		
		$pagina = $this->limpiarCadena($pagina);
		$registros = $this->limpiarCadena($registros);
		$url = $this->limpiarCadena($url);
		$url = APP_URL . $url . "/";
		$busqueda = $this->limpiarCadena($busqueda);
		$filtroEstado = $this->limpiarCadena($filtroEstado); // <-- LIMPIEZA AGREGADA
		$tabla = "";

		$pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
		$inicio = (isset($pagina) && $pagina > 0) ? (($pagina * $registros) - $registros) : 0;
		$campo  = $_SESSION['odsSearch_campo'] ?? 'Idods';

		if (!in_array($campo, ['Idods'])) { $campo = 'Idods'; }

		// FILTROS BASICOS
		$idTecnicoSesion = $_SESSION['id'] ?? 0;
		$filtroTecnico = " AND o.IdTecnico = $idTecnicoSesion";

		// --- FILTRO DE ESTADO (MODIFICADO) ---
		// Si se eligió un estado específico en el select, usamos ese.
		// Si dice "Todos" o está vacío, usamos la lista de activos por defecto.
		if($filtroEstado != "" && $filtroEstado != "Todos"){
			$filtroStatus = " AND o.Status = '$filtroEstado'";
		} else {
			$filtroStatus = " AND (
				UPPER(o.Status) LIKE '%REPARACI%' OR 
				UPPER(o.Status) LIKE '%DIAGN%' OR 
				UPPER(o.Status) LIKE '%REFACCIONES%'
			)";
		}
		// -------------------------------------

		// Filtro Fechas
		$filtroFecha = "";
		if (isset($_SESSION['start_date']) && isset($_SESSION['end_date'])) {
			$f_inicio = $_SESSION['start_date'];
			$f_fin    = $_SESSION['end_date'];
			$filtroFecha = " AND (DATE(o.Fecha) BETWEEN '$f_inicio' AND '$f_fin') ";
		}

		// SQL
		if (isset($busqueda) && $busqueda != "") {
			$consulta_datos = "SELECT o.*, c.Nombre AS cliente_nombre, p.Nombre AS asesor_nombre, p2.Nombre AS tecnico_nombre
							FROM ods o
							LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
							LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
							LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
							WHERE o.$campo LIKE '%$busqueda%' $filtroTecnico $filtroStatus $filtroFecha
							ORDER BY o.Idods DESC LIMIT $inicio,$registros";

			$consulta_total = "SELECT COUNT(o.Idods) FROM ods o 
							WHERE o.$campo LIKE '%$busqueda%' $filtroTecnico $filtroStatus $filtroFecha";
		} else {
			$consulta_datos = "SELECT o.*, c.Nombre AS cliente_nombre, p.Nombre AS asesor_nombre, p2.Nombre AS tecnico_nombre
							FROM ods o
							LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
							LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
							LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
							WHERE 1=1 $filtroTecnico $filtroStatus $filtroFecha
							ORDER BY o.Idods DESC LIMIT $inicio,$registros";

			$consulta_total = "SELECT COUNT(o.Idods) FROM ods o 
							WHERE 1=1 $filtroTecnico $filtroStatus $filtroFecha";
		}

		$datos = $this->ejecutarConsulta($consulta_datos);
		$datos = $datos->fetchAll();
		$total = $this->ejecutarConsulta($consulta_total);
		$total = (int) $total->fetchColumn();
		$numeroPaginas = ceil($total / $registros);
		
		// ESTILOS CSS
		$tabla.='
		<style>
			/* DEFINICIÓN DE COLORES EXACTOS */
			.estado-recepcion    { background-image: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important; color: white !important; }
			.estado-diagnostico  { background-image: linear-gradient(135deg, #eab308 0%, #ca8a04 100%) !important; color: white !important; }
			.estado-presupuesto  { background-image: linear-gradient(135deg, #a855f7 0%, #9333ea 100%) !important; color: white !important; }
			.estado-autorizacion { background-image: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important; color: white !important; }
			.estado-standby      { background-image: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%) !important; color: white !important; }
			.estado-reparacion   { background-image: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important; color: white !important; }
			.estado-refacciones  { background-image: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important; color: white !important; }
			.estado-listoe       { background-image: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; color: white !important; }
			.estado-almacen      { background-image: linear-gradient(135deg, #ec4899 0%, #db2777 100%) !important; color: white !important; }
			.estado-seguimiento  { background-image: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; color: white !important; }
			.estado-entregado    { background-image: linear-gradient(135deg, #facc15 0%, #eab308 100%) !important; color: #374151 !important; }
			.estado-cancelado    { background-image: linear-gradient(135deg, #6b7280 0%, #374151 100%) !important; color: white !important; }
			
			select.status-dropdown { width: 135px !important; height: 30px !important; font-weight: 600; font-size: 0.85rem !important; padding-left: 10px; border: none; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: all 0.3s ease; }
			select.status-dropdown option { background-color: white; color: #333; font-weight: normal; }
			.select.is-rounded { height: 30px !important; width: auto !important; }

			.badge-tiempo { font-size: 0.8rem; font-weight: bold; padding: 3px 8px; border-radius: 12px; white-space: nowrap; display: inline-block; width: 100%; }
			.tiempo-verde { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
			.tiempo-amarillo { background-color: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
			.tiempo-rojo { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
			.tiempo-finalizado { background-color: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
		</style>

		<div class="table-container">
			<table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth" style="font-family: \'Segoe UI\', system-ui, sans-serif; font-size: 0.95rem;">
				<thead>
					<tr style="background-color: #f9fafb; color: #6b7280; text-transform: uppercase; font-size: 0.85rem;">
						<th class="has-text-centered">ODS</th>
						<th class="has-text-centered">Cliente</th>
						<th class="has-text-centered">Asesor</th>
						<th class="has-text-centered">Tecnico</th>
						<th class="has-text-centered">Status</th>
						<th class="has-text-centered">Antigüedad</th>
						<th class="has-text-centered">Tipo</th>
						<th class="has-text-centered">Marca</th>
						<th class="has-text-centered">Fecha Ingreso</th>
					</tr>
				</thead>
				<tbody>
		';

		if ($total >= 1 && $pagina <= $numeroPaginas) {
			$contador = $inicio + 1;
			$pag_inicio = $inicio + 1;
			
			foreach ($datos as $rows) {
				$st = mb_strtoupper($rows['Status'], 'UTF-8');
				$claseColor = '';
				if(strpos($st, "RECEPC") !== false) $claseColor = 'estado-recepcion';
				elseif(strpos($st, "DIAGN") !== false) $claseColor = 'estado-diagnostico';
				elseif(strpos($st, "PRESUP") !== false) $claseColor = 'estado-presupuesto';
				elseif(strpos($st, "AUTORIZ")!== false)$claseColor = 'estado-autorizacion';
				elseif(strpos($st, "REPARA") !== false)  $claseColor = 'estado-reparacion';
				elseif(strpos($st, "REFACC") !== false) $claseColor = 'estado-refacciones';
				elseif(strpos($st, "STAND")  !== false) $claseColor = 'estado-standby';
				elseif(strpos($st, "ALMAC")  !== false) $claseColor = 'estado-almacen';
				elseif(strpos($st, "LIST")   !== false) $claseColor = 'estado-listoe';
				elseif(strpos($st, "ENTREG") !== false) $claseColor = 'estado-entregado';
				elseif(strpos($st, "SEGUIM") !== false) $claseColor = 'estado-seguimiento';
				elseif(strpos($st, "CANCEL") !== false) $claseColor = 'estado-cancelado';

				// Antigüedad (Igual que antes)
				$textoTiempo = ""; $claseTiempo = "";
				if ($rows['Fechaentrega'] != "" && $rows['Fechaentrega'] != "0000-00-00" && $rows['Fechaentrega'] != NULL) {
					$textoTiempo = '<i class="fas fa-check"></i> Entregado'; $claseTiempo = "tiempo-finalizado";
				} else {
					$fechaIngreso = new \DateTime($rows['Fecha']);
					$fechaHoy     = new \DateTime();
					$diferencia   = $fechaIngreso->diff($fechaHoy);
					$dias = $diferencia->days;
					if ($dias <= 7) { $textoTiempo = $dias . " días"; $claseTiempo = "tiempo-verde"; } 
					elseif ($dias <= 30) { $textoTiempo = floor($dias / 7) . " sem"; $claseTiempo = "tiempo-amarillo"; } 
					else { $textoTiempo = floor($dias / 30) . " mes"; $claseTiempo = "tiempo-rojo"; }
				}

				$tabla .= '
					<tr class="has-text-centered" >
						<td>
							' . $rows['Idods'] . '
							<a href="' . APP_URL . 'odsView/' . $rows['Idods'] . '/" target="_blank" class="button is-small is-link" title="Ver ODS">
								<i class="fas fa-eye"></i>
							</a>
						</td>
						<td>' . $rows['cliente_nombre'] . '</td>
						<td>' . $rows['asesor_nombre'] . '</td>
						<td>' . $rows['tecnico_nombre'] . '</td>
						<td>
							<div class="select is-rounded">
								<select name="Status" class="status-dropdown '.$claseColor.'" 
										onchange="actualizarStatusDirecto('.$rows['Idods'].', this.value)">
									<option value="Recepcion"    '.($st == "RECEPCION"    ? 'selected' : '').'>Recepción</option>
									<option value="Diagnostico"  '.($st == "DIAGNOSTICO"  ? 'selected' : '').'>Diagnóstico</option>
									<option value="Presupuesto"  '.($st == "PRESUPUESTO"  ? 'selected' : '').'>Presupuesto</option>
									<option value="Autorizacion" '.($st == "AUTORIZACION" ? 'selected' : '').'>Autorización</option>
									<option value="Reparacion"   '.($st == "REPARACION"   ? 'selected' : '').'>Reparación</option>
									<option value="Refacciones"  '.($st == "REFACCIONES"  ? 'selected' : '').'>Refacciones</option>
									<option value="StandBy"      '.($st == "STANDBY"      ? 'selected' : '').'>StandBy</option>
									<option value="Almacen"      '.($st == "ALMACEN"      ? 'selected' : '').'>Almacén</option>
									<option value="Listoe"       '.($st == "LISTOE" || $st == "LENTREGAR" ? 'selected' : '').'>Listo para Entregar</option>
									<option value="Entregado"    '.($st == "ENTREGADO"    ? 'selected' : '').'>Entregado</option>
									<option value="Seguimiento"  '.($st == "SEGUIMIENTO"  ? 'selected' : '').'>Seguimiento</option>
									<option value="Cancelado"    '.($st == "CANCELADO"    ? 'selected' : '').'>Cancelado</option>
								</select>
							</div>
						</td>
						<td><div class="badge-tiempo '.$claseTiempo.'"><i class="far fa-clock"></i> '.$textoTiempo.'</div></td>
						<td>' . $rows['Tipo'] . '</td>
						<td>' . $rows['Marca'] . '</td>
						<td>' . $rows['Fecha'] . '</td>
					</tr>
				';
				$contador++;
			}
			$pag_final = $contador - 1;
		} else {
			if ($total >= 1) {
				$tabla .= '<tr class="has-text-centered"><td colspan="14"><a href="' . $url . '1/" class="button is-link is-rounded is-small mt-4 mb-4">Recargar listado</a></td></tr>';
			} else {
				$tabla .= '<tr class="has-text-centered"><td colspan="14"><div class="has-text-grey p-4"><i class="fas fa-folder-open fa-2x mb-2"></i><br>No se encontraron órdenes con este filtro.</div></td></tr>';
			}
		}
		$tabla .= '</tbody></table></div>';
		
		if ($total > 0 && $pagina <= $numeroPaginas) {
			$tabla .= '<p class="has-text-right">Mostrando ODS <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
			$tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);
		}
		return $tabla;
	}

	/*----------  Controlador listar ODS Personal (HISTORIAL COMPLETO PARA MIS ODS)  ----------*/
		public function listarOdsGeneralMeControlador($pagina, $registros, $url, $busqueda, $filtroEstado = "") {
		
		$pagina = $this->limpiarCadena($pagina);
		$registros = $this->limpiarCadena($registros);
		$url = $this->limpiarCadena($url);
		$url = APP_URL . $url . "/";
		$busqueda = $this->limpiarCadena($busqueda);
		$filtroEstado = $this->limpiarCadena($filtroEstado);
		$tabla = "";

		$pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
		$inicio = (isset($pagina) && $pagina > 0) ? (($pagina * $registros) - $registros) : 0;
		
		// --- DATOS DE SESIÓN ---
		$idUser = $_SESSION['id'] ?? 0;
		$puestoUser = $_SESSION['Puesto'] ?? ''; 

		// --- DEFINIR OPCIONES DE ESTATUS SEGÚN ROL ---
		// Clave = Valor en BD, Valor = Texto a mostrar
		$opcionesTecnico = [
			'Recepcion'    => 'Recepción',
			'Diagnostico'  => 'Diagnóstico',
			'Presupuesto'  => 'Presupuesto',
			'Autorizacion' => 'Autorización',
			'Reparacion'   => 'Reparación',
			'Refacciones'  => 'Refacciones',
			'StandBy'      => 'StandBy',
			'Almacen'      => 'Almacén',
			'Listoe'       => 'Listo para Entregar',
			'Entregado'    => 'Entregado'
		];

		$opcionesAsesor = [
			'Recepcion'    => 'Recepción',
			'Diagnostico'  => 'Diagnóstico',
			'Presupuesto'  => 'Presupuesto',
			'Autorizacion' => 'Autorización',
			'Reparacion'   => 'Reparación',
			'Refacciones'  => 'Refacciones',
			'StandBy'      => 'StandBy',
			'Almacen'      => 'Almacén',
			'Listoe'       => 'Listo para Entregar',
			'Entregado'    => 'Entregado',
			'Seguimiento'  => 'Seguimiento',
			'Cancelado'    => 'Cancelado'
		];

		// Seleccionamos la lista correcta
		$misOpciones = ($puestoUser == 'TECNICO') ? $opcionesTecnico : $opcionesAsesor;

		// --- FILTROS INTELIGENTES ---
		$condicionUsuario = " AND (o.IdTecnico = '$idUser' OR o.Idasesor = '$idUser') ";

		$condicionStatus = "";
		if($filtroEstado != "" && $filtroEstado != "Todos"){
			$condicionStatus = " AND o.Status = '$filtroEstado' ";
		}

		// --- CONSULTAS SQL ---
		if (isset($busqueda) && $busqueda != "") {
			$consulta_datos = "SELECT o.*, c.Nombre AS cliente_nombre, p.Nombre AS asesor_nombre, p2.Nombre AS tecnico_nombre
							FROM ods o
							LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
							LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
							LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
							WHERE (o.Idods LIKE '%$busqueda%' OR c.Nombre LIKE '%$busqueda%') 
							$condicionUsuario $condicionStatus
							ORDER BY o.Idods DESC LIMIT $inicio,$registros";

			$consulta_total = "SELECT COUNT(o.Idods) FROM ods o 
							LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
							WHERE (o.Idods LIKE '%$busqueda%' OR c.Nombre LIKE '%$busqueda%') 
							$condicionUsuario $condicionStatus";
		} else {
			$consulta_datos = "SELECT o.*, c.Nombre AS cliente_nombre, p.Nombre AS asesor_nombre, p2.Nombre AS tecnico_nombre
							FROM ods o
							LEFT JOIN clientes c ON o.Idcliente = c.Idcliente
							LEFT JOIN personal p ON o.Idasesor  = p.Idasesor
							LEFT JOIN personal p2 ON o.IdTecnico  = p2.Idasesor
							WHERE 1=1 $condicionUsuario $condicionStatus
							ORDER BY o.Idods DESC LIMIT $inicio,$registros";

			$consulta_total = "SELECT COUNT(o.Idods) FROM ods o WHERE 1=1 $condicionUsuario $condicionStatus";
		}

		$datos = $this->ejecutarConsulta($consulta_datos);
		$datos = $datos->fetchAll();
		$total = $this->ejecutarConsulta($consulta_total);
		$total = (int) $total->fetchColumn();
		$numeroPaginas = ceil($total / $registros);
		
		// --- ESTILOS CSS ---
		$tabla.='
		<style>
			.btn-view-ods { background-color: #3b5998; color: white; border: none; width: 100%; border-radius: 4px; display: block; text-align: center; margin-top: 5px; padding: 2px 0; text-decoration: none; transition: background 0.2s; }
			.btn-view-ods:hover { background-color: #2d4373; color: white; }

			.estado-recepcion   { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important; color: white !important; }
			.estado-diagnostico { background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%) !important; color: white !important; }
			.estado-presupuesto { background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%) !important; color: white !important; }
			.estado-autorizacion{ background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important; color: white !important; }
			.estado-standby     { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%) !important; color: white !important; }
			.estado-reparacion  { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important; color: white !important; }
			.estado-refacciones { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important; color: white !important; }
			.estado-listoe      { background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; color: white !important; }
			.estado-almacen     { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%) !important; color: white !important; }
			.estado-entregado   { background: linear-gradient(135deg, #facc15 0%, #eab308 100%) !important; color: #444 !important; }
			.estado-seguimiento { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; color: white !important; }
			.estado-cancelado   { background: linear-gradient(135deg, #6b7280 0%, #374151 100%) !important; color: white !important; }
			
			select.status-dropdown { width: 135px !important; height: 30px !important; font-weight: 600; font-size: 0.85rem !important; padding-left: 10px; border: none; box-shadow: 0 2px 5px rgba(0,0,0,0.2); transition: all 0.3s ease; }
			select.status-dropdown option { background-color: white; color: #333; font-weight: normal; }
			.select.is-rounded { height: 30px !important; width: auto !important; }

			.badge-tiempo { font-size: 0.8rem; font-weight: bold; padding: 3px 8px; border-radius: 12px; white-space: nowrap; display: inline-block; width: 100%; }
			.tiempo-verde { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
			.tiempo-amarillo { background-color: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
			.tiempo-rojo { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
			.tiempo-finalizado { background-color: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
		</style>

		<div class="table-container">
			<table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth" style="font-family: \'Segoe UI\', system-ui, sans-serif; font-size: 0.95rem;">
				<thead>
					<tr style="background-color: #f9fafb; color: #6b7280; text-transform: uppercase; font-size: 0.85rem;">';
		
		// CABECERA
		if($puestoUser == 'TECNICO') {
			$tabla .= '
						<th class="has-text-centered">ODS</th>
						<th class="has-text-centered">Cliente</th>
						<th class="has-text-centered">Asesor</th>
						<th class="has-text-centered">Status</th>
						<th class="has-text-centered">Antigüedad</th>
						<th class="has-text-centered">Tipo</th>
						<th class="has-text-centered">Marca</th>
						<th class="has-text-centered">Fecha Ingreso</th>';
		} else {
			$tabla .= '
						<th class="has-text-centered">ODS</th>
						<th class="has-text-centered">Cliente</th>
						<th class="has-text-centered">Tecnico</th>
						<th class="has-text-centered">Status</th>
						<th class="has-text-centered">Antigüedad</th>
						<th class="has-text-centered">Total</th>
						<th class="has-text-centered">Resto</th>
						<th class="has-text-centered">Fecha Entrega</th>';
		}

		$tabla .= ' </tr>
				</thead>
				<tbody>';

		if ($total >= 1 && $pagina <= $numeroPaginas) {
			$contador = $inicio + 1;
			$pag_inicio = $inicio + 1;
			
			foreach ($datos as $rows) {
				$st = mb_strtoupper($rows['Status'], 'UTF-8');
				$claseColor = '';
				if(strpos($st, "RECEPC") !== false) $claseColor = 'estado-recepcion';
				elseif(strpos($st, "DIAGN") !== false) $claseColor = 'estado-diagnostico';
				elseif(strpos($st, "PRESUP") !== false) $claseColor = 'estado-presupuesto';
				elseif(strpos($st, "AUTORIZ")!== false)$claseColor = 'estado-autorizacion';
				elseif(strpos($st, "REPARA") !== false)  $claseColor = 'estado-reparacion';
				elseif(strpos($st, "REFACC") !== false) $claseColor = 'estado-refacciones';
				elseif(strpos($st, "STAND")  !== false) $claseColor = 'estado-standby';
				elseif(strpos($st, "ALMAC")  !== false) $claseColor = 'estado-almacen';
				elseif(strpos($st, "LIST")   !== false) $claseColor = 'estado-listoe';
				elseif(strpos($st, "ENTREG") !== false) $claseColor = 'estado-entregado';
				elseif(strpos($st, "SEGUIM") !== false) $claseColor = 'estado-seguimiento';
				elseif(strpos($st, "CANCEL") !== false) $claseColor = 'estado-cancelado';

				// Antigüedad
				$textoTiempo = ""; $claseTiempo = "";
				if ($rows['Fechaentrega'] != "" && $rows['Fechaentrega'] != "0000-00-00" && $rows['Fechaentrega'] != NULL) {
					$textoTiempo = '<i class="fas fa-check"></i> Entregado'; $claseTiempo = "tiempo-finalizado";
				} else {
					$fechaIngreso = new \DateTime($rows['Fecha']);
					$fechaHoy     = new \DateTime();
					$diferencia   = $fechaIngreso->diff($fechaHoy);
					$dias = $diferencia->days;
					if ($dias <= 7) { $textoTiempo = $dias . " días"; $claseTiempo = "tiempo-verde"; } 
					elseif ($dias <= 30) { $textoTiempo = floor($dias / 7) . " sem"; $claseTiempo = "tiempo-amarillo"; } 
					else { $textoTiempo = floor($dias / 30) . " mes"; $claseTiempo = "tiempo-rojo"; }
				}

				// Datos comunes
				$celdaODS = '<td>
								<div style="font-weight: bold; font-size: 1.1em; color: #1d4d80; margin-bottom: 5px;">' . $rows['Idods'] . '</div>
								<a href="' . APP_URL . 'odsView/' . $rows['Idods'] . '/" target="_blank" class="button is-small btn-view-ods" title="Ver ODS">
									<i class="fas fa-eye"></i>
								</a>
							</td>';
				
				// --- CONSTRUCCIÓN DEL SELECT DINÁMICO ---
				$selectHTML = '<div class="select is-rounded">
									<select name="Status" class="status-dropdown '.$claseColor.'" 
											onchange="actualizarStatusDirecto('.$rows['Idods'].', this.value)">';
				
				// 1. Verificamos si el estado actual está en la lista permitida.
				// Si NO está (ej: Tecnico ve una en RECEPCION), lo agregamos visualmente para que no se rompa.
				$statusActualEncontrado = false;
				foreach ($misOpciones as $val => $texto) {
					// Compara el valor actual mayúscula (BD) con el valor de la lista (convertido a mayúscula para asegurar)
					if (strtoupper($val) == $st || (strtoupper($val) == 'LISTOE' && $st == 'LENTREGAR')) {
						$statusActualEncontrado = true;
					}
				}

				// Si no estaba en la lista, lo imprimimos primero como "Actual"
				if (!$statusActualEncontrado) {
					$selectHTML .= '<option value="'.$rows['Status'].'" selected>'.$rows['Status'].'</option>';
				}

				// 2. Imprimimos las opciones permitidas
				foreach ($misOpciones as $val => $texto) {
					// Lógica de selección
					$selected = (strtoupper($val) == $st || (strtoupper($val) == 'LISTOE' && $st == 'LENTREGAR')) ? 'selected' : '';
					$selectHTML .= '<option value="'.$val.'" '.$selected.'>'.$texto.'</option>';
				}

				$selectHTML .= '    </select>
								</div>';
				
				$celdaStatus = '<td>' . $selectHTML . '</td>';
				$celdaTiempo = '<td><div class="badge-tiempo '.$claseTiempo.'"><i class="far fa-clock"></i> '.$textoTiempo.'</div></td>';

				// --- FILAS DINÁMICAS ---
				$tabla .= '<tr class="has-text-centered">';
				
				if($puestoUser == 'TECNICO') {
					$tabla .= $celdaODS;
					$tabla .= '<td>' . $rows['cliente_nombre'] . '</td>';
					$tabla .= '<td>' . $rows['asesor_nombre'] . '</td>';
					$tabla .= $celdaStatus;
					$tabla .= $celdaTiempo;
					$tabla .= '<td>' . $rows['Tipo'] . '</td>';
					$tabla .= '<td>' . $rows['Marca'] . '</td>';
					$tabla .= '<td>' . $rows['Fecha'] . '</td>';
				} else {
					$resto = $rows['Total'] - $rows['Cuenta'];
					$tabla .= $celdaODS;
					$tabla .= '<td>' . $rows['cliente_nombre'] . '</td>';
					$tabla .= '<td>' . $rows['tecnico_nombre'] . '</td>';
					$tabla .= $celdaStatus;
					$tabla .= $celdaTiempo;
					$tabla .= '<td style="font-weight:bold; color:#1d4d80;">$' . number_format($rows['Total'], 2) . '</td>';
					$tabla .= '<td style="color:#ef4444;">$' . number_format($resto, 2) . '</td>';
					$tabla .= '<td>' . ($rows['Fechaentrega'] == '0000-00-00' ? '-' : $rows['Fechaentrega']) . '</td>';
				}

				$tabla .= '</tr>';
				$contador++;
			}
			$pag_final = $contador - 1;
		} else {
			$tabla .= '<tr class="has-text-centered"><td colspan="8"><div class="has-text-grey p-4">No se encontraron registros.</div></td></tr>';
		}
		$tabla .= '</tbody></table></div>';
		
		if ($total > 0 && $pagina <= $numeroPaginas) {
			$tabla .= '<p class="has-text-right">Mostrando ODS <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
			$tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);
		}
		return $tabla;
	}

	/* 1. ESTADÍSTICAS (Funciona para Asesor y Técnico) */
    public function obtenerEstadisticasPersonal($idUsuario) {
        $idUsuario = $this->limpiarCadena($idUsuario);
        
        // Busca ODS donde el usuario sea el Técnico O el Asesor
        $consulta = "SELECT Status, COUNT(Idods) as Cantidad 
                     FROM ods 
                     WHERE (IdTecnico = '$idUsuario' OR Idasesor = '$idUsuario') 
                     GROUP BY Status";
                    
        $datos = $this->ejecutarConsulta($consulta);
        return $datos->fetchAll();
    }

	public function actualizar_tecnico_controlador() {
		// 1. Limpiar datos
		$id = $this->limpiarCadena($_POST['id_ods']);
		$tecnico = $this->limpiarCadena($_POST['id_tecnico']);

		if($id == "") {
			return json_encode(["success" => false, "msg" => "Error: ID no encontrado"]);
		}

		try {
			// SQL DIRECTO
			$pdo = mainModel::conectar();
			$sql = $pdo->prepare("UPDATE ods SET IdTecnico=:Tecnico WHERE Idods=:ID");
			
			$sql->bindParam(":Tecnico", $tecnico);
			$sql->bindParam(":ID", $id);
			
			if($sql->execute()){
				$alerta = ["success" => true, "msg" => "Técnico asignado correctamente"];
			} else {
				$alerta = ["success" => false, "msg" => "No se pudo actualizar la BD"];
			}
			$sql = null; $pdo = null;

		} catch (\Exception $e) { // <--- IMPORTANTE: La barra invertida '\' aquí
			$alerta = ["success" => false, "msg" => "Error: " . $e->getMessage()];
		}

		return json_encode($alerta);
	}
	}
?>

<script>
function actualizarStatusDirecto(idOds, nuevoStatus) {
    // 1. Preparamos los datos para enviar a odsAjax.php
    let data = new FormData();
    data.append('modulo_ods', 'cambiar_status'); // Este módulo llama a cambiarStatusOdsControlador
    data.append('Idods', idOds);
    data.append('Status', nuevoStatus);

    // 2. Enviamos la petición sin recargar la página
    fetch('<?php echo APP_URL; ?>app/ajax/odsAjax.php', {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(respuesta => {
        if(respuesta.success) {
            // ÉXITO: Mostramos alerta bonita o toast
            alert("✅ Estado actualizado a: " + nuevoStatus);
            // Opcional: Recargar para ver cambios de color si no usas lógica dinámica
            // location.reload(); 
        } else {
            // ERROR: Avisamos al usuario
            alert("❌ Error: " + respuesta.msg);
            // Recargamos para devolver el select a su estado real
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Error de conexión al guardar.");
    });
}
</script>
	
