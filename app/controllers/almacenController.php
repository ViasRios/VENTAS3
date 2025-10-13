<?php
namespace app\controllers;
use app\models\mainModel;
use \PDO;
class almacenController extends mainModel {

	public function registrarRefaccionControlador() {
    session_start();
	// Verificar si el formulario ya fue procesado para evitar duplicados
    if (isset($_SESSION['form_submitted'])) {
        return json_encode(['ok' => false, 'error' => 'La solicitud ya ha sido procesada.'], JSON_UNESCAPED_UNICODE);
    }
    // Verificar token CSRF
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        return json_encode(['ok' => false, 'error' => 'Error de seguridad: token CSRF inválido.'], JSON_UNESCAPED_UNICODE);
    }
    // Limpiar token CSRF de la sesión después de un envío exitoso
    unset($_SESSION['csrf_token']);

    // Validar los campos obligatorios: IdODS y IdAsesor
    if (empty($_POST['IdODS']) || empty($_POST['IdAsesor'])) {
        return json_encode(['ok' => false, 'error' => 'Los campos "IdODS" y "IdAsesor" son obligatorios.']);
    }

    // Preparar datos de la refacción
    $data = [
        'IdProducto'    => (!empty($_POST['IdProducto']) && is_numeric($_POST['IdProducto'])) 
                            ? (int)$_POST['IdProducto'] 
                            : null,
        'IdODS'         => $this->limpiarCadena($_POST['IdODS'] ?? ''),
        'IdAsesor'      => $this->limpiarCadena($_POST['IdAsesor'] ?? ''),
        'producto'      => $this->limpiarCadena($_POST['producto'] ?? ''), // No obligatorio
        'stock'         => $this->limpiarCadena($_POST['stock'] ?? ''),    // No obligatorio
        'refaccion'     => $_POST['refaccion'] ?? 0,
        'descripcion'   => $this->limpiarCadena($_POST['descripcion'] ?? ''), // No obligatorio
        'muestra_texto' => $this->limpiarCadena($_POST['muestra_texto'] ?? ''),
        'autorizacion'  => isset($_POST['autorizacion']) ? 1 : 0  // checkbox
    ];

    // Procesar la foto
    $muestra_foto = '';

    if (isset($_FILES['muestra_foto']) && $_FILES['muestra_foto']['error'] == 0) {
        $tmpName = $_FILES['muestra_foto']['tmp_name'];
        $originalName = basename($_FILES['muestra_foto']['name']);

        // Nombre único
        $nombreUnico = uniqid('foto_', true) . '_' . $originalName;

        // Carpeta destino física
        $carpetaDestino = dirname(__DIR__, 2) . "/fotos/";

        // Crear carpeta si no existe
        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        $rutaDestino = $carpetaDestino . $nombreUnico;

        if (move_uploaded_file($tmpName, $rutaDestino)) {
            // Guarda ruta relativa web
            $muestra_foto = "fotos/" . $nombreUnico;
        } else {
            $muestra_foto = "";
        }
    } else {
        $muestra_foto = "";
    }

    $data['muestra_foto'] = $muestra_foto;

    // Conexión a la base de datos
    $conn = $this->conectar();
    
    // Preparar la consulta SQL
    $sql = $conn->prepare("
        INSERT INTO refacciones 
        (IdProducto, IdODS, IdAsesor, producto, stock, refaccion, descripcion, muestra_texto, muestra_foto, autorizacion)
        VALUES 
        (:IdProducto, :IdODS, :IdAsesor, :producto, :stock, :refaccion, :descripcion, :muestra_texto, :muestra_foto, :autorizacion)
    ");

    // Establecer encabezado de tipo de contenido JSON
    header('Content-Type: application/json; charset=utf-8');

    // Ejecutar la consulta y verificar el resultado
    if ($sql->execute($data)) {
        // Marcar que el formulario ha sido procesado
        $_SESSION['form_submitted'] = true;

        return json_encode([
            'alert' => 'success',
            'title' => 'Éxito',
            'message' => 'Refacción registrada correctamente.'
        ]);
    } else {
        return json_encode([
            'alert' => 'error',
            'title' => 'Error',
            'message' => 'No se pudo registrar la refacción.'
        ]);
    }
}

