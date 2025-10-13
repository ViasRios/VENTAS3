<?php

	namespace app\controllers;
	use app\models\mainModel;

	class loginController extends mainModel{

		/*----------  Controlador iniciar sesion  ----------*/
		public function iniciarSesionControlador($tabla = 'personal'){
    if(session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $usuario=$this->limpiarCadena($_POST['login_usuario']);
    $clave=$this->limpiarCadena($_POST['login_clave']);

    if($usuario=="" || $clave==""){
        echo '<article class="message is-danger">
          <div class="message-body">
            <strong>Ocurrió un error inesperado</strong><br>
            No has llenado todos los campos que son obligatorios
          </div>
        </article>';
        return;
    }

    if($this->verificarDatos("[-_a-zA-Z0-9$@.]{4,20}",$usuario)){
        echo '<article class="message is-danger">
          <div class="message-body">
            <strong>Ocurrió un error inesperado</strong><br>
            El USUARIO no coincide con el formato solicitado
          </div>
        </article>';
        return;
    }

    if($this->verificarDatos("[-_a-zA-Z0-9$@.]{5,100}",$clave)){
        echo '<article class="message is-danger">
          <div class="message-body">
            <strong>Ocurrió un error inesperado</strong><br>
            La CLAVE no coincide con el formato solicitado
          </div>
        </article>';
        return;
    }

    if ($tabla == 'personal') {
        $check_usuario = $this->ejecutarConsulta("SELECT * FROM personal WHERE usuario='$usuario' AND habilitado=1");
    } else if ($tabla == 'usuarios') {
        $check_usuario = $this->ejecutarConsulta("SELECT * FROM usuarios WHERE usuario='$usuario' AND activo=1");
    } else {
        echo '<article class="message is-danger">
          <div class="message-body">
            <strong>Ocurrió un error inesperado</strong><br>
            Tabla no soportada
          </div>
        </article>';
        return;
    }

    if($check_usuario->rowCount()==1){
        $check_usuario=$check_usuario->fetch();

        if ($tabla == 'personal' && $check_usuario['habilitado'] != 1) {
            echo '<article class="message is-danger">
            <div class="message-body">
                <strong>Acceso denegado</strong><br>
                Tu cuenta ha sido deshabilitada. Contacta al administrador.
            </div>
            </article>';
            exit;
        }

        if(
            ($tabla == 'personal' && password_verify($clave,$check_usuario['clave1']))
            ||
            ($tabla == 'usuarios' && password_verify($clave,$check_usuario['clave']))
        ){

            if ($tabla == 'personal') {
                $_SESSION['id']=$check_usuario['Idasesor'];
                $_SESSION['nombre']=$check_usuario['Nombre'];
                $_SESSION['usuario']=$check_usuario['usuario'];
                $_SESSION['foto']=$check_usuario['personal_foto'];
                $_SESSION['Puesto']=$check_usuario['Puesto'];  // Asegúrate de que el campo 'puesto' esté en la tabla.
            } else if ($tabla == 'usuarios') {
                $_SESSION['usuario']=$check_usuario['usuario'];
                $_SESSION['id_usuario']=$check_usuario['id'] ?? null;
            }
            var_dump($_SESSION['Puesto']); // Verifica si el valor es correcto
            session_write_close();

            // Redirigir según el puesto
            if ($tabla == 'personal') {
                $puesto = $_SESSION['Puesto'];

                if ($puesto == 'ASESOR' || $puesto == 'JEFE DE PRODUCCIÓN' || $puesto == 'JEFE_DE_PRODUCCION') {
                    header("Location: ".APP_URL."dashboard/"); // Redirigir a dashboard de administrador
                } else if ($puesto == 'TECNICO') {
                    header("Location: ".APP_URL."dashboardTec/"); // Redirigir a dashboard de técnico
                } else {
                    header("Location: ".APP_URL."dashboard/"); // Redirigir a un dashboard por defecto
                }
            } else if ($tabla == 'usuarios') {
              header("Location: ".APP_URL."dashboard2/");
            } else {
              header("Location: ".APP_URL."login/");
            }
            exit;

        } else {
            echo '<article class="message is-danger">
              <div class="message-body">
                <strong>Ocurrió un error inesperado</strong><br>
                Usuario o clave incorrectos
              </div>
            </article>';
        }

    } else {
        echo '<article class="message is-danger">
          <div class="message-body">
            <strong>Ocurrió un error inesperado</strong><br>
            Usuario o clave incorrectos
          </div>
        </article>';
    }
}

		/*----------  Controlador cerrar sesion  ----------*/
		public function cerrarSesionControlador(){

			session_destroy();

		    if(headers_sent()){
                echo "<script> window.location.href='".APP_URL."login/'; </script>";
            }else{
                header("Location: ".APP_URL."login/");
            }
		}
}