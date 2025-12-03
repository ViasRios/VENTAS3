<?php
	namespace app\controllers;
	use app\models\mainModel;

	class movimientoController extends mainModel{

		/*----------  Controlador registrar movimiento  ----------*/
		public function registrarMovimientoControlador(){
			
		    // 1. Almacenando datos
		    $idods = $this->limpiarCadena($_POST['Idods']);
		    $tipo = $this->limpiarCadena($_POST['Tipo']);
		    $cantidad = $this->limpiarCadena($_POST['Cantidad']);
		    $medio = $this->limpiarCadena($_POST['Medio']);
            
            // Fecha y Hora actuales
            $fecha = date("Y-m-d");
            $hora = date("H:i:s");

		    // 2. Verificando campos obligatorios
		    if($idods=="" || $tipo=="" || $cantidad=="" || $medio==""){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

            // 3. Validar cantidad como número
            if(!is_numeric($cantidad) || $cantidad <= 0){
                $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La CANTIDAD debe ser un número mayor a cero.",
					"icono"=>"error"
				];
				return json_encode($alerta);
            }

            // 4. Insertar el movimiento en el historial
            $sql_insert = "INSERT INTO movimientos(Idods, Fecha, Hora, Tipo, Cantidad, Medio) 
                           VALUES('$idods', '$fecha', '$hora', '$tipo', '$cantidad', '$medio')";

			$registrar_movimiento = $this->ejecutarConsulta($sql_insert);
            
			if($registrar_movimiento->rowCount()==1){

                // --- CORRECCIÓN DE LA LÓGICA DE SALDO ---
                
                // CASO A: Si es dinero que ENTRA (Suma)
                if ($tipo == "Pago" || $tipo == "Anticipo" || $tipo == "Otro") {
                    
                    $sql_update_saldo = "UPDATE ods SET Cuenta = Cuenta + $cantidad WHERE Idods = $idods";
                    $this->ejecutarConsulta($sql_update_saldo);

                } 
                // CASO B: Si es dinero que SALE (Resta)
                elseif ($tipo == "Devolución" || $tipo == "Devolucion") {
                    
                    $sql_update_saldo = "UPDATE ods SET Cuenta = Cuenta - $cantidad WHERE Idods = $idods";
                    $this->ejecutarConsulta($sql_update_saldo);
                }
                
                // --- FIN CORRECCIÓN ---

				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Pago registrado",
					"texto"=>"El movimiento de tipo '$tipo' por $$cantidad se registró con éxito.",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No se pudo registrar el movimiento, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}
	}