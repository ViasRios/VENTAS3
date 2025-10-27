<div class="full-width navBar">
    <div class="full-width navBar-options">
        <i class="fas fa-exchange-alt fa-fw" id="btn-menu"></i>
        <nav class="navBar-options-list">
            <ul class="list-unstyle">
                <li class="noLink">
                    <form class="FormularioAjax no-confirm" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off">
                        <input type="hidden" name="modulo_buscador" value="buscar">
                        <input type="hidden" name="modulo_url" value="odsSearch">
                        <div class="search-box">
                            <input class="search-input" type="text" name="txt_buscador" placeholder="Buscar por ODS o Nombre" required>
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </li>
                <li class="text-condensedLight noLink">
                    <a class="btn-exit" href="<?php echo APP_URL."logOut/"; ?>">
                        <i class="fas fa-power-off"></i>
                    </a>
                </li>
                <li class="text-condensedLight noLink">
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
<style>
/* Contenedor principal para la caja de búsqueda */
.search-box {
    position: relative;
    width: 650px; 
    height: 35px;
}
/* El campo de texto donde el usuario escribe */
.search-input {
    width: 75%;
    height: 100%;
    border-radius: 25px;
    border: 1px solid #ddd;
    background-color: #f1f3f4;
    padding-left: 20px;
    padding-right: 45px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s ease-in-out;
}
/* Efecto visual cuando el usuario hace clic en el buscador */
.search-input:focus {
    border-color: #4a90e2;
    background-color: #fff;
    box-shadow: 0 0 8px rgba(74, 144, 226, 0.3);
}
/* El botón con el ícono de lupa */
.search-btn {
    position: absolute;
    top: 0;
    right: 10px;
    width: 180px;
    height: 150%;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: left;
}
/* Estilo del ícono de la lupa */
.search-btn i {
    color: #0c4782ff;
    font-size: 16px;
    transition: color 0.2s;
}
/* Cambia el color del ícono cuando pasas el mouse por encima */
.search-btn:hover i {
    color: #333;
}
</style>
</body>
</html>