<?php
	namespace app\controllers;
	use app\models\mainModel;
	class loginController extends mainModel{
		/*----------  Controlador iniciar sesion  ----------*/
    public function iniciarSesionPersonal(){
        $usuario = $this->limpiarCadena($_POST['login_usuario']);
        $clave = $this->limpiarCadena($_POST['login_clave']);
        if($usuario=="" || $clave==""){
            echo '<article class="message is-danger">
                  <div class="message-body"><strong>Error:</strong> Llene todos los campos.</div>
                </article>';
            return;
        }
        // ... (Aquí va tu lógica de verificación de formato de texto) ...
        // Verificar usuario en tabla PERSONAL
        $check_usuario = $this->ejecutarConsulta("SELECT * FROM personal WHERE usuario='$usuario' AND habilitado=1");
        if($check_usuario->rowCount()==1){
            $check_usuario=$check_usuario->fetch();
            
            if(password_verify($clave, $check_usuario['clave1'])){
                $_SESSION['id']=$check_usuario['Idasesor'];
                $_SESSION['nombre']=$check_usuario['Nombre'];
                $_SESSION['usuario']=$check_usuario['usuario'];
                $_SESSION['foto']=$check_usuario['personal_foto'];
                $_SESSION['Puesto']=$check_usuario['Puesto'];

                // Redirección según puesto
                if($check_usuario['Puesto']=="TECNICO"){
                     header("Location: ".APP_URL."dashboardTec/");
                } else {
                     header("Location: ".APP_URL."dashboard/");
                }
                exit;
            } else {
                 echo '<article class="message is-danger"><div class="message-body">Clave incorrecta</div></article>';
            }
        } else {
            echo '<article class="message is-danger"><div class="message-body">Usuario no encontrado o deshabilitado</div></article>';
        }
    }

    /*----------  Controlador para iniciar sesión CAJA (Solo Clave)  ----------*/
    public function iniciarSesionCaja(){

      // Aseguramos que la sesión esté iniciada para poder leer/escribir variables
      if (session_status() === PHP_SESSION_NONE) {
          session_start();
      }
      
      $clave = $this->limpiarCadena($_POST['login_clave']);

      // 1. Validar que haya escrito algo
      if($clave==""){
          echo '<article class="message is-danger">
                <div class="message-body"><strong>Error:</strong> Debe ingresar la clave de la caja.</div>
              </article>';
          return;
      }

      // 2. Verificar formato de clave
      if($this->verificarDatos("[-_a-zA-Z0-9$@.]{5,100}",$clave)){
          echo '<article class="message is-danger">
                <div class="message-body"><strong>Error:</strong> Formato de clave incorrecto.</div>
              </article>';
          return;
      }

      $usuarioCaja = "usuario_almacen"; 

      $check_usuario = $this->ejecutarConsulta("SELECT * FROM usuarios WHERE usuario='$usuarioCaja' AND activo=1");

      if($check_usuario->rowCount()==1){
          $data_usuario = $check_usuario->fetch();

          // Verificamos la contraseña
          if(password_verify($clave, $data_usuario['clave'])){
              
              // --- ÉXITO ---
              // Solo activamos el permiso, NO tocamos $_SESSION['usuario'] ni $_SESSION['nombre']
              $_SESSION['caja_activada'] = true; 
              
              if(headers_sent()){
                  echo "<script> window.location.href='".APP_URL."cashierNew/'; </script>";
              }else{
                  header("Location: ".APP_URL."cashierNew/");
              }
              exit;

          } else {
              echo '<article class="message is-danger">
                    <div class="message-body"><strong>Acceso denegado:</strong> La contraseña es incorrecta.</div>
                  </article>';
          }
      } else {
          echo '<article class="message is-danger">
                <div class="message-body"><strong>Error:</strong> No se encuentra la configuración de seguridad de la caja.</div>
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