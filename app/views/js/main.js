/*Mostrar ocultar menu principal*/
let btn_menu=document.getElementById('btn-menu');
btn_menu.addEventListener("click", function(e){
    e.preventDefault();

    let navLateral=document.getElementById('navLateral');
    let pageContent=document.getElementById('pageContent');

    if(navLateral.classList.contains('navLateral-change') && pageContent.classList.contains('pageContent-change')){
        navLateral.classList.remove('navLateral-change');
        pageContent.classList.remove('pageContent-change');
    }else{
        navLateral.classList.add('navLateral-change');
        pageContent.classList.add('pageContent-change');
    }
});

/*Mostrar y ocultar submenus*/
let btn_subMenu=document.querySelectorAll(".btn-subMenu");
btn_subMenu.forEach(subMenu => {
    subMenu.addEventListener("click", function(e){

        e.preventDefault();
        if(this.classList.contains('btn-subMenu-show')){
            this.classList.remove('btn-subMenu-show');
        }else{
            this.classList.add('btn-subMenu-show');
        }
    });
});


document.addEventListener('DOMContentLoaded', () => {
  // Functions to open and close a modal
  function openModal($el) {
    $el.classList.add('is-active');
  }

  function closeModal($el) {
    $el.classList.remove('is-active');
  }

  function closeAllModals() {
    (document.querySelectorAll('.modal') || []).forEach(($modal) => {
      closeModal($modal);
    });
  }

  // Add a click event on buttons to open a specific modal
  (document.querySelectorAll('.js-modal-trigger') || []).forEach(($trigger) => {
    const modal = $trigger.dataset.target;
    const $target = document.getElementById(modal);

    $trigger.addEventListener('click', () => {
      openModal($target);
    });
  });

  // Add a click event on various child elements to close the parent modal
  (document.querySelectorAll('.modal-background, .modal-close, .modal-card-head .delete, .modal-card-foot .button') || []).forEach(($close) => {
    const $target = $close.closest('.modal');

    $close.addEventListener('click', () => {
      closeModal($target);
    });
  });

  // Add a keyboard event to close all modals
  document.addEventListener('keydown', (event) => {
    if (event.code === 'Escape') {
      closeAllModals();
    }
  });
});

// Función para preparar el modal con los datos de la ODS
document.addEventListener("DOMContentLoaded", () => {
    const filas = document.querySelectorAll(".status-dropdown");

    filas.forEach(select => {
        const estadoInicial = select.value;

        select.addEventListener("change", () => {
            const botonNotificar = select.closest("td").querySelector(".js-modal-trigger");
            const estadoActual = select.value;

            if (estadoActual !== estadoInicial) {
                botonNotificar.disabled = false;
                botonNotificar.classList.remove("is-light");
            } else {
                botonNotificar.disabled = true;
                botonNotificar.classList.add("is-light");
            }
        });
    });
});
//Detectar cambio en el <select> y activar botón:
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".status-dropdown").forEach(select => {
        const estadoInicial = select.value;

        select.addEventListener("change", () => {
            const botonNotificar = select.closest("td").querySelector(".js-modal-trigger");
            botonNotificar.disabled = (select.value === estadoInicial);
        });
    });
});

//Modal: abrir, cargar usuarios, enviar:
let odsSeleccionada = null;
let nuevoStatusSeleccionado = null;

function prepararModalODS(idODS, selectElem) {
    odsSeleccionada = idODS;
    nuevoStatusSeleccionado = selectElem.value;

    fetch('/VENTAS3/app/ajax/usuariosAjax.php')
        .then(res => res.json())
        .then(data => {
            const contenedor = document.getElementById("lista_usuarios_checkboxes");
            if (!contenedor) {
                console.error("⚠️ Contenedor de usuarios no encontrado");
                return;
            }

            contenedor.innerHTML = "";
            data.forEach(usuario => {
               contenedor.innerHTML += `
                <label>
                    <input type="checkbox" class="checkbox-usuario" name="destinatarios[]" value="${usuario.id}"> ${usuario.nombre}
                </label><br>
            `;

            });

            document.getElementById("modalNotificacion").classList.add("is-active");
        })
        .catch(err => {
            console.error("Error al cargar usuarios:", err);
        });
}


function cerrarModal() {
    document.getElementById("modalNotificacion").classList.remove("is-active");
    odsSeleccionada = null;
    nuevoStatusSeleccionado = null;
}

function enviarNotificacionEstado() {
    const seleccionados = Array.from(document.querySelectorAll('.checkbox-usuario:checked'))
        .map(cb => cb.value);

    if (seleccionados.length === 0) {
        Swal.fire("Atención", "Debes seleccionar al menos un usuario.", "warning");
        return;
    }

    const formData = new FormData();
    formData.append("Idods", odsSeleccionada);
    formData.append("nuevo_status", nuevoStatusSeleccionado);
    formData.append("destinatarios", JSON.stringify(seleccionados));

    fetch("/VENTAS3/app/ajax/statusAjax.php", {
        method: "POST",
        body: formData,
        credentials: 'include'
    })
    .then(res => res.json())
    .then(res => {
        if (res.tipo === "exito") {
            Swal.fire(res.titulo, res.texto, "success");
            cerrarModal();
            actualizarCampanita();
        } else {
            Swal.fire(res.titulo || "Error", res.texto || "No se pudo notificar", "error");
        }
    });
}
