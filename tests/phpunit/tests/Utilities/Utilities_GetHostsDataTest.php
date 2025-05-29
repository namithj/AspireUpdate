<?php
/**
 * Class Utilities_GetHostsDataTest
 *
 * @package AspireUpdate
 */

use AspireUpdate\Utilities;

class Utilities_GetHostsDataTest extends \WP_UnitTestCase {
	public function test_get_hosts_data_returns_array() {
		$hosts_data = \AspireUpdate\Utilities::get_hosts_data();
		$this->assertTrue( is_array( $hosts_data ), 'get_hosts_data() should return an array' );
		$this->assertNotEmpty( $hosts_data, 'get_hosts_data() should not return an empty array' );
	}

	public function test_get_hosts_data_contains_expected_keys() {
		$hosts_data = \AspireUpdate\Utilities::get_hosts_data();
		$this->assertTrue( is_array( $hosts_data ) );
		foreach ( $hosts_data as $host ) {
			$this->assertArrayHasKey( 'url', $host );
			$this->assertArrayHasKey( 'label', $host );
		}
	}

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
