<div class="container is-fluid mb-2">
	<h1 class="title">Personal</h1>
	<h2 class="subtitle"><i class="fas fa-user-tie fa-fw"></i> &nbsp; Nuevo personal</h2>
</div>
<div class="container pb-2 pt-2">
	<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/personalAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data" >
		<input type="hidden" name="modulo_personal" value="registrar">
		<div class="columns">
		  	<div class="column">
		    	<div class="control">
					<label>Nombre <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="text" name="Nombre" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" maxlength="40" required >
				</div>
		  	</div>
		  	<div class="column">
		    	<div class="control">
					<label>Telefono <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="text" name="Telefono" pattern="[0-9]{7,15}" maxlength="15" required >
				</div>
		  	</div>
		</div>
		<div class="columns">
		  	<div class="column">
				<div class="control">
					<label>Puesto <?php echo CAMPO_OBLIGATORIO; ?></label>
					<div class="select">
						<select name="Puesto" required>
							<option value="ASESOR">ASESOR</option>
							<option value="TECNICO">TECNICO</option>
							<option value="JEFE_DE_PRODUCCION">JEFE DE PRODUCCION</option>
						</select>
					</div>
				</div>
			</div>
		<!--	<div class="column">
		    	<div class="control">
					<label>Prioridad</label>
				  	<input class="input" type="text" name="Prioridad"  maxlength="2" required >
				</div>
		  	</div> -->
		</div>
		<div class="columns">
		  	<div class="column">
		    	<div class="control">
					<label>Usuario <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="text" name="usuario" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" required >
				</div>
		  	</div>
		  	<div class="column">
		    	<div class="control">
					<label>Email</label>
				  	<input class="input" type="email" name="email" maxlength="70" >
				</div>
		  	</div>
		</div>
		<div class="columns">
		  	<div class="column">
		    	<div class="control">
					<label>Clave <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="password" name="usuario_clave_1" pattern="[a-zA-Z0-9$@.\-]{5,100}" maxlength="100" required >
				</div>
		  	</div>
		  	<div class="column">
		    	<div class="control">
					<label>Repetir clave <?php echo CAMPO_OBLIGATORIO; ?></label>
				  	<input class="input" type="password" name="usuario_clave_2" pattern="[a-zA-Z0-9$@.\-]{5,100}" maxlength="100" required >
				</div>
		  	</div>
		</div>
		<div class="columns">
		  	<div class="column">
				<div class="file has-name is-boxed">
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
		  	</div>
		  <!--	<div class="column">
		  		<label>Caja de ventas <?php echo CAMPO_OBLIGATORIO; ?></label><br>
				<div class="select">
				  	<select name="usuario_caja">
				    	<option value="" selected="" >Seleccione una opción</option>
                        <?php
                            $datos_cajas=$insLogin->seleccionarDatos("Normal","caja","*",0);

                            while($campos_caja=$datos_cajas->fetch()){
                                echo '<option value="'.$campos_caja['caja_id'].'">Caja No.'.$campos_caja['caja_numero'].' - '.$campos_caja['caja_nombre'].'</option>';
                            }
                        ?>
				  	</select>
				</div>
		  	</div> -->
		</div>
		<p class="has-text-centered">
			<button type="reset" class="button is-link is-light is-rounded"><i class="fas fa-paint-roller"></i> &nbsp; Limpiar</button>
			<button type="submit" class="button is-info is-rounded"><i class="far fa-save"></i> &nbsp; Guardar</button>
		</p>
		<p class="has-text-centered pt-6">
            <small>Los campos marcados con <?php echo CAMPO_OBLIGATORIO; ?> son obligatorios</small>
        </p>
	</form>
</div>