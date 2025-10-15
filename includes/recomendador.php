<?php
// Cargar wp-load.php desde el directorio raíz de WordPress
if (!defined('ABSPATH')) {
    $public_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
    include($public_path);
}
// Ve
function shortcode_recomendador_xeerpa()
{
    // User login functionality removed - recommendador disabled
    // This shortcode no longer works since user management was removed
    $text = '<div style="text-align: center; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
                <p><strong>Recomendador deshabilitado</strong></p>
                <p>Esta funcionalidad ha sido deshabilitada ya que requería gestión de usuarios.</p>
            </div>';
    return $text;
}

add_shortcode('recomendador_xeerpa', 'shortcode_recomendador_xeerpa');
