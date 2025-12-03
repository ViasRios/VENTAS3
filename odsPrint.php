<?php
declare(strict_types=1);
require_once __DIR__ . '/app/models/mainModel.php';

use app\models\mainModel;
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$auto = isset($_GET['auto']) && $_GET['auto'] == '1';
$pdo = mainModel::conectar();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n){ return '$'.number_format((float)$n, 2); }
function ffecha(?string $iso): string {
  if(!$iso) return '';
  $d = DateTime::createFromFormat('Y-m-d H:i:s', $iso) ?: DateTime::createFromFormat('Y-m-d', $iso);
  return $d ? $d->format('d/m/Y H:i') : $iso;
}

// === ODS encabezado ===
$stmt = $pdo->prepare("
  SELECT
    o.Idods, o.Fecha, o.Sucursal, o.Total,
    o.Garantia, o.Tipo, o.Marca, o.Modelo, o.Color, o.Noserie, o.Contrasena, o.Accesorios, o.Cuenta,
    o.Problema, o.FechaEntrega,
    c.Nombre  AS NombreCliente,
    c.Numero  AS TelefonoCliente,
    c.Email   AS EmailCliente,
    c.Colonia AS ColoniaCliente,
    a.Nombre  AS NombreAsesor,
    t.Nombre  AS NombreTecnico
  FROM ods o
  INNER JOIN clientes c ON o.Idcliente = c.Idcliente
  INNER JOIN personal a ON o.Idasesor = a.Idasesor
  LEFT  JOIN personal t ON o.IdTecnico = t.Idasesor
  WHERE o.Idods = :id
  LIMIT 1
");
$stmt->execute([':id'=>$id]);
$ods = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ods) {
  http_response_code(404);
  echo "<p>ODS no encontrada.</p>";
  exit;
}
$q = $pdo->prepare("SELECT * FROM ods WHERE Idods = :id ORDER BY Idods ASC");
$q->execute([':id'=>$id]);
$det = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];

