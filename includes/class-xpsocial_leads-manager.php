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
        
        // Only log constructor call once per session
        if (!isset($_SESSION['xpsocial_leads_manager_logged'])) {
            error_log('XPSocial: Leads Manager constructor called, table name: ' . $this->table_name);
            $_SESSION['xpsocial_leads_manager_logged'] = true;
        }
        
        // Check if table exists, if not create it
        $this->ensure_table_exists();
    }

    /**
     * Ensure the table exists, create it if it doesn't
     */
    private function ensure_table_exists()
    {
        global $wpdb;
        
        // Check if table exists - compatible with both MySQL and SQLite
        $table_exists = false;
        
        // Better detection of SQLite
        $is_sqlite = (class_exists('WP_SQLite_DB') && $wpdb instanceof WP_SQLite_DB) || 
                     (isset($wpdb->dbh) && $wpdb->dbh instanceof PDO);
        
        if ($is_sqlite) {
            // SQLite - use direct query without prepare for table name
            $result = $wpdb->get_var("SELECT name FROM sqlite_master WHERE type='table' AND name='{$this->table_name}'");
            $table_exists = ($result == $this->table_name);
            // Only log table check if table doesn't exist
            if (!$table_exists) {
                error_log('XPSocial: SQLite detected, table name: ' . $this->table_name . ', result: ' . ($result ?: 'null') . ', exists: ' . ($table_exists ? 'Yes' : 'No'));
            }
        } else {
            // MySQL
            $result = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $this->table_name));
            $table_exists = ($result == $this->table_name);
            // Only log table check if table doesn't exist
            if (!$table_exists) {
                error_log('XPSocial: MySQL detected, table name: ' . $this->table_name . ', result: ' . ($result ?: 'null') . ', exists: ' . ($table_exists ? 'Yes' : 'No'));
            }
        }
        
        if (!$table_exists) {
            error_log('XPSocial: Table does not exist, attempting to create it');
            $this->create_table();
        }
    }

    /**
     * Create the leads table
     */
    private function create_table()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Check if we're using SQLite or MySQL
        $is_sqlite = (class_exists('WP_SQLite_DB') && $wpdb instanceof WP_SQLite_DB) || 
                     (isset($wpdb->dbh) && $wpdb->dbh instanceof PDO);
        
        if ($is_sqlite) {
            // SQLite compatible SQL
            $sql = "CREATE TABLE {$this->table_name} (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                Marca TEXT,
                IDNumber TEXT,
                EmailAddress TEXT,
                FirstName TEXT,
                SecondName TEXT,
                LastName TEXT,
                SecondLastName TEXT,
                Gender TEXT,
                BirthDate TEXT,
                MobileNumber TEXT,
                Province TEXT,
                Country TEXT,
                UserRegisterSocial TEXT,
                CaptureDate TEXT,
                ModifiedDate TEXT,
                PoliticasPrivacidad TEXT,
                AceptaComunicaciones TEXT,
                Source TEXT,
                snid TEXT,
                it_token TEXT,
                id_token TEXT,
                dynamic_fields TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(EmailAddress, Source),
                UNIQUE(IDNumber, Source)
            );";
        } else {
            // MySQL compatible SQL
            $sql = "CREATE TABLE {$this->table_name} (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                `Marca` VARCHAR(120) NULL,
                `IDNumber` VARCHAR(100) NULL,
                `EmailAddress` VARCHAR(190) NULL,
                `FirstName` VARCHAR(100) NULL,
                `SecondName` VARCHAR(100) NULL,
                `LastName` VARCHAR(100) NULL,
                `SecondLastName` VARCHAR(100) NULL,
                `Gender` VARCHAR(30) NULL,
                `BirthDate` VARCHAR(20) NULL,
                `MobileNumber` VARCHAR(50) NULL,
                `Province` VARCHAR(120) NULL,
                `Country` VARCHAR(120) NULL,
                `UserRegisterSocial` VARCHAR(50) NULL,
                `CaptureDate` VARCHAR(20) NULL,
                `ModifiedDate` VARCHAR(20) NULL,
                `PoliticasPrivacidad` VARCHAR(5) NULL,
                `AceptaComunicaciones` VARCHAR(5) NULL,
                `Source` VARCHAR(180) NULL,
                snid VARCHAR(190) NULL,
                it_token TEXT NULL,
                id_token TEXT NULL,
                dynamic_fields TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_email_source (EmailAddress, Source),
                UNIQUE KEY uniq_id_source (IDNumber, Source),
                PRIMARY KEY  (id)
            ) {$charset_collate};";
        }

        if ($is_sqlite) {
            // For SQLite, use direct query instead of dbDelta
            $result = $wpdb->query($sql);
            if ($result === false) {
                error_log('XPSocial: Error creating table with direct query: ' . $wpdb->last_error);
            } else {
                error_log('XPSocial: Table created successfully by Leads Manager (SQLite)');
            }
        } else {
            // For MySQL, use dbDelta
            dbDelta($sql);
            error_log('XPSocial: Table created successfully by Leads Manager (MySQL)');
        }
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

        // Ensure we have the table name
        if (empty($this->table_name)) {
            $this->table_name = $wpdb->prefix . 'xpsocial_leads';
            error_log('XPSocial: Table name was empty, set to: ' . $this->table_name);
        }

        // Ensure table exists before trying to save
        error_log('XPSocial: About to ensure table exists before saving lead');
        $this->ensure_table_exists();
        
        // Double check table exists after ensure_table_exists
        $is_sqlite = (class_exists('WP_SQLite_DB') && $wpdb instanceof WP_SQLite_DB) || 
                     (isset($wpdb->dbh) && $wpdb->dbh instanceof PDO);
        
        if ($is_sqlite) {
            $result = $wpdb->get_var("SELECT name FROM sqlite_master WHERE type='table' AND name='{$this->table_name}'");
            $table_exists = ($result == $this->table_name);
        } else {
            $result = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $this->table_name));
            $table_exists = ($result == $this->table_name);
        }
        
        error_log('XPSocial: Final table check before save - exists: ' . ($table_exists ? 'Yes' : 'No'));
        
        if (!$table_exists) {
            error_log('XPSocial: Table still does not exist after ensure_table_exists, forcing creation');
            $this->force_create_table();
        }

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
     * Force create the table (public method for manual creation)
     *
     * @return bool True if successful, false otherwise
     */
    public function force_create_table()
    {
        error_log('XPSocial: Force creating table...');
        $this->create_table();
        
        // Verify table was created
        global $wpdb;
        $is_sqlite = (class_exists('WP_SQLite_DB') && $wpdb instanceof WP_SQLite_DB) || 
                     (isset($wpdb->dbh) && $wpdb->dbh instanceof PDO);
        
        if ($is_sqlite) {
            $result = $wpdb->get_var("SELECT name FROM sqlite_master WHERE type='table' AND name='{$this->table_name}'");
            $table_exists = ($result == $this->table_name);
        } else {
            $result = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $this->table_name));
            $table_exists = ($result == $this->table_name);
        }
        
        error_log('XPSocial: Table creation result: ' . ($table_exists ? 'Success' : 'Failed'));
        return $table_exists;
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
