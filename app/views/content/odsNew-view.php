<style>
  :root {
    --bg-body: #f0f2f5;
    --card-bg: #e1e6e9ff;
    --text-main: #1f2937;
    --text-muted: #6b7280;
    --primary: #4f46e5;
    --radius: 16px;
    --shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
  }

  /* Contenedor general */
  .ods-new-container {
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    color: var(--text-main);
  }

  /* Tarjetas Modernas (Igual que odsView) */
  .aesthetic-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border: 1px solid rgba(0,0,0,0.04);
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease;
  }
  .aesthetic-card:hover { transform: translateY(-2px); }

  /* Acentos de color laterales */
  .accent-blue   { border-left: 5px solid #3b82f6; }
  .accent-purple { border-left: 5px solid #8b5cf6; }
  .accent-green  { border-left: 5px solid #10b981; }
  .accent-orange { border-left: 5px solid #f97316; }
  .accent-pink   { border-left: 5px solid #ec4899; }

  /* Cabeceras de Sección */
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
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Iconos cuadrados */
  .icon-box {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
  }
  .ib-blue   { background: #e2edfaff; color: #3b82f6; }
  .ib-purple { background: #f5f3ff; color: #8b5cf6; }
  .ib-green  { background: #ecfdf5; color: #10b981; }
  .ib-orange { background: #fff7ed; color: #f97316; }
  .ib-pink   { background: #fdf2f8; color: #ec4899; }

  /* Labels modernos */
  label strong {
    font-weight: 600;
    color: #6b7280;
    font-size: 0.8rem;
    text-transform: uppercase;
    margin-bottom: 0.3rem;
    display: block;
  }

  /* Inputs Aesthetic */
  .input, .select select, .textarea {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
    transition: all 0.2s;
    color: #1f2937;
    font-weight: 500;
  }
  .input:focus, .select select:focus, .textarea:focus {
    border-color: var(--primary);
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  }
  .input[readonly] {
    background-color: #f3f4f6;
    color: #9ca3af;
    border-color: #e5e7eb;
    cursor: not-allowed;
  }

  /* Tabla moderna dentro de servicios */
  .table-modern {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }
  .table-modern th {
    background: #f9fafb;
    color: #6b7280;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    padding: 10px;
    border-bottom: 2px solid #e5e7eb;
  }
  .table-modern td {
    border-bottom: 1px solid #f3f4f6;
    padding: 10px;
    vertical-align: middle;
  }
  .table-modern tfoot td, .table-modern tfoot th {
    background-color: #f9fafb;
    border-top: 2px solid #e5e7eb;
    color: #1f2937;
    font-weight: 700;
  }

  /* Botones */
  .button.is-aesthetic {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    color: white;
    border: none;
    font-weight: 600;
    box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
    transition: all 0.2s;
  }
  .button.is-aesthetic:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 10px rgba(79, 70, 229, 0.3);
    color: white;
  }
</style>

<div class="ods-new-container">
    
    <div class="container is-fluid mb-4 mt-2">
        <h1 class="title" style="color: #1f2937; font-weight: 800;">Nueva ODS</h1>
        <h2 class="subtitle is-6" style="color: #6b7280;">Registro de Orden de Servicio</h2>
    </div>

    <div class="container is-fluid pb-6">
        <form class="FormularioAjax" id="form_nueva_ods" action="<?php echo APP_URL; ?>app/ajax/odsAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data">
            
            <input type="hidden" name="modulo_ods" value="registrar">
            <input type="hidden" name="filtro_campo" value="Status">
            
            <?php
            require_once __DIR__ . "/../../models/mainModel.php";
            use app\models\mainModel;
            
            $pdo = mainModel::conectar();
            
            // 1) Traer clientes
            $sql = "SELECT Idcliente, Nombre, Numero, Email, Colonia FROM clientes WHERE TRIM(Nombre) <> '' ORDER BY Nombre ASC";
            $clientes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            
            // 2) Traer Tipos
            $sqlTipo = "SELECT DISTINCT Tipo FROM ods WHERE Tipo IS NOT NULL AND TRIM(Tipo) <> '' ORDER BY Tipo ASC";
            $stmtTipo = $pdo->prepare($sqlTipo);
            $stmtTipo->execute();
            $tipos = array_unique($stmtTipo->fetchAll(PDO::FETCH_COLUMN));
            
            // 3) Traer Marcas y Modelos
            $marcas = $pdo->query("SELECT DISTINCT TRIM(Marca) FROM ods WHERE Marca IS NOT NULL AND TRIM(Marca) <> '' ORDER BY Marca ASC")->fetchAll(PDO::FETCH_COLUMN);
            $modelos = $pdo->query("SELECT DISTINCT TRIM(Modelo) FROM ods WHERE Modelo IS NOT NULL AND TRIM(Modelo) <> '' ORDER BY Modelo ASC")->fetchAll(PDO::FETCH_COLUMN);
            
            // 4) Traer ID ODS
            $idods = $pdo->query("SELECT DISTINCT Idods FROM ods WHERE Idods IS NOT NULL ORDER BY Idods DESC")->fetchAll(PDO::FETCH_COLUMN);
            
            // 5) Traer Usos
            $usos = $pdo->query("SELECT DISTINCT TRIM(Uso) FROM ods WHERE Uso IS NOT NULL AND TRIM(Uso) <> '' ORDER BY Uso ASC")->fetchAll(PDO::FETCH_COLUMN);
            ?>

            <div class="aesthetic-card accent-blue">
                <div class="card-header-custom">
                    <div class="card-title">
                        <div class="icon-box ib-blue"><i class="fas fa-user"></i></div>
                        Datos del Cliente
                    </div>
                    <button type="button" id="btn_guardar_cliente" class="button is-small is-light has-text-link">
                        <i class="fas fa-user-plus mr-1"></i> Nuevo Cliente
                    </button>
                </div>

                <div class="columns is-multiline">
                    <div class="column is-6">
                        <div class="control" style="position: relative;">
                            <label><strong>Nombre Cliente <?php echo CAMPO_OBLIGATORIO; ?></strong></label>
                            <input class="input" type="text" id="nombre_cliente" name="NombreCliente" autocomplete="off" required placeholder="Buscar o escribir nombre...">
                            <input type="hidden" id="id_cliente" name="Idcliente">
                            <div id="sug_clientes" class="box" style="position:absolute; top:100%; left:0; right:0; display:none; max-height:220px; overflow:auto; z-index:1000;"></div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="control">
                            <label><strong>Teléfono <?php echo CAMPO_OBLIGATORIO; ?></strong></label>
                            <input class="input" type="text" id="numero" name="Numero" required placeholder="10 dígitos">
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="control">
                            <label><strong>Email</strong></label>
                            <input class="input" type="email" id="email" name="Email" required placeholder="correo@ejemplo.com">
                        </div>
                    </div>
                    <div class="column is-12">
                        <div class="control" style="position: relative;">
                            <label><strong>Dirección / Colonia</strong></label>
                            <input class="input" type="text" id="colonia" name="Colonia" required autocomplete="off" placeholder="Colonia, Ciudad">
                            <div id="sug_colonia" class="box" style="position:absolute; top:100%; left:0; right:0; display:none; max-height:220px; overflow:auto; z-index:1000;"></div>
                            <p class="help" id="colonia_info"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="aesthetic-card accent-purple">
                <div class="card-header-custom">
                    <div class="card-title">
                        <div class="icon-box ib-purple"><i class="fas fa-laptop"></i></div>
                        Información del Equipo
                    </div>
                </div>

                <div class="columns is-multiline">
                    <div class="column is-3">
                        <div class="field">
                            <label><strong>Fecha Ingreso</strong></label>
                            <input class="input" type="text" id="fecha_registro_mostrar" readonly 
                                   style="background-color: #ffffff !important; color: #1f2937 !important; border: 1px solid #e5e7eb; font-weight: 600;">
                            <input type="hidden" id="fecha_registro" name="Fecha">
                        </div>
                    </div>
                    <?php
                        // Aseguramos la conexión para traer la lista completa
                        $pdo = app\models\mainModel::conectar();
                        $sqlPersonal = "SELECT Idasesor, Nombre FROM personal WHERE Puesto LIKE '%ASESOR%' OR Puesto LIKE '%TECNICO%' OR Puesto LIKE '%VENTAS%' ORDER BY Nombre ASC";
                        // Si prefieres traer TODOS sin importar puesto, usa: SELECT Idasesor, Nombre FROM personal ORDER BY Nombre ASC
                        $listaPersonal = $pdo->query("SELECT Idasesor, Nombre FROM personal ORDER BY Nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <div class="column is-3">
                        <div class="field">
                            <label><strong>Asesor</strong></label>
                            <div class="control has-icons-right">
                                <input class="input" type="text" 
                                      id="input_asesor_nombre" 
                                      name="NombreAsesor" 
                                      value="<?php echo isset($_SESSION['nombre']) ? $_SESSION['nombre'] : ''; ?>" 
                                      readonly
                                      list="lista_personal_asesor"
                                      placeholder="Seleccione un asesor..."
                                      autocomplete="off"
                                      style="background-color: #ffffff !important; color: #1f2937 !important; border: 1px solid #e5e7eb; font-weight: 600; cursor: pointer;">
                                
                                <datalist id="lista_personal_asesor">
                                    <?php foreach($listaPersonal as $per): ?>
                                        <option value="<?php echo $per['Nombre']; ?>" data-id="<?php echo $per['Idasesor']; ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
                                <input type="hidden" id="input_asesor_id" name="Idasesor" 
                                      value="<?php echo isset($_SESSION['id']) ? $_SESSION['id'] : ''; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label><strong>Sucursal</strong></label>
                            <div class="select is-fullwidth">
                                <select name="Sucursal" id="sucursal" required>
                                    <option value="Centro" selected>Centro</option>
                                    <option value="Sur">Sur</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="field">
                            <label><strong>Tipo de Orden</strong></label>
                            <div class="select is-fullwidth">
                                <select id="tipo_ods">
                                    <option value="Nueva" selected>Nueva</option>
                                    <option value="Garantia">Garantía</option>
                                </select>
                            </div>
                            <input type="hidden" id="garantia" name="Garantia" value="0">
                        </div>
                    </div>

                    <div class="column is-3">
                        <div class="control" style="position: relative;">
                            <label><strong>ODS Anterior</strong></label>
                            <input class="input" type="text" id="odsanterior" name="Odsanterior" maxlength="30" autocomplete="off" readonly placeholder="Si es garantía...">
                            <div id="sug_odsanterior" class="box" style="position:absolute; z-index:1000; display:none;"></div>
                        </div>
                    </div>

                    <div class="column is-3">
                        <div class="control">
                            <label><strong>Tipo Equipo <?php echo CAMPO_OBLIGATORIO; ?></strong></label>
                            <input class="input" list="lista_tipos" type="text" name="Tipo" maxlength="40" required placeholder="Laptop, PC...">
                            <datalist id="lista_tipos">
                                <?php foreach ($tipos as $t): ?><option value="<?php echo htmlspecialchars($t); ?>"><?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="control" style="position: relative;">
                            <label><strong>Marca <?php echo CAMPO_OBLIGATORIO; ?></strong></label>
                            <input class="input" type="text" id="marca" name="Marca" required autocomplete="off">
                            <div id="sug_marca" class="box" style="position:absolute; z-index:1000; display:none;"></div>
                        </div>
                    </div>
                    <div class="column is-3">
                        <div class="control" style="position: relative;">
                            <label><strong>Modelo <?php echo CAMPO_OBLIGATORIO; ?></strong></label>
                            <input class="input" type="text" id="modelo" name="Modelo" required autocomplete="off">
                            <div id="sug_modelo" class="box" style="position:absolute; z-index:1000; display:none;"></div>
                        </div>
                    </div>

                    <div class="column is-3">
                        <label><strong>No. Serie</strong></label>
                        <input class="input" type="text" name="Noserie" required>
                    </div>
                    <div class="column is-3">
                        <label><strong>Color</strong></label>
                        <input class="input" type="text" name="Color">
                    </div>
                    <div class="column is-3">
                        <label><strong>Contraseña</strong></label>
                        <input class="input" type="text" name="Contrasena" placeholder="Patrón o PIN">
                    </div>
                    <div class="column is-3">
                        <label><strong>Respaldo</strong></label>
                        <div class="select is-fullwidth">
                            <select name="Respaldo" required>
                                <option value="">-- Selecciona --</option>
                                <option value="Si">Sí, requiere</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>

                    <div class="column is-6">
                        <div class="control" style="position: relative;">
                            <label><strong>Uso / Estado</strong></label>
                            <input class="input" type="text" id="uso" name="Uso" autocomplete="off" placeholder="Hogar, Oficina, Roto...">
                            <div id="sug_uso" class="box" style="position:absolute; z-index:1000; display:none;"></div>
                        </div>
                    </div>
                    <div class="column is-6 ">
                        <label><strong>Accesorios</strong></label>
                        
                        <div class="control" style="position: relative;">
                            <input class="input" type="text" id="input_accesorio_temp" 
                                  placeholder="Selecciona de la lista o escribe..." autocomplete="off">
                            
                            <span class="icon is-small is-right" style="position: absolute; right: 10px; top: 10px; pointer-events: none; color: #3d5b8fff;">
                                <i class="fas fa-plus-circle"></i>
                            </span>

                            <div id="panel_sugerencias_accesorios" class="box" 
                                style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; display: none; 
                                        margin-top: 5px; padding: 10px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb;">
                                
                                <p class="has-text-grey-light is-size-7 mb-2" style="font-weight: 600; text-transform: uppercase;">Frecuentes:</p>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 8px;">
                                    <span class="tag is-white is-medium is-clickable item-accesorio" style="border: 1px solid #e5e7eb;">Ninguno</span>
                                    <span class="tag is-white is-medium is-clickable item-accesorio" style="border: 1px solid #e5e7eb;">Eliminador</span>
                                    <span class="tag is-white is-medium is-clickable item-accesorio" style="border: 1px solid #e5e7eb;">Cargador</span>
                                    <span class="tag is-white is-medium is-clickable item-accesorio" style="border: 1px solid #e5e7eb;">Chip</span>
                                    <span class="tag is-white is-medium is-clickable item-accesorio" style="border: 1px solid #e5e7eb;">USB</span>
                                    <span class="tag is-white is-medium is-clickable item-accesorio" style="border: 1px solid #e5e7eb;">Cable USB</span>
                                    <span class="tag is-white is-medium is-clickable item-accesorio" style="border: 1px solid #e5e7eb;">Mouse</span>
                                    <span class="tag is-white is-medium is-clickable item-accesorio" style="border: 1px solid #e5e7eb;">Teclado</span>
                                    <span class="tag is-white is-medium is-clickable item-accesorio" style="border: 1px solid #e5e7eb;">Cable corriente</span>
                                    <span class="tag is-white is-medium is-clickable item-accesorio" style="border: 1px solid #e5e7eb;">Memoria SD</span>
                                </div>
                            </div>
                        </div>

                        <div id="contenedor_tags_accesorios" class="tags are-medium mt-2" style="min-height: 15px;"></div>

                        <input type="hidden" name="Accesorios" id="input_accesorios_final">
                    </div>
                    
                    <div class="column is-6">
                        <label><strong>Problema Reportado</strong></label>
                        <textarea class="textarea" name="Problema" rows="3" placeholder="¿Qué falla presenta?"></textarea>
                    </div>
                    <div class="column is-6">
                        <label><strong>Inspección Física Rápida</strong></label>
                        <textarea class="textarea" name="Inspeccion" rows="3" placeholder="Rayones, golpes, tornillos faltantes..."></textarea>
                    </div>
                    <div class="column is-6 is-hidden">
                        <input class="input" type="text" name="Carpeta"> </div>
                </div>
            </div>

            <div class="aesthetic-card accent-green">
                <div class="card-header-custom">
                    <div class="card-title">
                        <div class="icon-box ib-green"><i class="fas fa-clock"></i></div>
                        Tiempos y Asignación
                    </div>
                </div>

                <div class="columns">
                    <div class="column is-4">
                        <label><strong>Tiempo de Respuesta</strong></label>
                        <div class="select is-fullwidth mt-1">
                            <select id="tipo_tiempo" name="Tipo_tiempo" required>
                                <option value="" selected disabled>— Selecciona —</option>
                                <option value="dias">Días (Fecha)</option>
                                <option value="horas">Horas (Mismo día)</option>
                            </select>
                        </div>
                        <div id="grupo_fecha" class="mt-3" style="display:none;">
                            <input class="input" type="date" id="fecha_respuesta" name="Fecha_respuesta">
                        </div>
                        <div id="grupo_hora" class="mt-3" style="display:none;">
                            <input class="input" type="time" id="hora_respuesta" name="Hora_respuesta" step="300">
                        </div>
                        <input type="hidden" id="campo_tiempo_unificado" name="Tiempo">
                    </div>

                    <div class="column is-4">
                        <label><strong>Status Inicial</strong></label>
                        <div class="select is-fullwidth mt-1">
                            <select name="Status" required>
                                <?php
                                $status_orden = ["Recepcion", "Diagnostico", "Reparacion"];
                                foreach ($status_orden as $st) {
                                    $sel = ($st === "Recepcion") ? "selected" : "";
                                    echo "<option value='$st' $sel>$st</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="column is-4">
                        <label><strong>Técnico Asignado</strong></label>
                        <div class="select is-fullwidth mt-1">
                            <select id="id_tecnico" name="IdTecnico" required>
                                <option value="" selected disabled>— Auto o Manual —</option>
                                <?php
                                $qTec = "SELECT Idasesor, Nombre FROM personal ORDER BY FIELD(Nombre, 'ALEJANDRO', 'FERNANDO', 'ARTURO M') DESC, Nombre ASC";
                                $tecnicos = $pdo->query($qTec)->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($tecnicos as $tec) {
                                    echo "<option value='{$tec['Idasesor']}'>{$tec['Nombre']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="aesthetic-card accent-pink">
                <div class="card-header-custom">
                    <div class="card-title">
                        <div class="icon-box ib-pink"><i class="fas fa-concierge-bell"></i></div>
                        Servicios y Costos
                    </div>
                </div>

                <div class="columns is-variable is-2">
                    <div class="column is-5">
                        <label><strong>Servicio</strong></label>
                        <input class="input" id="servicio" name="ServicioBusqueda" list="serviciosList" placeholder="Buscar servicio..." autocomplete="off">
                        <datalist id="serviciosList"></datalist>
                    </div>
                    <div class="column is-3">
                        <label><strong>Costo Unit.</strong></label>
                        <input class="input" id="costo" placeholder="0.00" autocomplete="off">
                    </div>
                    <div class="column is-2">
                        <label><strong>Cant.</strong></label>
                        <input class="input" id="cantidad_serv" type="number" value="1" min="1">
                    </div>
                    <div class="column is-2">
                        <label>&nbsp;</label>
                        <button type="button" id="btn_agregar_serv" class="button is-success is-light is-fullwidth">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                </div>

                <div class="table-container mt-3">
                    <table class="table-modern" id="tabla_servicios">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Servicio</th>
                                <th>Costo</th>
                                <th>Cant.</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="has-text-right">Subtotal</td>
                                <td id="subtotal_general">$0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="has-text-right">Descuento (<span id="lbl_desc">0%</span>)</td>
                                <td id="monto_descuento" class="has-text-danger">-$0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="has-text-right">A cuenta</td>
                                <td id="monto_acuenta" class="has-text-success">-$0.00</td>
                                <td></td>
                            </tr>
                            <tr style="font-size: 1.1rem;">
                                <td colspan="4" class="has-text-right">TOTAL</td>
                                <td id="total_pagar" class="has-text-link">$0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="columns mt-4">
                    <div class="column is-3">
                        <label><strong>Descuento %</strong></label>
                        <input class="input" id="descuento" name="Descuento" type="number" min="0" max="100" step="0.01" value="0">
                    </div>
                    <div class="column is-3">
                        <label><strong>A cuenta $</strong></label>
                        <input class="input" id="acuenta" name="Cuenta" type="number" min="0" step="0.01" value="0">
                    </div>
                </div>

                <input type="hidden" id="servicios_json" name="Servicios">
                <input type="hidden" id="total_hidden" name="Total">
            </div>

            <div class="has-text-centered mt-5">
                <button type="button" class="button is-aesthetic is-large is-rounded" id="btn-generar" style="min-width: 250px;">
                    <i class="far fa-save mr-2"></i> Generar ODS
                </button>
                <p class="has-text-grey-light is-size-7 mt-3">
                    Los campos marcados con <?php echo CAMPO_OBLIGATORIO; ?> son obligatorios
                </p>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const BASE = "<?php echo APP_URL; ?>";
  const form = document.getElementById('form_nueva_ods');
  const btn  = document.getElementById('btn-generar');

  btn.addEventListener('click', async (e) => {
    let previewWin = window.open('about:blank', '_blank');
    const fd = new FormData(form);
    const params = new URLSearchParams(fd); 

    try {
      const res  = await fetch(form.action, { method:'POST', body:params, credentials:'include' });
      const text = await res.text();
      
      if (!res.ok) {
        if (previewWin) previewWin.close();
        alert('Error del servidor: ' + res.status); 
        console.error(text);
        throw new Error(text.slice(0,400));
      }

      let json;
      try { json = JSON.parse(text); } catch (jsonError) {
        if (previewWin) previewWin.close();
        alert('Error en la respuesta de odsAjax.php.');
        console.error("Respuesta no JSON:", text);
        throw jsonError;
      }
      
      if (json.success && json.id) {
        const urlPrint = `${BASE}odsPrint.php?id=${encodeURIComponent(json.id)}&auto=1`;
        if (previewWin && !previewWin.closed) previewWin.location.replace(urlPrint);
        else window.open(urlPrint, '_blank');
        
        // Opcional: Recargar para limpiar
        setTimeout(() => window.location.reload(), 1000);
      } else {
        if (previewWin) previewWin.close();
        alert('Error al guardar: ' + (json.error || 'No se pudo guardar'));
      }
    } catch (err) {
      console.error(err);
      if (previewWin && !previewWin.closed) previewWin.close();
    }
  });
});
</script>

<script>
(function(){
  const BASE = "<?php echo APP_URL; ?>";
  const inpServicio = document.getElementById('servicio');
  const dlServicios = document.getElementById('serviciosList');
  const inpCosto    = document.getElementById('costo');
  const inpCant     = document.getElementById('cantidad_serv');
  const btnAgregar  = document.getElementById('btn_agregar_serv');
  const tbody       = document.querySelector('#tabla_servicios tbody');
  const lblSubtotal = document.getElementById('subtotal_general');
  const lblDescPct  = document.getElementById('lbl_desc');
  const lblDesc     = document.getElementById('monto_descuento');
  const lblACuenta  = document.getElementById('monto_acuenta');
  const lblTotal    = document.getElementById('total_pagar');
  const inpDesc     = document.getElementById('descuento');
  const inpACuenta  = document.getElementById('acuenta');
  const hiddenJson  = document.getElementById('servicios_json');
  const hiddenTotal = document.getElementById('total_hidden');

  let cacheBusqueda = []; 
  let items = [];

  async function fetchJSON(url){
    const res  = await fetch(url, { credentials:'include' });
    const text = await res.text();
    if (!res.ok) throw new Error('HTTP '+res.status);
    return JSON.parse(text);
  }

  async function buscarServicios(q){
    if (!q || q.trim().length < 2){ dlServicios.innerHTML = ''; cacheBusqueda = []; return; }
    const url = `${BASE}app/ajax/buscaServicioYReparacion.php?termino=${encodeURIComponent(q.trim())}`;
    let data = [];
    try { data = await fetchJSON(url); } catch{ dlServicios.innerHTML=''; return; }

    cacheBusqueda = (Array.isArray(data) ? data : []).map(x => ({
      id:       x.id ?? x.Idser ?? '',
      servicio: x.servicio ?? x.Descripcion ?? x.nombre ?? '',
      costo:    x.costo ?? x.Costo ?? x.precio ?? ''
    })).filter(x => x.servicio);

    dlServicios.innerHTML = cacheBusqueda.map(s =>
      `<option value="${s.servicio}" data-id="${s.id}" data-costo="${s.costo}">${s.servicio}</option>`
    ).join('');
  }

  inpServicio.addEventListener('input', e => buscarServicios(e.target.value));
  inpServicio.addEventListener('change', e => {
    const val = e.target.value;
    const opt = Array.from(dlServicios.options).find(o => o.value === val);
    let costo = opt?.dataset?.costo;
    if (!costo){
      const hit = cacheBusqueda.find(s => s.servicio === val);
      costo = hit?.costo ?? '';
    }
    inpCosto.value = costo || '';
  });

  const money = n => isNaN(n) ? '$0.00' : '$' + Number(n).toFixed(2);

  function renderTabla(){
    const subtotal = items.reduce((acc, it) => acc + (Number(it.costo||0) * Number(it.cantidad||1)), 0);
    let descPct = Number(inpDesc.value || 0);
    if (isNaN(descPct) || descPct < 0) descPct = 0;
    if (descPct > 100) descPct = 100;
    const descuento = subtotal * (descPct / 100);
    let acuenta = Number((inpACuenta.value || '0').toString().replace(/,/g,''));
    if (isNaN(acuenta) || acuenta < 0) acuenta = 0;
    let total = subtotal - descuento - acuenta;
    if (total < 0) total = 0;

    lblSubtotal.textContent = money(subtotal);
    lblDescPct.textContent  = `${descPct.toFixed(2)}%`;
    lblDesc.textContent     = '-'+money(descuento);
    lblACuenta.textContent  = '-'+money(acuenta);
    lblTotal.textContent    = money(total);

    tbody.innerHTML = items.map((it, i) => {
      const sub = Number(it.costo||0) * Number(it.cantidad||1);
      return `
      <tr>
        <td>${i+1}</td>
        <td>${it.servicio}</td>
        <td>${money(it.costo)}</td>
        <td><input type="number" min="1" value="${it.cantidad}" class="input is-small" style="width:60px;" data-idx="${i}" data-role="qty"></td>
        <td>${money(sub)}</td>
        <td>
          <button type="button" class="button is-small is-danger is-light" data-idx="${i}" data-role="del">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>`;
    }).join('');

    hiddenJson.value  = JSON.stringify(items);
    hiddenTotal.value = Number(total).toFixed(2);
  }

  tbody.addEventListener('input', e => {
    if (e.target.dataset.role === 'qty'){
      const idx = Number(e.target.dataset.idx);
      items[idx].cantidad = Math.max(1, Number(e.target.value || 1));
      renderTabla();
    }
  });
  tbody.addEventListener('click', e => {
    const btn = e.target.closest('[data-role="del"]');
    if (btn){
      items.splice(Number(btn.dataset.idx), 1);
      renderTabla();
    }
  });

  btnAgregar.addEventListener('click', () => {
    const nombre = (inpServicio.value || '').trim();
    const costo  = Number((inpCosto.value || '').toString().replace(/,/g,''));
    const cant   = Math.max(1, Number(inpCant.value || 1));

    if (!nombre){ alert('Elige un servicio.'); return; }
    if (isNaN(costo) || costo < 0){ alert('Costo inválido.'); return; }

    const i = items.findIndex(x => x.servicio === nombre && String(x.costo) === String(costo));
    if (i >= 0) items[i].cantidad += cant;
    else items.push({ id: '', servicio: nombre, costo, cantidad: cant });

    inpServicio.value = ''; inpCosto.value = ''; inpCant.value = 1;
    renderTabla();
  });

  inpDesc.addEventListener('input', renderTabla);
  inpACuenta.addEventListener('input', renderTabla);
  renderTabla();
})();
</script>

<script>
const CLIENTES = <?php echo json_encode($clientes, JSON_UNESCAPED_UNICODE|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
(function(){
  const inpNombre=document.getElementById('nombre_cliente'), inpId=document.getElementById('id_cliente');
  const inpNum=document.getElementById('numero'), inpEm=document.getElementById('email'), inpCol=document.getElementById('colonia');
  const box=document.getElementById('sug_clientes');
  
  function norm(s){ return (s||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
  
  inpNombre.addEventListener('input', ()=>{
    const term = norm(inpNombre.value);
    inpId.value='';
    if(term.length<2){ box.style.display='none'; return; }
    const res = CLIENTES.filter(c=>norm(c.Nombre).includes(term)).slice(0,10);
    box.innerHTML = res.map(c=>`
      <div class="p-2 is-clickable" style="border-bottom:1px solid #eee;" onclick="selectCli('${c.Idcliente}')">
        <strong>${c.Nombre}</strong><br><small>${c.Numero||''} - ${c.Colonia||''}</small>
      </div>
    `).join('');
    box.style.display = res.length?'block':'none';
  });

  window.selectCli = (id) => {
    const c = CLIENTES.find(x=>x.Idcliente==id);
    if(c){
      inpNombre.value=c.Nombre; inpId.value=c.Idcliente;
      inpNum.value=c.Numero||''; inpEm.value=c.Email||''; inpCol.value=c.Colonia||'';
      box.style.display='none';
    }
  };
  document.addEventListener('click', e=>{ if(!box.contains(e.target) && e.target!==inpNombre) box.style.display='none'; });
})();
</script>

<script>
document.getElementById('btn_guardar_cliente').addEventListener('click', async(e)=>{
  e.preventDefault();
  const BASE = "<?php echo APP_URL; ?>";
  const n=document.getElementById('nombre_cliente').value, tel=document.getElementById('numero').value;
  const mail=document.getElementById('email').value, col=document.getElementById('colonia').value;
  if(!n || !tel || !col) { alert('Faltan datos del cliente'); return; }
  
  try {
    const res = await fetch(`${BASE}app/ajax/guardarClienteAjax.php`, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ Nombre:n, Numero:tel, Email:mail, Colonia:col })
    });
    const json = await res.json();
    if(json.success){
        document.getElementById('id_cliente').value = json.Idcliente;
        CLIENTES.push({Idcliente:json.Idcliente, Nombre:n, Numero:tel, Email:mail, Colonia:col});
        alert('Cliente guardado y asignado');
    } else { alert(json.error || 'Error al guardar cliente'); }
  } catch(e){ console.error(e); alert('Error de red'); }
});
</script>

<script>
(function(){
  const inp=document.getElementById('colonia'), box=document.getElementById('sug_colonia'), info=document.getElementById('colonia_info');
  const cols = Array.from(new Set(CLIENTES.map(c=>c.Colonia).filter(Boolean))).sort();
  const norm = s=>s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');

  inp.addEventListener('input', ()=>{
    inp.value = inp.value.toUpperCase();
    const q = norm(inp.value);
    if(!q){ box.style.display='none'; return; }
    const res = cols.filter(c=>norm(c).includes(q)).slice(0,10);
    box.innerHTML = res.map(c=>`<div class="p-2 is-clickable" onclick="document.getElementById('colonia').value='${c}'; this.parentElement.style.display='none'">${c}</div>`).join('');
    box.style.display = res.length?'block':'none';
    
    const count = CLIENTES.filter(c=>norm(c.Colonia)===q).length;
    info.textContent = count ? `Hay ${count} clientes en esta colonia.` : 'Nueva colonia.';
  });
  document.addEventListener('click', e=>{ if(!box.contains(e.target) && e.target!==inp) box.style.display='none'; });
})();
</script>

<script>
const MARCAS=<?php echo json_encode($marcas); ?>, MODELOS=<?php echo json_encode($modelos); ?>;
const USOS=<?php echo json_encode($usos); ?>, IDODS=<?php echo json_encode($idods); ?>;

function setupAuto(idInp, idBox, dataArray){
  const inp=document.getElementById(idInp), box=document.getElementById(idBox);
  if(!inp || !box) return;
  inp.addEventListener('input', ()=>{
    const q = inp.value.toLowerCase();
    if(!q){ box.style.display='none'; return; }
    const res = dataArray.filter(x=>x.toString().toLowerCase().includes(q)).slice(0,10);
    box.innerHTML = res.map(x=>`<div class="p-2 is-clickable" onclick="document.getElementById('${idInp}').value='${x}'; document.getElementById('${idBox}').style.display='none'">${x}</div>`).join('');
    box.style.display = res.length?'block':'none';
  });
  document.addEventListener('click', e=>{ if(!box.contains(e.target) && e.target!==inp) box.style.display='none'; });
}
setupAuto('marca','sug_marca', MARCAS);
setupAuto('modelo','sug_modelo', MODELOS);
setupAuto('uso','sug_uso', USOS);
setupAuto('odsanterior','sug_odsanterior', IDODS);
</script>

<script>
document.addEventListener('DOMContentLoaded', ()=>{
  // Fecha hoy
  const hoy = new Date();
  document.getElementById('fecha_registro_mostrar').value = hoy.toLocaleDateString('es-MX');
  document.getElementById('fecha_registro').value = hoy.toISOString().split('T')[0];

  // Garantia
  const selTipo = document.getElementById('tipo_ods');
  const inpG = document.getElementById('garantia'), inpAnt = document.getElementById('odsanterior');
  selTipo.addEventListener('change', ()=>{
    if(selTipo.value==='Garantia'){ inpG.value='1'; inpAnt.readOnly=false; }
    else { inpG.value='0'; inpAnt.readOnly=true; inpAnt.value=''; }
  });

  // Tiempos
  const selT = document.getElementById('tipo_tiempo');
  selT.addEventListener('change', ()=>{
    document.getElementById('grupo_fecha').style.display = (selT.value==='dias')?'block':'none';
    document.getElementById('grupo_hora').style.display = (selT.value==='horas')?'block':'none';
    updateTiempo();
  });
  function updateTiempo(){
    const t = selT.value;
    const val = (t==='dias') ? document.getElementById('fecha_respuesta').value : document.getElementById('hora_respuesta').value;
    document.getElementById('campo_tiempo_unificado').value = (t && val) ? `${t}:${val}` : '';
  }
  document.getElementById('fecha_respuesta').addEventListener('change', updateTiempo);
  document.getElementById('hora_respuesta').addEventListener('change', updateTiempo);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', ()=>{
  const inpTipo = document.querySelector('input[name="Tipo"]');
  const selTec = document.getElementById('id_tecnico');
  const keywordsPC = ['HP','LAPTOP','CPU','PC','AIO','MAC','DELL','LENOVO','ASUS','ACER'];
  
  // Busca IDs de Alejandro y Arturo en el select
  let idAlejandro='', idArturo='';
  Array.from(selTec.options).forEach(o=>{
    if(o.text.toUpperCase().includes('ALEJANDRO')) idAlejandro=o.value;
    if(o.text.toUpperCase().includes('ARTURO M')) idArturo=o.value;
  });

  function autoAsignar(){
    const val = inpTipo.value.toUpperCase();
    if(keywordsPC.some(k=>val.includes(k)) && idArturo) selTec.value=idArturo;
    else if(idAlejandro) selTec.value=idAlejandro;
  }
  inpTipo.addEventListener('blur', autoAsignar);
  inpTipo.addEventListener('change', autoAsignar);
});
</script>
<script>
document.getElementById('numero').addEventListener('input', function (e) {
    // 1. Eliminar cualquier carácter que no sea número
    let input = e.target.value.replace(/\D/g, '');
    
    // 2. Limitar a 10 dígitos máximo (antes de formatear)
    if (input.length > 10) {
        input = input.substring(0, 10);
    }

    // 3. Aplicar el formato 3 - 3 - 4
    let formateado = input;
    
    if (input.length > 6) {
        // Si tiene más de 6 dígitos (ej: 1234567 -> 123 456 7)
        formateado = input.replace(/^(\d{3})(\d{3})(\d{0,4}).*/, '$1 $2 $3');
    } else if (input.length > 3) {
        // Si tiene más de 3 dígitos (ej: 1234 -> 123 4)
        formateado = input.replace(/^(\d{3})(\d{0,3})/, '$1 $2');
    }
    
    // 4. Asignar el valor formateado al input
    e.target.value = formateado;
});
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inpAsesor = document.getElementById('input_asesor_nombre');
    const hidAsesor = document.getElementById('input_asesor_id');
    const listaAsesores = document.getElementById('lista_personal_asesor');

    // 1. Mapear opciones para buscar ID por Nombre
    let personalMap = {};
    if (listaAsesores) {
        Array.from(listaAsesores.options).forEach(opt => {
            personalMap[opt.value.toUpperCase()] = opt.getAttribute('data-id');
        });
    }

    // 2. EVENTO DOBLE CLIC: DESBLOQUEAR Y MOSTRAR LISTA
    inpAsesor.addEventListener('click', function() {
        // Guardamos el valor actual por si se arrepiente
        this.dataset.original = this.value;
        
        // Quitamos candado
        this.removeAttribute('readonly');
        
        // Borramos texto momentáneamente para que el navegador muestre TODA la lista
        this.value = '';
        
        // Estilo visual de "Editando"
        this.style.cursor = 'text';
        this.style.borderColor = '#4f46e5';
        this.style.boxShadow = '0 0 0 3px rgba(79, 70, 229, 0.1)';
        
        // Forzamos el foco para desplegar lista
        this.focus();
    });

    // 3. EVENTO BLUR (AL SALIR): RE-BLOQUEAR
    inpAsesor.addEventListener('blur', function() {
        // Usamos un pequeño timeout para permitir que el click en la opción ocurra primero
        setTimeout(() => {
            // Si lo dejó vacío, restauramos el nombre anterior
            if (this.value.trim() === '') {
                this.value = this.dataset.original || '';
            }

            // ¡AQUÍ ESTÁ EL ARREGLO! Volvemos a bloquear para que el doble clic funcione de nuevo después
            this.setAttribute('readonly', true);
            
            // Restauramos estilos
            this.style.cursor = 'pointer';
            this.style.borderColor = '#e5e7eb';
            this.style.boxShadow = 'none';
        }, 200);
    });

    // 4. EVENTO CHANGE: ASIGNAR ID
    inpAsesor.addEventListener('change', function() {
        const nombre = this.value.toUpperCase().trim();
        if (personalMap[nombre]) {
            hidAsesor.value = personalMap[nombre];
        } else {
            // Si escribe un nombre que no existe, podrías limpiar el ID o dejar el anterior
            // Por seguridad, si no coincide, restauramos al original en el blur
        }
    });
});
</script>
<style>
  .item-accesorio {
    transition: all 0.2s ease;
    font-weight: 500;
    color: #4b5563;
}
.item-accesorio:hover {
    background-color: #e0e7ff !important; /* Azul muy suave al pasar mouse */
    color: #4f46e5 !important;            /* Texto morado/azul */
    border-color: #c7d2fe !important;
    transform: translateY(-1px);
}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inpTemp = document.getElementById('input_accesorio_temp');
    const panel = document.getElementById('panel_sugerencias_accesorios');
    const containerTags = document.getElementById('contenedor_tags_accesorios');
    const inpFinal = document.getElementById('input_accesorios_final');
    const opciones = document.querySelectorAll('.item-accesorio');

    // Almacén de accesorios seleccionados
    let seleccionados = [];

    // 1. Función para renderizar las etiquetas azules
    function render() {
        containerTags.innerHTML = '';
        seleccionados.forEach((acc, idx) => {
            // Creamos el tag visual
            const tag = document.createElement('span');
            tag.className = 'tag is-link is-light'; // Estilo azul suave aesthetic
            tag.style.marginRight = '5px';
            tag.style.marginBottom = '5px';
            tag.innerHTML = `${acc} <button class="delete is-small" type="button" data-idx="${idx}"></button>`;
            containerTags.appendChild(tag);
        });
        // Actualizamos el input oculto (texto separado por comas)
        inpFinal.value = seleccionados.join(', ');
    }

    // 2. Función para agregar un item
    function agregar(valor) {
        const v = valor.trim();
        if (v && !seleccionados.includes(v)) {
            seleccionados.push(v);
            render();
        }
        inpTemp.value = ''; // Limpiar input
    }

    // --- EVENTOS ---

    // Mostrar panel al enfocar el input
    inpTemp.addEventListener('focus', () => {
        panel.style.display = 'block';
    });

    // Ocultar panel al hacer clic fuera
    document.addEventListener('click', (e) => {
        if (!inpTemp.contains(e.target) && !panel.contains(e.target)) {
            panel.style.display = 'none';
        }
    });

    // Agregar al dar Enter (lo escrito manualmente)
    inpTemp.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault(); // No enviar form
            agregar(inpTemp.value);
        }
    });

    // Agregar al dar clic en una opción de la tablita
    opciones.forEach(opcion => {
        opcion.addEventListener('click', () => {
            agregar(opcion.textContent);
            // Opcional: No cerrar el panel para permitir selección múltiple rápida
            // panel.style.display = 'none'; 
            inpTemp.focus();
        });
        
        // Efecto visual al pasar mouse (opcional si ya tienes CSS de hover)
        opcion.addEventListener('mouseover', () => {
            opcion.classList.remove('is-white');
            opcion.classList.add('is-link');
        });
        opcion.addEventListener('mouseout', () => {
            opcion.classList.remove('is-link');
            opcion.classList.add('is-white');
        });
    });

    // Borrar tag al dar clic en la X
    containerTags.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete')) {
            const index = e.target.dataset.idx;
            seleccionados.splice(index, 1);
            render();
        }
    });
});
</script>