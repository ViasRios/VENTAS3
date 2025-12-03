<section class="full-width navLateral scroll" id="navLateral">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    
    <div class="full-width navLateral-body">
        
        <div class="full-width navLateral-body-logo has-text-centered tittles is-uppercase" 
             style="background: #fff; height: auto; min-height: 80px; padding: 15px 0; display: flex; flex-direction: column; justify-content: center; align-items: center;">
            
            <img src="<?php echo APP_URL; ?>app/views/img/logo.jpg" 
                 alt="Logo" 
                 style="height: 80px; width: auto; display: block; margin-bottom: 5px;">
            
          <!--  <span style="color: #14207aff; font-size: 24px; font-weight: 800; letter-spacing: 1px; line-height: 1;">
                KASCOM
            </span> -->
        </div>

        <figure class="full-width user-card" style="height: auto; padding: 1px 1px 10px 0;">
            <div style="margin: 0 auto; width: 100px; height: 100px; position: relative;">
                <?php
                    if(is_file("./app/views/fotos/".$_SESSION['foto'])){
                        echo '<img class="is-rounded user-img-bold" src="'.APP_URL.'app/views/fotos/'.$_SESSION['foto'].'">';
                    }else{
                        echo '<img class="is-rounded user-img-bold" src="'.APP_URL.'app/views/fotos/default.png">';
                    }
                ?>
            </div>
            <figcaption class="navLateral-body-cr" style="float: none; width: 100%; text-align: center; padding-top: 10px;">
                <span style="display: block; font-weight: 700; color: #000000; font-size: 1.1rem;">
                    <?php echo $_SESSION['nombre']; ?>
                </span>
                <span class="user-role-badge-bold">
                    <?php echo $_SESSION['Puesto']; ?>
                </span>
            </figcaption>
        </figure>

        <nav class="full-width">
            <ul class="full-width list-unstyle menu-principal">

                <li class="full-width">
                    <a href="<?php 
                        if ($_SESSION['Puesto'] == 'TECNICO') echo APP_URL . 'dashboardTec/';
                        elseif (in_array($_SESSION['Puesto'], ['ASESOR', 'JEFE DE PRODUCCION', 'JEFE_DE_PRODUCCION'])) echo APP_URL . 'dashboard/';
                        else echo APP_URL . 'dashboard/';
                    ?>" class="full-width btn-bold">
                        <div class="navLateral-body-cl">
                            <i class="fab fa-dashcube fa-fw"></i>
                        </div>
                        <div class="navLateral-body-cr">INICIO</div>
                    </a>
                </li>

                <li class="full-width divider-bold"></li>

                <li class="full-width">
                    <a href="#" class="full-width btn-subMenu btn-bold">
                        <div class="navLateral-body-cl"><i class="fas fa-tags fa-fw"></i></div>
                        <div class="navLateral-body-cr">ODS</div>
                        <span class="fas fa-chevron-down icon-arrow"></span>
                    </a>
                    <ul class="full-width menu-principal sub-menu-options">
                        <li class="full-width"><a href="<?php echo APP_URL; ?>odsMe/" class="full-width link-sub-bold"><i class="fas fa-caret-right mr-2"></i> Mis ODS</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>odsNew/" class="full-width link-sub-bold"><i class="fas fa-caret-right mr-2"></i> Nueva ODS</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>odsStatus/" class="full-width link-sub-bold"><i class="fas fa-caret-right mr-2"></i> Status ODS</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>odsList/" class="full-width link-sub-bold"><i class="fas fa-caret-right mr-2"></i> Lista de ODS</a></li>
                    </ul>
                </li>

                <?php if($_SESSION['Puesto'] == 'JEFE DE PRODUCCION' || $_SESSION['Puesto'] == 'JEFE_DE_PRODUCCION'): ?>  
                <li class="full-width divider-bold"></li>
                <li class="full-width">
                    <a href="#" class="full-width btn-subMenu btn-bold">
                        <div class="navLateral-body-cl"><i class="fas fa-users fa-fw"></i></div>
                        <div class="navLateral-body-cr">PERSONAL</div>
                        <span class="fas fa-chevron-down icon-arrow"></span>
                    </a>
                    <ul class="full-width menu-principal sub-menu-options">
                        <li class="full-width"><a href="<?php echo APP_URL; ?>userNew/" class="full-width link-sub-bold"><i class="fas fa-plus mr-2"></i> Nuevo usuario</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>userList/" class="full-width link-sub-bold"><i class="fas fa-list mr-2"></i> Lista usuarios</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>userSearch/" class="full-width link-sub-bold"><i class="fas fa-search mr-2"></i> Buscar usuario</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <li class="full-width divider-bold"></li>
                <li class="full-width">
                    <a href="#" class="full-width btn-subMenu btn-bold">
                        <div class="navLateral-body-cl"><i class="fas fa-address-book fa-fw"></i></div>
                        <div class="navLateral-body-cr">CLIENTES</div>
                        <span class="fas fa-chevron-down icon-arrow"></span>
                    </a>
                    <ul class="full-width menu-principal sub-menu-options">
                        <li class="full-width"><a href="<?php echo APP_URL; ?>clientNew/" class="full-width link-sub-bold"><i class="fas fa-user-plus mr-2"></i> Nuevo cliente</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>clientList/" class="full-width link-sub-bold"><i class="fas fa-list-ul mr-2"></i> Lista clientes</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>clientSearch/" class="full-width link-sub-bold"><i class="fas fa-search mr-2"></i> Buscar cliente</a></li>
                    </ul>
                </li>

                <?php if(in_array($_SESSION['Puesto'], ['ASESOR', 'JEFE DE PRODUCCION', 'JEFE_DE_PRODUCCION'])): ?>
                <li class="full-width divider-bold"></li>
                <li class="full-width">
                    <a href="#" class="full-width btn-subMenu btn-bold">
                        <div class="navLateral-body-cl"><i class="fas fa-file-invoice"></i></div>
                        <div class="navLateral-body-cr">FACTURAS</div>
                        <span class="fas fa-chevron-down icon-arrow"></span>
                    </a>
                    <ul class="full-width menu-principal sub-menu-options">
                        <li class="full-width"><a href="<?php echo APP_URL; ?>invoiceNew/" class="full-width link-sub-bold"><i class="fas fa-plus mr-2"></i> Crear factura</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>invoiceList/" class="full-width link-sub-bold"><i class="fas fa-list-alt mr-2"></i> Listar facturas</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>invoiceSearch/" class="full-width link-sub-bold"><i class="fas fa-search mr-2"></i> Buscar factura</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if(in_array($_SESSION['Puesto'], ['JEFE DE PRODUCCION', 'JEFE_DE_PRODUCCION'])): ?>
                <li class="full-width divider-bold"></li>
                <li class="full-width">
                    <a href="#" class="full-width btn-subMenu btn-bold">
                        <div class="navLateral-body-cl"><i class="fas fa-cogs"></i></div>
                        <div class="navLateral-body-cr">SERVICIOS</div>
                        <span class="fas fa-chevron-down icon-arrow"></span>
                    </a>
                    <ul class="full-width menu-principal sub-menu-options">
                        <li class="full-width"><a href="<?php echo APP_URL; ?>serviceNew/" class="full-width link-sub-bold"><i class="fas fa-plus-circle mr-2"></i> Nuevo Servicio</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>serviceList/" class="full-width link-sub-bold"><i class="fas fa-clipboard-list mr-2"></i> Lista Servicios</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>serviceSearch/" class="full-width link-sub-bold"><i class="fas fa-search mr-2"></i> Buscar Servicio</a></li>
                    </ul>
                </li>
                <li class="full-width divider-bold"></li>
                <li class="full-width">
                    <a href="http://localhost/sistema_inventario/" target="_blank" class="full-width btn-bold">
                        <div class="navLateral-body-cl"><i class="fas fa-boxes"></i></div>
                        <div class="navLateral-body-cr">REFACCIONES</div>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(in_array($_SESSION['Puesto'], ['JEFE DE PRODUCCION', 'JEFE_DE_PRODUCCION'])): ?>
                <li class="full-width divider-bold"></li>
                <li class="full-width">
                    <a href="<?php echo APP_URL; ?>cashierKey/" class="full-width btn-bold">
                        <div class="navLateral-body-cl"><i class="fas fa-key"></i></div>
                        <div class="navLateral-body-cr">CONTRASEÑAS</div>
                    </a>
                </li>
                <?php endif; ?>

                <li class="full-width divider-bold"></li>
                <li class="full-width">
                    <a href="#" class="full-width btn-subMenu btn-bold">
                        <div class="navLateral-body-cl"><i class="fas fa-shopping-cart fa-fw"></i></div>
                        <div class="navLateral-body-cr">VENTAS</div>
                        <span class="fas fa-chevron-down icon-arrow"></span>
                    </a>
                    <ul class="full-width menu-principal sub-menu-options">
                        <li class="full-width"><a href="<?php echo APP_URL; ?>saleNew/" class="full-width link-sub-bold"><i class="fas fa-cart-plus mr-2"></i> Nueva venta</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>saleList/" class="full-width link-sub-bold"><i class="fas fa-list-ol mr-2"></i> Lista ventas</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>saleSearch/" class="full-width link-sub-bold"><i class="fas fa-search-dollar mr-2"></i> Buscar venta</a></li>
                    </ul>
                </li>
                
                <?php if($_SESSION['Puesto'] == 'JEFE DE PRODUCCION'): ?>
                <li class="full-width divider-bold"></li>
                <li class="full-width">
                    <a href="#" class="full-width btn-subMenu btn-bold">
                        <div class="navLateral-body-cl"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="navLateral-body-cr">GASTOS</div>
                        <span class="fas fa-chevron-down icon-arrow"></span>
                    </a>
                    <ul class="full-width menu-principal sub-menu-options">
                        <li class="full-width"><a href="<?php echo APP_URL; ?>expensesNew/" class="full-width link-sub-bold"><i class="fas fa-file-invoice mr-2"></i> Nuevo gasto</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>expensesList/" class="full-width link-sub-bold"><i class="fas fa-file-invoice-dollar mr-2"></i> Lista gastos</a></li>
                        <li class="full-width"><a href="<?php echo APP_URL; ?>expensesSearch/" class="full-width link-sub-bold"><i class="fas fa-search mr-2"></i> Buscar gasto</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <li class="full-width divider-bold"></li>
                <li class="full-width">
                    <a href="<?php echo APP_URL; ?>loginCaja/" class="full-width btn-bold">
                        <div class="navLateral-body-cl"><i class="fas fa-cash-register fa-fw"></i></div>
                        <div class="navLateral-body-cr">CAJA</div>
                    </a>
                </li>

                <li class="full-width divider-bold"></li>
                <li class="full-width">
                    <a href="<?php echo APP_URL."userUpdate/".$_SESSION['id']."/"; ?>" class="full-width btn-bold">
                        <div class="navLateral-body-cl"><i class="fas fa-user-tie fa-fw"></i></div>
                        <div class="navLateral-body-cr">Mi cuenta</div>
                    </a>
                </li>
                
                <li class="full-width mt-4">
                    <a href="<?php echo APP_URL."logOut/"; ?>" class="full-width btn-bold btn-exit-bold" >
                        <div class="navLateral-body-cl"><i class="fas fa-power-off"></i></div>
                        <div class="navLateral-body-cr">Cerrar sesión</div>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</section>