$subtotal = 0.0;
foreach ($det as $k => $r) {
  $cant = (float)($r['Cantidad'] ?? 1);
  $costo = (float)($r['Costorep'] ?? 0);
  $importe = $cant * $costo;
  $det[$k]['_cantidad'] = $cant;
  $det[$k]['Costorep'] = $costo;
  $det[$k]['_importe'] = $importe;
  $subtotal += $importe;
}
$iva = $subtotal * 0.16;
$descuento = 0.0;
$total = $subtotal + $iva - $descuento;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Orden de Servicio #<?= h($ods['Idods']) ?></title>
<style>
/* ====== MODO PANTALLA (compacto) ====== */
*{box-sizing:border-box;margin:0;padding:0;font-family:'Segoe UI',Tahoma,sans-serif}
:root{
  --fs-base:14px;      /* Base legible estándar */
  --fs-small:11px;
  --fs-title:20px;     /* Título más grande */
  --gap:5px;           /* Reducido para que quepa todo (antes 10px) */
  --pad:5px;
}
body{background:#f5f7f9;color:#000;padding:10px;display:flex;justify-content:center;font-size:var(--fs-base)}
.container{
  width:210mm;                 /* A4 ancho */
  max-width:210mm;
  background:#fff;
  box-shadow:0 0 8px rgba(0,0,0,.08);
  padding:5mm 8mm;             /* Márgenes internos reducidos para ganar espacio */
}
.logo {
  flex:1;
  text-align:right;
}
.logo img {
  max-height:92px;   /* ajusta el alto del logo */
  width:auto;
  object-fit:contain;
}

.header {
  background: linear-gradient(to right,#0d476a,#1887cb);
  color:#fff;
  padding:8px;        /* Padding reducido */
  margin-bottom:10px;
  display:flex;
  justify-content:space-between;
  align-items:center;
}
.company-info { flex:2; }

.company-info h2{font-size:19px;margin-bottom:4px; font-weight:1000;}
.company-info p{font-size:var(--fs-small);margin:1px 0}

.document-title{
  background:#1887cb;color:#fff;text-align:center;
  padding:6px;font-size:var(--fs-title);font-weight:700;margin:8px 0;
  border-radius: 4px;
}
.order-number{font-size:24px}
.section{margin-bottom:var(--gap);page-break-inside:avoid}
.section-title{
  background:#0d476a;color:#fff;padding:5px 8px;margin-bottom:4px;
  border-radius:3px;font-weight:700;font-size:13px /* Aumentado de 11 a 13 */
}

/* Tablas optimizadas: Letra GRANDE, pero celdas compactas */
table{width:100%;border-collapse:collapse;margin-bottom:4px;font-size:12.5px;table-layout:fixed} /* Aumentado de 10.5 a 12.5 */
th{background:#e8f4ff;color:#0d476a;padding:4px;border-bottom:2px solid #1887cb} /* Padding bajado de 6 a 4 */
td{padding:4px;border-bottom:1px solid #ddd;vertical-align:top;word-wrap:break-word; color:#000;}
tr:nth-child(even){background:#fafafa}

/* Campos multilínea */
textarea{
  width:100%;padding:4px;border:1px solid #999;border-radius:3px;
  resize:none;min-height:42px;font-size:12.5px; color:#000; font-family:inherit;
}

/* Totales compactos */
.totals{
  background:#e8f4ff;padding:6px;border-radius:4px;border-left:4px solid #1887cb;
  margin:8px 0;font-size:12.5px
}
.total-row{display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px dashed #ccc}
.grand-total{font-weight:700;font-size:16px;color:#0d476a;border-bottom:none;margin-top:4px}

/* Firmas */
.signature-area{
  display:flex;gap:10mm;justify-content:space-between;margin-top:8px;padding-top:8px;border-top:2px dashed #999;font-size:12px
}
.signature-box{flex:1; text-align:center;}
.signature-line{height:1px;background:#000;margin:25px 0 5px}

/* === CAMBIO AQUI: Sello Circular === */
.stamp-box{
  width: 130px;           /* Ancho fijo */
  height: 130px;          /* Alto igual al ancho para que sea círculo */
  border-radius: 50%;    /* Borde redondeado al 50% */
  border:2px dashed #999;
  display:flex;
  align-items:center;
  justify-content:center;
  margin:4px auto;       /* auto centra el circulo horizontalmente */
  font-style:italic;
  color:#777;
  font-size:10px;
  text-align: center;
}
/* Footer */
.footer{background:#0d476a;color:#fff;text-align:center;padding:5px;font-size:10px;margin-top:10px}

.btn-print{background:#1887cb;color:#fff;border:none;padding:10px 15px;border-radius:4px;cursor:pointer;font-weight:700;margin-top:10px;width:100%;font-size:14px}
.btn-print:hover{background:#0d476a}

/* ====== MODO IMPRESIÓN A4 ====== */
@page{
  size: A4 portrait;
  margin: 5mm;               /* Margen reducido (era 9mm) para aprovechar hoja */
}
@media print{
  html,body{width:210mm;height:297mm;background:#fff}
  body{padding:0;display:block}
  .container{
    width:auto; max-width:none; box-shadow:none; padding:0;   /* ya usamos @page margin */
  }
  /* Forzar cortes limpios y evitar desbordes */
  .section, table, tr{page-break-inside:avoid; break-inside:avoid}
  .document-title{margin:4mm 0 4mm}
  /* Textareas: que impriman todo el contenido */
  textarea{
    display:block; height:auto !important; overflow:visible; border:none; padding:0;
    -webkit-print-color-adjust:exact; print-color-adjust:exact;
  }
  .btn-print{display:none}
}
</style>

<?php if ($auto): ?>
<script>window.addEventListener('load',()=>window.print());</script>
<?php endif; ?>
</head>
<body>
<div class="container">
  <div class="header">
    <div class="company-info">
      <h2>KASCOM</h2>
      <p>Xicoténcatl, No. 110</p>
      <p>Col. Centro Pachuca de Soto</p>
      <p>Tel: 771-402-02-58 | info@kascom.com</p>
      <p>Lunes-Viernes: 10:00-19:00 || Sábado: 10:00-17:00</p>
    </div>
    <div class="logo">
    <img src="/VENTAS3/foto_ods/Kascom.jpg" alt="Logo KASCOM">
  </div>
  </div>

  <div class="document-title">
    ORDEN DE SERVICIO <span class="order-number">#<?= h($ods['Idods']) ?></span>
  </div>

  <div class="section">
  <div class="section-title">📋 Información del Cliente</div>
  <table style="width:100%;">
    <thead>
      <tr>
        <th style="width:25%; text-align:left;">Cliente</th>
        <th style="width:25%; text-align:left;">Correo</th>
        <th style="width:25%; text-align:center;">Fecha de Ingreso</th>
        <th style="width:25%; text-align:center;">Garantía</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="text-align:left; font-weight:bold;"><?= h($ods['NombreCliente']) ?></td>
        <td style="text-align:left;"><?= h($ods['EmailCliente']) ?></td>
        <td style="text-align:center;"><?= h(ffecha($ods['Fecha'])) ?></td>
        <td style="text-align:center; font-weight:bold;"><?= (int)$ods['Garantia']===1?'Sí':'No' ?></td>
      </tr>
    </tbody>
  </table>

  <div class="section-title">💻 Información del Equipo</div>
  <table style="width:100%;">
    <thead>
      <tr>
        <th style="width:25%; text-align:left;">Tipo</th>
        <th style="width:25%; text-align:left;">Marca/Modelo</th>
        <th style="width:25%; text-align:center;">Color</th>
        <th style="width:25%; text-align:center;">N° Serie</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="text-align:left;"><?= h($ods['Tipo']) ?></td>
        <td style="text-align:left; font-weight:bold;"><?= h($ods['Marca'].' / '.$ods['Modelo']) ?></td>
        <td style="text-align:center;"><?= h($ods['Color']) ?></td>
        <td style="text-align:center;"><?= h($ods['Noserie']) ?></td>
      </tr>
    </tbody>
  </table>
  <table style="width:100%;">
    <thead>
      <tr>
        <th style="width:50%; text-align:left;">Contraseña</th>
        <th style="width:50%; text-align:left;">Accesorios</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="text-align:left; font-weight:bold;"><?= h($ods['Contrasena']) ?></td>
        <td style="text-align:left;"><?= h($ods['Accesorios']) ?></td>
      </tr>
    </tbody>
  </table>
</div>

  <div class="section">
    <div class="section-title">🔍 Descripción del Problema</div>
    <textarea readonly><?= h($ods['Problema']) ?></textarea>
  </div>

    <div class="section">
    <div class="section-title">⏱️ Servicios Realizados</div>
    <table style="width:100%;">
    <thead>
      <tr>
        <th style="width:50%; text-align:left;">Descripción</th>
        <th style="width:25%; text-align:center;">Cantidad</th>
        <th style="width:25%; text-align:right;">Costo</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($det as $r): ?>
      <tr>
        <td style="text-align:left;"><?= h($r['Reparacion']) ?></td>
        <td style="text-align:center;"><?= h($r['_cantidad']) ?></td>
        <td style="text-align:right;"><?= money($r['Costorep']) ?></td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td colspan="2" style="text-align:right; font-weight:bold;">TOTAL</td>
        <td style="text-align:right; font-weight:bold;"><?= money($subtotal) ?></td>
      </tr>
    </tbody>
  </table>
  </div>

  <div class="totals">
    <div class="total-row"><span>Subtotal:</span><span><?= money($subtotal) ?></span></div>
    <div class="total-row"><span>IVA (16%):</span><span><?= money($iva) ?></span></div>
    <div class="total-row"><span>Descuento:</span><span><?= money(-$descuento) ?></span></div>
    <div class="total-row"><span>A Cuenta:</span><span><?= h($ods['Cuenta']) ?></span></div>
    <div class="total-row grand-total"><span>TOTAL:</span><span><?= money($total) ?></span></div>
  </div>

  <div class="section">
    <div class="section-title">Observaciones</div>
    <textarea>Se recomienda no instalar software no verificado y realizar mantenimiento preventivo cada 6 meses. La garantía cubre mano de obra, excepto daños físicos o por mal uso.</textarea>
  </div>

  <div class="signature-area">
    <div class="signature-box">
      <div class="stamp-box">Espacio para sello</div>
      <div class="signature-line"></div>
       <p><strong>Asesor:</strong> <?= h($ods['NombreAsesor']) ?></p>
    </div>
    <div class="signature-box">
      <br>
      <p><strong>Recibí Conforme</strong></p>
      <p><strong>Fecha de Entrega:</strong> </p>
      <br><br>
      <br><br><br>
      <div class="signature-line"></div>
      <p>Firma del Cliente</p>
    </div>
  </div>

  <?php if(!$auto): ?><button class="btn-print" onclick="window.print()">Imprimir</button><?php endif; ?>
  <div class="footer">
    <p>KASCOM - Powered by Casa de la Computación</p>
  </div>
</div>
</body>
</html>