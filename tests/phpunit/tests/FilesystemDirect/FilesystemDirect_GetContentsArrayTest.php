<?php
/**
 * Class FilesystemDirect_GetContentsArrayTest
 *
 * @package AspireUpdate
 */

/**
 * Tests for Filesystem_Direct::get_contents_array()
 *
 * These tests cause constants to be defined.
 * They must run in separate processes and must not preserve global state.
 *
 * @covers \AspireUpdate\Filesystem_Direct::get_contents_array
 */
class FilesystemDirect_GetContentsArrayTest extends WP_UnitTestCase {
	private static $test_file = '/tmp/aspireupdate-getcontentsarray-test-file.txt';

	/**
	 * Remove the test file should it exist before any tests run.
	 */
	public static function set_up_before_class() {
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
	}

	/**
	 * Test that false is returned when the file does not exist.
	 */
	public function test_should_return_false_when_file_does_not_exist() {
		$filesystem = new AP_FakeFilesystem( false, true, true );
		$this->assertFalse( $filesystem->get_contents_array( 'non_existent_file.txt', 1 ) );
	}

	/**
	 * Test that false is returned when the file is not readable.
	 *
	 * This test fakes a true exists() result, despite the file not existing.
	 *
	 * Since the file doesn't exist, read checks should fail.
	 */
	public function test_should_return_false_when_the_file_cannot_be_read() {
		$filesystem = new AP_FakeFilesystem( true, false, false );

		$this->assertFalse( $filesystem->get_contents_array( self::$test_file, 1 ) );
	}

	/**
	 * Test that the entire log is returned if the number of requested lines is -1.
	 */
	public function test_should_return_the_entire_log_if_number_of_requested_lines_is_minus_one() {
		$filesystem = new AP_FakeFilesystem( true, true, true );
		$contents   = 'First line' . PHP_EOL . 'Second line' . PHP_EOL . 'Third line';
		file_put_contents( self::$test_file, $contents );

		$actual = $filesystem->get_contents_array( self::$test_file, -1 );

		$this->assertIsArray(
			$actual,
			'The contents were not read into an array.'
		);

		$this->assertSame(
			[
				'First line' . PHP_EOL,
				'Second line' . PHP_EOL,
				'Third line',
			],
			$actual,
			'The entire log was not read.'
		);
	}

	/**
	 * Test that the lines returned are from the bottom of the log file up when requested.
	 *
	 * @dataProvider data_count_bottom_to_top_enabled
	 *
	 * @param mixed $count_bottom_to_top Whether to count the lines from the bottom up.
	 */
	public function test_should_read_from_bottom_to_top_when_requested( $count_bottom_to_top ) {
		$filesystem = new AP_FakeFilesystem( true, true, true );
		$contents   = 'First line' . PHP_EOL . 'Second line' . PHP_EOL . 'Third line';
		file_put_contents( self::$test_file, $contents );

		$actual = $filesystem->get_contents_array( self::$test_file, 2, $count_bottom_to_top );

		$this->assertIsArray(
			$actual,
			'The contents were not read into an array.'
		);

		$this->assertCount(
			2,
			$actual,
			'The number of lines read does not match the requested number of lines.'
		);

		$this->assertSame(
			[
				'Second line',
				'Third line',
			],
			$actual,
			'The lines were not read from the bottom up.'
		);
	}

	/**
	 * Test that the whole log is returned when the log file has the same
	 * number of lines as requested.
	 *
	 * @dataProvider data_count_bottom_to_top_enabled
	 * @dataProvider data_count_bottom_to_top_disabled
	 *
	 * @param mixed $count_bottom_to_top Whether to count the lines from the bottom up.
	 */
	public function test_should_return_whole_log_when_log_file_has_the_same_number_of_lines_as_requested( $count_bottom_to_top ) {
		$filesystem = new AP_FakeFilesystem( true, true, true );
		$contents   = 'First line' . PHP_EOL . 'Second line' . PHP_EOL . 'Third line';
		file_put_contents( self::$test_file, $contents );

		$actual = $filesystem->get_contents_array( self::$test_file, 3, $count_bottom_to_top );

		$this->assertIsArray(
			$actual,
			'The contents were not read into an array.'
		);

		$this->assertSame(
			[
				'First line',
				'Second line',
				'Third line',
			],
			$actual,
			'The entire log was not read.'
		);
	}

	/**
	 * Test that only the requested number of lines is read.
	 *
	 * @dataProvider data_count_bottom_to_top_enabled
	 * @dataProvider data_count_bottom_to_top_disabled
	 *
	 * @param mixed $count_bottom_to_top Whether to count the lines from the bottom up.
	 */
	public function test_should_only_read_the_requested_number_of_lines( $count_bottom_to_top ) {
		$filesystem = new AP_FakeFilesystem( true, true, true );
		$contents   = 'First line' . PHP_EOL . 'Second line' . PHP_EOL . 'Third line';
		file_put_contents( self::$test_file, $contents );

		$actual = $filesystem->get_contents_array( self::$test_file, 2, $count_bottom_to_top );

		$this->assertIsArray(
			$actual,
			'The contents were not read into an array.'
		);

		if ( $count_bottom_to_top ) {
			$expected = [
				'Second line',
				'Third line',
			];
		} else {
			$expected = [
				'First line',
				'Second line',
			];
		}

		$this->assertSame(
			$expected,
			$actual,
			'The lines read do not match the expected lines.'
		);
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_count_bottom_to_top_enabled() {
		return [
			'$count_bottom_to_top as (bool) true'          => [
				'count_bottom_to_top' => true,
			],
			'$count_bottom_to_top as (int) 1'              => [
				'count_bottom_to_top' => 1,
			],
			'$count_bottom_to_top as (float) 1.0'          => [
				'count_bottom_to_top' => 1.0,
			],
			'$count_bottom_to_top as (float) -1.0'         => [
				'count_bottom_to_top' => -1.0,
			],
			'$count_bottom_to_top as (string) "1"'         => [
				'count_bottom_to_top' => '1',
			],
			'$count_bottom_to_top as a string with spaces' => [
				'count_bottom_to_top' => " \t\r\n",
			],
			'$count_bottom_to_top as a non-empty array'    => [
				'count_bottom_to_top' => [ 'not empty' ],
			],
			'$count_bottom_to_top as an object'            => [
				'count_bottom_to_top' => new stdClass(),
			],
			'$count_bottom_to_top as NAN'                  => [
				'count_bottom_to_top' => NAN,
			],
			'$count_bottom_to_top as INF'                  => [
				'count_bottom_to_top' => INF,
			],
		];
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public function data_count_bottom_to_top_disabled() {
		return [
			'$count_bottom_to_top as (bool) false'    => [
				'count_bottom_to_top' => false,
			],
			'$count_bottom_to_top as (int) 0'         => [
				'count_bottom_to_top' => 0,
			],
			'$count_bottom_to_top as (string) "0"'    => [
				'count_bottom_to_top' => '0',
			],
			'$count_bottom_to_top as (float) 0.0'     => [
				'count_bottom_to_top' => 0.0,
			],
			'$count_bottom_to_top as (float) -0.0'    => [
				'count_bottom_to_top' => -0.0,
			],
			'$count_bottom_to_top as an empty string' => [
				'count_bottom_to_top' => '',
			],
			'$count_bottom_to_top as an empty array'  => [
				'count_bottom_to_top' => [],
			],
			'$count_bottom_to_top as NULL'            => [
				'count_bottom_to_top' => null,
			],
		];
	}
}
