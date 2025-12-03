<style>
    /* ESTILOS AESTHETIC - FORMULARIO */
    .form-container { font-family: 'Poppins', sans-serif; }

    /* Tarjeta Principal */
    .form-card { 
        background: white; 
        border-radius: 15px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
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

    /* Inputs Personalizados */
    .input, .select select, .textarea {
        box-shadow: none !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        transition: all 0.3s;
        height: 2.7em;
        background-color: #fcfcfc;
    }
    .input:focus, .select select:focus, .textarea:focus {
        border-color: #1d4d80 !important;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(29, 77, 128, 0.1) !important;
    }
    
    .label { 
        color: #64748b; 
        font-weight: 500; 
        font-size: 0.9rem; 
        margin-bottom: 0.5em;
    }

    /* Estilo para el input de archivo */
    .file-cta {
        background-color: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
        color: #1d4d80 !important;
    }
    .file-name {
        border-color: #e2e8f0 !important;
    }

    /* Botones */
    .btn-save {
        background: linear-gradient(135deg, #1d4d80 0%, #245c94 100%);
        border: none;
        color: white;
        box-shadow: 0 4px 12px rgba(29, 77, 128, 0.3);
        transition: transform 0.2s;
        padding-left: 2em; padding-right: 2em;
    }
    .btn-save:hover { transform: translateY(-2px); color: white; }

    .btn-clean {
        background: white;
        border: 1px solid #e2e8f0;
        color: #64748b;
        transition: all 0.2s;
    }
    .btn-clean:hover { background: #f1f5f9; color: #1d4d80; }

    /* Sección Header */
    .page-header-title { color: #1d4d80; font-weight: 800; font-size: 1.5rem; margin-bottom: 2px; }
    .page-header-subtitle { color: #888; font-size: 0.9rem; }
</style>

<div class="container is-fluid mb-4 mt-4 form-container">

    <div class="columns is-vcentered mb-4">
        <div class="column">
            <h1 class="page-header-title">
                <i class="fas fa-user-tie mr-2"></i> Nuevo Personal
            </h1>
            <p class="page-header-subtitle">Complete el formulario para registrar un nuevo colaborador.</p>
        </div>
        <div class="column is-narrow">
            <a href="<?php echo APP_URL; ?>userList/" class="button is-white is-rounded shadow-sm" style="color: #555; border: 1px solid #eee;">
                <i class="fas fa-arrow-left mr-2"></i> Regresar
            </a>
        </div>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <span><i class="fas fa-edit mr-2"></i> Información del Usuario</span>
            <span style="font-size: 0.8em; opacity: 0.9;">
                <i class="fas fa-info-circle"></i> Los campos con <?php echo CAMPO_OBLIGATORIO; ?> son requeridos
            </span>
        </div>

        <div class="form-card-body">
            <form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/personalAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
                <input type="hidden" name="modulo_personal" value="registrar">
                
                <div class="columns">
                    <div class="column">
                        <div class="control">
                            <label class="label">Nombre Completo <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <input class="input" type="text" name="Nombre" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" maxlength="40" placeholder="Ej. Juan Pérez" required >
                        </div>
                    </div>
                    <div class="column">
                        <div class="control">
                            <label class="label">Teléfono <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <input class="input" type="text" name="Telefono" pattern="[0-9]{7,15}" maxlength="15" placeholder="Ej. 7711234567" required >
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column is-half">
                        <div class="control">
                            <label class="label">Puesto Asignado <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <div class="select is-fullwidth">
                                <select name="Puesto" required>
                                    <option value="" disabled selected>Seleccione una opción</option>
                                    <option value="ASESOR">ASESOR</option>
                                    <option value="TECNICO">TECNICO</option>
                                    <option value="JEFE_DE_PRODUCCION">JEFE DE PRODUCCION</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    </div>

                <hr style="background-color: #f1f5f9; margin: 20px 0;">

                <div class="columns">
                    <div class="column">
                        <div class="control">
                            <label class="label">Nombre de Usuario <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <input class="input" type="text" name="usuario" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" placeholder="Ej. jperez2024" required >
                        </div>
                    </div>
                    <div class="column">
                        <div class="control">
                            <label class="label">Correo Electrónico</label>
                            <input class="input" type="email" name="email" maxlength="70" placeholder="correo@ejemplo.com" >
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <div class="control">
                            <label class="label">Contraseña <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <input class="input" type="password" name="usuario_clave_1" pattern="[a-zA-Z0-9$@.\-]{5,100}" maxlength="100" required >
                        </div>
                    </div>
                    <div class="column">
                        <div class="control">
                            <label class="label">Repetir Contraseña <?php echo CAMPO_OBLIGATORIO; ?></label>
                            <input class="input" type="password" name="usuario_clave_2" pattern="[a-zA-Z0-9$@.\-]{5,100}" maxlength="100" required >
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column is-half">
                        <label class="label">Foto de Perfil</label>
                        <div class="file has-name is-fullwidth">
                            <label class="file-label">
                                <input class="file-input" type="file" name="personal_foto" accept=".jpg, .png, .jpeg" >
                                <span class="file-cta">
                                    <span class="file-icon"><i class="fas fa-upload"></i></span>
                                    <span class="file-label">Seleccionar imagen</span>
                                </span>
                                <span class="file-name">JPG, JPEG, PNG (Max 5MB)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="has-text-centered mt-5">
                    <button type="reset" class="button btn-clean is-rounded mr-3">
                        <i class="fas fa-paint-roller mr-2"></i> Limpiar
                    </button>
                    <button type="submit" class="button btn-save is-rounded">
                        <i class="far fa-save mr-2"></i> Guardar Usuario
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>