<?php

namespace Hametuha\Karma\Models;


use Hametuha\Karma\Pattern\Model;

/**
 * Point meta
 * @package karma
 */
class PointMeta extends Model {

	protected $version = '1.0.0';

	/**
	 * Get table schema
	 *
	 * @param string $prev_version
	 * @return string
	 */
	public function get_tables_schema( $prev_version ) {
		$charset = DB_CHARSET;
		return <<<SQL
			CREATE TABLE {$this->table} (
				`point_meta_id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				`point_id`      BiGINT UNSIGNED NOT NULL,
				`meta_key`      VARCHAR(48) NOT NULL,
				`meta_value`    LONGTEXT,
				`created`       DATETIME NOT NULL,
				`updated`       DATETIME NOT NULL,
				INDEX key_id ( meta_key, meta_value(128), `created` ),
				INDEX id_key ( point_id, meta_key, `created` )
			) ENGINE=InnoDB  DEFAULT CHARSET={$charset}
SQL;
	}

	/**
	 * Add multiple values.
	 *
	 * @param int   $point_id
	 * @param array $data
	 * @return false|int
	 */
	public function bulk_add( $point_id, $data = [] ) {
		if ( ! $data ) {
			return 0;
		}
		$records = [];
		foreach ( $data as $key => $value ) {
			$records[] = [
				'point_id'   => $point_id,
				'meta_key'   => $key,
				'meta_value' => maybe_serialize( $value ),
			];
		}
		return (int) $this->bulk_insert( $records );
	}

	/**
	 * @param int    $point_id
	 * @param string $key
	 * @param $value
	 * @return int|\WP_Error
	 */
	public function add( $point_id, $key, $value ) {
		return $this->insert( [
			'point_id' => $point_id,
			'meta_key' => $key,
			'meta_value' => maybe_serialize( $value ),
		] );
	}
}
