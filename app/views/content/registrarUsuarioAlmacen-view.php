<div class="main-container">

    <form class="box login" action="" method="POST" autocomplete="off">
        <p class="has-text-centered">
            <i class="fas fa-user-plus fa-5x"></i>
        </p>
        <h5 class="title is-5 has-text-centered">Registrar nuevo usuario</h5>

        <?php
            use app\controllers\almacenRegisterController;

            $insLogin = new almacenRegisterController();

            if(isset($_POST['nuevo_usuario']) && isset($_POST['nuevo_clave']) && isset($_POST['nuevo_nombre'])){
                $insLogin->registrarUsuarioAlmacenControlador();
            }
        ?>

        <div class="field">
            <label class="label"><i class="fas fa-id-card"></i> &nbsp; Nombre completo</label>
            <div class="control">
                <input class="input" type="text" name="nuevo_nombre" maxlength="50" required >
            </div>
        </div>

        <div class="field">
            <label class="label"><i class="fas fa-user"></i> &nbsp; Usuario</label>
            <div class="control">
                <input class="input" type="text" name="nuevo_usuario" pattern="[-_a-zA-Z0-9$@.]{4,20}" maxlength="20" required >
            </div>
        </div>

        <div class="field">
            <label class="label"><i class="fas fa-key"></i> &nbsp; Clave</label>
            <div class="control">
                <input class="input" type="password" name="nuevo_clave" pattern="[-_a-zA-Z0-9$@.]{5,100}" maxlength="100" required>
            </div>
        </div>

        <p class="has-text-centered mb-4 mt-3">
            <button type="submit" class="button is-success is-rounded">
                <i class="fas fa-save"></i> &nbsp; Registrar
            </button>
        </p>

    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('.box.login');
        const messageSuccess = document.querySelector('.message.is-success');
        if (messageSuccess) {
            form.reset();
        }
    });
</script>
