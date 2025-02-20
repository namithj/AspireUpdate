<?php
/**
 * The Class for Rewriting the Default API.
 *
 * @package aspire-update
 */

namespace AspireUpdate;

/**
 * The Class for Rewriting the Default API.
 */
class API_Rewrite {
	/**
	 * The default API Host.
	 *
	 * @var string
	 */
	private $default_host = 'api.wordpress.org';

	/**
	 * The redirected API Host.
	 *
	 * @var string
	 */
	private $redirected_host;

	/**
	 * Disable SSL.
	 *
	 * @var boolean
	 */
	private $disable_ssl;

	/**
	 * API Key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * The Constructor.
	 *
	 * @param string  $redirected_host The host to redirect to.
	 * @param boolean $disable_ssl     Disable SSL.
	 * @param string  $api_key         The API key to use with the host.
	 */
	public function __construct( $redirected_host, $disable_ssl, $api_key ) {
		if ( 'debug' === $redirected_host ) {
			$this->redirected_host = $this->default_host;
		} else {
			$this->redirected_host = strtolower( $redirected_host );
		}
		$this->disable_ssl = $disable_ssl;
		$this->api_key     = $api_key;
		add_filter( 'pre_http_request', [ $this, 'pre_http_request' ], 10, 3 );
	}

	/**
	 * Add Authorization Header if API Key found.
	 *
	 * @param array $args The HTTP request arguments.
	 *
	 * @return array $args The modified HTTP request arguments.
	 */
	private function add_authorization_header( $args ) {
		if ( '' !== $this->api_key ) {
			Debug::log_string( __( 'API Key Authorization header added.', 'aspireupdate' ) );
			if ( ! isset( $args['headers'] ) ) {
				$args['headers'] = [];
			}
			$args['headers']['Authorization'] = 'Bearer ' . $this->api_key;
		}
		return $args;
	}

	/**
	 * Add Accept JSON header if the request is not for a file asset.
	 *
	 * @param array $args The HTTP request arguments.
	 *
	 * @return array $args The modified HTTP request arguments.
	 */
	private function add_accept_json_header( $args, $url ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );
		// Check if the URL points to a .php file or has no extension.
		if ( preg_match( '#/[^/]+(\.php|/)$#', $path ) ) {
			Debug::log_string( __( 'Accept JSON Header added for API calls.', 'aspireupdate' ) );
			if ( ! isset( $args['headers'] ) ) {
				$args['headers'] = [];
			}
			$args['headers']['Accept'] = 'application/json';
		}
		return $args;
	}

	/**
	 * Adding cache buster parameter for AC beta test.  Will remove this after Beta.
	 *
	 * @param string $url The URL.
	 *
	 * @return string $url The updated URL.
	 */
	private function add_cache_buster( $url ) {
		Debug::log_string( __( 'Cache Buster Added to URL', 'aspireupdate' ) );
		return add_query_arg( 'cache_buster', time(), $url );
	}

	/**
	 * Rewrite the API End points.
	 *
	 * @param mixed  $response The response for the request.
	 * @param array  $parsed_args The arguments for the request.
	 * @param string $url The URL for the request.
	 *
	 * @return mixed The response or false.
	 */
	public function pre_http_request( $response, $parsed_args, $url ) {
		if (
			isset( $this->default_host ) &&
			( '' !== $this->default_host ) &&
			isset( $this->redirected_host ) &&
			( '' !== $this->redirected_host )
		) {
			if ( false !== strpos( $url, $this->default_host ) ) {
				Debug::log_string( __( 'Default API Found: ', 'aspireupdate' ) . $url );

				if ( $this->default_host !== $this->redirected_host ) {
					if ( $this->disable_ssl ) {
						Debug::log_string( __( 'SSL Verification Disabled', 'aspireupdate' ) );
						$parsed_args['sslverify'] = false;
					}

					$parsed_args = $this->add_authorization_header( $parsed_args );
					$parsed_args = $this->add_accept_json_header( $parsed_args, $url );
					$url         = $this->add_cache_buster( $url );

					$updated_url = str_replace( $this->default_host, $this->redirected_host, $url );
					Debug::log_string( __( 'API Rerouted to: ', 'aspireupdate' ) . $updated_url );

					Debug::log_request( $parsed_args );

					/**
					 * Temporarily Unhook Filter to prevent recursion.
					 */
					remove_filter( 'pre_http_request', [ $this, 'pre_http_request' ] );
					$response = wp_remote_request( $updated_url, $parsed_args );
					add_filter( 'pre_http_request', [ $this, 'pre_http_request' ], 10, 3 );

					Debug::log_response( $response );

					if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
						$message = wp_remote_retrieve_response_message( $response );
						Debug::log_string( $message );
						return new \WP_Error( 'failed_request', $message );
					}

					return $response;

				}
			}
		}
		return $response;
	}
}
