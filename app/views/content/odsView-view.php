<?php
use app\models\mainModel;

$Idods = isset($url[1]) ? intval($url[1]) : 0;

if ($Idods <= 0) {
    echo "<p class='has-text-centered has-text-danger'>No se encontró la ODS solicitada.</p>";
    return;
}

$sql = "
SELECT 
    ods.*, 
    clientes.Nombre AS NombreCliente,
    clientes.Numero,
    clientes.Email,
    clientes.Colonia,
    personal.Nombre AS NombreAsesor,
    p2.Nombre AS NombreTecnico
FROM ods
INNER JOIN clientes ON ods.Idcliente = clientes.Idcliente
INNER JOIN personal ON ods.Idasesor = personal.Idasesor
INNER JOIN personal p2 ON ods.IdTecnico = p2.Idasesor
WHERE ods.Idods = $Idods
";

$consulta = mainModel::ejecutarConsulta($sql);
$ods = $consulta->fetch();
if (!$ods) {
    echo "<p class='has-text-centered has-text-danger'>La ODS no existe.</p>";
    return;
}

// Obtener clase de color por estado
function claseColorEstado($status) {
    $normalizado = strtolower(str_replace(' ', '', iconv('UTF-8', 'ASCII//TRANSLIT', $status)));
    return match($normalizado) {
        'recepcion'     => 'estado-recepcion',
        'diagnostico'   => 'estado-diagnostico',
        'presupuesto'   => 'estado-presupuesto',
        'autorizacion'  => 'estado-autorizacion',
        'standby'       => 'estado-standby',
        'reparacion'    => 'estado-reparacion',
        'refacciones'   => 'estado-refacciones',
        'listoe'        => 'estado-listoe',
        'almacen'       => 'estado-almacen',
        'entregado'     => 'estado-entregado',
        'seguimiento'   => 'estado-seguimiento',
        default         => 'estado-default'
    };
}

$clase_estado = claseColorEstado($ods['Status']);
?>

<!-- ====== TEMA MODERNO (completamente distinto) ====== -->
<style>
/* Wrapper del nuevo tema (scope para no afectar modales) */
.odsview_modern {
  --bg: #f7f7f5;
  --fg: #1e293b;               /* gris pizarra oscuro */
  --muted: #242528ff;            /* gris medio */
  --accent: #0b6b60;           /* verde petróleo sobrio */
  --accent-2: #134e4a;         /* verde más oscuro */
  --border: #e3e3e0;
  --border-strong: #d6d6d3;
  --card: #ffffff;
  --shadow: 0 6px 24px rgba(0,0,0,.08);
  --radius: 14px;

  font-family: "Georgia", "Times New Roman", serif; /* look formal */
  color: var(--fg);
  background: var(--bg);
  padding-bottom: 1rem;
}

/* Encabezados generales */
.odsview_modern h1.title,
.odsview_modern h2.subtitle {
  color: var(--fg) !important;
  letter-spacing: .2px;
}
.odsview_modern h1.title { font-weight: 800; }
.odsview_modern h2.subtitle {
  font-weight: 700 !important;
  font-size: 1.05rem !important;
  display: flex; align-items: center; gap: .55rem;
}

/* Hero del header */
.odsview_modern .container.is-fluid.mb-3[class*="estado-"]{
  border-radius: 14px;
  padding: 1.2rem 1rem;
  box-shadow: 0 6px 24px rgba(0,0,0,.08);
  position: relative;
}
.odsview_modern .container.is-fluid.mb-3[class*="estado-"]::after{
  content:"";
  position:absolute; left:18px; right:18px; bottom:-10px; height:8px;
  border-radius: 0 0 10px 10px; background: rgba(0,0,0,.08);
}
.odsview_modern .container.is-fluid.mb-3[class*="estado-"] .title{
  margin:0 !important; font-weight:800;
}
.odsview_modern .container.is-fluid.mb-3[class*="estado-"] .title,
.odsview_modern .container.is-fluid.mb-3[class*="estado-"] .title .has-text-weight{
  color:#fff !important;
}

