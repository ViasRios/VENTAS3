<style>
    /* ESTILOS AESTHETIC - FORMULARIO DE ACTUALIZACIÓN */
    .invoice-container { font-family: 'Poppins', sans-serif; }

    /* Tarjeta Principal */
    .form-card { 
        background: white; 
        border-radius: 15px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
        overflow: hidden; 
        border: 1px solid rgba(0,0,0,0.02);
    }
    
    .form-card-header { 
        background: linear-gradient(135deg, #1d4d80 0%, #245c94 100%);
        color: white; 
        padding: 20px 30px; 
        font-weight: 600; 
        font-size: 1.1rem; 
        display: flex; align-items: center; justify-content: space-between;
    }

    .form-card-body { padding: 30px; background-color: #fff; }

    /* Títulos de Sección */
    .section-title {
        color: #1d4d80;
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 10px;
        margin-bottom: 20px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-icon {
        background: #eef6fc;
        color: #1d4d80;
        width: 30px; height: 30px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        font-size: 0.8rem;
    }

    /* Inputs Personalizados */
    .input, .select select, .textarea {
        box-shadow: none !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        transition: all 0.3s;
        height: 2.7em;
    }
    .input:focus, .select select:focus, .textarea:focus {
        border-color: #1d4d80 !important;
        box-shadow: 0 0 0 3px rgba(29, 77, 128, 0.1) !important;
    }
    .label { color: #64748b; font-weight: 500; font-size: 0.9rem; }
    .input[readonly] { background-color: #f8fafc; color: #888; cursor: not-allowed; }

    /* Botones */
    .btn-update {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); /* Verde para actualizar */
        border: none;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
        transition: transform 0.2s;
        color: white;
    }
    .btn-update:hover { transform: translateY(-2px); color: white; }
    
</style>

<div class="container is-fluid mb-1 mt-1 invoice-container">

    <!-- Encabezado con Botón Regresar -->
    <div class="columns is-vcentered mb-4">
        <div class="column">
            <h1 class="title is-3" style="color: #1d4d80; font-weight: 800;">
                <i class="fas fa-sync-alt mr-2"></i> Actualizar Factura
            </h1>
            <p class="subtitle is-6" style="color: #888;">Edición de datos fiscales y estado del comprobante.</p>
        </div>
        <div class="column is-narrow">
            <a href="<?php echo APP_URL; ?>invoiceList/" class="button is-white is-rounded shadow-sm" style="color: #555;">
                <i class="fas fa-arrow-left mr-2"></i> Regresar
            </a>
        </div>
    </div>

    <div class="container pb-6">
    <?php
        use app\controllers\invoiceController;
        $insInvoice = new invoiceController();

        // 1. Obtener ID de la URL
        $id = (isset($url[1])) ? $insInvoice->limpiarCadena($url[1]) : 0;

        // 2. Buscar datos en la BD
        // Asumiendo tabla "facturas" y clave primaria "Idfactura"
        $datos = $insInvoice->seleccionarDatos("Unico", "facturas", "Idfactura", $id);

        if($datos->rowCount() == 1){
            $datos = $datos->fetch();
            
            // Helper para limpiar salida
            $h = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
    ?>

    <div class="form-card">
        <div class="form-card-header">
            <span>
                <i class="fas fa-file-invoice mr-2"></i> 
                Editando Factura #<?php echo $h($datos['Idfactura']); ?>
            </span>
            <span class="tag is-white is-light is-rounded" style="color: #1d4d80; font-weight: bold;">
                ODS: <?php echo $h($datos['Idods']); ?>
            </span>
        </div>

        <div class="form-card-body">
            
            <form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/invoiceAjax.php" method="POST" autocomplete="off">

                <input type="hidden" name="modulo_factura" value="actualizar">
                <input type="hidden" name="Idfactura" value="<?php echo $datos['Idfactura']; ?>">

                <!-- GRUPO A: DATOS DEL CLIENTE -->
                <div class="section-title">
                    <span class="section-icon"><i class="fas fa-user-check"></i></span>
                    Identificación del Cliente
                </div>

                <div class="columns">
                    <div class="column is-3">
                        <label class="label">ID ODS</label>
                        <div class="control has-icons-left">
                            <!-- ID ODS suele ser fijo en actualización, pero editable si lo requieres -->
                            <input class="input" type="number" name="Idods" value="<?php echo $h($datos['Idods']); ?>" required>
                            <span class="icon is-small is-left"><i class="fas fa-hashtag"></i></span>
                        </div>
                    </div>
                    <div class="column is-5">
                        <label class="label">Nombre / Razón Social</label>
                        <div class="control has-icons-left">
                            <input class="input" type="text" name="Nombre" maxlength="150" required
                                oninput="this.value=this.value.toUpperCase()"
                                value="<?php echo $h($datos['Nombre']); ?>">
                            <span class="icon is-small is-left"><i class="fas fa-building"></i></span>
                        </div>
                    </div>
                     <div class="column is-4">
                        <label class="label">Correo Electrónico</label>
                        <div class="control has-icons-left">
                            <input class="input" type="email" name="correo" maxlength="150" required 
                                value="<?php echo $h($datos['correo']); ?>">
                            <span class="icon is-small is-left"><i class="fas fa-envelope"></i></span>
                        </div>
                    </div>
                </div>

                <!-- GRUPO B: DATOS FISCALES -->
                <div class="section-title">
                    <span class="section-icon"><i class="fas fa-landmark"></i></span>
                    Datos Fiscales (SAT)
                </div>

                <div class="columns">
                    <div class="column is-4">
                        <label class="label">RFC</label>
                        <div class="control has-icons-left">
                            <input class="input" type="text" name="rfc" maxlength="13" 
                                oninput="this.value=this.value.toUpperCase()" 
                                value="<?php echo $h($datos['rfc']); ?>">
                            <span class="icon is-small is-left"><i class="fas fa-id-card"></i></span>
                        </div>
                    </div>
                    <div class="column is-5">
                        <label class="label">Régimen Fiscal</label>
                         <div class="control has-icons-left">
                            <input class="input" type="text" name="regimenFiscal" maxlength="100" 
                                placeholder="Ej: Régimen General..." 
                                value="<?php echo $h($datos['regimenFiscal']); ?>">
                            <span class="icon is-small is-left"><i class="fas fa-balance-scale"></i></span>
                        </div>
                    </div>
                    <div class="column is-3">
                        <label class="label">Código Postal</label>
                         <div class="control has-icons-left">
                            <input class="input" type="text" name="codigoPostal" maxlength="5" pattern="[0-9]{5}"
                                value="<?php echo $h($datos['codigoPostal']); ?>">
                            <span class="icon is-small is-left"><i class="fas fa-map-marker-alt"></i></span>
                        </div>
                    </div>
                </div>

                <!-- GRUPO C: DETALLES DE PAGO -->
                <div class="section-title">
                    <span class="section-icon"><i class="fas fa-money-check-alt"></i></span>
                    Detalles del Comprobante
                </div>

                <div class="columns">
                    <div class="column is-4">
                        <label class="label">Tipo de Pago</label>
                        <div class="control has-icons-left">
                            <div class="select is-fullwidth">
                                <select name="tipoPago" required>
                                    <option value="01" <?php echo ($datos['tipoPago']=='01'?'selected':''); ?>>Efectivo</option>
                                    <option value="03" <?php echo ($datos['tipoPago']=='03'?'selected':''); ?>>Transferencia electrónica</option>
                                    <option value="04" <?php echo ($datos['tipoPago']=='04'?'selected':''); ?>>Tarjeta</option>
                                </select>
                            </div>
                            <span class="icon is-small is-left"><i class="fas fa-coins"></i></span>
                        </div>
                    </div>
                    <div class="column is-4">
                        <label class="label">Uso CFDI</label>
                        <div class="control has-icons-left">
                            <div class="select is-fullwidth">
                                <select name="CFDI">
                                    <?php 
                                      $cfdis = [
                                          'G03' => 'G03 - Gastos en general',
                                          'G01' => 'G01 - Adquisición de mercancías',
                                          'S01' => 'S01 - Sin efectos fiscales',
                                          'I04' => 'I04 - Equipo de computo y accesorios',
                                          'D01' => 'D01 - Honorarios médicos y dentales',
                                          'P01' => 'P01 - Por definir'
                                      ];
                                      foreach($cfdis as $key => $val){
                                          $sel = ($datos['CFDI'] == $key) ? 'selected' : '';
                                          echo "<option value='$key' $sel>$val</option>";
                                      }
                                    ?>
                                </select>
                            </div>
                            <span class="icon is-small is-left"><i class="fas fa-file-invoice"></i></span>
                        </div>
                    </div>
                    <div class="column is-4">
                        <label class="label">Estado de Factura</label>
                        <div class="control has-icons-left">
                            <div class="select is-fullwidth">
                                <select name="Estadofac">
                                    <option value="1" <?php echo ($datos['Estadofac']=='1'?'selected':''); ?>>Pendiente</option>
                                    <option value="2" <?php echo ($datos['Estadofac']=='2'?'selected':''); ?>>Realizada</option>
                                </select>
                            </div>
                            <span class="icon is-small is-left"><i class="fas fa-tasks"></i></span>
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column is-12">
                         <label class="label">Notas Adicionales</label>
                         <div class="control has-icons-left">
                            <input class="input" type="text" name="Datosfac" maxlength="255" 
                                placeholder="Cualquier observación relevante..."
                                value="<?php echo $h($datos['Datosfac']); ?>">
                            <span class="icon is-small is-left"><i class="fas fa-sticky-note"></i></span>
                         </div>
                    </div>
                </div>

                <!-- BOTONES -->
                <div class="has-text-right mt-4">
                    <a href="<?php echo APP_URL; ?>invoiceList/" class="button is-light is-rounded mr-2">
                        Cancelar
                    </a>
                    <button type="submit" class="button btn-update is-rounded">
                        <i class="fas fa-check-circle mr-2"></i> Actualizar Datos
                    </button>
                </div>

            </form>
        </div>
    </div>

    <?php
        } else {
            include "./app/views/inc/error_alert.php";
        }
    ?>
    </div>
</div>