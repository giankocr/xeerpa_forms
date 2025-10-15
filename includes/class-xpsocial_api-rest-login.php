<?php
/**
 * XPSocial Login - API REST Endpoints
 * 
 * Este archivo contiene los endpoints REST para la funcionalidad de países y estados
 * del plugin XPSocial Login.
 * 
 * @package    Xpsocial_login
 * @subpackage Xpsocial_login/includes
 * @author     Gianko <gian@gianko.com>
 * @since      1.0.0
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registrar endpoints REST para países y estados
 */
add_action('rest_api_init', function () {
    // Endpoint para obtener países seleccionados
    register_rest_route('geo-api/v1', '/selected-countries', array(
        'methods' => 'GET',
        'callback' => 'get_selected_countries',
        'permission_callback' => '__return_true'
    ));
    
    // Endpoint para obtener estados seleccionados
    register_rest_route('geo-api/v1', '/selected-states', array(
        'methods' => 'GET',
        'callback' => 'get_selected_states',
        'permission_callback' => '__return_true'
    ));
});

/**
 * Obtener países seleccionados desde la API externa
 * 
 * @param WP_REST_Request $request Objeto de solicitud REST
 * @return WP_REST_Response|WP_Error Respuesta con los países o error
 */
function get_selected_countries(WP_REST_Request $request)
{
    // Obtener configuración necesaria
    $token = sanitize_text_field(get_option('xpsocial_licencia'));
    $api_key = 'ZnRsOXNqaXczY3g5Y2hVZndtVHhPa2d2cTJSbVFlbUoyTVYzM0NHVQ==';
    $countries_id = get_option('xpsocial_countries');
    
    // Validar que tenemos los datos necesarios
    if (empty($token) || empty($countries_id)) {
        return new WP_Error('missing_config', 'Configuración incompleta', array('status' => 400));
    }
    
    // Preparar URL y datos
    $api_url = URLAPI . 'countries/selected';
    $countries_id = array_map('intval', $countries_id);
    $countries_id = implode(',', $countries_id);

    // Realizar petición a la API externa
    $response = wp_remote_post($api_url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'X-CSCAPI-KEY' => $api_key,
            'Referer' => $_SERVER['HTTP_HOST']
        ),
        'body' => json_encode(array('countries_id' => $countries_id)),
        'timeout' => 30
    ));

    // Verificar errores en la petición
    if (is_wp_error($response)) {
        return new WP_Error('api_error', 'Error al conectar con la API', array('status' => 500));
    }

    // Procesar respuesta
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('api_error', 'Error al decodificar la respuesta de la API', array('status' => 500));
    }

    return rest_ensure_response($data);
}

/**
 * Obtener estados seleccionados desde la API externa
 * 
 * @param WP_REST_Request $request Objeto de solicitud REST
 * @return WP_REST_Response|WP_Error Respuesta con los estados o error
 */
function get_selected_states(WP_REST_Request $request)
{
    // Obtener configuración necesaria
    $api_key = 'ZnRsOXNqaXczY3g5Y2hVZndtVHhPa2d2cTJSbVFlbUoyTVYzM0NHVQ==';
    $token = sanitize_text_field(get_option('xpsocial_licencia'));
    
    // Obtener parámetros de la solicitud
    $params = $request->get_params();
    $country_id = isset($params['country_id']) ? intval($params['country_id']) : 0;
    
    // Validar parámetros
    if (empty($token) || empty($country_id)) {
        return new WP_Error('missing_params', 'Parámetros incompletos', array('status' => 400));
    }
    
    // Preparar URL
    $api_url = URLAPI . 'states/selected';

    // Realizar petición usando cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['country_id' => $country_id]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'X-CSCAPI-KEY' => $api_key,
        'Referer: ' . $_SERVER['HTTP_HOST']
    ));

    $response = curl_exec($ch);
    
    // Verificar errores de cURL
    if (curl_errno($ch)) {
        curl_close($ch);
        return new WP_Error('api_error', 'Error al conectar con la API', array('status' => 500));
    }

    curl_close($ch);
    
    // Procesar respuesta
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('api_error', 'Error al decodificar la respuesta de la API', array('status' => 500));
    }

    return rest_ensure_response($data);
}