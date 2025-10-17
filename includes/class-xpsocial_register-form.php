<?php

// Cargar wp-load.php desde el directorio raíz de WordPress
if (!defined('ABSPATH')) {
    // Intentar diferentes rutas para wp-load.php
    $possible_paths = array(
        $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
        dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php',
        dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php'
    );
    
    $wp_loaded = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        http_response_code(500);
        echo json_encode(array('success' => false, 'message' => 'WordPress not found'));
        exit;
    }
    
    // Debug: verificar que WordPress se cargó correctamente
    error_log('XPSocial Debug - WordPress loaded, ABSPATH: ' . (defined('ABSPATH') ? ABSPATH : 'NOT DEFINED'));
    error_log('XPSocial Debug - wp_verify_nonce function exists: ' . (function_exists('wp_verify_nonce') ? 'YES' : 'NO'));
}

// Include configuration and optimization systems
require_once plugin_dir_path(__FILE__) . 'class-xpsocial_config.php';
require_once plugin_dir_path(__FILE__) . 'class-xpsocial_cache.php';
require_once plugin_dir_path(__FILE__) . 'class-xpsocial_performance.php';
// Registrar el hook de AJAX para WordPress
add_action('wp_ajax_xpsocial_register_form', 'after_submission_xeerpa');
add_action('wp_ajax_nopriv_xpsocial_register_form', 'after_submission_xeerpa');

