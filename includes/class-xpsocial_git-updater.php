<?php
/**
 * Git Auto-Updater for XPSocial Login Plugin
 * 
 * @package XPSocial_Login
 * @subpackage Git_Updater
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Xpsocial_Git_Updater {
    
    private $plugin_file;
    private $plugin_slug;
    private $git_repo_url;
    private $git_branch;
    
    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = 'xpsocial_login';
        
        // Obtener configuración desde la base de datos
        $this->git_repo_url = get_option('xpsocial_git_repo_url', '');
        $this->git_branch = get_option('xpsocial_git_branch', '');
        
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Hook para mostrar información de actualización en el admin
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);
        add_filter('site_transient_update_plugins', array($this, 'push_update'));
        
        // Hook para manejar la actualización
        add_action('upgrader_process_complete', array($this, 'after_update'), 10, 2);
        
        // Hook para limpiar cache después de actualización
        add_action('upgrader_process_complete', array($this, 'clear_update_cache'), 10, 2);
        
        // Hook para verificar actualizaciones manualmente
        add_action('wp_ajax_xpsocial_check_updates', array($this, 'ajax_check_updates'));
        add_action('wp_ajax_xpsocial_force_update', array($this, 'ajax_force_update'));
    }
    
    
    /**
     * Realizar verificación de actualizaciones
     */
    public function perform_update_check() {
        error_log('XPSocial Git Updater: Iniciando verificación de actualizaciones');
        
        $current_version = $this->get_current_version();
        error_log('XPSocial Git Updater: Versión actual: ' . $current_version);
        
        $latest_version = $this->get_latest_version_from_git();
        error_log('XPSocial Git Updater: Última versión en Git: ' . ($latest_version ?: 'No encontrada'));
        
        if ($latest_version && version_compare($current_version, $latest_version, '<')) {
            error_log('XPSocial Git Updater: Actualización disponible - Guardando información');
            $this->store_update_info($latest_version);
        } else {
            error_log('XPSocial Git Updater: No hay actualizaciones disponibles');
        }
        
        // Guardar timestamp de la verificación
        set_transient('xpsocial_last_update_check', time(), DAY_IN_SECONDS);
    }
    
    /**
     * Obtener la versión actual del plugin
     */
    private function get_current_version() {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        
        $plugin_data = get_plugin_data($this->plugin_file);
        return $plugin_data['Version'] ?? '1.0.0';
    }
    
    /**
     * Obtener la última versión desde Git
     */
    private function get_latest_version_from_git() {
        // Intentar obtener la versión desde el archivo del plugin en Git
        $git_file_url = str_replace('.git', '/raw/' . $this->git_branch . '/xpsocial_login.php', $this->git_repo_url);
        
        error_log('XPSocial Git Updater: Verificando URL: ' . $git_file_url);
        
        $response = wp_remote_get($git_file_url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'XPSocial-Plugin-Updater/1.0'
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('XPSocial Git Updater: Error al obtener información del repositorio - ' . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        error_log('XPSocial Git Updater: Código de respuesta HTTP: ' . $response_code);
        
        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            error_log('XPSocial Git Updater: Respuesta vacía del servidor');
            return false;
        }
        
        error_log('XPSocial Git Updater: Tamaño de respuesta: ' . strlen($body) . ' bytes');
        
        // Extraer la versión del archivo
        if (preg_match('/Version:\s*([0-9.]+)/i', $body, $matches)) {
            error_log('XPSocial Git Updater: Versión encontrada en Git: ' . $matches[1]);
            return $matches[1];
        }
        
        error_log('XPSocial Git Updater: No se pudo extraer la versión del archivo');
        error_log('XPSocial Git Updater: Primeros 500 caracteres del archivo: ' . substr($body, 0, 500));
        
        return false;
    }
    
    /**
     * Almacenar información de actualización
     */
    private function store_update_info($new_version) {
        $update_info = array(
            'version' => $new_version,
            'package' => $this->git_repo_url,
            'requires' => '5.0',
            'tested' => get_bloginfo('version'),
            'last_updated' => current_time('mysql'),
            'sections' => array(
                'description' => 'Actualización automática desde Git',
                'changelog' => 'Ver el historial de cambios en el repositorio Git'
            )
        );
        
        set_transient('xpsocial_update_info', $update_info, DAY_IN_SECONDS);
    }
    
    /**
     * Información del plugin para WordPress
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) {
            return $result;
        }
        
        $update_info = get_transient('xpsocial_update_info');
        if ($update_info) {
            $result = (object) $update_info;
        }
        
        return $result;
    }
    
    /**
     * Agregar actualización a la lista de WordPress
     */
    public function push_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        $update_info = get_transient('xpsocial_update_info');
        if (!$update_info) {
            return $transient;
        }
        
        $current_version = $this->get_current_version();
        if (version_compare($current_version, $update_info['version'], '<')) {
            $transient->response[$this->plugin_file] = (object) array(
                'slug' => $this->plugin_slug,
                'plugin' => $this->plugin_file,
                'new_version' => $update_info['version'],
                'url' => $this->git_repo_url,
                'package' => $this->git_repo_url,
                'requires' => $update_info['requires'],
                'tested' => $update_info['tested']
            );
        }
        
        return $transient;
    }
    
    /**
     * Después de la actualización
     */
    public function after_update($upgrader_object, $options) {
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            if (isset($options['plugins']) && in_array($this->plugin_file, $options['plugins'])) {
                // Limpiar transients
                delete_transient('xpsocial_last_update_check');
                delete_transient('xpsocial_update_info');
                
                // Log de actualización exitosa
                error_log('XPSocial Plugin: Actualización exitosa desde Git');
                
                // Opcional: ejecutar migraciones o actualizaciones de base de datos
                $this->run_post_update_tasks();
            }
        }
    }
    
    /**
     * Limpiar cache de actualizaciones
     */
    public function clear_update_cache($upgrader_object, $options) {
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            if (isset($options['plugins']) && in_array($this->plugin_file, $options['plugins'])) {
                wp_cache_delete('plugins', 'plugins');
            }
        }
    }
    
    /**
     * Tareas post-actualización
     */
    private function run_post_update_tasks() {
        // Aquí puedes agregar tareas que se ejecuten después de la actualización
        // Por ejemplo: actualizar base de datos, limpiar cache, etc.
        
        // Ejemplo: actualizar versión en la base de datos
        update_option('xpsocial_plugin_version', $this->get_current_version());
        
        // Ejemplo: limpiar cache si existe
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
    
    /**
     * AJAX: Verificar actualizaciones manualmente
     */
    public function ajax_check_updates() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'xpsocial_update_nonce')) {
            wp_die('Token de seguridad inválido');
        }
        
        // Forzar verificación de actualizaciones
        delete_transient('xpsocial_last_update_check');
        $this->perform_update_check();
        
        $update_info = get_transient('xpsocial_update_info');
        $current_version = $this->get_current_version();
        
        if ($update_info && version_compare($current_version, $update_info['version'], '<')) {
            wp_send_json_success(array(
                'message' => 'Actualización disponible: v' . $update_info['version'],
                'current_version' => $current_version,
                'new_version' => $update_info['version']
            ));
        } else {
            wp_send_json_success(array(
                'message' => 'El plugin está actualizado',
                'current_version' => $current_version
            ));
        }
    }
    
    /**
     * AJAX: Forzar actualización
     */
    public function ajax_force_update() {
        // Verificar permisos
        if (!current_user_can('manage_options')) {
            wp_die('Permisos insuficientes');
        }
        
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'xpsocial_update_nonce')) {
            wp_die('Token de seguridad inválido');
        }
        
        // Aquí podrías implementar una actualización forzada
        // Por ahora, solo limpiamos el cache y forzamos una nueva verificación
        delete_transient('xpsocial_last_update_check');
        delete_transient('xpsocial_update_info');
        
        wp_send_json_success(array(
            'message' => 'Cache de actualizaciones limpiado. Verifica nuevamente en unos minutos.'
        ));
    }
    
    /**
     * Obtener estado de actualizaciones
     */
    public function get_update_status() {
        $current_version = $this->get_current_version();
        $update_info = get_transient('xpsocial_update_info');
        $last_check = get_transient('xpsocial_last_update_check');
        
        return array(
            'current_version' => $current_version,
            'update_available' => $update_info ? version_compare($current_version, $update_info['version'], '<') : false,
            'new_version' => $update_info['version'] ?? null,
            'last_check' => $last_check ? date('Y-m-d H:i:s', $last_check) : 'Nunca',
            'next_check' => 'Solo manual'
        );
    }
}
