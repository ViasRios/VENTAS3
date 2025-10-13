<?php
$modulo_actual = "dashboard";
 use app\controllers\odsController;
// Verificar si el puesto es ASESOR o JEFE DE PRODUCCIÓN
if ($_SESSION['Puesto'] == 'ASESOR' || $_SESSION['Puesto'] == 'JEFE DE PRODUCCION') 
?>

<!-- Dashboard para ASESOR y JEFE DE PRODUCCIÓN -->
<div class="container is-fluid">
    <div class="level">
        <div class="level-left">
            <h1 class="title">PÁGINA PRINCIPAL</h1>
        </div>
        <div class="level-right is-flex is-flex-direction-column">
            <button class="button is-link is-rounded btn-back mb-2" onclick="window.location.href='/VENTAS3/odsNew'">
            <i class="fas fa-plus"></i> &nbsp; Crear ODS
            </button>
            <button class="button is-success is-rounded btn-back mb-2" onclick="window.location.href='/VENTAS3/notasList'">
            <i class="fas fa-clipboard-list"></i> &nbsp; Últimas Notas de Contacto
            </button>
        </div>
    </div>

  	<div class="columns is-flex is-justify-content-center">
    	<figure class="image is-128x128">
    		<?php
    			if(is_file("./app/views/fotos/".$_SESSION['foto'])){
    				echo '<img class="is-rounded" src="'.APP_URL.'app/views/fotos/'.$_SESSION['foto'].'">';
    			}else{
    				echo '<img class="is-rounded" src="'.APP_URL.'app/views/fotos/default.png">';
    			}
    		?>
		</figure>
  	</div>
  	<div class="columns is-flex is-justify-content-center">
  		<h2 class="subtitle">¡BIENVENIDO <?php echo $_SESSION['nombre']." "; ?>!</h2>
  	</div>
</div>

<?php
	$total_ods=$insLogin->seleccionarDatos("Normal","ods","Idods",0);
	$total_clientes=$insLogin->seleccionarDatos("Normal","clientes WHERE Idcliente!='1'","Idcliente",0);
?>

<!-- Esta parte solo se muestra a ASESOR y JEFE DE PRODUCCIÓN -->
<div class="container pb-1 pt-1">
    <?php    
       
        $insOds = new odsController();

        // Código de búsqueda y visualización
        if(!isset($_SESSION[$url[0]]) && empty($_SESSION[$url[0]])){
    ?>
    <br>
    <div class="columns">
        <div class="column">
            <form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" >
                <input type="hidden" name="modulo_buscador" value="buscar">
                <input type="hidden" name="modulo_url" value="<?php echo $modulo_actual; ?>">
                <div class="columns">
                    <div class="column is-three-quarters">
                        <div class="field">
                            <p class="control">
                                <input class="input is-rounded" type="text" name="txt_buscador" placeholder="¿Qué estás buscando?" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,50}" maxlength="50" required>
                            </p>
                        </div>
                    </div>
                    <div class="column">
                        <div class="field">
                            <p class="control">
                                <button class="button is-info" type="submit">
                                    Buscar
                                </button>
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php }else{ ?>
    <div class="columns">
        <div class="column">
            <form class="has-text-centered mt-5 mb-3 FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" >
                <input type="hidden" name="modulo_buscador" value="eliminar">
                <input type="hidden" name="modulo_url" value="<?php echo $url[0]; ?>">
                <p><i class="fas fa-search fa-fw"></i> &nbsp; Estas buscando <strong>“<?php echo $_SESSION[$modulo_actual]; ?>”</strong></p>
                <br>
                <button type="submit" class="button is-danger is-rounded"><i class="fas fa-trash-restore"></i> &nbsp; Eliminar busqueda</button>
            </form>
        </div>
    </div>
    <?php
           echo $insOds->listarDashboardControlador($url[1],15,$url[0],$_SESSION[$url[0]]);
        }
    ?>

</div>

<script src="<?php echo APP_URL; ?>app/views/js/ajax.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    actualizarCampanita();
    setInterval(actualizarCampanita, 30000);
});
</script>


