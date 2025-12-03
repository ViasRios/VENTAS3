<?php
// 1. CONFIGURACIÓN DE SEGURIDAD Y LIMPIEZA
// Evita que errores de PHP rompan el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_start(); 

$response = [];
$debug = [];

try {
    $peticionAjax = true;
    //   2. CARGA DE ARCHIVOS (EN EL ORDEN CORRECTO)    
    // A. Primero Configuración (Para tener APP_SESSION_NAME)
    if(file_exists("../../config/app.php")){
        require_once "../../config/app.php";
    } else {
        throw new Exception("Falta config/app.php");
    }
    // B. Segundo: Autoload (Para poder usar mainModel)
    require_once "../../autoload.php";
    // C. Tercero: Iniciar Sesión del Sistema
    $archivo_session = "../views/inc/session_start.php";
    if (file_exists($archivo_session)) {
        require_once $archivo_session;
    } else {
        if (session_status() == PHP_SESSION_NONE) session_start();
    }

    //  3. DETECCIÓN INTELIGENTE DE ID Y PUESTO
    $miId = 0;
    $miNombre = '';
    // Buscamos en las variables típicas
    if (isset($_SESSION['Idasesor'])) $miId = $_SESSION['Idasesor'];
    elseif (isset($_SESSION['id']))   $miId = $_SESSION['id'];
    elseif (isset($_SESSION['usuario_id'])) $miId = $_SESSION['usuario_id'];

    if (isset($_SESSION['usuario'])) $miNombre = $_SESSION['usuario'];

    // DEBUG: Enviamos esto al navegador para ver qué detectó
    $debug['Detectado'] = "ID: $miId | Usuario: $miNombre";

    if ($miId == 0) {
        // Si sigue saliendo 0, es que la sesión se perdió o no se inició bien
        $response = ["total" => 0, "html" => "", "debug" => $debug];
    } else {
        
        // Instanciamos el modelo (usando ruta completa para evitar errores)
        $insMain = new \app\models\mainModel();

        // --- A. CONSULTAR NOTIFICACIONES ---
        if (isset($_POST['modulo_notificacion']) && $_POST['modulo_notificacion'] == 'consultar') {
            
            // Buscamos mensajes dirigidos a MI ID que no estén leídos
            $sql = "SELECT * FROM notificaciones WHERE Idasesor = '$miId' AND leido = 0 ORDER BY fecha DESC LIMIT 10";
            $datos = $insMain->ejecutarConsulta($sql);
            
            if($datos){
                $rows = $datos->fetchAll();
                $total = count($rows);
                $html = '';

                if ($total > 0) {
                    foreach ($rows as $row) {
                        preg_match('/#(\d+)/', $row['mensaje'], $match);
                        $idOds = $match[1] ?? 0;
                        $link = ($idOds > 0) ? APP_URL . "odsView/" . $idOds . "/" : "#";
                        $fecha = date("d/m H:i", strtotime($row['fecha']));

                        // ... dentro del foreach ...
                        $html .= '
                        <div class="notif-item" onclick="marcarLeido('.$row['id'].', \''.$link.'\')">
                            <div class="notif-icon-box">
                                <i class="fas fa-info"></i>
                            </div>
                            <div class="notif-body">
                                <div class="notif-message">'.$row['mensaje'].'</div>
                                <div class="notif-date">'.$fecha.'</div>
                            </div>
                        </div>';
                    }
                } else {
                    $html = '<div class="notif-empty">No hay notificaciones nuevas</div>';
                }
                
                $response = ["total" => $total, "html" => $html, "debug" => $debug];
            } else {
                $response = ["total" => 0, "html" => "Error SQL", "debug" => $debug];
            }

        // --- B. MARCAR COMO LEÍDO ---
        } elseif (isset($_POST['accion']) && $_POST['accion'] == 'marcar_leido') {
             $idNotif = isset($_POST['id']) ? intval($_POST['id']) : 0;
             if($idNotif > 0){
                 $insMain->ejecutarConsulta("UPDATE notificaciones SET leido = 1 WHERE id = $idNotif AND Idasesor = $miId");
                 $response = ['ok' => true];
             }
        }
    }

} catch (Throwable $e) {
    $response = ["total" => 0, "html" => "", "error_fatal" => $e->getMessage()];
}

   // 4. SALIDA FINAL (JSON LIMPIO)
ob_clean(); // Borrar cualquier warning previo
header('Content-Type: application/json');
echo json_encode($response);
exit();

