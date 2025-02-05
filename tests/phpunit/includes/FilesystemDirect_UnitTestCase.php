<?php
/**
 * Abstract base test class for \AspireUpdate\Filesystem_Direct.
 *
 * All \AspireUpdate\Filesystem_Direct unit tests should inherit from this class.
 */
abstract class FilesystemDirect_UnitTestCase extends WP_UnitTestCase {
	protected static $test_file = '/tmp/aspireupdate-test-file.txt';

	/**
	 * Remove the test file should it exist before any tests run.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();

		if ( file_exists( self::$test_file ) ) {
			unlink( self::$test_file );
		}
	}

	/**
	 * Remove the test file should it exist after each test runs.
	 */
	public function tear_down() {
		if ( file_exists( self::$test_file ) ) {
			unlink( self::$test_file );
		}

		parent::tear_down();
	}
}
