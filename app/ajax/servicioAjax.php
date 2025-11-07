<?php
    
    require_once "../../config/app.php";
    require_once "../views/inc/session_start.php";
    require_once "../../autoload.php";
    
    use app\controllers\serviceController;

    // El 'if' principal debe revisar la clave singular
    if(isset($_POST['modulo_servicio'])){

        $insServicio = new serviceController();

        // Usamos 'else if' para que la lógica sea correcta
        if($_POST['modulo_servicio']=="registrar"){
            echo $insServicio->registrarServicioControlador();
        
        } else if($_POST['modulo_servicio']=="eliminar"){
            echo $insServicio->eliminarServicioControlador();
        
        } else if($_POST['modulo_servicio']=="actualizar"){
            echo $insServicio->actualizarServicioControlador();
        
        } 
        // CAMBIO AQUÍ: de 'modulo_servicios' (plural) a 'modulo_servicio' (singular)
        else if($_POST['modulo_servicio']=="buscar"){
            // No necesitamos un controlador para esto, podemos hacerlo aquí
            
            // 1. Incluye tu modelo principal (usa 'require_once' por si acaso)
            require_once "../models/mainModel.php";
            $mainModel = new \app\models\mainModel(); // Asegúrate de usar el namespace si existe
            
            // 2. Limpia el término de búsqueda
            $termino = $mainModel->limpiarCadena($_POST['termino']);

            // 3. Prepara la consulta
            // Usamos '%' al final para que busque 'mantenimiento...'
            $sql = "SELECT Descripcion, Costo FROM servicios WHERE Descripcion LIKE '".$termino."%' LIMIT 10";
            
            $resultados = $mainModel->ejecutarConsulta($sql);
            $items = $resultados->fetchAll(PDO::FETCH_ASSOC);
            
            // 4. Devuelve el JSON
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'items' => $items]);
        }
        
    }else{
        session_destroy();
      //  header("Location: ".APP_URL."login/");
    }