<?php
/**
 * Configuration management for XPSocial Login plugin
 * Handles only essential constants to prevent errors
 * 
 * Note: Main configuration is handled in opciones.php to avoid duplication
 */

class XPSocial_Config {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_constants();
    }
    
    /**
     * Initialize required constants only
     * Main configuration is handled in opciones.php
     */
    private function init_constants() {
        // Define URLAPI if not already defined
        if (!defined('URLAPI')) {
            // Use the constant defined in main plugin file
            // This is a fallback only
            define('URLAPI', 'https://geo.erna.group/api/');
        }
        
        // Define other essential constants if needed
        if (!defined('XPSOCIAL_VERSION')) {
            define('XPSOCIAL_VERSION', '3.1.7');
        }
        
        if (!defined('XPSOCIAL_PLUGIN_DIR')) {
            define('XPSOCIAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
        }
        
        if (!defined('XPSOCIAL_PLUGIN_URL')) {
            define('XPSOCIAL_PLUGIN_URL', plugin_dir_url(__FILE__));
        }
    }
    
    /**
     * Get API base URL from main configuration
     * @return string
     */
    public static function get_api_base_url() {
        // URLAPI is defined in main plugin file
        return defined('URLAPI') ? URLAPI : 'https://geo.erna.group/api/';
    }
    
    /**
     * Get license token from main configuration
     * @return string
     */
    public static function get_license_token() {
        return get_option('xpsocial_licencia', '');
    }
    
    /**
     * Validate essential configuration
     * @return array Array of validation results
     */
    public static function validate_config() {
        $errors = array();
        $warnings = array();
        
        // Check required constants
        if (!defined('URLAPI')) {
            $errors[] = 'URLAPI constant is not defined';
        }
        
        // Check required options (these are managed in opciones.php)
        if (empty(self::get_license_token())) {
            $warnings[] = 'License token is not configured';
        }
        
        if (empty(get_option('xpsocial_urlSocial'))) {
            $warnings[] = 'Social URL is not configured';
        }
        
        if (empty(get_option('xpsocial_authToken'))) {
            $warnings[] = 'Auth token is not configured';
        }
        
        return array(
            'errors' => $errors,
            'warnings' => $warnings,
            'is_valid' => empty($errors)
        );
    }
}

// Initialize configuration (constants only)
XPSocial_Config::get_instance();
