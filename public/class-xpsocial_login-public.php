<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://gianko.com/
 * @since      1.0.0
 *
 * @package    Xpsocial_login
 * @subpackage Xpsocial_login/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Xpsocial_login
 * @subpackage Xpsocial_login/public
 * @author     Gianko <gian@gianko.com>
 */
class Xpsocial_login_Public
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Xpsocial_login_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Xpsocial_login_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/xpsocial_login-public.css', array(), $this->version, 'all');
        
        // Agregar CSS dinámico para el formulario dinámico
        $this->add_dynamic_form_styles();
    }

    /**
     * Add dynamic CSS styles for the dynamic form based on plugin settings
     *
     * @since    1.0.0
     */
    private function add_dynamic_form_styles()
    {
        // Solo agregar estilos si hay un formulario dinámico en la página
        if (!$this->has_dynamic_form()) {
            return;
        }

        $css = $this->generate_dynamic_form_css();
        
        if (!empty($css)) {
            wp_add_inline_style($this->plugin_name, $css);
        }
    }

    /**
     * Check if there's a dynamic form on the current page
     *
     * @since    1.0.0
     * @return   bool
     */
    private function has_dynamic_form()
    {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        // Check if the post content contains the dynamic form shortcode
        return has_shortcode($post->post_content, 'xpsocial_register_form');
    }

    /**
     * Generate dynamic CSS for the form based on plugin settings
     *
     * @since    1.0.0
     * @return   string
     */
    private function generate_dynamic_form_css()
    {
        // Read raw options
        $input_height_raw = get_option('xp_input_height', '40');
        $input_border_raw = get_option('xp_input_border', '1px solid #ced4da');
        $input_border_radius_raw = get_option('xp_input_border_radius', '6');
        $bg_color_raw = get_option('xp_bg_color', '#ffffff');
        $div_color_raw = get_option('xp_div_color', '#f8f9fa');
        $font_color_raw = get_option('xp_font_color', '#495057');
        $btn_height_raw = get_option('xp_btn_height', '40');
        $btn_width_raw = get_option('xp_btn_width', '100');
        $btn_color_raw = get_option('xp_btn_color', '#ffffff');
        $btn_bgcolor_raw = get_option('xp_btn_bgcolor', '#007cba');
        $btn_border_width_raw = get_option('xp_btn_border_width', '1');
        $btn_border_color_raw = get_option('xp_btn_border_color', '#007cba');
        $btn_border_radius_raw = get_option('xp_btn_border_radius', '6');

        // Normalize with safe defaults
        $input_height = (is_numeric($input_height_raw) && (int)$input_height_raw > 0) ? (int)$input_height_raw : 40;
        $input_border = (is_string($input_border_raw) && trim($input_border_raw) !== '') ? trim($input_border_raw) : '1px solid #ced4da';
        $input_border_radius = is_numeric($input_border_radius_raw) ? (int)$input_border_radius_raw : 6;
        $bg_color = (is_string($bg_color_raw) && trim($bg_color_raw) !== '') ? trim($bg_color_raw) : '#ffffff';
        $div_color = (is_string($div_color_raw) && trim($div_color_raw) !== '') ? trim($div_color_raw) : '#f8f9fa';
        $font_color = (is_string($font_color_raw) && trim($font_color_raw) !== '') ? trim($font_color_raw) : '#495057';
        $btn_height = (is_numeric($btn_height_raw) && (int)$btn_height_raw > 0) ? (int)$btn_height_raw : 40;
        $btn_width = (is_numeric($btn_width_raw) && (int)$btn_width_raw > 0 && (int)$btn_width_raw <= 100) ? (int)$btn_width_raw : 100;
        $btn_color = (is_string($btn_color_raw) && trim($btn_color_raw) !== '') ? trim($btn_color_raw) : '#ffffff';
        $btn_bgcolor = (is_string($btn_bgcolor_raw) && trim($btn_bgcolor_raw) !== '') ? trim($btn_bgcolor_raw) : '#007cba';
        $btn_border_width = is_numeric($btn_border_width_raw) ? (int)$btn_border_width_raw : 1;
        $btn_border_color = (is_string($btn_border_color_raw) && trim($btn_border_color_raw) !== '') ? trim($btn_border_color_raw) : '#007cba';
        $btn_border_radius = is_numeric($btn_border_radius_raw) ? (int)$btn_border_radius_raw : 6;

        $css = "
        /* Estilos dinámicos del formulario basados en configuración del plugin */

        .xpsocial-form input[type=\"text\"],
        .xpsocial-form input[type=\"email\"],
        .xpsocial-form input[type=\"tel\"],
        .xpsocial-form input[type=\"date\"],
        .xpsocial-form input[type=\"number\"],
        .xpsocial-form input[type=\"password\"],
        .xpsocial-form input[type=\"search\"],
        .xpsocial-form input[type=\"url\"],
        .xpsocial-form input[type=\"radio\"],
        .xpsocial-form input[type=\"hidden\"],
        .xpsocial-form select,
        .xpsocial-form textarea {
            height: {$input_height}px;
            border: {$input_border};
            border-radius: {$input_border_radius}px;
            background-color: {$bg_color};
            color: {$font_color};
        }

        .xpsocial-form .xp-boton-registro.xp_button {
            height: {$btn_height}px;
            width: {$btn_width}%;
            background-color: {$btn_bgcolor};
            color: {$btn_color};
            border: {$btn_border_width}px solid {$btn_border_color};
            border-radius: {$btn_border_radius}px;
        }

        .xpsocial-form label {
            color: {$font_color};
        }

        .xpsocial-form a {
            color: {$btn_bgcolor};
        }
        ";

        return $css;
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Xpsocial_login_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Xpsocial_login_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/xpsocial_login-public.js', array( 'jquery' ), $this->version, true);
        wp_enqueue_script($this->plugin_name . '-forms', plugin_dir_url(__FILE__) . 'js/xpsocial_forms.js', array( 'jquery' ), $this->version, true);
        wp_enqueue_script($this->plugin_name . '-validation', plugin_dir_url(__FILE__) . 'js/xpsocial_validation.js', array( 'jquery' ), $this->version, true);
    }
}
