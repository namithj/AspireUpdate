<?php
/**
 * Abstract base test class for \AspireUpdate\Admin_Settings.
 *
 * All \AspireUpdate\Admin_Settings unit tests should inherit from this class.
 */
abstract class AdminSettings_UnitTestCase extends WP_UnitTestCase {
	/**
	 * The user ID of an administrator.
	 *
	 * @var int
	 */
	protected static $admin_id;

	/**
	 * The user ID of an editor.
	 *
	 * @var int
	 */
	protected static $editor_id;

	/**
	 * The Name of the Option.
	 *
	 * @var string
	 */
	protected static $option_name = 'aspireupdate_settings';

	/**
	 * The Slug of the Option's page.
	 *
	 * @var string
	 */
	protected static $options_page = 'aspireupdate-settings';

	/**
	 * Creates administrator and editor users before any tests run.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::$admin_id  = self::factory()->user->create( [ 'role' => 'administrator' ] );
		self::$editor_id = self::factory()->user->create( [ 'role' => 'editor' ] );
	}

	/**
	 * Deletes settings and sets the current user before each test runs.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		delete_site_option( self::$option_name );
		wp_set_current_user( self::$admin_id );
	}
}
