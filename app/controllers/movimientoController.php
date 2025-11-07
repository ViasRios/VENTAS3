<?php
	namespace app\controllers;
	use app\models\mainModel;
	class movimientoController extends mainModel{
		/*----------  Controlador registrar movimiento  ----------*/
		public function registrarMovimientoControlador(){
			# Almacenando datos#
		    $idods = $this->limpiarCadena($_POST['Idods']);
		    $tipo = $this->limpiarCadena($_POST['Tipo']);
		    $cantidad = $this->limpiarCadena($_POST['Cantidad']);
		    $medio = $this->limpiarCadena($_POST['Medio']);
            // Fecha y Hora actuales
            $fecha = date("Y-m-d");
            $hora = date("H:i:s");
		    # Verificando campos obligatorios #
		    if($idods=="" || $tipo=="" || $cantidad=="" || $medio==""){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		    }

            # Validar cantidad como número
            if(!is_numeric($cantidad) || $cantidad <= 0){
                $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"La CANTIDAD debe ser un número mayor a cero.",
					"icono"=>"error"
				];
				return json_encode($alerta);
            }
            // 1. Construir la consulta SQL para INSERTAR el movimiento
            $sql_insert = "INSERT INTO movimientos(Idods, Fecha, Hora, Tipo, Cantidad, Medio) 
                           VALUES('$idods', '$fecha', '$hora', '$tipo', '$cantidad', '$medio')";

            // 2. Ejecutar la consulta de INSERCIÓN
			$registrar_movimiento = $this->ejecutarConsulta($sql_insert);
            
			if($registrar_movimiento->rowCount()==1){

                // --- ¡NUEVO CÓDIGO PARA ACTUALIZAR EL SALDO! ---
                
                // Definimos qué tipos de movimiento deben restar del saldo
                $tipos_que_restan = ["Pago", "Devolucion", "Anticipo", "Otro"];

                // Si el 'Tipo' es uno de los que restan...
                if (in_array($tipo, $tipos_que_restan)) {
                    
                    // 3. Construir la consulta SQL para ACTUALIZAR el saldo en la ODS
                    // (Usamos $cantidad, que ya está validado como numérico)
                    $sql_update_saldo = "UPDATE ods SET Cuenta = Cuenta - $cantidad WHERE Idods = $idods";
                    
                    // 4. Ejecutar la actualización del saldo
                    $this->ejecutarConsulta($sql_update_saldo);
                }
                // --- FIN DEL NUEVO CÓDIGO ---


				$alerta=[
					"tipo"=>"recargar", // Esto le dice al JS que recargue la página
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