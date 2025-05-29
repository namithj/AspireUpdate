<?php
/**
 * The Class for adding branding to the dashboard.
 *
 * @package aspire-update
 */

namespace AspireUpdate;

/**
 * The Class for adding branding to the dashboard.
 */
class Branding {
	/**
	 * Hold a single instance of the class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$admin_settings = Admin_Settings::get_instance();
		if ( $admin_settings->get_setting( 'enable', false ) ) {
			$admin_notices_hook = is_multisite() ? 'network_admin_notices' : 'admin_notices';
			add_action( $admin_notices_hook, [ $this, 'output_admin_notice' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
			add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_menu' ], 100 );
		}
	}

	/**
	 * Initialize Class.
	 *
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @param string $hook The page identifier.
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( defined( 'AP_REMOVE_UI' ) && AP_REMOVE_UI ) {
			return;
		}

		wp_enqueue_style( 'aspire_update_settings_css', plugin_dir_url( __DIR__ ) . 'assets/css/aspire-update.css', [], AP_VERSION );
	}

	/**
	 * Output admin notice.
	 *
	 * @return void
	 */
	public function output_admin_notice() {
		if ( defined( 'AP_REMOVE_UI' ) && AP_REMOVE_UI ) {
			return;
		}

		$current_screen = get_current_screen();
		if ( ! $current_screen instanceof \WP_Screen ) {
			return;
		}

		$message = '';
		switch ( $current_screen->id ) {
			case 'plugins':
			case 'plugin-install':
				if ( is_multisite() ) {
					break;
				}
				// Fall-through.
			case 'plugins-network':
			case 'plugin-install-network':
				$message = sprintf(
					/* translators: 1: The name of the plugin, 2: The documentation URL. */
					__( 'Your plugin updates are now powered by <strong>%1$s</strong>. <a href="%2$s">Learn about %1$s</a>', 'aspireupdate' ),
					'AspireUpdate',
					__( 'https://docs.aspirepress.org/aspireupdate/', 'aspireupdate' )
				);
				break;
			case 'themes':
			case 'theme-install':
				if ( is_multisite() ) {
					break;
				}
				// Fall-through.
			case 'themes-network':
			case 'theme-install-network':
				$message = sprintf(
					/* translators: 1: The name of the plugin, 2: The documentation URL. */
					__( 'Your theme updates are now powered by <strong>%1$s</strong>. <a href="%2$s">Learn about %1$s</a>', 'aspireupdate' ),
					'AspireUpdate',
					__( 'https://docs.aspirepress.org/aspireupdate/', 'aspireupdate' )
				);
				break;
			case 'update-core':
				if ( is_multisite() ) {
					break;
				}
				// Fall-through.
			case 'update-core-network':
				$message = sprintf(
					/* translators: 1: The name of the plugin, 2: The documentation URL. */
					__( 'Your WordPress, plugin, theme and translation updates are now powered by <strong>%1$s</strong>. <a href="%2$s">Learn about %1$s</a>', 'aspireupdate' ),
					'AspireUpdate',
					__( 'https://docs.aspirepress.org/aspireupdate/', 'aspireupdate' )
				);
				break;
		}

		if ( '' === $message ) {
			return;
		}

		echo wp_kses_post( '<div class="notice aspireupdate-notice notice-info"><p>' . $message . '</p></div>' );
	}

	/**
	 * Add a menu to the admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance.
	 * @return void
	 */
	public function add_admin_bar_menu( $wp_admin_bar ) {
		if ( defined( 'AP_REMOVE_UI' ) && AP_REMOVE_UI ) {
			return;
		}

		$capability = is_multisite() ? 'manage_network' : 'manage_options';
		if ( ! current_user_can( $capability ) ) {
			return;
		}

		$admin_settings = Admin_Settings::get_instance();
		$options_base   = is_multisite() ? 'settings.php' : 'options-general.php';
		$settings_page  = network_admin_url( $options_base . '?page=aspireupdate-settings' );
		$menu_id        = 'aspireupdate-admin-bar-menu';

		$wp_admin_bar->add_menu(
			[
				'id'     => $menu_id,
				'parent' => 'top-secondary',
				'href'   => $settings_page,
				'title'  => '<span class="ab-icon aspireupdate-icon"></span><span class="screen-reader-text">' . __( 'AspireUpdate', 'aspireupdate' ) . '</span>',
			]
		);

		/* translators: 1: The API host's name. */
		$status_message = __( 'API host: %1$s', 'aspireupdate' );
		$api_host       = $admin_settings->get_setting( 'api_host' );
		switch ( $api_host ) {
			case 'https://api.aspirecloud.net':
				$api_host_name = 'AspireCloud';
				break;
			case 'https://api.aspirecloud.io':
				$api_host_name = 'AspireCloud Bleeding Edge';
				break;
			default:
				$api_host_name = $api_host;
				break;
		}

		$wp_admin_bar->add_menu(
			[
				'id'     => 'aspireupdate-admin-bar-menu-status',
				'parent' => $menu_id,
				'href'   => $settings_page . '#aspireupdate-settings-field-api_host',
				'title'  => sprintf( $status_message, $api_host_name ),
			]
		);

		$wp_admin_bar->add_menu(
			[
				'id'     => 'aspireupdate-admin-bar-menu-settings',
				'parent' => $menu_id,
				'href'   => $settings_page,
				'title'  => __( 'AspireUpdate Settings', 'aspireupdate' ),
			]
		);
	}
}
