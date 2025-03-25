<?php
/**
 * Class APIRewrite_GetAssetTypeTest
 *
 * @package AspireUpdate
 */

/**
 * Tests for API_Rewrite::get_asset_type()
 *
 * @covers \AspireUpdate\API_Rewrite::get_asset_type
 */
class APIRewrite_GetAssetTypeTest extends WP_UnitTestCase {
	/**
	 * Test that an empty string is returned when the asset type is not handled.
	 */
	public function test_should_return_empty_string_when_the_asset_type_is_not_handled() {
		$api_rewrite      = new AspireUpdate\API_Rewrite( 'https://my.api.org', false, '' );
		$reflected_method = new ReflectionMethod( $api_rewrite, 'get_asset_type' );

		$reflected_method->setAccessible( true );
		$actual = $reflected_method->invoke( $api_rewrite, 'https://my.api.org/translations/1.0/' );
		$reflected_method->setAccessible( false );

		$this->assertSame( '', $actual );
	}
}
