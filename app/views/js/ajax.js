// AL cargar la página, si existe PHPSESSID en localStorage
var savedSessionId = localStorage.getItem("PHPSESSID");
if (savedSessionId) {
    document.cookie = `PHPSESSID=${savedSessionId}; path=/`;
}

/* Enviar formularios via AJAX (CORREGIDO) */
document.querySelectorAll(".FormularioAjax").forEach(formulario => {
    formulario.addEventListener("submit", function(e) {
        e.preventDefault(); // Previene el envío por defecto del formulario

        if (this.dataset.sending === '1') return;

        // Función para enviar los datos del formulario via AJAX
        const sendFormData = () => {
            this.dataset.sending = '1';
            const data = new FormData(formulario);
            const method = formulario.getAttribute("method") || 'POST';
            const action = formulario.getAttribute("action");

            fetch(action, {
                method,
                body: data,
                credentials: 'include',
                cache: 'no-cache'
            })
            .then(respuesta => {
                if (!respuesta.ok) throw new Error(`Error HTTP ${respuesta.status}`);
                return respuesta.json().catch(() => { throw new Error('La respuesta no es un JSON válido.'); });
            })
            .then(respuesta => {
                alertas_ajax(respuesta); // Llama a la función de alertas
            })
            .catch(err => {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            })
            .finally(() => {
                this.dataset.sending = '0';
            });
        };

        // Si el formulario NO tiene la clase 'no-confirm', mostramos SweetAlert
        if (!this.classList.contains('no-confirm')) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Quieres realizar la acción solicitada",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, realizar',
                cancelButtonText: 'No, cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    sendFormData();
                }
            });
        } else {
            // Si SÍ tiene la clase 'no-confirm' (como nuestro buscador), envía los datos directamente
            sendFormData();
        }
    }, { passive: false });
});