	public function cancelarRefaccionControlador(int $IdRefaccion)
{
    try {
        if ($IdRefaccion <= 0) {
            return json_encode(['ok'=>false,'error'=>'IdRefaccion inválido'], JSON_UNESCAPED_UNICODE);
        }

        $db  = $this->conectar();
        $sql = $db->prepare("
            UPDATE refacciones
               SET autorizacion = 0
             WHERE IdRefaccion = :id
        ");
        $sql->execute([':id'=>$IdRefaccion]);

        if ($sql->rowCount() < 1) {
            return json_encode(['ok'=>false,'error'=>'No se pudo cancelar (¿ya estaba cancelada o no existe?)'], JSON_UNESCAPED_UNICODE);
        }

        return json_encode(['ok'=>true,'id'=>$IdRefaccion,'autorizacion'=>0], JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $e) {
        return json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

	public function eliminarRefaccionControlador(int $IdRefaccion)
{
    try {
        if ($IdRefaccion <= 0) {
            return json_encode(['ok'=>false,'error'=>'IdRefaccion inválido'], JSON_UNESCAPED_UNICODE);
        }

        $db = $this->conectar();
        $sql = $db->prepare("DELETE FROM refacciones WHERE IdRefaccion = :id");
        $sql->execute([':id'=>$IdRefaccion]);

        if ($sql->rowCount() < 1) {
            return json_encode(['ok'=>false,'error'=>'No se encontró la refacción o ya estaba eliminada'], JSON_UNESCAPED_UNICODE);
        }

        return json_encode(['ok'=>true,'deleted'=>$IdRefaccion], JSON_UNESCAPED_UNICODE);

    } catch (\Throwable $e) {
        return json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}


	public function registrarInventarioControlador() {

		$conn = $this->conectar();

		$data = [
			'producto'        => $this->limpiarCadena($_POST['producto'] ?? ''),
			'codigo'          => $this->limpiarCadena($_POST['codigo'] ?? null),
			'descripcion'     => $this->limpiarCadena($_POST['descripcion'] ?? ''),
			'caracteristica1' => $this->limpiarCadena($_POST['caracteristica1'] ?? ''),
			'caracteristica2' => $this->limpiarCadena($_POST['caracteristica2'] ?? ''),
			'caracteristica3' => $this->limpiarCadena($_POST['caracteristica3'] ?? ''),
			'caracteristica4' => $this->limpiarCadena($_POST['caracteristica4'] ?? ''),
			'proveedor'       => $this->limpiarCadena($_POST['proveedor'] ?? ''),
			'stock'           => is_numeric($_POST['stock'] ?? '') ? (int)$_POST['stock'] : 0,
			'precio_compra'   => is_numeric($_POST['precio_compra'] ?? '') ? (float)$_POST['precio_compra'] : 0.00,
			'precio_venta'    => is_numeric($_POST['precio_venta'] ?? '') ? (float)$_POST['precio_venta'] : 0.00,
			'precio_sugerido' => is_numeric($_POST['precio_sugerido'] ?? '') ? (float)$_POST['precio_sugerido'] : 0.00,
			'autorizacion'    => isset($_POST['autorizacion']) ? 1 : 0
		];

		// Preparar consulta
		$sql = $conn->prepare("
			INSERT INTO inventario 
			(producto, codigo, descripcion, caracteristica1, caracteristica2, caracteristica3, caracteristica4, proveedor, stock, precio_compra, precio_venta, autorizacion)
			VALUES 
			(:producto, :codigo, :descripcion, :caracteristica1, :caracteristica2, :caracteristica3, :caracteristica4, :proveedor, :stock, :precio_compra, :precio_venta, :autorizacion)
		");

		header('Content-Type: application/json; charset=utf-8');
		if ($sql->execute($data)) {
			return json_encode([
				'alert' => 'success',
				'title' => 'Éxito',
				'message' => 'Producto registrado correctamente en el inventario.'
			]);
		} else {
			return json_encode([
				'alert' => 'error',
				'title' => 'Error',
				'message' => 'No se pudo registrar el producto en el inventario.'
			]);
		}
	}

	public function listarInventarioControlador($pagina, $registros, $url, $busqueda) {

		$pagina = $this->limpiarCadena($pagina);
		$registros = $this->limpiarCadena($registros);

		$url = $this->limpiarCadena($url);
		$url = APP_URL . $url . "/";

		$busqueda = $this->limpiarCadena($busqueda);
		$tabla = "";

		$pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
		$inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

		$campos = "IdProducto,producto,codigo,descripcion,proveedor,stock,precio_compra,precio_venta";

		if (!empty($busqueda)) {
			$consulta_datos = "
				SELECT $campos 
				FROM inventario 
				WHERE producto LIKE '%$busqueda%' 
				OR descripcion LIKE '%$busqueda%' 
				OR proveedor LIKE '%$busqueda%' 
				ORDER BY producto ASC 
				LIMIT $inicio,$registros";

			$consulta_total = "
				SELECT COUNT(IdProducto) 
				FROM inventario 
				WHERE producto LIKE '%$busqueda%' 
				OR descripcion LIKE '%$busqueda%' 
				OR proveedor LIKE '%$busqueda%'";
		} else {
			$consulta_datos = "
				SELECT $campos 
				FROM inventario 
				ORDER BY producto ASC 
				LIMIT $inicio,$registros";

			$consulta_total = "
				SELECT COUNT(IdProducto) 
				FROM inventario";
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
						<th>#</th>
						<th>Producto</th>
						<th>Código</th>
						<th>Proveedor</th>
						<th>Stock</th>
						<th>Precio compra</th>
						<th>Precio venta</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
			';

			$contador = $inicio + 1;
			foreach ($datos as $rows) {
				$tabla .= '
					<tr>
						<td>' . $contador . '</td>
						<td>' . htmlspecialchars($rows['producto']) . '</td>
						<td>' . htmlspecialchars($rows['codigo']) . '</td>
						<td>' . htmlspecialchars($rows['proveedor']) . '</td>
						<td>' . $rows['stock'] . '</td>
						<td>$' . $rows['precio_compra'] . '</td>
						<td>$' . $rows['precio_venta'] . '</td>
						<td>
							<a href="' . APP_URL . 'inventarioUpdate/' . $rows['IdProducto'] . '/" class="button is-success is-small is-rounded">
								<i class="fas fa-sync fa-fw"></i>
							</a>
							<form class="FormularioAjax is-inline-block" action="' . APP_URL . 'app/ajax/inventarioAjax.php" method="POST" autocomplete="off">
								<input type="hidden" name="modulo_inventario" value="eliminar">
								<input type="hidden" name="IdProducto" value="' . $rows['IdProducto'] . '">
								<button type="submit" class="button is-danger is-small is-rounded">
									<i class="far fa-trash-alt fa-fw"></i>
								</button>
							</form>
						</td>
					</tr>
				';
				$contador++;
			}

			$tabla .= '
				</tbody>
			</table>
			</div>
			';

			$tabla .= '<p class="has-text-right">Mostrando productos <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
			$tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);

		} else {
			if ($total >= 1) {
				$tabla .= '
				<p class="has-text-centered pb-6"><i class="far fa-hand-point-down fa-5x"></i></p>
				<p class="has-text-centered">
					<a href="' . $url . '1/" class="button is-link is-rounded is-small mt-4 mb-4">
						Haga clic acá para recargar el listado
					</a>
				</p>';
			} else {
				$tabla .= '
				<p class="has-text-centered pb-6"><i class="far fa-grin-beam-sweat fa-5x"></i></p>
				<p class="has-text-centered">No hay productos registrados en el inventario</p>';
			}
		}

		return $tabla;
	}

	public function listarRefaccionControlador($pagina, $registros, $url, $busqueda) {

		$pagina = $this->limpiarCadena($pagina);
		$registros = $this->limpiarCadena($registros);
		$url = $this->limpiarCadena($url);
		$url = APP_URL . $url . "/";

		$busqueda = $this->limpiarCadena($busqueda);
		$tabla = "";

		$pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
		$inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

		$campos = "IdRefaccion, IdODS, IdAsesor, IdProducto, producto, stock, refaccion, descripcion, muestra_texto, muestra_foto, autorizacion";

		if (!empty($busqueda)) {
			$consulta_datos = "
				SELECT $campos
				FROM refacciones
				WHERE producto LIKE '%$busqueda%' OR descripcion LIKE '%$busqueda%'
				ORDER BY producto ASC
				LIMIT $inicio,$registros";

			$consulta_total = "
				SELECT COUNT(IdRefaccion)
				FROM refacciones
				WHERE producto LIKE '%$busqueda%' OR descripcion LIKE '%$busqueda%'";
		} else {
			$consulta_datos = "
				SELECT $campos
				FROM refacciones
				ORDER BY producto ASC
				LIMIT $inicio,$registros";

			$consulta_total = "
				SELECT COUNT(IdRefaccion)
				FROM refacciones";
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
						<th>ID Refaccion</th>
						<th>ID Producto</th>
						<th>ID ODS</th>
						<th>Asesor</th>
						<th>Producto</th>
						<th>Stock</th>
						<th>Refacción</th>
						<th>Autorizado</th>
						<th>Muestra texto</th>
						<th>Muestra foto</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
			';

			$contador = $inicio + 1;
			foreach ($datos as $rows) {
				$tabla .= '
					<tr>
						<td>' . htmlspecialchars($rows['IdRefaccion']) . '</td>
						<td>' . htmlspecialchars($rows['IdProducto']) . '</td>
						<td>' . htmlspecialchars($rows['IdODS']) . '</td>
						<td>' . htmlspecialchars($rows['IdAsesor']) . '</td>
						<td>' . htmlspecialchars($rows['producto']) . '</td>
						<td>' . $rows['stock'] . '</td>
						<td>' . ($rows['refaccion'] ? 'Sí' : 'No') . '</td>
						<td>' . ($rows['autorizacion'] ? 'Sí' : 'No') . '</td>
						<td>' . htmlspecialchars($rows['muestra_texto']) . '</td>
						<td>' . (!empty($rows['muestra_foto']) 
									? '<a href="' . APP_URL . $rows['muestra_foto'] . '" target="_blank">Ver foto</a>' 
									: '—') . '</td>
						<td>
							<a href="' . APP_URL . 'refaccionUpdate/' . $rows['IdRefaccion'] . '/" class="button is-success is-small is-rounded">
								<i class="fas fa-sync fa-fw"></i>
							</a>
							<form class="FormularioAjax is-inline-block" action="' . APP_URL . 'app/ajax/refaccionAjax.php" method="POST" autocomplete="off">
								<input type="hidden" name="modulo_refaccion" value="eliminar">
								<input type="hidden" name="IdRefaccion" value="' . $rows['IdRefaccion'] . '">
								<button type="submit" class="button is-danger is-small is-rounded">
									<i class="far fa-trash-alt fa-fw"></i>
								</button>
							</form>
						</td>
					</tr>
				';
				$contador++;
			}

			$tabla .= '
				</tbody>
			</table>
			</div>
			';

			$tabla .= '<p class="has-text-right">Mostrando refacciones <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
			$tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);

		} else {
			if ($total >= 1) {
				$tabla .= '
				<p class="has-text-centered pb-6"><i class="far fa-hand-point-down fa-5x"></i></p>
				<p class="has-text-centered">
					<a href="' . $url . '1/" class="button is-link is-rounded is-small mt-4 mb-4">
						Haga clic acá para recargar el listado
					</a>
				</p>';
			} else {
				$tabla .= '
				<p class="has-text-centered pb-6"><i class="far fa-grin-beam-sweat fa-5x"></i></p>
				<p class="has-text-centered">No hay refacciones registradas</p>';
			}
		}

		return $tabla;
	}

	public function listarPedidosControlador($pagina, $registros, $url, $busqueda) {

		$pagina = $this->limpiarCadena($pagina);
		$registros = $this->limpiarCadena($registros);
		$url = $this->limpiarCadena($url);
		$url = APP_URL . $url . "/";

		$busqueda = $this->limpiarCadena($busqueda);
		$tabla = "";

		$pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
		$inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

		$campos = "IdPedidos, IdODS, IdRefaccion, descripcion, precio_compra, proveedor, link_seguimiento, fecha_orden, fecha_llegada_aprox, fecha_caducidad, status, entregado_tecnico";

		if (!empty($busqueda)) {
			$consulta_datos = "
				SELECT $campos
				FROM pedidos
				WHERE descripcion LIKE '%$busqueda%' OR proveedor LIKE '%$busqueda%'
				ORDER BY fecha_orden DESC
				LIMIT $inicio,$registros";

			$consulta_total = "
				SELECT COUNT(IdPedidos)
				FROM pedidos
				WHERE descripcion LIKE '%$busqueda%' OR proveedor LIKE '%$busqueda%'";
		} else {
			$consulta_datos = "
				SELECT $campos
				FROM pedidos
				ORDER BY fecha_orden DESC
				LIMIT $inicio,$registros";

			$consulta_total = "
				SELECT COUNT(IdPedidos)
				FROM pedidos";
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
						<th>#</th>
						<th>ID ODS</th>
						<th>ID Refacción</th>
						<th>Descripción</th>
						<th>Precio compra</th>
						<th>Proveedor</th>
						<th>Status</th>
						<th>Entregado a técnico</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
			';

			$contador = $inicio + 1;
			foreach ($datos as $rows) {
				$tabla .= '
					<tr>
						<td>' . $contador . '</td>
						<td>' . htmlspecialchars($rows['IdODS']) . '</td>
						<td>' . htmlspecialchars($rows['IdRefaccion']) . '</td>
						<td>' . htmlspecialchars($rows['descripcion']) . '</td>
						<td>$' . $rows['precio_compra'] . '</td>
						<td>' . htmlspecialchars($rows['proveedor']) . '</td>
						<td>' . htmlspecialchars($rows['status']) . '</td>
						<td>' . ($rows['entregado_tecnico'] ? 'Sí' : 'No') . '</td>
						<td>
							<a href="' . APP_URL . 'pedidoUpdate/' . $rows['IdPedidos'] . '/" class="button is-success is-small is-rounded">
								<i class="fas fa-sync fa-fw"></i>
							</a>
							<form class="FormularioAjax is-inline-block" action="' . APP_URL . 'app/ajax/pedidoAjax.php" method="POST" autocomplete="off">
								<input type="hidden" name="modulo_pedido" value="eliminar">
								<input type="hidden" name="IdPedidos" value="' . $rows['IdPedidos'] . '">
								<button type="submit" class="button is-danger is-small is-rounded">
									<i class="far fa-trash-alt fa-fw"></i>
								</button>
							</form>
						</td>
					</tr>
				';
				$contador++;
			}

			$tabla .= '
				</tbody>
			</table>
			</div>
			';

			$tabla .= '<p class="has-text-right">Mostrando pedidos <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
			$tabla .= $this->paginadorTablas($pagina, $numeroPaginas, $url, 7);

		} else {
			if ($total >= 1) {
				$tabla .= '
				<p class="has-text-centered pb-6"><i class="far fa-hand-point-down fa-5x"></i></p>
				<p class="has-text-centered">
					<a href="' . $url . '1/" class="button is-link is-rounded is-small mt-4 mb-4">
						Haga clic acá para recargar el listado
					</a>
				</p>';
			} else {
				$tabla .= '
				<p class="has-text-centered pb-6"><i class="far fa-grin-beam-sweat fa-5x"></i></p>
				<p class="has-text-centered">No hay pedidos registrados</p>';
			}
		}

		return $tabla;
	}

	public function actualizarRefaccionControlador(){
		$id = intval($_POST['IdRefaccion']);
		$caducidad = $_POST['caducidad'] ?? null;
		$proveedor = mainModel::limpiarCadena($_POST['proveedor'] ?? '');
		$precio_cliente = floatval($_POST['precio_cliente'] ?? 0);
		$precio_estimado = floatval($_POST['precio_estimado'] ?? 0);
		$precio_compra = floatval($_POST['precio_compra'] ?? 0);

		$db = mainModel::conectar();

		$sql = "UPDATE refacciones 
				SET caducidad = :caducidad,
					proveedor = :proveedor,
					precio_cliente = :precio_cliente,
					precio_estimado = :precio_estimado,
					precio_compra = :precio_compra
				WHERE IdRefaccion = :id";

		$stmt = $db->prepare($sql);
		$stmt->bindParam(':caducidad', $caducidad);
		$stmt->bindParam(':proveedor', $proveedor);
		$stmt->bindParam(':precio_cliente', $precio_cliente);
		$stmt->bindParam(':precio_estimado', $precio_estimado);
		$stmt->bindParam(':precio_compra', $precio_compra);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);

		if($stmt->execute()){
			return json_encode([
				"Alerta" => "simple",
				"Titulo" => "¡Actualizado!",
				"Texto" => "La refacción ha sido actualizada correctamente.",
				"Tipo" => "success"
			]);
		} else {
			return json_encode([
				"Alerta" => "simple",
				"Titulo" => "Error",
				"Texto" => "No se pudo actualizar la refacción.",
				"Tipo" => "error"
			]);
		}
	}

	public function autorizarRefaccionControlador(int $IdRefaccion) {
    try {
        // Validar que el ID de la refacción sea válido
        if ($IdRefaccion <= 0) {
            return json_encode(['ok' => false, 'error' => 'ID de refacción inválido'], JSON_UNESCAPED_UNICODE);
        }

        // Conexión a la base de datos
        $db = $this->conectar();

        // Consulta para actualizar el estado de la refacción a "Autorizada"
        $sql = $db->prepare("
            UPDATE refacciones
            SET autorizacion = 1
            WHERE IdRefaccion = :id
        ");
        $sql->bindParam(':id', $IdRefaccion, PDO::PARAM_INT);

        // Ejecutar la consulta
        $sql->execute();

        // Verificar si la consulta afectó alguna fila
        if ($sql->rowCount() > 0) {
            return json_encode(['ok' => true, 'message' => 'Refacción autorizada correctamente.'], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(['ok' => false, 'error' => 'No se pudo autorizar la refacción o ya estaba autorizada.'], JSON_UNESCAPED_UNICODE);
        }
    } catch (\Throwable $e) {
        // Manejo de excepciones
        return json_encode(['ok' => false, 'error' => 'Error al autorizar la refacción: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}


	public function obtenerResumenEstadoRefaccionControlador() {
		$db = mainModel::conectar();
		// Consulta con FIELD para ordenar los estados en el orden que tú deseas
		$consulta = $db->query("SELECT estado, COUNT(*) AS total 
								FROM refacciones 
								GROUP BY estado 
								ORDER BY FIELD(estado, 'REQUERIDO', 'AUTORIZADO', 'RECIBIDO', 'ENTREGADO AL TECNICO', 'PRUEBAS')");
		return $consulta->fetchAll();
	}


	public function eliminarInventarioControlador($idRefaccion) {
        // Verifica si el ID es válido
        if (empty($idRefaccion)) {
            return json_encode(['success' => false, 'message' => 'ID de refacción no proporcionado.']);
        }

        // Intentar eliminar la refacción
        try {
            // Usar el modelo para eliminar la refacción
            $db = mainModel::conectar();  // Conectamos a la base de datos
            $stmt = $db->prepare("DELETE FROM refacciones WHERE IdRefaccion = :id");
            $stmt->bindValue(':id', $idRefaccion, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Si la refacción se eliminó con éxito
                return json_encode(['success' => true, 'message' => 'Refacción eliminada correctamente.']);
            } else {
                // Si no se eliminó ninguna fila (puede ser que el ID no exista)
                return json_encode(['success' => false, 'message' => 'No se encontró la refacción para eliminar.']);
            }
		} catch (\Exception $e) {
			// Si ocurre un error, se captura y devuelve el mensaje de error
			return json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
		}
    }

}
