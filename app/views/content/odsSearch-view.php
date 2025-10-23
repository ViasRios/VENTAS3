<div class="container is-fluid mb-1">
    <h1 class="title">ODS</h1>
    <h2 class="subtitle"><i class="fas fa-search fa-fw"></i> &nbsp; Buscar ODS</h2>
</div>

<div class="container pb-1 pt-1">
    <?php
        $modulo_actual = "odsSearch";
        use app\controllers\odsController;
        $insUsuario = new odsController();

        if(!isset($_SESSION[$url[0]]) && empty($_SESSION[$url[0]])){
    ?>
    <div class="columns">
        <div class="column">
            <form class="FormularioAjax no-confirm" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" >
                <input type="hidden" name="modulo_buscador" value="buscar">
                <input type="hidden" name="modulo_url" value="<?php echo $modulo_actual; ?>">
                <div class="field is-grouped">
                    <p class="control ">
                        <span class="select">
                            <select name="filtro_campo" required>
                                <option value="Idods">ID ODS</option>
                               
                            </select>
                        </span>
                    </p>
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
            <form class="has-text-centered mt-1 mb-1 FormularioAjax no-confirm" action="<?php echo APP_URL; ?>app/ajax/buscadorAjax.php" method="POST" autocomplete="off" >
                <input type="hidden" name="modulo_buscador" value="eliminar">
                <input type="hidden" name="modulo_url" value="<?php echo $modulo_actual; ?>">
                <p><i class="fas fa-search fa-fw"></i> &nbsp; Estas buscando <strong>“<?php echo $_SESSION[$modulo_actual]; ?>”</strong></p>
                <br>
                <button type="submit" class="button is-danger is-rounded"><i class="fas fa-trash-restore"></i> &nbsp; Eliminar busqueda</button>
            </form>
        </div>
    </div>
    <?php
            echo $insUsuario->listarOdsControlador($url[1],15,$url[0],$_SESSION[$url[0]]);
        }
    ?>
</div>