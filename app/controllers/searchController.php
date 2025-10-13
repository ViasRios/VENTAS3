<?php

	namespace app\controllers;
	use app\models\mainModel;

	class searchController extends mainModel{

		

		 /*----------  Controlador modulos de busquedas  ----------*/
    public function modulosBusquedaControlador($modulo){

        // Normaliza el slug (sin barras ni query)
        $modulo = trim((string)$modulo);
        $modulo = preg_replace('#[/?].*$#', '', $modulo);

        // Lista explícita (lo tuyo) + añadimos invoiceList
        $listaModulos = [
            'expensesSearch','dashboardTec','dashboard','invoiceSearch','invoiceList',
            'odsSearch','userSearch','cashierSearch','clientSearch',
            'categorySearch','productSearch','saleSearch'
        ];

        if (in_array($modulo, $listaModulos, true)) return true;

        // Respaldo: si el slug termina en List o Search, lo permitimos
        if (preg_match('/(?:List|Search)$/', $modulo)) return true;

        return false;
    }
    
    public function iniciarBuscadorControlador(){

    // Slug destino
    $url = $this->limpiarCadena($_POST['modulo_url'] ?? '');
    $url = preg_replace('#[/?].*$#', '', trim($url));
    
    // Si no se recibe un módulo específico, usar 'dashboard' por defecto
    if ($url === '') $url = 'dashboard';

    // Texto de búsqueda
    $texto = $this->limpiarCadena($_POST['txt_buscador'] ?? '');
    // Campo opcional (si lo usas)
    $filtro_campo = isset($_POST['filtro_campo']) ? $this->limpiarCadena($_POST['filtro_campo']) : null;

    // Verificación del módulo de búsqueda
    if (!$this->modulosBusquedaControlador($url)) {
        return json_encode([
            "tipo" => "simple",
            "titulo" => "Ocurrió un error inesperado",
            "texto" => "No podemos procesar la petición en este momento",
            "icono" => "error"
        ]);
    }

    // Verificación del término de búsqueda
    if ($texto === "") {
        return json_encode([
            "tipo" => "simple",
            "titulo" => "Ocurrió un error inesperado",
            "texto" => "Introduce un término de búsqueda",
            "icono" => "error"
        ]);
    }

    // Verificación de formato de búsqueda
    if ($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\- ]{1,30}", $texto)) {
        return json_encode([
            "tipo" => "simple",
            "titulo" => "Ocurrió un error inesperado",
            "texto" => "El término de búsqueda no coincide con el formato solicitado",
            "icono" => "error"
        ]);
    }

    // Guardar en sesión
    if ($filtro_campo !== null) {
        $_SESSION[$url . '_campo'] = $filtro_campo;
    }
    $_SESSION[$url] = $texto;

    // Si estamos en 'dashboardTec', redirigir a esa página
    if ($url === 'dashboardTec') {
        return json_encode([
            "tipo" => "redireccionar",
            "url" => APP_URL . 'dashboardTec/'  // Redirige a la misma página si es dashboardTec
        ]);
    }

    // Si estamos en 'dashboard', redirigir a esa página
    if ($url === 'dashboard') {
        return json_encode([
            "tipo" => "redireccionar",
            "url" => APP_URL . 'dashboard/'  // Redirige a la misma página si es dashboard
        ]);
    }

    // Si todo está bien, podemos realizar la búsqueda en la misma página y mostrar los resultados sin redirigir
    return json_encode([
        "tipo" => "resultado",
        "texto" => "Búsqueda realizada con éxito",
        "icono" => "success"
    ]);
}


    /*----------  Controlador eliminar busqueda  ----------*/
    public function eliminarBuscadorControlador(){

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