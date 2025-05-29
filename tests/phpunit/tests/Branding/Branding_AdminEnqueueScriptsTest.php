<?php
/**
 * Class Branding_AdminEnqueueScriptsTest
 *
 * @package AspireUpdate
 */

/**
 * Tests for Branding::admin_enqueue_scripts()
 *
 * @covers \AspireUpdate\Branding::admin_enqueue_scripts
 */
class Branding_AdminEnqueueScriptsTest extends WP_UnitTestCase {
	/**
	 * Dequeue the stylesheet after each test runs.
	 *
	 * @return void
	 */
	public function tear_down() {
		wp_dequeue_style( 'aspire_update_settings_css' );

		parent::tear_down();
	}

	/**
	 * Test that the stylesheet is enqueued.
	 */
	public function test_should_enqueue_style() {
		$branding = new AspireUpdate\Branding();
		$branding->admin_enqueue_scripts();
		$this->assertTrue( wp_style_is( 'aspire_update_settings_css' ) );
	}

	/**
	 * Test that the stylesheet is not enqueued when AP_REMOVE_UI is set to true.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_should_not_enqueue_style_when_ap_remove_ui_is_true() {
		// Prevent the notice from being displayed.
		define( 'AP_REMOVE_UI', true );

		$hook     = is_multisite() ? 'plugins-network' : 'plugins';
		$branding = new AspireUpdate\Branding();
		$branding->admin_enqueue_scripts( $hook );
		$this->assertFalse( wp_style_is( 'aspire_update_settings_css' ) );
	}
}