function after_submission_xeerpa()
{
    // Verificar nonce CSRF para seguridad
    $nonce = $_POST['register_form_nonce'] ?? '';
    
    if (empty($nonce) || !wp_verify_nonce($nonce, 'register_form_action')) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(array(
            'success' => false,
            'message' => 'Token de seguridad inválido. Por favor, recarga la página e intenta de nuevo.',
            'errors' => array('security' => 'Invalid security token')
        ));
        exit;
    }
    
    // Rate limiting básico - verificar que no se envíe demasiado rápido
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_limit_key = 'xpsocial_rate_limit_' . md5($user_ip);
    $last_submission = get_transient($rate_limit_key);
    
    if ($last_submission && (time() - $last_submission) < 30) { // 30 segundos entre envíos
        header('Content-Type: application/json');
        http_response_code(429);
        echo json_encode(array(
            'success' => false,
            'message' => 'Por favor, espera un momento antes de enviar otro formulario.',
            'errors' => array('rate_limit' => 'Too many requests')
        ));
        exit;
    }
    
    // Registrar el tiempo de este envío
    set_transient($rate_limit_key, time(), 300); // 5 minutos
    
    // Verificar tamaño total de la petición (protección contra ataques de tamaño)
    $max_post_size = ini_get('post_max_size');
    $max_upload_size = ini_get('upload_max_filesize');
    $content_length = $_SERVER['CONTENT_LENGTH'] ?? 0;
    
    if ($content_length > 0) {
        $max_bytes = min(
            wp_convert_hr_to_bytes($max_post_size),
            wp_convert_hr_to_bytes($max_upload_size),
            10 * 1024 * 1024 // 10MB máximo
        );
        
        if ($content_length > $max_bytes) {
            header('Content-Type: application/json');
            http_response_code(413);
            echo json_encode(array(
                'success' => false,
                'message' => 'El tamaño de los datos enviados es demasiado grande.',
                'errors' => array('size' => 'Request too large')
               ));
               exit;
           }
       }
    
    $obfKey2 = '0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_';
    $obfKey1 = '0fg14GHIJ789@ADFvwKLM2eh3NOPQz_RSYZabEcdi56jklmVWXnoTUpqBCrstuxy';
    if (isset($_POST[ 'field_email' ]) && !empty($_POST[ 'field_email' ])) {
        // Email existence check removed - no longer managing WordPress users
        date_default_timezone_set('America/Costa_Rica');
        
        // Get form source from POST data
        $form_source = sanitize_text_field($_POST['form_source'] ?? '');
        
        // Get form configuration from CPT if source is provided
        $form_config = null;
        if (!empty($form_source)) {
            $form_config = Xpsocial_Forms_CPT::get_form_config_by_source($form_source);
        }
        
        // Sanitize and validate input data first (fast operations)
        $it = sanitize_text_field($_POST[ 'field_it' ]);
        $desobfuscatedToken = strtr($it, $obfKey1, $obfKey2);
        $first_name = sanitize_text_field($_POST[ 'field_firstname' ]);
        $last_name = sanitize_text_field($_POST[ 'field_lastname' ]);
        $email = sanitize_email($_POST[ 'field_email' ]);
        
        // Validación adicional del email
        if (!is_email($email) || empty($email)) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(array(
                'success' => false,
                'message' => 'Email inválido.',
                'errors' => array('field_email' => 'Email inválido')
            ));
            exit;
        }
        // Map gender to full values
        $gender_raw = sanitize_text_field($_POST[ 'field_gender' ]);
        $genero = '';
        switch ($gender_raw) {
            case 'm':
                $genero = 'Masculino';
                break;
            case 'f':
                $genero = 'Femenino';
                break;
            case 'nb':
                $genero = 'No Binario';
                break;
            default:
                $genero = $gender_raw; // fallback to original value
        }
        
        $birthday = sanitize_text_field($_POST[ 'field_birthday' ]);
        $phone = sanitize_text_field($_POST[ 'field_countryPhoneCode' ]) . sanitize_text_field($_POST[ 'field_phone' ]);
        $IDCedula = sanitize_text_field($_POST[ 'field_id' ]);
        
        // Get country name instead of ID
        $country_id = sanitize_text_field($_POST[ 'field_country' ]);
        $country = $country_id; // Will be updated with actual country name below
        
        $provincia = sanitize_text_field($_POST[ 'field_province' ]);
        $snid = sanitize_text_field($_POST[ 'field_snid' ]);
        $sn = sanitize_text_field($_POST[ 'field_sn' ]) ?: 'FM'; // Default to 'FM' if not provided
        $password = isset($_POST[ 'field_password' ]) ? sanitize_text_field($_POST[ 'field_password' ]) : $it;

        // Prepare boolean values - check means "Sí"
        $terms_checked = isset($_POST[ 'field_terms' ]) && $_POST[ 'field_terms' ] === 'si';
        $privacy_checked = isset($_POST[ 'field_privacy' ]) && $_POST[ 'field_privacy' ] === 'si';
        
        // For database storage (Sí/No)
        $robinson_db = $terms_checked ? 'Sí' : 'No';
        $politicaprivacidad_db = $privacy_checked ? 'Sí' : 'No';
        
        // For external system (true/false)
        $robinson = $terms_checked ? 'false' : 'true';
        $politicaprivacidad = $privacy_checked ? 'true' : 'false';
        
        // Get dynamic fields data
        $dynamic_fields_data = array();
        if ($form_config && !empty($form_config['dynamic_fields'])) {
            foreach ($form_config['dynamic_fields'] as $field) {
                $field_name = 'dynamic_' . $field['name'];
                
                if ($field['type'] === 'audio' || $field['type'] === 'image') {
                    // Handle file uploads
                    if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
                        $uploaded_file = handle_file_upload($_FILES[$field_name], $field['type'], $field['name']);
                        if ($uploaded_file) {
                            $dynamic_fields_data[$field['name']] = $uploaded_file;
                        }
                    }
                } elseif (isset($_POST[$field_name])) {
                    if ($field['type'] === 'checkbox') {
                        // For checkboxes, we get an array
                        $values = is_array($_POST[$field_name]) ? $_POST[$field_name] : array($_POST[$field_name]);
                        $sanitized_values = array();
                        foreach ($values as $value) {
                            $sanitized_values[] = sanitize_text_field(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
                        }
                        $dynamic_fields_data[$field['name']] = implode(', ', $sanitized_values);
                    } else {
                        // Properly handle UTF-8 encoding for dynamic fields
                        $value = $_POST[$field_name];
                        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                        $value = sanitize_text_field($value);
                        $dynamic_fields_data[$field['name']] = $value;
                    }
                }
            }
        }

        // User creation functionality removed - no longer creating WordPress users

        // Get country data with caching (fast operation)
        $cache = XPSocial_Cache::get_instance();
        $country_data = $cache->get_cached_country_data($country_id);
        
        // Safely get country name with proper error checking
        $country_name = $country_id; // Default to original country ID
        if (!is_wp_error($country_data) && 
            isset($country_data->data) && 
            is_array($country_data->data) && 
            !empty($country_data->data) && 
            isset($country_data->data[0]['name'])) {
            $country_name = $country_data->data[0]['name'];
        } else {
            // Log error for debugging but continue with registration
            if (is_wp_error($country_data)) {
                error_log("XPSocial: Country data error - " . $country_data->get_error_message());
            } else {
                error_log("XPSocial: Invalid country data structure for country ID: " . $country_id);
            }
        }

        // User metadata and login functionality removed - no longer managing WordPress users

        // Save lead to database using the leads manager FIRST
        if (class_exists('Xpsocial_Leads_Manager')) {
            $leads_manager = Xpsocial_Leads_Manager::get_instance();
            
            // Prepare lead data
            $lead_data = array(
                'FirstName' => $first_name,
                'LastName' => $last_name,
                'EmailAddress' => $email,
                'IDNumber' => $IDCedula,
                'Gender' => $genero, // Now contains "Masculino", "Femenino", "No Binario"
                'BirthDate' => $birthday,
                'MobileNumber' => $phone,
                'Province' => $provincia,
                'Country' => $country_name, // Now contains the actual country name
                'UserRegisterSocial' => $sn,
                'CaptureDate' => current_time('Y-m-d H:i:s'),
                'ModifiedDate' => current_time('Y-m-d H:i:s'),
                'PoliticasPrivacidad' => $politicaprivacidad_db, // "Sí" or "No"
                'AceptaComunicaciones' => $robinson_db, // "Sí" or "No"
                'snid' => $snid,
                'it_token' => $it,
                'id_token' => $desobfuscatedToken,
                'dynamic_fields' => $dynamic_fields_data
            );
            
            // Add form configuration values if available
            if ($form_config) {
                $lead_data['Source'] = $form_config['source'];
                $lead_data['Marca'] = $form_config['marca'] ?? '';
                if (!empty($form_config['country'])) {
                    $lead_data['Country'] = $form_config['country'];
                }
                // Add form config for dynamic fields processing
                $lead_data['form_config'] = $form_config;
            } else {
                // Fallback values if no form config
                $lead_data['Source'] = $form_source ?: 'default_form';
                $lead_data['Marca'] = 'Default';
            }
            
            // Validate lead data before saving (pass form config for validation settings)
            $validation_errors = $leads_manager->validate_lead_data($lead_data, $form_config);
            
            if (!empty($validation_errors)) {
                // Return validation errors as JSON response
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(array(
                    'success' => false,
                    'errors' => $validation_errors,
                    'message' => 'Errores de validación encontrados'
                ));
                exit;
            }
            
            // Save the lead
            $lead_id = $leads_manager->save_lead($lead_data);
            
            if ($lead_id) {
                error_log("XPSocial: Lead saved successfully with ID: " . $lead_id);
            } else {
                error_log("XPSocial: Failed to save lead");
            }
        }

        // Verificar si hay redirect configurado para este formulario específico
        $redirect_url = '';
        if ($form_config && !empty($form_config['redirect_url'])) {
            $redirect_url = $form_config['redirect_url'];
        }
        
        if (!empty($redirect_url)) {
            // Validar que la URL de redirect sea segura
            $validated_redirect_url = esc_url_raw($redirect_url);
            if (wp_http_validate_url($validated_redirect_url)) {
                // Return JSON response with redirect URL
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Registro completado exitosamente',
                    'redirect' => $validated_redirect_url
                ));
            } else {
                // URL inválida, usar fallback
                header('Content-Type: application/json');
                http_response_code(200);
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Registro completado exitosamente',
                    'success_html' => '<div class="xpsocial-success-message"><h3>¡Registro exitoso!</h3><p>Gracias por registrarte. Tu información ha sido guardada correctamente.</p></div>'
                ));
            }
        } else {
            // No hay redirect configurado, mostrar mensaje de éxito HTML
            $success_html = '';
            if ($form_config && !empty($form_config['success_html'])) {
                $success_html = $form_config['success_html'];
            } else {
                $success_html = '<div class="xpsocial-success-message"><h3>¡Registro exitoso!</h3><p>Gracias por registrarte. Tu información ha sido guardada correctamente.</p></div>';
            }
            
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(array(
                'success' => true,
                'message' => 'Registro completado exitosamente',
                'success_html' => $success_html
            ));
        }
        exit;

    }
}

