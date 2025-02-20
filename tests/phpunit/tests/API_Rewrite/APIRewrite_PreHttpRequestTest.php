<?php
/**
 * Class APIRewrite_PreHttpRequestTest
 *
 * @package AspireUpdate
 */

/**
 * Tests for API_Rewrite::pre_http_request()
 *
 * @covers \AspireUpdate\API_Rewrite::pre_http_request
 */
class APIRewrite_PreHttpRequestTest extends WP_UnitTestCase {
	/**
	 * Test that no request is performed when the redirected host is an empty string.
	 */
	public function test_should_not_perform_request_when_redirected_host_is_an_empty_string() {
		$request = new MockAction();
		add_filter( 'pre_http_request', [ $request, 'filter' ] );

		$api_rewrite = new AspireUpdate\API_Rewrite( '', false, '' );
		$api_rewrite->pre_http_request( [], [], '' );

		$this->assertSame( 0, $request->get_call_count() );
	}

	/**
	 * Test that no request is performed when the default host and redirected host are the same.
	 */
	public function test_should_not_perform_request_when_default_host_and_redirected_host_are_the_same() {
		$request = new MockAction();
		add_filter( 'pre_http_request', [ $request, 'filter' ] );

		$default_host = $this->get_default_host();
		$api_rewrite  = new AspireUpdate\API_Rewrite( $default_host, false, '' );

		$api_rewrite->pre_http_request( [], [], $default_host );

		$this->assertSame( 0, $request->get_call_count() );
	}

	/**
	 * Test that the request's original SSL verification is respected when SSL is not forcibly disabled.
	 */
	public function test_should_respect_the_original_ssl_verification_when_ssl_is_not_forcibly_disabled() {
		$actual = '';

		add_filter(
			'pre_http_request',
			static function ( $response, $parsed_args ) use ( &$actual ) {
				$actual = $parsed_args['sslverify'];
				return $response;
			},
			PHP_INT_MAX,
			2
		);

		$api_rewrite = new AspireUpdate\API_Rewrite( 'my.api.org', false, '' );
		$api_rewrite->pre_http_request(
			[],
			[ 'sslverify' => 'original_sslverify_value' ],
			$this->get_default_host()
		);

		$this->assertSame( 'original_sslverify_value', $actual );
	}

	/**
	 * Test that disabling SSL is respected.
	 */
	public function test_should_respect_disabling_ssl() {
		$actual = '';

		add_filter(
			'pre_http_request',
			static function ( $response, $parsed_args ) use ( &$actual ) {
				$actual = $parsed_args['sslverify'];
				return $response;
			},
			PHP_INT_MAX,
			2
		);

		$api_rewrite = new AspireUpdate\API_Rewrite( 'my.api.org', true, '' );
		$api_rewrite->pre_http_request(
			[],
			[ 'sslverify' => true ],
			$this->get_default_host()
		);

		$this->assertFalse( $actual );
	}

	/**
	 * Test that the default host is replaced with the redirected host.
	 *
	 * @covers \AspireUpdate\API_Rewrite::add_cache_buster
	 */
	public function test_should_replace_default_host_with_redirected_host() {
		$actual = '';

		add_filter(
			'pre_http_request',
			static function ( $response, $parsed_args, $url ) use ( &$actual ) {
				$actual = $url;
				return $response;
			},
			PHP_INT_MAX,
			3
		);

		$api_rewrite = new AspireUpdate\API_Rewrite( 'my.api.org', true, '' );
		$api_rewrite->pre_http_request( [], [], $this->get_default_host() );

		$this->assertMatchesRegularExpression( '/my\.api\.org\?cache_buster=[0-9]+/', $actual );
	}

	/**
	 * Test that the API Key is added to the Authorization header.
	 *
	 * @covers \AspireUpdate\API_Rewrite::add_authorization_header
	 */
	public function test_should_add_api_key_to_authorization_header_when_present() {
		$actual = [];

		add_filter(
			'pre_http_request',
			static function ( $response, $parsed_args ) use ( &$actual ) {
				$actual = $parsed_args;
				return $response;
			},
			10,
			2
		);

		$api_key     = 'MY_API_KEY';
		$api_rewrite = new AspireUpdate\API_Rewrite( 'my.api.org', true, $api_key );
		$api_rewrite->pre_http_request( [], [], $this->get_default_host() );

		$this->assertIsArray(
			$actual,
			'Parsed arguments is not an array.'
		);

		$this->assertArrayHasKey(
			'headers',
			$actual,
			'The "headers" key is not present.'
		);

		$this->assertIsArray(
			$actual['headers'],
			'The "headers" value is not an array.'
		);

		$this->assertArrayHasKey(
			'Authorization',
			$actual['headers'],
			'There is no authorization header.'
		);

		$this->assertIsString(
			$actual['headers']['Authorization'],
			'The authorization header is not a string.'
		);

		$this->assertSame(
			"Bearer $api_key",
			$actual['headers']['Authorization'],
			'The authorization header is wrong.'
		);
	}

