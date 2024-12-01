<?php

/**
 * Fired during plugin activation
 *
 * @link       https://green-view.nl
 * @since      1.0.0
 *
 * @package    Gv_Splat
 * @subpackage Gv_Splat/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Gv_Splat
 * @subpackage Gv_Splat/includes
 * @author     GreenView <info@green-view.nl>
 */
class Gv_Splat_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        $upload_dir = wp_upload_dir();
        $splat_dir = $upload_dir['basedir'] . '/splats';

        if (!file_exists($splat_dir)) {
            wp_mkdir_p($splat_dir);

            // Protect directory
            $htaccess = $splat_dir . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, 'Options -Indexes');
            }
        }
	}

}
