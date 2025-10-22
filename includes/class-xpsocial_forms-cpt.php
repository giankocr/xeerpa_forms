<?php
/**
 * Custom Post Type para formularios dinámicos de XPSocial
 */

if (!defined('ABSPATH')) {
    exit;
}

class Xpsocial_Forms_CPT
{
    public function __construct()
    {
        add_action('init', array($this, 'register_cpt'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_form_meta'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_filter('manage_xpsocial_form_posts_columns', array($this, 'custom_columns'));
        add_action('manage_xpsocial_form_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'prevent_external_assets'));
        add_action('admin_head', array($this, 'add_admin_styles'));
    }

    /**
     * Agregar estilos CSS para la administración
     */
    public function add_admin_styles()
    {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'xpsocial_form') {
            ?>
            <style>
            /* Estilos para la columna de acciones */
            .wp-list-table .column-actions {
                width: 120px;
            }
            
            .wp-list-table .column-actions .button {
                margin: 2px;
                padding: 4px 8px;
                font-size: 11px;
                line-height: 1.4;
                min-height: auto;
            }
            
            .wp-list-table .column-actions .button[style*="color: #a00"] {
                border-color: #dc3232;
                color: #dc3232 !important;
            }
            
            .wp-list-table .column-actions .button[style*="color: #a00"]:hover {
                background: #dc3232;
                color: #fff !important;
            }
            </style>
            <?php
        }
    }

    /**
     * Prevenir carga de assets externos para evitar ERR_TOO_MANY_RETRIES
     */
    public function prevent_external_assets($hook)
    {
        // Solo aplicar en páginas del plugin
        $is_xpsocial_page = (
            strpos($hook, 'xpsocial') !== false || 
            strpos($hook, 'xpsocial_form') !== false ||
            (isset($_GET['post_type']) && $_GET['post_type'] === 'xpsocial_form') ||
            (isset($_GET['page']) && strpos($_GET['page'], 'xpsocial') !== false)
        );
        
        if ($is_xpsocial_page) {
            // Remover scripts y estilos que pueden causar problemas
            wp_dequeue_script('jquery');
            wp_dequeue_script('jquery-migrate');
            wp_dequeue_style('wp-admin');
            wp_dequeue_style('common');
            wp_dequeue_style('forms');
            wp_dequeue_style('admin-menu');
            wp_dequeue_style('dashboard');
            wp_dequeue_style('list-tables');
            wp_dequeue_style('edit');
            wp_dequeue_style('revisions');
            wp_dequeue_style('media');
            wp_dequeue_style('themes');
            wp_dequeue_style('about');
            wp_dequeue_style('nav-menus');
            wp_dequeue_style('wp-pointer');
            wp_dequeue_style('widgets');
            wp_dequeue_style('site-icon');
            wp_dequeue_style('l10n');
            wp_dequeue_style('buttons');
            wp_dequeue_style('wp-auth-check');
            
            // Agregar estilos mínimos necesarios inline
            add_action('admin_head', array($this, 'add_minimal_admin_styles'));
        }
    }

    /**
     * Agregar estilos mínimos del admin
     */
    public function add_minimal_admin_styles()
    {
        ?>
        <style>
        /* Estilos mínimos del admin de WordPress */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #23282d;
            background: #f1f1f1;
            margin: 0;
            padding: 0;
        }
        
        #wpbody {
            padding: 20px;
        }
        
        .wrap {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: #23282d;
            font-weight: 600;
            margin: 0 0 10px 0;
        }
        
        h1 {
            font-size: 23px;
        }
        
        h2 {
            font-size: 18px;
        }
        
        p {
            margin: 0 0 10px 0;
        }
        
        a {
            color: #2271b1;
            text-decoration: none;
        }
        
        a:hover {
            color: #135e96;
        }
        
        /* Estilos para formularios */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        .form-table th,
        .form-table td {
            padding: 15px 10px;
            border-bottom: 1px solid #c3c4c7;
            vertical-align: top;
        }
        
        .form-table th {
            width: 200px;
            font-weight: 600;
            color: #23282d;
        }
        
        .form-table tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        
        /* Estilos para inputs */
        input[type="text"],
        input[type="email"],
        input[type="url"],
        input[type="password"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            max-width: 400px;
            padding: 8px 12px;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            background-color: #fff;
            font-size: 14px;
            line-height: 1.4;
        }
        
        input[type="color"] {
            width: 50px;
            height: 35px;
            padding: 0;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            cursor: pointer;
        }
        
        input[type="checkbox"] {
            margin: 0 5px 0 0;
        }
        
        /* Estilos para botones */
        .button,
        .button-primary,
        .button-secondary {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px 0 0;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            background: #f6f7f7;
            color: #2c3338;
            text-decoration: none;
            font-size: 13px;
            line-height: 1.4;
            cursor: pointer;
            text-align: center;
        }
        
        .button-primary {
            background: #2271b1;
            border-color: #2271b1;
            color: #fff;
        }
        
        .button-primary:hover {
            background: #135e96;
            border-color: #135e96;
            color: #fff;
        }
        
        /* Estilos para tabs */
        .nav-tab-wrapper {
            margin-bottom: 20px;
            border-bottom: 1px solid #c3c4c7;
        }
        
        .nav-tab {
            display: inline-block;
            padding: 8px 12px;
            margin-right: 5px;
            border: 1px solid #c3c4c7;
            border-bottom: none;
            background: #f6f7f7;
            color: #2c3338;
            text-decoration: none;
            border-radius: 3px 3px 0 0;
        }
        
        .nav-tab:hover {
            background: #f0f0f1;
            color: #2c3338;
        }
        
        .nav-tab-active {
            background: #fff;
            color: #2c3338;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
            font-weight: 600;
        }
        
        /* Estilos para descripciones */
        .description {
            font-style: italic;
            color: #646970;
            font-size: 13px;
            margin-top: 5px;
        }
        
        /* Estilos para labels */
        label {
            font-weight: 600;
            color: #23282d;
        }
        
        /* Estilos para meta boxes */
        .field-item {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
        }
        
        .field-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .field-header h4 {
            margin: 0;
        }
        </style>
        <?php
    }

