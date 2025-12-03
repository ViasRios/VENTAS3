<style>
    /* ESTILOS AESTHETIC - FORMULARIO */
    .invoice-container { font-family: 'Poppins', sans-serif; }

    /* Tarjeta Principal */
    .form-card { 
        background: white; 
        border-radius: 15px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.08); 
        overflow: hidden; 
        border: 1px solid rgba(0,0,0,0.02);
    }
    
    .form-card-header { 
        background: linear-gradient(135deg, #1d4d80 0%, #245c94 100%);
        color: white; 
        padding: 20px 30px; 
        font-weight: 600; 
        font-size: 1.1rem; 
        display: flex; align-items: center; justify-content: space-between;
    }

    .form-card-body { padding: 30px; background-color: #fff; }

    /* Área de Carga de PDF */
    .upload-section {
        background-color: #f8fbff;
        border: 2px dashed #a5c3e3;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
        margin-bottom: 25px;
    }
    .upload-section:hover {
        background-color: #f0f7ff;
        border-color: #1d4d80;
        box-shadow: 0 4px 12px rgba(29, 77, 128, 0.1);
    }

    /* Títulos de Sección */
    .section-title {
        color: #1d4d80;
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 10px;
        margin-bottom: 20px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-icon {
        background: #eef6fc;
        color: #1d4d80;
        width: 30px; height: 30px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
        font-size: 0.8rem;
    }

    /* Inputs Personalizados */
    .input, .select select, .textarea {
        box-shadow: none !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        transition: all 0.3s;
        height: 2.7em; /* Un poco más altos */
    }
    .input:focus, .select select:focus, .textarea:focus {
        border-color: #1d4d80 !important;
        box-shadow: 0 0 0 3px rgba(29, 77, 128, 0.1) !important;
    }
    .label { color: #64748b; font-weight: 500; font-size: 0.9rem; }

    /* Botones */
    .btn-save {
        background: linear-gradient(135deg, #1d4d80 0%, #245c94 100%);
        border: none;
        box-shadow: 0 4px 12px rgba(29, 77, 128, 0.3);
        transition: transform 0.2s;
    }
    .btn-save:hover { transform: translateY(-2px); color: white; }

</style>

<div class="container is-fluid mb-4 mt-4 invoice-container">
    
    <div class="columns is-vcentered mb-4">
        <div class="column">
            <h1 class="title is-3" style="color: #1d4d80; font-weight: 800;">
                <i class="fas fa-plus-circle mr-2"></i> Nueva Factura
            </h1>
            <p class="subtitle is-6" style="color: #888;">Complete la información fiscal o cargue la constancia PDF.</p>
        </div>
        <div class="column is-narrow">
            <a href="<?php echo APP_URL; ?>invoiceList/" class="button is-white is-rounded shadow-sm" style="color: #555;">
                <i class="fas fa-arrow-left mr-2"></i> Regresar
            </a>
        </div>
    </div>

    <?php
        use app\controllers\invoiceController;
        $insInvoice = new invoiceController();
        echo $insInvoice->formNuevaFacturaControlador();
    ?>

</div>