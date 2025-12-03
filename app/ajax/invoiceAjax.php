<?php
    
    require_once "../../config/app.php";
    require_once "../views/inc/session_start.php";
    require_once "../../autoload.php";
    
    use app\controllers\invoiceController;

    // Establecemos la cabecera JSON para todas las respuestas
    header('Content-Type: application/json; charset=utf-8');

    if(isset($_POST['modulo_factura'])){

        $insFactura = new invoiceController();

        /* ========================================================
           NUEVO BLOQUE: Búsqueda de ODS para autocompletar
           ======================================================== */
        if($_POST['modulo_factura']=="buscar_ods"){
            
            $busqueda = $_POST['busqueda'] ?? '';
            $datosJson = [];

            if($busqueda != ''){
                // Usamos la conexión de tu modelo principal
                try {
                    $pdo = \app\models\mainModel::conectar();
                    
                    // Buscamos ID y datos del cliente (Nombre y Email)
                    // CAST(o.Idods AS CHAR) asegura que la búsqueda LIKE funcione bien en números
                    $sql = "SELECT o.Idods, c.Nombre, c.Email 
                            FROM ods o 
                            LEFT JOIN clientes c ON c.Idcliente = o.Idcliente 
                            WHERE CAST(o.Idods AS CHAR) LIKE :busqueda 
                            ORDER BY o.Idods DESC 
                            LIMIT 10";

                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':busqueda', "%$busqueda%", PDO::PARAM_STR);
                    $stmt->execute();
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        $datosJson[] = [
                            'Idods'   => $row['Idods'],
                            'Cliente' => $row['Nombre'],
                            'Email'   => $row['Email'] ?? '' // Si es null, enviamos vacío
                        ];
                    }
                } catch (\Exception $e) {
                    // Si falla la conexión, devuelve array vacío (no rompe el JS)
                }
            }

            echo json_encode($datosJson);
            exit(); // Terminamos aquí para que no se ejecute nada más
        }

        if($_POST['modulo_factura']=="registrar"){
            echo $insFactura->registrarFacturaControlador();
        }

        if($_POST['modulo_factura']=="eliminar"){
            echo $insFactura->eliminarFacturaControlador();
        }

        if($_POST['modulo_factura']=="actualizar"){
            echo $insFactura->actualizarFacturaControlador();
        }
        
    }else{
        session_destroy();
        header("Location: ".APP_URL."login/");
    }