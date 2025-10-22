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
}

// Include configuration and optimization systems
require_once plugin_dir_path(__FILE__) . 'class-xpsocial_config.php';
require_once plugin_dir_path(__FILE__) . 'class-xpsocial_cache.php';
require_once plugin_dir_path(__FILE__) . 'class-xpsocial_performance.php';
require_once plugin_dir_path(__FILE__) . 'class-xpsocial_timezone.php';
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

        // Prepare boolean values - check means "Sí"
        $terms_checked = isset($_POST[ 'field_terms' ]) && $_POST[ 'field_terms' ] === 'si';
        $privacy_checked = isset($_POST[ 'field_privacy' ]) && $_POST[ 'field_privacy' ] === 'si';
        
        // For database storage (Sí/No)
        $robinson_db = $terms_checked ? 'Sí' : 'No';
        $politicaprivacidad_db = $privacy_checked ? 'Sí' : 'No';
        
        // For external system (true/false) - CORRECTED LOGIC
        if (isset($_POST[ 'field_terms' ]) && $_POST[ 'field_terms' ] === 'si') {
            $tcsavedata = 'false'; // User accepts communications
        } else {
            $tcsavedata = 'true'; // User does NOT accept communications
        }
        
        if (isset($_POST[ 'field_privacy' ]) && $_POST[ 'field_privacy' ] === 'si') {
            $ppsavedata = 'true'; // User accepts privacy policy
        } else {
            $ppsavedata = 'false'; // User does NOT accept privacy policy
        }
        
        // Validate uploaded files first
        $file_validation_errors = array();
        if (!empty($_FILES)) {
            $file_validation_errors = validate_uploaded_files($form_config, $_FILES);
        }
        
        // If there are file validation errors, return them
        if (!empty($file_validation_errors)) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(array(
                'success' => false,
                'message' => 'Errores de validación en archivos subidos',
                'errors' => $file_validation_errors
            ));
            exit;
        }
        
        // Get dynamic fields data
        $dynamic_fields_data = array();
        
        // Log all POST/FILES data for debugging (focus on dynamic fields)
        $post_snapshot = $_POST;
        $files_snapshot = $_FILES;
        // Evitar logs enormes de binarios o campos sensibles
        foreach ($post_snapshot as $k => $v) {
            if (is_string($v) && strlen($v) > 2000) {
                $post_snapshot[$k] = substr($v, 0, 2000) . '...<truncated>';
            }
        }
        
        if ($form_config && !empty($form_config['dynamic_fields'])) {
            foreach ($form_config['dynamic_fields'] as $field) {
                $field_name = 'dynamic_' . $field['name'];
                
                if ($field['type'] === 'audio' || $field['type'] === 'image' || $field['type'] === 'video' || $field['type'] === 'file') {
                    // Handle file uploads
                    if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
                        $uploaded_file = handle_file_upload($_FILES[$field_name], $field['type'], $field['name']);
                        if ($uploaded_file) {
                            $dynamic_fields_data[$field['name']] = $uploaded_file;
                        }
                    } elseif (isset($_POST[$field_name . '_filename']) && !empty($_POST[$field_name . '_filename'])) {
                        // Handle audio files saved via hidden input (from independent recorder)
                        $filename = sanitize_text_field($_POST[$field_name . '_filename']);
                        
                        // Generate full URL for the audio file
                        $upload_dir = wp_upload_dir();
                        $audio_dir = $upload_dir['basedir'] . '/social-login/audios';
                        
                        // First try with the original filename
                        $audio_path = $audio_dir . '/' . $filename;
                        $audio_url = $upload_dir['baseurl'] . '/social-login/audios/' . $filename;
                        
                        
                        if (file_exists($audio_path)) {
                            $dynamic_fields_data[$field['name']] = $audio_url;
                        } else {
                            // If not found with original name, search for files with the same base name
                            $base_name = pathinfo($filename, PATHINFO_FILENAME);
                            $extension = pathinfo($filename, PATHINFO_EXTENSION);
                            
                            
                            $found_file = false;
                            if (is_dir($audio_dir)) {
                                $files = scandir($audio_dir);
                                foreach ($files as $file) {
                                    if (strpos($file, $base_name) !== false && pathinfo($file, PATHINFO_EXTENSION) === $extension) {
                                        $audio_path = $audio_dir . '/' . $file;
                                        $audio_url = $upload_dir['baseurl'] . '/social-login/audios/' . $file;
                                        
                                        if (file_exists($audio_path)) {
                                            $dynamic_fields_data[$field['name']] = $audio_url;
                                            $found_file = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            if (!$found_file) {
                                $dynamic_fields_data[$field['name']] = $filename;
                            }
                        }
                    } elseif (isset($_POST[$field_name]) && is_string($_POST[$field_name]) && preg_match('/^recording_.*\.(webm|wav|mp3|m4a|ogg)$/i', $_POST[$field_name])) {
                        // Fallback: algunos navegadores/formas pueden mandar el nombre en el campo principal
                        $filename = sanitize_text_field($_POST[$field_name]);
                        $upload_dir = wp_upload_dir();
                        $audio_path = $upload_dir['basedir'] . '/social-login/audios/' . $filename;
                        $audio_url = $upload_dir['baseurl'] . '/social-login/audios/' . $filename;
                        if (file_exists($audio_path)) {
                            $dynamic_fields_data[$field['name']] = $audio_url;
                        } else {
                            $dynamic_fields_data[$field['name']] = $filename;
                        }
                    } else {
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
        
        // Log dynamic fields data before saving

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
                'CaptureDate' => get_costa_rica_datetime('d/m/Y H:i:s'),
                'ModifiedDate' => get_costa_rica_datetime('d/m/Y H:i:s'),
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
                error_log('XPSocial: Validation errors returned: ' . print_r($validation_errors, true));
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
            error_log('XPSocial: Success response sent with HTML: ' . $success_html);
        }
         // Start background API calls asynchronously (non-blocking)
         $user_data_array = array(
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'birthday' => $birthday,
            'phone' => $phone,
            'genero' => $genero,
            'gender_raw' => $gender_raw, // Add raw gender value
            'IDCedula' => $IDCedula,
            'provincia' => $provincia,
            'robinson' => $tcsavedata,
            'politicaprivacidad' => $ppsavedata,
            'sn' => $sn,
            'it' => $it,
            'snid' => $snid
        );
        
        // Call background processing directly instead of using wp_schedule_single_event
        process_background_api_calls($user_data_array);
        error_log("XPSocial: Background API processing triggered for lead ID: " . $lead_id);
        exit;

    }
}

// Background processing function for API calls
function process_background_api_calls($user_data) {
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
                     '&genero=' . $user_data['gender_raw'] . // Use gender_raw instead of genero
                     '&IDCedula=' . $user_data['IDCedula'] .
                     '&id=' . $user_data['email'] .
                     '&provincia=' . $user_data['provincia'] .
                     '&robinson=' . $user_data['robinson'] .
                     '&pp=' . $user_data['politicaprivacidad'] .
                     '&sn=' . $user_data['sn'] .
                     '&it=' . $user_data['it'] .
                     '&idcrm=' . $user_data['email'];

            $urlSavedata = get_site_url() . '/wp-content/plugins/xpsocial_login/public/socialLoginSaveData.php?' . $params;
            
            // Log the request
            error_log("XPSocial SaveData Request: " . $urlSavedata);
            
            $response = wp_remote_get($urlSavedata, XPSocial_Performance::get_optimized_request_args(array(
                'method' => 'GET',
                'blocking' => false, // Make it non-blocking
            )));
            
            // Log response details
            if (is_wp_error($response)) {
                error_log("XPSocial SaveData Error: " . $response->get_error_message());
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
                error_log("XPSocial SaveData Response Code: " . $response_code);
                error_log("XPSocial SaveData Response Body: " . $response_body);
                
                // Store response in database for tracking
                if (function_exists('update_option')) {
                    $savedata_logs = get_option('xpsocial_savedata_logs', array());
                    $savedata_logs[] = array(
                        'timestamp' => current_time('mysql'),
                        'email' => $user_data['email'],
                        'url' => $urlSavedata,
                        'response_code' => $response_code,
                        'response_body' => $response_body,
                        'success' => ($response_code >= 200 && $response_code < 300)
                    );
                    
                    // Keep only last 100 logs
                    if (count($savedata_logs) > 100) {
                        $savedata_logs = array_slice($savedata_logs, -100);
                    }
                    
                    update_option('xpsocial_savedata_logs', $savedata_logs);
                }
            }
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

// Hook for background processing - removed since we call directly now

// AJAX handler para subir archivos de audio
add_action('wp_ajax_xpsocial_upload_audio', 'handle_audio_upload');
add_action('wp_ajax_nopriv_xpsocial_upload_audio', 'handle_audio_upload');

function handle_audio_upload() {
    // Verificar nonce
    if (!wp_verify_nonce($_POST['nonce'], 'xpsocial_ajax_nonce')) {
        wp_die('Security check failed');
    }
    
    // Verificar que hay un archivo
    if (!isset($_FILES['audio_file']) || $_FILES['audio_file']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error('No audio file received');
        return;
    }
    
    $file = $_FILES['audio_file'];
    $filename = sanitize_file_name($file['name']);
    
    // Crear directorio si no existe
    $upload_dir = wp_upload_dir();
    $audio_dir = $upload_dir['basedir'] . '/social-login/audios';
    
    if (!file_exists($audio_dir)) {
        wp_mkdir_p($audio_dir);
    }
    
    // Generar nombre único
    $unique_filename = wp_generate_password(12, false) . '_' . $filename;
    $file_path = $audio_dir . '/' . $unique_filename;
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $file_url = $upload_dir['baseurl'] . '/social-login/audios/' . $unique_filename;
        
        error_log('XPSocial: Audio file uploaded successfully - ' . $file_url);
        
        wp_send_json_success(array(
            'filename' => $unique_filename,
            'url' => $file_url,
            'path' => $file_path
        ));
    } else {
        error_log('XPSocial: Failed to upload audio file');
        wp_send_json_error('Failed to save audio file');
    }
}

/**
 * Get SaveData logs for monitoring
 * 
 * @param string $email Optional email to filter logs
 * @param int $limit Number of logs to return (default: 50)
 * @return array Array of SaveData logs
 */
function get_savedata_logs($email = '', $limit = 50) {
    $logs = get_option('xpsocial_savedata_logs', array());
    
    if (!empty($email)) {
        $logs = array_filter($logs, function($log) use ($email) {
            return $log['email'] === $email;
        });
    }
    
    // Sort by timestamp (newest first)
    usort($logs, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return array_slice($logs, 0, $limit);
}

/**
 * Get SaveData statistics
 * 
 * @return array Statistics about SaveData calls
 */
function get_savedata_stats() {
    $logs = get_option('xpsocial_savedata_logs', array());
    
    $total_calls = count($logs);
    $successful_calls = count(array_filter($logs, function($log) {
        return $log['success'];
    }));
    $failed_calls = $total_calls - $successful_calls;
    
    return array(
        'total_calls' => $total_calls,
        'successful_calls' => $successful_calls,
        'failed_calls' => $failed_calls,
        'success_rate' => $total_calls > 0 ? round(($successful_calls / $total_calls) * 100, 2) : 0
    );
}

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
            "CaptureDate" => get_costa_rica_datetime('d/m/Y H:i:s'),
            "ModifiedDate" => get_costa_rica_datetime('d/m/Y H:i:s'),
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
                'aac' => 'audio/aac',
                'webm' => 'audio/webm'
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

/**
 * Validar archivos subidos según la configuración del formulario
 */
function validate_uploaded_files($form_config, $uploaded_files) {
    $errors = array();
    
    if (empty($form_config) || empty($uploaded_files)) {
        return $errors;
    }
    
    // Crear un mapa de configuraciones por campo
    $field_configs = array();
    if (!empty($form_config['dynamic_fields'])) {
        foreach ($form_config['dynamic_fields'] as $field) {
            $field_name = 'dynamic_' . $field['name'];
            $field_configs[$field_name] = $field;
        }
    }
    
    foreach ($uploaded_files as $field_name => $file_info) {
        if (!isset($file_info['error']) || $file_info['error'] !== UPLOAD_ERR_OK) {
            continue; // Skip files with upload errors
        }
        
        // Obtener configuración específica del campo
        $field_config = isset($field_configs[$field_name]) ? $field_configs[$field_name] : null;
        
        if (!$field_config) {
            continue; // Skip if no field configuration found
        }
        
        $file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
        $file_size = $file_info['size'];
        $file_type = $file_info['type'];
        
        // Validar formato permitido
        $format_error = validate_file_format_by_field($file_extension, $field_config);
        if ($format_error) {
            $errors[$field_name] = $format_error;
            continue;
        }
        
        // Validar tamaño
        $size_error = validate_file_size_by_field($file_size, $field_config);
        if ($size_error) {
            $errors[$field_name] = $size_error;
            continue;
        }
        
        // Validación de duración eliminada por problemas de compatibilidad
    }
    
    return $errors;
}

/**
 * Determinar la categoría del archivo basado en extensión y tipo MIME
 */
function determine_file_category($extension, $mime_type) {
    $audio_extensions = ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'flac'];
    $video_extensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
    
    if (in_array($extension, $audio_extensions) || strpos($mime_type, 'audio/') === 0) {
        return 'audio';
    } elseif (in_array($extension, $video_extensions) || strpos($mime_type, 'video/') === 0) {
        return 'video';
    } elseif (in_array($extension, $image_extensions) || strpos($mime_type, 'image/') === 0) {
        return 'image';
    } else {
        return 'file';
    }
}

/**
 * Validar formato de archivo por campo
 */
function validate_file_format_by_field($extension, $field_config) {
    $allowed_formats = array();
    
    // Obtener formatos permitidos del campo
    if (!empty($field_config['allowed_formats'])) {
        $allowed_formats = explode(',', $field_config['allowed_formats']);
    } else {
        // Usar valores por defecto si no están configurados
        switch ($field_config['type']) {
            case 'audio':
                $allowed_formats = ['mp3', 'wav', 'm4a'];
                break;
            case 'video':
                $allowed_formats = ['mp4', 'avi', 'mov'];
                break;
            case 'image':
                $allowed_formats = ['jpg', 'jpeg', 'png', 'gif'];
                break;
            case 'file':
                $allowed_formats = ['pdf', 'doc', 'docx', 'txt'];
                break;
        }
    }
    
    // Limpiar espacios en blanco
    $allowed_formats = array_map('trim', $allowed_formats);
    
    if (!in_array($extension, $allowed_formats)) {
        return "Formato de archivo no permitido. Formatos permitidos: " . implode(', ', $allowed_formats);
    }
    
    return null;
}

/**
 * Validar tamaño de archivo por campo
 */
function validate_file_size_by_field($file_size, $field_config) {
    $max_size_mb = 0;
    
    // Obtener tamaño máximo del campo
    if (!empty($field_config['max_size_mb'])) {
        $max_size_mb = intval($field_config['max_size_mb']);
    } else {
        // Usar valores por defecto si no están configurados
        switch ($field_config['type']) {
            case 'image':
                $max_size_mb = 5;
                break;
            case 'video':
                $max_size_mb = 50;
                break;
            case 'audio':
            case 'file':
            default:
                $max_size_mb = 10;
                break;
        }
    }
    
    $max_size_bytes = $max_size_mb * 1024 * 1024;
    
    if ($file_size > $max_size_bytes) {
        $file_size_mb = round($file_size / 1024 / 1024, 2);
        return "El archivo es demasiado grande. Tamaño máximo permitido: {$max_size_mb}MB. Tamaño del archivo: {$file_size_mb}MB";
    }
    
    return null;
}

// Función de validación de duración eliminada por problemas de compatibilidad

// Función get_media_duration eliminada por problemas de compatibilidad

// Función get_mp3_duration eliminada por problemas de compatibilidad
