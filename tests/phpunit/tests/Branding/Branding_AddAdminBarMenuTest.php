<?php
/**
 * Class Branding_AddAdminBarMenuTest
 *
 * @package AspireUpdate
 */

require_once ABSPATH . 'wp-includes/class-wp-admin-bar.php';

/**
 * Tests for Branding::add_admin_bar_menu()
 *
 * These tests cause constants to be defined.
 * They must run in separate processes and must not preserve global state.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @covers \AspireUpdate\Branding::add_admin_bar_menu
 */
class Branding_AddAdminBarMenuTest extends WP_UnitTestCase {
	/**
	 * Test that the main admin bar menu is added.
	 */
	public function test_should_add_main_admin_bar_menu() {
		$this->set_user_to_administrator();

		$wp_admin_bar = new WP_Admin_Bar();
		$branding     = new AspireUpdate\Branding();

		$wp_admin_bar->initialize();
		$branding->add_admin_bar_menu( $wp_admin_bar );

		$actual = $wp_admin_bar->get_nodes();

		$this->assertIsArray( $actual, 'There are no admin bar nodes.' );
		$this->assertArrayHasKey(
			'aspireupdate-admin-bar-menu',
			$actual,
			'The main admin bar menu is not present.'
		);
	}

	/**
	 * Test that the status menu item is added.
	 */
	public function test_should_add_status_menu_item() {
		$this->set_user_to_administrator();

		$wp_admin_bar = new WP_Admin_Bar();
		$branding     = new AspireUpdate\Branding();

		$wp_admin_bar->initialize();
		$branding->add_admin_bar_menu( $wp_admin_bar );

		$actual = $wp_admin_bar->get_nodes();

		$this->assertIsArray( $actual, 'There are no admin bar nodes.' );

		$this->assertArrayHasKey(
			'aspireupdate-admin-bar-menu-status',
			$actual,
			'The status menu item is not present.'
		);

		$this->assertIsObject(
			$actual['aspireupdate-admin-bar-menu-status'],
			'The status menu item is not an object.'
		);

		$this->assertSame(
			'aspireupdate-admin-bar-menu',
			$actual['aspireupdate-admin-bar-menu-status']->parent,
			'The status menu item is not a child of the main menu.'
		);
	}

	/**
	 * Test that the settings link is added.
	 */
	public function test_should_add_settings_link() {
		$this->set_user_to_administrator();

		$wp_admin_bar = new WP_Admin_Bar();
		$branding     = new AspireUpdate\Branding();

		$wp_admin_bar->initialize();
		$branding->add_admin_bar_menu( $wp_admin_bar );

		$actual = $wp_admin_bar->get_nodes();

		$this->assertIsArray( $actual, 'There are no admin bar nodes.' );
		$this->assertArrayHasKey(
			'aspireupdate-admin-bar-menu-settings',
			$actual,
			'The settings link is not present.'
		);
	}

	/**
	 * Test that the main admin bar menu is not added when the user lacks appropriate capabilities.
	 */
	public function test_should_not_add_main_admin_bar_menu_when_the_user_lacks_appropriate_capabilities() {
		$wp_admin_bar = new WP_Admin_Bar();
		$branding     = new AspireUpdate\Branding();

		$wp_admin_bar->initialize();
		$branding->add_admin_bar_menu( $wp_admin_bar );

		$actual = $wp_admin_bar->get_nodes();

		$this->assertIsNotArray( $actual, 'There are admin bar nodes.' );
	}

	/**
	 * Test that the main admin bar menu is not added when AP_REMOVE_UI is set to true.
	 */
	public function test_should_not_add_main_admin_bar_menu_when_ap_remove_ui_is_true() {
		// Prevent the menu from being added.
		define( 'AP_REMOVE_UI', true );

		$this->set_user_to_administrator();

		$wp_admin_bar = new WP_Admin_Bar();
		$branding     = new AspireUpdate\Branding();

		$wp_admin_bar->initialize();
		$branding->add_admin_bar_menu( $wp_admin_bar );

		$actual = $wp_admin_bar->get_nodes();

		$this->assertIsNotArray( $actual, 'There are admin bar nodes.' );
	}

	/**
	 * Test that the status menu item's content is correctly dependent on the api_host option.
	 *
	 * @dataProvider data_api_hosts_and_expected_names
	 *
	 * @param string $api_host The API host to test.
	 * @param string $expected_name The expected name of the API host.
	 */
	public function test_should_set_status_menu_item_content_depending_on_the_api_host_option( $api_host, $expected_name ) {
		define( 'AP_HOST', $api_host );

		$this->set_user_to_administrator();

		$wp_admin_bar = new WP_Admin_Bar();
		$branding     = new AspireUpdate\Branding();

		$wp_admin_bar->initialize();
		$branding->add_admin_bar_menu( $wp_admin_bar );

		$actual = $wp_admin_bar->get_nodes();

		$this->assertIsArray( $actual, 'There are no admin bar nodes.' );

		$this->assertArrayHasKey(
			'aspireupdate-admin-bar-menu-status',
			$actual,
			'The status menu item is not present.'
		);

		$this->assertIsObject(
			$actual['aspireupdate-admin-bar-menu-status'],
			'The status menu item is not an object.'
		);

		$this->assertObjectHasProperty(
			'title',
			$actual['aspireupdate-admin-bar-menu-status'],
			'The status menu item does not have a title property.'
		);

		$this->assertSame(
			"API host: {$expected_name}",
			$actual['aspireupdate-admin-bar-menu-status']->title,
			'The status menu item title does not match the expected name.'
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_api_hosts_and_expected_names() {
		return [
			'AspireCloud' => [
				'api_host'      => 'https://api.aspirecloud.net',
				'expected_name' => 'AspireCloud',
			],
			'Other'       => [
				'api_host'      => 'https://my.api.org',
				'expected_name' => 'https://my.api.org',
			],
		];
	}

	/**
	 * Set the current user to an administrator.
	 *
	 * Grants super admin privileges when running multisite.
	 *
	 * @return void
	 */
	private function set_user_to_administrator() {
		$user = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );

		if ( is_multisite() ) {
			grant_super_admin( $user );
		}
	}
}
