<?php
    // 1. SEGURIDAD DE CAJA
    if(!isset($_SESSION['caja_activada']) || $_SESSION['caja_activada'] !== true){
        echo '<script> window.location.href="'.APP_URL.'login/"; </script>';
        exit();
    }
    
    $siguiente_idini = $_SESSION['siguiente_idini'] ?? 1; 

    // 2. OBTENER LISTA DE PERSONAL (Para el Select)
    // Usamos loginController ya que hereda de mainModel y nos permite hacer consultas
    use app\controllers\loginController;
    $insLogin = new loginController();

    // Consultamos id y nombre de todos los empleados habilitados
    $datos_personal = $insLogin->ejecutarConsulta("SELECT Idasesor, Nombre, Puesto FROM personal WHERE habilitado = 1 ORDER BY Nombre ASC");
    $lista_personal = $datos_personal->fetchAll();

    // Obtenemos el ID del usuario actual en sesión para pre-seleccionarlo
    $id_usuario_actual = $_SESSION['id'] ?? 0;
?>

<style>
    .invoice-container { font-family: 'Poppins', sans-serif; }
    /* ... (TUS ESTILOS CSS EXISTENTES SE MANTIENEN IGUAL) ... */
    .form-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid rgba(0,0,0,0.02); margin-bottom: 2rem; }
    .form-card-header { background: linear-gradient(135deg, #1d4d80 0%, #245c94 100%); color: white; padding: 20px 30px; font-weight: 600; font-size: 1.1rem; display: flex; align-items: center; justify-content: space-between; }
    .form-card-body { padding: 30px; background-color: #fff; }
    .section-title { color: #1d4d80; font-size: 0.95rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 25px; margin-top: 15px; display: flex; align-items: center; gap: 10px; }
    .input, .select select, .textarea { box-shadow: none !important; border: 1px solid #e2e8f0 !important; border-radius: 8px !important; transition: all 0.3s; height: 2.7em; }
    .input:focus, .select select:focus { border-color: #1d4d80 !important; box-shadow: 0 0 0 3px rgba(29, 77, 128, 0.1) !important; }
    .input[disabled], .select select[disabled] { background-color: #f8f9fa; color: #888; border-color: #eee !important; }
    .label { color: #64748b; font-weight: 500; font-size: 0.9rem; }
    .table-container { border: 1px solid #f1f5f9; border-radius: 8px; }
    .table thead th { background-color: #f8fbff; color: #1d4d80; border-bottom: 2px solid #e2e8f0; }
    .table tfoot th { background-color: #fff; border-top: 2px solid #1d4d80; }
    .btn-gradient { background: linear-gradient(135deg, #1d4d80 0%, #245c94 100%); border: none; color: white; transition: transform 0.2s; }
    .btn-gradient:hover { transform: translateY(-2px); color: white; opacity: 0.9; }
    .producto-item { cursor: pointer; transition: background 0.2s; border: 1px solid #eee; margin-bottom: 5px; padding: 10px; border-radius: 6px; }
    .producto-item:hover { background-color: #eef6fc !important; border-color: #a5c3e3; }
</style>

<div class="container is-fluid mb-4 mt-4 invoice-container">

    <div class="columns is-vcentered mb-4">
        <div class="column">
            <h1 class="title is-3" style="color: #1d4d80; font-weight: 800;">
                <i class="fas fa-cash-register mr-2"></i> Venta
            </h1>
            <p class="subtitle is-6" style="color: #888;">Gestión de caja y cobro de productos.</p>
        </div>
    </div>

    <div class="form-card">
        
        <div class="form-card-header">
            <span><i class="fas fa-file-invoice-dollar mr-2"></i> Nueva Venta</span>
            <span class="tag is-white is-light" style="color: #1d4d80; font-weight:bold;">
                <?php echo date('d/m/Y'); ?>
            </span>
        </div>

        <div class="form-card-body">

            <div class="section-title">
                <i class="fas fa-user-tag"></i> Datos de Sesión & Caja
            </div>

            <div class="columns is-vcentered mb-5">
                
                <div class="column is-4">
                    <div class="control">
                        <label class="label">Asesor / Vendedor <?php echo CAMPO_OBLIGATORIO; ?></label>
                        <div class="select is-fullwidth">
                            <select name="id_asesor" id="id_asesor">
                                <option value="" disabled>Seleccione una opción</option>
                                <?php
                                    if(count($lista_personal) > 0){
                                        foreach($lista_personal as $personal){
                                            // Si el ID del personal coincide con el de la sesión, lo seleccionamos
                                            $selected = ($personal['Idasesor'] == $id_usuario_actual) ? 'selected' : '';
                                            echo '<option value="'.$personal['Idasesor'].'" '.$selected.'>'.$personal['Nombre'].'</option>';
                                        }
                                    } else {
                                        echo '<option value="">No hay personal registrado</option>';
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="column is-3">
                    <div class="control">
                        <label class="label">Efectivo Inicial <?php echo CAMPO_OBLIGATORIO; ?></label>
                        <div class="field has-addons">
                            <p class="control is-expanded">
                                <input class="input" type="text" name="Efectivo" id="efectivo"
                                    pattern="^\d{1,9}(\.\d{1,2})?$" maxlength="20" value="0.00" required>
                            </p>
                            <p class="control">
                                <button type="button" id="btn_abrir_caja" class="button is-success">
                                    <i class="fas fa-check"></i>
                                </button>
                            </p>
                        </div>
                        <p id="mensaje-error" class="help is-danger is-hidden">
                            <i class="fas fa-exclamation-triangle"></i> ¡Ingresa el efectivo inicial!
                        </p>
                    </div>
                </div>
                
                <div class="column">
                    <article class="message is-info is-small" style="border-radius: 8px;">
                        <div class="message-body" style="border-radius: 8px; background-color: #f0f7ff; color: #1d4d80; border:none;">
                            <strong>Nota:</strong> Debe confirmar el vendedor y abrir la caja antes de agregar productos.
                        </div>
                    </article>
                </div>
            </div>

            <div class="section-title">
                <i class="fas fa-cart-plus"></i> Agregar Productos
            </div>

            <div class="columns">
                <div class="column is-4">
                    <div class="control">
                        <label class="label">Buscar Producto <?php echo CAMPO_OBLIGATORIO; ?></label>
                        <input class="input" type="text" name="producto" id="producto" autocomplete="off" placeholder="Escriba para buscar..." disabled>
                        <div id="producto-lista" class="lista-autocompletado" style="position:absolute; z-index:100; width:100%;"></div>
                    </div>
                </div>
                <div class="column is-2">
                    <div class="control">
                        <label class="label">Cantidad <?php echo CAMPO_OBLIGATORIO; ?></label>
                        <input class="input" type="number" id="cantidad" name="cantidad" required min="1" step="1" disabled>
                    </div>
                </div>
                <div class="column is-2">
                    <div class="control">
                        <label class="label">Costo Unit.</label>
                        <input class="input" id="costo" name="costo" type="text" value="0.00" readonly disabled style="background-color: #f8f9fa;">
                    </div>
                </div>
                <div class="column is-2">
                    <div class="control">
                        <label class="label">IVA (16%)</label>
                        <input class="input" id="iva" name="iva" type="text" value="0.16" disabled style="background-color: #f8f9fa;">
                    </div>
                </div>
                 <div class="column is-2">
                    <div class="control">
                        <label class="label">&nbsp;</label>
                        <button type="button" id="btn_agregar_producto" class="button btn-gradient is-fullwidth" disabled>
                            <i class="fas fa-plus"></i>&nbsp; Agregar
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="columns mb-5">
                <div class="column">
                    <div class="control">
                        <label class="label">Descripción / Detalles</label>
                        <input class="input" id="descripcion" name="descripcion" type="text" disabled placeholder="Detalles adicionales del producto...">
                    </div>
                </div>
            </div>

            <div class="table-container mb-6">
                <table class="table is-fullwidth is-hoverable" id="tabla_productos">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Costo</th>
                            <th>Subtotal</th>
                            <th>IVA</th>
                            <th>Total</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="has-text-right" style="color:#64748b;">Subtotal</th>
                            <th id="subtotal_general" style="color:#1d4d80;">$0.00</th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="6" class="has-text-right" style="color:#64748b;">IVA Total</th>
                            <th id="iva_total" style="color:#1d4d80;">$0.00</th>
                            <th></th>
                        </tr>
                        <tr style="background-color: #f8fbff;">
                            <th colspan="6" class="has-text-right is-size-5">Total a Pagar</th>
                            <th id="total_pagar" class="is-size-5" style="color:#1d4d80;">$0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="seccion_pago" style="display: none;">
                
                <div class="section-title" style="color: #245c94; border-color: #a5c3e3;">
                    <i class="fas fa-wallet"></i> Procesar Pago
                </div>

                <div class="columns is-vcentered box" style="background-color: #f8fbff; box-shadow:none; border: 1px dashed #a5c3e3;">
                    <div class="column is-4 has-text-centered">
                        <p class="heading">Total Venta</p>
                        <input class="input is-large has-text-centered" id="total_pagar_display" type="text" value="$0.00" readonly 
                               style="border:none; background:transparent; font-weight:800; color:#1d4d80; box-shadow:none !important;">
                    </div>
                    <div class="column is-4 has-text-centered" style="border-left: 1px solid #ddd; border-right: 1px solid #ddd;">
                        <p class="heading">Restante</p>
                        <input class="input is-large has-text-centered has-text-danger" id="total_restante_display" type="text" value="$0.00" readonly
                               style="border:none; background:transparent; font-weight:800; box-shadow:none !important;">
                    </div>
                    <div class="column is-4 has-text-centered">
                        <p class="heading">Cambio</p>
                        <input class="input is-large has-text-centered has-text-success" id="cambio_display" type="text" value="$0.00" readonly
                               style="border:none; background:transparent; font-weight:800; box-shadow:none !important;">
                    </div>
                </div>

                <div class="columns is-vcentered mt-4">
                    <div class="column is-4">
                        <div class="control">
                            <label class="label">Método de Pago</label>
                            <div class="select is-fullwidth">
                                <select id="metodo_pago_select">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Tarjeta">Tarjeta de Crédito/Débito</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="column is-4">
                        <div class="control">
                            <label class="label">Monto Recibido</label>
                            <input class="input" type="text" id="monto_pago_input" pattern="^\d{1,9}(\.\d{1,2})?$" placeholder="0.00">
                        </div>
                    </div>
                    <div class="column is-4">
                        <label class="label">&nbsp;</label>
                        <button type="button" id="btn_agregar_pago" class="button is-info is-light is-fullwidth" style="border: 1px solid #3e8ed0;">
                            <i class="fas fa-plus-circle"></i>&nbsp; Agregar Pago
                        </button>
                    </div>
                </div>

                <div class="columns">
                    <div class="column is-8">
                         <h5 class="subtitle is-6 mb-2 text-muted">Pagos Aplicados</h5>
                         <table class="table is-bordered is-fullwidth is-narrow" id="tabla_pagos_aplicados">
                            <thead>
                                <tr style="background:#f9f9f9;">
                                    <th>Método</th>
                                    <th>Monto</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="column is-4 is-flex is-align-items-end">
                        <button type="button" id="btn_finalizar_venta" class="button btn-gradient is-large is-fullwidth" disabled>
                            <i class="fas fa-check-circle"></i>&nbsp; FINALIZAR VENTA
                        </button>
                    </div>
                </div>
            </div>
            </div> 
    </div> 
</div>

<script>
    // Flag to track if the "caja" is open
    let cajaAbierta = false;
    
    // When the "Abrir Caja" button is clicked
    document.getElementById("btn_abrir_caja").addEventListener("click", function() {
        const efectivo = document.getElementById("efectivo").value;
        // VALIDAMOS QUE TAMBIÉN HAYA UN ASESOR SELECCIONADO
        const asesor = document.getElementById("id_asesor").value;
        
        if(asesor === "" || asesor === null){
            alert("Por favor, selecciona un Asesor / Vendedor.");
            document.getElementById("id_asesor").focus();
            return;
        }

        if (efectivo && !isNaN(efectivo) && parseFloat(efectivo) >= 0) { // Permitimos 0 si abre sin fondo
            cajaAbierta = true;
            document.getElementById("mensaje-error").classList.add("is-hidden");
            
            // Cambio visual en el botón
            const btn = document.getElementById("btn_abrir_caja");
            btn.className = "button is-success is-outlined";
            btn.innerHTML = '<i class="fas fa-lock-open"></i>';
            
            // Bloqueamos los controles iniciales para que no se cambien a mitad de venta
            document.getElementById("btn_abrir_caja").disabled = true;
            document.getElementById("efectivo").disabled = true; 
            document.getElementById("id_asesor").disabled = true; // Bloqueamos el asesor también
            
            habilitarCampos(true);
        } else {
            document.getElementById("mensaje-error").classList.remove("is-hidden");
            cajaAbierta = false;
        }
    });

    // Función para habilitar o deshabilitar campos del formulario
    function habilitarCampos(habilitar) {
        document.getElementById("producto").disabled = !habilitar;
        document.getElementById("cantidad").disabled = !habilitar;
        document.getElementById("costo").disabled = !habilitar;
        document.getElementById("iva").disabled = !habilitar; 
        document.getElementById("descripcion").disabled = !habilitar; 
        document.getElementById("btn_agregar_producto").disabled = !habilitar;
        
        if(habilitar) document.getElementById("producto").focus();
    }

    // (EL RESTO DEL JAVASCRIPT SE MANTIENE EXACTAMENTE IGUAL AL ANTERIOR)
    // ... búsqueda de producto ...
    // ... agregar producto a tabla ...
    // ... cálculos de totales ...
    // ... lógica de pagos ...

    document.getElementById("producto").addEventListener("input", function() {
        let query = this.value;
        if (query.length > 1) {
            fetch("<?php echo APP_URL; ?>app/ajax/buscarProducto.php?query=" + query)
                .then(response => { if (!response.ok) { throw new Error("Error: " + response.status); } return response.json(); })
                .then(data => {
                    let lista = document.getElementById("producto-lista");
                    lista.innerHTML = '';
                    if (data.error) { lista.innerHTML = '<div class="box p-2 has-text-danger">Error: ' + data.error + '</div>'; return; }
                    if (data.length > 0) {
                        data.forEach(producto => {
                            let item = document.createElement("div");
                            item.style.background = "#fff"; item.style.border = "1px solid #ddd"; item.style.padding = "10px"; item.style.cursor = "pointer"; item.style.borderBottom = "1px solid #eee";
                            item.innerHTML = `<strong>${producto.producto}</strong> <span class="tag is-light is-info is-pulled-right">$${producto.precio_venta}</span>`;
                            item.addEventListener("mouseenter", function() { this.style.background = "#f0f7ff"; });
                            item.addEventListener("mouseleave", function() { this.style.background = "#fff"; });
                            item.addEventListener("click", function() {
                                document.getElementById("producto").value = producto.producto;
                                document.getElementById("costo").value = producto.precio_venta;
                                document.getElementById("descripcion").value = producto.descripcion; 
                                lista.innerHTML = ''; 
                                document.getElementById("cantidad").focus();
                            });
                            lista.appendChild(item);
                        });
                    } else { lista.innerHTML = '<div class="box p-2 is-size-7 has-text-grey">No se encontraron productos</div>'; }
                })
                .catch(error => { console.error('Error al obtener los productos:', error); });
        } else { document.getElementById("producto-lista").innerHTML = ''; }
    });

    const tablaProductosBody = document.getElementById('tabla_productos').getElementsByTagName('tbody')[0];
    document.getElementById('btn_agregar_producto').addEventListener('click', function() {
        const nombreProd = document.getElementById('producto').value;
        const cantidad = parseFloat(document.getElementById('cantidad').value) || 0;
        const costoUnitario = parseFloat(document.getElementById('costo').value.replace(/[$,]/g, '')) || 0;
        const ivaDecimal = parseFloat(document.getElementById('iva').value) || 0.16;
        if (cantidad <= 0 || costoUnitario <= 0 || nombreProd.trim() === "") { alert("Datos inválidos."); return; }
        const subtotalLinea = cantidad * costoUnitario;
        const ivaLinea = subtotalLinea * ivaDecimal;
        const totalLinea = subtotalLinea + ivaLinea;
        const newRow = tablaProductosBody.insertRow();
        newRow.dataset.subtotal = subtotalLinea.toFixed(2); newRow.dataset.iva = ivaLinea.toFixed(2); newRow.dataset.total = totalLinea.toFixed(2);
        newRow.innerHTML = `<th>${tablaProductosBody.rows.length}</th><td>${nombreProd}</td><td>${cantidad}</td><td>$${costoUnitario.toFixed(2)}</td><td>$${subtotalLinea.toFixed(2)}</td><td>$${ivaLinea.toFixed(2)}</td><td class="has-text-weight-bold" style="color:#1d4d80;">$${totalLinea.toFixed(2)}</td><td><button class="button is-danger is-light is-small btn-eliminar-producto" style="border-radius:50%;"><i class="fas fa-times"></i></button></td>`;
        actualizarTotalesGenerales(); limpiarCamposProducto();
    });

    function actualizarTotalesGenerales() {
        let subtotalGeneral = 0.00; let ivaTotal = 0.00; let totalPagar = 0.00;
        for (let row of tablaProductosBody.rows) { subtotalGeneral += parseFloat(row.dataset.subtotal); ivaTotal += parseFloat(row.dataset.iva); totalPagar += parseFloat(row.dataset.total); }
        document.getElementById('subtotal_general').textContent = '$' + subtotalGeneral.toFixed(2);
        document.getElementById('iva_total').textContent = '$' + ivaTotal.toFixed(2);
        document.getElementById('total_pagar').textContent = '$' + totalPagar.toFixed(2);
    }
    function limpiarCamposProducto() { document.getElementById('producto').value = ''; document.getElementById('cantidad').value = ''; document.getElementById('descripcion').value = ''; document.getElementById('producto').focus(); }
    tablaProductosBody.addEventListener('click', function(event) { if (event.target.closest('.btn-eliminar-producto')) { event.target.closest('.btn-eliminar-producto').closest('tr').remove(); actualizarTotalesGenerales(); } });

    // LÓGICA DE PAGOS
    let pagosAplicados = []; let totalAPagar = 0.00;
    const seccionPago = document.getElementById('seccion_pago');
    const totalPagarElemento = document.getElementById('total_pagar');
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            totalAPagar = parseFloat(mutation.target.textContent.replace(/[$,]/g, '')) || 0.00;
            if (totalAPagar > 0) { seccionPago.style.display = 'block'; seccionPago.scrollIntoView({behavior: "smooth"}); } else { seccionPago.style.display = 'none'; }
            actualizarCalculosDePago();
        });
    });
    observer.observe(totalPagarElemento, { childList: true, subtree: true, characterData: true });

    document.getElementById('btn_agregar_pago').addEventListener('click', function() {
        const metodo = document.getElementById('metodo_pago_select').value;
        let monto = parseFloat(document.getElementById('monto_pago_input').value.replace(/[$,]/g, '')) || 0.00;
        if (monto <= 0) { alert("Monto inválido."); return; }
        pagosAplicados.push({ metodo: metodo, monto: monto });
        document.getElementById('monto_pago_input').value = '';
        actualizarCalculosDePago();
    });

    function actualizarCalculosDePago() {
        let totalPagado = 0.00; pagosAplicados.forEach(pago => { totalPagado += pago.monto; });
        let restante = totalAPagar - totalPagado; let cambio = 0.00;
        if (restante < 0) { cambio = -restante; restante = 0; }
        document.getElementById('total_pagar_display').value = '$' + totalAPagar.toFixed(2);
        document.getElementById('total_restante_display').value = '$' + restante.toFixed(2);
        document.getElementById('cambio_display').value = '$' + cambio.toFixed(2);
        const trd = document.getElementById('total_restante_display');
        trd.classList.toggle('has-text-danger', restante > 0); trd.classList.toggle('has-text-success', restante === 0);
        renderizarTablaPagos();
        document.getElementById('btn_finalizar_venta').disabled = !(restante === 0 && totalAPagar > 0);
    }

    function renderizarTablaPagos() {
        const tBody = document.getElementById('tabla_pagos_aplicados').getElementsByTagName('tbody')[0];
        tBody.innerHTML = '';
        pagosAplicados.forEach((pago, index) => {
            const row = tBody.insertRow();
            row.innerHTML = `<td>${pago.metodo}</td><td>$${pago.monto.toFixed(2)}</td><td><button class="button is-danger is-inverted is-small btn-eliminar-pago" data-index="${index}"><i class="fas fa-times"></i></button></td>`;
        });
        document.querySelectorAll('.btn-eliminar-pago').forEach(button => { button.addEventListener('click', function() { pagosAplicados.splice(parseInt(this.getAttribute('data-index')), 1); actualizarCalculosDePago(); }); });
    }

    document.getElementById('btn_finalizar_venta').addEventListener('click', function() {
        // AQUÍ PUEDES CAPTURAR EL ID DEL ASESOR PARA ENVIARLO
        const idAsesor = document.getElementById("id_asesor").value;
        console.log("Asesor ID:", idAsesor);
        
        alert("¡Venta Finalizada!\nAsesor: " + idAsesor + "\nTotal: $" + totalAPagar.toFixed(2));
        resetearFormularioVenta();
    });

    function resetearFormularioVenta() {
        tablaProductosBody.innerHTML = ''; 
        document.getElementById('subtotal_general').textContent = '$0.00';
        document.getElementById('iva_total').textContent = '$0.00';
        document.getElementById('total_pagar').textContent = '$0.00'; 
        pagosAplicados = []; totalAPagar = 0.00; actualizarCalculosDePago(); 
        document.getElementById('producto').value = '';
        
        // Desbloquear campos para nueva venta
        document.getElementById("btn_abrir_caja").disabled = false;
        document.getElementById("btn_abrir_caja").className = "button is-success";
        document.getElementById("btn_abrir_caja").innerHTML = '<i class="fas fa-check"></i>';
        
        document.getElementById("efectivo").disabled = false;
        document.getElementById("id_asesor").disabled = false;
        document.getElementById("id_asesor").focus();
        habilitarCampos(false);
    }
</script>