/* Función de alertas (CORREGIDA) */
function alertas_ajax(alerta) {
    if (alerta.tipo == "simple") {
        Swal.fire({
            icon: alerta.icono,
            title: alerta.titulo,
            text: alerta.texto,
            confirmButtonText: 'Aceptar'
        });
    } else if (alerta.tipo == "recargar") {
        Swal.fire({
            icon: alerta.icono,
            title: alerta.titulo,
            text: alerta.texto,
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if (result.isConfirmed) location.reload();
        });
    } else if (alerta.tipo == "limpiar") {
        Swal.fire({
            icon: alerta.icono,
            title: alerta.titulo,
            text: alerta.texto,
            confirmButtonText: 'Aceptar'
        }).then((result) => {
            if (result.isConfirmed) document.querySelector(".FormularioAjax").reset();
        });
    } else if (alerta.tipo == "redireccionar") {
        // MÉTODO MEJORADO PARA ABRIR EN NUEVA PESTAÑA
        // Crea un enlace temporal, lo "clickea" y lo elimina.
        // Esto es más confiable contra los bloqueadores de pop-ups.
        const link = document.createElement('a');
        link.href = alerta.url;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function abrirModalNotificacion(Idods, nuevo_status) {
    document.getElementById("modal_Idods").value = Idods;
    document.getElementById("modal_nuevo_status").value = nuevo_status;

    fetch("/VENTAS3/app/ajax/listarUsuariosAjax.php")
        .then(res => res.json())
        .then(data => {
            const contenedor = document.getElementById("lista_usuarios_checkboxes");
            contenedor.innerHTML = "";
            data.forEach(usuario => {
                const label = document.createElement("label");
                label.style.display = "block";
                label.innerHTML = `
                    <input type="checkbox" name="destinatarios[]" value="${usuario.Idasesor}">
                    ${usuario.Nombre}
                `;
                contenedor.appendChild(label);
            });

            document.getElementById("modalNotificacion").classList.add("is-active");
        })
        .catch(err => {
            console.error("Error al cargar usuarios:", err);
            alert("Error al cargar la lista de usuarios.");
        });
}

document.querySelectorAll(".modal .delete, #btnCancelarNotificacion").forEach(btn => {
    btn.addEventListener("click", () => {
        document.getElementById("modalNotificacion").classList.remove("is-active");
    });
});

function actualizarCampanita() {
    const badge = document.getElementById("notification-count");
    const bell = document.getElementById("bell-icon"); // ⚠️ id en <i>, no en <a>

    if (!badge || !bell) return;

    fetch("/VENTAS3/app/ajax/notificacionesAjax.php")
        .then(res => res.json())
        .then(data => {
            const count = data.count ?? 0;

            // Oculta el número si no quieres usarlo
            badge.style.display = "none";

            // Cambiar color de campana según haya notificaciones
            if (count > 0) {
                bell.classList.remove("has-text-grey", "has-text-warning");
                bell.classList.add("has-text-danger"); // rojo fuerte en Bulma
            } else {
                bell.classList.remove("has-text-danger");
                bell.classList.add("has-text-grey");
            }
        })
        .catch(err => {
            console.error("Error al obtener notificaciones", err);
        });
}


document.addEventListener("DOMContentLoaded", () => {
    actualizarCampanita();
    setInterval(actualizarCampanita, 30000);

    const bell = document.getElementById("notification-bell");
    const list = document.getElementById("notification-list");

    if (bell && list) {
        bell.addEventListener("click", (e) => {
            e.preventDefault();
            if (list.style.display === "block") {
                list.style.display = "none";
                return;
            }

            fetch("/VENTAS3/app/ajax/listarNotificacionesAjax.php")
                .then(res => res.json())
                .then(data => {
                    list.innerHTML = "";
                    if (!Array.isArray(data)) {
                        console.error("Respuesta inesperada:", data);
                        list.innerHTML = "<p style='padding:10px;'>Error al cargar notificaciones</p>";
                        return;
                    }

                    if (data.length === 0) {
                        list.innerHTML = "<p style='padding:10px;'>No tienes notificaciones</p>";
                        return;
                    }

                    data.forEach(notif => {
                        const item = document.createElement("div");
                        item.style.padding = "10px";
                        item.style.borderBottom = "1px solid #ccc";

                        const mensaje = document.createElement("p");
                        mensaje.textContent = `${notif.mensaje} (${notif.fecha})`;
                        item.appendChild(mensaje);

                        if (!notif.leido) {
                            const btn = document.createElement("button");
                            btn.textContent = "Marcar como leída";
                            btn.className = "button is-small is-success mt-1";
                            btn.onclick = () => marcarNotificacionLeida(notif.id);
                            item.appendChild(btn);
                        }

                        list.appendChild(item);
                    });

                    list.style.display = "block";
                })
                .catch(err => {
                    console.error("Error al cargar notificaciones", err);
                    list.innerHTML = "<p style='padding:10px;'>Error al cargar</p>";
                    list.style.display = "block";
                });
        });

        document.addEventListener("click", (e) => {
            if (!bell.contains(e.target) && !list.contains(e.target)) {
                list.style.display = "none";
            }
        });
    }

    const btnEnviarNotificacion = document.getElementById("btnEnviarNotificacion");
    if (btnEnviarNotificacion) {
        btnEnviarNotificacion.addEventListener("click", function (e) {
            e.preventDefault();
            const form = document.getElementById("formNotificacion");
            const datos = new FormData(form);

            fetch("/VENTAS3/app/ajax/statusAjax.php", {
                method: "POST",
                body: datos,
                credentials: 'include'
            })
                .then(res => res.json())
                .then(res => {
                    if (res.tipo === "exito") {
                        Swal.fire(res.titulo, res.texto, "success");
                        document.getElementById("modalNotificacion").classList.remove("is-active");
                        actualizarCampanita();
                    } else {
                        Swal.fire(res.titulo || "Error", res.texto || "Error al enviar notificación", "error");
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire("Error", "No se pudo enviar la notificación", "error");
                });
        });
    }

    const btn_exit = document.querySelectorAll(".btn-exit");
    btn_exit.forEach(exitSystem => {
        exitSystem.addEventListener("click", function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Quieres salir del sistema?',
                text: "La sesión actual se cerrará y saldrás del sistema",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = this.getAttribute("href");
                    window.location.href = url;
                }
            });
        });
    });

    const loginForm = document.querySelector('#login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetch(loginForm.action, {
                method: 'POST',
                body: new FormData(loginForm)
            })
            .then(res => res.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        if (data.session_id) {
                            localStorage.setItem('PHPSESSID', data.session_id);
                            document.cookie = `PHPSESSID=${data.session_id}; path=`;
                        }
                        window.location.href = data.redirect;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Usuario o contraseña incorrectos.'
                        });
                    }
                } catch (e) {
                    console.error('Respuesta inesperada:', text);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'El servidor devolvió una respuesta inesperada.'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message
                });
            });
        });
    }
});

function marcarNotificacionLeida(id) {
    const form = new FormData();
    form.append("id", id);

    fetch("/VENTAS3/app/ajax/marcarNotificacionLeidaAjax.php", {
        method: "POST",
        body: form,
        credentials: "include"
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            actualizarCampanita(); // actualiza color
            document.getElementById("notification-bell").click(); // recarga lista
        }
    })
    .catch(err => console.error("Error al marcar como leída", err));
}

