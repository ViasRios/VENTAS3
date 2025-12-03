<?php
    use app\controllers\personalController;
    $h = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
    
    // Configuración inicial
    $insPersonal = new personalController();
    $slug   = isset($url[0]) && $url[0] !== '' ? $url[0] : 'userList';
    $pagina = (isset($url[1]) && is_numeric($url[1]) && (int)$url[1] > 0) ? (int)$url[1] : 1;
    
    // Obtener búsqueda
    $filtro = isset($_SESSION[$slug]) ? $_SESSION[$slug] : "";
?>

<style>
    /* ESTILOS AESTHETIC - PERSONAL */
    .personal-container { font-family: 'Poppins', sans-serif; }
    
    .dashboard-card { 
        background: white; 
        border-radius: 15px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
        border: 1px solid rgba(0,0,0,0.05);
        display: flex; flex-direction: column;
        width: 100%;
        overflow: hidden; /* Asegura que nada se salga de las esquinas redondeadas */
    }
    
    .dashboard-card-header { 
        background: linear-gradient(135deg, #1d4d80 0%, #245c94 100%);
        color: white; 
        padding: 15px 25px; 
        font-weight: 600; 
        font-size: 1rem; 
        display: flex; align-items: center; justify-content: space-between;
    }

    .dashboard-card-body { 
        padding: 20px; 
        background-color: #fff; 
        /* IMPORTANTE: overflow hidden aquí evita que la tarjeta genere scroll */
        overflow: hidden; 
    }

    /* === CORRECCIÓN DEFINITIVA DE SCROLL === */
    /* Creamos una clase 'neutra' que no agrega scroll, solo contiene.
       Esto permite que el scroll que YA TRAE la tabla (del controlador) sea el único.
    */
    .table-wrapper-fix {
        width: 100%;
        overflow: visible; /* NO agregar scroll aquí */
    }
    
    /* Forzamos estilos a la tabla generada por PHP para que se vea bien 
       y sea la única que tenga scroll si es necesario.
    */
    .table-wrapper-fix .table-container {
        box-shadow: none !important; /* Quita sombras dobles si existen */
        overflow-x: auto !important; /* Solo este debe tener scroll */
        padding-bottom: 5px;
    }

    /* Buscador (Sin cambios) */
    .filter-box { 
        background: #fff; padding: 8px 20px; border-radius: 50px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; 
        display: flex; align-items: center; gap: 10px; max-width: 600px;
    }
    .search-input-aesthetic { border: none; background: transparent; width: 100%; outline: none; color: #555; font-weight: 500; font-size: 0.95rem; }
    .search-btn-aesthetic { background: linear-gradient(135deg, #1d4d80 0%, #245c94 100%); color: white; border: none; border-radius: 50px; padding: 8px 20px; cursor: pointer; transition: transform 0.2s; font-size: 0.9rem; font-weight: 600; box-shadow: 0 3px 10px rgba(29, 77, 128, 0.3); }
    .search-btn-aesthetic:hover { transform: translateY(-2px); }
    .welcome-title { color: #1d4d80; font-weight: 800; font-size: 1.5rem; margin-bottom: 5px; }
    .welcome-subtitle { color: #888; font-size: 0.9rem; }
</style>

<div class="container is-fluid mb-1 mt-1 personal-container">
    
    <div class="columns is-vcentered mb-1">
        <div class="column">
            <h1 class="welcome-title"><i class="fas fa-users mr-2"></i> Personal</h1>
            <p class="welcome-subtitle">Gestión de usuarios y empleados del sistema.</p>
        </div>
        <div class="column is-narrow">
            <a href="<?php echo APP_URL; ?>userNew/" class="button is-link is-rounded is-small shadow-sm">
                <i class="fas fa-plus mr-2"></i> Nuevo
            </a>
        </div>
    </div>

    <div class="mb-4">
        <?php if (!isset($_SESSION[$slug]) || empty($_SESSION[$slug])): ?>
            <div class="filter-box">
                <form class="FormularioBuscador" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" style="width: 100%; display: flex; align-items: center; justify-content: space-between;">
                    <input type="hidden" name="modulo_buscador" value="buscar">
                    <input type="hidden" name="modulo_url" value="<?php echo $slug; ?>">
                    <div style="flex-grow: 1; display: flex; align-items: center;">
                        <i class="fas fa-search" style="color: #ccc; margin-right: 10px;"></i>
                        <input class="search-input-aesthetic" type="text" name="txt_buscador" placeholder="Buscar personal..." required>
                    </div>
                    <button type="submit" class="search-btn-aesthetic">Buscar</button>
                </form>
            </div>
        <?php else: ?>
            <div class="filter-box" style="background-color: #fff8f8; border-color: #ffecec;">
                <form class="FormularioBuscador" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" style="width: 100%; display: flex; align-items: center; justify-content: space-between;">
                    <input type="hidden" name="modulo_buscador" value="eliminar">
                    <input type="hidden" name="modulo_url" value="<?php echo $slug; ?>">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-filter" style="color: #ef4444;"></i>
                        <span style="color: #555;">Buscando: <strong>“<?php echo $h($_SESSION[$slug] ?? ''); ?>”</strong></span>
                    </div>
                    <button type="submit" class="button is-danger is-small is-rounded" style="box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);">
                        <i class="fas fa-times mr-1"></i> Limpiar
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="form-rest mb-2"></div>

    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <span><i class="fas fa-clipboard-list mr-2"></i> Lista de Personal</span>
            <span class="tag is-light is-rounded" style="color: #1d4d80; font-weight: bold;">Total</span>
        </div>
        
        <div class="dashboard-card-body">
            <div class="table-wrapper-fix">
                <?php
                    echo $insPersonal->listarPersonalControlador($pagina, 15, $url[0], $filtro);
                ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll(".FormularioBuscador").forEach(form => {
        form.addEventListener("submit", function(e){
            e.preventDefault(); 
            let data = new FormData(this);
            let method = this.getAttribute("method");
            let action = this.getAttribute("action");
            fetch(action, { method: method, body: data })
            .then(response => response.json())
            .then(respuesta => {
                if(respuesta.url){ window.location.href = respuesta.url; } else { window.location.reload(); }
            })
            .catch(error => { console.error("Error:", error); window.location.reload(); });
        });
    });
</script>