<?php
namespace app\controllers;
use app\models\mainModel;

class searchController extends mainModel{

    /*----------  Controlador modulos de busquedas  ----------*/
    public function modulosBusquedaControlador($modulo){
        // ESTA FUNCIÓN ESTÁ BIEN, SE QUEDA IGUAL
        $modulo = trim((string)$modulo);
        $modulo = preg_replace('#[/?].*$#', '', $modulo);
        $listaModulos = [
            'expensesSearch','dashboardTec','dashboard','invoiceSearch','invoiceList',
            'odsSearch','userSearch','cashierSearch','clientSearch',
            'categorySearch','productSearch','saleSearch'
        ];
        if (in_array($modulo, $listaModulos, true)) return true;
        if (preg_match('/(?:List|Search)$/', $modulo)) return true;
        return false;
    }
    
    /*----------  Controlador iniciar busqueda (UNIFICADO Y CORREGIDO)  ----------*/
    public function iniciarBuscadorControlador(){
        
        // 1. VALIDACIÓN (Tu código original, está perfecto)
        $url = $this->limpiarCadena($_POST['modulo_url'] ?? 'dashboard');
        $url = preg_replace('#[/?].*$#', '', trim($url));
        if ($url === '') $url = 'dashboard';

        $texto = $this->limpiarCadena($_POST['txt_buscador'] ?? '');
        $filtro_campo = isset($_POST['filtro_campo']) ? $this->limpiarCadena($_POST['filtro_campo']) : null;

        if (!$this->modulosBusquedaControlador($url)) {
            return json_encode(["tipo" => "simple", "titulo" => "Ocurrió un error", "texto" => "No podemos procesar la petición", "icono" => "error"]);
        }
        if ($texto === "") {
            return json_encode(["tipo" => "simple", "titulo" => "Ocurrió un error", "texto" => "Introduce un término de búsqueda", "icono" => "error"]);
        }
        if ($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\- ]{1,30}", $texto)) {
            return json_encode(["tipo" => "simple", "titulo" => "Ocurrió un error", "texto" => "El término de búsqueda no coincide con el formato", "icono" => "error"]);
        }

        // 2. LÓGICA DE BÚSQUEDA INTELIGENTE
        
        // Guardar en sesión
        if ($filtro_campo !== null) {
            $_SESSION[$url . '_campo'] = $filtro_campo;
        }
        $_SESSION[$url] = $texto;

        // Si la búsqueda es para 'odsSearch', usamos la lógica inteligente
        if ($url === 'odsSearch') {
            
            $param_busqueda = "%".$texto."%";
            
            // Usamos $this->conectar() para obtener la conexión PDO
            $pdo = $this->conectar();
            $sql = $pdo->prepare("SELECT 
                        ods.Idods 
                    FROM 
                        ods 
                    INNER JOIN 
                        clientes ON ods.Idcliente = clientes.Idcliente
                    WHERE 
                        ods.Idods LIKE :busqueda OR clientes.Idcliente LIKE :busqueda OR clientes.Nombre LIKE :busqueda");
            
            $sql->bindParam(":busqueda", $param_busqueda);
            $sql->execute();
            $resultados = $sql->fetchAll(\PDO::FETCH_ASSOC);
            $numero_de_resultados = count($resultados);

            // Decide a dónde redirigir
            if ($numero_de_resultados === 1) {
                // 1 resultado: va directo a la odsView
                $id_ods_unico = $resultados[0]['Idods'];
                $url_destino = APP_URL . 'odsView/' . $id_ods_unico . '/';
            } else {
                // 0 o varios resultados: va a la lista de búsqueda
                $url_destino = APP_URL . 'odsSearch' . '/';
            }

            return json_encode([
                "tipo" => "redireccionar",
                "url" => $url_destino
            ]);

        } else {
            // Para cualquier otra búsqueda (dashboard, userSearch, etc.), usa la lógica normal
            // CORREGIMOS EL BUG: cambiamos "resultado" por "redireccionar"
            return json_encode([
                "tipo" => "redireccionar",
                "url" => APP_URL . $url . "/"
            ]);
        }
    }

    /*----------  Controlador eliminar busqueda  ----------*/
    public function eliminarBuscadorControlador(){
        // ESTA FUNCIÓN ESTÁ BIEN, SE QUEDA IGUAL
        $url = $this->limpiarCadena($_POST['modulo_url'] ?? '');
        $url = preg_replace('#[/?].*$#', '', trim($url));
        if ($url==='') $url = 'dashboard';

        if(!$this->modulosBusquedaControlador($url)){
            return json_encode([
                "tipo"=>"simple",
                "titulo"=>"Ocurrió un error inesperado",
                "texto"=>"No podemos procesar la petición en este momento",
                "icono"=>"error"
            ]);
        }

        unset($_SESSION[$url]);
        unset($_SESSION[$url.'_campo']);

        return json_encode([
            "tipo"=>"redireccionar",
            "url"=>APP_URL.$url."/"
        ]);
    }
}