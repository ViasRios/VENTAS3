<?php
    // 1. SEGURIDAD: VERIFICAR PUESTO
    // Solo permitimos el acceso a Jefes de Producción
    $puesto_actual = $_SESSION['Puesto'] ?? '';
    
    // Normalizamos quitando espacios o guiones bajos para comparar fácil
    $puesto_normalizado = str_replace('_', ' ', strtoupper($puesto_actual));

    if($puesto_normalizado != "JEFE DE PRODUCCION"){
        // Si no tiene permiso, lo mandamos al dashboard
        echo '
            <script>
                alert("ACCESO DENEGADO: Solo el Jefe de Producción puede configurar la caja.");
                window.location.href="'.APP_URL.'dashboard/";
            </script>
        ';
        exit();
    }

    // 2. PROCESAR EL CAMBIO DE CLAVE (Si se envió el formulario)
    if(isset($_POST['nueva_clave_caja']) && isset($_POST['conf_clave_caja'])){
        
        $nueva = $_POST['nueva_clave_caja'];
        $conf = $_POST['conf_clave_caja'];

        if($nueva == "" || $conf == ""){
            echo '<script>alert("Error: Los campos no pueden estar vacíos.");</script>';
        } elseif ($nueva != $conf) {
            echo '<script>alert("Error: Las contraseñas no coinciden.");</script>';
        } else {
            // Instanciamos el modelo para conectar a BD
            // Usamos loginController ya que hereda de mainModel y ya está cargado
            $insLogin = new app\controllers\loginController();
            
            // Encriptamos la clave
            $clave_hash = password_hash($nueva, PASSWORD_BCRYPT, ["cost"=>10]);
            
            // Usuario objetivo: La "Llave Maestra" (usuario_almacen)
            $usuario_objetivo = "usuario_almacen";
            
            // Preparamos la consulta directa para asegurar que funcione sin crear controladores nuevos
            $consulta = "UPDATE usuarios SET clave='$clave_hash' WHERE usuario='$usuario_objetivo'";
            
            $sql = $insLogin->ejecutarConsulta($consulta);

            if($sql->rowCount() >= 0){ // >= 0 porque si la clave es la misma no afecta filas pero es éxito
                echo '
                    <script>
                        alert("¡ÉXITO! La clave de acceso a Caja ha sido actualizada correctamente.");
                        window.location.href="'.APP_URL.'dashboard/";
                    </script>
                ';
            } else {
                echo '<script>alert("Error al actualizar la base de datos.");</script>';
            }
        }
    }
?>

<style>
    .key-container { font-family: 'Poppins', sans-serif; max-width: 600px; margin: 0 auto; }

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
        padding: 15px 20px; 
        text-align: center;
    }

    .form-card-body { padding: 20px 20px; background-color: #fff; }

    .input-group { margin-bottom: 20px; }
    
    .input {
        box-shadow: none !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        height: 2.8em; 
        transition: all 0.3s;
    }
    .input:focus {
        border-color: #1d4d80 !important;
        box-shadow: 0 0 0 3px rgba(29, 77, 128, 0.1) !important;
    }
    
    .btn-update {
        background: linear-gradient(135deg, #1d4d80 0%, #245c94 100%);
        border: none; color: white;
        transition: transform 0.2s;
        height: 2.7em;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .btn-update:hover { transform: translateY(-2px); color: white; opacity: 0.9; box-shadow: 0 4px 12px rgba(29, 77, 128, 0.3); }

    .icon-header { font-size: 3rem; margin-bottom: 10px; opacity: 0.9; }
</style>

<div class="container is-fluid mb-1 mt-2">
    <div class="key-container">
        <div class="form-card">
            <div class="form-card-header">
                <div class="icon-header"><i class="fas fa-key"></i></div>
                <h2 class="title is-4 has-text-white mb-1">Seguridad de Caja</h2>
                <p class="is-size-7" style="opacity: 0.8;">Actualizar contraseña maestra de acceso</p>
            </div>

            <div class="form-card-body">
                <form action="" method="POST" autocomplete="off">
                    <div class="notification is-light is-info is-small mb-3">
                        <i class="fas fa-info-circle mr-1"></i> Esta contraseña será utilizada por <strong>todo el personal</strong> para acceder a la caja.
                    </div>

                    <div class="field input-group">
                        <label class="label has-text-grey">Nueva Contraseña</label>
                        <div class="control has-icons-left">
                            <input class="input" type="password" name="nueva_clave_caja" placeholder="Escriba la nueva clave..." required pattern="[a-zA-Z0-9$@.-]{4,100}">
                            <span class="icon is-small is-left">
                                <i class="fas fa-lock has-text-grey-light"></i>
                            </span>
                        </div>
                    </div>

                    <div class="field input-group">
                        <label class="label has-text-grey">Confirmar Contraseña</label>
                        <div class="control has-icons-left">
                            <input class="input" type="password" name="conf_clave_caja" placeholder="Repita la clave..." required pattern="[a-zA-Z0-9$@.-]{4,100}">
                            <span class="icon is-small is-left">
                                <i class="fas fa-check-double has-text-grey-light"></i>
                            </span>
                        </div>
                    </div>

                    <div class="field mt-6">
                        <button type="submit" class="button btn-update is-fullwidth is-rounded">
                            ACTUALIZAR CLAVE
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <p class="has-text-centered mt-1 is-size-7 has-text-grey-light">
            Cambio autorizado solo para Jefe de Producción
        </p>

    </div>
</div>