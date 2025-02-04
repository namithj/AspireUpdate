<?php
/**
 * Class AdminSettings_UpdateSettingsTest
 *
 * @package AspireUpdate
 */

/**
 * Tests for Admin_Settings::update_settings()
 *
 * @covers \AspireUpdate\Admin_Settings::update_settings
 */
class AdminSettings_UpdateSettingsTest extends AdminSettings_UnitTestCase {
	/**
	 * Test that settings are not updated when the user does not have the required capability.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_should_not_update_settings_when_the_user_does_not_have_the_required_capability() {
		wp_set_current_user( self::$editor_id );
		$_POST['_wpnonce']              = wp_create_nonce( self::$options_page );
		$_POST['option_page']           = self::$option_name;
		$_POST['aspireupdate_settings'] = [ 'api_host' => 'the.option.value' ];

		$settings = get_site_option( self::$option_name, false );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame(
			$settings,
			get_site_option( self::$option_name, false )
		);
	}

	/**
	 * Test that settings are not updated when $_POST['_wpnonce'] is not set.
	 */
	public function test_should_not_update_settings_when_post_wpnonce_is_not_set() {
		unset( $_POST['_wpnonce'] );
		$_POST['option_page']           = self::$option_name;
		$_POST['aspireupdate_settings'] = [ 'api_host' => 'the.option.value' ];

		$settings = get_site_option( self::$option_name, false );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame(
			$settings,
			get_site_option( self::$option_name, false )
		);
	}

	/**
	 * Test that a redirect is not performed when $_POST['_wpnonce'] is not set.
	 */
	public function test_should_not_redirect_when_post_wpnonce_is_not_set() {
		unset( $_POST['_wpnonce'] );
		$_POST['option_page']           = self::$option_name;
		$_POST['aspireupdate_settings'] = [ 'api_host' => 'the.option.value' ];

		$redirect = new MockAction();
		add_filter( 'wp_redirect', [ $redirect, 'filter' ] );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame( 0, $redirect->get_call_count() );
	}

	/**
	 * Test that settings are not updated when nonce verification fails.
	 */
	public function test_should_not_update_settings_when_nonce_verification_fails() {
		$_POST['_wpnonce']              = 'incorrect_value';
		$_POST['option_page']           = self::$option_name;
		$_POST['aspireupdate_settings'] = [ 'api_host' => 'the.option.value' ];

		$settings = get_site_option( self::$option_name, false );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame(
			$settings,
			get_site_option( self::$option_name, false )
		);
	}

	/**
	 * Test that a redirect is not performed when nonce verification fails.
	 */
	public function test_should_not_redirect_when_nonce_verification_fails() {
		$_POST['_wpnonce']              = 'incorrect_value';
		$_POST['option_page']           = self::$option_name;
		$_POST['aspireupdate_settings'] = [ 'api_host' => 'the.option.value' ];

		$redirect = new MockAction();
		add_filter( 'wp_redirect', [ $redirect, 'filter' ] );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame( 0, $redirect->get_call_count() );
	}

	/**
	 * Test that settings are not updated when $_POST['options_page'] is not set.
	 */
	public function test_should_not_update_settings_when_post_optionspage_is_not_set() {
		$_POST['_wpnonce'] = wp_create_nonce( self::$options_page );
		unset( $_POST['option_page'] );
		$_POST['aspireupdate_settings'] = [ 'api_host' => 'the.option.value' ];

		$settings = get_site_option( self::$option_name, false );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame(
			$settings,
			get_site_option( self::$option_name, false )
		);
	}

	/**
	 * Test that a redirect is not performed when $_POST['options_page'] is not set.
	 */
	public function test_should_not_redirect_when_post_optionspage_is_not_set() {
		$_POST['_wpnonce'] = wp_create_nonce( self::$options_page );
		unset( $_POST['option_page'] );
		$_POST['aspireupdate_settings'] = [ 'api_host' => 'the.option.value' ];

		$redirect = new MockAction();
		add_filter( 'wp_redirect', [ $redirect, 'filter' ] );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame( 0, $redirect->get_call_count() );
	}

