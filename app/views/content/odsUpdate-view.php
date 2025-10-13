<div class="container is-fluid mb-6">
	<?php 

		$id=$insLogin->limpiarCadena($url[1]);

		if($id==$_SESSION['id']){ 
	?>
	<h1 class="title">Mi cuenta</h1>
	<h2 class="subtitle"><i class="fas fa-sync-alt"></i> &nbsp; Actualizar cuenta</h2>
	<?php }else{ ?>
	<h1 class="title">Personal</h1>
	<h2 class="subtitle"><i class="fas fa-sync-alt"></i> &nbsp; Actualizar personal</h2>
	<?php } ?>
</div>
<div class="container pb-6 pt-6">
	<?php
	
		include "./app/views/inc/btn_back.php";

		$datos=$insLogin->seleccionarDatos("Unico","personal","Idasesor",$id);

		if($datos->rowCount()==1){
			$datos=$datos->fetch();
	?>

	<div class="columns is-flex is-justify-content-center">
    	<figure class="image is-128x128">
    		<?php
    			if(is_file("./app/views/fotos/".$datos['personal_foto'])){
    				echo '<img class="is-rounded" src="'.APP_URL.'app/views/fotos/'.$datos['personal_foto'].'">';
    			}else{
    				echo '<img class="is-rounded" src="'.APP_URL.'app/views/fotos/default.png">';
    			}
    		?>
		</figure>
  	</div>

	<h2 class="title has-text-centered"><?php echo $datos['Nombre']." ".$datos['usuario']; ?></h2>

	<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/personalAjax.php" method="POST" autocomplete="off" >

		<input type="hidden" name="modulo_personal" value="actualizar">
		<input type="hidden" name="Idasesor" value="<?php echo $datos['Idasesor']; ?>">

		<div class="columns">
		  	<div class="column">
		    	<div class="control">
					<label>Nombres <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="text" name="Nombre" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" maxlength="40" value="<?php echo $datos['Nombre']; ?>" required >
				</div>
		  	</div>
		  	<div class="column">
		    	<div class="control">
					<label>Telefono <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="text" name="Telefono" pattern="[0-9]{7,15}" maxlength="15" value="<?php echo $datos['Telefono']; ?>" required >
				</div>
		  	</div>
		</div>
		<div class="columns">
		  	<div class="column">
		    	<div class="control">
					<label>Usuario <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="text" name="usuario" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" value="<?php echo $datos['usuario']; ?>" required >
				</div>
		  	</div>
		  	<div class="column">
		    	<div class="control">
					<label>Email</label>
				  	<input class="input" type="email" name="email" maxlength="70" value="<?php echo $datos['email']; ?>" >
				</div>
		  	</div>
		</div>
	<!--	<div class="columns">
		  	<div class="column">
		  		<label>Caja de ventas <?php echo CAMPO_OBLIGATORIO; ?></label><br>
				<div class="select">
				  	<select name="usuario_caja">
                        <?php
                            $datos_cajas=$insLogin->seleccionarDatos("Normal","caja","*",0);

                            while($campos_caja=$datos_cajas->fetch()){
                            	if($campos_caja['caja_id']==$datos['caja_id']){
                            		echo '<option value="'.$campos_caja['caja_id'].'" selected="" >Caja No.'.$campos_caja['caja_numero'].' - '.$campos_caja['caja_nombre'].' (Actual)</option>';
                            	}else{
                                	echo '<option value="'.$campos_caja['caja_id'].'">Caja No.'.$campos_caja['caja_numero'].' - '.$campos_caja['caja_nombre'].'</option>';
                            	}
                            }
                        ?>
				  	</select>
				</div>
		  	</div>
		</div> -->
		<br><br>
		<p class="has-text-centered">
			SI desea actualizar la clave de este usuario por favor llene el campo. Si NO desea actualizar la clave deje el campo vacío.
		</p>
		<br>
		<div class="columns">
		  	<div class="column">
		    	<div class="control">
					<label>Nueva clave</label>
				  	<input class="input" type="password" name="usuario_clave_1" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" >
				</div>
		  	</div>
		</div>
		<br><br><br>
		<p class="has-text-centered">
			Para poder actualizar los datos de este usuario por favor ingrese su USUARIO y CLAVE con la que ha iniciado sesión
		</p>
		<div class="columns">
		  	<div class="column">
		    	<div class="control">
					<label>Usuario <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="text" name="administrador_usuario" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" required >
				</div>
		  	</div>
		  	<div class="column">
		    	<div class="control">
					<label>Clave <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="password" name="administrador_clave" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required >
				</div>
		  	</div>
		</div>
		<p class="has-text-centered">
			<button type="submit" class="button is-success is-rounded"><i class="fas fa-sync-alt"></i> &nbsp; Actualizar</button>
		</p>
		<p class="has-text-centered pt-6">
            <small>Los campos marcados con <?php echo CAMPO_OBLIGATORIO; ?> son obligatorios</small>
        </p>
	</form>
	<?php
		}else{
			include "./app/views/inc/error_alert.php";
		}
	?>
</div>