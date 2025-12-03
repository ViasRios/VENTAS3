<?php
use app\controllers\invoiceController;
$h = function($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
// Evitar notices
$pagina = (isset($url[1]) && is_numeric($url[1]) && (int)$url[1] > 0) ? (int)$url[1] : 1;
$slug   = isset($url[0]) && $url[0] !== '' ? $url[0] : 'invoiceList';
$insInvoice = new invoiceController();
$filtro = isset($_SESSION[$slug]) ? $_SESSION[$slug] : "";
?>

<style>
    /* ESTILOS AESTHETIC (Unificados) */
    .invoice-container { font-family: 'Poppins', sans-serif; }
    
    .dashboard-card { 
        background: white; 
        border-radius: 15px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
        overflow: hidden; 
        border: 1px solid rgba(0,0,0,0.05);
        display: flex; flex-direction: column;
    }
    
    .dashboard-card-header { 
        background: linear-gradient(135deg, #1d4d80 0%, #245c94 100%);
        color: white; 
        padding: 15px 25px; 
        font-weight: 600; 
        font-size: 1rem; 
        display: flex; align-items: center; justify-content: space-between;
    }

    .dashboard-card-body { padding: 20px; background-color: #fff; flex-grow: 1; }

    /* Estilos del Buscador (Cápsula) */
    .filter-box { 
        background: #fff; 
        padding: 8px 20px; 
        border-radius: 50px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
        border: 1px solid #eee; 
        display: flex; 
        align-items: center; 
        gap: 10px;
        max-width: 600px;
    }

    .search-input-aesthetic {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
        color: #555;
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    .search-btn-aesthetic {
        background: linear-gradient(135deg, #1d4d80 0%, #245c94 100%);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 8px 20px;
        cursor: pointer;
        transition: transform 0.2s;
        font-size: 0.9rem;
        font-weight: 600;
        box-shadow: 0 3px 10px rgba(29, 77, 128, 0.3);
    }
    .search-btn-aesthetic:hover { transform: translateY(-2px); }

    .welcome-title { color: #1d4d80; font-weight: 800; font-size: 1.5rem; margin-bottom: 5px; }
    .welcome-subtitle { color: #888; font-size: 0.9rem; }

    /* Tabla */
    .table-container table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
    .table-container thead th { border: none !important; background-color: #f8f9fa; color: #1d4d80 !important; text-transform: uppercase; font-size: 0.85rem; padding: 15px; }
    .table-container tbody tr { background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.02); transition: transform 0.2s; }
    .table-container tbody tr:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .table-container td { 
        border: none !important; 
        vertical-align: middle; 
        color: #555;
        padding: 15px 40px 15px 0px; 
    }  
</style>

<div class="container is-fluid mb-1 mt-1 invoice-container">
    
    <div class="columns is-vcentered mb-1">
        <div class="column">
            <h1 class="welcome-title"><i class="fas fa-file-invoice-dollar mr-2"></i> Facturación</h1>
            <p class="welcome-subtitle">Gestión y control de comprobantes fiscales.</p>
        </div>
    </div>

    <div class="mb-1">
        <?php if (!isset($_SESSION[$slug]) || empty($_SESSION[$slug])): ?>
            <div class="filter-box">
                <form class="FormularioBuscador" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" style="width: 100%; display: flex; align-items: center; justify-content: space-between;">
                    <input type="hidden" name="modulo_buscador" value="buscar">
                    <input type="hidden" name="modulo_url" value="<?php echo $slug; ?>">
                    
                    <div style="flex-grow: 1; display: flex; align-items: center;">
                        <i class="fas fa-search" style="color: #ccc; margin-right: 10px;"></i>
                        <input class="search-input-aesthetic" type="text" name="txt_buscador" placeholder="Ingrese ODS para buscar si tiene factura..." required>
                    </div>
                    
                    <button type="submit" class="search-btn-aesthetic">
                        Buscar
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="filter-box" style="background-color: #fff8f8; border-color: #ffecec;">
                <form class="FormularioBuscador" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" style="width: 100%; display: flex; align-items: center; justify-content: space-between;">
                    <input type="hidden" name="modulo_buscador" value="eliminar">
                    <input type="hidden" name="modulo_url" value="<?php echo $slug; ?>">
                    
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-filter" style="color: #ef4444;"></i>
                        <span style="color: #555;">Resultados para: <strong>“<?php echo $h($_SESSION[$slug] ?? ''); ?>”</strong></span>
                    </div>
                    
                    <button type="submit" class="button is-danger is-small is-rounded" style="box-shadow: 0 2px 5px rgba(239, 68, 68, 0.3);">
                        <i class="fas fa-times mr-1"></i> Limpiar
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <span><i class="fas fa-list-ul mr-2"></i> Listado de Facturas</span>
            <span class="tag is-light is-rounded" style="color: #1d4d80; font-weight: bold;">
                <?php echo date('Y'); ?>
            </span>
        </div>
        
        <div class="dashboard-card-body">
            <div class="table-container">
                <?php
                    echo $insInvoice->listarFacturaControlador($pagina, 15, $slug, $filtro);
                ?>
            </div>
        </div>
    </div>

</div>

<script>
    document.querySelectorAll(".FormularioBuscador").forEach(form => {
        form.addEventListener("submit", function(e){
            e.preventDefault(); // 1. Detiene el envío normal (evita nueva pestaña y error de historial)
            
            let data = new FormData(this);
            let method = this.getAttribute("method");
            let action = this.getAttribute("action");

            fetch(action, {
                method: method,
                body: data
            })
            .then(response => response.json())
            .then(respuesta => {
                // 2. Si el backend responde una URL, vamos ahí.
                // Usar location.href es lo que permite que el botón "Atrás" funcione bien.
                if(respuesta.url){
                    window.location.href = respuesta.url;
                } else {
                    // Si no trae URL, recargamos la actual para ver cambios
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error("Error en búsqueda:", error);
                // Fallback por si falla el JSON
                window.location.reload();
            });
        });
    });
</script>