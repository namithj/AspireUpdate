<?php
/**
 * The Class for Debug Functions.
 *
 * @package aspire-update
 */

namespace AspireUpdate;

/**
 * The Class for Debug Functions.
 */
class Debug {

	/**
	 * The filesystem.
	 *
	 * @var Filesystem_Direct
	 */
	private static $filesystem;

	/**
	 * Generates a random file name for the log file.
	 *
	 * @return string The Log file name.
	 */
	private static function generate_log_file_name() {
		return '.debug-aspire-update-' . wp_generate_password( 12, false, false ) . '.log';
	}

	/**
	 * Get the Log file path.
	 *
	 * @return string The Log file path.
	 */
	public static function get_file_path() {
		$log_file = get_option( 'ap_log_file_name', false );
		if ( false === $log_file ) {
			$log_file = self::generate_log_file_name();
			update_option( 'ap_log_file_name', $log_file );
		}

		return WP_CONTENT_DIR . '/' . $log_file;
	}

	/**
	 * Initializes the WordPress Filesystem.
	 *
	 * @return Filesystem_Direct The filesystem object.
	 */
	public static function init_filesystem() {
		if ( ! self::$filesystem instanceof Filesystem_Direct ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
			WP_Filesystem();
			self::$filesystem = new Filesystem_Direct( false );
		}
		return self::$filesystem;
	}

	/**
	 * Get the content of the log file truncated upto N number of lines.
	 *
	 * @param integer $limit Max no of lines to return. Defaults to a 1000 lines.
	 *
	 * @return array|WP_Error An array of lines in the file, limited to $limit, or a WP_Error object on failure.
	 */
	public static function read( $limit = 1000 ) {
		$wp_filesystem = self::init_filesystem();
		$file_path     = self::get_file_path();

		if ( ! $wp_filesystem->exists( $file_path ) || ! $wp_filesystem->is_readable( $file_path ) ) {
			return new \WP_Error( 'not_readable', __( 'Error: Unable to read the log file.', 'aspireupdate' ) );
		}

		$file_content = $wp_filesystem->get_contents_array( $file_path, $limit, true );

		if ( ( false === $file_content ) || ( 0 === count( array_filter( $file_content ) ) ) ) {
			$file_content = [ esc_html__( '*****Log file is empty.*****', 'aspireupdate' ) ];
		}

		return $file_content;
	}

	/**
	 * Clear content of the log file.
	 *
	 * @return boolean|WP_Error true on success and false on failure.
	 */
	public static function clear() {
		$wp_filesystem = self::init_filesystem();
		$file_path     = self::get_file_path();

		if ( ! $wp_filesystem->exists( $file_path ) || ! $wp_filesystem->is_writable( $file_path ) ) {
			return new \WP_Error( 'not_accessible', __( 'Error: Unable to access the log file.', 'aspireupdate' ) );
		}

		$wp_filesystem->delete( $file_path );
		delete_option( 'ap_log_file_name' );

		return true;
	}

	/**
	 * Logs a message to the debug log file.
	 *
	 * @param mixed  $message The message to log.
	 * @param string $type   The log level ('string', 'request', 'response').
	 */
	public static function log( $message, $type = 'string' ) {
		$wp_filesystem     = self::init_filesystem();
		$timestamp         = gmdate( 'Y-m-d H:i:s' );
		$formatted_message = sprintf(
			'[%s] [%s]: %s',
			$timestamp,
			strtoupper( $type ),
			self::format_message( $message )
		) . PHP_EOL;

		$file_path = self::get_file_path();
		$wp_filesystem->put_contents(
			$file_path,
			$formatted_message,
			FS_CHMOD_FILE,
			'a'
		);
	}

	/**
	 * Formats the message to be logged.
	 *
	 * @param mixed $message The message to format (string, array, object, etc.).
	 * @return string The formatted message.
	 */
	private static function format_message( $message ) {
		if ( is_array( $message ) || is_object( $message ) ) {
			// phpcs:disable WordPress.PHP.DevelopmentFunctions
			/**
			 * Priting an array or object to log file.  This is a valid use case.
			 */
			return print_r( $message, true );
			 // phpcs:enable
		}
		return (string) $message;
	}

	/**
	 * Log an info message.
	 *
	 * @param mixed $message The message to log.
	 *
	 * @return void
	 */
	public static function log_string( $message ) {
		$admin_settings = Admin_Settings::get_instance();
		$debug_mode     = $admin_settings->get_setting( 'enable_debug', false );
		$debug_types    = $admin_settings->get_setting( 'enable_debug_type', [] );
		if ( $debug_mode && is_array( $debug_types ) && in_array( 'string', $debug_types, true ) ) {
			self::log( $message, 'string' );
		}
	}

	/**
	 * Log a warning message.
	 *
	 * @param mixed $message The message to log.
	 *
	 * @return void
	 */
	public static function log_request( $message ) {
		$admin_settings = Admin_Settings::get_instance();
		$debug_mode     = $admin_settings->get_setting( 'enable_debug', false );
		$debug_types    = $admin_settings->get_setting( 'enable_debug_type', [] );
		if ( $debug_mode && is_array( $debug_types ) && in_array( 'request', $debug_types, true ) ) {
			self::log( $message, 'request' );
		}
	}

	/**
	 * Log an error message.
	 *
	 * @param mixed $message The message to log.
	 *
	 * @return void
	 */
	public static function log_response( $message ) {
		$admin_settings = Admin_Settings::get_instance();
		$debug_mode     = $admin_settings->get_setting( 'enable_debug', false );
		$debug_types    = $admin_settings->get_setting( 'enable_debug_type', [] );
		if ( $debug_mode && is_array( $debug_types ) && in_array( 'response', $debug_types, true ) ) {
			self::log( $message, 'response' );
		}
	}
}
