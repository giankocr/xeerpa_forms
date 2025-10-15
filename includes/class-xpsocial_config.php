<?php
/**
 * Configuration management for XPSocial Login plugin
 * Handles constants and configuration to prevent errors
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
        $this->init_hooks();
    }
    
    /**
     * Initialize required constants
     */
    private function init_constants() {
        // Define URLAPI if not already defined
        if (!defined('URLAPI')) {
            // Try to get from options, fallback to default
            $api_base = get_option('xpsocial_api_base_url', 'https://api.xeerpa.com/');
            define('URLAPI', rtrim($api_base, '/') . '/');
        }
        
        // Define other constants if needed
        if (!defined('XPSOCIAL_VERSION')) {
            define('XPSOCIAL_VERSION', '1.0.0');
        }
        
        if (!defined('XPSOCIAL_PLUGIN_DIR')) {
            define('XPSOCIAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
        }
        
        if (!defined('XPSOCIAL_PLUGIN_URL')) {
            define('XPSOCIAL_PLUGIN_URL', plugin_dir_url(__FILE__));
        }
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('admin_init', array($this, 'register_config_settings'));
        add_action('admin_menu', array($this, 'add_config_menu'));
    }
    
    /**
     * Register configuration settings
     */
    public function register_config_settings() {
        register_setting('xpsocial-config-group', 'xpsocial_api_base_url');
        register_setting('xpsocial-config-group', 'xpsocial_licencia');
        register_setting('xpsocial-config-group', 'xpsocial_urlSocial');
        register_setting('xpsocial-config-group', 'xpsocial_authToken');
    }
    
    /**
     * Add configuration menu
     */
    public function add_config_menu() {
        add_submenu_page(
            'options-general.php',
            'XPSocial Configuration',
            'XPSocial Config',
            'manage_options',
            'xpsocial-config',
            array($this, 'config_page')
        );
    }
    
    /**
     * Configuration page
     */
    public function config_page() {
        ?>
        <div class="wrap">
            <h1>XPSocial Configuration</h1>
            <form method="post" action="options.php">
                <?php settings_fields('xpsocial-config-group'); ?>
                <?php do_settings_sections('xpsocial-config-group'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">API Base URL</th>
                        <td>
                            <input type="url" name="xpsocial_api_base_url" 
                                   value="<?php echo esc_attr(get_option('xpsocial_api_base_url', 'https://api.xeerpa.com/')); ?>" 
                                   class="regular-text" />
                            <p class="description">Base URL for Xeerpa API (default: https://api.xeerpa.com/)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">License Token</th>
                        <td>
                            <input type="text" name="xpsocial_licencia" 
                                   value="<?php echo esc_attr(get_option('xpsocial_licencia')); ?>" 
                                   class="regular-text" />
                            <p class="description">Your Xeerpa license token</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Social URL</th>
                        <td>
                            <input type="url" name="xpsocial_urlSocial" 
                                   value="<?php echo esc_attr(get_option('xpsocial_urlSocial')); ?>" 
                                   class="regular-text" />
                            <p class="description">Xeerpa social login URL</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Auth Token</th>
                        <td>
                            <input type="text" name="xpsocial_authToken" 
                                   value="<?php echo esc_attr(get_option('xpsocial_authToken')); ?>" 
                                   class="regular-text" />
                            <p class="description">Authentication token for social login</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Save Configuration'); ?>
            </form>
            
            <div class="card">
                <h2>Configuration Status</h2>
                <p><strong>URLAPI Constant:</strong> <?php echo defined('URLAPI') ? '✅ Defined (' . URLAPI . ')' : '❌ Not defined'; ?></p>
                <p><strong>License Token:</strong> <?php echo get_option('xpsocial_licencia') ? '✅ Configured' : '❌ Missing'; ?></p>
                <p><strong>Social URL:</strong> <?php echo get_option('xpsocial_urlSocial') ? '✅ Configured' : '❌ Missing'; ?></p>
                <p><strong>Auth Token:</strong> <?php echo get_option('xpsocial_authToken') ? '✅ Configured' : '❌ Missing'; ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get API base URL
     * @return string
     */
    public static function get_api_base_url() {
        return get_option('xpsocial_api_base_url', 'https://api.xeerpa.com/');
    }
    
    /**
     * Get license token
     * @return string
     */
    public static function get_license_token() {
        return get_option('xpsocial_licencia', '');
    }
    
    /**
     * Validate configuration
     * @return array Array of validation results
     */
    public static function validate_config() {
        $errors = array();
        $warnings = array();
        
        // Check required constants
        if (!defined('URLAPI')) {
            $errors[] = 'URLAPI constant is not defined';
        }
        
        // Check required options
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

// Initialize configuration
XPSocial_Config::get_instance();