/* Colores de fondo por estado */
.odsview_modern .estado-recepcion   { background:#f98151 !important; }
.odsview_modern .estado-diagnostico { background:#f6b555 !important; }
.odsview_modern .estado-presupuesto { background:#e75dfc !important; }
.odsview_modern .estado-autorizacion{ background:#5be6b7 !important; }
.odsview_modern .estado-standby     { background:#ff8d70 !important; }
.odsview_modern .estado-reparacion  { background:#3facdf !important; }
.odsview_modern .estado-refacciones { background:#9365e9 !important; }
.odsview_modern .estado-listoe      { background:#49c551 !important; }
.odsview_modern .estado-almacen     { background:#fc4c78 !important; }
.odsview_modern .estado-entregado   { background:#f6fc4b !important; }
.odsview_modern .estado-seguimiento { background:#f893b7 !important; }
.odsview_modern .estado-default     { background:#c7c5c5 !important; }

/* Tarjetas limpias */
.odsview_modern .box {
  background: var(--card) !important;
  border: 1px solid var(--border) !important;
  border-radius: var(--radius) !important;
  box-shadow: var(--shadow);
  padding: 18px !important;
  transition: transform .08s ease, box-shadow .15s ease;
}
.odsview_modern .box:hover { transform: translateY(-1px); box-shadow: 0 10px 30px rgba(0,0,0,.09); }

/* Estilo de columnas para el contenedor 2 por 2 */
.odsview_modern .columns {
  margin: 0;
  display: flex;
  flex-wrap: wrap;
}
.odsview_modern .column {
  flex: 1 1 48%; /* 2 elementos por fila */
  margin: 10px;
}
</style>

<!-- ====== ABRE EL WRAPPER DEL NUEVO TEMA ====== -->
<div class="odsview_modern">
  <!-- ENCABEZADO CON COLOR SEGÚN STATUS -->
  <div class="container is-fluid mb-3 <?php echo $clase_estado; ?>">
      <h1 class="title has-text-black">
          ODS #<?php echo $ods['Idods']; ?> 
          <span class="has-text-weight">— <?php echo strtoupper($ods['Status']); ?></span>
      </h1>
      <meta charset="UTF-8">
  </div>

  <div class="container pb-1 pt-1">
    <!-- Fila 1: CLIENTE + INFORMACIÓN ODS -->
    <div class="columns" style="margin-top: -10px;">
      <!-- CLIENTE -->
      <div class="column" >
        <div class="box" style="background-color: #007BFF; padding: 15px; margin: 5px; border-radius: 8px;">
          <div class="info-box" style="background-color: white; color: black; padding: 10px; border-radius: 6px; margin-bottom: -15px;">
              <h2 class="subtitle has-text-weight-bold">
                  <i class="fas fa-user"></i> CLIENTE
                  <button class="button is-small ml-3" onclick="toggleCliente()">
                      <span class="icon"><i class="fas fa-eye"></i></span>
                      <span>Ver/Ocultar</span>
                  </button>
              </h2>

              <div id="infoCliente" class="columns is-multiline mt-3">
                  <div class="column" style="margin-top: -20px;">
                      <p><strong>Nombre:</strong> <span><?php echo $ods['NombreCliente']; ?></span></p>
                      <p><strong>Teléfono:</strong> <span><?php echo $ods['Numero']; ?></span></p>
                      <p><strong>Correo:</strong> <span><?php echo $ods['Email']; ?></span></p>
                      <p><strong>Dirección:</strong> <span><?php echo $ods['Colonia']; ?></span></p>
                  </div>
              </div>
          </div>
        </div>
      </div>
      <script>
        function toggleCliente(){ document.getElementById("infoCliente").classList.toggle("is-hidden"); }
      </script>

      <!-- INFORMACIÓN ODS -->
      <div class="column">
        <div class="box pastel-yellow" style="padding: 15px; border-radius: 8px;">
          <div class="info-box" style="background-color: white; color: black; padding: 15px; border-radius: 6px; margin-bottom: -15px;">
              <h2 class="subtitle has-text-weight-bold">
                  <i class="fas fa-file-alt"></i> INFORMACIÓN ODS
                  <button class="button is-small ml-3" onclick="toggleOds()">
                      <span class="icon"><i class="fas fa-eye"></i></span>
                      <span>Ver/Ocultar</span>
                  </button>
              </h2>
              <div id="infoOds" class="columns is-multiline mt-3">
                  <div class="column" style="margin-top: -20px;">
                      <p><strong>Fecha ingreso:</strong> 
                          <span>
                          <?php $fecha = new DateTime($ods['Fecha']); echo $fecha->format('d/m/Y'); ?>
                          </span>
                      </p>

                      <?php if (!empty($ods['Odsanterior'])): ?>
                          <p>
                              <strong>ODS Anterior:</strong> 
                              <a class="button is-link is-small" href="<?php echo APP_URL; ?>odsView/<?php echo $ods['Odsanterior']; ?>/">
                                  Ver ODS #<?php echo $ods['Odsanterior']; ?>
                              </a>
                          </p>
                      <?php else: ?>
                          <p><strong>ODS Anterior:</strong> <span>No disponible</span></p>
                      <?php endif; ?>

                      <p><strong>Respaldo:</strong> 
                          <?php if (strtolower(trim($ods['Respaldo'])) === 'si'): ?>
                              <span style="color: red; font-weight: bold;">Sí</span>
                          <?php else: ?>
                              <span><?php echo htmlspecialchars($ods['Respaldo']); ?></span>
                          <?php endif; ?>
                      </p>

                      <p><strong>Asesor Recepción:</strong> <span><?php echo $ods['NombreAsesor']; ?></span></p>
                      <p><strong>Asesor Entrega:</strong> <span><?php echo $ods['NombreTecnico']; ?></span></p>
                      <p><strong>Fecha entrega:</strong> 
                          <span>
                          <?php $fecha = new DateTime($ods['Fechaentrega']); echo $fecha->format('d/m/Y'); ?>
                          </span>
                      </p>
                      <p><strong>Saldo a cuenta:</strong> <span><?php echo $ods['Cuenta']; ?></span></p>
                  </div>
              </div>
          </div>
        </div>
      </div>
    </div>
    <script>
        function toggleOds(){ document.getElementById("infoOds").classList.toggle("is-hidden"); }
      </script>

    <!-- Fila 2: APARATO + DESCRIPCIÓN EQUIPO -->
    <div class="columns" style="margin-top: -15px;">
      <!-- APARATO -->
      <div class="column">
        <div class="box pastel-green" style="padding: 15px; border-radius: 8px;">
          <div class="info-box" style="background-color: white; color: black; padding: 15px; border-radius: 6px;">
            <h2 class="subtitle has-text-weight-bold">
                <i class="fas fa-laptop"></i> APARATO
                <button class="button is-small ml-3" onclick="toggleAparato()">
                  <span class="icon"><i class="fas fa-eye"></i></span>
                  <span>Ver/Ocultar</span>
                </button>
            </h2>

            <div id="infoAparato" class="columns is-multiline mt-3">
                <div class="column" style="margin-top: -20px;">
                    <p><strong>Tipo:</strong> <span><?php echo $ods['Tipo']; ?></span></p>
                    <p><strong>Color:</strong> <span><?php echo $ods['Color']; ?></span></p>
                    <p><strong>Marca:</strong> <span><?php echo $ods['Marca']; ?></span></p>
                
                    <p><strong>Modelo:</strong> <span><?php echo $ods['Modelo']; ?></span></p>
                    <p><strong>No. Serie:</strong> <span><?php echo $ods['Noserie']; ?></span></p>
                    <p><strong>Contraseña:</strong> <span><?php echo $ods['Contrasena']; ?></span></p>
                    <p><strong>Accesorios:</strong> <span><?php echo $ods['Accesorios']; ?></span></p>
                </div>
            </div>
          </div>
        </div>
      </div>
      <script>
        function toggleAparato(){ document.getElementById("infoAparato").classList.toggle("is-hidden"); }
      </script>

      <!-- DESCRIPCIÓN PROBLEMA EQUIPO -->
      <div class="column">
        <div class="box pastel-orange" style="padding: 15px; border-radius: 8px;">
          <div class="info-box" style="background-color: white; color: black; padding: 15px; border-radius: 6px;">
            <h2 class="subtitle has-text-weight-bold">
                <i class="fas fa-desktop"></i> DESCRIPCIÓN EQUIPO
                <button class="button is-small ml-3" onclick="toggleDescripcion()">
                  <span class="icon"><i class="fas fa-eye"></i></span>
                  <span>Ver/Ocultar</span>
                </button>
            </h2>
            <div id="infoDescripcion" class="columns is-multiline mt-3">
                <div class="column" style="margin-top: -20px;">
                    <p><strong>Problema con el equipo:</strong> <span><?php echo $ods['Problema']; ?></span></p>
                </div>
                <div class="column">
                    <p><strong>Inspección del equipo:</strong> <span><?php echo $ods['Inspeccion']; ?></span></p>
                </div>
            </div>
          </div>
        </div>
      </div>
      <script>
        function toggleDescripcion(){ document.getElementById("infoDescripcion").classList.toggle("is-hidden"); }
      </script>
    </div>

    <!-- Fila 3: SERVICIOS + REFACCIONES -->
    <div class="columns" style="margin-top: -15px;">
      <!-- SERVICIOS -->
      <div class="column">
        <div class="box pastel-purpled" style="padding: 15px; border-radius: 8px;">
          <div class="info-box" style="background-color: white; color: black; padding: 15px; border-radius: 6px;">
            <h2 class="subtitle has-text-weight-bold">
                <i class="fas fa-concierge-bell"></i> SERVICIOS
                <button class="button is-small ml-3" onclick="toggleServicios()">
                  <span class="icon"><i class="fas fa-eye"></i></span>
                  <span>Ver/Ocultar</span>
                </button>
            </h2>

            <div id="tablaServicios" class="mt-3">
              <table class="table is-bordered is-fullwidth is-hoverable">
                <thead>
                  <tr>
                    <th>Servicio</th>
                    <th>Costo</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $lista_reparaciones = array_filter(explode(',', $ods['Reparacion']));
                  $lista_costos = array_filter(explode(',', $ods['Costorep']));
                  $max = max(count($lista_reparaciones), count($lista_costos));

                  for ($i = 0; $i < $max; $i++) {
                      $reparacion = trim($lista_reparaciones[$i] ?? '');
                      $costo = trim($lista_costos[$i] ?? '');
                      echo "<tr>
                              <td><span>{$reparacion}</span></td>
                              <td><span>{$costo}</span></td>
                            </tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
      <script>
        function toggleServicios(){ document.getElementById("tablaServicios").classList.toggle("is-hidden"); }
      </script>

      <!-- REFACCIONES -->
      <div class="column">
        <div class="box pastel-oranged" style="padding: 15px; border-radius: 8px;">
          <div class="info-box" style="background-color: white; color: black; padding: 15px; border-radius: 6px;">
            
            <h2 class="subtitle has-text-weight-bold">
                <i class="fas fa-tools"></i> REFACCIONES
                <button class="button is-small ml-3" onclick="toggleRefacciones()">
                  <span class="icon"><i class="fas fa-eye"></i></span>
                  <span>Ver/Ocultar</span>
                </button>
            </h2>

            <?php
            $Idods = isset($url[1]) ? intval($url[1]) : 0;
            $sql = "
            SELECT 
                refacciones.Nombre_refaccion,
                refacciones.descripcion,
                refacciones.estado,
                refacciones.fechaRefaccion AS Fecha,
                refacciones.IdAsesor,
                personal.Nombre AS NombreODS 
            FROM refacciones
            INNER JOIN personal ON refacciones.IdAsesor = personal.Idasesor
            WHERE refacciones.estado = 'Autorizado' AND refacciones.IdODS = $Idods
            ";

            $consulta = mainModel::ejecutarConsulta($sql);
            $refacciones = $consulta->fetchAll();

            if (!$refacciones) {
                echo "<p class='has-text-centered has-text-danger'>No hay refacciones autorizadas para esta ODS.</p>";
            } else {
            ?>
              <div id="contenidoRefacciones" class="mt-3">
                  <table class="table is-bordered is-fullwidth is-hoverable">
                      <thead>
                          <tr>
                              <th>Nombre</th>
                              <th>Descripción</th>
                              <th>Asesor</th>
                              <th>Estado</th>
                              <th>Fecha</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($refacciones as $refaccion): ?>
                            <tr>
                              <td><span><?= $refaccion['Nombre_refaccion'] ?></span></td>
                              <td><span><?= $refaccion['descripcion'] ?></span></td>
                              <td><span><?= $refaccion['NombreODS'] ?></span></td>
                              <td><span><?= $refaccion['estado'] ?></span></td>
                              <td><span><?= $refaccion['Fecha'] ?></span></td>
                            </tr>
                          <?php endforeach; ?>
                      </tbody>
                  </table>
              </div>
            <?php } ?>

            <div class="level-left mt-3">
                <button class="button is-small has-text-black has-text-weight-bold" id="abrirModalRefaccion">
                  <i class="fas fa-plus-circle"></i>&nbsp; Petición Refacción
                </button>
            </div>

          </div>
        </div>
      </div>
      <script>
        function toggleRefacciones(){
          const c = document.getElementById("contenidoRefacciones");
          if (c) c.classList.toggle("is-hidden");
        }
      </script>
    </div>

    <!-- REPORTES TÉCNICOS y NOTAS -->
    <?php
    $reportes = mainModel::ejecutarConsulta("SELECT Fecha, Hora, Tecnico, Reporte AS Contenido, Evidencia, 'reporte' AS Tipo FROM reportetec WHERE Idods = $Idods");
    $notas    = mainModel::ejecutarConsulta("SELECT Fecha, Hora, Tecnico, Nota AS Contenido, '' AS Evidencia, 'nota' AS Tipo FROM notas WHERE Idods = $Idods");
    $eventos  = array_merge($reportes->fetchAll(), $notas->fetchAll());
    usort($eventos, function($a, $b) {
        $fechaA = strtotime($a['Fecha'] . ' ' . $a['Hora']);
        $fechaB = strtotime($b['Fecha'] . ' ' . $b['Hora']);
        return $fechaA - $fechaB;
    });
    ?>

    <div class="box pastel-blues">
      <div class="info-box" style="background-color: white; color: black; padding: 15px; border-radius: 6px;">
        <h2 class="subtitle has-text-weight-bold">
            <i class="fas fa-comments"></i> HISTORIAL DE NOTAS Y REPORTES
            <button class="button is-small ml-3" onclick="toggleNotasReportes()">
              <span class="icon"><i class="fas fa-eye"></i></span>
              <span>Ver/Ocultar</span>
            </button>
        </h2>

        <div class="box" id="bloqueNotasReportes">
            <div class="level mb-2">
                <div class="level-right">
                    <button class="button is-small has-text-black has-text-weight-bold" id="abrirModalNota">
                        <i class="fas fa-sticky-note"></i>&nbsp; Nueva Nota
                    </button>
                    &nbsp;
                    <button class="button is-small has-text-black has-text-weight-bold" id="abrirModalReporte">
                        <i class="fas fa-plus-circle"></i>&nbsp; Nuevo Reporte
                    </button>
                </div>
            </div>

            <?php if (count($eventos) > 0): ?>
              <div class="table-container">
                  <table class="table is-fullwidth is-hoverable">
                      <thead>
                          <tr>
                              <th>Fecha</th>
                              <th>Hora</th>
                              <th>Técnico</th>
                              <th>Contenido</th>
                              <th>Evidencia</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach ($eventos as $e): ?>
                              <!-- 2) FONDOS COLOREADOS COMO ANTES (AZUL/ROJO) -->
                              <tr style="background-color: <?php echo ($e['Tipo'] === 'reporte') ? '#73b9faff' : '#f87f79ff'; ?>">
                                  <td><?php echo $e['Fecha']; ?></td>
                                  <td><?php echo $e['Hora']; ?></td>
                                  <td><?php echo htmlspecialchars($e['Tecnico']); ?></td>
                                  <td><?php echo nl2br(htmlspecialchars($e['Contenido'])); ?></td>
                                  <td>
                                      <?php if (!empty($e['Evidencia'])): ?>
                                          <?php
                                          $ruta = APP_URL . "app/files/reportes/" . $e['Evidencia'];
                                          $esImagen = preg_match('/\.(jpg|jpeg|png|gif)$/i', $e['Evidencia']);
                                          $esVideo = preg_match('/\.(mp4|webm|ogg)$/i', $e['Evidencia']);
                                          ?>
                                          <?php if ($esImagen): ?>
                                              <img src="<?php echo $ruta; ?>" style="max-width: 150px;">
                                          <?php elseif ($esVideo): ?>
                                              <video controls style="max-width: 200px;">
                                                  <source src="<?php echo $ruta; ?>">
                                                  Tu navegador no soporta videos.
                                              </video>
                                          <?php else: ?>
                                              <a href="<?php echo $ruta; ?>" target="_blank">Ver archivo</a>
                                          <?php endif; ?>
                                      <?php else: ?>
                                          <?php echo ($e['Tipo'] === 'reporte') ? 'Sin archivo' : '-'; ?>
                                      <?php endif; ?>
                                  </td>
                              </tr>
                          <?php endforeach; ?>
                      </tbody>
                  </table>
              </div>
            <?php else: ?>
              <p class="has-text-grey">No hay notas ni reportes registrados.</p>
            <?php endif; ?>
        </div>
      </div>
    </div>
    <script>
      function toggleNotasReportes(){ document.getElementById('bloqueNotasReportes').classList.toggle('is-hidden'); }
    </script>

    <!-- ACCIONES -->
    <div class="box pastel-black" style="padding: 15px; border-radius: 8px;">
      <div class="info-box" style="background-color: white; color: black; padding: 15px; border-radius: 6px;">
        
        <h2 class="subtitle has-text-weight-bold">
            <i class="fa-solid fa-list-alt"></i> ACCIONES
            <button class="button is-small ml-3" onclick="toggleAcciones()">
                <span class="icon"><i class="fas fa-eye"></i></span>
                <span>Ver/Ocultar</span>
            </button>
        </h2>

        <div id="bloqueAcciones" class="level-left mt-8">
          <div class="level-item">
            <?php
              $_SESSION['factura_idods']  = (int)$ods['Idods'];
              $_SESSION['factura_nombre'] = $cliente['Nombre'] ?? '';
              $_SESSION['factura_correo'] = $cliente['correo'] ?? ($cliente['email'] ?? '');
            ?>
              <form method="post" action="<?= APP_URL ?>invoiceNew/">
                <input type="hidden" name="prefill_idods"  value="<?= (int)$ods['Idods'] ?>">
                <input type="hidden" name="prefill_nombre" value="<?= htmlspecialchars($cliente['Nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="prefill_correo" value="<?= htmlspecialchars($cliente['Email'] ?? ($cliente['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <button class="button is-link is-rounded btn-back mb-2" type="submit">
                  <i class="fas fa-file-invoice-dollar"></i>&nbsp; Realizar Factura
                </button>
              </form>
          </div>

            <?php
              $idods = (int)($ods['Idods'] ?? 0);
              $estado_actual_raw = (string)($ods['Status'] ?? '');

              $remove_accents = function(string $s): string {
                $map = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','ñ'=>'n','Ñ'=>'N'];
                return strtr($s, $map);
              };
              $norm = function($s) use ($remove_accents){
                $s = trim((string)$s);
                $s = preg_replace('/\x{00A0}/u', ' ', $s);
                $s = preg_replace('/\s+/', ' ', $s);
                $s = $remove_accents($s);
                return strtoupper($s);
              };
              $pretty = function($s){ return ucwords(mb_strtolower(trim((string)$s),'UTF-8')); };
              $estado_clave = $norm($estado_actual_raw);

              if ($estado_clave === '' && $idods > 0) {
                $stmt = $pdo->prepare("SELECT Status FROM ods WHERE Idods=:id LIMIT 1");
                $stmt->execute([':id'=>$idods]);
                $estado_actual_raw = (string)($stmt->fetchColumn() ?? '');
                $estado_clave = $norm($estado_actual_raw);
              }

              $transiciones = [
                'RECEPCION'   => ['RECEPCION','DIAGNOSTICO','REPARACION','CANCELADO'],
                'DIAGNOSTICO' => ['DIAGNOSTICO','PRESUPUESTO','AUTORIZACION','REPARACION','STANDBY','CANCELADO'],
                'PRESUPUESTO' => ['PRESUPUESTO','DIAGNOSTICO','STANDBY','AUTORIZACION','CANCELADO'],
                'STANDBY'     => ['STANDBY','AUTORIZACION','CANCELADO'],
                'AUTORIZACION'=> ['AUTORIZACION','PRESUPUESTO','REPARACION','CANCELADO'],
                'REPARACION'  => ['REPARACION','REFACCIONES','STANDBY','LISTOE','CANCELADO'],
                'REFACCIONES' => ['REFACCIONES','REPARACION','STANDBY','CANCELADO'],
                'LISTOE'      => ['LISTOE','REPARACION','ENTREGADO','ALMACEN','CANCELADO'],
                'ENTREGADO'   => ['ENTREGADO','SEGUIMIENTO'],
                'SEGUIMIENTO' => [],
                'ALMACEN'     => [],
                'DBAJA'  => ['DBAJA','SEGUIMIENTO'],
                'CANCELADO'   => []
              ];

              $opciones = array_key_exists($estado_clave,$transiciones) ? $transiciones[$estado_clave] : [];
              $es_final = empty($opciones);
            ?>

          <div class="level-item" style="margin-left: 60px;">
            <div class="control">
              <label class="label"><strong>Status actual:</strong></label>
              <div class="select ml-2" >
                <select id="status_select" name="Status" data-idods="<?= $idods ?>"
                        <?= $es_final ? 'disabled' : '' ?> required>
                  <?php foreach ($opciones as $opt): ?>
                    <option value="<?= htmlspecialchars($pretty($opt)) ?>">
                      <?= htmlspecialchars($pretty($opt)) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <?php if ($es_final): ?>
                <p class="help is-danger">Estado final.</p>
              <?php endif; ?>
            </div>
          </div>
          
          <div class="level-item" style="margin-left: 60px;">
            <?php
              // Lista de técnicos (SIN $this en vista)
              $consulta_tecnicos = "
                SELECT Idasesor, Nombre
                FROM personal
                WHERE Puesto = 'TECNICO' 
                  OR Puesto LIKE '%TECNIC%' 
                  OR Puesto LIKE '%tecnico%'
                  OR Puesto LIKE '%JEFE DE PRODUCCION%'
                ORDER BY Nombre
              ";
              try {
                $rs_t = mainModel::ejecutarConsulta($consulta_tecnicos);
                $tecnicos = $rs_t ? $rs_t->fetchAll(PDO::FETCH_ASSOC) : [];
              } catch (Throwable $e) {
                $tecnicos = [];
                error_log('Error al cargar técnicos: '.$e->getMessage());
              }

              // opciones del select
              $opciones_tecnicos = '';
              if (!empty($tecnicos)) {
                foreach ($tecnicos as $tec) {
                  $sel = ((string)($ods['IdTecnico'] ?? '') === (string)$tec['Idasesor']) ? ' selected' : '';
                  $opciones_tecnicos .= sprintf(
                    '<option value="%s"%s>%s</option>',
                    htmlspecialchars($tec['Idasesor'], ENT_QUOTES, 'UTF-8'),
                    $sel,
                    htmlspecialchars($tec['Nombre'], ENT_QUOTES, 'UTF-8')
                  );
                }
              } elseif (!empty($row['NombreTecnico']) && !empty($ods['IdTecnico'])) {
                // fallback: si no hay lista, al menos el actual
                $opciones_tecnicos .= sprintf(
                  '<option value="%s" selected>%s</option>',
                  htmlspecialchars($ods['IdTecnico'], ENT_QUOTES, 'UTF-8'),
                  htmlspecialchars($row['NombreTecnico'], ENT_QUOTES, 'UTF-8')
                );
              }
            ?>

            <div class="control">
              <label class="label"><strong>Técnico asignado:</strong></label>
              <div class="select ml-2 is-rounded">
                <select id="tecnico_select_<?= $idods ?>"
                        name="Tecnico"
                        class="tecnico_dropdown"
                        data-idods="<?= $idods ?>"
                        <?= $es_final ? 'disabled' : '' ?>
                        required
                        onchange="actualizar_tecnico(this.dataset.idods, this.value)">
                  <option value=""><?= htmlspecialchars('Sin asignar', ENT_QUOTES, 'UTF-8') ?></option>
                  <?= $opciones_tecnicos ?>
                </select>
              </div>

              <?php if ($es_final): ?>
                <p class="help is-danger">Bloqueado por estado final.</p>
              <?php else: ?>
                <p class="help">Selecciona el técnico que tendrá la ODS.</p>
              <?php endif; ?>
            </div>
          </div>

          <div class="level-item">
            <a href="/VENTAS3/odsPrint.php?id=<?= urlencode($ods['Idods']) ?>&auto=1" target="_blank" class="button is-link" style="margin-left: 60px;">
              Imprimir
            </a>
          </div>
  
          <?php
          $sql = "
              SELECT 
                  ods.*, 
                  clientes.Nombre AS NombreCliente,
                  clientes.Numero,
                  clientes.Email,
                  clientes.Colonia,
                  personal.Nombre AS NombreAsesor,
                  p2.Nombre AS NombreTecnico
              FROM ods
              INNER JOIN clientes ON ods.Idcliente = clientes.Idcliente
              INNER JOIN personal ON ods.Idasesor = personal.Idasesor
              INNER JOIN personal p2 ON ods.IdTecnico = p2.Idasesor
              WHERE ods.Idods = $Idods
              ";

              $consulta = mainModel::ejecutarConsulta($sql);
              $result = $consulta;
              $row = $result->fetch(PDO::FETCH_ASSOC);
                
                    // Obtén el número de teléfono del cliente
              $numeroCliente = $row['Numero'];

              // Crear el enlace de WhatsApp con el número del cliente
              $whatsappLink = "https://wa.me/$numeroCliente?text=Hola, nos comunicamos de KASCOM ";
              ?>
              <head>
                  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
              </head>
            <div class="level-item" style="display: flex;">
                  <a href="<?php echo $whatsappLink; ?>" target="_blank">
                      <i class="fab fa-whatsapp" style="font-size: 50px; color: #25D366; margin-left: 60px;"></i>
                  </a>
            </div>
    

        </div>
      </div>
    </div>

    <script>
      function toggleAcciones(){ document.getElementById("bloqueAcciones").classList.toggle("is-hidden"); }
    </script>

  </div>
</div>
<!-- ====== CIERRA EL WRAPPER DEL NUEVO TEMA ====== -->


<!-- ====== MODALES (FUERA DEL WRAPPER PARA NO ALTERAR SU ESTILO) ====== -->

<!-- ESTRUCTURA MODAL REPORTE -->
<div class="modal" id="modalReporte">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">Nuevo Reporte Técnico</p>
      <button class="delete" aria-label="close" id="cerrarModalReporte"></button>
    </header>
    <section class="modal-card-body">
      <form id="formNuevoReporte" class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/reporteTecAjax.php" method="POST" enctype="multipart/form-data">
        
        <div class="field">
            <label class="label">#ODS</label>
            <div class="control">
                <input class="input" type="text" name="Idods" value="<?php echo isset($ods['Idods']) ? htmlspecialchars($ods['Idods']) : ''; ?>" readonly>
            </div>
        </div>

        <div class="field">
            <label class="label">Problema ODS</label>
            <div class="control">
                <input class="input" type="text" name="Problema" value="<?php echo isset($ods['Problema']) ? htmlspecialchars($ods['Problema']) : ''; ?>" readonly>
            </div>
        </div>

        <div class="field">
            <label class="label">Técnico</label>
            <div class="control">
                <input class="input" type="text" name="Tecnico" value="<?php echo $_SESSION['nombre']; ?>" readonly>
            </div>
        </div>

        <div class="field">
            <label class="label">Reporte</label>
            <div class="control">
                <textarea class="textarea" name="Reporte" required></textarea>
            </div>
        </div>

        <div class="field">
            <label class="label">Evidencia (foto o video)</label>
            <div class="control">
                <input class="input" type="file" name="Evidencia" accept="image/*,video/*">
            </div>
        </div>

        <!-- Checkbox para hacer público -->
        <div class="field">
            <label class="checkbox">
                <input type="checkbox" name="HacerPublico" value="1">
                Hacer público (Enviar al cliente por WhatsApp)
            </label>
        </div>
      <!--  <div class="field">
            <label class="checkbox">
                <input type="checkbox" name="MostrarCliente" value="1">
                Mostrar al cliente en notas
            </label>
        </div> -->
      </form>
    </section>
    <footer class="modal-card-foot">
        <button class="button is-success" type="submit" form="formNuevoReporte">Guardar</button>
        <button class="button" id="cancelarModalReporte">Cancelar</button>
    </footer>
  </div>
</div>

<!-- ESTRUCTURA MODAL NOTA -->
<div class="modal" id="modalNota">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">Nueva Nota al Cliente</p>
      <button class="delete" aria-label="close" id="cerrarModalNota"></button>
    </header>
    <section class="modal-card-body">
      <form id="formNuevaNota" class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/notaAjax.php" method="POST">
        
        <div class="field">
            <label class="label">#ODS</label>
            <div class="control">
                <input class="input" type="text" name="Idods" value="<?php echo isset($ods['Idods']) ? htmlspecialchars($ods['Idods']) : ''; ?>" readonly>
            </div>
        </div>

        <div class="field">
            <label class="label">Problema ODS</label>
            <div class="control">
                <input class="input" type="text" name="Problema" value="<?php echo isset($ods['Problema']) ? htmlspecialchars($ods['Problema']) : ''; ?>" readonly>
            </div>
        </div>

        <div class="field">
            <label class="label">Técnico</label>
            <div class="control">
                <input class="input" type="text" name="Tecnico" value="<?php echo $_SESSION['nombre']; ?>" readonly>
            </div>
        </div>

        <div class="field">
            <label class="label">Nota</label> 
            <div class="control">
                <textarea class="textarea" name="Nota" required></textarea>
            </div>
        </div>

        <!-- Checkbox para hacer público -->
        <div class="field">
            <label class="checkbox">
                <input type="checkbox" name="HacerPublico" value="1">
                Hacer público (Enviar al cliente por WhatsApp)
            </label>
        </div>


      </form>
    </section>
    <footer class="modal-card-foot">
        <button class="button is-success" type="submit" form="formNuevaNota">Guardar</button>
        <button class="button" id="cancelarModalNota">Cancelar</button>
    </footer>
  </div>
</div>

<!-- MODAL REFACCIONES -->
<?php $_SESSION['form_token'] = bin2hex(random_bytes(16)); ?>
<input type="hidden" name="form_token" value="<?=$_SESSION['form_token']?>">

<div class="modal" id="modalRefaccion">
  <div class="modal-background"></div>
  <div class="modal-card">
    <header class="modal-card-head">
      <p class="modal-card-title">Nueva Solicitud Refacciones</p>
      <button class="delete" aria-label="close" id="cerrarModalRefaccion" type="button"></button>
    </header>

    <section class="modal-card-body">
      <?php $_SESSION['form_token'] = bin2hex(random_bytes(16)); ?>

      <form id="formNuevoRefaccion"
            class="FormularioAjax"
            action="<?php echo APP_URL; ?>app/ajax/refaccionAjax.php"
            method="POST"
            enctype="multipart/form-data"
            autocomplete="off">

        <input type="hidden" name="modulo_refaccion" value="registrar">
        <input type="hidden" name="form_token" value="<?=$_SESSION['form_token']?>">

        <div class="field">
          <label class="label">#ODS</label>
          <div class="control">
            <input class="input" type="text" name="IdODS"
                   value="<?php echo isset($ods['Idods']) ? htmlspecialchars($ods['Idods'],ENT_QUOTES,'UTF-8') : ''; ?>"
                   readonly>
          </div>
        </div>

        <div class="field">
          <label class="label">Técnico</label>
          <div class="control">
            <input class="input" type="text" value="<?php echo htmlspecialchars($_SESSION['nombre'] ?? '',ENT_QUOTES,'UTF-8'); ?>" readonly>
            <input type="hidden" name="IdAsesor" value="<?php echo htmlspecialchars($_SESSION['Idasesor'] ?? '',ENT_QUOTES,'UTF-8'); ?>">
          </div>
        </div>

        <div class="field">
          <label class="label">Producto (opcional)</label>
          <div class="control" style="position: relative;">
            <input class="input" type="text" name="producto" id="producto_input" placeholder="Escribe el nombre o código">
            <input type="hidden" name="IdProducto" id="producto_id">
            <div id="sug-prods" class="box"
                 style="position:absolute; z-index:30; width:100%; display:none; max-height:220px; overflow:auto; padding:0;"></div>
          </div>
        </div>

        <div class="field">
          <label class="label">Stock</label>
          <div class="control">
            <input class="input" type="number" name="stock" id="stock_input" readonly>
          </div>
        </div>

        <div class="columns">
          <div class="column">
            <div class="control">
              <label class="label">Nombre Refacción (opcional)</label>
              <input class="input" type="text" name="nombre_refaccion" id="nombre_refaccion">
            </div>
          </div>
        </div>

        <div class="columns">
          <div class="column">
            <div class="control">
              <label class="label">Descripción (requerida)</label>
              <textarea class="textarea" name="descripcion" required></textarea>
            </div>
          </div>
        </div>

        <div class="columns">
          <div class="column">
            <div class="control">
              <label class="label">Muestra texto (opcional)</label>
              <input class="input" type="text" name="muestra_texto">
            </div>
          </div>
          <div class="column">
            <div class="control">
              <label class="label">Muestra foto (opcional)</label>
              <input class="input" type="file" name="muestra_foto" accept="image/*">
            </div>
          </div>
        </div>
      </form>
    </section>

    <footer class="modal-card-foot">
      <button class="button is-success" type="submit" form="formNuevoRefaccion">Guardar</button>
      <button class="button" id="cancelarModalRefaccion" type="button">Cancelar</button>
    </footer>
  </div>
</div>

<!-- Modal de confirmación para agregar seguimiento -->
<div id="seguimientoModal" class="modal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Cambiar a Estado: Seguimiento</p>
            <button class="delete" aria-label="close" onclick="cerrarModalSeguimiento()"></button>
        </header>
        <section class="modal-card-body">
            <h3 class="subtitle is-5">¿Programar seguimiento para esta ODS?</h3>
            <!-- Opciones SÍ/NO -->
            <div class="field">
                <label class="label">¿Agregar fecha de seguimiento?</label>
                <div class="control">
                    <label class="radio">
                        <input type="radio" name="opcionSeguimiento" value="si" id="opcionSi">
                        Sí, programar seguimiento
                    </label>
                    <label class="radio">
                        <input type="radio" name="opcionSeguimiento" value="no" id="opcionNo" checked>
                        No, solo cambiar estado
                    </label>
                </div>
            </div>
            
            <!-- Campo de fecha (inicialmente desactivado) -->
            <div class="field" id="campoFecha" style="display: none;">
                <label class="label">Fecha de Seguimiento</label>
                <div class="control">
                    <input type="date" id="seguimientoFecha" class="input" disabled>
                </div>
                <p class="help">Fecha predeterminada: 1 año a partir de hoy</p>
            </div>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" id="confirmarSeguimiento">Confirmar Cambio</button>
            <button class="button" id="cancelarSeguimiento">Cancelar</button>
        </footer>
    </div>
</div>

<style>
#seguimientoModal {
    display: none;
}
#seguimientoModal.is-active {
    display: flex;
}
</style>

<!-- Cargar jQuery desde un CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ====== JS EXISTENTE (SIN CAMBIOS DE LÓGICA) ====== -->
<script>
document.getElementById("abrirModalReporte").addEventListener("click", function () {
  document.getElementById("modalReporte").classList.add("is-active");
});
document.getElementById("cerrarModalReporte").addEventListener("click", function () {
  document.getElementById("modalReporte").classList.remove("is-active");
});
document.getElementById("cancelarModalReporte").addEventListener("click", function () {
  document.getElementById("modalReporte").classList.remove("is-active");
});
</script>

<script>
document.getElementById("abrirModalNota").addEventListener("click", function () {
  document.getElementById("modalNota").classList.add("is-active");
});
document.getElementById("cerrarModalNota").addEventListener("click", function () {
  document.getElementById("modalNota").classList.remove("is-active");
});
document.getElementById("cancelarModalNota").addEventListener("click", function () {
  document.getElementById("modalNota").classList.remove("is-active");
});
</script>

<script>
document.getElementById("abrirModalRefaccion").addEventListener("click", function () {
  document.getElementById("modalRefaccion").classList.add("is-active");
});
document.getElementById("cerrarModalRefaccion").addEventListener("click", function () {
  document.getElementById("modalRefaccion").classList.remove("is-active");
});
document.getElementById("cancelarModalRefaccion").addEventListener("click", function () {
  document.getElementById("modalRefaccion").classList.remove("is-active");
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const inp        = document.getElementById('producto_input');
  const hid        = document.getElementById('producto_id');
  const sug        = document.getElementById('sug-prods');
  const stockInput = document.getElementById('stock_input');
  const refHidden  = document.getElementById('refaccion_hidden');
  const nombreRef  = document.getElementById('nombre_refaccion');

  if (!inp || !sug) return;

  let timer = null;

  function showSug(items){
    if (!items || !items.length) { sug.style.display = 'none'; sug.innerHTML = ''; return; }
    sug.innerHTML = items.map(it => `
      <div class="p-3 is-clickable suggestion-item"
           data-id="${it.id}"
           data-nombre="${(it.producto || '').replace(/"/g,'&quot;')}"
           data-stock="${it.stock ?? ''}"
           data-caracteristica1="${it.caracteristica1 ?? ''}" 
           data-caracteristica2="${it.caracteristica2 ?? ''}">
        <strong>${it.producto ?? ''}</strong>
        ${it.codigo ? `<small class="has-text-grey"> · ${it.codigo}</small>` : ``}
        ${it.stock !== undefined ? `<small class="tag is-light ml-2">Stock: ${it.stock}</small>` : ``}
        ${it.precio_venta !== undefined ? `<small class="tag is-info is-light ml-2">$${it.precio_venta}</small>` : ``}
        <br>
        ${it.caracteristica1 ? `<small class="tag is-light ml-2"> ${it.caracteristica1}</small>` : ``}
        ${it.caracteristica2 ? `<small class="tag is-light ml-2"> ${it.caracteristica2}</small>` : ``}
        ${it.caracteristica3 ? `<small class="tag is-light ml-2"> ${it.caracteristica3}</small>` : ``}
        ${it.caracteristica4 ? `<small class="tag is-light ml-2"> ${it.caracteristica4}</small>` : ``}
      </div>
      <hr class="m-0">
    `).join('');
    if (sug.lastElementChild && sug.lastElementChild.tagName === 'HR') sug.removeChild(sug.lastElementChild);
    sug.style.display = 'block';
  }

  function hideSug(){ sug.style.display = 'none'; }

  inp.addEventListener('input', () => {
    const q = inp.value.trim();
    if (hid) hid.value = '';
    if (stockInput) stockInput.value = '';
    if (refHidden) refHidden.value = "Vacío";
    if (nombreRef) { nombreRef.value = ''; nombreRef.readOnly = false; }

    clearTimeout(timer);
    if (q.length < 2) { hideSug(); return; }

    timer = setTimeout(async () => {
      try {
        const form = new FormData();
        form.append('modulo_inventario', 'buscar');
        form.append('termino', q);
        window.APP_URL = "<?php echo APP_URL; ?>";
        const res  = await fetch(`${window.APP_URL}app/ajax/inventarioAjax.php`, {
          method: 'POST',
          body: form,
          credentials: 'include'
        });

        const text = await res.text();
        let data = null;
        try { data = JSON.parse(text); } catch {}

        if (data && data.ok && Array.isArray(data.items)) showSug(data.items);
        else hideSug();
      } catch (e) {
        console.error(e); hideSug();
      }
    }, 250);
  });

  sug.addEventListener('click', (e) => {
    const item = e.target.closest('.suggestion-item'); if (!item) return;
    inp.value = item.dataset.nombre || '';
    if (hid) hid.value = item.dataset.id || '';
    if (stockInput) { stockInput.value = item.dataset.stock || ''; stockInput.readOnly = true; }
    if (refHidden) refHidden.value = 1;
    if (nombreRef) nombreRef.readOnly = true;
    hideSug();
  });

  document.addEventListener('click', (e) => { if (!sug.contains(e.target) && e.target !== inp) hideSug(); });
  inp.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideSug(); });
});
</script>

<script>
document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (!form.classList.contains('FormularioAjax')) return;

    e.preventDefault();

    const idODS = form.querySelector('[name="IdODS"]')?.value?.trim?.() ?? '';
    if (!idODS) { alert('El campo "IdODS" es obligatorio.'); return; }

    const idAsesor = form.querySelector('[name="IdAsesor"]')?.value?.trim?.() ?? '';
    if (!idAsesor) { alert('El campo "IdAsesor" es obligatorio.'); return; }

    if (form.dataset.sending === '1') return;
    form.dataset.sending = '1';

    try {
        const resp = await fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            credentials: 'include'
        });

        const data = await resp.json();

        if (data.ok || data.success) {
            alert('Refacción registrada correctamente');
            form.reset();
        } else {
            alert(data.error || 'Hubo un problema');
        }
    } catch (err) {
        console.error(err);
        alert('Error de red al registrar la refacción');
    } finally {
        form.dataset.sending = '0';
    }
});
</script>

<script>
function norm(s) {
  return (s || '')
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/\u00A0/g, ' ')
    .replace(/\s+/g,' ')
    .trim()
    .toUpperCase();
}

// Cambiar status en BD
async function cambiarStatusODS(odsId, nuevoNorm) {
  const form = new FormData();
  form.append('modulo_ods', 'cambiar_status');
  form.append('Idods', odsId);
  form.append('Status', nuevoNorm);

  const resp = await fetch('/VENTAS3/app/ajax/odsAjax.php', {
    method: 'POST',
    body: form,
    credentials: 'include'
  });
  return resp.json();
}

// Enviar correo automático
async function enviarCorreoAuto(odsId, toEmail) {
  const subject = 'Estado de su equipo';
  const message = 'Su equipo con orden #' + odsId + ' está listo para su entrega.';

  const res = await fetch('/VENTAS3/enviar_email.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action:  'enviar_email',
      subject: subject,
      message: message,
      to:      toEmail || 'ri399580@uaeh.edu.mx'
    })
  });

  const text = await res.text();
  try { return JSON.parse(text); }
  catch { console.error('Respuesta no JSON:', text); return { success:false }; }
}

document.addEventListener('DOMContentLoaded', () => {
  const sel = document.getElementById('status_select');
  if (!sel) return;

  sel.addEventListener('change', async () => {
    const odsId     = sel.dataset.idods;
    const toEmail   = sel.dataset.email || 'ri399580@uaeh.edu.mx';
    const nuevoTxt  = sel.value;
    const nuevoNorm = norm(nuevoTxt);

    try {
      const data = await cambiarStatusODS(odsId, nuevoNorm);

      if (data && data.success) {
        const badge = document.getElementById('status_actual_badge');
        if (badge) badge.textContent = nuevoTxt;

        if (nuevoNorm === 'LISTOE') {
          const mail = await enviarCorreoAuto(odsId, toEmail);
          if (mail.success) alert('Correo enviado con éxito para la ODS #' + odsId);
          else alert('Status cambiado, pero el correo no se envió.');
        }
      } else {
        alert((data && (data.msg || data.error)) || 'No se pudo actualizar el status.');
      }
    } catch (err) {
      console.error(err);
      alert('Error de red al actualizar el status.');
    }
  });
});
</script>

<script>
document.getElementById('formNuevoReporte').addEventListener('submit', function(event) {
    event.preventDefault(); // Evitar que el formulario se envíe de la manera tradicional

    var form = new FormData(this); // Capturar los datos del formulario

    // Enviar los datos con AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", this.action, true); // Enviar al mismo archivo, cambiar si es necesario
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText); // Parsear la respuesta JSON

            if (response.success) {
                // Obtener el mensaje de "Reporte" (lo necesitamos para WhatsApp)
                var mensajeReporte = document.querySelector('[name="Reporte"]').value;
                
                // Limpiar los campos del formulario después de guardar
                document.querySelector('[name="Reporte"]').value = ''; // Limpiar el campo Reporte
                document.querySelector('[name="Evidencia"]').value = ''; // Limpiar el campo de evidencia
                document.querySelector('[name="HacerPublico"]').checked = false; // Desmarcar el checkbox

                alert(response.mensaje); // Mostrar mensaje de éxito

                // Opcional: Si "Hacer público" está marcado, abre WhatsApp con el mensaje
                var hacerPublico = document.querySelector('[name="HacerPublico"]:checked');
                if (hacerPublico) {
                    var idOds = document.querySelector('[name="Idods"]').value; // Obtener el Id de ODS
                    var numeroCliente = "<?php echo $ods['Numero']; ?>"; // Número del cliente

                    var enlaceWhatsApp = "https://wa.me/" + numeroCliente + "?text=" + encodeURIComponent("Reporte ODS #" + idOds + ": " + mensajeReporte);
                    window.open(enlaceWhatsApp, '_blank'); // Abrir WhatsApp
                }

                // Recargar la página (se queda en la misma página)
                location.reload(); // Recargar la página sin redirigir
            } else {
                alert(response.mensaje); // Mostrar mensaje de error
            }
        } else {
            alert('Hubo un error al guardar el reporte');
        }
    };
    xhr.send(form); // Enviar los datos
});