    /**
     * Registrar Custom Post Type
     */
    public function register_cpt()
    {
        $labels = array(
            'name' => 'Formularios XP Social',
            'singular_name' => 'Formulario XP Social',
            'menu_name' => 'Formularios XP Social',
            'add_new' => 'Agregar Nuevo',
            'add_new_item' => 'Agregar Nuevo Formulario',
            'edit_item' => 'Editar Formulario',
            'new_item' => 'Nuevo Formulario',
            'view_item' => 'Ver Formulario',
            'search_items' => 'Buscar Formularios',
            'not_found' => 'No se encontraron formularios',
            'not_found_in_trash' => 'No se encontraron formularios en la papelera'
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Lo manejamos manualmente
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'menu_icon' => 'dashicons-feedback',
            'supports' => array('title', 'editor'),
            'show_in_rest' => false
        );

        register_post_type('xpsocial_form', $args);
    }

    /**
     * Agregar menú de administración
     */
    public function add_admin_menu()
    {
        add_menu_page(
            'Formularios XP Social',
            'Formularios XP Social',
            'manage_options',
            'xpsocial-forms',
            array($this, 'forms_list_page'),
            'dashicons-feedback',
            30
        );

        add_submenu_page(
            'xpsocial-forms',
            'Todos los Formularios',
            'Todos los Formularios',
            'manage_options',
            'edit.php?post_type=xpsocial_form'
        );

        add_submenu_page(
            'xpsocial-forms',
            'Agregar Nuevo',
            'Agregar Nuevo',
            'manage_options',
            'post-new.php?post_type=xpsocial_form'
        );
    }

