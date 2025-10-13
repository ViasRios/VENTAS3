<?php
	
	namespace app\models;

	class viewsModel{

		/*---------- Modelo obtener vista ----------*/
		protected function obtenerVistasModelo($vista){

			$listaBlanca=["odsMe","dashboardTec","loginCaja","notasList","proveedorNew","proveedorList","refaccionEstado","peticionRefaccion","refaccionHistorial","odsView","odsListStatus","odsStatus","pedidosList","refaccionesList","inventarioList","inventarioNew","refaccionNew","pedidosNew","registrarUsuarioAlmacen","loginAlmacen","dashboard2","odsUpdate","odsSearch","odsNew","odsList","expensesList","expensesNew","expensesUpdate","expensesSearch","invoiceUpdate","invoiceList","invoiceNew","invoiceSearch","serviceList","serviceNew","serviceSearch","serviceUpdate","dashboard","cashierNew","cashierList","cashierSearch","cashierUpdate","userNew","userList","userUpdate","userSearch","userPhoto","clientNew","clientList","clientSearch","clientUpdate","categoryNew","categoryList","categorySearch","categoryUpdate","productPhoto","productCategory","companyNew","saleNew","saleList","saleSearch","saleDetail","logOut"];

			if(in_array($vista, $listaBlanca)){
				if(is_file("./app/views/content/".$vista."-view.php")){
					$contenido="./app/views/content/".$vista."-view.php";
				}else{
					$contenido="404";
				}
			}elseif($vista=="login" || $vista=="index"){
				$contenido="login";
			}else{
				$contenido="404";
			}
			return $contenido;
		}

	}