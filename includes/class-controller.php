<?php
/**
 * The Class for managing the plugins Workflow.
 *
 * @package aspire-update
 */

namespace AspireUpdate;

/**
 * The Class for managing the plugins Workflow.
 */
class Controller {
	/**
	 * The Constructor.
	 */
	public function __construct() {
		Admin_Settings::get_instance();
		Plugins_Screens::get_instance();
		Themes_Screens::get_instance();
		Branding::get_instance();
		$this->api_rewrite();
		add_action( 'init', [ $this, 'privacy_options' ] );
		add_action( 'wp_ajax_aspireupdate_clear_log', [ $this, 'clear_log' ] );
		add_action( 'wp_ajax_aspireupdate_read_log', [ $this, 'read_log' ] );
	}

	/**
	 * Enable API Rewrites based on the Users settings.
	 *
	 * @codeCoverageIgnore Side-effects are from other methods already covered by tests.
	 *
	 * @return void
	 */
	private function api_rewrite() {
		$admin_settings = Admin_Settings::get_instance();

		if ( $admin_settings->get_setting( 'enable', false ) ) {
			$api_host = $admin_settings->get_setting( 'api_host', '' );
		} else {
			$api_host = 'debug';
		}

		if ( isset( $api_host ) && ( '' !== $api_host ) ) {
			$enable_debug = $admin_settings->get_setting( 'enable_debug', false );
			$disable_ssl  = $admin_settings->get_setting( 'disable_ssl_verification', false );
			$api_key      = $admin_settings->get_setting( 'api_key', '' );
			if ( $enable_debug && $disable_ssl ) {
				new API_Rewrite( $api_host, true, $api_key );
			} else {
				new API_Rewrite( $api_host, false, $api_key );
			}
		}
	}

	public function privacy_options() {
		$admin_settings = Admin_Settings::get_instance();
		$peekaboo       = Peekaboo::get_instance();

		if ( $admin_settings->get_setting( 'disable_privacy_remote_avatar_services', false ) ) {
			$peekaboo->disable_remote_avatar_services();
		}

		if ( $admin_settings->get_setting( 'disable_privacy_dashboard_news_widget', false ) ) {
			$peekaboo->disable_dashboard_news_events_widget();
		}

		if ( $admin_settings->get_setting( 'disable_privacy_oembed_discovery', false ) ) {
			$peekaboo->disable_oembed_rest_discovery();
		}

		if ( $admin_settings->get_setting( 'disable_privacy_xmlrpc', false ) ) {
			$peekaboo->disable_xmlrpc();
		}

		if ( $admin_settings->get_setting( 'disable_privacy_remote_core_update_check', false ) ) {
			$peekaboo->disable_remote_core_version_check();
		}

		if ( $admin_settings->get_setting( 'disable_privacy_remote_plugin_update_check', false ) ) {
			$peekaboo->disable_remote_plugin_update_check();
		}

		if ( $admin_settings->get_setting( 'disable_privacy_remote_theme_update_check', false ) ) {
			$peekaboo->disable_remote_theme_update_check();
		}
	}

	/**
	 * Ajax action to clear the Log file.
	 *
	 * @codeCoverageIgnore Cannot be tested. Results in script termination.
	 *
	 * @return void
	 */
	public function clear_log() {
		$capability = is_multisite() ? 'manage_network_options' : 'manage_options';
		if (
			! current_user_can( $capability ) ||
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'aspireupdate-ajax' )
		) {
			wp_send_json_error(
				[
					'message' => __( 'Error: You are not authorized to access this resource.', 'aspireupdate' ),
				]
			);
		}

		$status = Debug::clear();
		if ( is_wp_error( $status ) ) {
			wp_send_json_error(
				[
					'message' => $status->get_error_message(),
				]
			);
		}

		wp_send_json_success(
			[
				'message' => __( 'Log file cleared successfully.', 'aspireupdate' ),
			]
		);
	}

	/**
	 * Ajax action to read the Log file.
	 *
	 * @codeCoverageIgnore Cannot be tested. Results in script termination.
	 *
	 * @return void
	 */
	public function read_log() {
		$capability = is_multisite() ? 'manage_network_options' : 'manage_options';
		if (
			! current_user_can( $capability ) ||
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'aspireupdate-ajax' )
		) {
			wp_send_json_error(
				[
					'message' => __( 'Error: You are not authorized to access this resource.', 'aspireupdate' ),
				]
			);
		}

		$content = Debug::read( 1000 );
		if ( is_wp_error( $content ) ) {
			wp_send_json_error(
				[
					'message' => $content->get_error_message(),
				]
			);
		}

		wp_send_json_success(
			[
				'content' => $content,
			]
		);
	}
}
