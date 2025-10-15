<?php
/*
 * Add my new menu to the Admin Control Panel
 */
// Cargar wp-load.php desde el directorio raíz de WordPress

require_once plugin_dir_path(__FILE__) . 'xpsocial_login-license.php';

add_action('admin_menu', 'xpsocial_admin_menu');
add_action('admin_init', 'xpsocial_plugin_init');
function xpsocial_enqueue_scripts()
{
    // User login check removed - always enqueue scripts
    // Crear un script inline con las variables que necesitas
    wp_register_script('xpsocial-options-script', '', [], '', true);
    wp_enqueue_script('xpsocial-options-script');

    // Obtén las URLs de redirección
    $redirect_url = get_option('xpsocial_redirect_login');
    $register_url = get_option('xpsocial_redirect_to_registro');
    $redirect_existing_user = get_option('xpsocial_redirect_existing_user');
    // Pasa las variables a JavaScript
    wp_localize_script('xpsocial-options-script', 'URLxpSocialPluginData', array(
        'redirectUrl' => esc_url($redirect_url),
        'registerUrl' => esc_url($register_url),
        'redirectExistingUser' => esc_url($redirect_existing_user)
    ));

    // Opcionalmente, puedes agregar cualquier código JavaScript inline que necesites
   /* wp_add_inline_script('xpsocial-options-script', '
        console.log("XPSocial URLs loaded:", URLxpSocialPluginData);
    ');*/
}

add_action('wp_enqueue_scripts', 'xpsocial_enqueue_scripts');

function xpsocial_admin_menu()
{
    add_options_page('XpSocial Configuracion', 'XpSocial Config', 'manage_options', 'xpsocial_setting_page', 'xpsocial_plugin_options');
}

function xpsocial_plugin_init()
{
    register_setting('xpsocial-group', 'xpsocial_urlSocial');
    register_setting('xpsocial-group', 'xpsocial_urlForm');
    register_setting('xpsocial-group', 'xpsocial_authToken');
    register_setting('xpsocial-group', 'xpsocial_clientId');
    register_setting('xpsocial-group', 'xpsocial_clientPwd');
    register_setting('xpsocial-group', 'xpsocial_appId');
    register_setting('xpsocial-group', 'xpsocial_Callback');
    register_setting('xpsocial-group', 'xpsocial_Settings');
    register_setting('xpsocial-group', 'xpsocial_GFormID');
    register_setting('xpsocial-group', 'xpsocial_GoogleID');
    register_setting('xpsocial-group', 'xpsocial_GFxeerpa');
    register_setting('xpsocial-group', 'xpsocial_redirect_login');
    register_setting('xpsocial-group', 'xpsocial_redirect_to_registro');
    register_setting('xpsocial-group', 'xpsocial_redirect_existing_user');
    register_setting('xpsocial-group', 'xpsocial_marca');
    register_setting('xpsocial-group', 'xpsocial_linkPP');
    register_setting('xpsocial-group', 'xpsocial_linkTyC');
    register_setting('xpsocial-group', 'xpsocial_licencia');
    register_setting('xpsocial-group', 'xpsocial_countries');
    register_setting('xpsocial-group', 'xpsocial_lost_password_url');
    register_setting('xpsocial-group', 'fifco_api_url');
    register_setting('xpsocial-group', 'fifco_api_token');


    register_setting('xpsocial-group', 'xp_input_height');
    register_setting('xpsocial-group', 'xp_input_border');
    register_setting('xpsocial-group', 'xp_input_border_radius');
    register_setting('xpsocial-group', 'xp_bg_color');
    register_setting('xpsocial-group', 'xp_font_color');
    register_setting('xpsocial-group', 'xp_div_color');
    register_setting('xpsocial-group', 'xp_divisor_color');
    register_setting('xpsocial-group', 'xp_btn_height');
    register_setting('xpsocial-group', 'xp_btn_width');
    register_setting('xpsocial-group', 'xp_btn_color');
    register_setting('xpsocial-group', 'xp_btn_bgcolor');
    register_setting('xpsocial-group', 'xp_btn_border_width');
    register_setting('xpsocial-group', 'xp_btn_border_color');
    register_setting('xpsocial-group', 'xp_btn_border_radius');

    if (get_option('xpsocial_GFormID', $default = false)) {
        $form_id = get_option('xpsocial_GFormID', $default = false);
        $fields = GFAPI::get_form($form_id);
        foreach ($fields as $key => $data) {
            if ($key == 'fields') {
                foreach ($data as $key => $field) {
                    if ($field['type'] !== 'html' && $field['type'] !== 'password') {
                        $id = $form_id . '-' . $field['id'];
                        register_setting('xpsocial-group', 'xpsocial_GF-' . $id);
                        register_setting('xpsocial-group', 'xpsocial_GFcheck-' . $id);
                    }
                }
            }
        }
    }
}