// Background processing function for API calls
function process_background_api_calls($user_id, $user_data) {
    try {
        // Update WordPress native user fields if not already updated

        // Prepare data for API calls
        $fecha_datetime = new DateTime($user_data['birthday']);
        $fecha_formateada = $fecha_datetime->format('d/m/y');
        
        $genero = strtolower($user_data['genero']);
        $field_gender = '';
        switch ($genero) {
            case 'f': $field_gender = 'Femenino'; break;
            case 'm': $field_gender = 'Masculino'; break;
            case 'nb': $field_gender = 'No Binario'; break;
            default: $field_gender = 'No especificado'; break;
        }

        // 1. Send data to FIFCO (optimized with shorter timeout)
        send_data_to_fifco_optimized($user_data);

        // 2. Process Xeerpa calls based on conditions
        if (!empty($user_data['it'])) {
            // Call SaveData endpoint
            $params = '&email=' . $user_data['email'] .
                     '&first_name=' . $user_data['first_name'] .
                     '&last_name=' . $user_data['last_name'] .
                     '&birthday=' . $user_data['birthday'] .
                     '&phone=' . $user_data['phone'] .
                     '&genero=' . $user_data['genero'] .
                     '&IDCedula=' . $user_data['IDCedula'] .
                     '&id=' . $user_data['email'] .
                     '&provincia=' . $user_data['provincia'] .
                     '&robinson=' . $user_data['robinson'] .
                     '&pp=' . $user_data['politicaprivacidad'] .
                     '&sn=' . $user_data['sn'] .
                     '&it=' . $user_data['it'] .
                     '&idcrm=' . $user_data['email'];

            $urlSavedata = get_site_url() . '/wp-content/plugins/xpsocial_login/public/socialLoginSaveData.php?' . $params;
            var_dump($urlSavedata);
            exit;
            wp_remote_get($urlSavedata, XPSocial_Performance::get_optimized_request_args(array(
                'method' => 'GET',
                'blocking' => false, // Make it non-blocking
            )));
        } else {
            // Call DiscoverUser endpoint
            $urlDiscoveruser = get_option('xpsocial_urlForm');
            $clientId = get_option('xpsocial_clientId');
            $clientPwd = get_option('xpsocial_clientPwd');
            $appId = get_option('xpsocial_appId');
            $socialNetwork = 'FM';

            $dataArray = array(
                'email' => $user_data['email'],
                'first_name' => $user_data['first_name'],
                'last_name' => $user_data['last_name'],
                'birthday' => $user_data['birthday'],
                'location' => $user_data['provincia'],
                'IDCedula' => $user_data['IDCedula'],
                'phone' => $user_data['phone'],
                'gender' => $user_data['genero'],
                'robinson' => $user_data['robinson'],
            );

            $data = json_encode($dataArray);
            $dataDiscoveruser = $urlDiscoveruser . '?clientId=' . $clientId . '&clientPwd=' . $clientPwd . '&appId=' . $appId . '&socialNetwork=' . $socialNetwork . '&userId=' . $user_data['email'] . '&robinson=' . $user_data['robinson'] . '&data=' . $data . '&id=' . $user_data['email'];

            wp_remote_get($dataDiscoveruser, XPSocial_Performance::get_optimized_request_args(array(
                'method' => 'GET',
                'blocking' => false, // Non-blocking
            )));
        }

        // Google Sheets functionality removed - no longer sending data to Google

    } catch (Exception $e) {
        error_log("Background API processing error: " . $e->getMessage());
    }
}

