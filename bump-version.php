<?php
/**
 * BumpVersion - Utility to update version numbers in project files.
 *
 * Usage:
 *   php bump-version.php <version> [--dry-run]
 *
 * Example:
 *   php bump-version.php 1.2.3
 *   php bump-version.php 1.2.3 --dry-run
 */

class BumpVersion {
	/**
	 * The version string to set.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Whether to perform a dry run (no files are written).
	 *
	 * @var bool
	 */
	private $dry_run;

	/**
	 * Constructor.
	 *
	 * @param string $version Version string (e.g., 1.2.3).
	 * @param bool   $dry_run Optional. If true, only preview changes. Default false.
	 */
	public function __construct( $version, $dry_run = false ) {
		$this->version = $version;
		$this->dry_run = $dry_run;
	}

	/**
	 * Run the version bump process.
	 *
	 * @return void
	 */
	public function run() {
		$this->print_header();
		//$this->update_composer();
		$this->update_package();
		$this->update_files();
		$this->print_footer();
	}

	/**
	 * Print a message, add dry run prefix if applicable.
	 *
	 * @param string $message The message to print.
	 * @return void
	 */
	private function print_message( $message ) {
		echo ( $this->dry_run ? 'DRY RUN : ' : '' );
		echo $message . PHP_EOL;
	}

	/**
	 * Print the header message.
	 *
	 * @return void
	 */
	private function print_header() {
		$this->print_message( "Bumping version to {$this->version}" );
	}

	/**
	 * Print the footer message.
	 *
	 * @return void
	 */
	private function print_footer() {
		$this->print_message( "Version bump completed: {$this->version}" );
	}

	/**
	 * Update the version in composer.json.
	 *
	 * @return void
	 */
	private function update_composer() {
		$composer_path = __DIR__ . '/composer.json';
		if ( ! file_exists( $composer_path ) ) {
			$this->print_message( 'composer.json not found' );
			return;
		}
		$composer            = json_decode( file_get_contents( $composer_path ), true );
		$composer['version'] = $this->version;

		if ( ! $this->dry_run ) {
			file_put_contents( $composer_path, json_encode( $composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL );
		}
		$this->print_message( 'Updated version in composer.json' );
	}

	/**
	 * Update the version in package.json if it exists.
	 *
	 * @return void
	 */
	private function update_package() {
		$package_path = __DIR__ . '/package.json';
		if ( ! file_exists( $package_path ) ) {
			$this->print_message( 'package.json not found' );
			return;
		}
		$package            = json_decode( file_get_contents( $package_path ), true );
		$package['version'] = $this->version;

		if ( ! $this->dry_run ) {
			file_put_contents( $package_path, json_encode( $package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL );
		}
		$this->print_message( 'Updated version in package.json' );
	}

	/**
	 * Update version numbers in plugin.php, readme.txt, and readme.md.
	 *
	 * @return void
	 */
	private function update_files() {
		$files = [
			[
				'path'     => __DIR__ . '/aspire-update.php',
				'patterns' => [
					'/(^Version:\s*)([\d\.]+)/mi' => function ( $matches ) {
						return $matches[1] . $this->version; },
					'/(\$version\s*=\s*[\'"])([\d\.]+)([\'"])/i' => function ( $matches ) {
						return $matches[1] . $this->version . $matches[3]; },
					'/define\(\s*\'AP_VERSION\'\s*,\s*\'([\d\.]+)\'\s*\)\s*;/i' => function ( $matches ) {
						return str_replace( $matches[1], $this->version, $matches[0] ); },
				],
			],
			[
				'path'     => __DIR__ . '/readme.txt',
				'patterns' => [
					'/(^Stable tag:\s*)([\d\.]+)/mi' => function ( $matches ) {
						return $matches[1] . $this->version; },
				],
			],
		];

		foreach ( $files as $file ) {
			$file_path = $file['path'];
			if ( ! file_exists( $file_path ) ) {
				$this->print_message( "File not found: $file_path" );
				continue;
			}

			$original = file_get_contents( $file_path );
			$modified = $original;

			foreach ( $file['patterns'] as $pattern => $replacement ) {
				if ( is_callable( $replacement ) ) {
					$modified = preg_replace_callback( $pattern, $replacement->bindTo( $this ), $modified );
				} else {
					$modified = preg_replace( $pattern, $replacement, $modified );
				}
			}

			if ( $original === $modified ) {
				$this->print_message( "No version changes needed: $file_path" );
			} else {
				if ( ! $this->dry_run ) {
					file_put_contents( $file_path, $modified );
				}
				$this->print_message( "Updated version in: $file_path" );
			}
		}
	}
}

$usage = <<<USAGE
Usage:
  php bump-version.php <version> [--dry-run]

Example:
  php bump-version.php 1.2.3
  php bump-version.php 1.2.3 --dry-run

USAGE;

if ( $argc < 2 || ! preg_match( '/^\d+\.\d+\.\d+$/', $argv[1] ) ) {
	echo $usage;
	exit( 1 );
}

$version = $argv[1];
$dry_run = in_array( '--dry-run', $argv, true );

$bumper = new BumpVersion( $version, $dry_run );
$bumper->run();
