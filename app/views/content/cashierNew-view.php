<?php $siguiente_idini = $_SESSION['siguiente_idini'] ?? 1; ?>
<div class="container is-fluid mb-2 mt-0">
    <h1 class="title">Caja</h1>
    <h2 class="subtitle"><i class="fas fa-cash-register fa-fw"></i> &nbsp; Nueva caja</h2>
</div>
<?php $siguiente_idini = $_SESSION['siguiente_idini'] ?? 1; ?>

<div class="box" style="background:#a6cff4;">
    <div class="columns is-vcentered">
        <div class="column pb-0 pt-5">
            <div class="control">
                <button type="button" id="btn_abrir_caja" class="button is-success is-fullwidth">
                    Abrir Caja
                </button>
            </div>
        </div>
        <div class="column pt-2 pb-1">
            <div class="control">
                <label>Efectivo en caja <?php echo CAMPO_OBLIGATORIO; ?></label>
                <input class="input" type="text" name="Efectivo" id="efectivo"
                    pattern="^\d{1,9}(\.\d{1,2})?$"
                    maxlength="20"
                    value="0.00"
                    required>
            </div>
        </div>
        <div class="column is-6">
            <div class="control">
                <p id="mensaje-error" class="has-text-danger is-hidden">¡Ingresa el efectivo de la caja!</p>
            </div>
        </div>
    </div>

    <div class="box" style="background:#f0f8ff;">
        <h3 class="title is-4">Agregar Productos</h3>
    
        <div class="columns">
            <div class="column">
                <div class="control">
                    <label>Producto o Servicio<?php echo CAMPO_OBLIGATORIO; ?></label>
                    <div id="producto-lista" class="lista-autocompletado"></div>

                    <input class="input" type="text" name="producto" id="producto" autocomplete="off" placeholder="Buscar producto..." disabled>
                </div>
            </div>
            <div class="column">
                <div class="control">
                    <label>Cantidad <?php echo CAMPO_OBLIGATORIO; ?></label>
                    <input class="input" type="number" id="cantidad" name="cantidad" required min="1" step="1" disabled>
                </div>
            </div>
            <div class="column">
                <div class="control">
                    <label>Costo <?php echo CAMPO_OBLIGATORIO; ?></label>
                    <input class="input" id="costo" name="costo" type="text" value="0.00" readonly disabled>
                </div>
            </div>
            <div class="column">
                <div class="control">
                    <label>IVA (16%)</label>
                    <input class="input" id="iva" name="iva" type="text" value="0.16" pattern="^\d{1,}(\.\d{1,4})?$" disabled>
                </div>
            </div>
        </div>
        <div class="columns">
            <div class="column">
                <label>Descripcion</label>
                    <input class="input" id="descripcion" name="descripcion" type="text" value="" disabled>
            </div>
        </div>
        <div class="columns">
            <div class="column is-3">
                <button type="button" id="btn_agregar_producto" class="button is-info is-fullwidth" disabled>
                    <i class="fas fa-plus"></i>&nbsp; Agregar Producto
                </button>
            </div>
        </div>

    </div> <div class="table-container">
        <table class="table is-fullwidth is-striped is-hoverable" id="tabla_productos">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Costo</th>
                    <th>Subtotal</th>
                    <th>IVA</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th colspan="6" class="has-text-right">Subtotal</th>
                    <th id="subtotal_general">$0.00</th>
                    <th></th>
                </tr>
                <tr>
                    <th colspan="6" class="has-text-right">IVA</th>
                    <th id="iva_total">$0.00</th>
                    <th></th>
                </tr>
                <tr>
                    <th colspan="6" class="has-text-right">Total</th>
                    <th id="total_pagar">$0.00</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="box" id="seccion_pago" style="background:#f0f8ff; display: none;">
        <h3 class="title is-4">Procesar Pago</h3>
        <div class="columns is-vcentered">
            <div class="column is-3">
                <div class="control">
                    <label class="label">Total de la Venta:</label>
                    <input class="input is-large" id="total_pagar_display" type="text" value="$0.00" readonly style="font-weight: bold; background: #eee;">
                </div>
            </div>
            <div class="column is-3">
                <div class="control">
                    <label class="label">Restante por Pagar:</label>
                    <input class="input is-large has-text-danger" id="total_restante_display" type="text" value="$0.00" readonly style="font-weight: bold;">
                </div>
            </div>
            <div class="column is-3">
                <div class="control">
                    <label class="label">Cambio:</label>
                    <input class="input is-large has-text-success" id="cambio_display" type="text" value="$0.00" readonly style="font-weight: bold;">
                </div>
            </div>
        </div>
        <hr>
        <div class="columns is-vcentered">
             <div class="column is-4">
                <div class="control">
                    <label class="label">Método de Pago</label>
                    <div class="select is-fullwidth">
                        <select id="metodo_pago_select">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="column is-4">
                <div class="control">
                    <label class="label">Monto</label>
                    <input class="input" type="text" id="monto_pago_input" pattern="^\d{1,9}(\.\d{1,2})?$" placeholder="0.00">
                </div>
            </div>
            <div class="column is-4">
                <label class="label">&nbsp;</label> <div class="control">
                    <button type="button" id="btn_agregar_pago" class="button is-info is-fullwidth">
                        <i class="fas fa-plus"></i>&nbsp; Agregar Pago
                    </button>
                </div>
            </div>
        </div>
        <h4 class="subtitle is-5 mt-4">Pagos Aplicados</h4>
        <div class="table-container">
             <table class="table is-fullwidth is-striped" id="tabla_pagos_aplicados">
                <thead>
                    <tr>
                        <th>Método</th>
                        <th>Monto</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
        <div class="control mt-5">
            <button type="button" id="btn_finalizar_venta" class="button is-primary is-large is-fullwidth" disabled>
                <i class="fas fa-check"></i>&nbsp; Finalizar Venta
            </button>
        </div>
    </div>
