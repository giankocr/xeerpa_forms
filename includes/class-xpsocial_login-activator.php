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
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        $my_options = null;

        function my_init_options()
        {
            global $my_options;
            if (!$my_options) {
                $my_options = get_option('xpsocial_url');
            }
        }
    }
}
