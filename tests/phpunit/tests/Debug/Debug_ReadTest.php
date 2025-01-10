<?php
/**
 * Class Debug_ReadTest
 *
 * @package AspireUpdate
 */

/**
 * Tests for Debug::read()
 *
 * @covers \AspireUpdate\Debug::read
 */
class Debug_ReadTest extends Debug_UnitTestCase {
	/**
	 * Test that a WP_Error object is returned when the log file doesn't exist.
	 *
	 * @covers \AspireUpdate\Debug::init_filesystem
	 * @covers \AspireUpdate\Debug::get_file_path
	 */
	public function test_should_return_wp_error_when_log_file_does_not_exist() {
		$this->assertWPError( AspireUpdate\Debug::read() );
	}

	/**
	 * Test that a WP_Error object is returned when the log file isn't readable.
	 *
	 * @covers \AspireUpdate\Debug::init_filesystem
	 * @covers \AspireUpdate\Debug::get_file_path
	 */
	public function test_should_return_wp_error_when_log_file_is_not_readable() {
		// Create the log file.
		file_put_contents( self::$log_file, '' );

		// Replace the filesystem object.
		self::$reflection->setStaticPropertyValue( 'filesystem', $this->get_fake_filesystem( true, false, true ) );

		$actual = AspireUpdate\Debug::read();

		$this->assertWPError( $actual );
	}

	/**
	 * Test that an empty log message is returned when the log file is empty.
	 */
	public function test_should_return_an_empty_log_message_when_log_file_is_empty() {
		file_put_contents( self::$log_file, '' );

		$actual = AspireUpdate\Debug::read();
		$this->assertIsArray(
			$actual,
			'An array was not returned.'
		);

		$this->assertCount(
			1,
			$actual,
			'An incorrect number of entries was returned.'
		);

		$entry = reset( $actual );
		$this->assertIsString(
			$entry,
			'The entry is not a string.'
		);

		$this->assertStringContainsString(
			'Log file is empty',
			$entry,
			'The empty log file message was not returned.'
		);
	}

	/**
	 * Test that an empty log message is returned when the log file's content
	 * is only empty space.
	 */
	public function test_should_return_an_empty_log_message_when_log_file_only_has_empty_space() {
		file_put_contents( self::$log_file, " \n\r\t\v\x00" );

		$actual = AspireUpdate\Debug::read();
		$this->assertIsArray(
			$actual,
			'An array was not returned.'
		);

		$this->assertCount(
			1,
			$actual,
			'An incorrect number of entries was returned.'
		);

		$entry = reset( $actual );
		$this->assertIsString(
			$entry,
			'The entry is not a string.'
		);

		$this->assertStringContainsString(
			'Log file is empty',
			$entry,
			'The empty log file message was not returned.'
		);
	}

	/**
	 * Test that an empty log message is not returned when the log file
	 * has contents.
	 */
	public function test_should_not_return_an_empty_log_message_when_log_file_has_contents() {
		file_put_contents( self::$log_file, 'Some contents' );

		$actual = AspireUpdate\Debug::read();
		$this->assertIsArray(
			$actual,
			'An array was not returned.'
		);

		$this->assertCount(
			1,
			$actual,
			'An incorrect number of entries was returned.'
		);

		$entry = reset( $actual );
		$this->assertIsString(
			$entry,
			'The entry is not a string.'
		);

		$this->assertStringNotContainsString(
			'Log file is empty',
			$entry,
			'The empty log file message was returned.'
		);
	}

	/**
	 * Test that a truncation message is added when the log file has more
	 * lines than requested.
	 */
	public function test_should_add_a_truncation_message_when_log_file_has_more_lines_than_requested() {
		file_put_contents(
			self::$log_file,
			"First line\r\nSecond line\r\nThird line"
		);

		$actual = AspireUpdate\Debug::read( 2 );

		$this->assertIsArray(
			$actual,
			'An array was not returned.'
		);

		$this->assertCount(
			2,
			$actual,
			'An incorrect number of entries was returned.'
		);
	}

	/**
	 * Test that no truncation message is added when the log file has the same
	 * number of lines as requested.
	 */
	public function test_should_not_add_a_truncation_message_when_log_file_has_the_same_number_of_lines_as_requested() {
		file_put_contents(
			self::$log_file,
			"First line\r\nSecond line\r\nThird line"
		);

		$actual = AspireUpdate\Debug::read( 3 );

		$this->assertIsArray(
			$actual,
			'An array was not returned.'
		);

		$this->assertCount(
			3,
			$actual,
			'An incorrect number of entries was returned.'
		);
	}

	/**
	 * Test that no truncation message is added when the log file has fewer than
	 * lines than requested.
	 */
	public function test_should_not_add_a_truncation_message_when_log_file_has_fewer_lines_than_requested() {
		file_put_contents(
			self::$log_file,
			"First line\r\nSecond line\r\nThird line"
		);

		$actual = AspireUpdate\Debug::read( 4 );

		$this->assertIsArray(
			$actual,
			'An array was not returned.'
		);

		$this->assertCount(
			3,
			$actual,
			'An incorrect number of entries was returned.'
		);
	}
}
