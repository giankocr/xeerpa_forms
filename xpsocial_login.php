<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://gianko.com/
 * @package           Xpsocial_login
 *
 * @wordpress-plugin
 * Plugin Name:       XPSocial Login
 * Plugin URI:        https://gianko.com/
 * Description:       Plugin para conectar wordpress con los api de Xeerpa Social.
 * Version:           3.1.1
 * Author:            giankocr
 * Author URI:        https://gianko.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       xpsocial_login
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('XPSOCIAL_LOGIN_VERSION', '1.0.0');
//Prod
define('URLAPI', 'https://geo.erna.group/api/');
//Test
//define('URLAPI', 'http://127.0.0.1:8000/api/');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-xpsocial_login-activator.php
 */
function activate_xpsocial_login()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-xpsocial_login-activator.php';
    Xpsocial_login_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-xpsocial_login-deactivator.php
 */
function deactivate_xpsocial_login()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-xpsocial_login-deactivator.php';
    Xpsocial_login_Deactivator::deactivate();
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-xpsocial_login.php';

/*
*   Include opciones.php, use require_once to stop the script if opciones.php is not found
*/
require plugin_dir_path(__FILE__) . 'includes/opciones.php';
require plugin_dir_path(__FILE__) . 'includes/recomendador.php'; // TODO- configurar el recomendador

register_activation_hook(__FILE__, 'activate_xpsocial_login');
register_deactivation_hook(__FILE__, 'deactivate_xpsocial_login');

/**
 * The console_log function to debug console_log(string)
 */
function console_log($output, $with_script_tags = true)
{
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
    ');';
    if ($with_script_tags) {
        $js_code = '<script>' . $js_code . '</script>';
    }
    echo $js_code;
}





/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_xpsocial_login()
{

    $plugin = new Xpsocial_login();
    $plugin->run();
}

// Admin bar functionality removed - no longer managing user roles

run_xpsocial_login();
