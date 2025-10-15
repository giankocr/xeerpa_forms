<?php

/**
 * Shortcode para formulario de registro.
 */

function registrar_formulario_shortcode()
{
    $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';
    ob_start();
 
        include 'social-login-btn.html';
        include 'register-form.html';

    return ob_get_clean();
}
 // add_shortcode('xpsocial_register_form', 'registrar_formulario_shortcode'); // Disabled - using dynamic shortcode instead
