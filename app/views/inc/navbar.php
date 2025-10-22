<div class="full-width navBar">
    <div class="full-width navBar-options">
        <i class="fas fa-exchange-alt fa-fw" id="btn-menu"></i>
        <nav class="navBar-options-list">
            <ul class="list-unstyle">

                <li class="noLink">
                    <form id="search-redirect-form">
                        <div class="search-box">
                            <input class="search-input" type="text" id="search-redirect-input" placeholder="Ir a ODS por ID..." required>
                            
                            <button type="submit" class="search-btn">
                                <i class="fas fa-arrow-right"></i>
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

<script>
// Espera a que el formulario con el ID 'search-redirect-form' sea enviado (al presionar Enter o el botón)
document.getElementById('search-redirect-form').addEventListener('submit', function(event) {
    
    // 1. Previene que la página se recargue y envíe datos a un archivo
    event.preventDefault();

    // 2. Obtiene el valor (el ID) que el usuario escribió en el campo de texto
    const odsId = document.getElementById('search-redirect-input').value;

    // 3. Construye la URL de destino usando la variable APP_URL de PHP
    //    Esto hace que tu código funcione sin importar si está en localhost o en un servidor real
    const baseUrl = '<?php echo rtrim(APP_URL, "/"); ?>';
    const newUrl = baseUrl + '/odsView/' + odsId + '/';

    // 4. Redirige el navegador a la nueva URL
    window.open(newUrl);
});
</script>
</body>
</html>