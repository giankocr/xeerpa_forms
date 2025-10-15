<?php
// Registrar el shortcode
function custom_login_form_shortcode()
{

    ob_start();
    if (!is_user_logged_in()) {
        include 'social-login-btn.html';
        ?>
        <div class="divisor">O</div>
        <span id="login-message"></span>
        <?php echo do_shortcode("[custom_login_form]"); ?>
        <script>

        </script>
        <style>
            .socials {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .socials img {
                width: 320px;
                margin: 5px;
                box-shadow: 1px 1px 8px 0px #dadada;
                border-radius: 50px;
            }
        </style>
        <?php
    } else {
        $user_id = get_current_user_id();
        $firt_name = get_user_meta($user_id, 'first_name', true);
        ?>
        <div style="text-align: center;">
            <h2>¡Hola <?php echo $firt_name; ?>! Ya estás logueado</h2>
        </div>
        <?php
    }
    return ob_get_clean();
}

add_shortcode('xpsocial_login_form', 'custom_login_form_shortcode');
?>