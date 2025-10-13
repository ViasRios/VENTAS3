<?php
namespace app\controllers;
use app\models\mainModel;

// Incluir el archivo de autoload desde la raíz
require_once __DIR__ . '/../../../autoload.php';  // Subimos tres niveles para llegar a la raíz

// Incluir la clase loginController
require_once __DIR__ . '/../../controllers/loginController.php';  // Subimos dos niveles para llegar a /controllers

// Instanciar el controlador de login
$insLogin = new loginController();  // Aquí instanciamos el controlador de login

// Comprobar si el formulario para caja fue enviado
/*if (isset($_POST['login_clave'])) {
    // Llamamos a la función para iniciar sesión en Caja
    $insLogin->iniciarSesionCajaControlador();
    exit;
} */

// Si hay alguna otra lógica para el login de usuarios o personal, puedes agregarla aquí.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once __DIR__ . '/../inc/head.php'; ?>
</head>
<body>
     <h1>Proceso de Login</h1>
    <p>Este es el archivo de verificación de login.</p>
    <?php
        use app\controllers\viewsController;

        $viewsController = new viewsController();
        $vista = $viewsController->obtenerVistasControlador('login'); // Cargamos la vista del login

        require_once "./app/views/content/".$vista."-view.php";
    ?>
</body>
</html>
