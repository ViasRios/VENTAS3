<div class="container is-fluid mb-1">
	<?php 

		$id=$insLogin->limpiarCadena($url[1]);

		if($id==$_SESSION['id']){ 
	?>
	<h1 class="title">Mi foto de perfil</h1>
	<h2 class="subtitle"><i class="fas fa-camera"></i> &nbsp; Actualizar foto de perfil</h2>
	<?php }else{ ?>
	<h1 class="title">Personal</h1>
	<h2 class="subtitle"><i class="fas fa-camera"></i> &nbsp; Actualizar foto de perfil</h2>
	<?php } ?>
</div>
<div class="container pb-2 pt-2">
	<?php
	
		include "./app/views/inc/btn_back.php";

		$datos=$insLogin->seleccionarDatos("Unico","personal","Idasesor",$id);

		if($datos->rowCount()==1){
			$datos=$datos->fetch();
	?>

	<h2 class="title has-text-centered has-text-link"><?php echo $datos['Nombre']." "; ?></h2>

	<div class="columns">
		<div class="column is-two-fifths">
			<h4 class="subtitle is-4 has-text-centered pb-6">Foto actual del usuario</h4>
            <?php if(is_file("./app/views/fotos/".$datos['personal_foto'])){ ?>
			<figure class="image mb-6">
                <img class="is-rounded is-photo" src="<?php echo APP_URL; ?>app/views/fotos/<?php echo $datos['personal_foto']; ?>">
			</figure>

			<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/personalAjax.php" method="POST" autocomplete="off" >

				<input type="hidden" name="modulo_personal" value="eliminarFoto">
				<input type="hidden" name="Idasesor" value="<?php echo $datos['Idasesor']; ?>">

				<p class="has-text-centered">
					<button type="submit" class="button is-danger is-rounded"><i class="far fa-trash-alt"></i> &nbsp; Eliminar foto</button>
				</p>
			</form>
			<?php }else{ ?>
			<figure class="image mb-6">
			  	<img class="is-rounded is-photo" src="<?php echo APP_URL; ?>app/views/fotos/default.png">
			</figure>
			<?php }?>
		</div>


		<div class="column">
			<h4 class="subtitle is-4 has-text-centered pb-6">Actualizar foto de personal</h4>
			<form class="mb-6 has-text-centered FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/personalAjax.php" method="POST" enctype="multipart/form-data" autocomplete="off" >

				<input type="hidden" name="modulo_personal" value="actualizarFoto">
				<input type="hidden" name="Idasesor" value="<?php echo $datos['Idasesor']; ?>">

				<label>Foto o imagen del personal</label><br>

				<div class="file has-name is-boxed is-justify-content-center mb-6">
				  	<label class="file-label">
						<input class="file-input" type="file" name="personal_foto" accept=".jpg, .png, .jpeg" >
						<span class="file-cta">
							<span class="file-label">
								Seleccione una foto
							</span>
						</span>
						<span class="file-name">JPG, JPEG, PNG. (MAX 5MB)</span>
					</label>
				</div>
				<p class="has-text-centered">
					<button type="submit" class="button is-success is-rounded"><i class="fas fa-sync-alt"></i> &nbsp; Actualizar foto</button>
				</p>
			</form>
		</div>
	</div>
	<?php
		}else{
			include "./app/views/inc/error_alert.php";
		}
	?>
</div>