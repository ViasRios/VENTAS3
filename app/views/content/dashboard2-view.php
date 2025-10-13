<?php
		$modulo_actual = "dashboard2";
?>
<div class="container is-fluid">
	<h1 class="title">PÁGINA PEDIDOS</h1>
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
  		<h2 class="subtitle">¡Bienvenido <?php echo $_SESSION['usuario']." "; ?>!</h2>
  	</div>
</div>

<?php
	$total_pedidos=$insLogin->seleccionarDatos("Normal","pedidos","IdPedidos",0);
	$total_refacciones=$insLogin->seleccionarDatos("Normal","refacciones", "IdProducto", 0);
    $total_inventario = $insLogin->seleccionarDatos("Normal","inventario", "IdProducto", 0);
?>

<div class="container pb-6 pt-6">
    <?php
        use app\controllers\odsController;
        $insOds = new odsController();
        if(!isset($_SESSION[$url[0]]) && empty($_SESSION[$url[0]])){
    ?>
    <div class="columns">
        <div class="column">
            <form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" >
                <input type="hidden" name="modulo_buscador" value="buscar">
                <input type="hidden" name="modulo_url" value="<?php echo $modulo_actual; ?>">
            
                <div class="field is-grouped">
                <!--    <p class="control ">
                        <span class="select">
                            <select name="filtro_campo" required>
                                <option value="Idasesor">ID ODS</option>
                                <option value="Numero">Numero</option>
                            </select>
                        </span>
                    </p> -->
                    <p class="control is-expanded">
                        <input class="input is-rounded" type="text" name="txt_buscador" placeholder="¿Qué estas buscando?" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,50}" maxlength="50" required>
                    </p>
                    <p class="control">
                        <button class="button is-info" type="submit">
                            Buscar
                        </button>
                    </p>
                </div>
            </form>
        </div>
    </div>
    <?php }else{ ?>
    <div class="columns">
        <div class="column">
            <form class="has-text-centered mt-6 mb-6 FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" >
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

<div class="container pb-6 pt-6">
	<div class="columns pb-6">
		<div class="column">
			<nav class="level is-mobile">
			  	<div class="level-item has-text-centered">
				    <a href="<?php echo APP_URL; ?>pedidosNew/">
				      	<p class="heading" style="font-weight: bold; font-size: 1rem;">
                            <i class="fas fa-cash-register fa-fw"></i> &nbsp; Pedidos</p>
				      	<p class="title"><?php echo $total_pedidos->rowCount(); ?></p>
				    </a>
			  	</div>
                
                <?php
                    $cantidad_pendientes = $total_inventario->rowCount();
                    $estilo = "";

                    if ($cantidad_pendientes >= 4) {
                        $estilo = 'style="background-color: #f86b6bff;"';
                    } elseif ($cantidad_pendientes >= 1) {
                        $estilo = 'style="background-color: #fbcd51ff;"';
                    }else {
                        $estilo = 'style="background-color: #f7e9c3ff;"';
                    }
                ?>
                <div class="level-item has-text-centered" <?php echo $estilo; ?>>
                        <a href="<?php echo APP_URL; ?>refaccionNew/">
                            <p class="heading" style="font-weight: bold; font-size: 1rem;">
                                <i class="fas fa-users fa-fw"></i> &nbsp; Refacciones
                            </p>
                            <p class="title"><?php echo $cantidad_pendientes; ?></p>
                        </a>
                </div>

                <div class="level-item has-text-centered">
			    	<a href="<?php echo APP_URL; ?>inventarioList/">
			      		<p class="heading" style="font-weight: bold; font-size: 1rem;">
                            <i class="fas fa-boxes fa-fw"></i> &nbsp; Inventario
                        </p>
			      		<p class="title"><?php echo $total_inventario->rowCount(); ?></p>
			    	</a>
			  	</div>
			</nav>
		</div>
	</div>
</div>
