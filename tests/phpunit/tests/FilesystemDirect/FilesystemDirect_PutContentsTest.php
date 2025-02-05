<?php
/**
 * Class FilesystemDirect_PutContentsTest
 *
 * @package AspireUpdate
 */

/**
 * Tests for Filesystem_Direct::put_contents()
 *
 * These tests cause constants to be defined.
 * They must run in separate processes and must not preserve global state.
 *
 * @covers \AspireUpdate\Filesystem_Direct::put_contents
 */
class FilesystemDirect_PutContentsTest extends WP_UnitTestCase {
	private static $test_file = '/tmp/aspireupdate-putcontents-test-file.txt';

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

	/**
	 * Test that false is returned when an invalid write mode is provided.
	 */
	public function test_should_return_false_when_the_write_mode_is_invalid() {
		$filesystem = new AP_FakeFilesystem( true, true, true );
		$this->assertFalse( $filesystem->put_contents( self::$test_file, '', false, 'g' ) );
	}

	/**
	 * Test that the log file is created when it doesn't already exist.
	 *
	 * This test causes constants to be defined.
	 * It must run in a separate process and must not preserve global state.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_should_create_log_file_if_it_does_not_already_exist() {
		define( 'FS_CHMOD_FILE', 0644 );

		$filesystem = new AP_FakeFilesystem( false, true, true );
		$filesystem->put_contents( self::$test_file, '', false, 'w' );

		$this->assertFileExists(
			self::$test_file,
			'The log file was not created.'
		);
	}

	/**
	 * Test that false is returned when the path is a directory.
	 */
	public function test_should_return_false_when_the_path_is_a_directory() {
		$test_dir   = '/tmp/aspireupdate-putcontents-test-dir';
		$filesystem = new AP_FakeFilesystem( false, true, true );
		mkdir( $test_dir );

		$this->assertDirectoryExists(
			$test_dir,
			'The test directory was not created.'
		);

		$actual = $filesystem->put_contents( $test_dir, '', false, 'w' );
		rmdir( $test_dir );

		$this->assertFalse(
			$actual,
			'Passing a directory path did not return false.'
		);
	}

	/**
	 * Test that content is appended to the file when the write mode is 'a'.
	 *
	 * This test causes constants to be defined.
	 * It must run in a separate process and must not preserve global state.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_should_append_to_file_when_the_write_mode_is_a() {
		define( 'FS_CHMOD_FILE', 0644 );

		$existing_content = 'This is existing content.';
		$new_content      = PHP_EOL . 'This is new content';
		file_put_contents( self::$test_file, $existing_content );

		$this->assertFileExists(
			self::$test_file,
			'The file was not created before testing.'
		);

		$this->assertSame(
			$existing_content,
			file_get_contents( self::$test_file ),
			'The contents of the test file are not correct before testing.'
		);

		$filesystem = new AP_FakeFilesystem( true, true, true );
		$filesystem->put_contents( self::$test_file, $new_content, false, 'a' );
		$contents = file_get_contents( self::$test_file );

		$this->assertSame(
			$contents,
			$existing_content . $new_content,
			'The contents of the file are unexpected.'
		);

		$this->assertLessThan(
			strpos( $contents, $new_content ),
			strpos( $contents, $existing_content ),
			'The new content was not appended to the file.'
		);
	}
}
