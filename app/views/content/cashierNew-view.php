<?php $siguiente_idini = $_SESSION['siguiente_idini'] ?? 1; ?>
<div class="container is-fluid mb-1">
    <h1 class="title">Caja</h1>
    <h2 class="subtitle"><i class="fas fa-cash-register fa-fw"></i> &nbsp; Nueva caja</h2>
</div>
<?php $siguiente_idini = $_SESSION['siguiente_idini'] ?? 1; ?>

<div class="box" style="background:#a6cff4;">
<div class="columns is-vcentered">
    <!-- Botón para abrir la caja -->
    <div class="column">
        <div class="control">
            <button type="button" id="btn_abrir_caja" class="button is-success is-fullwidth">
                Abrir Caja
            </button>
        </div>
    </div>
    
    <!-- Campo de Efectivo en caja -->
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
	<!-- Mensaje de error -->
    <div class="column is-6">
        <div class="control">
            <p id="mensaje-error" class="has-text-danger is-hidden">¡Ingresa el efectivo de la caja!</p>
        </div>
    </div>
</div>
    <div class="columns">
        <div class="column">
            <div class="control">
                <label>Producto <?php echo CAMPO_OBLIGATORIO; ?></label>
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
                <input class="input" id="iva" name="iva" type="text" value="0.00" readonly disabled>
            </div>
        </div>
    </div>
	<div class="columns">
        <div class="column">
            <label>Descripcion</label>
                <input class="input" id="descripcion" name="descripcion" type="text" value="" disabled>
        </div>
		<div class="column">
            <label>Tipo pago</label>
                <input class="input" id="tipo_pago" name="tipo_pago" type="text" value="" disabled>
        </div>
    </div>
    <div class="columns">
        <div class="column is-3">
            <button type="button" id="btn_agregar_producto" class="button is-success is-fullwidth" disabled>
                <i class="fas fa-plus"></i>&nbsp; Agregar Producto
            </button>
        </div>
    </div>

    <div class="table-container">
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
</div>

<script>
// Flag to track if the "caja" is open
let cajaAbierta = false;

// When the "Abrir Caja" button is clicked
document.getElementById("btn_abrir_caja").addEventListener("click", function() {
    const efectivo = document.getElementById("efectivo").value;

    // Check if the efectivo field is filled and valid
    if (efectivo && !isNaN(efectivo) && parseFloat(efectivo) > 0) {
        cajaAbierta = true;  // Open the box
        document.getElementById("mensaje-error").classList.add("is-hidden");  // Hide the error message
        alert("Caja abierta correctamente.");

        // Disable the "Abrir Caja" button after opening the caja
        document.getElementById("btn_abrir_caja").disabled = true;

        // Enable all the fields for the transaction
        habilitarCampos(true);
    } else {
        document.getElementById("mensaje-error").classList.remove("is-hidden");  // Show the error message
        cajaAbierta = false;  // Don't open the box if "efectivo" is not valid
    }
});

// Función para habilitar o deshabilitar campos del formulario
function habilitarCampos(habilitar) {
    document.getElementById("producto").disabled = !habilitar;
    document.getElementById("cantidad").disabled = !habilitar;
    document.getElementById("costo").disabled = !habilitar;
    document.getElementById("subtotal").disabled = !habilitar;
    document.getElementById("iva").disabled = !habilitar;
    document.getElementById("btn_agregar_producto").disabled = !habilitar;  // Enable the "Add Product" button
}

// Función para buscar productos mientras se escribe
document.getElementById("producto").addEventListener("input", function() {
    let query = this.value;
    if (query.length > 1) {
        // Hacemos el fetch con la URL correcta
        fetch("<?php echo APP_URL; ?>app/ajax/buscarProducto.php?query=" + query)
            .then(response => response.json())  // Procesa la respuesta JSON
            .then(data => {
                console.log(data);  // Verifica los datos recibidos del servidor
                let lista = document.getElementById("producto-lista");
                lista.innerHTML = '';  // Limpiar la lista previa
                if (data.length > 0) {
                    data.forEach(producto => {
						  const descripcion = producto.caracteristica1 + ' ' + producto.caracteristica2 + ' ' + producto.caracteristica3 + ' ' + producto.caracteristica4;
                        let item = document.createElement("div");
                        item.classList.add("box");
                        item.style.background = "#c7dfc7ff"; // Aquí puedes poner el color deseado
                        item.classList.add("producto-item");
                        item.textContent = producto.producto;  // Cambiar 'nombre' por 'producto'
                        item.addEventListener("click", function() {
                            document.getElementById("producto").value = producto.producto;  // Cambiar 'nombre' por 'producto'
                            document.getElementById("costo").value = producto.precio_venta;  // Cambiar 'nombre' por 'producto'
							lista.innerHTML = '';  // Limpiar lista después de seleccionar
                            agregarProductoSeleccionado(producto.producto);  // Agregar al contenedor de productos
                        });
                        lista.appendChild(item);
                    });
                } else {
                    lista.innerHTML = '<div class="box" style="background:#f3f3f3;">No se encontraron productos</div>';
                }
            })
            .catch(error => {
                console.error('Error al obtener los productos:', error);
            });
    } else {
        document.getElementById("producto-lista").innerHTML = '';
    }
});

// Función para agregar el producto seleccionado a #contenedor-productos
function agregarProductoSeleccionado(nombreProducto) {
    const contenedorProductos = document.getElementById('contenedor-productos');

    const productoElement = document.createElement('div');
    productoElement.classList.add('producto');
    productoElement.textContent = nombreProducto;  // Mostrar el nombre del producto seleccionado

    // Agregar el producto al contenedor de productos
    contenedorProductos.appendChild(productoElement);
}

// Validate when trying to submit the form
document.getElementById("formulario-caja").addEventListener("submit", function(event) {
    if (!cajaAbierta) {
        event.preventDefault();  // Prevent form submission
        alert("Debes abrir la caja antes de realizar una venta.");
    }
});
</script>