    /**
     * Página de lista de formularios
     */
    public function forms_list_page()
    {
        $forms = get_posts(array(
            'post_type' => 'xpsocial_form',
            'numberposts' => -1,
            'post_status' => 'publish'
        ));

        echo '<div class="wrap">';
        echo '<h1>Formularios XP Social</h1>';
        
        if (empty($forms)) {
            echo '<p>No hay formularios creados aún.</p>';
            echo '<a href="' . admin_url('post-new.php?post_type=xpsocial_form') . '" class="button button-primary">Crear Primer Formulario</a>';
        } else {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';
            echo '<th>Título</th>';
            echo '<th>Source</th>';
            echo '<th>Marca FIFCO</th>';
            echo '<th>Campos Dinámicos</th>';
            echo '<th>Acciones</th>';
            echo '</tr></thead>';
            echo '<tbody>';
            
            foreach ($forms as $form) {
                $source = get_post_meta($form->ID, '_xpsocial_source', true);
                $marca = get_post_meta($form->ID, '_xpsocial_marca', true);
                $dynamic_fields = get_post_meta($form->ID, '_xpsocial_dynamic_fields', true);
                $fields_count = 0;
                
                if ($dynamic_fields) {
                    $fields_data = is_string($dynamic_fields) ? json_decode($dynamic_fields, true) : $dynamic_fields;
                    $fields_count = is_array($fields_data) ? count($fields_data) : 0;
                }
                
                echo '<tr>';
                echo '<td><strong>' . esc_html($form->post_title) . '</strong></td>';
                echo '<td>' . esc_html($source ?: 'No configurado') . '</td>';
                echo '<td>' . esc_html($marca ?: 'No configurado') . '</td>';
                echo '<td>' . $fields_count . ' campos</td>';
                echo '<td>';
                echo '<a href="' . get_edit_post_link($form->ID) . '" class="button button-small">Editar</a> ';
                echo '<a href="' . get_permalink($form->ID) . '" class="button button-small" target="_blank">Ver</a>';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        }
        
        echo '</div>';
    }

    /**
     * Agregar meta boxes
     */
    public function add_meta_boxes()
    {
        add_meta_box(
            'xpsocial_form_config',
            'Configuración del Formulario',
            array($this, 'form_config_meta_box'),
            'xpsocial_form',
            'normal',
            'high'
        );

        add_meta_box(
            'xpsocial_form_fields',
            'Campos Dinámicos',
            array($this, 'form_fields_meta_box'),
            'xpsocial_form',
            'normal',
            'high'
        );
    }

    /**
     * Meta box de configuración básica
     */
    public function form_config_meta_box($post)
    {
        wp_nonce_field('xpsocial_form_meta', 'xpsocial_form_meta_nonce');
        
        $source = get_post_meta($post->ID, '_xpsocial_source', true);
        $success_html = get_post_meta($post->ID, '_xpsocial_success_html', true);
        $redirect_url = get_post_meta($post->ID, '_xpsocial_redirect_url', true);
        $fifco_enabled = get_post_meta($post->ID, '_xpsocial_fifco_enabled', true);
        $marca = get_post_meta($post->ID, '_xpsocial_marca', true);
        $country = get_post_meta($post->ID, '_xpsocial_country', true);
        $show_terms = get_post_meta($post->ID, '_xpsocial_show_terms', true);
        $show_privacy = get_post_meta($post->ID, '_xpsocial_show_privacy', true);
        
        // Validation settings
        $validate_email = get_post_meta($post->ID, '_xpsocial_validate_email', true);
        $validate_id_number = get_post_meta($post->ID, '_xpsocial_validate_id_number', true);
        $validate_age = get_post_meta($post->ID, '_xpsocial_validate_age', true);
        $min_age = get_post_meta($post->ID, '_xpsocial_min_age', true) ?: 18;
        
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="xpsocial_source">Source</label></th>
                <td>
                    <input type="text" id="xpsocial_source" name="xpsocial_source" value="<?php echo esc_attr($source); ?>" class="regular-text" />
                    <p class="description">Identificador único para este formulario (ej: mi_formulario_2025)</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="xpsocial_success_html">Mensaje de Éxito</label></th>
                <td>
                    <textarea id="xpsocial_success_html" name="xpsocial_success_html" rows="5" cols="50" class="large-text"><?php echo esc_textarea($success_html); ?></textarea>
                    <p class="description">HTML que se mostrará después del registro exitoso (solo si no se configura redirect). Puedes usar etiquetas HTML como &lt;h1&gt;, &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;a&gt;, etc.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="xpsocial_redirect_url">URL de Redirección</label></th>
                <td>
                    <input type="url" id="xpsocial_redirect_url" name="xpsocial_redirect_url" value="<?php echo esc_attr($redirect_url); ?>" class="regular-text" placeholder="https://ejemplo.com/gracias" />
                    <p class="description">URL a la que redirigir después del registro exitoso. Si se deja vacío, se mostrará el mensaje de éxito HTML.</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="xpsocial_fifco_enabled">Activar FIFCO API</label></th>
                <td>
                    <input type="checkbox" id="xpsocial_fifco_enabled" name="xpsocial_fifco_enabled" value="1" <?php checked($fifco_enabled, '1'); ?> />
                    <label for="xpsocial_fifco_enabled">Habilitar integración con FIFCO API</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="xpsocial_marca">Marca FIFCO</label></th>
                <td>
                    <input type="text" id="xpsocial_marca" name="xpsocial_marca" value="<?php echo esc_attr($marca); ?>" class="regular-text" />
                    <p class="description">Marca específica para este formulario (ej: Imperial, Kerns, etc.)</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="xpsocial_country">País</label></th>
                <td>
                    <input type="text" id="xpsocial_country" name="xpsocial_country" value="<?php echo esc_attr($country); ?>" class="regular-text" />
                    <p class="description">País específico para este formulario</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="xpsocial_show_terms">Mostrar Términos</label></th>
                <td>
                    <input type="checkbox" id="xpsocial_show_terms" name="xpsocial_show_terms" value="1" <?php checked($show_terms, '1'); ?> />
                    <label for="xpsocial_show_terms">Mostrar checkbox de términos y condiciones</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="xpsocial_show_privacy">Mostrar Privacidad</label></th>
                <td>
                    <input type="checkbox" id="xpsocial_show_privacy" name="xpsocial_show_privacy" value="1" <?php checked($show_privacy, '1'); ?> />
                    <label for="xpsocial_show_privacy">Mostrar checkbox de política de privacidad</label>
                </td>
            </tr>
        </table>
        
        <h3>Configuración de Validaciones</h3>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="xpsocial_validate_email">Validar Email</label></th>
                <td>
                    <input type="checkbox" id="xpsocial_validate_email" name="xpsocial_validate_email" value="1" <?php checked($validate_email, '1'); ?> />
                    <label for="xpsocial_validate_email">Validar que el email no esté duplicado para esta campaña</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="xpsocial_validate_id_number">Validar ID Number</label></th>
                <td>
                    <input type="checkbox" id="xpsocial_validate_id_number" name="xpsocial_validate_id_number" value="1" <?php checked($validate_id_number, '1'); ?> />
                    <label for="xpsocial_validate_id_number">Validar que el número de identificación no esté duplicado para esta campaña</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="xpsocial_validate_age">Validar Edad</label></th>
                <td>
                    <input type="checkbox" id="xpsocial_validate_age" name="xpsocial_validate_age" value="1" <?php checked($validate_age, '1'); ?> />
                    <label for="xpsocial_validate_age">Validar edad mínima del usuario</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="xpsocial_min_age">Edad Mínima</label></th>
                <td>
                    <input type="number" id="xpsocial_min_age" name="xpsocial_min_age" value="<?php echo esc_attr($min_age); ?>" min="1" max="100" class="small-text" />
                    <p class="description">Edad mínima requerida (por defecto: 18 años)</p>
                </td>
            </tr>
        </table>
        
        <?php
    }

    /**
     * Meta box de campos dinámicos
     */
    public function form_fields_meta_box($post)
    {
        $dynamic_fields = get_post_meta($post->ID, '_xpsocial_dynamic_fields', true);
        $fields = array();
        
        if ($dynamic_fields) {
            $fields = is_string($dynamic_fields) ? json_decode($dynamic_fields, true) : $dynamic_fields;
            if (!is_array($fields)) {
                $fields = array();
            }
        }
        
        ?>
        <div id="dynamic-fields-container">
            <p class="description">Agrega campos dinámicos que aparecerán en el formulario antes de los términos y privacidad.</p>
            
            <div id="fields-list">
                <?php if (!empty($fields)): ?>
                    <?php foreach ($fields as $index => $field): ?>
                        <div class="field-item" data-index="<?php echo $index; ?>">
                            <div class="field-header">
                                <h4>Campo <?php echo $index + 1; ?></h4>
                                <button type="button" class="button remove-field">Eliminar</button>
                            </div>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label>Etiqueta</label></th>
                                    <td><input type="text" name="fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr($field['label']); ?>" class="regular-text" /></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label>Nombre del Campo</label></th>
                                    <td><input type="text" name="fields[<?php echo $index; ?>][name]" value="<?php echo esc_attr($field['name']); ?>" class="regular-text" /></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label>Tipo</label></th>
                                    <td>
                                        <select name="fields[<?php echo $index; ?>][type]" class="field-type-select">
                                            <option value="text" <?php selected($field['type'], 'text'); ?>>Texto</option>
                                            <option value="textarea" <?php selected($field['type'], 'textarea'); ?>>Área de Texto</option>
                                            <option value="select" <?php selected($field['type'], 'select'); ?>>Lista Desplegable</option>
                                            <option value="radio" <?php selected($field['type'], 'radio'); ?>>Botones de Radio</option>
                                            <option value="checkbox" <?php selected($field['type'], 'checkbox'); ?>>Casillas de Verificación</option>
                                            <option value="hidden" <?php selected($field['type'], 'hidden'); ?>>Campo Oculto</option>
                                            <option value="audio" <?php selected($field['type'], 'audio'); ?>>Audio (Grabar/Subir)</option>
                                            <option value="video" <?php selected($field['type'], 'video'); ?>>Video</option>
                                            <option value="image" <?php selected($field['type'], 'image'); ?>>Imagen</option>
                                            <option value="file" <?php selected($field['type'], 'file'); ?>>Archivo</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label>Requerido</label></th>
                                    <td>
                                        <input type="checkbox" name="fields[<?php echo $index; ?>][required]" value="1" <?php checked($field['required'], '1'); ?> />
                                        <label>Este campo es obligatorio</label>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label>Placeholder</label></th>
                                    <td><input type="text" name="fields[<?php echo $index; ?>][placeholder]" value="<?php echo esc_attr($field['placeholder']); ?>" class="regular-text" /></td>
                                </tr>
                                
                                <!-- Campo para valor por defecto (solo para campos ocultos) -->
                                <tr class="default-value-row" style="<?php echo $field['type'] === 'hidden' ? '' : 'display: none;'; ?>">
                                    <th scope="row"><label>Valor por Defecto</label></th>
                                    <td>
                                        <input type="text" name="fields[<?php echo $index; ?>][default_value]" value="<?php echo esc_attr($field['default_value'] ?? ''); ?>" class="regular-text" />
                                        <p class="description">Valor que tendrá el campo oculto por defecto</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label>Opciones (para select, radio, checkbox)</label></th>
                            <td>
                                <?php $options_value = is_array($field['options']) ? implode("\n", $field['options']) : (string)$field['options']; ?>
                                <textarea name="fields[<?php echo $index; ?>][options]" rows="3" cols="50" class="large-text"><?php echo esc_textarea($options_value); ?></textarea>
                                <p class="description">Una opción por línea</p>
                            </td>
                                </tr>
                                
                                <!-- Configuraciones específicas para campos de archivo -->
                                <tr class="file-config-row" style="<?php echo in_array($field['type'], ['audio', 'video', 'image', 'file']) ? '' : 'display: none;'; ?>">
                                    <th scope="row"><label>Configuración de Archivo</label></th>
                                    <td>
                                        <div class="file-config-container">
                                            <!-- Formatos permitidos -->
                                            <div class="file-config-item">
                                                <label><strong>Formatos Permitidos:</strong></label><br>
                                                <input type="text" name="fields[<?php echo $index; ?>][allowed_formats]" 
                                                       value="<?php echo esc_attr($field['allowed_formats'] ?? ''); ?>" 
                                                       class="regular-text" 
                                                       placeholder="<?php echo $field['type'] === 'audio' ? 'mp3,wav,m4a' : ($field['type'] === 'video' ? 'mp4,avi,mov' : ($field['type'] === 'image' ? 'jpg,jpeg,png,gif' : 'pdf,doc,docx,txt')); ?>" />
                                                <p class="description">Formatos permitidos separados por comas</p>
                                            </div>
                                            
                                            <!-- Tamaño máximo -->
                                            <div class="file-config-item">
                                                <label><strong>Tamaño Máximo (MB):</strong></label><br>
                                                <input type="number" name="fields[<?php echo $index; ?>][max_size_mb]" 
                                                       value="<?php echo esc_attr($field['max_size_mb'] ?? ''); ?>" 
                                                       class="small-text" min="1" max="100" 
                                                       placeholder="<?php echo $field['type'] === 'image' ? '5' : '10'; ?>" />
                                                <p class="description">Tamaño máximo en MB</p>
                                            </div>
                                            
                                            <!-- Duración máxima (solo para audio/video) -->
                                            <?php if (in_array($field['type'], ['audio', 'video'])): ?>
                                            <div class="file-config-item">
                                                <label><strong>Duración Máxima (segundos):</strong></label><br>
                                                <input type="number" name="fields[<?php echo $index; ?>][max_duration]" 
                                                       value="<?php echo esc_attr($field['max_duration'] ?? ''); ?>" 
                                                       class="small-text" min="1" max="3600" 
                                                       placeholder="<?php echo $field['type'] === 'audio' ? '300' : '600'; ?>" />
                                                <p class="description">Duración máxima en segundos</p>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Permitir grabación (solo para audio) -->
                                            <?php if ($field['type'] === 'audio'): ?>
                                            <div class="file-config-item">
                                                <label>
                                                    <input type="hidden" name="fields[<?php echo $index; ?>][allow_recording]" value="0" />
                                                    <input type="checkbox" name="fields[<?php echo $index; ?>][allow_recording]" 
                                                           value="1" <?php checked($field['allow_recording'] ?? '1', '1'); ?> />
                                                    <strong>Permitir grabación desde el dispositivo</strong>
                                                </label>
                                                <p class="description">Permite grabar audio directamente desde el micrófono del dispositivo</p>
                                            </div>
                                            
                                            <div class="file-config-item">
                                                <label>
                                                    <input type="hidden" name="fields[<?php echo $index; ?>][allow_file_upload]" value="0" />
                                                    <input type="checkbox" name="fields[<?php echo $index; ?>][allow_file_upload]" 
                                                           value="1" <?php checked($field['allow_file_upload'] ?? '1', '1'); ?> />
                                                    <strong>Permitir subir archivo de audio</strong>
                                                </label>
                                                <p class="description">Permite subir un archivo de audio existente desde el dispositivo</p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <button type="button" id="add-field" class="button button-secondary">Agregar Campo</button>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var fieldIndex = <?php echo count($fields); ?>;
            
            $('#add-field').click(function() {
                var fieldHtml = `
                    <div class="field-item" data-index="${fieldIndex}">
                        <div class="field-header">
                            <h4>Campo ${fieldIndex + 1}</h4>
                            <button type="button" class="button remove-field">Eliminar</button>
                        </div>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label>Etiqueta</label></th>
                                <td><input type="text" name="fields[${fieldIndex}][label]" value="" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label>Nombre del Campo</label></th>
                                <td><input type="text" name="fields[${fieldIndex}][name]" value="" class="regular-text" /></td>
                            </tr>
                            <tr>
                                <th scope="row"><label>Tipo</label></th>
                                <td>
                                    <select name="fields[${fieldIndex}][type]">
                                        <option value="text">Texto</option>
                                        <option value="textarea">Área de Texto</option>
                                        <option value="select">Lista Desplegable</option>
                                        <option value="radio">Botones de Radio</option>
                                        <option value="checkbox">Casillas de Verificación</option>
                                        <option value="hidden">Campo Oculto</option>
                                        <option value="audio">Audio (Grabar/Subir)</option>
                                        <option value="video">Video</option>
                                        <option value="image">Imagen</option>
                                        <option value="file">Archivo</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label>Requerido</label></th>
                                <td>
                                    <input type="checkbox" name="fields[${fieldIndex}][required]" value="1" />
                                    <label>Este campo es obligatorio</label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label>Placeholder</label></th>
                                <td><input type="text" name="fields[${fieldIndex}][placeholder]" value="" class="regular-text" /></td>
                            </tr>
                            
                            <!-- Campo para valor por defecto (solo para campos ocultos) -->
                            <tr class="default-value-row" style="display: none;">
                                <th scope="row"><label>Valor por Defecto</label></th>
                                <td>
                                    <input type="text" name="fields[${fieldIndex}][default_value]" value="" class="regular-text" />
                                    <p class="description">Valor que tendrá el campo oculto por defecto</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label>Opciones (para select, radio, checkbox)</label></th>
                                <td>
                                    <textarea name="fields[${fieldIndex}][options]" rows="3" cols="50" class="large-text"></textarea>
                                    <p class="description">Una opción por línea</p>
                                </td>
                            </tr>
                            
                            <!-- Configuraciones específicas para campos de archivo -->
                            <tr class="file-config-row" style="display: none;">
                                <th scope="row"><label>Configuración de Archivo</label></th>
                                <td>
                                    <div class="file-config-container">
                                        <!-- Formatos permitidos -->
                                        <div class="file-config-item">
                                            <label><strong>Formatos Permitidos:</strong></label><br>
                                            <input type="text" name="fields[${fieldIndex}][allowed_formats]" 
                                                   value="" class="regular-text" placeholder="mp3,wav,m4a" />
                                            <p class="description">Formatos permitidos separados por comas</p>
                                        </div>
                                        
                                        <!-- Tamaño máximo -->
                                        <div class="file-config-item">
                                            <label><strong>Tamaño Máximo (MB):</strong></label><br>
                                            <input type="number" name="fields[${fieldIndex}][max_size_mb]" 
                                                   value="" class="small-text" min="1" max="100" placeholder="10" />
                                            <p class="description">Tamaño máximo en MB</p>
                                        </div>
                                        
                                        <!-- Duración máxima (solo para audio/video) -->
                                        <div class="file-config-item duration-config" style="display: none;">
                                            <label><strong>Duración Máxima (segundos):</strong></label><br>
                                            <input type="number" name="fields[${fieldIndex}][max_duration]" 
                                                   value="" class="small-text" min="1" max="3600" placeholder="300" />
                                            <p class="description">Duración máxima en segundos</p>
                                        </div>
                                        
                                        <!-- Permitir grabación (solo para audio) -->
                                        <div class="file-config-item recording-config" style="display: none;">
                                            <label>
                                                <input type="hidden" name="fields[${fieldIndex}][allow_recording]" value="0" />
                                                <input type="checkbox" name="fields[${fieldIndex}][allow_recording]" value="1" checked />
                                                <strong>Permitir grabación desde el dispositivo</strong>
                                            </label>
                                            <p class="description">Permite grabar audio directamente desde el micrófono del dispositivo</p>
                                        </div>
                                        
                                        <div class="file-config-item upload-config" style="display: none;">
                                            <label>
                                                <input type="hidden" name="fields[${fieldIndex}][allow_file_upload]" value="0" />
                                                <input type="checkbox" name="fields[${fieldIndex}][allow_file_upload]" value="1" checked />
                                                <strong>Permitir subir archivo de audio</strong>
                                            </label>
                                            <p class="description">Permite subir un archivo de audio existente desde el dispositivo</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                `;
                
                $('#fields-list').append(fieldHtml);
                fieldIndex++;
            });
            
            $(document).on('click', '.remove-field', function() {
                $(this).closest('.field-item').remove();
            });
            
            // Manejar cambio de tipo de campo
            $(document).on('change', '.field-type-select', function() {
                var $row = $(this).closest('.field-item');
                var $fileConfigRow = $row.find('.file-config-row');
                var $defaultValueRow = $row.find('.default-value-row');
                var $durationConfig = $row.find('.duration-config');
                var $recordingConfig = $row.find('.recording-config');
                var fieldType = $(this).val();
                
                // Mostrar/ocultar campo de valor por defecto para campos ocultos
                if (fieldType === 'hidden') {
                    $defaultValueRow.show();
                } else {
                    $defaultValueRow.hide();
                }
                
                // Mostrar/ocultar configuración de archivo
                if (['audio', 'video', 'image', 'file'].includes(fieldType)) {
                    $fileConfigRow.show();
                    
                    // Mostrar/ocultar configuración de duración
                    if (['audio', 'video'].includes(fieldType)) {
                        $durationConfig.show();
                    } else {
                        $durationConfig.hide();
                    }
                    
                    // Mostrar/ocultar configuración de grabación y subida
                    if (fieldType === 'audio') {
                        $recordingConfig.show();
                        $row.find('.upload-config').show();
                    } else {
                        $recordingConfig.hide();
                        $row.find('.upload-config').hide();
                    }
                    
                    // Actualizar placeholders según el tipo
                    var $allowedFormats = $row.find('input[name*="[allowed_formats]"]');
                    var $maxSize = $row.find('input[name*="[max_size_mb]"]');
                    var $maxDuration = $row.find('input[name*="[max_duration]"]');
                    
                    switch(fieldType) {
                        case 'audio':
                            $allowedFormats.attr('placeholder', 'mp3,wav,m4a');
                            $maxSize.attr('placeholder', '10');
                            $maxDuration.attr('placeholder', '300');
                            break;
                        case 'video':
                            $allowedFormats.attr('placeholder', 'mp4,avi,mov');
                            $maxSize.attr('placeholder', '50');
                            $maxDuration.attr('placeholder', '600');
                            break;
                        case 'image':
                            $allowedFormats.attr('placeholder', 'jpg,jpeg,png,gif');
                            $maxSize.attr('placeholder', '5');
                            break;
                        case 'file':
                            $allowedFormats.attr('placeholder', 'pdf,doc,docx,txt');
                            $maxSize.attr('placeholder', '10');
                            break;
                    }
                } else {
                    $fileConfigRow.hide();
                }
            });
        });
        </script>

