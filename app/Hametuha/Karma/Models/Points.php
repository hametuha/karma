<?php

namespace Hametuha\Karma\Models;


use Hametuha\Karma\Pattern\Model;

/**
 * Point database.
 *
 * @package karma
 * @property PointMeta $point_meta
 */
class Points extends Model {

	protected $version = '1.0.0';

	protected $labels = [];

	protected $models = [
		'point_meta' => PointMeta::class,
	];

	/**
	 * Default placeholder
	 *
	 * @var array
	 */
	protected $default_placeholder = [
		'point_id' => '%d',
		'type' => '%s',
		'user_id' => '%d',
		'point' => '%d',
		'status' => '%d',
		'created' => '%s',
		'updated' => '%s',
	];

	/**
	 * Add label
	 */
	protected function init() {
		parent::init();
		/**
		 * Get labels for execution
		 *
		 * @param array $settings Assoc array like 'type' => [ 'label' => 'Label', 'description' => 'What this point means.' ]
		 */
		$labels = apply_filters( 'karma_types', [
			'post'    => [ 'label' => __( 'Post', 'karma' ) ],
			'consume' => [ 'label' => __( 'Consume', 'karma' ) ],
		] );
		foreach ( $labels as $key => $values ) {
			$values = wp_parse_args( $values, [
				'label' => '',
				'description' => '',
			] );
			if ( ! $values['label'] ) {
				continue;
			}
			$this->labels[ $key ] = $values;
		}
	}