	/**
	 * Test that settings are not updated when $_POST['options_page'] is set to an incorrect value.
	 */
	public function test_should_not_update_settings_when_post_optionspage_is_set_to_an_incorrect_value() {
		$_POST['_wpnonce']              = wp_create_nonce( self::$options_page );
		$_POST['option_page']           = 'incorrect_value';
		$_POST['aspireupdate_settings'] = [ 'api_host' => 'the.option.value' ];

		$settings = get_site_option( self::$option_name, false );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame(
			$settings,
			get_site_option( self::$option_name, false )
		);
	}

	/**
	 * Test that a redirect is not performed when $_POST['options_page'] is set to an incorrect value.
	 */
	public function test_should_not_redirect_when_post_optionspage_is_set_to_an_incorrect_value() {
		$_POST['_wpnonce']              = wp_create_nonce( self::$options_page );
		$_POST['option_page']           = 'incorrect_value';
		$_POST['aspireupdate_settings'] = [ 'api_host' => 'the.option.value' ];

		$redirect = new MockAction();
		add_filter( 'wp_redirect', [ $redirect, 'filter' ] );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame( 0, $redirect->get_call_count() );
	}

	/**
	 * Test that settings are not updated when $_POST['aspireupdate_settings'] is not set.
	 */
	public function test_should_not_update_settings_when_post_aspireupdatesettings_is_not_set() {
		$_POST['_wpnonce']    = wp_create_nonce( self::$options_page );
		$_POST['option_page'] = self::$option_name;
		unset( $_POST['aspireupdate_settings'] );

		$settings = get_site_option( self::$option_name, false );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame(
			$settings,
			get_site_option( self::$option_name, false )
		);
	}

	/**
	 * Test that a redirect is not performed when $_POST['aspireupdate_settings'] is not set.
	 */
	public function test_should_not_redirect_when_post_aspireupdatesettings_is_not_set() {
		$_POST['_wpnonce']    = wp_create_nonce( self::$options_page );
		$_POST['option_page'] = self::$option_name;
		unset( $_POST['aspireupdate_settings'] );

		$redirect = new MockAction();
		add_filter( 'wp_redirect', [ $redirect, 'filter' ] );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame( 0, $redirect->get_call_count() );
	}

	/**
	 * Test that settings are updated when update requirements are met.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_should_update_settings_when_update_requirements_are_met() {
		$_POST['_wpnonce']              = wp_create_nonce( self::$options_page );
		$_POST['option_page']           = self::$option_name;
		$_POST['aspireupdate_settings'] = [ 'api_host' => 'the.option.value' ];

		if ( is_multisite() ) {
			grant_super_admin( wp_get_current_user()->ID );
		}

		delete_site_option( self::$option_name );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();
		$actual = get_site_option( self::$option_name, false );

		$this->assertIsArray(
			$actual,
			'The settings are not an array.'
		);

		$this->assertArrayHasKey(
			'api_host',
			$actual,
			'There is no "api_host" setting.'
		);

		$this->assertSame(
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$_POST['aspireupdate_settings']['api_host'],
			$actual['api_host'],
			'The settings were not updated.'
		);
	}

	/**
	 * Test that a redirect is performed when update requirements are met.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_should_redirect_when_update_requirements_are_met() {
		$_POST['_wpnonce']              = wp_create_nonce( self::$options_page );
		$_POST['option_page']           = self::$option_name;
		$_POST['aspireupdate_settings'] = [ 'api_host' => 'the.option.value' ];

		if ( is_multisite() ) {
			grant_super_admin( wp_get_current_user()->ID );
		}

		$redirect = new MockAction();
		add_filter( 'wp_redirect', [ $redirect, 'filter' ] );

		$admin_settings = new AspireUpdate\Admin_Settings();
		$admin_settings->update_settings();

		$this->assertSame( 1, $redirect->get_call_count() );
	}
}