<style>
/* 1. CONTENEDOR PRINCIPAL */
#navLateral, .navLateral-body {
    background-color: #ffffff !important;
    border-right: 1px solid #e5e7eb;
    font-family: 'Segoe UI', sans-serif !important;
}

/* 2. FOTO DE PERFIL (Ajustada a 100px) */
.user-img-bold {
    width: 100px !important;
    height: 100px !important;
    object-fit: cover;
    border: 3px solid #fff !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Badge del Rol */
.user-role-badge-bold {
    background-color: #b3c9f6ff;
    color: #000000;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    display: inline-block;
    margin-top: 5px;
}

/* 3. BOTONES DEL MENÚ PRINCIPAL (Ajustado a 1rem) */
.btn-bold {
    color: #000000 !important;
    background: transparent !important;
    font-weight: 700 !important;
    font-size: 1rem !important; /* Letra más controlada */
    height: 50px !important;
    line-height: 50px !important;
    display: flex !important;
    align-items: center !important;
    transition: all 0.2s ease;
    border-left: 5px solid transparent;
}

/* Iconos del menú principal (Ajustado a 1.2rem) */
.btn-bold .navLateral-body-cl {
    color: #000000 !important;
    font-size: 1.2rem !important;
    width: 55px !important;
    text-align: center;
}

/* Texto del menú principal */
.btn-bold .navLateral-body-cr {
    font-size: 0.95rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.3px;
}

/* Hover Menú */
.btn-bold:hover {
    background-color: #f3f4f6 !important;
    border-left: 5px solid #4f46e5;
}
.btn-bold:hover .navLateral-body-cl {
    color: #4f46e5 !important;
    transform: scale(1.05);
}

/* 4. FLECHA DEL SUBMENÚ */
.icon-arrow {
    color: #000000 !important;
    font-size: 0.8rem !important;
    margin-right: 15px;
}

/* 5. SUBMENÚS */
.sub-menu-options {
    background-color: #ffffff !important;
    padding-bottom: 5px;
}

/* Enlaces del submenú (Ajustado) */
.link-sub-bold {
    color: #374151 !important;
    font-size: 0.9rem !important;
    font-weight: 600 !important;
    padding: 10px 10px 10px 60px !important;
    display: block !important;
    text-decoration: none !important;
    border-left: 5px solid transparent;
}
.link-sub-bold i {
    color: #9ca3af;
    width: 18px;
    text-align: center;
}

.link-sub-bold:hover {
    background-color: #eff6ff !important;
    color: #000000 !important;
}
.link-sub-bold:hover i {
    color: #4f46e5;
}

/* 6. DIVISORES */
.divider-bold {
    border-bottom: 1px solid #f3f4f6 !important;
    margin: 3px 0 !important;
}

/* 7. BOTÓN SALIR */
.btn-exit-bold {
    color: #dc2626 !important;
}
.btn-exit-bold .navLateral-body-cl { color: #dc2626 !important; }
.btn-exit-bold:hover {
    background-color: #fef2f2 !important;
    border-left: 5px solid #dc2626;
}
</style>