	/**
	 * Get table schema SQL
	 *
	 * @param string $prev_version
	 * @return string
	 */
	public function get_tables_schema( $prev_version ) {
		$charset = DB_CHARSET;
		return <<<SQL
			CREATE TABLE {$this->table} (
				`point_id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				`type`     VARCHAR(48) NOT NULL,
				`user_id`  BIGINT UNSIGNED NOT NULL DEFAULT 0,
				`point`    BIGINT SIGNED NOT NULL DEFAULT 0,
				`status`   TINYINT UNSIGNED  NOT NULL DEFAULT 1, 
				`created`  DATETIME NOT NULL,
				`updated`  DATETIME NOT NULL,
				INDEX type_user ( `type`, `user_id`, `created` ),
				INDEX user_type ( `user_id`, `created` )
			) ENGINE=InnoDB  DEFAULT CHARSET={$charset}
SQL;
	}

	/**
	 * Consume point.
	 *
	 * @param int    $user_id
	 * @param int    $point
	 * @param array  $data Additional meta data.
	 * @param string $type
	 * @param int    $status
	 * @return int|\WP_Error
	 */
	public function consume( $user_id, $point = 0, $data = [], $type = 'consume', $status = 1 ) {
		if ( 0 < $point ) {
			return new \WP_Error( 'invalid_amount', __( 'To consume point, you have to set amount point less than 0', 'karma' ) );
		}
		$total = $this->get_total( $user_id );
		if ( $total + $point < 0 ) {
			return new \WP_Error( 'short_balance', __( 'You cannot consume more than your balacne.', 'karma' ) );
		}
		return $this->add_record( $type, $user_id, $point, $status, $data );
	}

	/**
	 * Detect if type is valid.
	 *
	 * @param string $type
	 * @return bool
	 */
	public function type_exists( $type ) {
		return isset( $this->labels[ $type ] ) && $this->labels[ $type ];
	}

	/**
	 * Add record.
	 *
	 * @param string $type
	 * @param int $user_id
	 * @param int    $point
	 * @param array $data
	 * @param int $status
	 * @return int|\WP_Error
	 */
	public function add_record( $type, $user_id, $point, $data = [], $status = 1 ) {
		if ( ! $this->type_exists( $type ) ) {
			return new \WP_Error( 'type_not_found', sprintf( __( 'Type %s is not registered.', 'karma' ), $type ) );
		}
		$point_id = $this->insert( [
			'type'    => $type,
			'user_id' => $user_id,
			'point'   => $point,
			'status'  => $status,
		] );
		if ( ! is_wp_error( $point_id ) && $data ) {
			// Add metadata
			$this->point_meta->bulk_add( $point_id, $data );
		}
		return $point_id;
	}

	/**
	 * Update record
	 *
	 * @param int    $point_id
	 * @param string $type
	 * @param int    $point
	 * @param int    $status
	 * @return false|int
	 */
	public function modify_record( $point_id, $type, $point, $status = 1 ) {
		if ( ! $this->type_exists( $type ) ) {
			return false;
		}
		$vars = [
			'type' => $type,
			'point' => $point,
			'status' => $status,
		];
		return $this->update( $vars, [
			'point_id' => $point_id,
		] );
	}

	/**
	 * Get all record.
	 *
	 * @param int $user_id
	 * @param int $status
	 * @return int
	 */
	public function get_total( $user_id, $status = 1 ) {
		$query = <<<SQL
			SELECT SUM(point) FROM {$this->table}
			WHERE `user_id` = %d
			  AND `status`  = %d
SQL;
		return (int) $this->get_var( $query, $user_id, $status );
	}

	/**
	 * Search points result
	 *
	 * @param  array $args
	 * @return array
	 */
	public function search( $args ) {
		$args = wp_parse_args( $args, [
			'type'    => '',
			's'      => '',
			'user_id' => 0,
			'status' => '',
			'per_page' => 20,
			'page' => 1,
			'orderby' => 'created',
			'order'   => 'DESC',
		] );
		$wheres = [];
		if ( $args['type'] ) {
			$wheres[] = $this->make_query( [ '( p.type = %s )', $args['type'] ] );
		}
		if ( $args['user_id'] ) {
			$wheres[] = $this->make_query( [ '( p.user_id = %d )', $args['user_id'] ] );
		}
		if ( '' !== $args['status'] ) {
			$wheres[] = $this->make_query( [ '( p.status = %d )', $args['status'] ] );
		}
		if ( $args['s'] ) {
			$sub_query = <<<SQL
				(
					( u.display_name LIKE %s )
					OR
					( u.user_email LIKE %s )
					OR
					( u.user_login LIKE %s )
				)
SQL;
			$s = '%' . $args['s'] . '%';
			$wheres[] = $this->make_query( [ $sub_query, $s, $s, $s ] );
		}
		$where_clause = $wheres ? 'WHERE ' . implode( ' AND ', $wheres ) : '';
		$page = max( (int) $args['page'], 1 );
		$per_page = (int) $args['per_page'];
		$order = 'ASC' == $args['order'] ? 'ASC' : 'DESC';
		$orderby = 'p.' . ( array_key_exists( $args['orderby'], $this->default_placeholder ) ? $args['orderby'] : 'created' );
		$query = <<<SQL
			SELECT SQL_CALC_FOUND_ROWS * FROM {$this->table} AS p
			LEFT JOIN {$this->db->users} AS u
			ON p.user_id = u.ID 
			{$where_clause}
			ORDER BY {$orderby} {$order}
			LIMIT %d, %d
SQL;
		return $this->set_labels( $this->get_results( $query, ( $page - 1 ) * $per_page, $per_page ) );
	}

	/**
	 * Set verbose label
	 *
	 * @param  \stdClass $data
	 * @return \stdClass
	 */
	protected function set_label( $data ) {
		$data->label = isset( $this->labels[ $data->type ] ) ? $this->labels[ $data->type ]['label'] : $data->type;
		return $data;
	}

	/**
	 * Get description of type
	 *
	 * @param string $key
	 * @return string
	 */
	public function get_description( $key ) {
		return isset( $this->labels[ $key ] ) ? $this->labels[ $key ]['description'] : '';
	}

	/**
	 * Set label
	 *
	 * @param array|\stdClass  $results
	 * @return array|\stdClass
	 */
	protected function set_labels( $results ) {
		if ( is_array( $results ) ) {
			return array_map( function( $result ) {
				return $this->set_label( $result );
			}, $results );
		} else {
			return $this->set_label( $results );
		}
	}

	/**
	 * Get point object
	 *
	 * @param $id
	 * @return \stdClass
	 */
	public function get( $id ) {
		$query = <<<SQL
			SELECT * FROM {$this->table} WHERE point_id = %d
SQL;
		$point = $this->get_row( $query, $id );
		return $point ? $this->set_label( $point ) : null;
	}

	/**
	 * Get registered labels.
	 *
	 * @return array
	 */
	public function get_types() {
		return $this->labels;
	}

}
