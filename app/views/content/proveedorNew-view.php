<div class="container is-fluid mb-6">
    <h1 class="title">Proveedores</h1>
    <h2 class="subtitle"><i class="fas fa-plus"></i> &nbsp; Nuevo proveedor</h2>
</div>

<div class="container pb-6 pt-6">
    <form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/proveedorAjax.php" method="POST" autocomplete="off">

        <!-- Indica al ajax qué acción es -->
        <input type="hidden" name="modulo_proveedor" value="registrar">

        <div class="columns is-multiline">

            <div class="column is-half">
                <label class="label">Nombre proveedor</label>
                <input type="text" name="proveedor" class="input" placeholder="Nombre del proveedor" required>
            </div>

            <div class="column is-half">
                <label class="label">Teléfono</label>
                <input type="tel" name="telefono" class="input" placeholder="Ej: 7711234567">
            </div>

            <div class="column is-half">
                <label class="label">Email</label>
                <input type="email" name="email" class="input" placeholder="correo@dominio.com">
            </div>

            <div class="column is-half">
                <label class="label">Dirección</label>
                <input type="text" name="direccion" class="input" placeholder="Dirección">
            </div>

            <div class="column is-half">
                <label class="label">Sitio web</label>
                <input type="url" name="web" class="input" placeholder="https://www.ejemplo.com">
            </div>

        </div>

        <p class="has-text-centered">
            <button type="submit" class="button is-primary is-rounded">
                <i class="fas fa-save"></i> &nbsp; Guardar proveedor
            </button>
        </p>
    </form>
</div>

<script>

    document.querySelectorAll(".FormularioAjax").forEach(formulario => {
  formulario.addEventListener("submit", async (e) => {
    e.preventDefault();

    const action = formulario.getAttribute("action");
    const data   = new FormData(formulario);

    try {
      const res  = await fetch(action, { method: "POST", body: data, credentials: "same-origin" });
      const text = await res.text(); // siempre como texto

      let json;
      try { json = JSON.parse(text); }
      catch(e){
        console.error("Respuesta cruda no-JSON:", text);
        Swal.fire({ title: "Respuesta inesperada", text: "El servidor no devolvió JSON válido.", icon: "error" });
        return;
      }

      if (json.Alerta === "simple") {
        Swal.fire({
          title: json.Titulo || "OK",
          text:  json.Texto  || "",
          icon:  json.Tipo   || "success",
          timer: 3000,
          showConfirmButton: false
        });
        if (json.Tipo === "success") {
          formulario.reset();
        }
          formulario.reset();
      } else {
        Swal.fire({ title: "Aviso", text: "Formato de respuesta no esperado.", icon: "warning" });
      }

    } catch (err) {
      console.error("Error de red:", err);
      Swal.fire({ title: "Error", text: "No se pudo contactar al servidor.", icon: "error" });
    }
  });
});


</script>