function xpsocial_plugin_options()
{
    ?>
    <span class="xp_title">
        <h1> Xp Social Login </h1>
        <img src="<?php echo esc_url(plugins_url()); ?>/xpsocial_login/public/images/gianko-logo.png" alt="logo de gianko" width="250">
    </span>
    <form action="options.php" method="post">
        <?php @submit_button(); ?>
        <?php settings_fields('xpsocial-group'); ?>
        <?php @do_settings_fields('xpsocial_plugin_options', 'xpsocial-group') ?>

        <div class="tab">
            <div class="tablinks " onclick="openCity(event, 'London')" id="defaultOpen">Configuración</div>
            <div class="tablinks" onclick="openCity(event, 'Paris')">Google Sheet y Estilos</div>
            <div class="tablinks" onclick="openCity(event, 'Tokyo')">Acerca de</div>
        </div>
        <!--
    /****************************
    *       TAB 1               *
    *                           * 
    *****************************/
-->
        <div id="London" class="tabcontent defaultOpen">
            <table class='form-table'>
                <tr valign='top'>
                    <th scope="row">
                        <label for="xpsocial_licencia">Licencia de Uso</label>
                    </th>
                    <td>
                        <input type="text" name="xpsocial_licencia" id="xpsocial_licencia_" size='100' value="<?php echo get_option('xpsocial_licencia'); ?>" />
                        <br><small>Licencia de uso del plugin. contacta a gian@gianko.com para generar una licencia.</small>
                    </td>
                </tr>
                <?php if (xp_plugin_validate_license('activated')) { ?>
                    <tr valign='top'>
                        <th scope="row">
                            <label for="xpsocial_countries">Activar paises</label>
                        </th>
                        <td>
                            <?php
                            // Obtener los IDs de los países preseleccionados
                            $selectedCountries = get_option('xpsocial_countries') ?: [];
                            $countries = allcountries();
                            ?>
                            <select name="xpsocial_countries[]" id="xpsocial_countries_" size="4" style="position:relative" onclick="size=(size!=1)?10:20;" onblur="size=1;" multiple>
                                <?php foreach ($countries as $country) : ?>
                                    <option value="<?= htmlspecialchars($country['country_id']) ?>" <?= in_array($country['country_id'], $selectedCountries) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($country['emoji']) ?> <?= htmlspecialchars($country['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <br><small>Selecciona los paises que deseas que se muestren al usuario.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label for="xpsocial_urlSocial">Url SOCIAL Xeerpa</label>
                        </th>
                        <td>
                            <input type="text" name="xpsocial_urlSocial" id="xpsocial_urlSocial_" size='80' value="<?php echo get_option('xpsocial_urlSocial'); ?>" />
                            <br><small>URL de SOCIAL Xeerpa a la que se accede desde su servidor.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label for="xpsocial_urlForm">Url FORM Xeerpa</label>
                        </th>
                        <td>
                            <input type="text" name="xpsocial_urlForm" id="xpsocial_urlForm_" size='80' value="<?php echo get_option('xpsocial_urlForm'); ?>" />
                            <br><small>URL de FORM Xeerpa a la que se accede desde su servidor.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label for="xpsocial_authToken">Xeerpa AuthToken</label>
                        </th>
                        <td>
                            <input type="text" name="xpsocial_authToken" id="xpsocial_authToken_xeerpa_" size='130' value='<?php echo get_option('xpsocial_authToken', $default = false) ?>' />
                            <br><small>authToken es el identificador de la aplicación facebook. Reemplace este token por el enviado por Xeerpa.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label for="xpsocial_clientPwd">Xeerpa ClientPwd</label>
                        </th>
                        <td>
                            <input type="text" name="xpsocial_clientPwd" id="xpsocial_clientPwd" size='130' value='<?php echo get_option('xpsocial_clientPwd', $default = false) ?>' />
                            <br><small> Ingrese el Xeerpa clientPwd, enviado por Xeerpa.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label for="xpsocial_clientId">Xeerpa ClientId</label>
                        </th>
                        <td>
                            <input type="text" name="xpsocial_clientId" id="xpsocial_clientId" size='80' value='<?php echo get_option('xpsocial_clientId'); ?>' />
                            <br><small>Ingrese el Xeerpa clientId, enviado por Xeerpa.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label for="xpsocial_clientId">Xeerpa AppId</label>
                        </th>
                        <td>
                            <input type="text" name="xpsocial_appId" id="xpsocial_appId" size='80' value='<?php echo get_option('xpsocial_appId'); ?>' />
                            <br><small>Ingrese el Xeerpa AppId, enviado por Xeerpa.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label>URL Redirect Login</label>
                        </th>
                        <td>
                            <input type="url" name="xpsocial_redirect_login" size='160' id="xpsocial_redirect_login_" value='<?php echo get_option('xpsocial_redirect_login') ?>' />
                            <br>
                            <small>Indica aquí la url donde se utiliza el shortcode <code>[xpsocial_login_form]</code>.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label>URL de Registro</label>
                        </th>
                        <td>
                            <input type="url" name="xpsocial_redirect_to_registro" size='160' id="xpsocial_redirect_to_registro_" value='<?php echo get_option('xpsocial_redirect_to_registro') ?>' />
                            <br>
                            <small> Indica aquí la url donde se utiliza el shortcode <code>[xpsocial_register_form]</code></small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label>MARCA</label>
                        </th>
                        <td>
                            <input type="text" name="xpsocial_marca" size='160' id="xpsocial_marca" value='<?php echo get_option('xpsocial_marca') ?>' />
                            <br>
                            <small>Indica aquí la marca del sitio, esta marca se incluye en el texto de acepta comunicación en el formulario de registro.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label>LINK Políticas de Privacidad</label>
                        </th>
                        <td>
                            <input type="url" name="xpsocial_linkPP" size='160' id="xpsocial_linkPP" value='<?php echo get_option('xpsocial_linkPP') ?>' />
                            <br>
                            <small>LINK Políticas de Privacidad.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label>LINK Términos y Condiciones</label>
                        </th>
                        <td>
                            <input type="url" name="xpsocial_linkTyC" size='160' id="xpsocial_linkTyC" value='<?php echo get_option('xpsocial_linkTyC') ?>' />
                            <br>
                            <small>LINK Términos y Condiciones.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label>LINK para olvidé mi contraseña</label>
                        </th>
                        <td>
                            <input type="url" name="xpsocial_lost_password_url" size='160' id="xpsocial_lost_password_url" value='<?php echo get_option('xpsocial_lost_password_url') ?>' />
                            <br>
                            <small>Ingrese el link para cambiar contraseña. esto se mostrará en el login</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label>URL de redirección para usuarios existentes</label>
                        </th>
                        <td>
                            <input type="url" name="xpsocial_redirect_existing_user" size='160' id="xpsocial_redirect_existing_user" value='<?php echo get_option('xpsocial_redirect_existing_user') ?>' />
                            <br>
                            <small>Indica aquí la url donde se redirigirá al usuario después de iniciar sesión si ya existe en el sistema.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label for="xpsocial_urlSocial">Shortcode Recomendador Xeerpa</label>
                        </th>
                        <td>
                           <code>[recomendador_xeerpa]</code>
                            <br><small>Recomendador de xeerpa.</small>
                        </td>
                    </tr>
                    <tr valign='top'>
            <th scope="row">
                <label for="fifco_api_url">FIFCO API Endpoint</label>
            </th>
            <td>
                <input type="text" name="fifco_api_url" id="fifco_api_url" size='80' value="<?php echo esc_attr(get_option('fifco_api_url')); ?>" />
                <br><small>URL completa al endpoint de FIFCO. Ej: https://api.fifco.com/entrada-web/ImperialWeb</small>
            </td>
        </tr>
        <tr valign='top'>
            <th scope="row">
                <label for="fifco_api_token">FIFCO API Token</label>
            </th>
            <td>
                <input type="text" name="fifco_api_token" id="fifco_api_token" size='80' value="<?php echo esc_attr(get_option('fifco_api_token')); ?>" />
                <br><small>Token de autenticación en formato base64. Requiere encabezado Authorization: Basic [token]</small>
            </td>
        </tr>
                    <tr valign='top'>
                        <td colspan="2">
                            <h3>Datos de Referencia</h3>
                            <p>Requeridos por xeerpa para configurar la campaña.</p>
                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label>URL de xpsocial login_Callback</label>
                        </th>
                        <td>
                            <input type="text" name="xpsocial_Callback" id="xpsocial_Callback" disabled size='100' value='<?php echo get_site_url() . '/wp-content/plugins/xpsocial_login/public/socialLoginCallback.php' ?>' />

                        </td>
                    </tr>
                    <tr valign='top'>
                        <th scope="row">
                            <label>URL de xpsocial login_Completed</label>
                        </th>
                        <td>
                            <input type="text" name="xpsocial_Settings" id="xpsocial_Settings" disabled size='100' value='<?php echo get_site_url() . '/wp-content/plugins/xpsocial_login/public/socialLoginCompleted.php' ?>' />
                            <script>
                            </script>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
        <!--
    /****************************
    *       TAB 2               *
    *                           * 
    *****************************/
-->
        <div id="Paris" class="tabcontent">
            <table class='form_data'>
                <tr valign='top'>
                    <th scope="row">
                        <label>URL Google Sheets</label>
                    </th>
                    <td>
                        <input type="text" size='130' name="xpsocial_GoogleID" id="xpsocial_GoogleID_xeerpa_" value='<?php echo get_option('xpsocial_GoogleID', $default = false) ?>' />
                        <br>
                        <small>Ingrese la URL de GOOGLE Sheets. Proporcionada por Gianko.com</small>
                    </td>
                </tr>
            </table>
            <section>
                <H3>Estilos de Formularios</H3>
                <article>
                    <table class='form_data_style'>
                        <tr valign='top'>
                            <td>
                                <label>Inputs Height</label>
                                <input type="number" size='3' name="xp_input_height" id="xp_input_height_" value='<?php echo get_option('xp_input_height', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Inputs Border</label>
                                <input type="text" size='30' placeholder="1x solid #0000" name="xp_input_border" id="xp_input_border_" value='<?php echo get_option('xp_input_border', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Inputs Border-radius</label>
                                <input type="number" size='30' placeholder="3" name="xp_input_border_radius" id="xp_input_border_radius_" value='<?php echo get_option('xp_input_border_radius', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Input Backgound-color</label>
                                <input type="color" size='3' name="xp_bg_color" id="xp_bg_color_" value='<?php echo get_option('xp_bg_color', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Login Backgound-color</label>
                                <input type="color" size='3' name="xp_div_color" id="xp_div_color_" value='<?php echo get_option('xp_div_color', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Font Color Login</label>
                                <input type="color" size='3' name="xp_font_color" id="xp_font_color_" value='<?php echo get_option('xp_font_color', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Divisor Login Color</label>
                                <input type="color" size='3' name="xp_divisor_color" id="xp_divisor_color_" value='<?php echo get_option('xp_divisor_color', $default = false) ?>' />
                            </td>
                        </tr>
                        <tr valign='top'>
                            <td>
                                <label>Button Height</label>
                                <input type="number" size='3' name="xp_btn_height" id="xp_btn_height_" value='<?php echo get_option('xp_btn_height', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Button Width</label>
                                <input type="number" size='3' name="xp_btn_width" id="xp_btn_width_" value='<?php echo get_option('xp_btn_width', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Button Color</label>
                                <input type="color" size='3' name="xp_btn_color" id="xp_btn_color_" value='<?php echo get_option('xp_btn_color', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Button Background Color</label>
                                <input type="color" size='3' name="xp_btn_bgcolor" id="xp_btn_bgcolor_" value='<?php echo get_option('xp_btn_bgcolor', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Button Border Width</label>
                                <input type="number" size='3' name="xp_btn_border_width" id="xp_btn_border_width_" value='<?php echo get_option('xp_btn_border_width', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Button Border Color</label>
                                <input type="color" size='3' name="xp_btn_border_color" id="xp_btn_border_color_" value='<?php echo get_option('xp_btn_border_color', $default = false) ?>' />
                            </td>
                            <td>
                                <label>Button Border Radius</label>
                                <input type="number" size='3' name="xp_btn_border_radius" id="xp_btn_border_radius_" value='<?php echo get_option('xp_btn_border_radius', $default = false) ?>' />
                            </td>
                        </tr>
                    </table>
                </article>
            </section>
        </div>
        <!--  $id= $form_id.'-'.$field['id'];
                    register_setting('xpsocial-group', 'xpsocial_GF-'.$id);
    /****************************
    *       TAB 3               *
    *                           * 
    *****************************/
-->
        <div id="Tokyo" class="tabcontent">
            <h1>gianko.com</h1>
            <p>Webdeveloper/Business Developer</p>
            <a href="mailto:gian@gianko.com">gian@gianko.com</a>
        </div>




        <?php @submit_button(); ?>
    </form>
    <script>
        console.log('Created by ' + String.fromCodePoint(128568) + ' "https://gianko.com" ' + String.fromCodePoint(128561, 128640));
    </script>
    <?php
}

function allcountries()
{
    // Define la URL del endpoint de la API
    $api_url = URLAPI . "countries/all";
    // Inicializa una sesión cURL
    $ch = curl_init();

    // Configura las opciones cURL
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Referer: ' . $_SERVER['HTTP_HOST']
    ));

    // Ejecuta la solicitud
    $response = curl_exec($ch);

    // Verifica si ocurrió algún error
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
        curl_close($ch);
        exit;
    }

    // Cierra la sesión cURL
    curl_close($ch);

    // Decodifica el JSON de la respuesta
    $countries = json_decode($response, true);

    return $countries;
}
