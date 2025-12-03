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
    p2.Nombre AS NombreTecnico,
    p3.Nombre AS NombreEntrego  
FROM ods
INNER JOIN clientes ON ods.Idcliente = clientes.Idcliente
INNER JOIN personal ON ods.Idasesor = personal.Idasesor
INNER JOIN personal p2 ON ods.IdTecnico = p2.Idasesor
LEFT JOIN  personal p3 ON ods.Entrego = p3.Idasesor 
WHERE ods.Idods = $Idods
";

$consulta = mainModel::ejecutarConsulta($sql);
$ods = $consulta->fetch();
if (!$ods) {
    echo "<p class='has-text-centered has-text-danger'>La ODS no existe.</p>";
    return;
}
// (Consulta para la tabla 'movimiento')
$sql_pagos = "SELECT * FROM movimientos WHERE Idods = $Idods ORDER BY Fecha DESC, Hora DESC";
$pagos = mainModel::ejecutarConsulta($sql_pagos)->fetchAll();

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

<style>
  :root {
    --bg-body: #f0f2f5;
    --card-bg: #ffffff;
    --text-main: #1f2937;
    --text-muted: #6b7280;
    --primary: #4f46e5;
    --radius: 16px;
    --shadow: 0 4px 20px rgba(0,0,0,0.03);
  }

  .odsview_modern {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    background-color: var(--bg-body);
    color: var(--text-main);
    padding-bottom: 2rem;
  }

  /* Header Principal (Estado) */
  .status-header {
    border-radius: var(--radius);
    padding: 2rem;
    margin-bottom: 1.5rem;
    color: white;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .status-header h1 { font-size: 2rem; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
  .status-header span { font-weight: 300; opacity: 0.9; font-size: 1.2rem; }
  
  /* Colores de Estado (Gradientes Suaves) */
  .estado-recepcion   { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); }
  .estado-diagnostico { background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%); }
  .estado-presupuesto { background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%); }
  .estado-autorizacion{ background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); }
  .estado-standby     { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); }
  .estado-reparacion  { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
  .estado-refacciones { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
  .estado-listoe      { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
  .estado-almacen     { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); }
  .estado-entregado   { background: linear-gradient(135deg, #facc15 0%, #eab308 100%); color: #444 !important; }
  .estado-seguimiento { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
  .estado-default     { background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%); }

  /* Tarjetas Modernas */
  .aesthetic-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    height: 100%;
    border: 1px solid rgba(0,0,0,0.02);
    transition: transform 0.2s ease;
    position: relative;
    overflow: hidden;
  }
  .aesthetic-card:hover { transform: translateY(-2px); }

  /* Acentos de color laterales en las tarjetas */
  .accent-blue   { border-left: 5px solid #3b82f6; }
  .accent-purple { border-left: 5px solid #8b5cf6; }
  .accent-green  { border-left: 5px solid #10b981; }
  .accent-orange { border-left: 5px solid #f97316; }
  .accent-pink   { border-left: 5px solid #ec4899; }
  .accent-cyan   { border-left: 5px solid #06b6d4; }

  /* Títulos de Sección */
  .card-header-custom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid #f3f4f6;
  }
  .card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-main);
    display: flex;
    align-items: center;
    gap: 0.8rem;
  }
  .icon-box {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
  }
  
  /* Colores de iconos */
  .ib-blue   { background: #eff6ff; color: #3b82f6; }
  .ib-purple { background: #f5f3ff; color: #8b5cf6; }
  .ib-green  { background: #ecfdf5; color: #10b981; }
  .ib-orange { background: #fff7ed; color: #f97316; }
  .ib-pink   { background: #fdf2f8; color: #ec4899; }

  /* Grid de Datos */
  .data-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 1rem;
  }
  .data-item { margin-bottom: 0.5rem; }
  .data-label {
    display: block;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    font-weight: 600;
    margin-bottom: 0.2rem;
  }
  .data-value {
    font-size: 0.95rem;
    color: var(--text-main);
    font-weight: 500;
    word-break: break-word;
  }
  .data-value.highlight { color: #ef4444; font-weight: 700; }

  /* Botones pequeños de visibilidad */
  .btn-toggle {
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 0.85rem;
  }
  .btn-toggle:hover { color: var(--primary); }

  /* Tablas Aesthetic */
  .table-modern {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }
  .table-modern th {
    background: #f9fafb;
    font-weight: 600;
    color: var(--text-muted);
    padding: 12px;
    font-size: 0.85rem;
    text-transform: uppercase;
    border-bottom: 2px solid #e5e7eb;
  }
  .table-modern td {
    padding: 12px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.95rem;
  }
  .table-modern tr:last-child td { border-bottom: none; }
  .table-modern tr:hover td { background: #f9fafb; }

  /* Estilo para los mensajes tipo chat (Notas) */
  .chat-bubble {
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 10px;
    position: relative;
    border: 1px solid rgba(0,0,0,0.03);
  }
  .chat-report { background-color: #eff6ff; border-left: 4px solid #3b82f6; } /* Azul muy suave */
  .chat-note   { background-color: #fff1f2; border-left: 4px solid #f43f5e; } /* Rojo muy suave */
  
  .chat-meta {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 5px;
    display: flex; justify-content: space-between;
  }
</style>

<style>
/* ========================================== */
/* ESTILOS TIMELINE JERÁRQUICO (FULL WIDTH)   */
/* ========================================== */
.timeline-scroll {
    display: flex;
    align-items: flex-start;
    gap: 0;
    overflow-x: auto;
    /* CAMBIO AQUÍ: Bajamos el tercer valor (abajo) de 40px a 5px */
    padding: 20px 20px 5px 20px; 
    position: relative;
}

/* LÍNEA HORIZONTAL CENTRAL */
.timeline-line {
    position: absolute;
    /* ALINEACIÓN PERFECTA: 20px (padding) + 22px (mitad círculo) - 1.5px (mitad línea) */
    top: 45px; 
    left: 40px;
    right: 40px;
    height: 3px;
    background: #8f9196ff;
    z-index: 0;
}

/* GRUPOS (COLUMNAS) */
.timeline-step-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
    flex: 1; /* Crecen equitativamente */
    min-width: 80px; /* Ancho mínimo para no aplastarse en móvil */
}

/* --- PADRE --- */
.step-node.main-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    transition: transform 0.3s;
}
.step-node.main-node.is-active { transform: scale(1.15); z-index: 2; }

.step-circle {
    width: 44px; height: 44px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    color: #9ca3af; /* Gris inactivo */
    background: #f3f4f6; 
    border: 4px solid #fff; /* Borde blanco para cortar la línea */
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    margin-bottom: 6px;
    box-sizing: content-box;
}

.step-label {
    font-size: 0.65rem;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    text-align: center;
    background: rgba(255,255,255,0.9);
    padding: 2px 4px;
    border-radius: 4px;
}

/* --- CONECTOR VERTICAL --- */
.vertical-line {
    width: 2px;
    height: 15px;
    background: #d1d5db;
    margin: 2px auto;
}

/* --- HIJO --- */
.step-node.sub-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: -1px; 
    transition: transform 0.3s;
}
.step-node.sub-node.is-active { transform: scale(1.1); }

.step-circle-small {
    width: 30px; height: 30px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem;
    color: #9ca3af;
    background: #fff;
    border: 2px solid #e5e7eb;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 3px;
}

.step-label-small {
    font-size: 0.6rem;
    color: #9ca3af;
    font-weight: 600;
    text-transform: uppercase;
}

/* --- COLORES ACTIVOS (FORZAR CON !IMPORTANT) --- */

/* Texto negro al activarse */
.step-node.main-node.is-active .step-label,
.step-node.sub-node.is-active .step-label-small {
    color: #111827 !important;
    font-weight: 800 !important;
}

/* Regla para aplicar gradientes */
[class*="estado-"]:not(.estado-inactivo):not(.estado-inactivo-sub) {
    color: white !important;
    border-color: white !important;
}

/* Definición de tus colores */
.estado-recepcion    { background-image: linear-gradient(135deg, #f97316 0%, #ea580c 100%) !important; }
.estado-diagnostico  { background-image: linear-gradient(135deg, #eab308 0%, #ca8a04 100%) !important; }
.estado-presupuesto  { background-image: linear-gradient(135deg, #a855f7 0%, #9333ea 100%) !important; }
.estado-autorizacion { background-image: linear-gradient(135deg, #22c55e 0%, #16a34a 100%) !important; }
.estado-standby      { background-image: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%) !important; }
.estado-reparacion   { background-image: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important; }
.estado-refacciones  { background-image: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%) !important; }
.estado-listoe       { background-image: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; }
.estado-almacen      { background-image: linear-gradient(135deg, #ec4899 0%, #db2777 100%) !important; }
.estado-seguimiento  { background-image: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; }
.estado-entregado    { background-image: linear-gradient(135deg, #facc15 0%, #eab308 100%) !important; color: #374151 !important; }

/* Sombras al activar */
.step-node.main-node.is-active .step-circle { box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
.step-node.sub-node.is-active .step-circle-small { box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
</style>

<div class="odsview_modern">
  <div class="container is-fluid mb-1 mt-1">
    <div class="aesthetic-card pt-4 pb-1 px-2" style="overflow: hidden;">
      <div class="timeline-scroll">
            <?php
                // 1. DEFINIR JERARQUÍA (Padre => Hijo)
                $pasos_jerarquia = [
                    'recepcion' => [
                        'icon' => 'fa-clipboard-check', 'label' => 'Recepción',
                        'sub' => null 
                    ],
                    'diagnostico' => [
                        'icon' => 'fa-stethoscope', 'label' => 'Diagnóstico',
                        'sub' => ['key' => 'presupuesto', 'icon' => 'fa-file-invoice-dollar', 'label' => 'Presupuesto']
                    ],
                    'autorizacion' => [
                        'icon' => 'fa-check-circle', 'label' => 'Autorización',
                        'sub' => null
                    ],
                    'reparacion' => [
                        'icon' => 'fa-screwdriver', 'label' => 'Reparación',
                        'sub' => ['key' => 'refacciones', 'icon' => 'fa-tools', 'label' => 'Refacciones']
                    ],
                    'listoe' => [
                        'icon' => 'fa-check-double', 'label' => 'Listo',
                        'sub' => null
                    ],
                    'entregado' => [
                        'icon' => 'fa-handshake', 'label' => 'Entregado',
                        'sub' => ['key' => 'seguimiento', 'icon' => 'fa-calendar-alt', 'label' => 'Seguimiento']
                    ],
                    'almacen' => [
                        'icon' => 'fa-box', 'label' => 'Almacén',
                        'sub' => null
                    ]
                ];

                // 2. NORMALIZAR ESTADO ACTUAL
                $statusRaw = $ods['Status'] ?? '';
                $statusActual = strtolower(str_replace(' ', '', iconv('UTF-8', 'ASCII//TRANSLIT', $statusRaw)));
            ?>

            <div class="timeline-line"></div>

            <?php foreach ($pasos_jerarquia as $keyPadre => $info): ?>
                <?php 
                    $subInfo = $info['sub'];
                    $keyHijo = $subInfo ? $subInfo['key'] : null;

                    // Lógica de activación
                    $esPadreActivo = ($keyPadre === $statusActual);
                    $esHijoActivo = ($keyHijo === $statusActual);

                    // Colores iniciales (Si es activo pone la clase de color, si no gris)
                    $clasePadre = $esPadreActivo ? 'estado-' . $keyPadre : 'estado-inactivo';
                    $claseHijo  = $esHijoActivo  ? 'estado-' . $keyHijo  : 'estado-inactivo-sub';
                ?>

                <div class="timeline-step-group">
                    
                    <div class="step-node main-node <?php echo $esPadreActivo ? 'is-active' : ''; ?>" 
                         data-status="<?php echo $keyPadre; ?>">
                        <div class="step-circle <?php echo $clasePadre; ?>">
                            <i class="fas <?php echo $info['icon']; ?>"></i>
                        </div>
                        <span class="step-label"><?php echo $info['label']; ?></span>
                    </div>

                    <?php if ($subInfo): ?>
                        <div class="vertical-line"></div>
                        <div class="step-node sub-node <?php echo $esHijoActivo ? 'is-active' : ''; ?>"
                             data-status="<?php echo $keyHijo; ?>">
                            <div class="step-circle-small <?php echo $claseHijo; ?>">
                                <i class="fas <?php echo $subInfo['icon']; ?>"></i>
                            </div>
                            <span class="step-label-small"><?php echo $subInfo['label']; ?></span>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

  <div class="container is-fluid mb-4" style="position: sticky; top: 0; z-index: 999; padding-top: 15px; background-color: #f0f2f5;">
      <div class="status-header <?php echo $clase_estado; ?>">
          <div>
            <h1>ODS #<?php echo $ods['Idods']; ?></h1>
          </div>
          <div style="text-align: right; background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 40px; backdrop-filter: blur(5px);">
             <span style="font-weight: 500; letter-spacing: 1px; font-size: 1rem;"><?php echo strtoupper($ods['Status']); ?></span>
          </div>
      </div>
  </div>

  <div class="container is-fluid">
    <div class="columns is-variable is-4">
      <div class="column is-6">
        <div class="aesthetic-card accent-blue">
          <div class="card-header-custom">
            <div class="card-title">
              <div class="icon-box ib-blue"><i class="fas fa-user"></i></div>
              DATOS DEL CLIENTE
            </div>
            <button class="btn-toggle" onclick="toggleCliente()"><i class="fas fa-eye"></i></button>
          </div>

          <div id="infoCliente">
            <div class="data-grid">
               <div class="data-item" style="grid-column: span 2;">
                 <span class="data-label">Nombre Completo</span>
                 <span class="data-value" style="font-size: 1.1rem; font-weight: 700;"><?php echo $ods['NombreCliente']; ?></span>
               </div>
               <?php
                    // 1. Obtenemos el número (asegurándonos de que no sea nulo)
                    $telefono = isset($ods['Numero']) ? $ods['Numero'] : '';

                    // 2. Limpiamos el número: Quitamos espacios, guiones, paréntesis y símbolos
                    // Ejemplo: "(555) 123-4567" se convierte en "5551234567"
                    $numeroLimpio = preg_replace('/[^0-9]/', '', $telefono);

                    // 3. Creamos el enlace. Si el número existe, creamos el link de la API
                    if (!empty($numeroLimpio)) {
                        $whatsappLink = "https://wa.me/" . $numeroLimpio;
                    } else {
                        $whatsappLink = "#"; // Enlace muerto si no hay número
                    }
                ?>
                <div class="data-item">
                    <span class="data-label">Teléfono / WhatsApp</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="data-value"><?php echo $ods['Numero']; ?></span>
                        
                        <?php if (!empty($numeroLimpio)): ?>
                            <a href="<?php echo $whatsappLink; ?>" target="_blank" 
                              style="background:#dcfce7; color:#16a34a; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:50%; text-decoration:none; transition:all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                <i class="fab fa-whatsapp" style="font-size: 26px;"></i>
                            </a>
                        <?php else: ?>
                            <span style="background:#f3f4f6; color:#9ca3af; width:28px; height:28px; display:flex; align-items:center; justify-content:center; border-radius:50%;">
                                <i class="fab fa-whatsapp" style="font-size: 26px;"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

               <div class="data-item">
                 <span class="data-label">Correo Electrónico</span>
                 <span class="data-value"><?php echo $ods['Email']; ?></span>
               </div>

               <div class="data-item" style="grid-column: span 2;">
                 <span class="data-label">Dirección</span>
                 <span class="data-value"><?php echo $ods['Colonia']; ?></span>
               </div>
            </div>
          </div>
        </div>
      </div>
      <script>function toggleCliente(){ document.getElementById("infoCliente").classList.toggle("is-hidden"); }</script>

      <div class="column is-6">
        <div class="aesthetic-card accent-purple">
          <div class="card-header-custom">
            <div class="card-title">
              <div class="icon-box ib-purple"><i class="fas fa-file-alt"></i></div>
              DETALLES DE LA ORDEN
            </div>
            <button class="btn-toggle" onclick="toggleOds()"><i class="fas fa-eye"></i></button>
          </div>

          <div id="infoOds" class="data-grid">
             <div class="data-item">
               <span class="data-label">Fecha Ingreso</span>
               <span class="data-value"><?php $fecha = new DateTime($ods['Fecha']); echo $fecha->format('d/m/Y'); ?></span>
             </div>

             <div class="data-item">
               <span class="data-label">Fecha Estimada Entrega</span>
               <span class="data-value"><?php $fecha = new DateTime($ods['Fechaentrega']); echo $fecha->format('d/m/Y'); ?></span>
             </div>

             <div class="data-item">
               <span class="data-label">ODS Anterior</span>
               <?php if (!empty($ods['Odsanterior'])): ?>
                  <a href="<?php echo APP_URL; ?>odsView/<?php echo $ods['Odsanterior']; ?>/" class="tag is-link is-light">
                    #<?php echo $ods['Odsanterior']; ?>
                  </a>
               <?php else: ?>
                  <span class="data-value">-</span>
               <?php endif; ?>
             </div>

             <div class="data-item">
               <span class="data-label">Respaldo de Datos</span>
               <?php if (strtolower(trim($ods['Respaldo'])) === 'si'): ?>
                  <span class="tag is-danger is-light" style="font-weight:bold;">REQUIERE RESPALDO</span>
               <?php else: ?>
                  <span class="data-value">No solicitado</span>
               <?php endif; ?>
             </div>

             <div class="data-item">
               <span class="data-label">Recibió</span>
               <span class="data-value"><?php echo $ods['NombreAsesor']; ?></span>
             </div>
             <div class="data-item">
               <span class="data-label">Técnico</span>
               <span class="data-value"><?php echo $ods['NombreTecnico']; ?></span>
             </div>
             <div class="data-item">
                  <span class="data-label">Entregó</span>
                  <span class="data-value"><?php echo !empty($ods['NombreEntrego']) ? $ods['NombreEntrego'] : '-'; ?></span>
              </div>

             <div class="data-item" style="grid-column: span 2; background: #f9fafb; padding: 10px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
               <div>
                 <span class="data-label">Pagos a Cuenta</span>
                 <span class="data-value" style="font-size: 1.1rem;">$<?php echo $ods['Cuenta']; ?></span>
               </div>
               <button class="button is-small is-rounded is-white" id="abrirModalPagos" style="box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                  <span class="icon"><i class="fas fa-history"></i></span>
                  <span>Ver Historial</span>
               </button>
             </div>
          </div>
        </div>
      </div>
      <script>function toggleOds(){ document.getElementById("infoOds").classList.toggle("is-hidden"); }</script>
    </div>

    <div class="columns is-variable is-4 mt-2">
      <div class="column is-6">
        <div class="aesthetic-card accent-green">
          <div class="card-header-custom">
             <div class="card-title">
               <div class="icon-box ib-green"><i class="fas fa-laptop"></i></div>
               EQUIPO RECIBIDO
             </div>
             <button class="btn-toggle" onclick="toggleAparato()"><i class="fas fa-eye"></i></button>
          </div>
          
          <div id="infoAparato" class="data-grid">
            <div class="data-item"><span class="data-label">Tipo</span><span class="data-value"><?php echo $ods['Tipo']; ?></span></div>
            <div class="data-item"><span class="data-label">Marca</span><span class="data-value"><?php echo $ods['Marca']; ?></span></div>
            <div class="data-item"><span class="data-label">Modelo</span><span class="data-value"><?php echo $ods['Modelo']; ?></span></div>
            <div class="data-item"><span class="data-label">Color</span><span class="data-value"><?php echo $ods['Color']; ?></span></div>
            <div class="data-item"><span class="data-label">No. Serie</span><span class="data-value"><?php echo $ods['Noserie']; ?></span></div>
            <div class="data-item"><span class="data-label">Contraseña</span><span class="data-value highlight"><?php echo $ods['Contrasena']; ?></span></div>
            <div class="data-item" style="grid-column: span 2;"><span class="data-label">Accesorios</span><span class="data-value"><?php echo $ods['Accesorios']; ?></span></div>
          </div>
        </div>
      </div>
      <script>function toggleAparato(){ document.getElementById("infoAparato").classList.toggle("is-hidden"); }</script>
      
      <div class="column is-6">
        <div class="aesthetic-card accent-orange">
          <div class="card-header-custom">
             <div class="card-title">
               <div class="icon-box ib-orange"><i class="fas fa-stethoscope"></i></div>
               DIAGNÓSTICO INICIAL
             </div>
             <button class="btn-toggle" onclick="toggleDescripcion()"><i class="fas fa-eye"></i></button>
          </div>
          
          <div id="infoDescripcion">
            <div class="mb-4">
              <span class="data-label">Problema Reportado</span>
              <p class="data-value" style="background: #fff7ed; padding: 10px; border-radius: 8px; margin-top: 5px;">
                <?php echo $ods['Problema']; ?>
              </p>
            </div>
            <div>
              <span class="data-label">Inspección Física</span>
              <p class="data-value"><?php echo $ods['Inspeccion']; ?></p>
            </div>
          </div>
        </div>
      </div>
      <script>function toggleDescripcion(){ document.getElementById("infoDescripcion").classList.toggle("is-hidden"); }</script>
    </div>

    <div class="columns is-variable is-4 mt-2">
      <div class="column is-6">
        <div class="aesthetic-card accent-pink">
          <div class="card-header-custom">
            <div class="card-title">
              <div class="icon-box ib-pink"><i class="fas fa-concierge-bell"></i></div>
              SERVICIOS
            </div>
            <button class="btn-toggle" onclick="toggleServicios()"><i class="fas fa-eye"></i></button>
          </div>
          
          <div id="tablaServicios">
            <table class="table-modern">
              <thead><tr><th>Servicio</th><th style="text-align:right">Costo</th></tr></thead>
              <tbody>
                <?php
                  $lista_reparaciones = array_filter(explode(',', $ods['Reparacion']));
                  $lista_costos = array_filter(explode(',', $ods['Costorep']));
                  $max = max(count($lista_reparaciones), count($lista_costos));
                  for ($i = 0; $i < $max; $i++) {
                      $reparacion = trim($lista_reparaciones[$i] ?? '');
                      $costo = trim($lista_costos[$i] ?? '');
                      echo "<tr>
                              <td>{$reparacion}</td>
                              <td style='text-align:right; font-weight:bold;'>\${$costo}</td>
                            </tr>";
                  }
                  if ($max == 0) echo "<tr><td colspan='2' class='has-text-centered has-text-grey-light'>Sin servicios registrados</td></tr>";
                ?>
              </tbody>
            </table>
            <button class="button is-small is-fullwidth mt-3 is-light" id="abrirModalServicios" style="border:1px dashed #db2777; color: #db2777;">
              <i class="fas fa-plus-circle mr-1"></i> Agregar Servicio
            </button>
          </div>
        </div>
      </div>
      <script>function toggleServicios(){ document.getElementById("tablaServicios").classList.toggle("is-hidden"); }</script>

      <div class="column is-6">
        <div class="aesthetic-card accent-cyan">
          <div class="card-header-custom">
             <div class="card-title">
               <div class="icon-box" style="background:#ecfeff; color:#06b6d4;"><i class="fas fa-tools"></i></div>
               REFACCIONES SOLICITADAS
             </div>
             <button class="btn-toggle" onclick="toggleRefacciones()"><i class="fas fa-eye"></i></button>
          </div>
          
          <div id="contenidoRefacciones">
             <?php
              // Reutilizamos tu consulta PHP original
              $sqlRef = "SELECT refacciones.Nombre_refaccion, refacciones.estado, refacciones.fechaRefaccion AS Fecha, personal.Nombre AS NombreODS 
                         FROM refacciones INNER JOIN personal ON refacciones.IdAsesor = personal.Idasesor
                         WHERE refacciones.estado = 'Autorizado' AND refacciones.IdODS = $Idods";
              $refacciones = mainModel::ejecutarConsulta($sqlRef)->fetchAll();
             ?>
             
             <table class="table-modern">
               <thead><tr><th>Refacción</th><th>Estado</th><th>Fecha</th></tr></thead>
               <tbody>
                  <?php if (empty($refacciones)): ?>
                    <tr><td colspan="3" class="has-text-centered has-text-grey-light">No hay refacciones</td></tr>
                  <?php else: ?>
                    <?php foreach ($refacciones as $ref): ?>
                      <tr>
                        <td><?php echo $ref['Nombre_refaccion']; ?></td>
                        <td><span class="tag is-success is-light is-rounded" style="font-size:0.7rem"><?php echo $ref['estado']; ?></span></td>
                        <td style="font-size:0.8rem;"><?php echo $ref['Fecha']; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
               </tbody>
             </table>
             <button class="button is-small is-fullwidth mt-3 is-light" id="abrirModalRefaccion" style="border:1px dashed #0891b2; color: #0891b2;">
               <i class="fas fa-plus-circle mr-1"></i> Solicitar Refacción
             </button>
          </div>
        </div>
      </div>
      <script>function toggleRefacciones(){ document.getElementById("contenidoRefacciones").classList.toggle("is-hidden"); }</script>
    </div>

    <div class="column is-12 p-0 mt-4 mb-4">
        
         <?php
    // 1. Consultas SQL
    $sql_rep = "
        SELECT 
            r.Fecha, 
            r.Hora, 
            p.Nombre AS Tecnico,  
            r.Reporte AS Contenido, 
            r.Evidencia, 
            'reporte' AS Tipo 
        FROM reportetec r
        LEFT JOIN personal p ON r.Tecnico = p.Idasesor
        WHERE r.Idods = $Idods
    ";
    $sql_not = "SELECT Fecha, Hora, Tecnico, Nota AS Contenido, '' AS Evidencia, 'nota' AS Tipo FROM notas WHERE Idods = $Idods";
    
    // 2. Ejecución
    $reportes = mainModel::ejecutarConsulta($sql_rep);
    $notas    = mainModel::ejecutarConsulta($sql_not);
    
    // 3. Fusión y Ordenamiento
    $eventos  = array_merge($reportes->fetchAll(), $notas->fetchAll());
    usort($eventos, function($a, $b) {
        $fechaA = strtotime($a['Fecha'] . ' ' . $a['Hora']);
        $fechaB = strtotime($b['Fecha'] . ' ' . $b['Hora']);
        return $fechaB - $fechaA; // Orden cronológico
    });
    ?>

    <div class="column is-12 p-0 mt-4 mb-5">
      <div class="aesthetic-card" style="border-left: 5px solid #6b7280;">
         
         <div class="card-header-custom">
            <div class="card-title">
              <div class="icon-box" style="background:#f3f4f6; color:#374151;">
                <i class="fas fa-comments"></i>
              </div>
              NOTAS Y REPORTES
            </div>
            <style>
              /* Botón Azul Aesthetic */
              .button.is-blue {
                  background: linear-gradient(135deg, #4386f2ff 0%, #5f8ceeff 100%); /* Gradiente Azul */
                  color: white;
                  border: none;
                  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
                  transition: all 0.2s ease;
              }
              .button.is-blue:hover {
                  transform: translateY(-1px);
                  box-shadow: 0 6px 15px rgba(37, 99, 235, 0.4);
                  color: white;
              }
            </style>
            <div class="buttons are-small">
               <button class="button is-danger" id="abrirModalNota">
                   <i class="fas fa-sticky-note mr-1"></i> Nueva Nota de Contacto
               </button>
               <button class="button is-blue" id="abrirModalReporte">
                   <i class="fas fa-file-medical mr-1"></i> Nuevo Reporte Técnico
               </button>
               <button class="btn-toggle ml-2" onclick="toggleNotasReportes()">
                   <i class="fas fa-eye"></i>
               </button>
            </div>
         </div>

         <div id="bloqueNotasReportes" class="table-container mt-2">
            <?php if (!empty($eventos) && count($eventos) > 0): ?>
              
              <table class="table is-fullwidth" style="font-size: 1.05rem; border-collapse: collapse; border-spacing: 0 5px;">
                  <thead>
                      
                          <th style="color: #6b7280; font-size: 0.85rem; border:none;">Fecha</th>
                          <th style="color: #6b7280; font-size: 0.85rem; border:none;">Hora</th>
                          <th style="color: #6b7280; font-size: 0.85rem; border:none;">Técnico</th>
                          <th style="color: #6b7280; font-size: 0.85rem; border:none;">Descripción</th>
                          <th style="color: #6b7280; font-size: 0.85rem; border:none;">Evidencia</th>
                      
                  </thead>
                  <tbody>
                      <?php foreach ($eventos as $e): ?>
                          <?php 
                             // --- LÓGICA DE COLORES SEGÚN TU PHP ---
                             if ($e['Tipo'] === 'reporte') {
                                 $fondo = '#96d0f9ff'; // Azul muy suave
                                 $borde = '#90caf9'; // Azul borde
                             } else {
                                 $fondo = '#ec7575ff'; // Rojo muy suave
                                 $borde = '#ec7575ff'; // Rojo borde
                             }
                          ?>
                          
                          <tr style="background-color: <?php echo $fondo; ?>;">
                              
                              <td style="vertical-align: middle; border-bottom: 1px solid <?php echo $borde; ?>; border-top: 1px solid <?php echo $borde; ?>; border-left: 4px solid <?php echo $borde; ?>; border-radius: 6px 0 0 6px;">
                                  <?php echo date("d/m/Y", strtotime($e['Fecha'])); ?>
                              </td>
                              
                              <td style="vertical-align: middle; border-bottom: 1px solid <?php echo $borde; ?>; border-top: 1px solid <?php echo $borde; ?>;">
                                  <?php echo $e['Hora']; ?>
                              </td>

                              <td style="vertical-align: middle; font-weight: 600; border-bottom: 1px solid <?php echo $borde; ?>; border-top: 1px solid <?php echo $borde; ?>;">
                                  <?php echo htmlspecialchars($e['Tecnico']); ?>
                              </td>

                              <td style="vertical-align: middle; line-height: 1.5; border-bottom: 1px solid <?php echo $borde; ?>; border-top: 1px solid <?php echo $borde; ?>;">
                                  <span style="font-size: 0.85rem; opacity: 0.7; display: block; margin-bottom: 2px;">
                                  </span>
                                  <?php echo nl2br(htmlspecialchars($e['Contenido'])); ?>
                              </td>

                              <td style="vertical-align: middle; border-bottom: 1px solid <?php echo $borde; ?>; border-top: 1px solid <?php echo $borde; ?>; border-right: 1px solid <?php echo $borde; ?>; border-radius: 0 6px 6px 0;">
                                  <?php if (!empty($e['Evidencia'])): ?>
                                      <?php
                                      $ruta = APP_URL . "app/files/reportes/" . $e['Evidencia'];
                                      $esImagen = preg_match('/\.(jpg|jpeg|png|gif)$/i', $e['Evidencia']);
                                      $esVideo = preg_match('/\.(mp4|webm|ogg)$/i', $e['Evidencia']);
                                      ?>
                                      
                                      <?php if ($esImagen): ?>
                                          <a href="<?php echo $ruta; ?>" target="_blank">
                                            <img src="<?php echo $ruta; ?>" style="height: 50px; width: auto; border-radius: 4px; border: 1px solid rgba(0,0,0,0.1);">
                                          </a>
                                      <?php elseif ($esVideo): ?>
                                          <a href="<?php echo $ruta; ?>" target="_blank" class="button is-small is-link is-light">
                                              <i class="fas fa-video"></i>
                                          </a>
                                      <?php else: ?>
                                          <a href="<?php echo $ruta; ?>" target="_blank" class="button is-small is-light">
                                              <i class="fas fa-paperclip"></i>
                                          </a>
                                      <?php endif; ?>

                                  <?php else: ?>
                                      <span class="has-text-grey-light" style="font-size: 0.8rem;">-</span>
                                  <?php endif; ?>
                              </td>
                          </tr>
                          <tr style="height: 10px; background: transparent;"><td colspan="5" style="border:none; padding:0;"></td></tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>
            <?php else: ?>
              <div class="has-text-centered p-5 has-text-grey-light">
                  <i class="far fa-folder-open fa-2x mb-2"></i><br>
                  <span class="is-size-5">No hay historial registrado</span>
              </div>
            <?php endif; ?>
         </div>
      </div>
    </div>
    <script>function toggleNotasReportes(){ document.getElementById('bloqueNotasReportes').classList.toggle('is-hidden'); }</script>
  </div>
</div>

  <style>
  /* Tarjeta Principal */
  .action-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0,0,0,0.04);
    overflow: hidden;
    margin-bottom: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  /* Cabecera */
  .action-header {
    background: linear-gradient(to right, #f8f9fa, #ffffff);
    padding: 15px 25px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .action-title-text {
    font-size: 1.1rem;
    color: #333;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .icon-circle {
    width: 32px; height: 32px;
    background: #e0e7ff; color: #4f46e5;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
  }

  /* Grid Layout (Reemplaza los margins manuales) */
  .action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    padding: 25px;
    align-items: end;
  }

  /* Controles Modernos */
  .modern-control label {
    display: block;
    font-size: 0.8rem;
    color: #6b7280;
    font-weight: 600;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Select Personalizado */
  .modern-select-wrapper {
    position: relative;
  }
  .modern-select-wrapper select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background-color: #f9fafb;
    color: #1f2937;
    font-size: 0.95rem;
    appearance: none;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  }
  .modern-select-wrapper select:focus {
    outline: none;
    border-color: #6366f1;
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
  }
  .modern-select-wrapper::after {
    content: '\f078'; /* FontAwesome chevron */
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    right: 15px; top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
    font-size: 0.8rem;
  }

  /* Botones Modernos */
  .btn-aesthetic {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    border: none;
    cursor: pointer;
    width: 100%;
    text-decoration: none !important;
    transition: transform 0.1s, box-shadow 0.2s;
  }
  .btn-aesthetic:active { transform: scale(0.98); }

  /* Variantes de Botones */
  .btn-invoice {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
  }
  .btn-invoice:hover { color:white; box-shadow: 0 6px 15px rgba(79, 70, 229, 0.35); }

  .btn-whatsapp {
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
  }
  .btn-whatsapp:hover { color:white; box-shadow: 0 6px 15px rgba(37, 211, 102, 0.35); }

  .btn-print {
    background: #ffffff;
    border: 1px solid #d1d5db;
    color: #374151;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  }
  .btn-print:hover { background: #f3f4f6; border-color: #9ca3af; color: #111; }

  /* Utilidades */
  .status-locked { color: #ef4444; font-size: 0.75rem; margin-top: 5px; display: block; }
</style>

<div class="column is-12 p-0 mt-4 mb-4">
      <div class="aesthetic-card" style="border-left: 5px solid #4f46e5;">
        
        <div class="card-header-custom">
          <div class="card-title">
            <div class="icon-box" style="background:#e0e7ff; color:#4f46e5;">
              <i class="fas fa-sliders-h"></i>
            </div>
            ACCIONES
          </div>
          <button class="btn-toggle" onclick="toggleAcciones()">
            <i class="fas fa-eye"></i>
          </button>
        </div>

        <div id="bloqueAcciones">
          
          <?php
            // --- LÓGICA PHP ORIGINAL ---
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
              'RECEPCION'   => ['RECEPCION','DIAGNOSTICO','PRESUPUESTO','STANDBY','AUTORIZACION','REPARACION','REFACCIONES','LISTOE','ENTREGADO','SEGUIMIENTO','ALMACEN','DBAJA','CANCELADO'],
              'DIAGNOSTICO' => ['DIAGNOSTICO','RECEPCION','PRESUPUESTO','STANDBY','AUTORIZACION','REPARACION','REFACCIONES','LISTOE','ENTREGADO','SEGUIMIENTO','ALMACEN','DBAJA','CANCELADO'],
              'PRESUPUESTO' => ['PRESUPUESTO','DIAGNOSTICO','STANDBY','AUTORIZACION','REPARACION','REFACCIONES','LISTOE','ENTREGADO','ALMACEN','DBAJA','CANCELADO'],
              'STANDBY'     => ['STANDBY','PRESUPUESTO','AUTORIZACION','REPARACION','REFACCIONES','LISTOE','ENTREGADO','DBAJA','CANCELADO'],
              'AUTORIZACION'=> ['AUTORIZACION','STANDBY','PRESUPUESTO','REPARACION','REFACCIONES','LISTOE','ENTREGADO','SEGUIMIENTO','ALMACEN','DBAJA','CANCELADO'],
              'REPARACION'  => ['REPARACION','REFACCIONES','DIAGNOSTICO','PRESUPUESTO','STANDBY','LISTOE','ENTREGADO','ALMACEN','DBAJA','CANCELADO'],
              'REFACCIONES' => ['REFACCIONES','REPARACION','STANDBY','LISTOE','ENTREGADO','ALMACEN','DBAJA','CANCELADO'],
              'LISTOE'      => ['LISTOE','STANDBY','REPARACION','ENTREGADO','ALMACEN','DBAJA','CANCELADO'],
              'ENTREGADO'   => ['ENTREGADO','REPARACION','SEGUIMIENTO'],
              'SEGUIMIENTO' => [],
              'ALMACEN'     => ['REFACCIONES'],
              'DBAJA'       => ['DBAJA','SEGUIMIENTO'],
              'CANCELADO'   => []
            ];

            $opciones = array_key_exists($estado_clave,$transiciones) ? $transiciones[$estado_clave] : [];
            $es_final = empty($opciones);
          ?>

          <div class="data-grid" style="grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap: 1.5rem;">
            
            <div class="data-item">
              <label class="data-label" style="margin-bottom: 8px;">Administrativo</label>
              <?php
                $_SESSION['factura_idods']  = (int)$ods['Idods'];
                $_SESSION['factura_nombre'] = $cliente['Nombre'] ?? '';
                $_SESSION['factura_correo'] = $cliente['correo'] ?? ($cliente['email'] ?? '');
              ?>
              <form method="post" action="<?= APP_URL ?>invoiceNew/">
                <input type="hidden" name="prefill_idods"  value="<?= (int)$ods['Idods'] ?>">
                <input type="hidden" name="prefill_nombre" value="<?= htmlspecialchars($cliente['Nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="prefill_correo" value="<?= htmlspecialchars($cliente['Email'] ?? ($cliente['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                
                <button type="submit" class="button is-fullwidth" 
                        style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: white; border:none; border-radius: 12px; height: 40px; font-size: 0.95rem; font-weight: 700; letter-spacing: 0.3px; transition: all 0.2s; box-shadow: 0 4px 6px rgba(79, 70, 229, 0.15); display: flex; align-items: center; justify-content: center;">
                  <span class="icon is-small mr-2"><i class="fas fa-file-invoice-dollar"></i></span>
                  <span>Generar Factura</span>
                </button>
              </form>
            </div>

            <div class="data-item">
              <label class="data-label" style="margin-bottom: 8px;">Siguiente Estado</label>
              
              <?php
                // 1. DEFINIR EL "FLUJO NATURAL" (Siguiente paso lógico)
                $flujo_natural = [
                    'RECEPCION'    => 'DIAGNOSTICO',
                    'DIAGNOSTICO'  => 'PRESUPUESTO',
                    'PRESUPUESTO'  => 'AUTORIZACION',
                    'AUTORIZACION' => 'REPARACION',
                    'REFACCIONES'  => 'REPARACION',
                    'REPARACION'   => 'LISTOE',
                    'STANDBY'      => 'REPARACION',
                    'LISTOE'       => 'ENTREGADO',
                    'ALMACEN'      => 'LISTOE',
                    'SEGUIMIENTO'  => 'REPARACION'
                ];

                // Detectamos si hay un siguiente paso "obvio" y si es válido
                $siguiente_clave = $flujo_natural[$estado_clave] ?? null;
                $mostrar_atajo = ($siguiente_clave && in_array($siguiente_clave, $opciones));
              ?>

              <?php if ($mostrar_atajo && !$es_final): ?>
                
                <div id="ui_estado_rapido" style="display:flex; gap:10px;">
                    
                    <button type="button" 
                            class="button is-fullwidth <?php echo claseColorEstado($siguiente_clave); ?>"
                            style="color:white; border:none; border-radius:12px; font-weight:700; box-shadow: 0 4px 10px rgba(0,0,0,0.15); transition: transform 0.2s;"
                            onclick="cambiarEstadoDirecto('<?php echo $idods; ?>', '<?php echo $pretty($siguiente_clave); ?>')"
                            onmouseover="this.style.transform='translateY(-2px)'"
                            onmouseout="this.style.transform='translateY(0)'">
                        <span class="icon is-small mr-1"><i class="fas fa-arrow-right"></i></span>
                        <span><?php echo $pretty($siguiente_clave); ?></span>
                    </button>

                    <button type="button" class="button" 
                            style="border-radius:12px; border:1px dashed #db2777; color:#db2777; background:#fff;"
                            onclick="activarModoBrincar()"
                            title="Ver todos los estados disponibles">
                        <i class="fas fa-exchange-alt mr-1"></i> Brincar
                    </button>
                </div>

                <div id="ui_estado_completo" style="display:none; animation: fadeIn 0.3s;">
                    <div class="select is-fullwidth">
                        <select id="status_select" name="Status" data-idods="<?= $idods ?>" required
                                style="border-radius: 12px; border: 1px solid #db2777; background-color: #fff1f2; height: 45px; font-weight: 600; color:#be185d;">
                        <option value="" disabled selected>-- Selecciona un estado --</option>
                        <?php foreach ($opciones as $opt): ?>
                            <option value="<?= htmlspecialchars($pretty($opt)) ?>">
                            <?= htmlspecialchars($pretty($opt)) ?>
                            </option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="has-text-right mt-1">
                        <a href="javascript:void(0)" onclick="desactivarModoBrincar()" style="font-size:0.8rem; color:#6b7280; text-decoration:none;">
                            <i class="fas fa-undo"></i> Cancelar brincar
                        </a>
                    </div>
                </div>

              <?php else: ?>
                
                <div class="select is-fullwidth">
                    <select id="status_select" name="Status" data-idods="<?= $idods ?>" <?= $es_final ? 'disabled' : '' ?> required
                            style="border-radius: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; height: 45px; font-weight: 500;">
                    <?php foreach ($opciones as $opt): ?>
                        <option value="<?= htmlspecialchars($pretty($opt)) ?>" <?= ($estado_clave == $norm($opt)) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pretty($opt)) ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                </div>

              <?php endif; ?>

              <?php if ($es_final): ?>
                 <p class="help is-danger mt-1"><i class="fas fa-lock"></i> ODS Finalizada</p>
              <?php endif; ?>
            </div>
            
            <!-- Funciones para alternar entre el Botón Rápido y la Lista "Brincar" -->
            <script>
              function activarModoBrincar() {
                  document.getElementById('ui_estado_rapido').style.display = 'none';
                  document.getElementById('ui_estado_completo').style.display = 'block';
                  // Abrir el select automáticamente (opcional, depende del navegador)
                  document.getElementById('status_select').focus();
              }

              function desactivarModoBrincar() {
                  document.getElementById('ui_estado_completo').style.display = 'none';
                  document.getElementById('ui_estado_rapido').style.display = 'flex';
              }
            </script>

            <div class="data-item">
              <label class="data-label" style="margin-bottom: 8px;">Técnico Responsable</label>
              <?php
                $consulta_tecnicos = "
                  SELECT Idasesor, Nombre FROM personal
                  WHERE Puesto = 'TECNICO' OR Puesto LIKE '%TECNIC%' OR Puesto LIKE '%JEFE DE PRODUCCION%'
                  ORDER BY Nombre";
                try {
                  $rs_t = mainModel::ejecutarConsulta($consulta_tecnicos);
                  $tecnicos = $rs_t ? $rs_t->fetchAll(PDO::FETCH_ASSOC) : [];
                } catch (Throwable $e) { $tecnicos = []; }

                $opciones_tecnicos = '';
                if (!empty($tecnicos)) {
                  foreach ($tecnicos as $tec) {
                    $sel = ((string)($ods['IdTecnico'] ?? '') === (string)$tec['Idasesor']) ? ' selected' : '';
                    $opciones_tecnicos .= sprintf('<option value="%s"%s>%s</option>', htmlspecialchars($tec['Idasesor']), $sel, htmlspecialchars($tec['Nombre']));
                  }
                } elseif (!empty($row['NombreTecnico']) && !empty($ods['IdTecnico'])) {
                  $opciones_tecnicos .= sprintf('<option value="%s" selected>%s</option>', htmlspecialchars($ods['IdTecnico']), htmlspecialchars($row['NombreTecnico']));
                }
              ?>
              <div class="select is-fullwidth">
                <select id="tecnico_select_<?= $idods ?>" name="Tecnico" data-idods="<?= $idods ?>" <?= $es_final ? 'disabled' : '' ?> required
                        onchange="actualizar_tecnico(this.dataset.idods, this.value)"
                        style="border-radius: 12px; border: 1px solid #e5e7eb; background-color: #f9fafb; height: 45px; font-weight: 500;">
                  <option value="">Sin asignar</option>
                  <?= $opciones_tecnicos ?>
                </select>
              </div>
            </div>

            <div class="data-item">
               <label class="data-label" style="margin-bottom: 8px;">Documentación</label>
               <a href="/VENTAS3/odsPrint.php?id=<?= urlencode($ods['Idods']) ?>&auto=1" target="_blank" class="button is-fullwidth"
                  style="background: white; border: 1px solid #d1d5db; color: #374151; border-radius: 12px; height: 45px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                  <span class="icon"><i class="fas fa-print"></i></span>
                  <span>Imprimir ODS</span>
               </a>
            </div>
          </div> 
        </div>
      </div>
    </div>
    <script>
      function toggleAcciones(){ document.getElementById("bloqueAcciones").classList.toggle("is-hidden"); }
    </script>
<!-- ====== CIERRA EL WRAPPER DEL NUEVO TEMA ====== -->

<!-- ====== MODALES (FUERA DEL WRAPPER PARA NO ALTERAR SU ESTILO) ====== -->
<!-- Modal Reporte y Nota -->
<div class="modal" id="modalReporte">
  <div class="modal-background" style="background-color: rgba(31, 41, 55, 0.6); backdrop-filter: blur(4px);"></div>
  
  <div class="modal-card modal-card-aesthetic" style="width: 100%; max-width: 600px;">
    
    <header class="modal-card-head modal-head-aesthetic">
      <p class="modal-card-title modal-title-aesthetic">
        <span class="icon-box" style="background:#eff6ff; color:#3b82f6; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; vertical-align:middle;">
            <i class="fas fa-file-medical" style="font-size:14px"></i>
        </span>
        Nuevo Reporte Técnico
      </p>
      <button class="delete" aria-label="close" id="cerrarModalReporte"></button>
    </header>

    <section class="modal-card-body modal-body-aesthetic">
      
      <form id="formNuevoReporte" action="<?php echo APP_URL; ?>app/ajax/reporteTecAjax.php" method="POST" enctype="multipart/form-data" autocomplete="off">
        
        <div class="columns is-variable is-3 mb-0">
            <div class="column">
                <div class="field">
                    <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">#ODS</label>
                    <div class="control has-icons-left">
                        <input class="input input-aesthetic" type="text" name="Idods" value="<?php echo isset($ods['Idods']) ? htmlspecialchars($ods['Idods']) : ''; ?>" readonly style="background-color:#f9fafb; font-weight:bold;">
                        <span class="icon is-small is-left" style="height:45px;"><i class="fas fa-hashtag"></i></span>
                    </div>
                </div>
            </div>
            <div class="column">
        <div class="field">
            <label class="data-label" style="color:#6b7280; font-size:0.8rem;">Técnico</label>
            <div class="control has-icons-left">
                
                <input type="hidden" name="Tecnico" value="<?php echo $_SESSION['Idasesor'] ?? $_SESSION['id'] ?? '0'; ?>">
                
                <input class="input input-aesthetic" type="text" value="<?php echo $_SESSION['nombre'] ?? 'Usuario'; ?>" readonly style="background-color:#f9fafb;">
                
                <span class="icon is-small is-left"><i class="fas fa-user-cog"></i></span>
            </div>
        </div>
    </div>
        </div>

        <div class="field mb-3">
            <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Problema Reportado</label>
            <div class="control has-icons-left">
                <input class="input input-aesthetic" type="text" name="Problema" value="<?php echo isset($ods['Problema']) ? htmlspecialchars($ods['Problema']) : ''; ?>" readonly style="background-color:#f9fafb;">
                <span class="icon is-small is-left" style="height:45px;"><i class="fas fa-exclamation-circle"></i></span>
            </div>
        </div>

        <div class="field mb-3">
            <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Detalle del Reporte</label>
            <div class="control">
                <textarea class="textarea" name="Reporte" required 
                          style="border-radius:12px; border:1px solid #e5e7eb; background:#fff; min-height:100px; padding:15px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);" 
                          placeholder="Describe el procedimiento realizado o el diagnóstico..."></textarea>
            </div>
        </div>

        <div class="field mb-4">
            <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Evidencia (Foto/Video)</label>
            <div class="file is-fullwidth is-boxed has-name">
                <label class="file-label">
                    <input class="file-input" type="file" name="Evidencia" accept="image/*,video/*">
                    <span class="file-cta" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                        <span class="file-icon"><i class="fas fa-camera"></i></span>
                        <span class="file-label">Seleccionar archivo...</span>
                    </span>
                </label>
            </div>
        </div>
      </form>
    </section>

    <footer class="modal-card-foot modal-foot-aesthetic">
      <button class="button btn-modal-action btn-gray-outline" id="cancelarModalReporte">Cancelar</button>
      <button class="button btn-modal-action btn-green-gradient" type="submit" form="formNuevoReporte">
        <i class="fas fa-save"></i> Guardar Reporte
      </button>
    </footer>
  </div>
</div>

<div class="modal" id="modalNota">
  <div class="modal-background" style="background-color: rgba(31, 41, 55, 0.6); backdrop-filter: blur(4px);"></div>
  
  <div class="modal-card modal-card-aesthetic" style="width: 100%; max-width: 600px;">
    
    <header class="modal-card-head modal-head-aesthetic">
      <p class="modal-card-title modal-title-aesthetic">
        <span class="icon-box" style="background:#fef2f2; color:#ef4444; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; vertical-align:middle;">
            <i class="fas fa-sticky-note" style="font-size:14px"></i>
        </span>
        Nueva Nota de Contacto
      </p>
      <button class="delete" aria-label="close" id="cerrarModalNota"></button>
    </header>

    <section class="modal-card-body modal-body-aesthetic">
      
      <form id="formNuevaNota" action="<?php echo APP_URL; ?>app/ajax/notaAjax.php" method="POST" autocomplete="off">
        
        <div class="columns is-variable is-3 mb-0">
            <div class="column">
                <div class="field">
                    <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">#ODS</label>
                    <div class="control has-icons-left">
                        <input class="input input-aesthetic" type="text" name="Idods" value="<?php echo isset($ods['Idods']) ? htmlspecialchars($ods['Idods']) : ''; ?>" readonly style="background-color:#f9fafb; font-weight:bold;">
                        <span class="icon is-small is-left" style="height:45px;"><i class="fas fa-hashtag"></i></span>
                    </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                    <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Técnico</label>
                    <div class="control has-icons-left">
                        <input class="input input-aesthetic" type="text" name="Tecnico" value="<?php echo $_SESSION['nombre']; ?>" readonly style="background-color:#f9fafb;">
                        <span class="icon is-small is-left" style="height:45px;"><i class="fas fa-user"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="field mb-3">
            <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Problema ODS</label>
            <div class="control has-icons-left">
                <input class="input input-aesthetic" type="text" name="Problema" value="<?php echo isset($ods['Problema']) ? htmlspecialchars($ods['Problema']) : ''; ?>" readonly style="background-color:#f9fafb;">
                <span class="icon is-small is-left" style="height:45px;"><i class="fas fa-info-circle"></i></span>
            </div>
        </div>

        <div class="field mb-4">
            <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Contenido de la Nota</label>
            <div class="control">
                <textarea class="textarea" name="Nota" required
                          style="border-radius:12px; border:1px solid #e5e7eb; background:#fff; min-height:100px; padding:15px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);" 
                          placeholder="Escribe aquí la nota o comentario para el cliente..."></textarea>
            </div>
        </div>

        <div class="field">
            <div class="control">
                <label class="checkbox" style="background: #fef2f2; padding: 12px; border-radius: 12px; border: 1px solid #fee2e2; display: flex; align-items: center; width: 100%; cursor: pointer;">
                    <input type="checkbox" name="HacerPublico" value="1" style="accent-color: #ef4444; transform: scale(1.2); margin-right: 10px;">
                    <span style="color:#991b1b; font-weight:500;">
                        <i class="fab fa-whatsapp mr-1"></i> Enviar al cliente por WhatsApp
                    </span>
                </label>
            </div>
        </div>

      </form>
    </section>

    <footer class="modal-card-foot modal-foot-aesthetic">
      <button class="button btn-modal-action btn-gray-outline" id="cancelarModalNota">Cancelar</button>
      <button class="button btn-modal-action btn-green-gradient" type="submit" form="formNuevaNota">
        <i class="fas fa-save"></i> Guardar Nota
      </button>
    </footer>
  </div>
</div>

<!-- ESTRUCTURA MODAL REFACCION -->
<?php $_SESSION['form_token'] = bin2hex(random_bytes(16)); ?>
<input type="hidden" name="form_token" value="<?=$_SESSION['form_token']?>">

<div class="modal" id="modalRefaccion">
  <div class="modal-background" style="background-color: rgba(31, 41, 55, 0.6); backdrop-filter: blur(4px);"></div>
  
  <div class="modal-card modal-card-aesthetic" style="width: 100%; max-width: 800px;">
    
    <header class="modal-card-head modal-head-aesthetic">
      <p class="modal-card-title modal-title-aesthetic">
        <span class="icon-box" style="background:#ecfeff; color:#06b6d4; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; vertical-align:middle;">
            <i class="fas fa-tools" style="font-size:14px"></i>
        </span>
        Nueva Solicitud Refacciones
      </p>
      <button class="delete" aria-label="close" id="cerrarModalRefaccion" type="button"></button>
    </header>

    <section class="modal-card-body modal-body-aesthetic">
      <?php $_SESSION['form_token'] = bin2hex(random_bytes(16)); ?>

      <form id="formNuevoRefaccion"
            class="FormularioAjax"
            action="<?php echo APP_URL; ?>app/ajax/refaccionAjax.php"
            method="POST"
            enctype="multipart/form-data"
            autocomplete="off">

        <input type="hidden" name="modulo_refaccion" value="registrar">
        <input type="hidden" name="form_token" value="<?=$_SESSION['form_token']?>">

        <div class="columns is-variable is-4">
            <div class="column">
                <div class="field">
                  <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">#ODS</label>
                  <div class="control has-icons-left">
                    <input class="input input-aesthetic" type="text" name="IdODS"
                           value="<?php echo isset($ods['Idods']) ? htmlspecialchars($ods['Idods'],ENT_QUOTES,'UTF-8') : ''; ?>"
                           readonly style="background-color:#f9fafb; font-weight:bold;">
                    <span class="icon is-small is-left" style="height:45px;"><i class="fas fa-hashtag"></i></span>
                  </div>
                </div>
            </div>
            <div class="column">
                <div class="field">
                  <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Técnico Solicitante</label>
                  <div class="control has-icons-left">
                    <input class="input input-aesthetic" type="text" value="<?php echo htmlspecialchars($_SESSION['nombre'] ?? '',ENT_QUOTES,'UTF-8'); ?>" readonly style="background-color:#f9fafb;">
                    <input type="hidden" name="IdAsesor" value="<?php echo htmlspecialchars($_SESSION['Idasesor'] ?? '',ENT_QUOTES,'UTF-8'); ?>">
                    <span class="icon is-small is-left" style="height:45px;"><i class="fas fa-user"></i></span>
                  </div>
                </div>
            </div>
        </div>

        <div class="field mt-2">
          <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Buscar Producto (Inventario)</label>
          <div class="control has-icons-left" style="position: relative;">
            <input class="input input-aesthetic" type="text" name="producto" id="producto_input" placeholder="Escribe nombre o código para buscar...">
            <span class="icon is-small is-left" style="height:45px;"><i class="fas fa-search"></i></span>
            
            <input type="hidden" name="IdProducto" id="producto_id">
            <div id="sug-prods" class="box"
                 style="position:absolute; z-index:30; width:100%; display:none; max-height:220px; overflow:auto; padding:0; border-radius:12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);"></div>
          </div>
        </div>

        <div class="columns is-variable is-4 mt-2">
             <div class="column is-4">
                <div class="field">
                  <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Stock Disponible</label>
                  <div class="control">
                    <input class="input input-aesthetic" type="number" name="stock" id="stock_input" readonly style="background-color:#f9fafb;">
                  </div>
                </div>
             </div>
             <div class="column is-8">
                <div class="field">
                  <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Nombre Refacción (Manual)</label>
                  <input class="input input-aesthetic" type="text" name="nombre_refaccion" id="nombre_refaccion" placeholder="Si no está en inventario">
                </div>
             </div>
        </div>

        <div class="field mt-2">
           <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Descripción / Detalles (Requerido)</label>
           <div class="control">
             <textarea class="textarea" name="descripcion" required 
                       style="border-radius:12px; border:1px solid #e5e7eb; background:#f9fafb; min-height:80px; padding:15px;" placeholder="Escribe los detalles aquí..."></textarea>
           </div>
        </div>

        <div class="columns is-variable is-4 mt-2">
          <div class="column">
            <div class="field">
              <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Link / Texto Muestra</label>
              <input class="input input-aesthetic" type="text" name="muestra_texto" placeholder="URL o referencia">
            </div>
          </div>
          <div class="column">
            <div class="field">
              <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Foto Muestra</label>
              <div class="file is-fullwidth is-small is-boxed has-name">
                 <label class="file-label">
                    <input class="file-input" type="file" name="muestra_foto" accept="image/*">
                    <span class="file-cta" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px;">
                      <span class="file-icon"><i class="fas fa-upload"></i></span>
                      <span class="file-label">Subir imagen...</span>
                    </span>
                 </label>
              </div>
            </div>
          </div>
        </div>
      </form>
    </section>

    <footer class="modal-card-foot modal-foot-aesthetic">
      <button class="button btn-modal-action btn-gray-outline" id="cancelarModalRefaccion" type="button">Cancelar</button>
      <button class="button btn-modal-action btn-green-gradient" type="submit" form="formNuevoRefaccion">
        <i class="fas fa-save"></i> Guardar Solicitud
      </button>
    </footer>
  </div>
</div>

<div class="modal" id="modalServicios">
  <div class="modal-background" style="background-color: rgba(31, 41, 55, 0.8); backdrop-filter: blur(4px);"></div>
  
  <div class="modal-card modal-card-aesthetic" style="width: 100%; max-width: 950px;">
    
    <header class="modal-card-head modal-head-aesthetic" style="padding-bottom:0; border:none;">
      <div style="width:100%; text-align:center;">
          <p class="modal-card-title modal-title-aesthetic" style="font-size:1.4rem;">
            Gestión de Servicios y Presupuesto
          </p>
          <span class="tag is-link is-light is-rounded has-text-weight-bold">ODS #<?php echo $ods['Idods']; ?></span>
      </div>
      <button class="delete" aria-label="close" id="cerrarModalServicios" style="position:absolute; right:20px; top:20px;"></button>
    </header>

    <section class="modal-card-body modal-body-aesthetic" style="padding-top:10px;">

        <div class="columns is-multiline mb-5" style="border-bottom: 2px dashed #e5e7eb; padding-bottom: 1.5rem;">
            
            <div class="column is-12-mobile is-5-tablet">
                <div class="p-3" style="background: #eff6ff; border-radius: 8px; border: 1px solid #dbeafe; height: 100%;">
                    <label class="label is-small has-text-link mb-1" style="text-transform:uppercase; font-size:0.7rem;">Cliente</label>
                    <h5 class="title is-6 has-text-dark mb-1">
                        <i class="fas fa-user-circle mr-1"></i> 
                        <?php echo isset($ods['NombreCliente']) ? htmlspecialchars($ods['NombreCliente']) : 'Cliente'; ?>
                    </h5> 
                    <p class="is-size-7 has-text-grey">
                        <i class="fas fa-phone-alt mr-1"></i> 
                        <?php echo isset($ods['Numero']) ? htmlspecialchars($ods['Numero']) : '---'; ?>
                    </p>
                </div>
            </div>

            <div class="column is-12-mobile is-7-tablet">
                <div style="border-radius: 8px; background-color: #f9fafb; border: 1px solid #e5e7eb; font-size: 0.85rem; color: #4b5563; padding: 12px; height: 100%; overflow-y: auto;">
                    <?php
                        $equipoHTML  = "<strong style='color:#1f2937;'>EQUIPO:</strong> " . htmlspecialchars($ods['Tipo']) . " " . htmlspecialchars($ods['Marca']) . " " . htmlspecialchars($ods['Modelo']) . "<br>";
                        $equipoHTML .= "<strong style='color:#1f2937;'>SERIE:</strong> " . htmlspecialchars($ods['Noserie']) . " <span class='has-text-grey-light'>|</span> <strong style='color:#1f2937;'>COLOR:</strong> " . htmlspecialchars($ods['Color']) . "<br>";
                        $equipoHTML .= "<div style='margin-top:4px; padding-top:4px; border-top:1px solid #e5e7eb;'>";
                        $equipoHTML .= "<strong style='color:#dc2626;'>INSPECCIÓN TÉCNICA:</strong> " . htmlspecialchars($ods['Inspeccion']) . "<br>";
                        $equipoHTML .= "<strong style='color:#dc2626;'>PROBLEMA REPORTADO:</strong> " . htmlspecialchars($ods['Problema']);
                        $equipoHTML .= "</div>";
                        echo $equipoHTML;
                    ?>
                </div>
            </div>
        </div>

        <?php
            $rawServicios = explode(',', $ods['Reparacion']);
            $rawCostos    = explode(',', $ods['Costorep']);
            
            $pendientes = [];
            $aceptados  = [];
            $totalAceptado = 0;
            $totalPendiente = 0;

            foreach ($rawServicios as $index => $desc) {
                $desc = trim($desc);
                if($desc == "") continue;

                $costo = isset($rawCostos[$index]) ? floatval($rawCostos[$index]) : 0;
                
                // Detectar marca [OK]
                if (strpos($desc, '[OK]') !== false) {
                    $nombreLimpio = str_replace(' [OK]', '', $desc);
                    $aceptados[] = ['i' => $index, 'nombre' => $nombreLimpio, 'costo' => $costo];
                    $totalAceptado += $costo;
                } else {
                    $pendientes[] = ['i' => $index, 'nombre' => $desc, 'costo' => $costo];
                    $totalPendiente += $costo;
                }
            }
        ?>

        <div class="mb-5">
            <div class="level mb-2">
                <div class="level-left">
                    <h4 class="subtitle is-6 has-text-weight-bold has-text-grey mb-0">
                        <i class="fas fa-clipboard-list mr-1"></i> Pendientes / Sugeridos
                    </h4>
                </div>
                <div class="level-right">
                    <button class="button is-danger is-small is-rounded is-light" id="btnBorrarMasivo" onclick="borrarSeleccionados()" disabled>
                        <i class="fas fa-trash-alt mr-1"></i> Borrar Marcados
                    </button>
                </div>
            </div>

            <div class="table-container" style="border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
                <table class="table is-fullwidth is-hoverable mb-0">
                    <thead style="background:#f3f4f6;">
                        <tr>
                            <th style="width:40px; text-align:center;">
                                <input type="checkbox" id="checkAll" onclick="toggleAllChecks(this)">
                            </th>
                            <th style="color:#6b7280; font-size:0.8rem;">Descripción</th>
                            <th style="color:#6b7280; font-size:0.8rem; text-align:center;">Costo</th>
                            <th style="color:#6b7280; font-size:0.8rem; text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pendientes) > 0): ?>
                            <?php foreach ($pendientes as $item): ?>
                            <tr>
                                <td style="text-align:center; vertical-align:middle;">
                                    <input type="checkbox" class="chk-servicio" value="<?php echo $item['i']; ?>" onchange="verificarSeleccion()">
                                </td>
                                <td style="vertical-align:middle;"><?php echo htmlspecialchars($item['nombre']); ?></td>
                                <td style="text-align:center; vertical-align:middle; font-weight:bold;">$<?php echo number_format($item['costo'], 2); ?></td>
                                <td style="text-align:center; vertical-align:middle;">
                                    <button class="button is-small is-success is-light" onclick="cambiarEstadoServicio(<?php echo $ods['Idods']; ?>, <?php echo $item['i']; ?>, 'aceptar')" title="Aceptar este servicio">
                                        <i class="fas fa-check mr-1"></i> Aceptar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="has-text-centered has-text-grey-light p-3">No hay servicios pendientes.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="has-text-right mt-1 is-size-7 has-text-grey">Subtotal Pendiente: $<?php echo number_format($totalPendiente, 2); ?></p>
        </div>

        <div>
            <h4 class="subtitle is-6 has-text-weight-bold has-text-link mb-2">
                <i class="fas fa-check-circle mr-1"></i> Servicios Aceptados (Autorizados)
            </h4>
            <div class="table-container" style="border:1px solid #bfdbfe; border-radius:8px; overflow:hidden;">
                <table class="table is-fullwidth is-hoverable mb-0">
                    <thead style="background:#eff6ff;">
                        <tr>
                            <th style="color:#1e40af; font-size:0.8rem;">Descripción</th>
                            <th style="color:#1e40af; font-size:0.8rem; text-align:center;">Costo</th>
                            <th style="color:#1e40af; font-size:0.8rem; text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($aceptados) > 0): ?>
                            <?php foreach ($aceptados as $item): ?>
                            <tr style="background:#f0fdf4;">
                                <td style="vertical-align:middle;">
                                    <i class="fas fa-check has-text-success mr-2"></i>
                                    <?php echo htmlspecialchars($item['nombre']); ?>
                                </td>
                                <td style="text-align:center; vertical-align:middle; font-weight:bold; color:#15803d;">$<?php echo number_format($item['costo'], 2); ?></td>
                                <td style="text-align:center; vertical-align:middle;">
                                    <button class="button is-small is-warning is-light" onclick="cambiarEstadoServicio(<?php echo $ods['Idods']; ?>, <?php echo $item['i']; ?>, 'rechazar')" title="Mover a pendientes">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="has-text-centered has-text-grey-light p-3">Aún no hay servicios aceptados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:#dcfce7;">
                            <td style="text-align:right; font-weight:bold; color:#166534;">TOTAL ACEPTADO:</td>
                            <td style="text-align:center; font-weight:bold; color:#166534;">$<?php echo number_format($totalAceptado, 2); ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </section>

    <footer class="modal-card-foot modal-foot-aesthetic" style="justify-content: center; gap: 15px;">
      <button class="button btn-modal-action" 
              onclick="document.getElementById('modalServicios').classList.remove('is-active'); document.getElementById('modalAgregarServicio').classList.add('is-active');"
              style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);">
        <i class="fas fa-plus"></i> Nuevo Servicio
      </button>
    </footer>
  </div>
</div>


<!-- 1. MODAL SEGUIMIENTO (ACTUALIZADO)         -->
<!-- ========================================== -->
<div class="modal" id="modalSeguimiento">
  <div class="modal-background" style="background-color: rgba(31, 41, 55, 0.6); backdrop-filter: blur(4px);"></div>
  <div class="modal-card modal-card-aesthetic" style="width: 100%; max-width: 500px;">
    <header class="modal-card-head modal-head-aesthetic">
      <p class="modal-card-title modal-title-aesthetic">
        <span class="icon-box" style="background:#eff6ff; color:#4f46e5; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; vertical-align:middle;">
            <i class="fas fa-calendar-alt" style="font-size:14px"></i>
        </span>
        Seguimiento
      </p>
      <button class="delete" aria-label="close" id="btnCerrarSeguimientoX"></button>
    </header>
    <section class="modal-card-body modal-body-aesthetic">
      
      <!-- Opción de Fecha -->
      <div class="field">
        <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:10px; display:block; text-transform:uppercase;">
            ¿Programar recordatorio?
        </label>
        <div class="control">
          <div style="background: #f9fafb; padding: 15px; border-radius: 12px; border: 1px solid #e5e7eb;">
              <label class="radio" style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer;">
                <input type="radio" name="seg_opcion" value="si" id="seg_opcion_si" style="accent-color: #4f46e5; transform: scale(1.2); margin-right: 10px;">
                <span style="font-weight: 500; color: #1f2937;">Sí, programar fecha</span>
              </label>
              <label class="radio" style="display: flex; align-items: center; cursor: pointer;">
                <input type="radio" name="seg_opcion" value="no" id="seg_opcion_no" checked style="accent-color: #4f46e5; transform: scale(1.2); margin-right: 10px;">
                <span style="font-weight: 500; color: #1f2937;">No, solo cambiar estado</span>
              </label>
          </div>
        </div>
      </div>
      
      <!-- Input Fecha (Oculto por defecto) -->
      <div class="field mt-3" id="seg_bloque_fecha" style="display: none;">
        <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Fecha de Recordatorio</label>
        <div class="control has-icons-left">
          <input type="date" id="seg_fecha_input" class="input input-aesthetic" style="font-weight:bold; color:#4f46e5;">
          <span class="icon is-small is-left" style="height:45px;"><i class="far fa-calendar-check"></i></span>
        </div>
      </div>

      <!-- Nota (Obligatoria) -->
      <div class="field mt-4">
          <label class="data-label" style="color:#ef4444; font-weight:700; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Nota de Seguimiento (Obligatoria)</label>
          <div class="control">
              <textarea id="seg_nota_input" class="textarea" style="border-radius:12px; border:1px solid #e5e7eb; background:#fff; min-height:80px; padding:15px;" placeholder="Escribe el motivo del seguimiento aquí..."></textarea>
          </div>
          <p class="help is-danger" id="seg_error_nota" style="display:none;">* Debes escribir una nota para continuar.</p>
      </div>

    </section>
    <footer class="modal-card-foot modal-foot-aesthetic">
      <button class="button btn-modal-action btn-gray-outline" id="btnCancelarSeguimiento">Cancelar</button>
      <button class="button btn-modal-action" id="btnConfirmarSeguimiento" style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: white; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);">
        <i class="fas fa-check"></i> Confirmar
      </button>
    </footer>
  </div>
</div>

<!-- ========================================== -->
<!-- 2. MODAL ENTREGA (ACTUALIZADO)             -->
<!-- ========================================== -->
<div class="modal" id="modalEntrega">
  <div class="modal-background" style="background-color: rgba(31, 41, 55, 0.6); backdrop-filter: blur(4px);"></div>
  <div class="modal-card modal-card-aesthetic" style="width: 100%; max-width: 500px;">
    <header class="modal-card-head modal-head-aesthetic">
      <p class="modal-card-title modal-title-aesthetic">
        <span class="icon-box" style="background:#fef9c3; color:#ca8a04; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; vertical-align:middle;">
            <i class="fas fa-handshake" style="font-size:14px"></i>
        </span>
        Finalizar Entrega
      </p>
      <button class="delete" aria-label="close" id="btnCerrarEntregaX"></button>
    </header>
    <section class="modal-card-body modal-body-aesthetic">
      
      <!-- Quien entrega -->
      <div class="field">
          <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">¿Quién entrega el equipo?</label>
          <div class="control">
              <div class="select is-fullwidth select-aesthetic">
                  <select id="entrega_personal_id">
                      <option value="" disabled selected>-- Selecciona personal --</option>
                      <?php
                        $sqlPersonal = "SELECT Idasesor, Nombre FROM personal ORDER BY Nombre ASC";
                        $stmtP = app\models\mainModel::conectar()->prepare($sqlPersonal);
                        $stmtP->execute();
                        $listaPersonal = $stmtP->fetchAll();
                        $idUsuarioActual = $_SESSION['Idasesor'] ?? $_SESSION['id'] ?? 0;
                        foreach($listaPersonal as $p) {
                            $selected = ($p['Idasesor'] == $idUsuarioActual) ? 'selected' : '';
                            echo "<option value='{$p['Idasesor']}' {$selected}>{$p['Nombre']}</option>";
                        }
                      ?>
                  </select>
              </div>
          </div>
      </div>

      <!-- Fecha Entrega -->
      <div class="field mt-3">
        <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Fecha de Entrega</label>
        <div class="control has-icons-left">
          <input type="date" id="entrega_fecha" class="input input-aesthetic" value="<?php echo date('Y-m-d'); ?>" style="font-weight:bold; color:#1f2937;">
          <span class="icon is-small is-left" style="height:45px;"><i class="far fa-calendar-check"></i></span>
        </div>
      </div>
      
      <!-- SECCION SEGUIMIENTO DENTRO DE ENTREGA -->
      <div class="field mt-4" style="background: #fdf2f8; padding: 15px; border-radius: 12px; border: 1px dashed #db2777;">
        <label class="checkbox" style="font-weight: 600; color: #be185d; display:flex; align-items:center;">
            <input type="checkbox" id="entrega_check_seguimiento" style="accent-color: #db2777; transform: scale(1.2); margin-right: 10px;">
            ¿Deseas programar un seguimiento futuro?
        </label>
        
        <div id="entrega_bloque_fecha_seg" class="mt-3" style="display:none; animation: fadeIn 0.3s;">
            <label class="data-label" style="color:#be185d; font-size:0.75rem;">Fecha de Recordatorio</label>
            <input type="date" id="entrega_fecha_seg" class="input input-aesthetic" style="border-color:#db2777;">
        </div>
      </div>

      <!-- Nota Obligatoria -->
      <div class="field mt-4">
          <label class="data-label" style="color:#ef4444; font-weight:700; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Nota de Entrega (Obligatoria)</label>
          <div class="control">
              <textarea id="entrega_nota" class="textarea" style="border-radius:12px; border:1px solid #e5e7eb; background:#fff; min-height:80px; padding:15px;" placeholder="Detalles de la entrega, condiciones, etc..."></textarea>
          </div>
          <p class="help is-danger" id="entrega_error_nota" style="display:none;">* Debes escribir una nota para continuar.</p>
      </div>
    </section>
    <footer class="modal-card-foot modal-foot-aesthetic">
      <button class="button btn-modal-action btn-gray-outline" id="btnCancelarEntrega">Cancelar</button>
      <button class="button btn-modal-action" id="btnConfirmarEntrega" style="background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%); color: white; box-shadow: 0 4px 10px rgba(234, 179, 8, 0.3);">
        <i class="fas fa-check-double"></i> Confirmar Entrega
      </button>
    </footer>
  </div>
</div>

<div class="modal" id="modalCambioEstado">
  <div class="modal-background" style="background-color: rgba(31, 41, 55, 0.6); backdrop-filter: blur(4px);"></div>
  <div class="modal-card modal-card-aesthetic" style="width: 100%; max-width: 450px;">
    
    <header class="modal-card-head modal-head-aesthetic">
      <p class="modal-card-title modal-title-aesthetic">
        <span class="icon-box" style="background:#e0e7ff; color:#4f46e5; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; vertical-align:middle;">
            <i class="fas fa-exchange-alt" style="font-size:14px"></i>
        </span>
        Confirmar Cambio
      </p>
      <button class="delete" aria-label="close" id="btnCerrarCambioX"></button>
    </header>

    <section class="modal-card-body modal-body-aesthetic">
      
      <div class="notification is-light is-link mb-4" style="border-radius: 12px; padding: 15px;">
          <p class="is-size-7">Estás a punto de cambiar el estado a: <strong id="lblNuevoEstado" style="text-transform: uppercase;">...</strong></p>
      </div>

      <input type="hidden" id="input_nuevo_estado_target">

      <div class="field">
          <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">¿Quién realiza el cambio?</label>
          <div class="control has-icons-left">
              <div class="select is-fullwidth select-aesthetic">
                  <select id="cambio_personal_id">
                      <option value="" disabled selected>-- Selecciona personal --</option>
                      <?php
                        // Reutilizamos la lógica de PHP para llenar el select
                        if(!isset($listaPersonal)) {
                            $sqlPersonal = "SELECT Idasesor, Nombre FROM personal ORDER BY Nombre ASC";
                            $stmtP = app\models\mainModel::conectar()->prepare($sqlPersonal);
                            $stmtP->execute();
                            $listaPersonal = $stmtP->fetchAll();
                        }
                        $idUsuarioActual = $_SESSION['Idasesor'] ?? $_SESSION['id'] ?? 0;
                        foreach($listaPersonal as $p) {
                            $selected = ($p['Idasesor'] == $idUsuarioActual) ? 'selected' : '';
                            echo "<option value='{$p['Idasesor']}' {$selected}>{$p['Nombre']}</option>";
                        }
                      ?>
                  </select>
              </div>
              <span class="icon is-small is-left" style="height:45px; z-index: 5;">
                <i class="fas fa-user-check"></i>
              </span>
          </div>
      </div>

    </section>

    <footer class="modal-card-foot modal-foot-aesthetic">
      <button class="button btn-modal-action btn-gray-outline" id="btnCancelarCambio">Cancelar</button>
      <button class="button btn-modal-action" id="btnConfirmarCambio" style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: white; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);">
        <i class="fas fa-save"></i> Guardar Cambio
      </button>
    </footer>
  </div>
</div>


<script>
function toggleAllChecks(source) {
    const checkboxes = document.querySelectorAll('.chk-servicio');
    checkboxes.forEach(chk => chk.checked = source.checked);
    verificarSeleccion();
}

function verificarSeleccion() {
    const checkboxes = document.querySelectorAll('.chk-servicio:checked');
    const btn = document.getElementById('btnBorrarMasivo');
    if(btn) btn.disabled = (checkboxes.length === 0);
}

async function borrarSeleccionados() {
    const idOds = "<?php echo $ods['Idods']; ?>";
    const checkboxes = document.querySelectorAll('.chk-servicio:checked');
    if (checkboxes.length === 0) return;
    let indices = [];
    checkboxes.forEach(chk => indices.push(chk.value));
    const formData = new FormData();
    formData.append('modulo_servicio', 'eliminar_masivo');
    formData.append('Idods', idOds);
    formData.append('indices', indices.join(','));

    try {
        const res = await fetch("<?php echo APP_URL; ?>app/ajax/servicioAjax.php", { method: 'POST', body: formData });
        const data = await res.json();
        if(data.success) { location.reload(); } else { alert(data.error || "Error al borrar"); }
    } catch (e) { console.error(e); }
}

async function cambiarEstadoServicio(idOds, index, accion) {
    const formData = new FormData();
    formData.append('modulo_servicio', 'cambiar_estado');
    formData.append('Idods', idOds);
    formData.append('Index', index);
    formData.append('Accion', accion);
    try {
        const res = await fetch("<?php echo APP_URL; ?>app/ajax/servicioAjax.php", { method: 'POST', body: formData });
        const data = await res.json();
        if(data.success) location.reload();
    } catch (e) { console.error(e); }
}
</script>

<div class="modal" id="modalAgregarServicio">
  <div class="modal-background" style="background-color: rgba(31, 41, 55, 0.6); backdrop-filter: blur(4px);"></div>
  
  <div class="modal-card modal-card-aesthetic" style="width: 100%; max-width: 600px;">
    
    <header class="modal-card-head modal-head-aesthetic">
      <p class="modal-card-title modal-title-aesthetic">
        <span class="icon-box" style="background:#eff6ff; color:#2563eb; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; vertical-align:middle;">
            <i class="fas fa-plus" style="font-size:14px"></i>
        </span>
        Agregar Nuevo Servicio
      </p>
      <button class="delete" aria-label="close" onclick="document.getElementById('modalAgregarServicio').classList.remove('is-active'); document.getElementById('modalServicios').classList.add('is-active');"></button>
    </header>

    <section class="modal-card-body modal-body-aesthetic">
      
      <form id="formNuevoServicio" action="<?php echo APP_URL; ?>app/ajax/servicioAjax.php" method="POST" autocomplete="off">
        
        <input type="hidden" name="modulo_servicio" value="registrar">
        
        <div class="field is-hidden">
            <input type="text" name="Idods" value="<?php echo isset($ods['Idods']) ? htmlspecialchars($ods['Idods']) : ''; ?>" readonly>
        </div>

        <div class="field mb-4">
            <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Descripción del Servicio</label>
            <div class="control has-icons-left" style="position: relative;">
                <input class="input input-aesthetic" type="text" name="Descripcion" id="servicio_descripcion_input" placeholder="Escribe para buscar o añadir..." required>
                <span class="icon is-small is-left" style="height:45px;"><i class="fas fa-keyboard"></i></span>
                <div id="sug-servs" class="box" style="position:absolute; z-index:30; width:100%; display:none; max-height:220px; overflow:auto; padding:0; border-radius:12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);"></div>
            </div>
        </div>

        <div class="columns is-variable is-3">
            <div class="column is-4">
                <div class="field">
                    <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Subtotal</label>
                    <div class="control has-icons-left">
                        <input class="input input-aesthetic" type="number" name="Costo" id="servicio_costo_input" placeholder="0.00" step="0.01" min="0" required>
                        <span class="icon is-small is-left" style="height:45px;"><i class="fas fa-dollar-sign"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-4">
                <div class="field">
                    <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">IVA (16%)</label>
                    <input class="input input-aesthetic" type="text" id="servicio_iva_input" readonly style="background-color:#f9fafb; color:#6b7280;">
                </div>
            </div>
            <div class="column is-4">
                <div class="field">
                    <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Total</label>
                    <input class="input input-aesthetic" type="text" id="servicio_total_input" readonly style="background-color:#eff6ff; color:#2563eb; font-weight:bold;">
                </div>
            </div>
        </div>
      </form>
    </section>

    <footer class="modal-card-foot modal-foot-aesthetic">
      <button class="button btn-modal-action btn-gray-outline" type="button"
              onclick="document.getElementById('modalAgregarServicio').classList.remove('is-active'); document.getElementById('modalServicios').classList.add('is-active');">
        Cancelar
      </button>
      <button class="button btn-modal-action" type="submit" form="formNuevoServicio" id="btnGuardarServicio"
              style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);">
        <i class="fas fa-save"></i> Guardar
      </button>
    </footer>
  </div>
</div>


<script>
    document.getElementById("cerrarModalServicios").addEventListener("click", function () {
      document.getElementById("modalServicios").classList.remove("is-active");
    });
</script>

<!-- 
  CAMBIO 4: 
  Añadimos el script para calcular IVA 
-->
<script>
// Función para calcular y mostrar el IVA
function calcularIVA(costoBase) {
    const costo = parseFloat(costoBase) || 0;
    const iva = costo * 0.16;
    const total = costo + iva;

    // Buscamos los campos por su ID y los actualizamos
    const subtotalInput = document.getElementById('servicio_costo_input');
    const ivaInput = document.getElementById('servicio_iva_input');
    const totalInput = document.getElementById('servicio_total_input');

    // Asignamos el costo base al subtotal (si no es el que disparó el evento)
    if (subtotalInput && subtotalInput.value !== costoBase) {
        subtotalInput.value = costo.toFixed(2);
    }
    // Asignamos IVA y Total
    if (ivaInput) {
        ivaInput.value = iva.toFixed(2);
    }
    if (totalInput) {
        totalInput.value = total.toFixed(2);
    }
}

// Añadimos el listener al campo de costo (subtotal)
document.addEventListener('DOMContentLoaded', () => {
    const costoInput = document.getElementById('servicio_costo_input');
    if (costoInput) {
        // Se activa cada vez que el usuario teclea en el campo de costo
        costoInput.addEventListener('input', (e) => {
            calcularIVA(e.target.value);
        });
    }
});
</script>


<script>
// ... existing code ... -->
document.getElementById("cancelarModalServicios").addEventListener("click", function () {
  document.getElementById("modalServicios").classList.remove("is-active");
});
</script>

<!-- JS SILENCIOSO: SIN ALERTA DE LOCALHOST -->
<!-- SCRIPT BLINDADO: EVITA DOBLE ENVÍO -->
<script>
(function() {
    const form = document.getElementById('formNuevoServicio');
    if (!form) return;

    // TRUCO DE LIMPIEZA: Clonamos el formulario para eliminar cualquier evento 'submit' duplicado anterior
    const newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);

    // Ahora agregamos el evento al formulario "limpio"
    newForm.addEventListener('submit', function(e) {
        e.preventDefault(); 

        const btn = document.getElementById('btnGuardarServicio');
        const originalText = btn.innerHTML;
        
        // 1. DESACTIVAR BOTÓN INMEDIATAMENTE
        btn.classList.add('is-loading');
        btn.disabled = true;

        const formData = new FormData(this);

        // Corrección de formato de precio (si aplica)
        let costo = formData.get('Costo'); 
        if (costo) {
            formData.set('Costo', parseFloat(costo).toFixed(2));
        }

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Éxito
            if (data.tipo === 'limpiar' || data.tipo === 'recargar' || data.success || data.ok) {
                
                if(typeof mostrarNotificacion === 'function') {
                    mostrarNotificacion("Servicio agregado correctamente");
                } else {
                    alert("Servicio agregado correctamente");
                }
                
                // Esperar un poco antes de recargar
                setTimeout(() => {
                    location.reload();
                }, 500);

            } else {
                // Error del servidor: Reactivamos el botón para intentar de nuevo
                alert(data.texto || "Ocurrió un error");
                btn.classList.remove('is-loading');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Error de conexión");
            // Error de red: Reactivamos el botón
            btn.classList.remove('is-loading');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });
})();
</script>

<!-- 
  CAMBIO 5: 
  Modificamos el script de autocompletar de PRODUCTOS
  para que sea el de SERVICIOS
-->
<script>
document.addEventListener('DOMContentLoaded', () => {
  // IDs de nuestro modal de SERVICIOS
  const inp        = document.getElementById('servicio_descripcion_input');
  const costoInput = document.getElementById('servicio_costo_input');
  const sug        = document.getElementById('sug-servs');

  if (!inp || !sug || !costoInput) return; // Si no existen los elementos, no hace nada

  let timer = null;

  function showSug(items){
    if (!items || !items.length) { sug.style.display = 'none'; sug.innerHTML = ''; return; }
    
    // Adaptamos el HTML a los campos 'Descripcion' y 'Costo' de la BD
    sug.innerHTML = items.map(it => `
      <div class="p-3 is-clickable suggestion-item"
           data-id="${it.Descripcion}" 
           data-nombre="${(it.Descripcion || '').replace(/"/g,'&quot;')}"
           data-costo="${it.Costo ?? ''}">
        
        <strong>${it.Descripcion ?? ''}</strong>
        ${it.Costo !== undefined ? `<small class="tag is-info is-light ml-2">$${it.Costo}</small>` : ``}
      </div>
      <hr class="m-0">
    `).join('');
    
    if (sug.lastElementChild && sug.lastElementChild.tagName === 'HR') sug.removeChild(sug.lastElementChild);
    sug.style.display = 'block';
  }

  function hideSug(){ sug.style.display = 'none'; }

  inp.addEventListener('input', () => {
    const q = inp.value.trim();
    
    // Cuando escribe, resetea el costo
    costoInput.value = ''; 
    calcularIVA(0); // Limpia los campos de IVA

    clearTimeout(timer);
    if (q.length < 2) { hideSug(); return; } // Busca a partir de 2 letras

    timer = setTimeout(async () => {
      try {
        const form = new FormData();
        // Apuntamos al módulo y AJAX correctos
        form.append('modulo_servicio', 'buscar'); // SINGULAR
        form.append('termino', q);
        
        window.APP_URL = "<?php echo APP_URL; ?>";
        // CAMBIO: Apuntamos a 'servicioAjax.php' (SINGULAR)
        const res  = await fetch(`${window.APP_URL}app/ajax/servicioAjax.php`, {
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
    }, 250); // Pequeña pausa para no saturar la BD
  });

  // Al hacer clic en una sugerencia
  sug.addEventListener('click', (e) => {
    const item = e.target.closest('.suggestion-item'); if (!item) return;
    
    // Rellena los campos 'Descripcion' y 'Costo'
    inp.value = item.dataset.nombre || '';
    
    // Rellena el costo y se asegura que sea modificable
    costoInput.value = item.dataset.costo || '';
    costoInput.readOnly = false; // Nos aseguramos que sea editable
    
    // LLama a la función de calcular IVA
    calcularIVA(item.dataset.costo);

    hideSug();
  });

  document.addEventListener('click', (e) => { if (!sug.contains(e.target) && e.target !== inp) hideSug(); });
  inp.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideSug(); });
});
</script>

<!-- ESTRUCTURA MODAL SEGUIMIENTO -->
<div id="seguimientoModal" class="modal">
  <div class="modal-background" style="background-color: rgba(31, 41, 55, 0.6); backdrop-filter: blur(4px);"></div>
  
  <div class="modal-card modal-card-aesthetic" style="width: 100%; max-width: 500px;">
    
    <header class="modal-card-head modal-head-aesthetic">
      <p class="modal-card-title modal-title-aesthetic">
        <span class="icon-box" style="background:#eff6ff; color:#4f46e5; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; vertical-align:middle;">
            <i class="fas fa-calendar-check" style="font-size:14px"></i>
        </span>
        Seguimiento
      </p>
      <button class="delete" aria-label="close" onclick="cerrarModalSeguimiento()"></button>
    </header>

    <section class="modal-card-body modal-body-aesthetic">
      
      <div class="has-text-centered mb-5">
         <h3 class="subtitle is-6 has-text-weight-semibold has-text-grey">
           ¿Deseas programar un recordatorio futuro para esta ODS?
         </h3>
      </div>

      <div class="field">
        <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:10px; display:block; text-transform:uppercase;">
            Selecciona una opción
        </label>
        <div class="control">
          <div style="background: #f9fafb; padding: 15px; border-radius: 12px; border: 1px solid #e5e7eb;">
              
              <label class="radio" style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer;">
                <input type="radio" name="opcionSeguimiento" value="si" id="opcionSi" style="accent-color: #4f46e5; transform: scale(1.2); margin-right: 10px;">
                <span style="font-weight: 500; color: #1f2937;">Sí, programar fecha</span>
              </label>
              
              <label class="radio" style="display: flex; align-items: center; cursor: pointer;">
                <input type="radio" name="opcionSeguimiento" value="no" id="opcionNo" checked style="accent-color: #4f46e5; transform: scale(1.2); margin-right: 10px;">
                <span style="font-weight: 500; color: #1f2937;">No, solo cambiar estado</span>
              </label>

          </div>
        </div>
      </div>
      
      <div class="field mt-4" id="campoFecha" style="display: none; animation: fadeIn 0.3s ease-in-out;">
        <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">
            Fecha de Recordatorio
        </label>
        <div class="control has-icons-left">
          <input type="date" id="seguimientoFecha" class="input input-aesthetic" disabled style="font-weight:bold; color:#4f46e5;">
          <span class="icon is-small is-left" style="height:45px;">
            <i class="far fa-calendar-alt"></i>
          </span>
        </div>
        <p class="help has-text-grey-light mt-2" style="font-size: 0.8rem;">
            <i class="fas fa-info-circle mr-1"></i> Se sugiere 1 año a partir de hoy
        </p>
      </div>

    </section>

    <footer class="modal-card-foot modal-foot-aesthetic">
      <button class="button btn-modal-action btn-gray-outline" id="cancelarSeguimiento">
        Cancelar
      </button>
      <button class="button btn-modal-action btn-green-gradient" id="confirmarSeguimiento">
        <i class="fas fa-check"></i> Confirmar
      </button>
    </footer>

  </div>
</div>

<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

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
document.getElementById("abrirModalServicios").addEventListener("click", function () {
  document.getElementById("modalServicios").classList.add("is-active");
});
document.getElementById("cancelarModalServicios").addEventListener("click", function () {
  document.getElementById("modalServicios").classList.remove("is-active");
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
// Abrir modal
document.getElementById("abrirModalServicios").addEventListener("click", function () {
  document.getElementById("modalServicios").classList.add("is-active");
});

// Cerrar modal (botón X)
document.getElementById("cerrarModalServicios").addEventListener("click", function () {
  document.getElementById("modalServicios").classList.remove("is-active");
});

// Cerrar modal (botón Cancelar)
document.getElementById("cancelarModalServicios").addEventListener("click", function () {
  document.getElementById("modalServicios").classList.remove("is-active");
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
//document.addEventListener('submit', async (e) => {
  document.getElementById('formNuevoRefaccion').addEventListener('submit', async (e) => {
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
// --- NUEVO REPORTE TÉCNICO ---
document.getElementById('formNuevoReporte').addEventListener('submit', function(event) {
    event.preventDefault(); 

    var form = new FormData(this);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", this.action, true);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);

                if (response.success) {
                    // 1. CERRAR MODAL
                    document.getElementById("modalReporte").classList.remove("is-active");

                    // 2. MOSTRAR NOTIFICACIÓN (En lugar de alert)
                    if(typeof mostrarNotificacion === 'function') {
                        mostrarNotificacion(response.mensaje || "Reporte guardado");
                    }

                    // 3. WHATSAPP (Tu lógica original)
                    var mensajeReporte = document.querySelector('[name="Reporte"]').value;
                    var hacerPublico = document.querySelector('[name="HacerPublico"]:checked');
                    
                    if (hacerPublico) {
                        var idOds = document.querySelector('[name="Idods"]').value;
                        var numeroCliente = "<?php echo $ods['Numero']; ?>"; 
                        var enlaceWhatsApp = "https://wa.me/" + numeroCliente + "?text=" + encodeURIComponent("Reporte ODS #" + idOds + ": " + mensajeReporte);
                        window.open(enlaceWhatsApp, '_blank');
                    }

                    // 4. LIMPIAR Y RECARGAR
                    document.querySelector('[name="Reporte"]').value = '';
                    document.querySelector('[name="Evidencia"]').value = '';
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000); 

                } else {
                    // Error controlado
                    if(typeof mostrarNotificacion === 'function') {
                        mostrarNotificacion(response.mensaje, 'error');
                    } else {
                        alert(response.mensaje);
                    }
                }
            } catch (e) {
                console.error("Error JSON", e);
            }
        } else {
            alert('Error de conexión al guardar');
        }
    };
    xhr.send(form);
});

// --- NUEVA NOTA ---
document.getElementById('formNuevaNota').addEventListener('submit', function(event) {
    event.preventDefault(); 

    var form = new FormData(this);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", this.action, true);

    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);

                if (response.success) {
                    // 1. CERRAR MODAL
                    document.getElementById("modalNota").classList.remove("is-active");

                    // 2. NOTIFICACIÓN
                    if(typeof mostrarNotificacion === 'function') {
                        mostrarNotificacion(response.mensaje || "Nota guardada");
                    }

                    // 3. WHATSAPP
                    var mensajeNota = document.querySelector('[name="Nota"]').value;
                    var hacerPublico = document.querySelector('[name="HacerPublico"]:checked');
                    
                    if (hacerPublico) {
                        var idOds = document.querySelector('[name="Idods"]').value;
                        var numeroCliente = "<?php echo $ods['Numero']; ?>"; 
                        var enlaceWhatsApp = "https://wa.me/" + numeroCliente + "?text=" + encodeURIComponent("Nota ODS #" + idOds + ": " + mensajeNota);
                        window.open(enlaceWhatsApp, '_blank');
                    }

                    // 4. LIMPIAR Y RECARGAR
                    document.querySelector('[name="Nota"]').value = '';
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1000);

                } else {
                    if(typeof mostrarNotificacion === 'function') {
                        mostrarNotificacion(response.mensaje, 'error');
                    } else {
                        alert(response.mensaje);
                    }
                }
            } catch (e) {
                console.error("Error JSON", e);
            }
        } else {
            alert('Error de conexión al guardar');
        }
    };
    xhr.send(form);
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

  // Función para mapear Estados a Clases CSS (Copiamos la lógica de PHP a JS)
      function obtenerClaseEstado(status) {
          // Normalizamos quitando acentos y espacios, y a minúsculas
          let s = status.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/\s/g, '');
          
          const mapa = {
              'recepcion': 'estado-recepcion',
              'diagnostico': 'estado-diagnostico',
              'presupuesto': 'estado-presupuesto',
              'autorizacion': 'estado-autorizacion',
              'standby': 'estado-standby',
              'reparacion': 'estado-reparacion',
              'refacciones': 'estado-refacciones',
              'listoe': 'estado-listoe',
              'almacen': 'estado-almacen',
              'entregado': 'estado-entregado',
              'seguimiento': 'estado-seguimiento'
          };
          return mapa[s] || 'estado-default';
      }

      function actualizarTimelineVisual(nuevoEstadoTexto) {
          // 1. Normalizar texto (ej: "Listo E" -> "listoe")
          let clave = nuevoEstadoTexto.toLowerCase()
                          .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                          .replace(/\s/g, '');

          // 2. Resetear TODOS los nodos (Padres e Hijos)
          document.querySelectorAll('.step-node').forEach(nodo => {
              nodo.classList.remove('is-active');
              
              // Apagar Padre
              const circle = nodo.querySelector('.step-circle');
              if(circle) circle.className = 'step-circle estado-inactivo';
              
              // Apagar Hijo
              const subCircle = nodo.querySelector('.step-circle-small');
              if(subCircle) subCircle.className = 'step-circle-small estado-inactivo-sub';
          });

          // 3. Buscar y Activar el nodo correcto
          const nodoActivo = document.querySelector(`.step-node[data-status="${clave}"]`);
          
          if (nodoActivo) {
              nodoActivo.classList.add('is-active');
              
              // Si es Padre
              const circle = nodoActivo.querySelector('.step-circle');
              if(circle) {
                  circle.classList.remove('estado-inactivo');
                  circle.classList.add('estado-' + clave);
              }

              // Si es Hijo
              const subCircle = nodoActivo.querySelector('.step-circle-small');
              if(subCircle) {
                  subCircle.classList.remove('estado-inactivo-sub');
                  subCircle.classList.add('estado-' + clave);
              }
              
              // Centrar vista en móvil si es necesario
              nodoActivo.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
          }
      }

    // Estilo para la animación de "nuevo elemento"
    const style = document.createElement('style');
    style.innerHTML = `
      @keyframes highlight {
        from { background-color: #fff; transform: scale(0.98); }
        to { background-color: #96d0f9ff; transform: scale(1); }
      }
    `;
    document.head.appendChild(style);

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

<style>
  /* FIX: Asegurar que los modales tapen el encabezado pegajoso */
.modal {
  z-index: 10000 !important;
}
  /* Quita los estilos por defecto de Bulma para el modal-card y los hace modernos */
  .modal-card-aesthetic {
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255,255,255,0.5);
    overflow: hidden; /* Para que el header respete el borde redondo */
    background: #fff;
  }
  
  /* Header limpio y blanco */
  .modal-head-aesthetic {
    background: #ffffff;
    border-bottom: 1px solid #f3f4f6;
    padding: 20px 25px;
  }
  .modal-title-aesthetic {
    font-weight: 800;
    color: #1f2937;
    font-size: 1.25rem;
    letter-spacing: -0.5px;
  }

  /* Cuerpo con padding */
  .modal-body-aesthetic {
    background-color: #fff;
    padding: 25px;
  }

  /* Footer limpio */
  .modal-foot-aesthetic {
    background: #f9fafb;
    border-top: 1px solid #f3f4f6;
    padding: 15px 25px;
    display: flex;
    justify-content: flex-end; /* Botones a la derecha */
    gap: 10px;
  }

  /* Inputs y Selects Modernos */
  .input-aesthetic, .select-aesthetic select {
    border: 1px solid #e5e7eb;
    background-color: #f9fafb;
    border-radius: 12px;
    padding: 10px 15px;
    height: 45px; /* Más altos para touch */
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
    transition: all 0.2s;
    font-size: 0.95rem;
  }
  .input-aesthetic:focus, .select-aesthetic select:focus {
    border-color: #4f46e5;
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    outline: none;
  }

  /* Botones */
  .btn-modal-action {
    border-radius: 12px;
    font-weight: 600;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    transition: transform 0.1s;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .btn-modal-action:active { transform: scale(0.98); }
  
  .btn-green-gradient {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
  }
  .btn-green-gradient:hover { color: white; opacity: 0.9; }

  .btn-gray-outline {
    background: white;
    border: 1px solid #d1d5db;
    color: #374151;
  }
  .btn-gray-outline:hover { background: #f3f4f6; }
</style>

<div class="modal" id="modalPagos">
  <div class="modal-background" style="background-color: rgba(31, 41, 55, 0.6); backdrop-filter: blur(4px);"></div>
  <div class="modal-card modal-card-aesthetic" style="width: 90%; max-width: 800px;">
    
    <header class="modal-card-head modal-head-aesthetic">
      <p class="modal-card-title modal-title-aesthetic">
        <span class="icon-box" style="background:#ecfdf5; color:#10b981; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; margin-right:10px; vertical-align:middle;">
            <i class="fas fa-money-bill-wave" style="font-size:14px"></i>
        </span>
        Historial de Pagos <span style="font-weight:400; color:#6b7280; font-size:1rem;">(ODS #<?php echo $Idods; ?>)</span>
      </p>
      <button class="delete" aria-label="close" id="cerrarModalPagos"></button>
    </header>

    <section class="modal-card-body modal-body-aesthetic">
      <?php if (empty($pagos)): ?>
        <div class="has-text-centered py-5">
            <div style="background:#f3f4f6; width:60px; height:60px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:10px; color:#9ca3af;">
                <i class="fas fa-receipt fa-2x"></i>
            </div>
            <p class="has-text-grey">No hay movimientos registrados.</p>
        </div>
      <?php else: ?>
        <div class="table-container">
            <table class="table is-fullwidth" style="font-size: 0.95rem; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr style="background-color: #f9fafb;">
                  <th style="color:#6b7280; border:none; border-radius: 8px 0 0 8px; padding:12px;">Fecha</th>
                  <th style="color:#6b7280; border:none; padding:12px;">Tipo</th>
                  <th style="color:#6b7280; border:none; padding:12px;">Cantidad</th>
                  <th style="color:#6b7280; border:none; border-radius: 0 8px 8px 0; padding:12px;">Medio</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagos as $movimiento): ?>
                <tr style="border-bottom: 1px solid #f3f4f6;">
                    <td style="padding: 15px 12px; border-bottom:1px solid #f3f4f6; font-weight:500;">
                        <?php echo htmlspecialchars(date('d/m/Y', strtotime($movimiento['Fecha']))); ?>
                        <small class="has-text-grey-light ml-1"><?php echo htmlspecialchars(date('H:i', strtotime($movimiento['Hora']))); ?></small>
                    </td>
                    <td style="padding: 15px 12px; border-bottom:1px solid #f3f4f6;">
                        <?php 
                           $tipo = htmlspecialchars($movimiento['Tipo']);
                           $colorTag = ($tipo == 'Pago' || $tipo == 'Anticipo') ? 'is-success' : 'is-warning';
                        ?>
                        <span class="tag <?php echo $colorTag; ?> is-light is-rounded"><?php echo $tipo; ?></span>
                    </td>
                    <td style="padding: 15px 12px; border-bottom:1px solid #f3f4f6; font-weight: 700; color: #1f2937;">
                        $<?php echo htmlspecialchars(number_format($movimiento['Cantidad'], 2)); ?>
                    </td>
                    <td style="padding: 15px 12px; border-bottom:1px solid #f3f4f6; color:#4b5563;">
                        <?php
                            $medio_db = $movimiento['Medio'];
                            $medio_texto = match(strval($medio_db)) {
                                '0' => 'Efectivo', '1' => 'Tarjeta', '2' => 'Transferencia', '3' => 'Otro', default => $medio_db 
                            };
                            // Icono según medio
                            $iconoMedio = match(strval($medio_db)) {
                                '0','Efectivo' => '<i class="fas fa-money-bill-wave mr-1"></i>',
                                '1','Tarjeta' => '<i class="far fa-credit-card mr-1"></i>',
                                '2','Transferencia' => '<i class="fas fa-university mr-1"></i>',
                                default => '<i class="fas fa-exchange-alt mr-1"></i>'
                            };
                            echo $iconoMedio . htmlspecialchars($medio_texto);
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        </div>
      <?php endif; ?>
    </section>
    
    <footer class="modal-card-foot modal-foot-aesthetic">
      <button class="button btn-modal-action btn-gray-outline" id="cancelarModalPagos">
        Cerrar
      </button>
      <button class="button btn-modal-action btn-green-gradient" id="abrirModalNuevoPago">
        <i class="fas fa-plus"></i> Nuevo Pago
      </button>
    </footer>
  </div>
</div>

<div class="modal" id="modalNuevoPago">
  <div class="modal-background" style="background-color: rgba(31, 41, 55, 0.6); backdrop-filter: blur(4px);"></div>
  <div class="modal-card modal-card-aesthetic" style="width: 100%; max-width: 500px;"> <header class="modal-card-head modal-head-aesthetic">
      <p class="modal-card-title modal-title-aesthetic">
        Registrar Nuevo Pago
      </p>
      <button class="delete" aria-label="close" id="cerrarModalNuevoPago"></button>
    </header>

    <section class="modal-card-body modal-body-aesthetic">
      
      <form id="formNuevoPago"  action="<?php echo APP_URL; ?>app/ajax/movimientoAjax.php" method="POST" autocomplete="off">
        
        <input type="hidden" name="modulo_movimiento" value="registrar">
        <input type="hidden" name="Idods" value="<?php echo $Idods; ?>">

        <div class="field">
            <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Tipo de Movimiento</label>
            <div class="control">
                <div class="select is-fullwidth select-aesthetic">
                    <select name="Tipo" required>
                        <option value="Pago" selected>Pago (Ingreso)</option>
                        <option value="Anticipo">Anticipo</option>
                        <option value="Devolución">Devolución (Egreso)</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="field mt-4">
            <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Cantidad</label>
            <div class="control has-icons-left">
                <input class="input input-aesthetic" type="number" name="Cantidad" step="0.01" min="0" placeholder="0.00" required style="font-weight:bold; color:#1f2937;">
                <span class="icon is-small is-left" style="height:45px;">
                    <i class="fas fa-dollar-sign"></i>
                </span>
            </div>
        </div>

        <div class="field mt-4 mb-2">
            <label class="data-label" style="color:#6b7280; font-weight:600; font-size:0.8rem; margin-bottom:5px; display:block; text-transform:uppercase;">Medio de Pago</label>
            <div class="control">
                <div class="select is-fullwidth select-aesthetic">
                    <select name="Medio" required>
                        <option value="" selected disabled>Selecciona el medio</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>
        </div>

      </form>
    </section>

    <footer class="modal-card-foot modal-foot-aesthetic">
      <button class="button btn-modal-action btn-gray-outline" id="cancelarModalNuevoPago">
        Cancelar
      </button>
      <button class="button btn-modal-action btn-green-gradient" type="submit" form="formNuevoPago">
        <i class="fas fa-save"></i> Guardar Pago
      </button>
    </footer>
  </div>
</div>
<!-- --- FIN DEL MODAL DE NUEVO PAGO --- -->

<!-- 3. JS PARA MODAL DE NUEVO PAGO -->
<script>
// "Cerebro" del botón "Nuevo Pago"
document.getElementById("abrirModalNuevoPago").addEventListener("click", function () {
  // Cierra el modal de historial
  document.getElementById("modalPagos").classList.remove("is-active");
  // Abre el modal de nuevo pago
  document.getElementById("modalNuevoPago").classList.add("is-active");
});

// "Cerebro" del botón X del formulario
document.getElementById("cerrarModalNuevoPago").addEventListener("click", function () {
  document.getElementById("modalNuevoPago").classList.remove("is-active");
  // Vuelve a abrir el modal de historial
  document.getElementById("modalPagos").classList.add("is-active");
});

// "Cerebro" del botón "Cancelar" del formulario
document.getElementById("cancelarModalNuevoPago").addEventListener("click", function () {
  document.getElementById("modalNuevoPago").classList.remove("is-active");
  // Vuelve a abrir el modal de historial
  document.getElementById("modalPagos").classList.add("is-active");
});

// "Cerebro" para enviar el formulario de pago
document.getElementById('formNuevoPago').addEventListener('submit', function(event) {
  
    event.preventDefault(); 
    var form = new FormData(this); 
    var xhr = new XMLHttpRequest();
    xhr.open("POST", this.action, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText); 
                // Revisa la respuesta del 'movimientoController.php'
                if (response.tipo === 'limpiar' || response.tipo === 'recargar') {
                  //  alert(response.texto || 'Pago agregado con éxito');
                    // Cierra el modal de pago
                    document.getElementById("modalNuevoPago").classList.remove("is-active");
                    // Recarga la página para ver el historial y saldo actualizados
                    location.reload(); 
                } else {
                    alert(response.texto || 'Error: No se pudo agregar el pago');
                }
            } catch (e) {
                alert('Respuesta inesperada del servidor.');
            }
        } else {
            alert('Error de conexión al guardar el pago.');
        }
    };
    xhr.send(form); 
});
</script>
<!-- --- FIN DEL JS --- -->


<!-- --- NUEVO CÓDIGO: JS PARA MODAL DE PAGOS --- -->
<script>
document.getElementById("abrirModalPagos").addEventListener("click", function () {
  document.getElementById("modalPagos").classList.add("is-active");
});

document.getElementById("cerrarModalPagos").addEventListener("click", function () {
  document.getElementById("modalPagos").classList.remove("is-active");
});

document.getElementById("cancelarModalPagos").addEventListener("click", function () {
  document.getElementById("modalPagos").classList.remove("is-active");
});
</script>
<!-- --- FIN DE MODAL PAGOS --- -->

<style>
  #toast-container {
    visibility: hidden;
    min-width: 250px;
    margin-left: -125px;
    background-color: #fff;
    color: #333;
    text-align: center;
    border-radius: 12px;
    padding: 16px;
    position: fixed;
    z-index: 9999;
    left: 50%;
    bottom: 30px;
    font-size: 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border-left: 6px solid #4f46e5; /* Color morado aesthetic */
    transform: translateY(20px);
    opacity: 0;
    transition: all 0.3s ease-in-out;
    font-family: 'Segoe UI', sans-serif;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
  }

  #toast-container.show {
    visibility: visible;
    transform: translateY(0);
    opacity: 1;
  }

  /* Icono de check animado */
  .toast-icon {
    background: #e0e7ff;
    color: #4f46e5;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
  }
</style>

<div id="toast-container">
  <div class="toast-icon"><i class="fas fa-check"></i></div>
  <span id="toast-message">Mensaje aquí</span>
</div>

<script>
  // 1. Función para mostrar el aviso bonito
  function mostrarNotificacion(mensaje, tipo = 'success') {
    const toast = document.getElementById("toast-container");
    const text = document.getElementById("toast-message");
    const icon = toast.querySelector(".toast-icon");
    
    text.innerText = mensaje;

    // Cambiar color si es error
    if (tipo === 'error') {
      toast.style.borderLeftColor = '#ef4444';
      icon.style.backgroundColor = '#fee2e2';
      icon.style.color = '#ef4444';
      icon.innerHTML = '<i class="fas fa-times"></i>';
    } else {
      toast.style.borderLeftColor = '#4f46e5';
      icon.style.backgroundColor = '#e0e7ff';
      icon.style.color = '#4f46e5';
      icon.innerHTML = '<i class="fas fa-check"></i>';
    }

    // Mostrar
    toast.classList.add("show");

    // Ocultar después de 3 segundos
    setTimeout(function() {
      toast.classList.remove("show");
    }, 3000);
  }

  // 2. Función AJAX para actualizar el técnico
  async function actualizar_tecnico(idOds, idTecnico) {
    // Preparamos los datos para enviar
    const formData = new FormData();
    formData.append('modulo_ods', 'asignar_tecnico'); // Asegúrate que tu backend espere este módulo
    formData.append('id_ods', idOds);
    formData.append('id_tecnico', idTecnico);

    try {
      // Hacemos la petición al servidor (ajusta la ruta si es diferente)
      const url = "<?php echo APP_URL; ?>app/ajax/odsAjax.php";
      
      const response = await fetch(url, {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success || data.ok || data.respuesta == 'true') {
        mostrarNotificacion("¡Técnico actualizado correctamente!");
      } else {
        mostrarNotificacion("Error al cambiar técnico: " + (data.msg || data.error), 'error');
      }

    } catch (error) {
      console.error("Error:", error);
      mostrarNotificacion("Técnico actualizado (Sin respuesta del servidor)", 'success');
    }
  }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.title = "#<?php echo $ods['Idods']; ?>";
    });
</script>

<!-- JS para borrar servicio ODS -->
<script>
function borrarServicioODS(idOds, index) {
    if(!confirm("¿Seguro que deseas eliminar este servicio?")) return;

    const formData = new FormData();
    formData.append('modulo_servicio', 'eliminar_ods'); // El caso nuevo que creamos
    formData.append('Idods', idOds);
    formData.append('Index', index);

    // Deshabilitar botones para evitar doble click
    const botones = document.querySelectorAll('.btn-borrar-servicio');
    botones.forEach(b => b.disabled = true);

    fetch("<?php echo APP_URL; ?>app/ajax/servicioAjax.php", {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.tipo === 'limpiar' || data.tipo === 'recargar' || data.success) {
            // Recarga rápida para ver la tabla actualizada
            location.reload();
        } else {
            alert(data.texto || "Error al eliminar");
            botones.forEach(b => b.disabled = false);
        }
    })
    .catch(error => {
        console.error(error);
        alert("Error de conexión");
        botones.forEach(b => b.disabled = false);
    });
}
</script>

<!-- 3. SCRIPT UNIFICADO (BLINDADO)             -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. OBTENER DATOS DE SESIÓN
    const NOMBRE_USUARIO = "<?php echo isset($_SESSION['nombre']) ? ($_SESSION['nombre'] . ' ' . ($_SESSION['apellido'] ?? '')) : 'Usuario'; ?>";
    const statusSelect = document.getElementById('status_select');
    
    // SI NO EXISTE EL SELECT (ODS FINALIZADA), TERMINAMOS
    if (!statusSelect) return;

    let odsIdActual = statusSelect.dataset.idods;
    let estadoOriginal = statusSelect.value; // Para revertir si cancela

    // --------------------------------------------------------
    // A. LISTENERS PRINCIPALES (SELECT Y BOTÓN RÁPIDO)
    // --------------------------------------------------------

    // 1. Cambio en el Select "Brincar"
    // (Usamos un clone para limpiar listeners viejos si recargas via AJAX)
    const newSelect = statusSelect.cloneNode(true);
    statusSelect.parentNode.replaceChild(newSelect, statusSelect);
    
    newSelect.addEventListener('change', function(e) {
        gestionarCambioEstado(this.dataset.idods, this.value, e);
    });

    // 2. Interceptar botones de "Siguiente Estado" (Atajos)
    // Buscamos cualquier botón que llame a cambiarEstadoDirecto en el HTML
    // y lo reescribimos para que pase por nuestra validación de modales.
    const botonesRapidos = document.querySelectorAll('#ui_estado_rapido button[onclick*="cambiarEstadoDirecto"]');
    botonesRapidos.forEach(btn => {
        // Extraemos el estado destino del onclick original
        const match = btn.getAttribute('onclick').match(/'([^']+)'\)$/); 
        if(match && match[1]) {
            const estadoDestino = match[1];
            btn.removeAttribute('onclick'); // Quitamos el inline viejo
            btn.addEventListener('click', function(e) {
                gestionarCambioEstado(odsIdActual, estadoDestino, e);
            });
        }
    });

    // --------------------------------------------------------
    // B. LÓGICA CENTRAL DE CAMBIO
    // --------------------------------------------------------
    function gestionarCambioEstado(idOds, nuevoEstado, evento) {
        const estadoNormalizado = nuevoEstado.trim(); // Ajusta si necesitas .toLowerCase()

        // CASO 1: SEGUIMIENTO
        if (estadoNormalizado === 'Seguimiento') {
            if(evento) { evento.preventDefault(); evento.stopPropagation(); }
            abrirModalSeguimiento();
        } 
        // CASO 2: ENTREGADO
        else if (estadoNormalizado === 'Entregado') {
            if(evento) { evento.preventDefault(); evento.stopPropagation(); }
            abrirModalEntrega();
        } 
        // CASO 3: DIRECTO
        else {
            cambiarEstadoDirecto(idOds, nuevoEstado);
        }
    }

    // --------------------------------------------------------
    // C. FUNCIONES AUXILIARES (AJAX)
    // --------------------------------------------------------
    async function guardarNotaAsync(idOds, texto, prefijo = "NOTA") {
        if (!texto || texto.trim() === "") return false;
        const formData = new FormData();
        formData.append('Idods', idOds);
        formData.append('Tecnico', NOMBRE_USUARIO);
        formData.append('Problema', 'Nota de Sistema');
        formData.append('Nota', prefijo + ": " + texto);
        try {
            await fetch("<?php echo APP_URL; ?>app/ajax/notaAjax.php", { method: 'POST', body: formData });
            return true;
        } catch (e) { console.error("Error nota", e); return false; }
    }

    async function guardarSeguimientoAsync(idOds, fecha) {
        if (!fecha) return false;
        const formData = new FormData();
        formData.append('odsId', idOds);
        formData.append('fechaSeguimiento', fecha);
        try {
            const url = "<?php echo APP_URL; ?>guardarSeguimiento.php"; 
            await fetch(url, { method: 'POST', body: formData });
            return true;
        } catch (e) { console.error("Error seguimiento", e); return false; }
    }

    // --------------------------------------------------------
    // D. MODALES (LÓGICA INTERNA)
    // --------------------------------------------------------
    
    // --- MODAL SEGUIMIENTO ---
    const modalSeg = document.getElementById('modalSeguimiento');
    const cerrarSeg = () => { 
        modalSeg.classList.remove('is-active'); 
        if(newSelect) newSelect.value = estadoOriginal; 
    };
    
    const radioSi = document.getElementById('seg_opcion_si');
    const radioNo = document.getElementById('seg_opcion_no');
    const bloqueFechaSeg = document.getElementById('seg_bloque_fecha');
    
    if(radioSi && radioNo) {
        radioSi.addEventListener('change', () => bloqueFechaSeg.style.display = 'block');
        radioNo.addEventListener('change', () => bloqueFechaSeg.style.display = 'none');
    }

    // Botones Cerrar
    const xSeg = document.getElementById('btnCerrarSeguimientoX');
    if(xSeg) xSeg.onclick = cerrarSeg;
    const cSeg = document.getElementById('btnCancelarSeguimiento');
    if(cSeg) cSeg.onclick = cerrarSeg;
    
    // Botón Confirmar Seguimiento
    const btnConfSeg = document.getElementById('btnConfirmarSeguimiento');
    const newBtnConfSeg = btnConfSeg.cloneNode(true);
    btnConfSeg.parentNode.replaceChild(newBtnConfSeg, btnConfSeg);

    newBtnConfSeg.addEventListener('click', async function() {
        const btn = this;
        const nota = document.getElementById('seg_nota_input').value.trim();
        const quiereFecha = document.getElementById('seg_opcion_si').checked;
        const fecha = document.getElementById('seg_fecha_input').value;

        if (nota === "") {
            document.getElementById('seg_error_nota').style.display = 'block';
            document.getElementById('seg_nota_input').classList.add('is-danger');
            document.getElementById('seg_nota_input').focus();
            return;
        } else {
            document.getElementById('seg_error_nota').style.display = 'none';
            document.getElementById('seg_nota_input').classList.remove('is-danger');
        }

        if (quiereFecha && !fecha) { alert("Selecciona fecha."); return; }

        btn.classList.add('is-loading');
        await guardarNotaAsync(odsIdActual, nota, "SEGUIMIENTO");
        if (quiereFecha) await guardarSeguimientoAsync(odsIdActual, fecha);
        await cambiarEstadoDirecto(odsIdActual, 'Seguimiento');
    });

    // --- MODAL ENTREGA ---
    const modalEnt = document.getElementById('modalEntrega');
    const cerrarEnt = () => { 
        modalEnt.classList.remove('is-active'); 
        if(newSelect) newSelect.value = estadoOriginal; 
    };

    const checkSeg = document.getElementById('entrega_check_seguimiento');
    const bloqueFechaEnt = document.getElementById('entrega_bloque_fecha_seg');
    if(checkSeg) {
        checkSeg.addEventListener('change', function() {
            bloqueFechaEnt.style.display = this.checked ? 'block' : 'none';
            if(this.checked && !document.getElementById('entrega_fecha_seg').value) {
                    const f = new Date(); f.setFullYear(f.getFullYear() + 1);
                    document.getElementById('entrega_fecha_seg').value = f.toISOString().split('T')[0];
            }
        });
    }

    const xEnt = document.getElementById('btnCerrarEntregaX');
    if(xEnt) xEnt.onclick = cerrarEnt;
    const cEnt = document.getElementById('btnCancelarEntrega');
    if(cEnt) cEnt.onclick = cerrarEnt;

    const btnConfEnt = document.getElementById('btnConfirmarEntrega');
    const newBtnConfEnt = btnConfEnt.cloneNode(true);
    btnConfEnt.parentNode.replaceChild(newBtnConfEnt, btnConfEnt);

    newBtnConfEnt.addEventListener('click', async function() {
        const btn = this;
        const personal = document.getElementById('entrega_personal_id').value;
        const fechaEnt = document.getElementById('entrega_fecha').value;
        const nota = document.getElementById('entrega_nota').value.trim();
        const quiereSeg = document.getElementById('entrega_check_seguimiento').checked;
        const fechaSeg = document.getElementById('entrega_fecha_seg').value;

        if (!personal) { alert("Selecciona quién entrega."); return; }
        if (!fechaEnt) { alert("Fecha de entrega requerida."); return; }
        
        if (nota === "") {
            document.getElementById('entrega_error_nota').style.display = 'block';
            document.getElementById('entrega_nota').classList.add('is-danger');
            document.getElementById('entrega_nota').focus();
            return;
        } else {
            document.getElementById('entrega_error_nota').style.display = 'none';
            document.getElementById('entrega_nota').classList.remove('is-danger');
        }
        
        if (quiereSeg && !fechaSeg) { alert("Selecciona fecha seguimiento."); return; }

        btn.classList.add('is-loading');

        // Guardamos todo
        await guardarNotaAsync(odsIdActual, nota, "ENTREGA");
        if (quiereSeg) await guardarSeguimientoAsync(odsIdActual, fechaSeg);

        // Actualizamos estado con datos extra
        await cambiarEstadoDirecto(odsIdActual, 'Entregado', {
            'Entrego': personal,
            'Fechaentrega': fechaEnt
        });
    });

    // HELPERS PARA ABRIR LOS MODALES
    function abrirModalSeguimiento() {
        document.getElementById('seg_nota_input').value = "";
        document.getElementById('seg_error_nota').style.display = 'none';
        modalSeg.classList.add('is-active');
    }
    
    function abrirModalEntrega() {
        document.getElementById('entrega_nota').value = "";
        document.getElementById('entrega_error_nota').style.display = 'none';
        modalEnt.classList.add('is-active');
    }

});

// --- FUNCIÓN GLOBAL DE CAMBIO DE ESTADO ---
async function cambiarEstadoDirecto(odsId, nuevoEstado, extraData = null) {
    try {
        const formData = new FormData();
        formData.append('modulo_ods', 'cambiar_status');
        formData.append('Idods', odsId);
        formData.append('Status', nuevoEstado);
        
        if (extraData) {
            for (const key in extraData) {
                formData.append(key, extraData[key]);
            }
        }
        
        const response = await fetch('/VENTAS3/app/ajax/odsAjax.php', { method: 'POST', body: formData });
        const data = await response.json(); 
        
        if (data && data.success) {
            if(typeof mostrarNotificacion === 'function') mostrarNotificacion(`Estado cambiado a ${nuevoEstado}`);
            else alert(`Estado actualizado a: ${nuevoEstado}`);
            
            setTimeout(() => location.reload(), 500);
        } else {
            alert("Error: " + (data.msg || data.error || "No se pudo actualizar"));
            location.reload();
        }
    } catch (error) {
        console.error(error);
        alert('Error de conexión.');
        location.reload();
    }
}
</script>