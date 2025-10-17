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

    // Configuraciones de redirección movidas a configuración por formulario
    // Las URLs de redirect ahora se configuran individualmente en cada formulario

    // Opcionalmente, puedes agregar cualquier código JavaScript inline que necesites
   /* wp_add_inline_script('xpsocial-options-script', '
        console.log("XPSocial URLs loaded:", URLxpSocialPluginData);
    ');*/
}

add_action('wp_enqueue_scripts', 'xpsocial_enqueue_scripts');

function xpsocial_admin_menu()
{
    add_options_page('XpSocial Configuracion', 'Social Login Config', 'manage_options', 'xpsocial_setting_page', 'xpsocial_plugin_options');
    add_options_page('XpSocial Actualizaciones', 'Social Login Updates', 'manage_options', 'xpsocial_updates_page', 'xpsocial_updates_options');
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
    // Google Sheets functionality removed
    register_setting('xpsocial-group', 'xpsocial_GFxeerpa');
    // Configuraciones de redirect eliminadas - ahora se configuran por formulario
    register_setting('xpsocial-group', 'xpsocial_marca');
    register_setting('xpsocial-group', 'xpsocial_linkPP');
    register_setting('xpsocial-group', 'xpsocial_linkTyC');
    register_setting('xpsocial-group', 'xpsocial_licencia');
    register_setting('xpsocial-group', 'xpsocial_countries');
    // register_setting('xpsocial-group', 'xpsocial_lost_password_url'); // eliminado - no se requiere
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
        <h1> Social Login - Xeerpa </h1>
        <img src="<?php echo esc_url(plugins_url()); ?>/xpsocial_login/public/images/gianko-logo.png" alt="logo de gianko" width="250">
    </span>
    
    <!-- Estilos CSS para pestañas minimalistas -->
    <style>
        /* Contenedor de pestañas */
        .tab {
            display: flex;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
            border-bottom: 1px solid #e9ecef;
            margin: 20px 0 0 0;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        /* Botones de pestañas */
        .tablinks {
            background: transparent;
            border: none;
            padding: 16px 24px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.3s ease;
            border-radius: 8px 8px 0 0;
            position: relative;
            flex: 1;
            text-align: center;
            outline: none;
        }
        
        /* Hover en pestañas */
        .tablinks:hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-1px);
        }
        
        /* Pestaña activa */
        .tablinks.active {
            background: #ffffff;
            color: #007cba;
            border-bottom: 3px solid #007cba;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
        }
        
        /* Contenido de pestañas */
        .tabcontent {
            display: none;
            background: #ffffff;
            border-radius: 0 0 8px 8px;
            padding: 30px;
            margin: 0 0 20px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            border-top: none;
            animation: fadeIn 0.3s ease-in-out;
        }
        
        /* Pestaña activa por defecto */
        .tabcontent.defaultOpen {
            display: block;
        }
        
        /* Animación de entrada */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Mejoras para formularios dentro de pestañas */
        .tabcontent .form-table {
            margin: 0;
        }
        
        .tabcontent .form-table th {
            width: 200px;
            padding: 15px 10px 15px 0;
            font-weight: 600;
            color: #495057;
        }
        
        .tabcontent .form-table td {
            padding: 15px 0;
        }
        
        .tabcontent input[type="text"],
        .tabcontent input[type="url"],
        .tabcontent input[type="number"],
        .tabcontent input[type="color"],
        .tabcontent select {
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 14px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
            max-width: 400px;
        }
        
        .tabcontent input[type="text"]:focus,
        .tabcontent input[type="url"]:focus,
        .tabcontent input[type="number"]:focus,
        .tabcontent input[type="color"]:focus,
        .tabcontent select:focus {
            border-color: #007cba;
            box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
            outline: none;
        }
        
        .tabcontent small {
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        /* Estilos para la sección de estilos */
        .tabcontent .form_data_style {
            width: 100%;
            border-collapse: collapse;
        }
        
        .tabcontent .form_data_style td {
            padding: 10px;
            vertical-align: top;
        }
        
        .tabcontent .form_data_style label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
            color: #495057;
        }
        
        /* Estilos específicos para campos de color */
        .tabcontent input[type="color"] {
            width: 80px !important;
            height: 50px;
            border: 3px solid #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .tabcontent input[type="color"]:hover {
            border-color: #007cba;
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 25px rgba(0, 124, 186, 0.15);
        }
        
        .tabcontent input[type="color"]:active {
            transform: translateY(0) scale(0.98);
            box-shadow: 0 2px 8px rgba(0, 124, 186, 0.2);
        }
        
        .tabcontent input[type="color"]:focus {
            border-color: #007cba;
            box-shadow: 0 0 0 4px rgba(0, 124, 186, 0.1);
            outline: none;
        }
        
        /* Estilos para el wrapper del color swatch */
        .tabcontent input[type="color"]::-webkit-color-swatch-wrapper {
            padding: 4px;
            border-radius: 8px;
            background: transparent;
        }
        
        /* Estilos para el color swatch */
        .tabcontent input[type="color"]::-webkit-color-swatch {
            border: 2px solid rgba(255, 255, 255, 0.8);
            border-radius: 6px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        /* Efecto de brillo en el color swatch */
        .tabcontent input[type="color"]::-webkit-color-swatch::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, transparent 50%);
            border-radius: 6px;
            pointer-events: none;
        }
        
        /* Estilos para Firefox */
        .tabcontent input[type="color"]::-moz-color-swatch {
            border: 2px solid rgba(255, 255, 255, 0.8);
            border-radius: 6px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* Animación de pulso para campos vacíos */
        .tabcontent input[type="color"]:empty {
            animation: colorPulse 2s infinite;
        }
        
        @keyframes colorPulse {
            0%, 100% { 
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }
            50% { 
                box-shadow: 0 2px 8px rgba(0, 124, 186, 0.2);
            }
        }
        
        /* Efecto de gradiente en el borde */
        .tabcontent input[type="color"]::before {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            background: linear-gradient(45deg, #007cba, #0056b3, #007cba);
            border-radius: 15px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .tabcontent input[type="color"]:hover::before {
            opacity: 1;
        }
        
        /* Estilos para labels de color */
        .tabcontent .form_data_style label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #495057;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Contenedor mejorado para campos de color */
        .tabcontent .form_data_style td {
            padding: 15px;
            vertical-align: top;
            position: relative;
        }
        
        /* Indicador de color seleccionado */
        .tabcontent input[type="color"]::after {
            content: '✓';
            position: absolute;
            top: 2px;
            right: 2px;
            color: white;
            font-size: 10px;
            font-weight: bold;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .tabcontent input[type="color"]:not([value=""])::after {
            opacity: 1;
        }
        
        /* Estilos para campos con color seleccionado */
        .tabcontent input[type="color"].has-color {
            border-color: #28a745;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
        }
        
        .tabcontent input[type="color"].has-color:hover {
            border-color: #28a745;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.25);
        }
        
        /* Efecto de ondas al hacer click */
        .tabcontent input[type="color"] {
            position: relative;
            overflow: hidden;
        }
        
        .tabcontent input[type="color"]::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .tabcontent input[type="color"]:active::before {
            width: 300px;
            height: 300px;
        }
        
        /* Mejoras para la tabla de estilos */
        .tabcontent .form_data_style {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .tabcontent .form_data_style tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .tabcontent .form_data_style tr:hover {
            background: #e3f2fd;
            transition: background-color 0.3s ease;
        }
        
        /* Estilos para inputs numéricos y de texto */
        .tabcontent input[type="number"],
        .tabcontent input[type="text"] {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .tabcontent input[type="number"]:focus,
        .tabcontent input[type="text"]:focus {
            border-color: #007cba;
            box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
            outline: none;
        }
        
        .tabcontent input[type="number"]:hover,
        .tabcontent input[type="text"]:hover {
            border-color: #007cba;
            box-shadow: 0 4px 12px rgba(0, 124, 186, 0.1);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .tab {
                flex-direction: column;
            }
            
            .tablinks {
                border-radius: 0;
                border-bottom: 1px solid #e9ecef;
            }
            
            .tablinks:last-child {
                border-bottom: none;
            }
            
            .tabcontent {
                border-radius: 0 0 8px 8px;
                padding: 20px;
            }
        }
        
        /* Mejoras para el título */
        .xp_title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 20px 0;
            border-bottom: 2px solid #e9ecef;
        }
        
        .xp_title h1 {
            margin: 0;
            color: #495057;
            font-size: 28px;
            font-weight: 600;
        }
        
        /* Estilos para el contenedor del color picker */
        .color-picker-container {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: flex-start;
        }
        
        /* Estilos para el botón transparente */
        .transparent-btn {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #dee2e6;
            border-radius: 6px;
            color: #6c757d;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .transparent-btn:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            border-color: #adb5bd;
            color: #495057;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .transparent-btn:active {
            transform: translateY(0);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }
        
        .transparent-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }
        
        .transparent-btn:hover::before {
            left: 100%;
        }
        
        /* Estilos especiales para campos transparentes */
        .tabcontent input[type="color"].is-transparent {
            border-color: #6c757d;
            background: linear-gradient(45deg, #ccc 25%, transparent 25%), 
                        linear-gradient(-45deg, #ccc 25%, transparent 25%), 
                        linear-gradient(45deg, transparent 75%, #ccc 75%), 
                        linear-gradient(-45deg, transparent 75%, #ccc 75%);
            background-size: 8px 8px;
            background-position: 0 0, 0 4px, 4px -4px, -4px 0px;
            position: relative;
        }
        
        .tabcontent input[type="color"].is-transparent::after {
            content: 'T';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #6c757d;
            font-size: 12px;
            font-weight: bold;
            text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
            pointer-events: none;
        }
        
        /* Animación para el patrón de transparencia */
        .tabcontent input[type="color"].is-transparent {
            animation: transparentPattern 2s ease-in-out infinite;
        }
        
        @keyframes transparentPattern {
            0%, 100% { 
                background-position: 0 0, 0 4px, 4px -4px, -4px 0px;
            }
            50% { 
                background-position: 4px 4px, 4px 8px, 8px 0px, 0px 4px;
            }
        }
    </style>
    
    <form action="options.php" method="post">
        <?php @submit_button(); ?>
        <?php settings_fields('xpsocial-group'); ?>
        <?php @do_settings_fields('xpsocial_plugin_options', 'xpsocial-group') ?>

        <div class="tab">
            <div class="tablinks " onclick="openCity(event, 'XeerpaConfig')" id="defaultOpen">Xeerpa Config</div>
            <div class="tablinks" onclick="openCity(event, 'FIFCOGCP')">FIFCO GCP</div>
            <div class="tablinks" onclick="openCity(event, 'Estilos')">Estilos</div>
            <div class="tablinks" onclick="openCity(event, 'Acerca')">Acerca de</div>
        </div>
        <!--
    /****************************
    *       TAB 1               *
    *                           * 
    *****************************/
-->
        <div id="XeerpaConfig" class="tabcontent defaultOpen">
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
                    <!-- URL Redirect Login eliminado - ahora se configura por formulario -->
                    <!-- URL de Registro eliminado - ahora se configura por formulario -->
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
                    <!-- LINK para olvidé mi contraseña eliminado - no se requiere -->
                    <!-- URL de redirección para usuarios existentes eliminado - ahora se configura por formulario -->
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
    *       TAB 2 - FIFCO GCP   *
    *                           * 
    *****************************/
-->
        <div id="FIFCOGCP" class="tabcontent">
            <table class='form-table'>
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
            </table>
        </div>
        <!--
    /****************************
    *       TAB 3 - Estilos     *
    *                           * 
    *****************************/
-->
        <div id="Estilos" class="tabcontent">
            <!-- Google Sheets configuration removed -->
            <section>
                <H3>Estilos de Formularios</H3>
                <article>
                    <table class='form_data_style'>
                        <tr valign='top'>
                            <td>
                                <label>Inputs Height</label>
                                <input type="number" size='3' name="xp_input_height" id="xp_input_height_" value='<?php echo get_option('xp_input_height', '40') ?>' />
                            </td>
                            <td>
                                <label>Inputs Border</label>
                                <input type="text" size='30' placeholder="1px solid #ced4da" name="xp_input_border" id="xp_input_border_" value='<?php echo get_option('xp_input_border', '1px solid #ced4da') ?>' />
                            </td>
                            <td>
                                <label>Inputs Border-radius</label>
                                <input type="number" size='30' placeholder="6" name="xp_input_border_radius" id="xp_input_border_radius_" value='<?php echo get_option('xp_input_border_radius', '6') ?>' />
                            </td>
                            <td>
                                <label>Input Backgound-color</label>
                                <div class="color-picker-container">
                                    <input type="color" size='3' name="xp_bg_color" id="xp_bg_color_" value='<?php echo get_option('xp_bg_color', '#ffffff') ?>' />
                                    <button type="button" class="transparent-btn" data-target="xp_bg_color_" title="Seleccionar transparente">Transparente</button>
                                </div>
                            </td>
                            <td>
                                <label>Login Backgound-color</label>
                                <div class="color-picker-container">
                                    <input type="color" size='3' name="xp_div_color" id="xp_div_color_" value='<?php echo get_option('xp_div_color', '#f8f9fa') ?>' />
                                    <button type="button" class="transparent-btn" data-target="xp_div_color_" title="Seleccionar transparente">Transparente</button>
                                </div>
                            </td>
                            <td>
                                <label>Font Color Login</label>
                                <div class="color-picker-container">
                                    <input type="color" size='3' name="xp_font_color" id="xp_font_color_" value='<?php echo get_option('xp_font_color', '#495057') ?>' />
                                    <button type="button" class="transparent-btn" data-target="xp_font_color_" title="Seleccionar transparente">Transparente</button>
                                </div>
                            </td>
                            <td>
                                <label>Divisor Login Color</label>
                                <div class="color-picker-container">
                                    <input type="color" size='3' name="xp_divisor_color" id="xp_divisor_color_" value='<?php echo get_option('xp_divisor_color', '#e9ecef') ?>' />
                                    <button type="button" class="transparent-btn" data-target="xp_divisor_color_" title="Seleccionar transparente">Transparente</button>
                                </div>
                            </td>
                        </tr>
                        <tr valign='top'>
                            <td>
                                <label>Button Height</label>
                                <input type="number" size='3' name="xp_btn_height" id="xp_btn_height_" value='<?php echo get_option('xp_btn_height', '40') ?>' />
                            </td>
                            <td>
                                <label>Button Width</label>
                                <input type="number" size='3' name="xp_btn_width" id="xp_btn_width_" value='<?php echo get_option('xp_btn_width', '100') ?>' />
                            </td>
                            <td>
                                <label>Button Color</label>
                                <div class="color-picker-container">
                                    <input type="color" size='3' name="xp_btn_color" id="xp_btn_color_" value='<?php echo get_option('xp_btn_color', '#ffffff') ?>' />
                                    <button type="button" class="transparent-btn" data-target="xp_btn_color_" title="Seleccionar transparente">Transparente</button>
                                </div>
                            </td>
                            <td>
                                <label>Button Background Color</label>
                                <div class="color-picker-container">
                                    <input type="color" size='3' name="xp_btn_bgcolor" id="xp_btn_bgcolor_" value='<?php echo get_option('xp_btn_bgcolor', '#007cba') ?>' />
                                    <button type="button" class="transparent-btn" data-target="xp_btn_bgcolor_" title="Seleccionar transparente">Transparente</button>
                                </div>
                            </td>
                            <td>
                                <label>Button Border Width</label>
                                <input type="number" size='3' name="xp_btn_border_width" id="xp_btn_border_width_" value='<?php echo get_option('xp_btn_border_width', '1') ?>' />
                            </td>
                            <td>
                                <label>Button Border Color</label>
                                <div class="color-picker-container">
                                    <input type="color" size='3' name="xp_btn_border_color" id="xp_btn_border_color_" value='<?php echo get_option('xp_btn_border_color', '#007cba') ?>' />
                                    <button type="button" class="transparent-btn" data-target="xp_btn_border_color_" title="Seleccionar transparente">Transparente</button>
                                </div>
                            </td>
                            <td>
                                <label>Button Border Radius</label>
                                <input type="number" size='3' name="xp_btn_border_radius" id="xp_btn_border_radius_" value='<?php echo get_option('xp_btn_border_radius', '6') ?>' />
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
        <div id="Acerca" class="tabcontent">
            <h1>gianko.com</h1>
            <p>Webdeveloper/Business Developer</p>
            <a href="mailto:gian@gianko.com">gian@gianko.com</a>
        </div>




        <?php @submit_button(); ?>
    </form>
    
    <!-- JavaScript para funcionalidad de pestañas y color picker -->
    <script>
        function openCity(evt, cityName) {
            // Ocultar todos los contenidos de pestañas
            var tabcontent = document.getElementsByClassName("tabcontent");
            for (var i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            
            // Remover la clase "active" de todos los botones de pestañas
            var tablinks = document.getElementsByClassName("tablinks");
            for (var i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            
            // Mostrar el contenido de la pestaña seleccionada
            document.getElementById(cityName).style.display = "block";
            
            // Agregar la clase "active" al botón de pestaña que fue clickeado
            evt.currentTarget.classList.add("active");
        }
        
        // Función para sincronizar color picker con campos de entrada
        function syncColorPickers() {
            // Obtener todos los campos de color
            var colorInputs = document.querySelectorAll('input[type="color"]');
            
            colorInputs.forEach(function(input) {
                // Agregar event listener para cambios en el color
                input.addEventListener('input', function() {
                    // Si el usuario selecciona un color normal, remover la marca de transparencia
                    if (this.getAttribute('data-transparent') === 'true') {
                        this.removeAttribute('data-transparent');
                        this.setAttribute('value', this.value);
                    } else {
                        // Actualizar el valor del campo
                        this.setAttribute('value', this.value);
                    }
                    
                    // Actualizar la visualización del color
                    updateColorDisplay(this);
                    
                    // Agregar clase para indicar que tiene color
                    this.classList.add('has-color');
                    
                    // Efecto visual de confirmación
                    this.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 200);
                    
                    // Log para debugging
                    console.log('Color actualizado:', this.name, '=', this.value);
                });
                
                // Establecer el color de fondo inicial
                if (input.value && input.value !== '') {
                    // Verificar si el valor guardado es transparente
                    if (input.value === 'transparent') {
                        input.setAttribute('data-transparent', 'true');
                        input.value = '#000000'; // Color temporal para el input
                    }
                    updateColorDisplay(input);
                    input.classList.add('has-color');
                } else {
                    input.classList.remove('has-color');
                }
                
                // Agregar efecto de hover mejorado
                input.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.02)';
                });
                
                input.addEventListener('mouseleave', function() {
                    if (!this.matches(':focus')) {
                        this.style.transform = '';
                    }
                });
            });
            
            // Agregar event listeners para los botones de transparente
            var transparentBtns = document.querySelectorAll('.transparent-btn');
            transparentBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var targetId = this.getAttribute('data-target');
                    var targetInput = document.getElementById(targetId);
                    
                    if (targetInput) {
                        // Usar un color especial para representar transparencia
                        // Usamos rgba(0,0,0,0) que es transparente pero válido para el input
                        targetInput.value = '#000000';
                        targetInput.setAttribute('value', 'transparent');
                        targetInput.setAttribute('data-transparent', 'true');
                        
                        // Actualizar la visualización
                        updateColorDisplay(targetInput);
                        
                        // Agregar clase especial para transparente
                        targetInput.classList.add('has-color', 'is-transparent');
                        
                        // Efecto visual
                        targetInput.style.transform = 'scale(1.1)';
                        setTimeout(() => {
                            targetInput.style.transform = '';
                        }, 200);
                        
                        console.log('Color establecido como transparente:', targetInput.name);
                    }
                });
            });
        }
        
        // Función para actualizar la visualización del color
        function updateColorDisplay(input) {
            // Verificar si el campo está marcado como transparente
            var isTransparent = input.getAttribute('data-transparent') === 'true' || 
                               input.getAttribute('value') === 'transparent' ||
                               input.value === 'transparent';
            
            if (isTransparent || input.value === '') {
                // Mostrar patrón de transparencia
                input.style.background = 'linear-gradient(45deg, #ccc 25%, transparent 25%), linear-gradient(-45deg, #ccc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #ccc 75%), linear-gradient(-45deg, transparent 75%, #ccc 75%)';
                input.style.backgroundSize = '8px 8px';
                input.style.backgroundPosition = '0 0, 0 4px, 4px -4px, -4px 0px';
                input.style.backgroundColor = 'transparent';
                
                // Asegurar que tenga la clase de transparencia
                input.classList.add('is-transparent');
            } else {
                // Mostrar color sólido
                input.style.background = input.value;
                input.style.backgroundColor = input.value;
                
                // Remover clase de transparencia si no es transparente
                input.classList.remove('is-transparent');
                input.removeAttribute('data-transparent');
            }
        }
        
        // Función para procesar valores transparentes antes de enviar el formulario
        function processTransparentValues() {
            var colorInputs = document.querySelectorAll('input[type="color"]');
            colorInputs.forEach(function(input) {
                if (input.getAttribute('data-transparent') === 'true') {
                    // Crear un input oculto para enviar el valor transparente
                    var hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = input.name;
                    hiddenInput.value = 'transparent';
                    
                    // Insertar el input oculto antes del input de color
                    input.parentNode.insertBefore(hiddenInput, input);
                    
                    // Deshabilitar el input de color para que no se envíe
                    input.disabled = true;
                }
            });
        }
        
        // Activar la primera pestaña por defecto y sincronizar color pickers
        document.addEventListener('DOMContentLoaded', function() {
            // Encontrar el botón con id "defaultOpen" y hacer click
            var defaultTab = document.getElementById("defaultOpen");
            if (defaultTab) {
                defaultTab.click();
            }
            
            // Sincronizar color pickers
            syncColorPickers();
            
            // Agregar event listener al formulario para procesar valores transparentes
            var form = document.querySelector('form[action="options.php"]');
            if (form) {
                form.addEventListener('submit', processTransparentValues);
            }
            
            // Re-sincronizar después de un breve delay para asegurar que todos los elementos estén cargados
            setTimeout(syncColorPickers, 500);
        });
        
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

/**
 * Página de administración para actualizaciones
 */
function xpsocial_updates_options() {
    // Verificar permisos
    if (!current_user_can('manage_options')) {
        wp_die('No tienes permisos para acceder a esta página.');
    }
    
    // Obtener instancia del updater
    $plugin_file = plugin_basename(dirname(__FILE__) . '/../xpsocial_login.php');
    $updater = new Xpsocial_Git_Updater(plugin_dir_path(dirname(__FILE__)) . '../' . $plugin_file);
    $update_status = $updater->get_update_status();
    
    // Procesar acciones AJAX
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'check_updates' && wp_verify_nonce($_POST['nonce'], 'xpsocial_update_nonce')) {
            delete_transient('xpsocial_last_update_check');
            $updater->perform_update_check();
            $update_status = $updater->get_update_status();
            echo '<div class="notice notice-success"><p>Verificación de actualizaciones completada. Revisa el debug.log para más detalles.</p></div>';
        }
        
        if ($_POST['action'] === 'debug_check' && wp_verify_nonce($_POST['nonce'], 'xpsocial_update_nonce')) {
            delete_transient('xpsocial_last_update_check');
            $updater->perform_update_check();
            $update_status = $updater->get_update_status();
            echo '<div class="notice notice-info"><p><strong>Debug completado.</strong> Revisa el archivo debug.log en /wp-content/debug.log para ver los logs detallados de la verificación.</p></div>';
        }
    }
    
    ?>
    <div class="wrap">
        <h1>XpSocial Login - Actualizaciones</h1>
        
        <div class="card">
            <h2>Estado de Actualizaciones</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Versión Actual</th>
                    <td><strong><?php echo esc_html($update_status['current_version']); ?></strong></td>
                </tr>
                <tr>
                    <th scope="row">Actualización Disponible</th>
                    <td>
                        <?php if ($update_status['update_available']): ?>
                            <span style="color: #d63638;">Sí - Versión <?php echo esc_html($update_status['new_version']); ?></span>
                        <?php else: ?>
                            <span style="color: #00a32a;">No - El plugin está actualizado</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Última Verificación</th>
                    <td><?php echo esc_html($update_status['last_check']); ?></td>
                </tr>
                <tr>
                    <th scope="row">Tipo de Verificación</th>
                    <td><?php echo esc_html($update_status['next_check']); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="card">
            <h2>Acciones</h2>
            <form method="post" action="">
                <?php wp_nonce_field('xpsocial_update_nonce', 'nonce'); ?>
                <input type="hidden" name="action" value="check_updates">
                <p>
                    <input type="submit" class="button button-primary" value="Verificar Actualizaciones Ahora">
                    <span class="description">Fuerza una verificación inmediata de actualizaciones disponibles.</span>
                </p>
            </form>
            
            <form method="post" action="">
                <?php wp_nonce_field('xpsocial_update_nonce', 'nonce'); ?>
                <input type="hidden" name="action" value="debug_check">
                <p>
                    <input type="submit" class="button button-secondary" value="Debug - Ver Logs">
                    <span class="description">Ejecuta verificación con logs detallados para debug.</span>
                </p>
            </form>
            
            <?php if ($update_status['update_available']): ?>
                <div class="notice notice-warning">
                    <p><strong>Actualización disponible:</strong> Hay una nueva versión (<?php echo esc_html($update_status['new_version']); ?>) disponible. 
                    Ve a <a href="<?php echo admin_url('plugins.php'); ?>">Plugins</a> para actualizar.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Información del Sistema</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Repositorio Git</th>
                    <td>
                        <code>https://github.com/giankocr/xeerpa_forms.git</code>
                        <p class="description">Repositorio configurado en class-xpsocial_git-updater.php</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Rama</th>
                    <td><code>develop</code></td>
                </tr>
                <tr>
                    <th scope="row">Intervalo de Verificación</th>
                    <td>Manual únicamente</td>
                </tr>
                <tr>
                    <th scope="row">URL de Verificación</th>
                    <td>
                        <code>https://github.com/giankocr/xeerpa_forms/raw/develop/xpsocial_login.php</code>
                        <p class="description">URL que se verifica para obtener la versión</p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="card">
            <h2>Configuración</h2>
            <p>Para configurar el repositorio Git, edita el archivo:</p>
            <code>includes/class-xpsocial_git-updater.php</code>
            <p>Y cambia la variable <code>$git_repo_url</code> por la URL de tu repositorio.</p>
        </div>
    </div>
    
    <style>
    .card {
        background: #fff;
        border: 1px solid #c3c4c7;
        border-radius: 4px;
        padding: 20px;
        margin: 20px 0;
    }
    .card h2 {
        margin-top: 0;
    }
    </style>
    <?php
}
