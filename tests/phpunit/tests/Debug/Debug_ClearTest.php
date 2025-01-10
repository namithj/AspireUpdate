<?php
/**
 * Class Debug_ClearTest
 *
 * @package AspireUpdate
 */

/**
 * Tests for Debug::clear()
 *
 * These tests cause constants to be defined.
 * They must run in separate processes and must not preserve global state.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @covers \AspireUpdate\Debug::clear
 */
class Debug_ClearTest extends Debug_UnitTestCase {
	/**
	 * Test that a WP_Error object is returned when the log file doesn't exist.
	 *
	 * @covers \AspireUpdate\Debug::init_filesystem
	 * @covers \AspireUpdate\Debug::get_file_path
	 */
	public function test_should_return_wp_error_when_log_file_does_not_exist() {
		$this->assertWPError(
			AspireUpdate\Debug::clear(),
			'A WP_Error object was not returned.'
		);

		$this->assertFileDoesNotExist(
			self::$log_file,
			'The log file was created.'
		);
	}

	/**
	 * Test that a WP_Error object is returned when the log file isn't writable.
	 *
	 * @covers \AspireUpdate\Debug::init_filesystem
	 * @covers \AspireUpdate\Debug::get_file_path
	 */
	public function test_should_return_wp_error_when_log_file_is_not_writable() {
		file_put_contents( self::$log_file, '' );

		// Replace the filesystem object.
		self::$reflection->setStaticPropertyValue( 'filesystem', $this->get_fake_filesystem( true, true, false ) );

		$actual = AspireUpdate\Debug::clear();

		$this->assertWPError(
			$actual,
			'A WP_Error was not returned.'
		);
	}

	/**
	 * Test that the log file is cleared.
	 */
	public function test_should_clear_log_file() {
		file_put_contents(
			self::$log_file,
			"First line\r\nSecond line\r\nThird line"
		);

		$this->assertFileExists(
			self::$log_file,
			'The log file was not created before testing.'
		);

		AspireUpdate\Debug::clear();

		$this->assertFileExists(
			self::$log_file,
			'The log file was deleted.'
		);

		$this->assertSame(
			'',
			file_get_contents( self::$log_file ),
			'The log file was not cleared.'
		);
	}
}