// Hook for background processing
add_action('process_background_api_calls', 'process_background_api_calls', 10, 2);

function send_data_to_fifco_optimized($data) {
    $endpoint = get_option('fifco_api_url');
    $token = get_option('fifco_api_token');
    
    if (empty($endpoint) || !filter_var($endpoint, FILTER_VALIDATE_URL)) {
        error_log("Endpoint FIFCO inválido o no definido.");
        return array('status' => 0, 'response' => 'Invalid endpoint');
    }

    $genero = sanitize_text_field(strtolower($data['genero']));
    $field_gender = '';
    switch ($genero) {
        case 'f': $field_gender = 'Femenino'; break;
        case 'm': $field_gender = 'Masculino'; break;
        case 'nb': $field_gender = 'No Binario'; break;
        default: $field_gender = 'No especificado'; break;
    }

    // Get form configuration values
    $form_config = $data['form_config'] ?? null;
    $form_source = $data['form_source'] ?? '';
    
    // Determine values from form config or fallback to defaults
    $marca = 'Default';
    $source = 'default_form';
    $country = $data['country'] ?? 'Guatemala';
    
    if ($form_config) {
        $marca = $form_config['marca'] ?? 'Default';
        $source = $form_config['source'] ?? 'default_form';
        if (!empty($form_config['country'])) {
            $country = $form_config['country'];
        }
    } else if (!empty($form_source)) {
        $source = $form_source;
    }
    
    // Convert boolean values to Spanish
    $politicas_privacidad = $data['politicaprivacidad'] === 'true' ? 'Sí' : 'No';
    $acepta_comunicaciones = $data['robinson'] === 'false' ? 'Sí' : 'No';

    try {
        $all_fields =  array(
            "Marca" => $marca,
            "IDNumber" => $data['IDCedula'],
            "EmailAddress" => $data['email'],
            "FirstName" => $data['first_name'],
            "SecondName" => "",
            "LastName" => $data['last_name'],
            "SecondLastName" => "",
            "Gender" => $field_gender,
            "BirthDate" => (new DateTime($data['birthday']))->format('d/m/Y'),
            "MobileNumber" => $data['phone'],
            "Province" => $data['provincia'],
            "Country" => $country,
            "UserRegisterSocial" => $data['sn'],
            "CaptureDate" => date('d/m/Y'),
            "ModifiedDate" => date('d/m/Y'),
            "PoliticasPrivacidad" => $politicas_privacidad,
            "AceptaComunicaciones" => $acepta_comunicaciones,
            "Source" => $source
        );
        // 2. Prepara el arreglo que contendrá los objetos con "label" y "value".
        $formatted_fields = [];

        // 3. Recorre tus datos y dales el formato correcto.
        foreach ($all_fields as $label => $value) {
            $formatted_fields[] = [
                "label" => $label,
                "value" => $value
            ];
        }
        
        // Add dynamic fields if available
        if (isset($data['dynamic_fields']) && !empty($data['dynamic_fields'])) {
            foreach ($data['dynamic_fields'] as $field_name => $field_value) {
                $formatted_fields[] = [
                    "label" => ucfirst(str_replace('_', ' ', $field_name)),
                    "value" => $field_value
                ];
            }
        }

        // 4. Construye el payload final.
        $payload = [
            "fields" => $formatted_fields
        ];

        // 5. Codifica el payload a JSON.
        // Usamos JSON_PRETTY_PRINT para que sea más fácil de leer al depurar.
        $payloadJson = json_encode($payload, JSON_PRETTY_PRINT);

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => XPSocial_Performance::get_max_redirects(),
            CURLOPT_TIMEOUT => XPSocial_Performance::get_api_timeout(),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payloadJson,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic ' . $token,
            ),
        ));

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $curl_error = curl_error($ch);
            error_log("FIFCO API Error: $curl_error");
        }

        curl_close($ch);
        return array(
            'status' => $httpcode,
            'response' => $response
        );

    } catch (Exception $e) {
        error_log("Excepción al enviar a FIFCO: " . $e->getMessage());
        return array('status' => 0, 'response' => 'Exception: ' . $e->getMessage());
    }
}

