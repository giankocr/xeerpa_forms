<?php
// Cargar wp-load.php desde el directorio raíz de WordPress
if (!defined('ABSPATH')) {
    $public_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
    include($public_path);
}
// Ve
function shortcode_recomendador_xeerpa()
{
    ob_start();

    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $user_id =  $current_user->ID;
        $it = get_user_meta($user_id, 'meta_xeerpa_it', true);

        if ($it) {
            ?>
            <script>
                window.document.addEventListener('DOMContentLoaded', function() {
                    showWidget('<?php echo esc_js($it); ?>'); // Escapar el valor de $it
                })
            </script>
            <div>
                <div id="divWidgetWrapper" style="text-align: center;">
                    <div id="loading" class="blinking" style="display:none;" >Cargando recomendaciones, por favor espere...</div>
                    <div id="divWidget">
                    </div>
                </div>
                <div class="socials">
                    <button class="btn-recomendador-refrescar" onclick="location.reload();">Ver recomendaciones</button>
                </div>
            </div>
            <?php
        } else {
            ?>
           <div class="socials register-social">
                No has realizado login con una red social, para poder ver las recomendaciones
                <?php include 'social-login-btn.html'; ?>
            </div>
            <?php
        }
        return ob_get_clean();
    }
    // Manejar el caso en que el usuario no está logueado
    $text = '<div style="text-align: center;">No estás logueado. Inicia sesión para ver las recomendaciones.</div>';
       // Incluir el contenido de social-login-btn.html de forma segura
    $social_login_buttons = file_get_contents(plugin_dir_path(__FILE__) . 'social-login-btn.html'); // Asegúrate de que la ruta sea correcta
    $text .= $social_login_buttons;
    return $text;
}

add_shortcode('recomendador_xeerpa', 'shortcode_recomendador_xeerpa');
