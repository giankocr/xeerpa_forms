<?php

function xp_plugin_validate_license($estado_plugin)
{
    // Token Bearer para la autenticación
    $token = sanitize_text_field(get_option('xpsocial_licencia'));
    $estado = sanitize_text_field($estado_plugin);
    // Construir la URL de la API externa con los parámetros
    $api_url = URLAPI . "plugin/$estado";

    // Preparar los datos a enviar
    $response = wp_remote_post($api_url, array(
        'method'    => 'GET',
        'headers'   => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
            'Referer' => $_SERVER['HTTP_HOST']
        )
    ));
    // Verificar si hay errores en la solicitud
    if (is_wp_error($response)) {
        wp_admin_notice('Contacta a Gianko para obtener una licencia.', [ 'type' => 'error' ]);
        return false;
    }

    // Obtener y procesar la respuesta
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);
    // Verificar si la respuesta indica que la licencia es válida
    if (isset($data->message)) {
        if ($data->message === "Plugin Activated") {
            wp_admin_notice('Plugin Activado', [ 'type' => 'success' ]);
            return true;
        } else {
            wp_admin_notice('Contacta a Gianko para obtener una licencia.', [ 'type' => 'error' ]);
        }
    } else {
        wp_admin_notice('Contacta a Gianko para obtener una licencia.', [ 'type' => 'error' ]);
    }
}