</div>

<script>
    // Flag to track if the "caja" is open
    let cajaAbierta = false;
    // When the "Abrir Caja" button is clicked
    document.getElementById("btn_abrir_caja").addEventListener("click", function() {
        const efectivo = document.getElementById("efectivo").value;
        if (efectivo && !isNaN(efectivo) && parseFloat(efectivo) > 0) {
            cajaAbierta = true;
            document.getElementById("mensaje-error").classList.add("is-hidden");
            alert("Caja abierta correctamente.");
            document.getElementById("btn_abrir_caja").disabled = true;
            document.getElementById("efectivo").disabled = true; // Deshabilitar también el efectivo inicial
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
        document.getElementById("iva").disabled = !habilitar; // Puedes dejarla o quitarla si no la usas
        document.getElementById("descripcion").disabled = !habilitar; // Añadido
        document.getElementById("btn_agregar_producto").disabled = !habilitar;
    }

    // Función para buscar productos mientras se escribe
    document.getElementById("producto").addEventListener("input", function() {
        let query = this.value;
        if (query.length > 1) {
            fetch("<?php echo APP_URL; ?>app/ajax/buscarProducto.php?query=" + query)
                .then(response => {
                    // Primero, verificamos si la respuesta es JSON válido
                    if (!response.ok) {
                        throw new Error("Error en la red o el servidor: " + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    let lista = document.getElementById("producto-lista");
                    lista.innerHTML = '';
                    // Manejar si PHP nos envió un error JSON
                    if (data.error) {
                         console.error('Error desde PHP:', data.error);
                         lista.innerHTML = '<div class="box" style="background:#f3f3f3;">Error: ' + data.error + '</div>';
                         return;
                    }
                    if (data.length > 0) {
                        data.forEach(producto => {
                            // ===== ¡AQUÍ ESTÁ LA CORRECCIÓN! =====                            
                            let item = document.createElement("div");
                            item.classList.add("box");
                            item.style.background = "#c7dfc7ff";
                            item.classList.add("producto-item");
                            item.textContent = producto.producto; // Esto muestra el nombre en la lista
                            item.addEventListener("click", function() {
                                // Al hacer clic, rellenamos los campos:
                                document.getElementById("producto").value = producto.producto;
                                document.getElementById("costo").value = producto.precio_venta;
                                // ¡Simplemente usamos la descripción que viene del servidor!
                                document.getElementById("descripcion").value = producto.descripcion; 
                                lista.innerHTML = ''; // Limpiamos la lista
                                // Mover el foco a la cantidad para agilizar
                                document.getElementById("cantidad").focus();
                            });
                            lista.appendChild(item);
                        });
                    } else {
                        lista.innerHTML = '<div class="box" style="background:#f3f3f3;">No se encontraron productos</div>';
                    }
                })
                .catch(error => {
                    // Esto atrapará el "Unexpected token '<'" si PHP falla
                    console.error('Error al obtener los productos:', error);
                    let lista = document.getElementById("producto-lista");
                    lista.innerHTML = '<div class="box" style="background:#ffcccc;">Error al procesar la respuesta. Revise la consola.</div>';
                });
        } else {
            document.getElementById("producto-lista").innerHTML = '';
        }
    });

    // ==== INICIO DE LA NUEVA LÓGICA DE "AGREGAR PRODUCTO" ====
    const tablaProductosBody = document.getElementById('tabla_productos').getElementsByTagName('tbody')[0];
    // ---- 1. Botón "Agregar Producto" ----
    document.getElementById('btn_agregar_producto').addEventListener('click', function() {
        // Obtener valores de los inputs
        const nombreProd = document.getElementById('producto').value;
        const cantidad = parseFloat(document.getElementById('cantidad').value) || 0;
        const costoUnitario = parseFloat(document.getElementById('costo').value.replace(/[$,]/g, '')) || 0;
        // Si el campo está vacío o es inválido, usará 0.16 como respaldo.
        const ivaDecimal = parseFloat(document.getElementById('iva').value) || 0.16;
        // Validación simple
        if (cantidad <= 0 || costoUnitario <= 0 || nombreProd.trim() === "") {
            alert("Por favor, ingrese un producto, cantidad y costo válidos.");
            return;
        }
        // Calcular totales de la línea
        const subtotalLinea = cantidad * costoUnitario;
        const ivaLinea = subtotalLinea * ivaDecimal;
        const totalLinea = subtotalLinea + ivaLinea;
        // Crear la fila en la tabla
        const newRow = tablaProductosBody.insertRow();
        // Guardar los datos puros en el 'dataset' de la fila para cálculos fáciles
        newRow.dataset.subtotal = subtotalLinea.toFixed(2);
        newRow.dataset.iva = ivaLinea.toFixed(2);
        newRow.dataset.total = totalLinea.toFixed(2);

        // Insertar las celdas (<td>) con formato
        newRow.innerHTML = `
            <th>${tablaProductosBody.rows.length}</th>
            <td>${nombreProd}</td>
            <td>${cantidad}</td>
            <td>$${costoUnitario.toFixed(2)}</td>
            <td>$${subtotalLinea.toFixed(2)}</td>
            <td>$${ivaLinea.toFixed(2)}</td>
            <td>$${totalLinea.toFixed(2)}</td>
            <td>
                <button class="button is-danger is-small btn-eliminar-producto">
                    <span class="icon is-small"><i class="fas fa-times"></i></span>
                </button>
            </td>
        `;
        
        // Actualizar los totales generales del tfoot
        actualizarTotalesGenerales();
        // Limpiar campos para el siguiente producto
        limpiarCamposProducto();
    });

    // ---- 2. Función para Actualizar Totales (tfoot) ----
    function actualizarTotalesGenerales() {
        let subtotalGeneral = 0.00;
        let ivaTotal = 0.00;
        let totalPagar = 0.00;
        
        // Recorrer todas las filas del tbody
        for (let row of tablaProductosBody.rows) {
            subtotalGeneral += parseFloat(row.dataset.subtotal);
            ivaTotal += parseFloat(row.dataset.iva);
            totalPagar += parseFloat(row.dataset.total);
        }
        // Actualizar el HTML del tfoot
        document.getElementById('subtotal_general').textContent = '$' + subtotalGeneral.toFixed(2);
        document.getElementById('iva_total').textContent = '$' + ivaTotal.toFixed(2);
        document.getElementById('total_pagar').textContent = '$' + totalPagar.toFixed(2);
    }
    // ---- 3. Función para Limpiar Inputs ----
    function limpiarCamposProducto() {
        document.getElementById('producto').value = '';
        document.getElementById('cantidad').value = ''; 
        document.getElementById('descripcion').value = '';
        document.getElementById('producto').focus(); // Regresar foco al producto
    }

    // ---- 4. Lógica para Eliminar un Producto de la Fila ----
    tablaProductosBody.addEventListener('click', function(event) {
        // Buscar si el clic fue en un botón de eliminar
        const botonEliminar = event.target.closest('.btn-eliminar-producto');
        if (botonEliminar) {
            const filaParaEliminar = botonEliminar.closest('tr');
            filaParaEliminar.remove();
            // Recalcular todo después de eliminar
            actualizarTotalesGenerales();
        }
    });
</script>

<script>
    // Variables globales para el manejo de pagos
    let pagosAplicados = [];
    let totalAPagar = 0.00;

    // Referencias a elementos del DOM de la nueva sección de pago
    const seccionPago = document.getElementById('seccion_pago');
    const totalPagarDisplay = document.getElementById('total_pagar_display');
    const totalRestanteDisplay = document.getElementById('total_restante_display');
    const cambioDisplay = document.getElementById('cambio_display');
    const metodoPagoSelect = document.getElementById('metodo_pago_select');
    const montoPagoInput = document.getElementById('monto_pago_input');
    const btnAgregarPago = document.getElementById('btn_agregar_pago');
    const tablaPagosBody = document.getElementById('tabla_pagos_aplicados').getElementsByTagName('tbody')[0];
    const btnFinalizarVenta = document.getElementById('btn_finalizar_venta');

    // 1. Observador para el Total a Pagar
    const totalPagarElemento = document.getElementById('total_pagar');

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            // El texto ha cambiado.
            const nuevoTotalTexto = mutation.target.textContent;
            // Limpiamos el texto (quitamos "$", ",", etc.) y convertimos a número
            totalAPagar = parseFloat(nuevoTotalTexto.replace(/[$,]/g, '')) || 0.00;
            
            // Si el total es > 0, mostramos la sección de pago
            if (totalAPagar > 0) {
                seccionPago.style.display = 'block';
            } else {
                seccionPago.style.display = 'none';
            }
            // Actualizar todos los totales de pago
            actualizarCalculosDePago();
        });
    });

    // Configurar el observador para que vigile los cambios en el texto
    observer.observe(totalPagarElemento, {
        childList: true,
        subtree: true,
        characterData: true
    });

    // 2. Event Listener para "Agregar Pago"
    btnAgregarPago.addEventListener('click', function() {
        const metodo = metodoPagoSelect.value;
        let monto = parseFloat(montoPagoInput.value.replace(/[$,]/g, '')) || 0.00;
        
        if (monto <= 0) {
            alert("El monto debe ser mayor a cero.");
            return;
        }
        // Añadir el pago al array
        pagosAplicados.push({
            metodo: metodo,
            monto: monto
        });
        // Limpiar inputs
        montoPagoInput.value = '';
        metodoPagoSelect.selectedIndex = 0;
        // Actualizar la UI
        actualizarCalculosDePago();
    });

    // 3. Función principal para recalcular y actualizar la UI
    function actualizarCalculosDePago() {
        let totalPagado = 0.00;
        // Sumar todos los pagos aplicados
        pagosAplicados.forEach(pago => {
            totalPagado += pago.monto;
        });
        let restante = totalAPagar - totalPagado;
        let cambio = 0.00;
        if (restante < 0) {
            cambio = -restante; // El cambio es el excedente
            restante = 0;     // Ya no resta nada por pagar
        }
        // Actualizar los displays
        totalPagarDisplay.value = '$' + totalAPagar.toFixed(2);
        totalRestanteDisplay.value = '$' + restante.toFixed(2);
        cambioDisplay.value = '$' + cambio.toFixed(2);
        // Asignar clases de color al campo "Restante"
        totalRestanteDisplay.classList.toggle('has-text-danger', restante > 0);
        totalRestanteDisplay.classList.toggle('has-text-success', restante === 0);
        // Renderizar la tabla de pagos
        renderizarTablaPagos();
        // Habilitar o deshabilitar el botón de finalizar venta
        // Se habilita solo si el total a pagar es > 0 Y el restante es 0
        if (restante === 0 && totalAPagar > 0) {
            btnFinalizarVenta.disabled = false;
        } else {
            btnFinalizarVenta.disabled = true;
        }
    }

    // 4. Función para dibujar la tabla de pagos
    function renderizarTablaPagos() {
        tablaPagosBody.innerHTML = ''; // Limpiar la tabla
        pagosAplicados.forEach((pago, index) => {
            const row = tablaPagosBody.insertRow();
            row.innerHTML = `
                <td>${pago.metodo}</td>
                <td>$${pago.monto.toFixed(2)}</td>
                <td>
                    <button class="button is-danger is-small btn-eliminar-pago" data-index="${index}">
                        <span class="icon is-small"><i class="fas fa-times"></i></span>
                    </button>
                </td>
            `;
        });
        // Añadir listeners a los nuevos botones de eliminar
        document.querySelectorAll('.btn-eliminar-pago').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                eliminarPago(index);
            });
        });
    }

    // 5. Función para eliminar un pago (si el usuario se equivoca)
    function eliminarPago(index) {
        pagosAplicados.splice(index, 1); // Eliminar el pago del array
        actualizarCalculosDePago(); 
    }

    // 6. Event Listener para "Finalizar Venta"
    btnFinalizarVenta.addEventListener('click', function() {
        console.log("Enviando venta al servidor...");
        console.log("Total Venta:", totalAPagar);
        console.log("Pagos Registrados:", pagosAplicados);
        alert("¡Venta Finalizada!\nTotal: $" + totalAPagar.toFixed(2) + "\nPagos registrados: " + pagosAplicados.length);
        // Opcional: Resetear todo para una nueva venta
        resetearFormularioVenta();
    });

    function resetearFormularioVenta() {
        // Resetear productos (limpiando la tabla y los totales)
        document.getElementById('tabla_productos').getElementsByTagName('tbody')[0].innerHTML = '';
        document.getElementById('subtotal_general').textContent = '$0.00';
        document.getElementById('iva_total').textContent = '$0.00';
        // Al setear total_pagar a $0.00, el MutationObserver se disparará
        document.getElementById('total_pagar').textContent = '$0.00'; 
        // Resetear pagos
        pagosAplicados = [];
        totalAPagar = 0.00;
        actualizarCalculosDePago(); // Esto limpiará los displays de pago
        // Opcional: limpiar el campo de producto
        document.getElementById('producto').value = '';
    }
</script>