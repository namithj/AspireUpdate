<?php
/**
 * The Class for Managing Several Instances of potentially unwanted Callbacks Home.
 *
 * @package aspire-update
 */

namespace AspireUpdate;

/**
 * The Class for Admin Settings Page and functions to access Settings Values.
 */
class Peekaboo {
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
	}

	/**
	 * Get the singleton instance of the Peekaboo class.
	 *
	 * @return Peekaboo The singleton instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * This method hooks into WordPress actions to disable the Dashboard News and Events widget
	 * to prevent unnecessary calls to WordPress API Host Server.
	 */
	public function disable_dashboard_news_events_widget() {
		add_action(
			'wp_dashboard_setup',
			function () {
				remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
			},
			99
		);
	}

	/**
	 * This method hooks into WordPress actions to disable the oEmbed discovery endpoint.
	 */
	public function disable_oembed_rest_discovery() {
		/**
		 * Disable the oEmbed discovery endpoint.
		 */
		remove_action( 'rest_api_init', 'wp_oembed_register_route' );

		/**
		 * Disable the oEmbed discovery links in the head.
		 */
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		remove_action( 'wp_head', 'wp_maybe_enqueue_oembed_host_js' );
	}

	/**
	 * This method hooks into WordPress filters to disable remote core version checks
	 * and prevent unnecessary calls to WordPress API Host Server.
	 */
	public function disable_remote_core_version_check() {
		/**
		 * Disable the core version check to prevent remote calls to WordPress API Host Server.
		 */
		add_filter( 'pre_site_transient_update_core', '__return_null' );

		/**
		 * Disable the core update check to prevent remote calls to WordPress API Host Server.
		 */
		remove_action( 'init', 'wp_version_check' );

		/**
		 * Disable the core update check on admin_init to prevent remote calls to WordPress API Host Server.
		 */
		remove_action( 'admin_init', '_maybe_update_core' );
	}

	/**
	 * This method hooks into WordPress filters to disable remote plugin and theme update checks
	 * and prevent unnecessary calls to WordPress API Host Server.
	 */
	public function disable_remote_plugin_update_check() {
		/**
		 * Disable the plugin update check to prevent remote calls to WordPress API Host Server.
		 */
		add_filter( 'pre_site_transient_update_plugins', '__return_null' );

		/**
		 * Disable the plugin update check on admin_init to prevent remote calls to WordPress API Host Server.
		 */
		remove_action( 'admin_init', '_maybe_update_plugins' );

		/**
		 * Disable the plugin update check on load-update-core.php to prevent remote calls to WordPress API Host Server.
		 */
		remove_action( 'load-update-core.php', 'wp_update_plugins' );
	}

	/**
	 * This method hooks into WordPress filters to disable remote theme update checks
	 * and prevent unnecessary calls to WordPress API Host Server.
	 */
	public function disable_remote_theme_update_check() {
		/**
		 * Disable the theme update check to prevent remote calls to WordPress API Host Server.
		 */
		add_filter( 'pre_site_transient_update_themes', '__return_null' );

		/**
		 * Disable the theme update check on admin_init to prevent remote calls to WordPress API Host Server.
		 */
		remove_action( 'admin_init', '_maybe_update_themes' );

		/**
		 * Disable the theme update check on load-update-core.php to prevent remote calls to WordPress API Host Server.
		 */
		remove_action( 'load-update-core.php', 'wp_update_themes' );
	}

	/**
	 * This method hooks into WordPress filters to disable fetching avatars from remote services
	 * and instead use a local avatar image.
	 */
	public function disable_remote_avatar_services() {
		/**
		 * Disables remote avatar fetching (e.g. Gravatar) and replaces it with a local image.
		 */
		add_filter( 'pre_get_avatar', [ $this, 'pre_get_avatar' ], 10, 5 );

		/**
		 * Removes Gravatar-related options from the avatar settings dropdown in the admin area.
		 */
		add_filter( 'avatar_defaults', [ $this, 'avatar_defaults' ] );

		/**
		 * Ensures the use of a local avatar URL instead of fetching from a remote server.
		 */
		add_filter( 'get_avatar_url', [ $this, 'get_avatar_url' ] );

		/**
		 * Removes DNS prefetch hints to third-party domains like gravatar.com to prevent implicit tracking.
		 */
		add_filter( 'wp_resource_hints', [ $this, 'wp_resource_hints' ], 10, 2 );
	}

	/**
	 * Callback to disable fetching avatars from remote services.
	 *
	 * @param string $avatar The avatar HTML.
	 * @param mixed  $id_or_email The user ID or email.
	 * @param string $args  Arguments passed to get_avatar_url(), after processing.
	 * @return string The modified avatar HTML.
	 */
	public function pre_get_avatar( $avatar, $id_or_email, $args = [] ) {
		$url        = plugin_dir_url( __DIR__ ) . 'assets/images/default-avatar.jpg';
		$url2x      = plugin_dir_url( __DIR__ ) . 'assets/images/default-avatar@2x.jpg';
		$class      = [ 'avatar', 'custom-avatar-class', 'avatar-' . ( isset( $args['size'] ) ? intval( $args['size'] ) : 96 ) ];
		$extra_attr = 'loading="lazy" decoding="async" sizes="32x32"';
		$avatar     = sprintf(
			"<img alt='%s' src='%s' srcset='%s' class='%s' height='%d' width='%d' %s />",
			esc_attr( isset( $args['alt'] ) ? $args['alt'] : __( 'Default Avatar', 'aspire-update' ) ),
			esc_url( $url ),
			esc_url( $url2x ) . ' 2x',
			esc_attr( implode( ' ', $class ) ),
			isset( $args['height'] ) ? (int) $args['height'] : ( isset( $args['size'] ) ? (int) $args['size'] : 96 ),
			isset( $args['width'] ) ? (int) $args['width'] : ( isset( $args['size'] ) ? (int) $args['size'] : 96 ),
			$extra_attr
		);
		return $avatar;
	}

	/**
	 * Callback to remove Gravatar-related options from the avatar settings dropdown.
	 *
	 * @param array $avatar_defaults The current avatar defaults.
	 * @return array The modified avatar defaults.
	 */
	public function avatar_defaults( $avatar_defaults ) {
		unset( $avatar_defaults['mystery'] );
		unset( $avatar_defaults['blank'] );
		unset( $avatar_defaults['gravatar_default'] );
		unset( $avatar_defaults['retro'] );
		unset( $avatar_defaults['robohash'] );
		unset( $avatar_defaults['monsterid'] );
		unset( $avatar_defaults['wavatar'] );
		unset( $avatar_defaults['identicon'] );
		return $avatar_defaults;
	}

	/**
	 * Callback to ensure the use of a local avatar URL.
	 *
	 * @param string $avatar_url The avatar URL.
	 * @return string The modified avatar URL.
	 */
	public function get_avatar_url( $avatar_url ) {
		$avatar_url = plugin_dir_url( __DIR__ ) . 'assets/images/default-avatar.jpg';
		return $avatar_url;
	}

	/**
	 * Removes gravatar.com DNS prefetch entries from the <head>.
	 *
	 * @param array $urls List of URLs.
	 * @param string $relation_type Hint type (e.g., 'dns-prefetch').
	 * @return array Filtered URLs.
	 */
	public function wp_resource_hints( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			return array_filter(
				$urls,
				function ( $url ) {
					return strpos( $url, 'gravatar.com' ) === false;
				}
			);
		}
		return $urls;
	}
}
