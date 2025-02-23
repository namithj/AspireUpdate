<?php
/**
 * The Class for Universal Integration of Git Updater Lite.
 *
 * Only requirement is appropriate `Update URI` header.
 *
 * @package aspire-update
 */

namespace AspireUpdate;

/**
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class GUL_Loader
 */
class GUL_Loader {

	// phpcs:ignore Generic.Commenting.DocComment.MissingShort
	/** @var array */
	public static $package_arr = [];

	/**
	 * Gather all plugins/themes with data in Update URI header.
	 *
	 * @return \stdClass
	 */
	public function init() {
		// Seems to be required for PHPUnit testing on GitHub workflow.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_path = trailingslashit( \WP_PLUGIN_DIR );
		$plugins     = get_plugins();
		foreach ( $plugins as $file => $plugin ) {
			$update_uri = $plugin['UpdateURI'];

			if ( ! empty( $update_uri ) ) {
				self::$package_arr[] = $plugin_path . $file;
			}
		}

		$theme_path = \ABSPATH . \WP_CONTENT_DIR . '/themes/';
		$themes     = wp_get_themes();
		foreach ( $themes as $file => $theme ) {
			$update_uri = $theme->get( 'UpdateURI' );

			if ( ! empty( $update_uri ) ) {
				self::$package_arr[] = $theme_path . $file . '/style.css';
			}
		}

		return $this;
	}

	/**
	 * Run Git Updater Lite for potential packages.
	 *
	 * @return void
	 */
	public function run() {
		require_once dirname( __DIR__ ) . '/vendor/afragen/git-updater-lite/Lite.php';
		foreach ( self::$package_arr as $package ) {
			( new \Fragen\Git_Updater\Lite( $package ) )->run();
		}
	}
}
