
<div class="full-width navBar">
    <div class="full-width navBar-options">
        <i class="fas fa-exchange-alt fa-fw" id="btn-menu"></i> 
        <nav class="navBar-options-list">
            <ul class="list-unstyle">
                <li class="text-condensedLight noLink" >
                    <a class="btn-exit" href="<?php echo APP_URL."logOut/"; ?>" >
                        <i class="fas fa-power-off"></i>
                    </a>
                </li>
                <!-- 🔔 Campana de notificaciones -->
                <li class="text-condensedLight noLink" style="position: relative;">
                    <a href="#" id="notification-bell" style="position: relative;">
                        <i class="fas fa-bell has-text-grey" id="bell-icon"></i>

                        <span id="notification-count" style="
                            position: absolute;
                            top: -5px;
                            right: -8px;
                            background: red;
                            color: white;
                            font-size: 10px;
                            border-radius: 50%;
                            padding: 2px 5px;
                            display: none;
                        ">0</span>
                    </a>

                    <!-- 🔽 Lista desplegable de notificaciones -->
                    <div id="notification-list" class="box" style="
                        position: absolute;
                        top: 30px;
                        right: 0;
                        display: none;
                        z-index: 999;
                        width: 280px;
                        max-height: 300px;
                        overflow-y: auto;
                        background: white;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                        border-radius: 4px;
                        font-size: 13px;
                    ">
                        <!-- Se llena por JS -->
                    </div>
                </li>
                <li class="text-condensedLight noLink" >
                    <small><?php echo $_SESSION['usuario']; ?></small>
                </li>
                <li class="noLink">
                    <?php
                        if(is_file("./app/views/fotos/".$_SESSION['foto'])){
                            echo '<img class="is-rounded img-responsive" src="'.APP_URL.'app/views/fotos/'.$_SESSION['foto'].'">';
                        }else{
                            echo '<img class="is-rounded img-responsive" src="'.APP_URL.'app/views/fotos/default.png">';
                        }
                    ?>
                </li>
            </ul>
        </nav>
    </div>
</div>
<!-- <script>
document.addEventListener("DOMContentLoaded", () => {
    actualizarCampanita();

    setInterval(actualizarCampanita, 30000);
});
</script> -->
