<section class="full-width navLateral scroll" id="navLateral">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
	<div class="full-width navLateral-body">
		<div class="full-width navLateral-body-logo has-text-centered tittles is-uppercase">
			KASCOM 
		</div>
		<figure class="full-width" style="height: 77px;">
			<div class="navLateral-body-cl">
				<?php
                    if(is_file("./app/views/fotos/".$_SESSION['foto'])){
                        echo '<img class="is-rounded img-responsive" src="'.APP_URL.'app/views/fotos/'.$_SESSION['foto'].'">';
                    }else{
                        echo '<img class="is-rounded img-responsive" src="'.APP_URL.'app/views/fotos/default.png">';
                    }
                ?>
			</div>
			<figcaption class="navLateral-body-cr">
				<span>
					<?php echo $_SESSION['nombre']; ?><br>
					<small><?php echo $_SESSION['usuario']; ?></small>
				</span>
			</figcaption>
		</figure>
		<nav class="full-width">
			<ul class="full-width list-unstyle menu-principal">

				<li class="full-width">
					<a href="<?php 
						if ($_SESSION['Puesto'] == 'TECNICO') {
							echo APP_URL . 'dashboardTec/';
						} elseif ($_SESSION['Puesto'] == 'ASESOR' || $_SESSION['Puesto'] == 'JEFE DE PRODUCCION' || $_SESSION['Puesto'] == 'JEFE_DE_PRODUCCION') {
							echo APP_URL . 'dashboard/';
						} else {
							echo APP_URL . 'dashboard/'; // Default redirect (por si acaso)
						}
					?>" class="full-width">
						<div class="navLateral-body-cl">
							<i class="fab fa-dashcube fa-fw"></i>
						</div>
						<div class="navLateral-body-cr">
							Inicio
						</div>
					</a>
				</li>

				<li class="full-width divider-menu-h"></li>

				<li class="full-width">
					<a href="#" class="full-width btn-subMenu">
						<div class="navLateral-body-cl">
							<i class="fas fa-tags fa-fw"></i>
						</div>
						<div class="navLateral-body-cr">
							ODS
						</div>
						<span class="fas fa-chevron-down"></span>
					</a>
					<ul class="full-width menu-principal sub-menu-options">
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>odsMe/" class="full-width">
								<div class="navLateral-body-cl">
								<i class="fas fa-tools fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									MIS ODS
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>odsNew/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-tag fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Nueva ODS
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>odsStatus/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-bars-progress fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Status ODS
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>odsList/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-clipboard-list fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Lista de ODS
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>odsSearch/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-search fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Buscar ODS
								</div>
							</a>
						</li>
					</ul>
				</li>

				<li class="full-width divider-menu-h"></li>
				
				 <!-- Mostrar el menú ODS solo si el usuario es TECNICO o JEFE DE PRODUCCION -->
				<?php if($_SESSION['Puesto'] == 'TECNICO' || $_SESSION['Puesto'] == 'JEFE DE PRODUCCION' || $_SESSION['Puesto'] == 'JEFE_DE_PRODUCCION'): ?>  
				<li class="full-width">
					<a href="#" class="full-width btn-subMenu">
						<div class="navLateral-body-cl">
							<i class="fas fa-users fa-fw"></i>
						</div>
						<div class="navLateral-body-cr">
							USUARIOS
						</div>
						<span class="fas fa-chevron-down"></span>
					</a>
					<ul class="full-width menu-principal sub-menu-options">
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>userNew/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-cash-register fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Nuevo usuario
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>userList/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-clipboard-list fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Lista de usuarios
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>userSearch/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-search fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Buscar usuario
								</div>
							</a>
						</li>
					</ul>
				</li>

				<li class="full-width divider-menu-h"></li>
				<?php endif; ?>

				<li class="full-width">
					<a href="#" class="full-width btn-subMenu">
						<div class="navLateral-body-cl">
							<i class="fas fa-address-book fa-fw"></i>
						</div>
						<div class="navLateral-body-cr">
							CLIENTES
						</div>
						<span class="fas fa-chevron-down"></span>
					</a>
					<ul class="full-width menu-principal sub-menu-options">
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>clientNew/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-male fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Nuevo cliente
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>clientList/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-clipboard-list fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Lista de clientes
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>clientSearch/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-search fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Buscar cliente
								</div>
							</a>
						</li>
					</ul>
				</li>

				<li class="full-width divider-menu-h"></li>

				<?php if($_SESSION['Puesto'] == 'ASESOR' || $_SESSION['Puesto'] == 'JEFE DE PRODUCCION' || $_SESSION['Puesto'] == 'JEFE_DE_PRODUCCION'): ?>
				<li class="full-width">
					<a href="#" class="full-width btn-subMenu">
						<div class="navLateral-body-cl">
							<i class="fas fa-file-invoice"></i>
						<!--	<i class="fas fa-address-book fa-fw"></i>  -->
						</div>
						<div class="navLateral-body-cr">
							FACTURAS
						</div>
						<span class="fas fa-chevron-down"></span>
					</a>
					<ul class="full-width menu-principal sub-menu-options">
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>invoiceNew/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-male fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Crear factura
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>invoiceList/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-clipboard-list fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Listar facturas
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>invoiceSearch/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-search fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Buscar factura
								</div>
							</a>
						</li>
					</ul>
				</li>

				<li class="full-width divider-menu-h"></li>
				<?php endif; ?>

				<?php if($_SESSION['Puesto'] == 'JEFE DE PRODUCCION' || $_SESSION['Puesto'] == 'JEFE_DE_PRODUCCION'): ?>
				<li class="full-width">
					<a href="#" class="full-width btn-subMenu">
						<div class="navLateral-body-cl">
						<i class="fas fa-cog"></i>
						</div>
						<div class="navLateral-body-cr">
							SERVICIOS
						</div>
						<span class="fas fa-chevron-down"></span>
					</a>
					<ul class="full-width menu-principal sub-menu-options">
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>serviceNew/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-box fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Nuevo Servicio
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>serviceList/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-clipboard-list fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Lista de Servicios
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>serviceSearch/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-search fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Buscar Servicio
								</div>
							</a>
						</li>
					</ul>
				</li>
				<li class="full-width divider-menu-h"></li>
				<?php endif; ?>

				<?php if($_SESSION['Puesto'] == 'JEFE DE PRODUCCION' || $_SESSION['Puesto'] == 'JEFE_DE_PRODUCCION'): ?>
				<li class="full-width">
						<a href="<?php echo APP_URL; ?>loginAlmacen/" class="full-width">
						<div class="navLateral-body-cl">
							<i class="fas fa-shopping-cart fa-fw"></i>
						</div>
						<div class="navLateral-body-cr">
							REFACCIONES
						</div>
						</a>
						<a href="<?php echo APP_URL; ?>registrarUsuarioAlmacen/" class="full-width">
							<div class="navLateral-body-cl">
								<i class="fas fa-user-plus fa-fw"></i>
							</div>
							<div class="navLateral-body-cr">
									Registrar usuario Almacén
							</div>
						</a>
				</li>
				
				<li class="full-width divider-menu-h"></li>
				<?php endif; ?>

				<li class="full-width">
					<a href="#" class="full-width btn-subMenu">
						<div class="navLateral-body-cl">
							<i class="fas fa-shopping-cart fa-fw"></i>
						</div>
						<div class="navLateral-body-cr">
							VENTAS
						</div>
						<span class="fas fa-chevron-down"></span>
					</a>
					<ul class="full-width menu-principal sub-menu-options">
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>saleNew/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-cart-plus fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Nueva venta
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>saleList/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-clipboard-list fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Lista de ventas
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>saleSearch/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-search-dollar fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Buscar venta
								</div>
							</a>
						</li>
					</ul>
				</li>
				
				<li class="full-width divider-menu-h"></li>
					
				<?php if($_SESSION['Puesto'] == 'JEFE DE PRODUCCION'): ?>
				<li class="full-width">
					<a href="#" class="full-width btn-subMenu">
						<div class="navLateral-body-cl">
							<i class="fa-solid fa-money-bills"></i>			
						</div>
						<div class="navLateral-body-cr">
							GASTOS
						</div>
						<span class="fas fa-chevron-down"></span>
					</a>
					<ul class="full-width menu-principal sub-menu-options">
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>expensesNew/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-cash-register fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Nuevo gasto
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>expensesList/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-clipboard-list fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Lista de gastos
								</div>
							</a>
						</li>
						<li class="full-width">
							<a href="<?php echo APP_URL; ?>expensesSearch/" class="full-width">
								<div class="navLateral-body-cl">
									<i class="fas fa-search fa-fw"></i>
								</div>
								<div class="navLateral-body-cr">
									Buscar gasto
								</div>
							</a>
						</li>
					</ul>
				</li>
				

				<li class="full-width divider-menu-h"></li>
                 <?php endif; ?>

					<li class="full-width">
						<a href="<?php echo APP_URL; ?>loginCaja/" class="full-width">
							<div class="navLateral-body-cl">
								<i class="fas fa-cash-register fa-fw"></i>
							</div>
							<div class="navLateral-body-cr">
								CAJA
							</div>
						</a>
					</li>
					<li class="full-width">
						<a href="<?php echo APP_URL; ?>cashierNew/" class="full-width">
							<div class="navLateral-body-cl">
								<i class="fas fa-cash-register fa-fw"></i>
							</div>
							<div class="navLateral-body-cr">
								CAJA
							</div>
						</a>
					</li>

				<li class="full-width divider-menu-h"></li>

				<?php if($_SESSION['Puesto'] == 'JEFE DE PRODUCCION'): ?>
					<li class="full-width">
						<a href="#" class="full-width btn-subMenu">
							<div class="navLateral-body-cl">
								<i class="fas fa-cogs fa-fw"></i>
							</div>
							<div class="navLateral-body-cr">
								CONFIGURACIONES
							</div>
							<span class="fas fa-chevron-down"></span>
						</a>
						<ul class="full-width menu-principal sub-menu-options">
							<li class="full-width">
								<a href="<?php echo APP_URL; ?>companyNew/" class="full-width">
									<div class="navLateral-body-cl">
										<i class="fas fa-store-alt fa-fw"></i>
									</div>
									<div class="navLateral-body-cr">
										Datos de empresa
									</div>
								</a>
							</li>
						</ul>
					</li>
				<?php endif; ?>

				
				<!-- Opciones Mi cuenta y Mi foto fuera del menú Configuraciones -->
				<li class="full-width">
					<a href="<?php echo APP_URL."userUpdate/".$_SESSION['id']."/"; ?>" class="full-width">
						<div class="navLateral-body-cl">
							<i class="fas fa-user-tie fa-fw"></i>
						</div>
						<div class="navLateral-body-cr">
							Mi cuenta
						</div>
					</a>
				</li>
				<li class="full-width divider-menu-h"></li>
				<li class="full-width">
					<a href="<?php echo APP_URL."userPhoto/".$_SESSION['id']."/"; ?>" class="full-width">
						<div class="navLateral-body-cl">
							<i class="fas fa-camera"></i>
						</div>
						<div class="navLateral-body-cr">
							Mi foto
						</div>
					</a>
				</li>
				<li class="full-width">
					<a href="<?php echo APP_URL."userPhoto/".$_SESSION['id']."/"; ?>" class="full-width">
						<div class="navLateral-body-cl">
							<i class="fas fa-camera"></i>
						</div>
						<div class="navLateral-body-cr">
							Contraseñas
						</div>
					</a>
				</li>


				<li class="full-width divider-menu-h"></li>

				<li class="full-width mt-5">
					<a href="<?php echo APP_URL."logOut/"; ?>" class="full-width btn-exit" >
						<div class="navLateral-body-cl">
							<i class="fas fa-power-off"></i>
						</div>
						<div class="navLateral-body-cr">
							Cerrar sesión
						</div>
					</a>
				</li>

			</ul>
		</nav>
	</div>
</section>