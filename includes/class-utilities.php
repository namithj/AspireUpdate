<?php
/**
 * The Class for Miscellaneous Helper Functions.
 *
 * @package aspire-update
 */

namespace AspireUpdate;

/**
 * The Class for Admin Settings Page and functions to access Settings Values.
 */
class Utilities {
	/**
	 * Get the domain name from the site URL.
	 *
	 * @return string The domain name.
	 */
	public static function get_site_domain() {
		$site_url = network_site_url();
		return wp_parse_url( $site_url, PHP_URL_HOST );
	}

	/**
	 * Return the content of the File after processing.
	 *
	 * @param string $file File name.
	 * @param array  $args Data to pass to the file.
	 */
	public static function include_file( $file, $args = [] ) {
		$file_path = __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $file;
		if ( ( '' !== $file ) && file_exists( $file_path ) ) {
			//phpcs:disable
			// Usage of extract() is necessary in this content to simulate templating functionality.
			extract( $args );
			//phpcs:enable
			include $file_path;
		}
	}

	/**
	 * Get the hosts data from the JSON file.
	 *
	 * @return array|false The hosts data as an associative array, or false on failure.
	 */
	public static function get_hosts_data() {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();
		global $wp_filesystem;
		$file_path = AP_PATH . DIRECTORY_SEPARATOR . 'hosts.json';

		if ( ! $wp_filesystem->exists( $file_path ) || ! $wp_filesystem->is_readable( $file_path ) ) {
			Debug::log_string( __( 'Config file is missing or unreadable.', 'aspireupdate' ) );
			return false;
		}

		$json_data = $wp_filesystem->get_contents( $file_path );
		if ( false === $json_data ) {
			Debug::log_string( __( 'Config file is empty.', 'aspireupdate' ) );
			return false;
		}

		$json_data = json_decode( $json_data, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			Debug::log_string( __( 'Error found in config file content.', 'aspireupdate' ) );
			return false;
		}

		return $json_data;
	}
}
