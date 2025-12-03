<?php
    require_once "./config/app.php";
    require_once "./autoload.php";
    require_once "./app/views/inc/session_start.php";

    use app\controllers\loginController;
    $insLogin = new loginController();
    // DETECTAR TIPO DE LOGIN
    if(isset($_POST['login_clave'])){
        // Si viene el campo 'login_almacen', es LOGIN DE CAJA
        if(isset($_POST['login_almacen'])){
            $insLogin->iniciarSesionCaja(); 
        } 
        // Si viene 'login_usuario', es LOGIN DE PERSONAL
        elseif(isset($_POST['login_usuario'])) {
            $insLogin->iniciarSesionPersonal();
        }
    }
    if(isset($_GET['views'])){
        $url=explode("/", $_GET['views']);
    }else{
        $url=["login"];
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once "./app/views/inc/head.php"; ?>
</head>
<body>
    <?php
        use app\controllers\viewsController;
        $insLogin = new loginController();
        $viewsController= new viewsController();
        $vista=$viewsController->obtenerVistasControlador($url[0]);
        if($vista=="login" || $vista=="404"){
            require_once "./app/views/content/".$vista."-view.php";
        }else{
    ?>
    <main class="page-container">
    <?php
            # Cerrar sesion #
            if((!isset($_SESSION['id']) || $_SESSION['id']=="") || (!isset($_SESSION['usuario']) || $_SESSION['usuario']=="")){
                $insLogin->cerrarSesionControlador();
                exit();
            }
            require_once "./app/views/inc/navlateral.php";
    ?>      
        <section class="full-width pageContent scroll" id="pageContent">
            <?php
                require_once "./app/views/inc/navbar.php";

                require_once $vista;

            ?>
        </section>
    </main>
    <?php
        }

        require_once "./app/views/inc/script.php"; 
    ?>
</body>
</html>