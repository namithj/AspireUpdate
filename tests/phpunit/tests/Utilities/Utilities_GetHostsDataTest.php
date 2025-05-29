<?php
/**
 * Class Utilities_GetHostsDataTest
 *
 * @package AspireUpdate
 */


/**
 * Tests for Utilities::get_hosts_data()
 *
 * These tests cause constants to be defined.
 * They must run in separate processes and must not preserve global state.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @covers \AspireUpdate\Utilities::get_hosts_data
 */
class Utilities_GetHostsDataTest extends \WP_UnitTestCase {
	/**
	 * Test that an array is returned by get_hosts_data().
	 *
	 * @dataProvider data_multisite_hooks_and_methods
	 *
	 * @group ms-required
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_get_hosts_data_returns_array() {
		$hosts_data = \AspireUpdate\Utilities::get_hosts_data();
		$this->assertTrue( is_array( $hosts_data ), 'get_hosts_data() should return an array' );
		$this->assertNotEmpty( $hosts_data, 'get_hosts_data() should not return an empty array' );
	}

	/**
	 * Test that the array elements returned by get_hosts_data() contained the required parameters.
	 *
	 * @dataProvider data_multisite_hooks_and_methods
	 *
	 * @group ms-required
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_get_hosts_data_contains_expected_keys() {
		$hosts_data = \AspireUpdate\Utilities::get_hosts_data();
		$this->assertTrue( is_array( $hosts_data ) );
		foreach ( $hosts_data as $host ) {
			$this->assertArrayHasKey( 'url', $host );
			$this->assertArrayHasKey( 'label', $host );
		}
	}

	/**
	 * Test that the get_hosts_data() returns false when the hosts.json file is missing.
	 *
	 * @dataProvider data_multisite_hooks_and_methods
	 *
	 * @group ms-required
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_get_hosts_data_returns_false_on_missing_file() {
		global $wp_filesystem;
		$file   = defined( 'AP_PATH' ) ? AP_PATH . DIRECTORY_SEPARATOR . 'hosts.json' : null;
		$backup = $file ? $file . '.bak' : null;
		if ( $file && file_exists( $file ) ) {
			$wp_filesystem->move( $file, $backup );
		}
		// Clear cached data
		$ref  = new \ReflectionClass( '\\AspireUpdate\\Utilities' );
		$prop = $ref->getProperty( 'hosts_data' );
		$prop->setAccessible( true );
		$prop->setValue( null, null );
		$result = \AspireUpdate\Utilities::get_hosts_data();
		$this->assertFalse( $result, 'get_hosts_data() should return false if file is missing' );
		// Restore the file
		if ( $backup && file_exists( $backup ) ) {
			$wp_filesystem->move( $backup, $file );
		}
	}
}
