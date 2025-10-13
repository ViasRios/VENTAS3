<?php
namespace app\controllers;

use app\models\mainModel;
use PDO;

class almacenRegisterController extends mainModel {

    public function registrarUsuarioAlmacenControlador() {

        // --- 0) Sesión ---
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

        // --- 1) Solo POST (evita alta en refresh/GET) ---
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->respond(['ok'=>false,'error'=>'Método no permitido'], 405);
        }

        // ¿AJAX?
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                  && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // --- 2) Token anti-reenvío/CSRF (idempotencia) ---
        $token = $_POST['form_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['form_token'] ?? '', $token)) {
            return $this->respond(['ok'=>false,'error'=>'Token inválido'], 400, $isAjax);
        }
        // Consumir el token para evitar doble envío
        unset($_SESSION['form_token']);

        // --- 3) Limpiar datos ---
        $nombre  = $this->limpiarCadena($_POST['nuevo_nombre']  ?? '');
        $usuario = $this->limpiarCadena($_POST['nuevo_usuario'] ?? '');
        $clave   = $this->limpiarCadena($_POST['nuevo_clave']   ?? '');

        // --- 4) Validaciones mínimas ---
        if ($nombre === '' || $usuario === '' || $clave === '') {
            return $this->respond(['ok'=>false,'error'=>'Todos los campos son obligatorios'], 422, $isAjax);
        }
        if ($this->verificarDatos("[-_a-zA-Z0-9$@.]{4,20}", $usuario)) {
            return $this->respond(['ok'=>false,'error'=>'El USUARIO no tiene el formato correcto'], 422, $isAjax);
        }
        if ($this->verificarDatos("[-_a-zA-Z0-9$@.]{5,100}", $clave)) {
            return $this->respond(['ok'=>false,'error'=>'La CLAVE no tiene el formato correcto'], 422, $isAjax);
        }

        // --- 5) Checar duplicado con consulta preparada ---
        $db  = $this->conectar();
        $q1  = $db->prepare("SELECT 1 FROM usuarios WHERE usuario = :usuario LIMIT 1");
        $q1->execute([':usuario'=>$usuario]);
        if ($q1->fetchColumn()) {
            return $this->respond(['ok'=>false,'error'=>'El nombre de usuario ya está registrado'], 409, $isAjax);
        }

        // --- 6) Insert seguro ---
        $hash = password_hash($clave, PASSWORD_BCRYPT);
        $ins  = $db->prepare("INSERT INTO usuarios (nombre, usuario, clave) VALUES (:nombre, :usuario, :clave)");
        $ok   = $ins->execute([':nombre'=>$nombre, ':usuario'=>$usuario, ':clave'=>$hash]);

        if (!$ok) {
            return $this->respond(['ok'=>false,'error'=>'No se pudo registrar el usuario'], 500, $isAjax);
        }

        // --- 7) Éxito → AJAX = JSON | NO-AJAX = PRG ---
        if ($isAjax) {
            return $this->respond(['ok'=>true,'msg'=>'Usuario registrado','id'=>$db->lastInsertId()], 200, true);
        } else {
            // Post/Redirect/Get: evita que F5 re-envíe el POST
            $_SESSION['flash_ok'] = 'Usuario registrado exitosamente';
            header('Location: '.APP_URL.'usuariosNew/?created=1'); // ajusta la ruta destino
            exit;
        }
    }

    /**
     * Respuesta uniforme: si $asJson=true o AJAX => JSON; si no => HTML simple.
     */
    private function respond(array $payload, int $code = 200, bool $asJson = true) {
        http_response_code($code);
        if ($asJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($payload, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            // Mensaje HTML simple (fallback no-AJAX). Usa tus componentes Bulma si prefieres.
            $tipo = $payload['ok'] ?? false ? 'is-success' : 'is-danger';
            $msg  = htmlspecialchars($payload['error'] ?? ($payload['msg'] ?? ''), ENT_QUOTES, 'UTF-8');
            echo '<article class="message '.$tipo.'"><div class="message-body">'.$msg.'</div></article>';
            exit;
        }
    }

    
}
