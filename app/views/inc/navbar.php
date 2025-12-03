<div class="full-width navBar">
    <div class="full-width navBar-options">
        <i class="fas fa-exchange-alt fa-fw" id="btn-menu"></i>
        <nav class="navBar-options-list">
            <ul class="list-unstyle">
                
                <li class="noLink">
                    <form id="searchFormNavbar" class="no-confirm" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off">
                        <input type="hidden" name="modulo_buscador" value="buscar">
                        <input type="hidden" name="modulo_url" value="odsSearch">
                        <div class="search-box">
                            <input class="search-input" type="text" name="txt_buscador" placeholder="Buscar por ODS o Nombre" required>
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </li>

                <li class="noLink" id="btn-notificaciones" style="position: relative; cursor: pointer; margin-right: 15px;">
                    <!-- Icono Campana en Blanco -->
                    <i id="icon-bell" class="fas fa-bell" style="font-size: 1.3rem; color: #fff; transition: color 0.3s ease;"></i>
                    
                    <span id="notif-contador" class="notif-badge" style="display: none;">0</span>
                    
                    <!-- Lista de Notificaciones -->
                    <div id="notif-lista" class="notif-dropdown">
                        <div class="notif-header">Notificaciones</div>
                        <div id="notif-items" class="notif-content">
                            <div class="notif-empty">Cargando...</div>
                        </div>
                    </div>
                </li>

                <li class="text-condensedLight noLink">
                    <a class="btn-exit" href="<?php echo APP_URL."logOut/"; ?>">
                        <i class="fas fa-power-off"></i>
                    </a>
                </li>
                <li class="text-condensedLight noLink">
                    <small><?php echo $_SESSION['usuario']; ?></small>
                </li>
                <li class="noLink">
                    <?php
                        if(is_file("./app/views/fotos/".$_SESSION['foto'])){
                            echo '<img class="is-rounded img-responsive" src="'.APP_URL.'app/views/fotos/'.$_SESSION['foto'].'">';
                        }else{
                            echo '<img class="is-rounded img-responsive" src="'.APP_URL.'app/views/fotos/default.png">';
                        }
                    ?>
                </li>
            </ul>
        </nav>
    </div>
</div>

<!-- SCRIPTS JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // A. LOGICA BUSCADOR
    const searchForm = document.getElementById('searchFormNavbar');
    if(searchForm){
        searchForm.addEventListener('submit', function(e){
            e.preventDefault(); 
            let data = new FormData(this);
            let method = this.getAttribute('method');
            let action = this.getAttribute('action');
            fetch(action, { method: method, body: data })
            .then(response => response.json())
            .then(respuesta => {
                if(respuesta.tipo === 'redireccionar'){ window.location.href = respuesta.url; } 
                else if(respuesta.tipo === 'simple'){ alert(respuesta.texto); }
            })
            .catch(error => { console.error('Error:', error); });
        });
    }

    // B. LOGICA NOTIFICACIONES
    const btnNotif = document.getElementById('btn-notificaciones');
    const listaNotif = document.getElementById('notif-lista');
    const contador = document.getElementById('notif-contador');
    const iconCampana = document.getElementById('icon-bell');

    // 1. Abrir/Cerrar menú
    if(btnNotif){
        btnNotif.addEventListener('click', function(e) {
            e.stopPropagation(); 
            listaNotif.classList.toggle('show');
        });
    }

    // 2. Cerrar menú clic afuera
    document.addEventListener('click', function() {
        if(listaNotif) listaNotif.classList.remove('show');
    });
    if(listaNotif){
        listaNotif.addEventListener('click', function(e){
            e.stopPropagation(); 
        });
    }

    // 3. Función Polling
    function consultarNotificaciones() {
        let data = new FormData();
        data.append('modulo_notificacion', 'consultar');

        fetch('<?php echo APP_URL; ?>app/ajax/notificacionAjax.php', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(data => {
            if(contador && listaNotif) {
                if (data.total > 0) {
                    contador.innerText = data.total;
                    contador.style.display = 'flex'; 
                    document.getElementById('notif-items').innerHTML = data.html;
                    
                    // Activar campana roja
                    if(iconCampana) {
                        iconCampana.classList.add('bell-active');
                        iconCampana.style.color = ''; 
                    }
                } else {
                    contador.style.display = 'none'; 
                    // Desactivar campana roja
                    if(iconCampana) {
                        iconCampana.classList.remove('bell-active');
                        iconCampana.style.color = '#fff'; // Regresar a blanco
                    }
                    if(document.getElementById('notif-items').innerHTML.includes('Cargando') || data.total == 0){
                         document.getElementById('notif-items').innerHTML = '<div class="notif-empty">No hay novedades</div>';
                    }
                }
            }
        })
        .catch(e => console.error("Error polling:", e));
    }

    setInterval(consultarNotificaciones, 5000);
    consultarNotificaciones();
});