document.getElementById('formNuevaNota').addEventListener('submit', function(event) {
    event.preventDefault(); // Evitar que el formulario se envíe de la manera tradicional

    var form = new FormData(this); // Capturar los datos del formulario

    // Enviar los datos con AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", this.action, true); // Enviar al mismo archivo, cambiar si es necesario
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText); // Parsear la respuesta JSON

            if (response.success) {
                // Obtener el mensaje de "Nota" (lo necesitamos para WhatsApp)
                var mensajeNota = document.querySelector('[name="Nota"]').value;

                // Limpiar los campos del formulario después de guardar
                document.querySelector('[name="Nota"]').value = ''; // Limpiar el campo Nota
                document.querySelector('[name="HacerPublico"]').checked = false; // Desmarcar el checkbox

                alert(response.mensaje); // Mostrar mensaje de éxito

                // Opcional: Si "Hacer público" está marcado, abre WhatsApp con el mensaje
                var hacerPublico = document.querySelector('[name="HacerPublico"]:checked');
                if (hacerPublico) {
                    var idOds = document.querySelector('[name="Idods"]').value; // Obtener el Id de ODS
                    var numeroCliente = "<?php echo $ods['Numero']; ?>"; // Número del cliente

                    var enlaceWhatsApp = "https://wa.me/" + numeroCliente + "?text=" + encodeURIComponent("Nota ODS #" + idOds + ": " + mensajeNota);
                    window.open(enlaceWhatsApp, '_blank'); // Abrir WhatsApp
                }

                // Recargar la página (se queda en la misma página)
                location.reload(); // Recargar la página sin redirigir
            } else {
                alert(response.mensaje); // Mostrar mensaje de error
            }
        } else {
            alert('Hubo un error al guardar la nota');
        }
    };
    xhr.send(form); // Enviar los datos
});
</script>

