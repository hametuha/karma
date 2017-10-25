<?php

namespace Hametuha;

use Hametuha\Karma\Admin\Screen;
use Hametuha\Karma\Pattern\Singleton;

/**
 * Class Karma
 * @package karma
 */
class Karma extends Singleton {

	/**
	 * Get root directory
	 *
	 * @return string
	 */
	public static function dir() {
		return dirname( dirname(__DIR__) );
	}

	/**
	 * Get URL
	 *
	 * @param string $path
	 * @return string
	 */
	public static function asset_url( $path ) {
		return plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . ltrim( $path, '/' );
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	public static function version() {
		return KARMA_VERSION;
	}

	/**
	 * Initialize karma
	 */
	protected function init() {
		// Autoload api and models.
		foreach ( [
			'Models' => 'Hametuha\\Karma\\Pattern\\Model',
			'API' => 'Hametuha\\Karma\\Pattern\\RestApi',
	  	] as $dir => $subclass ) {
			$base_dir = __DIR__ . '/Karma/' . $dir;
			$namespace = "Hametuha\\Karma\\{$dir}";
			self::bulk_load( $base_dir, $namespace, $subclass );
		}
		// Generate admin screen
		Screen::get_instance();
	}

	/**
	 * Get admin capability
	 *
	 * @return string
	 */
	public static function get_admin_caps() {
		/**
		 * karma_admin_capability
		 *
		 * @param string $capability
		 * @return string
		 */
		return apply_filters( 'karma_admin_capability', 'edit_users' );
	}

	/**
	 * Load all instance.
	 *
	 * @param string $dir
	 * @param string $name_space
	 * @param string $subclass_of
	 * @param string $method_name
	 */
	public static function bulk_load( $dir, $name_space, $subclass_of, $method_name = 'get_instance' ) {
		foreach ( scandir( $dir ) as $file ) {
			if ( preg_match( '#^([^._].*)\.php#u', $file, $match ) ) {
				$class_name = "{$name_space}\\{$match[1]}";
				if ( class_exists( $class_name ) ) {
					$reflection = new \ReflectionClass( $class_name );
					if ( $reflection->isSubclassOf( $subclass_of ) && ! $reflection->isAbstract() ) {
						call_user_func( [ $class_name, $method_name ] );
					}
				}
			}
		}
	}

	public static function get( $key ) {
		return 'hoge';
	}

}