	/**
	 * Test that the Accept header is added for JSON.
	 *
	 * @dataProvider data_accept_json_paths
	 *
	 * @covers \AspireUpdate\API_Rewrite::add_accept_json_header
	 *
	 * @param string $path The path to add to the URL.
	 */
	public function test_should_add_accept_header_with_json( $path ) {
		$actual = [];

		add_filter(
			'pre_http_request',
			static function ( $response, $parsed_args ) use ( &$actual ) {
				$actual = $parsed_args;
				return $response;
			},
			10,
			2
		);

		$api_rewrite = new AspireUpdate\API_Rewrite( 'my.api.org', true, 'MY_API_KEY' );
		$api_rewrite->pre_http_request(
			[],
			[],
			untrailingslashit( $this->get_default_host() ) . $path
		);

		$this->assertIsArray(
			$actual,
			'Parsed arguments is not an array.'
		);

		$this->assertArrayHasKey(
			'headers',
			$actual,
			'The "headers" key is not present.'
		);

		$this->assertIsArray(
			$actual['headers'],
			'The "headers" value is not an array.'
		);

		$this->assertArrayHasKey(
			'Accept',
			$actual['headers'],
			'There is no accept header.'
		);

		$this->assertIsString(
			$actual['headers']['Accept'],
			'The accept header is not a string.'
		);

		$this->assertSame(
			'application/json',
			$actual['headers']['Accept'],
			'The accept header is wrong.'
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_accept_json_paths() {
		return [
			'a PHP file'                                  => [
				'path' => '/files/file.php',
			],
			'a PHP file with a period'                    => [
				'path' => '/files/file.file.php',
			],
			'no extension, a period and a trailing slash' => [
				'path' => '/files/file.file/',
			],
			'no extension and a trailing slash'           => [
				'path' => '/files/file/',
			],
		];
	}

	/**
	 * Test that the Accept header is not added for JSON when a non-PHP file
	 * is requested.
	 *
	 * @dataProvider data_do_not_accept_json_paths
	 *
	 * @covers \AspireUpdate\API_Rewrite::add_accept_json_header
	 *
	 * @param string $path The path to add to the URL.
	 */
	public function test_should_not_add_accept_header_with_json( $path ) {
		$actual = [];

		add_filter(
			'pre_http_request',
			static function ( $response, $parsed_args ) use ( &$actual ) {
				$actual = $parsed_args;
				return $response;
			},
			10,
			2
		);

		$api_rewrite = new AspireUpdate\API_Rewrite( 'my.api.org', true, 'MY_API_KEY' );
		$api_rewrite->pre_http_request(
			[],
			[],
			$this->get_default_host() . $path
		);

		$this->assertIsArray(
			$actual,
			'Parsed arguments is not an array.'
		);

		$this->assertArrayHasKey(
			'headers',
			$actual,
			'The "headers" key is not present.'
		);

		$this->assertIsArray(
			$actual['headers'],
			'The "headers" value is not an array.'
		);

		$this->assertArrayNotHasKey(
			'Accept',
			$actual['headers'],
			'There should not be an accept header.'
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_do_not_accept_json_paths() {
		return [
			'a ZIP extension'   => [
				'path' => '/file.zip',
			],
			'an HTML extension' => [
				'path' => '/index.html',
			],
			'a PDF extension'   => [
				'path' => '/file.pdf',
			],
			'a JPG extension'   => [
				'path' => '/file.jpg',
			],
			'a PNG extension'   => [
				'path' => '/file.png',
			],
			'a GIF extension'   => [
				'path' => '/file.gif',
			],
			'a WEBP extension'  => [
				'path' => '/file.webp',
			],
			'a WEBM extension'  => [
				'path' => '/file.webm',
			],
			'no trailing slash' => [
				'path' => '/file',
			],
		];
	}

	/**
	 * Test that the headers array is created if it does not already exist when adding
	 * the Accept header.
	 *
	 * @covers \AspireUpdate\API_Rewrite::add_accept_json_header
	 */
	public function test_should_create_headers_array_if_it_does_not_already_exist_when_adding_the_accept_header() {
		$actual = [];

		add_filter(
			'pre_http_request',
			static function ( $response, $parsed_args ) use ( &$actual ) {
				$actual = $parsed_args;
				return $response;
			},
			10,
			2
		);

		// No API key ensures no Authorization header will be already set.
		$api_rewrite = new AspireUpdate\API_Rewrite( 'my.api.org', true, '' );
		$api_rewrite->pre_http_request(
			[],
			[],
			$this->get_default_host() . '/file.php'
		);

		$this->assertIsArray(
			$actual,
			'Parsed arguments is not an array.'
		);

		$this->assertArrayHasKey(
			'headers',
			$actual,
			'The "headers" key is not present.'
		);

		$this->assertIsArray(
			$actual['headers'],
			'The "headers" value is not an array.'
		);
	}

	/**
	 * Test that a WP_Error object is returned for non-200 HTTP responses.
	 */
	public function test_should_return_wp_error_for_non_200_responses() {
		add_filter(
			'pre_http_request',
			static function () {
				return [
					'response' => [
						'code'    => 401,
						'message' => 'Unauthorized.',
					],
				];
			},
			10,
			2
		);

		$api_rewrite = new AspireUpdate\API_Rewrite( 'my.api.org', true, '' );
		$actual      = $api_rewrite->pre_http_request(
			[],
			[],
			$this->get_default_host() . '/file.php'
		);

		$this->assertInstanceOf(
			'WP_Error',
			$actual,
			'A WP_Error object was not returned.'
		);

		$this->assertSame(
			'failed_request',
			$actual->get_error_code(),
			'The wrong error code was returned.'
		);
	}

	/**
	 * Gets the default host.
	 *
	 * @return string The default host.
	 */
	private function get_default_host() {
		static $default_host;

		if ( ! $default_host ) {
			$reflection   = new ReflectionClass( 'AspireUpdate\API_Rewrite' );
			$default_host = $reflection->getDefaultProperties()['default_host'];
		}

		return $default_host;
	}
}