<!-- ====== NUEVO JS PARA SEGUIMIENTO ====== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status_select');
    let odsIdActual = null;
    let nuevoEstadoActual = null;
    
    statusSelect.addEventListener('change', function() {
        odsIdActual = this.dataset.idods;
        nuevoEstadoActual = this.value;
        
        // Verifica si el nuevo estado es 'Seguimiento'
        if (nuevoEstadoActual === 'Seguimiento') {
            abrirModalSeguimiento();
        } else {
            // Si no es Seguimiento, cambiar el estado directamente
            cambiarEstadoDirecto(odsIdActual, nuevoEstadoActual);
        }
    });
    
    // Botones del modal
    const confirmarBtn = document.getElementById('confirmarSeguimiento');
    const cancelarBtn = document.getElementById('cancelarSeguimiento');
    const opcionSi = document.getElementById('opcionSi');
    const opcionNo = document.getElementById('opcionNo');
    const campoFecha = document.getElementById('campoFecha');
    const fechaInput = document.getElementById('seguimientoFecha');
    const resumenAccion = document.getElementById('resumenAccion');
    
    // Eventos para las opciones SÍ/NO
    if (opcionSi) {
        opcionSi.addEventListener('change', function() {
            if (this.checked) {
                campoFecha.style.display = 'block';
                fechaInput.disabled = false;
                fechaInput.required = true;
                actualizarResumen(true, fechaInput.value);
            }
        });
    }
    
    if (opcionNo) {
        opcionNo.addEventListener('change', function() {
            if (this.checked) {
                campoFecha.style.display = 'none';
                fechaInput.disabled = true;
                fechaInput.required = false;
                actualizarResumen(false, null);
            }
        });
    }
    
    // Evento para cambios en la fecha
    if (fechaInput) {
        fechaInput.addEventListener('change', function() {
            if (opcionSi.checked) {
                actualizarResumen(true, this.value);
            }
        });
    }
    
    // Confirmar acción
    if (confirmarBtn) {
        confirmarBtn.addEventListener('click', function() {
            if (opcionSi.checked) {
                // Con fecha de seguimiento
                if (fechaInput && fechaInput.value) {
                    guardarFechaSeguimiento(odsIdActual, fechaInput.value);
                } else {
                    alert('Por favor selecciona una fecha de seguimiento');
                    return;
                }
            } else {
                // Sin fecha de seguimiento - solo cambiar estado
                cambiarEstadoDirecto(odsIdActual, 'Seguimiento');
            }
            cerrarModalSeguimiento();
        });
    }
    
    // Cancelar
    if (cancelarBtn) {
        cancelarBtn.addEventListener('click', function() {
            cerrarModalSeguimiento();
            // Resetear el select al estado anterior
            if (statusSelect) {
                statusSelect.selectedIndex = 0;
            }
        });
    }
});

