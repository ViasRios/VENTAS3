<style>
    /* Estilo Aesthetic - Glassmorphism */
    .main-container {
        background: linear-gradient(135deg, #1d4d80 0%, #1d4d80 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Poppins', sans-serif;
    }

    /* Efecto de Cristal */
    .box.login {
        background: rgba(255, 255, 255, 0.25);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: white;
        padding: 3rem;
        width: 100%;
        max-width: 400px;
    }

    /* Textos en blanco */
    .title.is-5, .label, .icon-user {
        color: white !important;
    }

    /* Inputs redondeados y suaves */
    .input {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 50px;
        padding-left: 20px;
        height: 45px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .input:focus {
        transform: scale(1.02);
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
    }

    /* Botón con gradiente */
    .button.is-info {
        background: linear-gradient(to right, #161a6cff 0%, #1a1b6cff 100%);
        border: none;
        color: #fff;
        font-weight: bold;
        letter-spacing: 1px;
        width: 100%;
        height: 45px;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s;
    }

    .button.is-info:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    /* Animación del icono */
    .icon-user {
        animation: float 3s ease-in-out infinite;
        text-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="main-container pb-3 pt-1">

    <form class="box login" action="" method="POST" autocomplete="off">
        
        <p class="has-text-centered mb-1">
            <i class="fas fa-user-circle fa-5x icon-user"></i>
        </p>
        
        <h5 class="title is-5 has-text-centered mb-5">Inicia sesión con tu cuenta</h5>

        <?php
            if(isset($_POST['login_usuario']) && isset($_POST['login_clave'])){
                $insLogin->iniciarSesionControlador();
            }
        ?>

        <div class="field">
            <label class="label has-text-weight-normal">
                <i class="fas fa-user-secret"></i> &nbsp; Usuario
            </label>
            <div class="control">
                <input class="input" type="text" name="login_usuario" pattern="[-_a-zA-Z0-9$@.]{4,20}" maxlength="20" required placeholder="Nombre de usuario">
            </div>
        </div>

        <div class="field mt-4">
            <label class="label has-text-weight-normal">
                <i class="fas fa-key"></i> &nbsp; Clave
            </label>
            <div class="control">
                <input class="input" type="password" name="login_clave" pattern="[-_a-zA-Z0-9$@.]{5,100}" maxlength="100" required placeholder="••••••••">
            </div>
        </div>

        <div class="field mt-5">
            <button type="submit" class="button is-info is-rounded">LOG IN</button>
        </div>

    </form>
</div>