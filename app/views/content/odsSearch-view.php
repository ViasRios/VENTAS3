<div class="container is-fluid mb-1">
    <h1 class="title">RESULTADOS</h1>
</div>

<div class="container pb-1 pt-1">
    <?php
        $modulo_actual = "odsSearch";
        use app\controllers\odsController;
        $insUsuario = new odsController();

        if(!isset($_SESSION[$url[0]]) && empty($_SESSION[$url[0]])){
    ?>
    <div class="columns">
        <div class="column">
            <form class="FormularioAjax no-confirm" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" >
                <input type="hidden" name="modulo_buscador" value="buscar">
                <input type="hidden" name="modulo_url" value="<?php echo $modulo_actual; ?>">
                <div class="field is-grouped">
                    <p class="control ">
                        <span class="select">
                            <select name="filtro_campo" required>
                                <option value="Idods">ID ODS</option>
                               
                            </select>
                        </span>
                    </p>
                    <p class="control is-expanded">
                        <input class="input is-rounded" type="text" name="txt_buscador" placeholder="¿Qué estas buscando?" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,50}" maxlength="50" required>
                    </p>
                    <p class="control">
                        <button class="button is-info" type="submit">
                            Buscar
                        </button>
                    </p>
                </div>
            </form>
        </div>
    </div>
    <?php }else{ ?>
    
    <?php
            echo $insUsuario->listarOdsControlador($url[1],15,$url[0],$_SESSION[$url[0]]);
        }
    ?>
</div>

<div id="toast-container">
  <div class="toast-icon"><i class="fas fa-check"></i></div>
  <span id="toast-message">Mensaje aquí</span>
</div>

<style>
  #toast-container {
    visibility: hidden;
    min-width: 250px;
    margin-left: -125px; /* Mitad del ancho para centrar */
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
    border-left: 6px solid #4f46e5; /* Color morado/azul */
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

<script>
    // A. Función para mostrar la notificación bonita
    function mostrarNotificacion(mensaje, tipo = 'success') {
        const toast = document.getElementById("toast-container");
        const text = document.getElementById("toast-message");
        const icon = toast.querySelector(".toast-icon");
        
        text.innerText = mensaje;

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

        toast.classList.add("show");

        // Ocultar después de 3 segundos
        setTimeout(function() {
            toast.classList.remove("show");
        }, 3000);
    }

    // B. Función actualizada para cambiar status sin "alert"
    function actualizarStatusDirecto(idOds, nuevoStatus) {
        let data = new FormData();
        data.append('modulo_ods', 'cambiar_status');
        data.append('Idods', idOds);
        data.append('Status', nuevoStatus);

        fetch('<?php echo APP_URL; ?>app/ajax/odsAjax.php', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(respuesta => {
            if(respuesta.success) {
                // ¡AQUI ESTÁ EL CAMBIO! Usamos mostrarNotificacion
                mostrarNotificacion("Estado actualizado correctamente");
                console.log("Guardado OK: " + nuevoStatus);
            } else {
                mostrarNotificacion(respuesta.msg, 'error');
                // Recargamos solo si hubo error para corregir el select visualmente
                setTimeout(() => { location.reload(); }, 2000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion("Error de conexión", 'error');
        });
    }
</script>