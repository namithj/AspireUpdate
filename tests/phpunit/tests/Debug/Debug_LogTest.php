<?php
/**
 * Class Debug_LogTest
 *
 * @package AspireUpdate
 */

/**
 * Tests for Debug::log()
 *
 * These tests cause constants to be defined.
 * They must run in separate processes and must not preserve global state.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @covers \AspireUpdate\Debug::log
 */
class Debug_LogTest extends Debug_UnitTestCase {

	/**
	 * Test that the log file is created when it doesn't already exist.
	 */
	public function test_should_create_log_file_if_it_does_not_already_exist() {
		$this->assertFileDoesNotExist(
			self::$log_file,
			'The log file already exists before testing.'
		);

		$message = 'Test log message.';

		AspireUpdate\Debug::log( $message );

		$this->assertFileExists(
			self::$log_file,
			'The log file was not created.'
		);
	}

	/**
	 * Test that the message is added to the log file.
	 *
	 * @covers \AspireUpdate\Debug::format_message
	 */
	public function test_should_add_message_to_log_file() {
		$this->assertFileDoesNotExist(
			self::$log_file,
			'The log file already exists before testing.'
		);

		$message = 'Test log message.';

		AspireUpdate\Debug::log( $message );

		$this->assertFileExists(
			self::$log_file,
			'The log file was not created.'
		);

		$this->assertStringContainsString(
			$message,
			file_get_contents( self::$log_file ),
			'The message was not added.'
		);
	}

	/**
	 * Test that the message is appended to an existing log file.
	 *
	 * @covers \AspireUpdate\Debug::format_message
	 */
	public function test_should_append_message_to_an_existing_log_file() {
		$previous_message = "A previously logged message.\n";
		file_put_contents( self::$log_file, $previous_message );

		$new_message = 'New log message.';

		AspireUpdate\Debug::log( $new_message );

		$this->assertFileExists(
			self::$log_file,
			'The log file was not created.'
		);

		$actual = file_get_contents( self::$log_file );

		$this->assertStringContainsString(
			$previous_message,
			$actual,
			'The previous message does not exist.'
		);

		$this->assertStringContainsString(
			$new_message,
			$actual,
			'The new message does not exist.'
		);

		$this->assertLessThan(
			strpos( $actual, $new_message ),
			strpos( $actual, $previous_message ),
			'The new message was not appended to the log file.'
		);
	}

	/**
	 * Test that the message is prefixed with the timestamp.
	 *
	 * @covers \AspireUpdate\Debug::format_message
	 */
	public function test_should_prefix_message_with_timestamp() {
		AspireUpdate\Debug::log( 'Test log message.' );

		$this->assertFileExists(
			self::$log_file,
			'The log file was not created.'
		);

		$this->assertMatchesRegularExpression(
			'/^\[[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\]/',
			file_get_contents( self::$log_file ),
			'The message was not prefixed with the timestamp.'
		);
	}

	/**
	 * Test that the message is prefixed with its type.
	 *
	 * @dataProvider data_message_types
	 *
	 * @covers \AspireUpdate\Debug::format_message
	 *
	 * @param string $type The type of message.
	 */
	public function test_should_prefix_message_with_type( $type ) {
		$message = 'Test log message.';

		AspireUpdate\Debug::log( $message, $type );

		$this->assertFileExists(
			self::$log_file,
			'The log file was not created.'
		);

		$this->assertStringContainsString(
			'[' . strtoupper( $type ) . ']: ' . $message,
			file_get_contents( self::$log_file ),
			'The message was not prefixed with its type.'
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_message_types() {
		return $this->text_array_to_dataprovider(
			[
				'string',
				'request',
				'response',
				'custom',
			]
		);
	}

	/**
	 * Test that array and object messages are expanded.
	 *
	 * @dataProvider data_arrays_and_objects
	 *
	 * @covers \AspireUpdate\Debug::format_message
	 *
	 * @param array|object $message The message.
	 */
	public function test_should_expand_array_or_object_messages( $message ) {
		AspireUpdate\Debug::log( $message );

		$this->assertFileExists(
			self::$log_file,
			'The log file was not created.'
		);

		$this->assertStringContainsString(
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			print_r( $message, true ),
			file_get_contents( self::$log_file ),
			'The array message was not expanded.'
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_arrays_and_objects() {
		return [
			'an array'                     => [
				'message' => [],
			],
			'a non-empty array'            => [
				'message' => [ 'First line', 'Second line', 'Third line' ],
			],
			'an object with no properties' => [
				'message' => (object) [],
			],
			'an object with properties'    => [
				'message' => (object) [ 'First line', 'Second line', 'Third line' ],
			],
		];
	}
}
