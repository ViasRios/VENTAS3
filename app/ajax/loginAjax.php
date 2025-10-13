<?php
session_start();

// Requiere configuración y autoload
require_once __DIR__ . "/../../config/app.php";
require_once __DIR__ . "/../../autoload.php";

use app\controllers\loginController;

// Instancia del controlador
$insLogin = new loginController();

// Aquí puedes cambiar a 'usuarios' si es el login de otro tipo de cuenta
$insLogin->iniciarSesionControlador(); 
