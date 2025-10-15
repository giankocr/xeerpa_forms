<?php
/**
 * Manager para la tabla wp_xpsocial_leads
 * Maneja el guardado y recuperación de leads desde formularios dinámicos
 */

if (!defined('ABSPATH')) {
    exit;
}

class Xpsocial_Leads_Manager
{
    private static $instance = null;
    private $table_name;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'xpsocial_leads';
    }

    /**
     * Guardar un lead en la tabla
     *
     * @param array $data Datos del lead
     * @return int|false ID del lead insertado o false en caso de error
     */
    public function save_lead($data)
    {
        global $wpdb;

        // Validar datos requeridos
        if (empty($data['EmailAddress']) && empty($data['IDNumber'])) {
            error_log('XPSocial: Error - EmailAddress o IDNumber son requeridos');
            return false;
        }

        if (empty($data['Source'])) {
            error_log('XPSocial: Error - Source es requerido');
            return false;
        }

        // Preparar datos para inserción
        $lead_data = array(
            'Marca' => sanitize_text_field($data['Marca'] ?? ''),
            'IDNumber' => sanitize_text_field($data['IDNumber'] ?? ''),
            'EmailAddress' => sanitize_email($data['EmailAddress'] ?? ''),
            'FirstName' => sanitize_text_field($data['FirstName'] ?? ''),
            'SecondName' => sanitize_text_field($data['SecondName'] ?? ''),
            'LastName' => sanitize_text_field($data['LastName'] ?? ''),
            'SecondLastName' => sanitize_text_field($data['SecondLastName'] ?? ''),
            'Gender' => sanitize_text_field($data['Gender'] ?? ''),
            'BirthDate' => sanitize_text_field($data['BirthDate'] ?? ''),
            'MobileNumber' => sanitize_text_field($data['MobileNumber'] ?? ''),
            'Province' => sanitize_text_field($data['Province'] ?? ''),
            'Country' => sanitize_text_field($data['Country'] ?? ''),
            'UserRegisterSocial' => sanitize_text_field($data['UserRegisterSocial'] ?? ''),
            'CaptureDate' => sanitize_text_field($data['CaptureDate'] ?? current_time('Y-m-d H:i:s')),
            'ModifiedDate' => sanitize_text_field($data['ModifiedDate'] ?? current_time('Y-m-d H:i:s')),
            'PoliticasPrivacidad' => sanitize_text_field($data['PoliticasPrivacidad'] ?? ''),
            'AceptaComunicaciones' => sanitize_text_field($data['AceptaComunicaciones'] ?? ''),
            'Source' => sanitize_text_field($data['Source']),
            'snid' => sanitize_text_field($data['snid'] ?? ''),
            'it_token' => sanitize_text_field($data['it_token'] ?? ''),
            'id_token' => sanitize_text_field($data['id_token'] ?? ''),
            'dynamic_fields' => isset($data['dynamic_fields']) ? json_encode($data['dynamic_fields']) : null
        );

        // Intentar insertar
        $result = $wpdb->insert($this->table_name, $lead_data);

        if ($result === false) {
            error_log('XPSocial: Error al insertar lead - ' . $wpdb->last_error);
            return false;
        }

        $lead_id = $wpdb->insert_id;
        error_log('XPSocial: Lead guardado exitosamente con ID: ' . $lead_id);

        return $lead_id;
    }

    /**
     * Obtener un lead por ID
     *
     * @param int $lead_id ID del lead
     * @return object|null Objeto del lead o null si no existe
     */
    public function get_lead($lead_id)
    {
        global $wpdb;

        $lead = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $lead_id
        ));

        if ($lead && $lead->dynamic_fields) {
            $lead->dynamic_fields = json_decode($lead->dynamic_fields, true);
        }

        return $lead;
    }

    /**
     * Obtener leads por source
     *
     * @param string $source Source del formulario
     * @param int $limit Límite de resultados
     * @param int $offset Offset para paginación
     * @return array Array de leads
     */
    public function get_leads_by_source($source, $limit = 50, $offset = 0)
    {
        global $wpdb;

        $leads = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE Source = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $source,
            $limit,
            $offset
        ));

        // Decodificar campos dinámicos
        foreach ($leads as $lead) {
            if ($lead->dynamic_fields) {
                $lead->dynamic_fields = json_decode($lead->dynamic_fields, true);
            }
        }

        return $leads;
    }

    /**
     * Obtener leads por email
     *
     * @param string $email Email del lead
     * @return array Array de leads
     */
    public function get_leads_by_email($email)
    {
        global $wpdb;

        $leads = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE EmailAddress = %s ORDER BY created_at DESC",
            $email
        ));

        // Decodificar campos dinámicos
        foreach ($leads as $lead) {
            if ($lead->dynamic_fields) {
                $lead->dynamic_fields = json_decode($lead->dynamic_fields, true);
            }
        }

        return $leads;
    }

    /**
     * Obtener estadísticas de leads
     *
     * @param string $source Source específico (opcional)
     * @return array Estadísticas
     */
    public function get_leads_stats($source = null)
    {
        global $wpdb;

        $where_clause = $source ? $wpdb->prepare("WHERE Source = %s", $source) : '';
        
        $stats = array();

        // Total de leads
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} {$where_clause}");

        // Leads por source
        $stats['by_source'] = $wpdb->get_results("
            SELECT Source, COUNT(*) as count 
            FROM {$this->table_name} 
            GROUP BY Source 
            ORDER BY count DESC
        ");

        // Leads por marca
        $stats['by_marca'] = $wpdb->get_results("
            SELECT Marca, COUNT(*) as count 
            FROM {$this->table_name} 
            WHERE Marca IS NOT NULL AND Marca != ''
            GROUP BY Marca 
            ORDER BY count DESC
        ");

        // Leads por país
        $stats['by_country'] = $wpdb->get_results("
            SELECT Country, COUNT(*) as count 
            FROM {$this->table_name} 
            WHERE Country IS NOT NULL AND Country != ''
            GROUP BY Country 
            ORDER BY count DESC
        ");

        // Leads por fecha (últimos 30 días)
        $stats['recent'] = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$this->table_name} 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            {$where_clause}
        ");

        return $stats;
    }

    /**
     * Actualizar un lead existente
     *
     * @param int $lead_id ID del lead
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update_lead($lead_id, $data)
    {
        global $wpdb;

        // Preparar datos para actualización
        $update_data = array();
        $allowed_fields = array(
            'Marca', 'IDNumber', 'EmailAddress', 'FirstName', 'SecondName',
            'LastName', 'SecondLastName', 'Gender', 'BirthDate', 'MobileNumber',
            'Province', 'Country', 'UserRegisterSocial', 'PoliticasPrivacidad',
            'AceptaComunicaciones', 'snid', 'it_token', 'id_token', 'dynamic_fields'
        );

        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if ($field === 'dynamic_fields' && is_array($data[$field])) {
                    $update_data[$field] = json_encode($data[$field]);
                } else {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                }
            }
        }

        $update_data['ModifiedDate'] = current_time('Y-m-d H:i:s');

        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $lead_id),
            array_fill(0, count($update_data), '%s'),
            array('%d')
        );

        if ($result === false) {
            error_log('XPSocial: Error al actualizar lead - ' . $wpdb->last_error);
            return false;
        }

        error_log('XPSocial: Lead actualizado exitosamente con ID: ' . $lead_id);
        return true;
    }

    /**
     * Eliminar un lead
     *
     * @param int $lead_id ID del lead
     * @return bool True si se eliminó correctamente
     */
    public function delete_lead($lead_id)
    {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $lead_id),
            array('%d')
        );

        if ($result === false) {
            error_log('XPSocial: Error al eliminar lead - ' . $wpdb->last_error);
            return false;
        }

        error_log('XPSocial: Lead eliminado exitosamente con ID: ' . $lead_id);
        return true;
    }

    /**
     * Verificar si existe un lead con el mismo email y source
     *
     * @param string $email Email del lead
     * @param string $source Source del formulario
     * @return bool True si existe
     */
    public function lead_exists($email, $source)
    {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE EmailAddress = %s AND Source = %s",
            $email,
            $source
        ));

        return $count > 0;
    }

    /**
     * Obtener el nombre de la tabla
     *
     * @return string Nombre de la tabla
     */
    public function get_table_name()
    {
        return $this->table_name;
    }
}
