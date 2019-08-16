<?php
/**
 * WP CLI ACF PRO Activate license
 *
 * @author      Per Soderlind
 * @copyright   2019 Per Soderlind
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: WP CLI ACF PRO Activate license
 * Plugin URI: https://github.com/soderlind/wp-cli-acfpro-activate
 * GitHub Plugin URI: https://github.com/soderlind/wp-cli-acfpro-activate
 * Description: Activate ACF PRO license using WP CLI
 * Version:     0.0.4
 * Author:      Per Soderlind
 * Author URI:  https://soderlind.no
 * Text Domain: wp-cli-acfpro-activate
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * Credits: Most of the code is from https://anchor.host/preloading-advanced-custom-fields-pro-license-key/
 */


namespace Soderlind\WPCLI;

! defined( 'ABSPATH' ) and exit;
if ( ! defined( 'WP_CLI' ) ) {
	return;
}

\WP_CLI::add_command( 'acfpro', __NAMESPACE__ . '\AcfPro_Licence_Activate_CLI' );

class AcfPro_Licence_Activate_CLI extends \WP_CLI_Command {

	/**
	 * Activte the ACF PRO license
	 *
	 * ## OPTIONS
	 *
	 * --key=<licensekey>
	 * : You'll find your ACF PRO license key at https://www.advancedcustomfields.com/my-account/
	 *
	 * ## EXAMPLES
	 *
	 * wp acfpro activate --key=XXXXXX
	 */
	public function activate( $args, $assoc_args ) {
		if ( isset( $assoc_args['key'] ) && is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {

			// Loads ACF plugin
			include_once ABSPATH . 'wp-content/plugins/advanced-custom-fields-pro/acf.php';

			// connect
			$post = [
				'acf_license' => $assoc_args['key'],
				'acf_version' => acf_get_setting( 'version' ),
				'wp_name'     => get_bloginfo( 'name' ),
				'wp_url'      => home_url(),
				'wp_version'  => get_bloginfo( 'version' ),
				'wp_language' => get_bloginfo( 'language' ),
				'wp_timezone' => get_option( 'timezone_string' ),
			];

			// connect
			$response = \acf_updates()->request( 'v2/plugins/activate?p=pro', $post );

			// ensure response is expected JSON array (not string)
			if ( is_string( $response ) ) {
				$response = new \WP_Error( 'server_error', esc_html( $response ) );
			}

			// error
			if ( is_wp_error( $response ) ) {
				\WP_CLI::error( $response->get_error_message() );
			}

			// success
			if ( $response['status'] == 1 ) {
				\acf_pro_update_license( $response['license'] ); // update license
				\WP_CLI::success( 'ACF PRO: ' . wp_strip_all_tags( $response['message'] ) ); // show message
			} else {
				\WP_CLI::error( 'ACF PRO: ' . wp_strip_all_tags( $response['message'] ) ); // show message
			}
		}
	}
}