function marcarLeido(idNotif, urlDestino) {
    let data = new FormData();
    data.append('accion', 'marcar_leido');
    data.append('id', idNotif);

    fetch('<?php echo APP_URL; ?>app/ajax/notificacionAjax.php', {
        method: 'POST',
        body: data
    }).then(() => {
        if(urlDestino && urlDestino !== '#') window.location.href = urlDestino;
    });
}
</script>

<style>
/* --- 1. BARRA DE NAVEGACIÓN AZUL --- */
.navBar, .navBar-options {
    background-color: #1d4d80 !important; /* Azul solicitado */
    color: #fff !important; /* Texto general blanco */
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Forzar color blanco en iconos y enlaces del navbar */
#btn-menu, .btn-exit, .text-condensedLight, .navBar-options-list ul li {
    color: #fff !important;
}

/* --- 2. ESTILOS BUSCADOR --- */
.search-box { position: relative; width: 650px; height: 35px; }
.search-input { 
    width: 75%; height: 100%; border-radius: 25px; border: 1px solid #ddd; 
    background-color: #f1f3f4; padding-left: 20px; padding-right: 45px; 
    font-size: 14px; outline: none; transition: all 0.3s ease-in-out;
    color: #333; /* Texto negro al escribir */
}
.search-input:focus { border-color: #4a90e2; background-color: #fff; box-shadow: 0 0 8px rgba(74, 144, 226, 0.3); }
.search-btn { position: absolute; top: 0; right: 10px; width: 180px; height: 150%; background: transparent; border: none; cursor: pointer; padding: 0; display: flex; align-items: center; justify-content: left; }
.search-btn i { color: #0c4782ff !important; font-size: 16px; transition: color 0.2s; } /* Icono de lupa azul oscuro */

/* --- 3. ESTILOS NOTIFICACIONES (CORREGIDO "NO SE VE CONTENIDO") --- */

/* Campana roja animada */
.bell-active {
    color: #ef4444 !important;
    animation: swing 1s ease;
}

/* Círculo contador */
.notif-badge {
    position: absolute;
    top: 15px;
    right: -5px;
    background-color: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: bold;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    z-index: 10;
    animation: bounceIn 0.3s;
}

/* Caja desplegable */
.notif-dropdown {
    position: absolute; top: 45px; right: -10px;
    width: 320px; background: white;
    border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    display: none; z-index: 2000;
    border: 1px solid #e5e7eb; overflow: hidden;
    
    /* IMPORTANTE: Forzamos el color de texto a NEGRO dentro de la lista 
       para que no herede el blanco del navbar y sea invisible */
    color: #333 !important; 
    text-align: left;
}
.notif-dropdown.show { display: block; animation: fadeIn 0.2s; }

/* Cabecera de la lista */
.notif-header {
    background: #f9fafb; padding: 12px 13px; font-weight: 700;
    border-bottom: 1px solid #e5e7eb; 
    color: #060606ff !important; /* Texto oscuro forzado */
    font-size: 1rem;
}

/* ESTILOS INTERNOS DE CADA NOTIFICACIÓN (IMPORTANTE) */
/* Usamos selectores profundos por si el PHP no tiene clases */
.notif-content div, .notif-content a {
    color: #4a4a4a;
    text-decoration: none;
    display: block;
}

/* Forzamos estilo a los items generados por PHP */
.notif-item, .notif-content > div {
    padding: 15px 15px;
    border-bottom: 1px solid #f1f2f6;
    display: flex; 
    align-items: flex-start;
    gap: 10px; /* Espacio entre icono y texto */
    transition: background 0.2s ease;
    cursor: pointer;
    font-size: 0.92rem;
    line-height: 1.4;
}

.notif-item:hover, .notif-content > div:hover {
    background-color: #f7fbff; /* Azul muy suave */
}

/* Arreglando el icono "i" o cualquier icono dentro */
.notif-item i, .notif-content > div i {
    background: #cbe8fcff; /* Fondo circular azul claro */
    color: #104880ff;
    width: 35px; height: 35px;
    display: flex !important;
    align-items: center; justify-content: center;
    border-radius: 50%;
    flex-shrink: 0; /* Evita que se aplaste */
    font-size: 1rem !important;
    font-style: normal;
}

/* Texto de la fecha (asumiendo que viene en un small o div) */
.notif-item small, .notif-content > div small {
    display: block;
    color: #a4b0be;
    font-size: 0.75rem;
    margin-top: 5px;
    font-weight: 500;
}

.notif-empty {
    padding: 40px 20px; text-align: center; color: #a4b0be;
    display: flex; flex-direction: column; align-items: center;
}

/* Scrollbar personalizado delgado */
.custom-scroll::-webkit-scrollbar { width: 5px; }
.custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
.custom-scroll::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
.custom-scroll::-webkit-scrollbar-thumb:hover { background: #bbb; }

/* Animaciones */
@keyframes bounceIn { 0% { transform: scale(0); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
@keyframes swing { 20% { transform: rotate(15deg); } 40% { transform: rotate(-10deg); } 60% { transform: rotate(5deg); } 80% { transform: rotate(-5deg); } 100% { transform: rotate(0deg); } }
</style>