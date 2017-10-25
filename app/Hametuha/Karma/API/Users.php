<?php

namespace Hametuha\Karma\API;


use Hametuha\Karma\Pattern\RestApi;

/**
 * Search users
 *
 * @package karma
 */
class Users extends RestApi {

	protected $route = 'users';

	/**
	 * Search users
	 *
	 * @param \WP_REST_Request $request
	 * @return array
	 */
	public function handle_get( $request ) {
		$s = "%{$request['s']}%";
		$per_page = $request['number'];
		$offset = ( max( 1, (int) $request['paged'] ) - 1 ) * $per_page;
		global $wpdb;
		$query = <<<SQL
			SELECT * FROM {$wpdb->users}
			WHERE (
				( user_login LIKE %s )
				OR
				( display_name LIKE %s )
				OR
				( user_email LIKE %s )
			)
			LIMIT %d, %d
SQL;
		return $wpdb->get_results( $wpdb->prepare( $query, $s, $s, $s, $offset, $per_page ) );
	}

	/**
	 * Permission check
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( \WP_REST_Request $request ) {
		return current_user_can( 'list_users' );
	}

	/**
	 * Get all arguments.
	 *
	 * @param string $http_method
	 * @return array
	 */
	protected function get_args( $http_method ) {
		switch ( $http_method ) {
			case 'GET':
				return [
					's' => [
						'required' => true,
						'validate_callback' => [ $this, 'is_not_empty' ],
					],
					'number' => [
						'default' => 10,
						'validate_callback' => [ $this, 'is_numeric' ],
					],
					'paged' => [
						'default' => 1,
						'validate_callback' => [ $this, 'is_numeric' ],
					],
				];
				break;
			default:
				return [];
				break;
		}
	}

}
