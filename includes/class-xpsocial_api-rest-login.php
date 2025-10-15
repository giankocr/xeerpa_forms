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
    
    // Endpoint temporal para crear la tabla de leads
    register_rest_route('geo-api/v1', '/create-leads-table', array(
        'methods' => 'POST',
        'callback' => 'create_leads_table_endpoint',
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
    if (empty($token)) {
        error_log('XPSocial API Error: Token de licencia no configurado');
        return new WP_Error('missing_token', 'Token de licencia no configurado. Por favor configure la licencia en la configuración del plugin.', array('status' => 400));
    }
    
    if (empty($countries_id)) {
        error_log('XPSocial API Error: Países no configurados');
        return new WP_Error('missing_countries', 'No hay países configurados. Por favor seleccione al menos un país en la configuración del plugin.', array('status' => 400));
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
        $error_message = $response->get_error_message();
        error_log('XPSocial API Error - WP Error: ' . $error_message);
        return new WP_Error('api_error', 'Error al conectar con la API: ' . $error_message, array('status' => 500));
    }

    // Procesar respuesta
    $body = wp_remote_retrieve_body($response);
    
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $json_error = json_last_error_msg();
        error_log('XPSocial API Error - JSON Decode Error: ' . $json_error);
        return new WP_Error('api_error', 'Error al decodificar la respuesta de la API: ' . $json_error, array('status' => 500));
    }
    
    // Verificar si la respuesta tiene datos válidos - manejo más flexible
    $countries_data = null;
    
    if (isset($data['data']) && is_array($data['data'])) {
        // Estructura esperada: { "data": [...] }
        $countries_data = $data['data'];
    } elseif (is_array($data)) {
        // Estructura alternativa: [...] (array directo)
        $countries_data = $data;
    } elseif (isset($data['countries']) && is_array($data['countries'])) {
        // Estructura alternativa: { "countries": [...] }
        $countries_data = $data['countries'];
    } elseif (isset($data['results']) && is_array($data['results'])) {
        // Estructura alternativa: { "results": [...] }
        $countries_data = $data['results'];
    } else {
        // Devolver una respuesta vacía en lugar de error
        $empty_response = array('data' => array());
        return rest_ensure_response($empty_response);
    }
    
    // Reconstruir la respuesta con la estructura estándar
    $standard_response = array('data' => $countries_data);

    return rest_ensure_response($standard_response);
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
    if (empty($token)) {
        error_log('XPSocial States API Error: Token de licencia no configurado');
        return new WP_Error('missing_token', 'Token de licencia no configurado. Por favor configure la licencia en la configuración del plugin.', array('status' => 400));
    }
    
    if (empty($country_id)) {
        error_log('XPSocial States API Error: Country ID no proporcionado');
        return new WP_Error('missing_country_id', 'ID de país no proporcionado', array('status' => 400));
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
        $curl_error = curl_error($ch);
        curl_close($ch);
        error_log('XPSocial States API Error - cURL Error: ' . $curl_error);
        return new WP_Error('api_error', 'Error al conectar con la API: ' . $curl_error, array('status' => 500));
    }

    curl_close($ch);
    
    // Procesar respuesta
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $json_error = json_last_error_msg();
        error_log('XPSocial States API Error - JSON Decode Error: ' . $json_error);
        return new WP_Error('api_error', 'Error al decodificar la respuesta de la API: ' . $json_error, array('status' => 500));
    }
    
    // Verificar si la respuesta tiene datos válidos - manejo más flexible
    $states_data = null;
    
    if (isset($data['data']) && is_array($data['data'])) {
        // Estructura esperada: { "data": [...] }
        $states_data = $data['data'];
    } elseif (is_array($data)) {
        // Estructura alternativa: [...] (array directo)
        $states_data = $data;
    } elseif (isset($data['states']) && is_array($data['states'])) {
        // Estructura alternativa: { "states": [...] }
        $states_data = $data['states'];
    } elseif (isset($data['results']) && is_array($data['results'])) {
        // Estructura alternativa: { "results": [...] }
        $states_data = $data['results'];
    } else {
        // Devolver una respuesta vacía en lugar de error
        $empty_response = array('data' => array());
        return rest_ensure_response($empty_response);
    }
    
    // Reconstruir la respuesta con la estructura estándar
    $standard_response = array('data' => $states_data);

    return rest_ensure_response($standard_response);
}

/**
 * Endpoint temporal para crear la tabla de leads
 * 
 * @param WP_REST_Request $request Objeto de solicitud REST
 * @return WP_REST_Response Respuesta con el resultado de la creación
 */
function create_leads_table_endpoint(WP_REST_Request $request)
{
    $response = array(
        'success' => false,
        'message' => '',
        'table_exists_before' => false,
        'table_exists_after' => false
    );
    
    // Use the Leads Manager to handle table creation
    if (class_exists('Xpsocial_Leads_Manager')) {
        $leads_manager = Xpsocial_Leads_Manager::get_instance();
        
        // Check if table exists before
        global $wpdb;
        $table_name = $wpdb->prefix . 'xpsocial_leads';
        
        $is_sqlite = (class_exists('WP_SQLite_DB') && $wpdb instanceof WP_SQLite_DB) || 
                     (isset($wpdb->dbh) && $wpdb->dbh instanceof PDO);
        
        if ($is_sqlite) {
            $result = $wpdb->get_var("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table_name}'");
            $table_exists_before = ($result == $table_name);
        } else {
            $result = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            $table_exists_before = ($result == $table_name);
        }
        
        $response['table_exists_before'] = $table_exists_before;
        
        if ($table_exists_before) {
            $response['message'] = 'Table already exists';
            $response['success'] = true;
        } else {
            // Force create the table
            $success = $leads_manager->force_create_table();
            
            // Check if table exists after
            if ($is_sqlite) {
                $result = $wpdb->get_var("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table_name}'");
                $table_exists_after = ($result == $table_name);
            } else {
                $result = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
                $table_exists_after = ($result == $table_name);
            }
            
            $response['table_exists_after'] = $table_exists_after;
            $response['success'] = $success;
            $response['message'] = $success ? 'Table created successfully' : 'Failed to create table';
        }
    } else {
        $response['message'] = 'Xpsocial_Leads_Manager class not found';
    }
    
    return rest_ensure_response($response);
}
