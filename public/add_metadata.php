<?php

// Asegúrate de que este archivo se ejecute en el contexto de WordPress
if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}
$public_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
include($public_path);
// Registrar el endpoint de la API REST
add_action('rest_api_init', function () {
    register_rest_route('geo-api/v1', '/login_add_metadata_user_api', array(
        'methods' => 'POST',
        'callback' => 'login_metadata_user_api',
        'permission_callback' => '__return_true', // Permitir acceso público; ajusta según tus necesidades
    ));
});

function login_metadata_user_api(WP_REST_Request $request)
{
    // Verificar si el usuario está autenticado
    if (is_user_logged_in()) {
        // Obtener datos del cuerpo de la solicitud
        $meta_xeerpa_it = sanitize_text_field($request->get_param('meta_xeerpa_it'));

        $user_id = get_current_user_id();

        // Verificar si el usuario ya tiene el metadato
        if (!get_user_meta($user_id, 'meta_xeerpa_it', true)) {
            // Agregar el metadato
            $response = add_user_meta($user_id, 'meta_xeerpa_it', $meta_xeerpa_it);

            // Enviar una respuesta exitosa
            return new WP_REST_Response('Metadato del usuario actualizado con éxito', 200);
        } else {
            // Si el usuario ya tiene el metadato, enviar un error
            return new WP_REST_Response('El usuario ya tiene este metadato', 400);
        }
    } else {
        // Si el usuario no está autenticado, enviar un error
        return new WP_REST_Response('Solicitud no válida', 401);
    }
}