// Función para actualizar el resumen de la acción
function actualizarResumen(conFecha, fecha) {
    const resumen = document.getElementById('resumenAccion');
    if (conFecha && fecha) {
        const fechaFormateada = new Date(fecha).toLocaleDateString('es-ES');
        resumen.innerHTML = `<strong>Resumen:</strong> El estado cambiará a "Seguimiento" con fecha programada para el <strong>${fechaFormateada}</strong>.`;
    } else {
        resumen.innerHTML = `<strong>Resumen:</strong> El estado cambiará a "Seguimiento" sin fecha programada.`;
    }
}

// Función para cambiar estado sin seguimiento
async function cambiarEstadoDirecto(odsId, nuevoEstado) {
    try {
        const formData = new FormData();
        formData.append('modulo_ods', 'cambiar_status');
        formData.append('Idods', odsId);
        formData.append('Status', nuevoEstado);
        
        const response = await fetch('/VENTAS3/app/ajax/odsAjax.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data && data.success) {
            alert('✅ Estado cambiado correctamente a "' + nuevoEstado + '"');
            location.reload();
        } else {
            throw new Error(data && (data.msg || data.error) || 'No se pudo actualizar el estado');
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error: ' + error.message);
        // Resetear el select si falla
        const statusSelect = document.getElementById('status_select');
        if (statusSelect) {
            statusSelect.selectedIndex = 0;
        }
    }
}

// Función para guardar la fecha de seguimiento
async function guardarFechaSeguimiento(odsId, fecha) {
    try {
        console.log('Guardando seguimiento...', { odsId, fecha });
        
        const formData = new FormData();
        formData.append('odsId', odsId);
        formData.append('fechaSeguimiento', fecha);
        
        const url = window.location.origin + '/VENTAS3/guardarSeguimiento.php';
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const responseText = await response.text();
        console.log('Respuesta del servidor:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Error parseando JSON:', e);
            throw new Error('Respuesta del servidor no válida');
        }
        
        if (data.success) {
            alert('✅ ' + data.message);
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Error del servidor');
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error: ' + error.message);
        
        // Resetear el select si falla
        const statusSelect = document.getElementById('status_select');
        if (statusSelect) {
            statusSelect.selectedIndex = 0;
        }
    }
}

function abrirModalSeguimiento() {
    const modal = document.getElementById('seguimientoModal');
    if (modal) {
        modal.style.display = 'block';
        
        // Resetear opciones
        document.getElementById('opcionNo').checked = true;
        document.getElementById('campoFecha').style.display = 'none';
        
        // Establecer fecha por defecto (1 año después)
        const fecha = new Date();
        fecha.setFullYear(fecha.getFullYear() + 1);
        const fechaFormateada = fecha.toISOString().split('T')[0];
        
        const fechaInput = document.getElementById('seguimientoFecha');
        if (fechaInput) {
            fechaInput.value = fechaFormateada;
            fechaInput.disabled = true;
        }
        
        // Actualizar resumen inicial
        actualizarResumen(false, null);
    }
}

function cerrarModalSeguimiento() {
    const modal = document.getElementById('seguimientoModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Cerrar modal con ESC o click fuera
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalSeguimiento();
        const statusSelect = document.getElementById('status_select');
        if (statusSelect) {
            statusSelect.selectedIndex = 0;
        }
    }
});

document.addEventListener('click', function(event) {
    const modal = document.getElementById('seguimientoModal');
    if (event.target === modal) {
        cerrarModalSeguimiento();
        const statusSelect = document.getElementById('status_select');
        if (statusSelect) {
            statusSelect.selectedIndex = 0;
        }
    }
});
</script>
