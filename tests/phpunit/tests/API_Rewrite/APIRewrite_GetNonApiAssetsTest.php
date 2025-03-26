<?php
/**
 * Class APIRewrite_GetNonApiAssetsTest
 *
 * @package AspireUpdate
 */

/**
 * Tests for API_Rewrite::get_non_api_assets()
 *
 * @covers \AspireUpdate\API_Rewrite::get_non_api_assets
 */
class APIRewrite_GetNonApiAssetsTest extends WP_UnitTestCase {
	/**
	 * Test that an empty array is returned when the asset type is not supported.
	 */
	public function test_should_return_empty_array_when_the_asset_type_is_not_supported() {
		$api_rewrite      = new AspireUpdate\API_Rewrite( 'https://my.api.org', false, '' );
		$reflected_method = new ReflectionMethod( $api_rewrite, 'get_non_api_assets' );

		$reflected_method->setAccessible( true );
		$actual = $reflected_method->invoke( $api_rewrite, 'translations' );
		$reflected_method->setAccessible( false );

		$this->assertSame( [], $actual );
	}
}
