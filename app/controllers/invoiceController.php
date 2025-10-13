<?php
	namespace app\controllers;
	use app\models\mainModel;
	class invoiceController extends mainModel{
		/*----------  Controlador registrar factura  ----------*/
		public function registrarFacturaControlador(){
    // Datos (Idfactura fuera: AUTO_INCREMENT)
    $Idods          = $this->limpiarCadena($_POST['Idods'] ?? '');
    $Datosfac       = $this->limpiarCadena($_POST['Datosfac'] ?? '');  // opcional
    $Estadofac      = $this->limpiarCadena($_POST['Estadofac'] ?? '');
	$Nombre         = strtoupper($this->limpiarCadena($_POST['Nombre'] ?? ''));
    $rfc            = strtoupper($this->limpiarCadena($_POST['rfc'] ?? ''));
    $regimenFiscal  = $this->limpiarCadena($_POST['regimenFiscal'] ?? '');
    $codigoPostal   = $this->limpiarCadena($_POST['codigoPostal'] ?? '');
    $correo         = $this->limpiarCadena($_POST['correo'] ?? '');
    $tipoPago       = $this->limpiarCadena($_POST['tipoPago'] ?? '');
    $CFDI           = strtoupper($this->limpiarCadena($_POST['CFDI'] ?? ''));

    // Obligatorios (Datosfac opcional)
    if ($Idods === "" || $Nombre === "") {
        return json_encode([
            "tipo"=>"simple",
            "titulo"=>"Faltan datos",
            "texto"=>"Completa todos los campos obligatorios de la factura.",
            "icono"=>"error"
        ]);
    }

    // Validaciones
    if($this->verificarDatos("^[0-9]{1,30}$",$Idods)){
        return json_encode(["tipo"=>"simple","titulo"=>"Dato inválido","texto"=>"El ID de la ODS no es válido.","icono"=>"error"]);
    }
    if($this->verificarDatos("^.{2,150}$",$Nombre)){
        return json_encode(["tipo"=>"simple","titulo"=>"Dato inválido","texto"=>"El nombre del receptor no es válido.","icono"=>"error"]);
    }

    // Verificar que la ODS exista
    $pdo = \app\models\mainModel::conectar();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ods WHERE Idods = :id");
    $stmt->bindValue(':id', (int)$Idods, \PDO::PARAM_INT);
    $stmt->execute();
    if((int)$stmt->fetchColumn() === 0){
        return json_encode([
            "tipo"=>"simple",
            "titulo"=>"ODS no encontrada",
            "texto"=>"El Idods indicado no existe.",
            "icono"=>"error"
        ]);
    }

    // Insert (SIN Idfactura)
    $factura_datos_reg=[
        ["campo_nombre"=>"Idods",         "campo_marcador"=>":Idods",         "campo_valor"=>$Idods],
        ["campo_nombre"=>"Datosfac",      "campo_marcador"=>":Datosfac",      "campo_valor"=>$Datosfac],
        ["campo_nombre"=>"Estadofac",     "campo_marcador"=>":Estadofac",     "campo_valor"=>$Estadofac],

        ["campo_nombre"=>"Nombre",        "campo_marcador"=>":Nombre",        "campo_valor"=>$Nombre],
        ["campo_nombre"=>"rfc",           "campo_marcador"=>":rfc",           "campo_valor"=>$rfc],
        ["campo_nombre"=>"regimenFiscal", "campo_marcador"=>":regimenFiscal", "campo_valor"=>$regimenFiscal],
        ["campo_nombre"=>"codigoPostal",  "campo_marcador"=>":codigoPostal",  "campo_valor"=>$codigoPostal],
        ["campo_nombre"=>"correo",        "campo_marcador"=>":correo",        "campo_valor"=>$correo],
        ["campo_nombre"=>"tipoPago",      "campo_marcador"=>":tipoPago",      "campo_valor"=>$tipoPago],
        ["campo_nombre"=>"CFDI",          "campo_marcador"=>":CFDI",          "campo_valor"=>$CFDI],
    ];

    $registrar = $this->guardarDatos("facturas", $factura_datos_reg);

    if($registrar->rowCount()==1){
        return json_encode([
            "tipo"=>"limpiar",
            "titulo"=>"Factura registrada",
            "texto"=>"La factura se registró con éxito.",
            "icono"=>"success"
        ]);
    }else{
        return json_encode([
            "tipo"=>"simple",
            "titulo"=>"Ocurrió un error",
            "texto"=>"No se pudo registrar la factura, intenta nuevamente.",
            "icono"=>"error"
        ]);
    }
}

		public function formNuevaFacturaControlador() {

    // 0) Helper
    $h = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

    // 1) Traer posibles prefill desde POST (del form en odsView)
    $idods_pref  = isset($_POST['prefill_idods'])  ? (int)$_POST['prefill_idods']  : 0;
    $nombre_pref = $_POST['prefill_nombre'] ?? '';
    $correo_pref = $_POST['prefill_correo'] ?? '';

    // 2) Si no llegó por POST, intentar por GET
    if ($idods_pref <= 0 && isset($_GET['idods']) && $_GET['idods']!=='') {
        $idods_pref = (int)$_GET['idods'];
    }

    // 3) Si no hay por POST/GET, intentar SESSION
    if ($idods_pref <= 0 && !empty($_SESSION['factura_idods'])) {
        $idods_pref = (int)$_SESSION['factura_idods'];
        if ($nombre_pref==='') $nombre_pref = $_SESSION['factura_nombre'] ?? '';
        if ($correo_pref==='') $correo_pref = $_SESSION['factura_correo'] ?? '';
    }

    // 4) Si tenemos idods pero faltan nombre/correo, consulta BD (ods -> clientes)
    if ($idods_pref > 0 && ($nombre_pref==='' || $correo_pref==='')) {
        try {
            $pdo = \app\models\mainModel::conectar();
            $q = $pdo->prepare("
                SELECT 
                    o.Idods,
                    c.Nombre AS cliente_nombre,
                    COALESCE(NULLIF(c.Email,''), c.Email) AS cliente_correo
                FROM ods o
                LEFT JOIN clientes c ON c.Idcliente = o.Idcliente
                WHERE o.Idods = :id
                LIMIT 1
            ");
            $q->execute([':id'=>$idods_pref]);
            if ($row = $q->fetch(\PDO::FETCH_ASSOC)) {
                if ($nombre_pref==='') $nombre_pref = $row['cliente_nombre'] ?? '';
                if ($correo_pref==='') $correo_pref = $row['cliente_correo'] ?? '';
            }
        } catch (\Throwable $e) {
            // silencioso
        }
    }

    // 5) Render del form (los valores quedan como SUGERENCIA editable)
    $html = '
    <form class="FormularioAjax box" action="'.APP_URL.'app/ajax/invoiceAjax.php" method="POST" autocomplete="off">
        <input type="hidden" name="modulo_factura" value="registrar">

        <div class="box" style="background:#f7e8bc;">
            <div class="columns">
                <div class="column is-3">
                    <label class="label">ID ODS</label>
                    <input class="input" type="number" name="Idods" min="1" required value="'.$h($idods_pref).'">
                </div>
                <div class="column is-9">
                    <label class="label">Nombre</label>
                     <input class="input" type="text" name="Nombre" maxlength="150" required
         oninput="this.value=this.value.toUpperCase()"
         value="'.$h($nombre_pref).'">
                </div>
            </div>

            <div class="columns">
                <div class="column is-4">
                    <label class="label">RFC</label>
                    <input class="input" type="text" name="rfc" maxlength="13" oninput="this.value=this.value.toUpperCase()">
                </div>
                <div class="column is-4">
                    <label class="label">Régimen Fiscal</label>
                    <input class="input" type="text" name="regimenFiscal" maxlength="100" placeholder="Ej: Régimen General de Ley Personas Morales">
                </div>
                <div class="column is-4">
                    <label class="label">Código Postal</label>
                    <input class="input" type="text" name="codigoPostal" maxlength="5" pattern="[0-9]{5}">
                </div>
            </div>

            <div class="columns">
                <div class="column is-5">
                    <label class="label">Correo</label>
                    <input class="input" type="email" name="correo" maxlength="150" required value="'.$h($correo_pref).'">
                </div>
                <div class="column is-3">
					<label class="label">Tipo de Pago</label>
					<div class="select is-fullwidth">
						<select name="tipoPago" required>
							<option value="01" selected>Efectivo</option>
							<option value="03">Transferencia electrónica</option>
							<option value="04">Tarjeta</option>
						</select>
					</div>
				</div>
                <div class="column is-4">
                    <label class="label">Uso CFDI</label>
                    <input class="input" type="text" name="CFDI" maxlength="5" oninput="this.value=this.value.toUpperCase()" placeholder="Ej: G03-Gastos en general">
                </div>
            </div>

            <div class="columns">
                <div class="column is-8">
                    <label class="label">Otros datos</label>
                    <input class="input" type="text" name="Datosfac" maxlength="255" placeholder="Nota libre">
                </div>
                <div class="column is-4">
                    <label class="label">Estado de la factura</label>
                    <div class="select is-fullwidth">
                        <select name="Estadofac">
                            <option value="1" selected>Pendiente</option>
                            <option value="2">Realizada</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="has-text-right">
            <button type="submit" class="button is-link is-rounded">
                <i class="fas fa-save"></i>&nbsp; Guardar factura
            </button>
            <a href="'.APP_URL.'invoiceList/" class="button is-light is-rounded">Cancelar</a>
        </div>
    </form>';

    return $html;
}
		/*----------  Controlador listar factura  ----------*/
		/*----------  Controlador listar factura  ----------*/
		/*----------  Controlador listar factura  ----------*/
public function listarFacturaControlador($pagina,$registros,$url,$busqueda){
	// DEBUG PROVISORIO (borra luego)
file_put_contents(__DIR__.'/debug_get.txt', date('c').' $_GET='.var_export($_GET,true).PHP_EOL, FILE_APPEND);

    // Sanitizar básicos
    $pagina    = $this->limpiarCadena($pagina);
    $registros = $this->limpiarCadena($registros);
    $url_slug  = $this->limpiarCadena($url);

    // URL base para enlaces
    $base_url  = APP_URL.$url_slug."/";

    /* ===========================
       Filtro ESTADO robusto
       Lee GET -> fallback a SESSION
       Acepta solo '1' o '2'
    ============================*/
    if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

    // 1) prioriza GET
    $estado = isset($_GET['estado']) ? trim((string)$_GET['estado']) : '';

    // 2) si no viene en GET, usa lo último de sesión
    if ($estado === '' && !empty($_SESSION['facturas_estado'])) {
        $estado = (string)$_SESSION['facturas_estado'];
    }

    // 3) valida (solo 1 o 2). Cualquier otra cosa => todos
    if ($estado !== '1' && $estado !== '2') {
        $estado = '';
    }

    // 4) persiste selección
    $_SESSION['facturas_estado'] = $estado;

    // URL de paginación conservando ?estado
    $url_paginacion = $base_url . ( $estado !== '' ? ('?estado='.$estado) : '' );

    // Búsqueda libre
    $busqueda  = trim($this->limpiarCadena($busqueda));

    // Paginación
    $pagina = (isset($pagina) && $pagina>0) ? (int)$pagina : 1;
    $inicio = ($pagina>0) ? (($pagina * (int)$registros) - (int)$registros) : 0;

    // Conexión
    $pdo = \app\models\mainModel::conectar();

    // Campos a seleccionar
    $select_campos = "
        Idfactura, Idods, Datosfac, Estadofac,
        Nombre, rfc, regimenFiscal, codigoPostal, correo, tipoPago, CFDI
    ";

    // --------- WHERE + parámetros ----------
    $where  = " WHERE Idfactura!='0' ";
    $params = [];

    // Filtro de búsqueda (TODO en el mismo paréntesis)
    if ($busqueda !== "") {
        $like = "%{$busqueda}%";
        $busqConds = [
            "CAST(Idods AS CHAR) LIKE :like1",
            "CAST(Idfactura AS CHAR) LIKE :like2",
            "Nombre LIKE :like3",
            "rfc LIKE :like4",
            "regimenFiscal LIKE :like5",
            "codigoPostal LIKE :like6",
            "correo LIKE :like7",
            "CFDI LIKE :like8",
            "tipoPago LIKE :like9"
        ];
        $params[':like1'] = $like;
        $params[':like2'] = $like;
        $params[':like3'] = $like;
        $params[':like4'] = $like;
        $params[':like5'] = $like;
        $params[':like6'] = $like;
        $params[':like7'] = $like;
        $params[':like8'] = $like;
        $params[':like9'] = $like;

        // Palabras -> código de forma de pago (dentro del mismo grupo)
        $busq_lc = mb_strtolower($busqueda, 'UTF-8');
        if (mb_strpos($busq_lc, 'efect') !== false) {
            $params[':tpCode'] = '01'; $busqConds[] = "tipoPago = :tpCode";
        } elseif (mb_strpos($busq_lc, 'transfe') !== false) {
            $params[':tpCode'] = '03'; $busqConds[] = "tipoPago = :tpCode";
        } elseif (mb_strpos($busq_lc, 'tarj') !== false) {
            $params[':tpCode'] = '04'; $busqConds[] = "tipoPago = :tpCode";
        }

        $where .= " AND ( " . implode(" OR ", $busqConds) . " ) ";
    }

    // Filtro por estado (1=Pendiente, 2=Realizada)
    if ($estado !== '') {
        // OJO: si tu columna es VARCHAR con '1'/'2' también funciona,
        // si es INT, este bind como INT asegura el match exacto.
        $where .= " AND Estadofac = :estado ";
        $params[':estado'] = (int)$estado;
    }

    // --------- Consultas ----------
    $sql_datos = "
        SELECT $select_campos
        FROM facturas
        $where
        ORDER BY Idfactura DESC
        LIMIT :inicio, :registros
    ";
    $sql_total = "
        SELECT COUNT(Idfactura) AS total
        FROM facturas
        $where
    ";

    // Ejecutar total
    $stmtTotal = $pdo->prepare($sql_total);
    foreach ($params as $k=>$v) {
        if ($k === ':estado') { $stmtTotal->bindValue($k, (int)$v, \PDO::PARAM_INT); }
        else { $stmtTotal->bindValue($k, $v, \PDO::PARAM_STR); }
    }
    $stmtTotal->execute();
    $total = (int)$stmtTotal->fetchColumn();

    // Ejecutar datos
    $stmtDatos = $pdo->prepare($sql_datos);
    foreach ($params as $k=>$v) {
        if ($k === ':estado') { $stmtDatos->bindValue($k, (int)$v, \PDO::PARAM_INT); }
        else { $stmtDatos->bindValue($k, $v, \PDO::PARAM_STR); }
    }
    $stmtDatos->bindValue(':inicio', (int)$inicio, \PDO::PARAM_INT);
    $stmtDatos->bindValue(':registros', (int)$registros, \PDO::PARAM_INT);
    $stmtDatos->execute();
    $datos = $stmtDatos->fetchAll(\PDO::FETCH_ASSOC);

    $numeroPaginas = ($registros>0) ? (int)ceil($total/$registros) : 1;

    // Helpers
    $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

    $mapEstado = [
        '1' => ['label'=>'Pendiente', 'badge'=>'tag is-warning'],
        '2' => ['label'=>'Realizada', 'badge'=>'tag is-success']
    ];
    $mapPago = [
        '01' => 'Efectivo',
        '03' => 'Transferencia electrónica',
        '04' => 'Tarjeta'
    ];

    // UI del select (refleja lo que realmente se está aplicando)
    $selTodos     = ($estado==='')  ? 'selected' : '';
    $selPendiente = ($estado==='1') ? 'selected' : '';
    $selRealizada = ($estado==='2') ? 'selected' : '';

    $tabla = '
    <!-- debug suave: estado='.@$estado.' -->
    <div class="level mb-2">
      <div class="level-left">
        <form method="GET" action="'.$base_url.'" class="field has-addons" data-estado-aplicado="'.$e($estado).'">
          <div class="control">
            <div class="select">
              <select name="estado" onchange="this.form.submit()">
                <option value="" '.$selTodos.'>Todos</option>
                <option value="1" '.$selPendiente.'>Pendiente</option>
                <option value="2" '.$selRealizada.'>Realizada</option>
              </select>
            </div>
          </div>
          <div class="control">
            <a class="button is-light" href="'.$base_url.'">Limpiar</a>
          </div>
        </form>
      </div>
    </div>

    <div class="table-container">
    <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
        <thead>
            <tr>
                <th class="has-text-centered">ID ODS</th>
                <th class="has-text-centered">Datos Factura</th>
                <th class="has-text-centered">Estado</th>
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

            $tipoPagoCode = (string)($rows['tipoPago'] ?? '');
            $tipoPagoTxt  = $mapPago[$tipoPagoCode] ?? $tipoPagoCode;

            $estadoKey = (string)($rows['Estadofac'] ?? '');
            $estadoUI  = $mapEstado[$estadoKey] ?? ['label'=>'Desconocido','badge'=>'tag is-light'];

            $datosFactura = '
                <div class="has-text-left" style="line-height:1.35;">
                    <strong>Nombre:</strong> '.$e($rows["Nombre"] ?? "").'<br>
                    <strong>RFC:</strong> '.$e($rows["rfc"] ?? "").'<br>
                    <strong>Régimen Fiscal:</strong> '.$e($rows["regimenFiscal"] ?? "").'<br>
                    <strong>Código Postal:</strong> '.$e($rows["codigoPostal"] ?? "").'<br>
                    <strong>Correo:</strong> '.$e($rows["correo"] ?? "").'<br>
                    <strong>Tipo de Pago:</strong> '.$e($tipoPagoTxt).' ('.$e($tipoPagoCode).')<br>
                    <strong>Uso CFDI:</strong> '.$e($rows["CFDI"] ?? "").'<br>' .
                    (!empty($rows["Datosfac"]) ? '<strong>Otros datos:</strong> '.$e($rows["Datosfac"]) : '') .
                '</div>';

            $tabla.='
                <tr class="has-text-centered">
                    <td>'.$e($rows['Idods']).'</td>
                    <td>'.$datosFactura.'</td>
                    <td><span class="'.$estadoUI['badge'].'">'.$estadoUI['label'].'</span></td>
                    <td>
                        <a href="'.APP_URL.'invoiceUpdate/'.$e($rows['Idfactura']).'/" class="button is-success is-rounded is-small" title="Actualizar">
                            <i class="fas fa-sync fa-fw"></i>
                        </a>
                    </td>
                    <td>
                        <form class="FormularioAjax" action="'.APP_URL.'app/ajax/invoiceAjax.php" method="POST" autocomplete="off">
                            <input type="hidden" name="modulo_factura" value="eliminar">
                            <input type="hidden" name="Idfactura" value="'.$e($rows['Idfactura']).'">
                            <button type="submit" class="button is-danger is-rounded is-small" title="Eliminar">
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
                    <td colspan="5">
                        <a href="'.$base_url.'1/'.($estado!==''?'?estado='.$estado:'').'" class="button is-link is-rounded is-small mt-4 mb-4">
                            Haga clic acá para recargar el listado
                        </a>
                    </td>
                </tr>
            ';
        }else{
            $tabla.='
                <tr class="has-text-centered">
                    <td colspan="5">No hay registros en el sistema</td>
                </tr>
            ';
        }
    }

    $tabla.='</tbody></table></div>';

    // Paginación (conserva ?estado=...)
    if($total>0 && $pagina<=$numeroPaginas){
        $tabla.='<p class="has-text-right">Mostrando facturas <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';
        $tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url_paginacion,7);
    }

    return $tabla;
}

		/*----------  Controlador eliminar facturas  ----------*/
		public function eliminarFacturaControlador(){

			$id=$this->limpiarCadena($_POST['Idfactura']);

			if($id==1){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos eliminar la factura del sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    #    exit(); #
			}

			# Verificando factura #
		    $datos=$this->ejecutarConsulta("SELECT * FROM facturas WHERE Idfactura='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la factura en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    #    exit(); #
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Verificando factura #
		    $check_factura=$this->ejecutarConsulta("SELECT Idfactura FROM sistema WHERE Idfactura='$id' LIMIT 1");
		    if($check_factura->rowCount()>0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos eliminar la factura del sistema ya que tiene ventas asociadas",
					"icono"=>"error"
				];
				return json_encode($alerta);
		     #   exit();#
		    }

		    $eliminarFactura=$this->eliminarRegistro("facturas","Idfactura",$id);

		    if($eliminarFactura->rowCount()==1){

		        $alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Factura eliminada",
					"texto"=>"La factura ".$datos['Idfactura']." ha sido eliminado del sistema correctamente",
					"icono"=>"success"
				];

		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido eliminar la factura ".$datos['Idfactura']." del sistema, por favor intente nuevamente",
					"icono"=>"error"
				];
		    }

		    return json_encode($alerta);
		}


		/*----------  Controlador actualizar factura  ----------*/
		public function actualizarFacturaControlador(){

			$id=$this->limpiarCadena($_POST['Idfactura']);

			# Verificando factura #
		    $datos=$this->ejecutarConsulta("SELECT * FROM facturas WHERE Idfactura='$id'");
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la factura en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		      #  exit(); #
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Almacenando datos#
		    $Idfactura=$this->limpiarCadena($_POST['Idfactura']);
			$Idods=$this->limpiarCadena($_POST['Idods']);
		    $Datosfac=$this->limpiarCadena($_POST['Datosfac']);
		    $Estadofac=$this->limpiarCadena($_POST['Estadofac']);  
		   # $Email=$this->limpiarCadena($_POST['Email']); #

		    # Verificando integridad de los datos #
			if ($this->verificarDatos("^[0-9]{7,30}$", $Idfactura)) {
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El ID de la factura no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    #    exit(); #
		    }

		    if ($this->verificarDatos("^[0-9]{7,30}$", $Idods)) {
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El ID de la factura no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    #    exit(); #
		    }

		     if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{4,60}",$Datosfac)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NÚMERO no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    #    exit();  #
		    }

		    if($this->verificarDatos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{4,30}",$Estadofac)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La COLONIA no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    #    exit(); #
		    }


            $factura_datos_up=[
					[
					"campo_nombre"=>"Idfactura",
					"campo_marcador"=>":Id",
					"campo_valor"=>$Idfactura
				],
				[
					"campo_nombre"=>"Idods",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$Idods
				],
				[
					"campo_nombre"=>"Datosfac",
					"campo_marcador"=>":Apellido",
					"campo_valor"=>$Datosfac
				],
				[
					"campo_nombre"=>"Estadofac",
					"campo_marcador"=>":Colonia",
					"campo_valor"=>$Estadofac
				]
			];

			$condicion=[
				"condicion_campo"=>"Idfactura",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("facturas",$factura_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Factura actualizada",
					"texto"=>"Los datos de la factura ".$datos['Idfactura']." se actualizaron correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido actualizar los datos de la factura ".$datos['Idfactura']." por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

	}