        <style>
        .field-item {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
        }
        .field-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .field-header h4 {
            margin: 0;
        }
        
        .file-config-container {
            background: #fff;
            border: 1px solid #e1e1e1;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .file-config-item {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .file-config-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .file-config-item label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .file-config-item input[type="text"],
        .file-config-item input[type="number"] {
            width: 100%;
            max-width: 300px;
        }
        </style>
        <?php
    }

    /**
     * Guardar meta datos del formulario
     */
    public function save_form_meta($post_id)
    {
        // Solo procesar nuestro CPT
        if (get_post_type($post_id) !== 'xpsocial_form') {
            return;
        }
        
        if (!isset($_POST['xpsocial_form_meta_nonce']) || 
            !wp_verify_nonce($_POST['xpsocial_form_meta_nonce'], 'xpsocial_form_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Guardar configuración básica
        $fields_to_save = array(
            '_xpsocial_source',
            '_xpsocial_success_html',
            '_xpsocial_redirect_url',
            '_xpsocial_fifco_enabled',
            '_xpsocial_marca',
            '_xpsocial_country',
            '_xpsocial_show_terms',
            '_xpsocial_show_privacy',
            '_xpsocial_validate_email',
            '_xpsocial_validate_id_number',
            '_xpsocial_validate_age',
            '_xpsocial_min_age'
        );

        foreach ($fields_to_save as $field) {
            $post_key = str_replace('_xpsocial_', 'xpsocial_', $field);
            if (isset($_POST[$post_key])) {
                // Permitir HTML en el campo success_html
                if ($field === '_xpsocial_success_html') {
                    // Permitir etiquetas HTML comunes para mensajes de éxito
                    $allowed_html = array(
                        'h1' => array(),
                        'h2' => array(),
                        'h3' => array(),
                        'h4' => array(),
                        'h5' => array(),
                        'h6' => array(),
                        'p' => array(),
                        'div' => array('class' => array(), 'id' => array()),
                        'span' => array('class' => array(), 'id' => array()),
                        'strong' => array(),
                        'em' => array(),
                        'b' => array(),
                        'i' => array(),
                        'u' => array(),
                        'br' => array(),
                        'a' => array('href' => array(), 'target' => array(), 'class' => array()),
                        'img' => array('src' => array(), 'alt' => array(), 'class' => array(), 'width' => array(), 'height' => array()),
                        'ul' => array('class' => array()),
                        'ol' => array('class' => array()),
                        'li' => array(),
                        'blockquote' => array('class' => array()),
                        'small' => array(),
                        'mark' => array(),
                        'del' => array(),
                        'ins' => array(),
                        'sub' => array(),
                        'sup' => array()
                    );
                    $value = wp_kses($_POST[$post_key], $allowed_html);
                } else {
                    $value = sanitize_text_field($_POST[$post_key]);
                }
                update_post_meta($post_id, $field, $value);
            } else {
                // Para checkboxes, si no están presentes, significa que están desmarcados
                if (in_array($field, ['_xpsocial_fifco_enabled', '_xpsocial_show_terms', '_xpsocial_show_privacy', '_xpsocial_validate_email', '_xpsocial_validate_id_number', '_xpsocial_validate_age'])) {
                    update_post_meta($post_id, $field, '0');
                } else {
                    delete_post_meta($post_id, $field);
                }
            }
        }

        // Guardar campos dinámicos
        if (isset($_POST['fields']) && is_array($_POST['fields'])) {
            $fields = array();
            foreach ($_POST['fields'] as $field) {
                if (!empty($field['label']) && !empty($field['name'])) {
                    // Properly handle UTF-8 encoding for dynamic field labels and options
                    $label = html_entity_decode($field['label'], ENT_QUOTES, 'UTF-8');
                    $options_raw = isset($field['options']) ? html_entity_decode($field['options'], ENT_QUOTES, 'UTF-8') : '';
                    $placeholder = isset($field['placeholder']) ? html_entity_decode($field['placeholder'], ENT_QUOTES, 'UTF-8') : '';

                    // Normalize options to array for select/radio/checkbox
                    $options_array = array();
                    if (!empty($options_raw)) {
                        $lines = preg_split("/\r\n|\r|\n/", $options_raw);
                        foreach ($lines as $opt) {
                            $opt = trim($opt);
                            if ($opt !== '') {
                                $options_array[] = sanitize_text_field($opt);
                            }
                        }
                    }

                    $field_data = array(
                        'label' => sanitize_text_field($label),
                        'name' => sanitize_text_field($field['name']),
                        'type' => sanitize_text_field($field['type']),
                        'required' => isset($field['required']) ? '1' : '0',
                        'options' => in_array($field['type'], array('select','radio','checkbox'), true) ? $options_array : sanitize_textarea_field($options_raw),
                        'placeholder' => sanitize_text_field($placeholder)
                    );
                    
                    // Agregar valor por defecto para campos ocultos
                    if ($field['type'] === 'hidden' && isset($field['default_value'])) {
                        $field_data['default_value'] = sanitize_text_field($field['default_value']);
                    }
                    
                    // Agregar configuraciones específicas para campos de archivo
                    if (in_array($field['type'], ['audio', 'video', 'image', 'file'])) {
                        if (isset($field['allowed_formats'])) {
                            $field_data['allowed_formats'] = sanitize_text_field($field['allowed_formats']);
                        }
                        if (isset($field['max_size_mb'])) {
                            $field_data['max_size_mb'] = sanitize_text_field($field['max_size_mb']);
                        }
                        if (isset($field['max_duration'])) {
                            $field_data['max_duration'] = sanitize_text_field($field['max_duration']);
                        }
                        if (isset($field['allow_recording'])) {
                            $field_data['allow_recording'] = sanitize_text_field($field['allow_recording']);
                        }
                        if (isset($field['allow_file_upload'])) {
                            $field_data['allow_file_upload'] = sanitize_text_field($field['allow_file_upload']);
                        }
                    }
                    
                    $fields[] = $field_data;
                }
            }
            
            // Guardar como JSON string con codificación UTF-8
            update_post_meta($post_id, '_xpsocial_dynamic_fields', json_encode($fields, JSON_UNESCAPED_UNICODE));
        } else {
            delete_post_meta($post_id, '_xpsocial_dynamic_fields');
        }
    }

    /**
     * Agregar columnas personalizadas
     */
    public function custom_columns($columns)
    {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['source'] = 'Source';
        $new_columns['marca'] = 'Marca FIFCO';
        $new_columns['dynamic_fields'] = 'Campos Dinámicos';
        $new_columns['actions'] = 'Acciones';
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    /**
     * Contenido de columnas personalizadas
     */
    public function custom_column_content($column, $post_id)
    {
        switch ($column) {
            case 'source':
                $source = get_post_meta($post_id, '_xpsocial_source', true);
                echo $source ? esc_html($source) : 'No configurado';
                break;
                
            case 'marca':
                $marca = get_post_meta($post_id, '_xpsocial_marca', true);
                echo $marca ? esc_html($marca) : 'No configurado';
                break;
                
            case 'dynamic_fields':
                $dynamic_fields = get_post_meta($post_id, '_xpsocial_dynamic_fields', true);
                if ($dynamic_fields) {
                    $fields_data = is_string($dynamic_fields) ? json_decode($dynamic_fields, true) : $dynamic_fields;
                    $count = is_array($fields_data) ? count($fields_data) : 0;
                    echo $count . ' campos';
                } else {
                    echo '0 campos';
                }
                break;
                
            case 'actions':
                $delete_url = wp_nonce_url(
                    admin_url('post.php?post=' . $post_id . '&action=delete'),
                    'delete-post_' . $post_id
                );
                $edit_url = get_edit_post_link($post_id);
                
                echo '<div style="display: flex; gap: 5px;">';
                echo '<a href="' . esc_url($edit_url) . '" class="button button-small">Editar</a>';
                echo '<a href="' . esc_url($delete_url) . '" class="button button-small" onclick="return confirm(\'¿Estás seguro de que quieres eliminar este formulario? Esta acción no se puede deshacer.\');" style="color: #a00;">Eliminar</a>';
                echo '</div>';
                break;
        }
    }

    /**
     * Obtener configuración de formulario por source
     */
    public static function get_form_config_by_source($source)
    {
        $posts = get_posts(array(
            'post_type' => 'xpsocial_form',
            'meta_key' => '_xpsocial_source',
            'meta_value' => $source,
            'post_status' => 'publish',
            'numberposts' => 1
        ));

        if (empty($posts)) {
            return null;
        }

        $post = $posts[0];
        $config = array(
            'source' => $source,
            'success_html' => get_post_meta($post->ID, '_xpsocial_success_html', true),
            'redirect_url' => get_post_meta($post->ID, '_xpsocial_redirect_url', true),
            'fifco_enabled' => get_post_meta($post->ID, '_xpsocial_fifco_enabled', true),
            'marca' => get_post_meta($post->ID, '_xpsocial_marca', true),
            'country' => get_post_meta($post->ID, '_xpsocial_country', true),
            'show_terms' => get_post_meta($post->ID, '_xpsocial_show_terms', true),
            'show_privacy' => get_post_meta($post->ID, '_xpsocial_show_privacy', true),
            'validate_email' => get_post_meta($post->ID, '_xpsocial_validate_email', true),
            'validate_id_number' => get_post_meta($post->ID, '_xpsocial_validate_id_number', true),
            'validate_age' => get_post_meta($post->ID, '_xpsocial_validate_age', true),
            'min_age' => get_post_meta($post->ID, '_xpsocial_min_age', true) ?: 18,
            'dynamic_fields' => array()
        );

        // Obtener campos dinámicos
        $dynamic_fields = get_post_meta($post->ID, '_xpsocial_dynamic_fields', true);
        if ($dynamic_fields) {
            $config['dynamic_fields'] = is_string($dynamic_fields) ? json_decode($dynamic_fields, true) : $dynamic_fields;
            if (!is_array($config['dynamic_fields'])) {
                $config['dynamic_fields'] = array();
            }
        }

        return $config;
    }
}

/**
 * Shortcode para formulario dinámico con source
 */
function xpsocial_register_form_dynamic_shortcode($atts) {
    $atts = shortcode_atts(array(
        'source' => ''
    ), $atts);
    
    if (empty($atts['source'])) {
        return '<p>Error: Se requiere el atributo "source" en el shortcode.</p>';
    }
    
    // Get form configuration from CPT
    $form_config = Xpsocial_Forms_CPT::get_form_config_by_source($atts['source']);
    
    if (!$form_config) {
        return '<p>Error: No se encontró configuración para el source "' . esc_html($atts['source']) . '".</p>';
    }
    
    // Start output buffering
    ob_start();
    
    // Include the dynamic form template
    include plugin_dir_path(__FILE__) . 'social-login-btn.html';
    include plugin_dir_path(__FILE__) . 'register-form-dynamic.html';
    
    return ob_get_clean();
}

// Register the shortcode
add_shortcode('xpsocial_register_form', 'xpsocial_register_form_dynamic_shortcode');
