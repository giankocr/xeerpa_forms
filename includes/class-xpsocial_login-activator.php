<?php

/**
 * Fired during plugin activation
 *
 * @link       https://gianko.com/
 * @since      1.0.0
 *
 * @package    Xpsocial_login
 * @subpackage Xpsocial_login/public
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Xpsocial_login
 * @subpackage Xpsocial_login/public
 * @author     Gianko <gian@gianko.com>
 */
class Xpsocial_login_Activator
{
    /**
     * Activate the plugin and create necessary database tables
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        // Create custom table for leads
        self::create_custom_table();
        
        // Initialize default options
        self::init_default_options();
        
        // Set activation flag
        update_option('xpsocial_plugin_activated', true);
        update_option('xpsocial_activation_date', current_time('mysql'));
    }

    /**
     * Create custom table for storing leads data
     *
     * @since    1.0.0
     */
    private static function create_custom_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'xpsocial_leads';
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Check if table exists - compatible with both MySQL and SQLite
        $table_exists = false;
        
        if ($wpdb->dbh instanceof PDO) {
            // SQLite
            $result = $wpdb->get_var($wpdb->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=%s", $table_name));
            $table_exists = ($result == $table_name);
        } else {
            // MySQL
            $result = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            $table_exists = ($result == $table_name);
        }
        
        // Check if we're using SQLite or MySQL
        $is_sqlite = ($wpdb->dbh instanceof PDO);
        
        if ($is_sqlite) {
            // SQLite compatible SQL
            $sql = "CREATE TABLE {$table_name} (
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
            $sql = "CREATE TABLE {$table_name} (
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
                error_log('XPSocial: Table wp_xpsocial_leads created successfully (SQLite)');
            }
        } else {
            // For MySQL, use dbDelta
            dbDelta($sql);
            error_log('XPSocial: Table wp_xpsocial_leads created successfully (MySQL)');
        }
    }

    /**
     * Initialize default plugin options
     *
     * @since    1.0.0
     */
    private static function init_default_options()
    {
        // Default options array
        $default_options = array(
            'xpsocial_licencia' => '',
            'xpsocial_urlSocial' => '',
            'xpsocial_urlForm' => '',
            'xpsocial_authToken' => '',
            'xpsocial_clientId' => '',
            'xpsocial_clientPwd' => '',
            'xpsocial_appId' => '',
            'xpsocial_redirect_login' => '',
            'xpsocial_redirect_to_registro' => '',
            'xpsocial_redirect_existing_user' => '',
            'xpsocial_marca' => '',
            'xpsocial_linkPP' => '',
            'xpsocial_linkTyC' => '',
            'xpsocial_lost_password_url' => '',
            'xpsocial_countries' => array(),
            'fifco_api_url' => '',
            'fifco_api_token' => '',
            // Style options with defaults
            'xp_input_height' => '40',
            'xp_input_border' => '1px solid #ced4da',
            'xp_input_border_radius' => '6',
            'xp_bg_color' => '#ffffff',
            'xp_div_color' => '#f8f9fa',
            'xp_font_color' => '#495057',
            'xp_divisor_color' => '#e9ecef',
            'xp_btn_height' => '40',
            'xp_btn_width' => '100',
            'xp_btn_color' => '#ffffff',
            'xp_btn_bgcolor' => '#007cba',
            'xp_btn_border_width' => '1',
            'xp_btn_border_color' => '#007cba',
            'xp_btn_border_radius' => '6'
        );

        // Set default options if they don't exist
        foreach ($default_options as $option_name => $default_value) {
            if (get_option($option_name) === false) {
                add_option($option_name, $default_value);
            }
        }

        // Log options initialization
        error_log('XPSocial: Default options initialized successfully');
    }

    /**
     * Create the custom table manually (for troubleshooting)
     * This method can be called to create the table if it doesn't exist
     *
     * @since    1.0.0
     */
    public static function create_table_manually()
    {
        self::create_custom_table();
        error_log('XPSocial: Table creation attempted manually');
    }
}
