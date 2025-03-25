<?php
/**
 * Class APIRewrite_GetRequestTypeTest
 *
 * @package AspireUpdate
 */

/**
 * Tests for API_Rewrite::get_request_type()
 *
 * @covers \AspireUpdate\API_Rewrite::get_request_type
 */
class APIRewrite_GetRequestTypeTest extends WP_UnitTestCase {
	/**
	 * Test that an empty string is returned when the request type is not handled.
	 */
	public function test_should_return_empty_string_when_the_request_type_is_not_handled() {
		$api_rewrite      = new AspireUpdate\API_Rewrite( 'https://my.api.org', false, '' );
		$reflected_method = new ReflectionMethod( $api_rewrite, 'get_request_type' );

		$reflected_method->setAccessible( true );
		$actual = $reflected_method->invoke( $api_rewrite, 'https://my.api.org/stats/1.0/' );
		$reflected_method->setAccessible( false );

		$this->assertSame( '', $actual );
	}
}
