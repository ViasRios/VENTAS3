<div class="main-container">

    <form class="box login" action="" method="POST" autocomplete="off">
        <p class="has-text-centered">
            <i class="fas fa-user-circle fa-5x"></i>
        </p>
        <h5 class="title is-5 has-text-centered">Inicia sesión para Almacén</h5>

        <input type="hidden" name="login_almacen" value="1">


        <div class="field">
            <label class="label"><i class="fas fa-user-secret"></i> &nbsp; Usuario</label>
            <div class="control">
                <input class="input" type="text" name="login_usuario" pattern="[-_a-zA-Z0-9$@.]{4,20}" maxlength="20" required >
            </div>
        </div>

        <div class="field">
            <label class="label"><i class="fas fa-key"></i> &nbsp; Clave</label>
            <div class="control">
                <input class="input" type="password" name="login_clave" pattern="[-_a-zA-Z0-9$@.]{5,100}" maxlength="100" required >
            </div>
        </div>

        <p class="has-text-centered mb-4 mt-3">
            <button type="submit" class="button is-info is-rounded">LOG IN</button>
        </p>

    </form>
</div>
