<?php
/**
 * Cache system for XPSocial Login plugin
 * Optimizes API calls and reduces response times
 */

// Include configuration
require_once plugin_dir_path(__FILE__) . 'class-xpsocial_config.php';

class XPSocial_Cache {
    
    private static $instance = null;
    private $cache_timeout = 3600; // 1 hour default
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Initialize cache hooks
        add_action('init', array($this, 'init_cache'));
    }
    
    public function init_cache() {
        // Set cache timeout based on configuration
        $this->cache_timeout = get_option('xpsocial_cache_timeout', 3600);
    }
    
    /**
     * Get cached data
     * @param string $key Cache key
     * @return mixed|false Cached data or false if not found/expired
     */
    public function get($key) {
        $cached_data = get_transient('xpsocial_cache_' . md5($key));
        
        if ($cached_data === false) {
            return false;
        }
        
        // Check if cache is still valid
        if (isset($cached_data['expires']) && $cached_data['expires'] < time()) {
            delete_transient('xpsocial_cache_' . md5($key));
            return false;
        }
        
        return $cached_data['data'];
    }
    
    /**
     * Set cached data
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $timeout Timeout in seconds (optional)
     */
    public function set($key, $data, $timeout = null) {
        if ($timeout === null) {
            $timeout = $this->cache_timeout;
        }
        
        $cache_data = array(
            'data' => $data,
            'expires' => time() + $timeout,
            'created' => time()
        );
        
        set_transient('xpsocial_cache_' . md5($key), $cache_data, $timeout);
    }
    
    /**
     * Delete cached data
     * @param string $key Cache key
     */
    public function delete($key) {
        delete_transient('xpsocial_cache_' . md5($key));
    }
    
    /**
     * Clear all XPSocial cache
     */
    public function clear_all() {
        global $wpdb;
        
        // Delete all transients that start with xpsocial_cache_
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_xpsocial_cache_%'
            )
        );
        
        // Also delete transient timeouts
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_timeout_xpsocial_cache_%'
            )
        );
    }
    
    /**
     * Optimized HTTP request with caching
     * @param string $url URL to request
     * @param array $args Request arguments
     * @param int $cache_timeout Cache timeout in seconds
     * @return array|WP_Error Response or error
     */
    public function cached_request($url, $args = array(), $cache_timeout = 3600) {
        $cache_key = 'http_request_' . md5($url . serialize($args));
        
        // Try to get from cache first
        $cached_response = $this->get($cache_key);
        if ($cached_response !== false) {
            return $cached_response;
        }
        
        // Make the actual request
        $response = wp_remote_request($url, $args);
        
        // Cache successful responses only
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $this->set($cache_key, $response, $cache_timeout);
        }
        
        return $response;
    }
    
    /**
     * Batch update user meta for better performance
     * @param int $user_id User ID
     * @param array $meta_data Array of meta_key => meta_value
     */
    public function batch_update_user_meta($user_id, $meta_data) {
        global $wpdb;
        
        if (empty($meta_data) || !is_array($meta_data)) {
            return;
        }
        
        // Prepare batch insert/update
        $values = array();
        $placeholders = array();
        
        foreach ($meta_data as $meta_key => $meta_value) {
            $values[] = $user_id;
            $values[] = $meta_key;
            $values[] = maybe_serialize($meta_value);
            $placeholders[] = "(%d, %s, %s)";
        }
        
        if (!empty($values)) {
            $query = "INSERT INTO {$wpdb->usermeta} (user_id, meta_key, meta_value) VALUES " . 
                     implode(', ', $placeholders) . 
                     " ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)";
            
            $wpdb->query($wpdb->prepare($query, $values));
        }
    }
    
    /**
     * Optimized country data retrieval with caching
     * @param string $country_id Country ID
     * @return object|WP_Error Country data or error
     */
    public function get_cached_country_data($country_id) {
        $cache_key = 'country_data_' . $country_id;
        
        // Try cache first
        $cached_data = $this->get($cache_key);
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // Get fresh data
        $country_data = $this->fetch_country_data($country_id);
        
        // Cache for 24 hours since country data rarely changes
        // Only cache if we have valid data structure
        if (!is_wp_error($country_data) && 
            isset($country_data->data) && 
            is_array($country_data->data) && 
            !empty($country_data->data)) {
            $this->set($cache_key, $country_data, 86400);
        }
        
        return $country_data;
    }
    
    /**
     * Fetch country data from API
     * @param string $country_id Country ID
     * @return object|WP_Error Country data or error
     */
    private function fetch_country_data($country_id) {
        // Use centralized configuration
        $token = XPSocial_Config::get_license_token();
        $api_url = XPSocial_Config::get_api_base_url() . 'countries/selected';
        
        // Check if we have required configuration
        if (empty($token) || empty($api_url)) {
            return new WP_Error('config_error', 'Missing API configuration');
        }
        
        $response = wp_remote_post($api_url, array(
            'timeout' => 5, // Reduced timeout
            'headers' => array(
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'User-Agent' => 'WordPress/XPSocial'
            ),
            'body' => json_encode(array('countries_id' => $country_id))
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            return new WP_Error('api_error', 'API returned status code: ' . $response_code);
        }
        
        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            return new WP_Error('api_error', 'Empty response from API');
        }
        
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Error decoding API response: ' . json_last_error_msg());
        }
        
        // Validate data structure
        if (!isset($data['data']) || !is_array($data['data']) || empty($data['data'])) {
            return new WP_Error('data_error', 'Invalid data structure from API');
        }
        
        return rest_ensure_response($data);
    }
}

// Initialize cache system
XPSocial_Cache::get_instance();