function mi_plugin_log($mensaje) {
    $log_path = plugin_dir_path(__FILE__) . 'xpsocial.log';
    $fecha = date('Y-m-d H:i:s');
    file_put_contents($log_path, "[$fecha] $mensaje\n", FILE_APPEND);
}

function get_country_data($id)
{
    // Use centralized configuration
    $token = XPSocial_Config::get_license_token();
    $api_url = XPSocial_Config::get_api_base_url() . 'countries/selected';
    
    // Validate required parameters
    if (empty($token) || empty($api_url)) {
        return new WP_Error('config_error', 'Missing API configuration');
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([ 'countries_id' => $id ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json',
        'Referer' => $_SERVER[ 'HTTP_HOST' ]
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Add timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Add connection timeout

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return new WP_Error('api_error', 'Error al conectar con la API: ' . $error, array( 'status' => 500 ));
    }

    curl_close($ch);
    
    // Check HTTP response code
    if ($http_code !== 200) {
        return new WP_Error('api_error', 'API returned status code: ' . $http_code, array( 'status' => $http_code ));
    }
    
    if (empty($response)) {
        return new WP_Error('api_error', 'Empty response from API', array( 'status' => 500 ));
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('api_error', 'Error al decodificar la respuesta de la API: ' . json_last_error_msg(), array( 'status' => 500 ));
    }

    // Validate data structure
    if (!isset($data['data']) || !is_array($data['data'])) {
        return new WP_Error('data_error', 'Invalid data structure from API', array( 'status' => 500 ));
    }

    return rest_ensure_response($data);
}

/**
 * Handle file upload for dynamic fields
 * 
 * @param array $file The $_FILES array element
 * @param string $field_type The type of field (audio, image, document)
 * @param string $field_name The name of the field
 * @return string|false The uploaded file URL or false on failure
 */
function handle_file_upload($file, $field_type, $field_name) {
    // Create upload directory if it doesn't exist
    $upload_dir = wp_upload_dir();
    $social_login_dir = $upload_dir['basedir'] . '/social-login';
    
    if (!file_exists($social_login_dir)) {
        wp_mkdir_p($social_login_dir);
    }
    
    // Create subdirectories based on file type
    $subdir = '';
    switch ($field_type) {
        case 'audio':
            $subdir = '/audios';
            break;
        case 'image':
            $subdir = '/imagenes';
            break;
        default:
            $subdir = '/documentos';
            break;
    }
    
    $type_dir = $social_login_dir . $subdir;
    if (!file_exists($type_dir)) {
        wp_mkdir_p($type_dir);
    }
    
    // Validate file type
    $allowed_types = get_allowed_file_types($field_type);
    $file_type = wp_check_filetype($file['name'], $allowed_types);
    
    if (!$file_type['type']) {
        error_log('XPSocial: Invalid file type for ' . $field_name . ': ' . $file['name']);
        return false;
    }
    
    // Generate unique filename
    $filename = sanitize_file_name($file['name']);
    $filename = pathinfo($filename, PATHINFO_FILENAME);
    $extension = $file_type['ext'];
    $unique_filename = $filename . '_' . time() . '_' . wp_generate_password(8, false) . '.' . $extension;
    
    $file_path = $type_dir . '/' . $unique_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Generate URL
        $file_url = $upload_dir['baseurl'] . '/social-login' . $subdir . '/' . $unique_filename;
        
        // Log successful upload
        error_log('XPSocial: File uploaded successfully - ' . $field_name . ': ' . $file_url);
        
        return $file_url;
    } else {
        error_log('XPSocial: Failed to move uploaded file for ' . $field_name);
        return false;
    }
}

/**
 * Get allowed file types for different field types
 * 
 * @param string $field_type The type of field
 * @return array Allowed MIME types
 */
function get_allowed_file_types($field_type) {
    switch ($field_type) {
        case 'audio':
            return array(
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'ogg' => 'audio/ogg',
                'm4a' => 'audio/mp4',
                'aac' => 'audio/aac'
            );
        case 'image':
            return array(
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml'
            );
        default: // documents
            return array(
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
                'rtf' => 'application/rtf'
            );
    }
}
