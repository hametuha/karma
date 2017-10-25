<?php

namespace Hametuha\Karma\API;


use Hametuha\Karma;
use Hametuha\Karma\Pattern\RestApi;

class Manager extends RestApi {

	protected $route = 'points/manager';

	/**
	 * Create post for user.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\stdClass
	 */
	public function handle_post( \WP_REST_Request $request ) {
		$result = $this->model()->add_record( $request['type'], $request['user_id'], $request['point'], [
			'log' => sprintf( __( 'Manually added by site admin %d.', 'karma' ), get_current_user_id() ),
		], $request['status'] );
		if ( is_wp_error( $result ) ) {
			return $result;
		} else {
			return $this->model()->get( $result );
		}
	}

	/**
	 * Updated existing record
	 *
	 * @param \WP_REST_Request $request
	 * @return \stdClass|\WP_Error
	 */
	public function handle_put( $request ) {
		$result = $this->model()->modify_record( $request['point_id'], $request['type'], $request['point'], $request['status'] );
		if ( ! $result ) {
			return new \WP_Error( 'nothing_changed', __( 'Nothing updated.', 'karma' ) );
		} else {
			return $this->model()->get( $request['point_id'] );
		}
	}

	/**
	 * Permission check
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( \WP_REST_Request $request ) {
		return current_user_can( Karma::get_admin_caps() );
	}

	/**
	 * Get all arguments.
	 *
	 * @param string $http_method
	 * @return array
	 */
	protected function get_args( $http_method ) {
		$args = [
			'type' => [
				'required' => true,
				'validate_callback' => function($type) {
					return $this->model()->type_exists( $type );
				}
			],
			'status' => [
				'default' => 1,
				'validate_callback' => [ $this, 'is_numeric' ],
			],
			'point' => [
				'required' => true,
				'validate_callback' => [ $this, 'is_numeric' ],
			],
		];
		switch ( $http_method ) {
			case 'POST':
				$args['user_id'] = [
					'required' => true,
					'validate_callback' => function ( $var ) {
						if ( ! is_numeric( $var ) || 1 > $var ) {
							return false;
						}
						return get_userdata( $var );
					},
				];
				break;
			case 'PUT':
				$args['point_id'] = [
					'required' => true,
					'validate_callback' => [ $this, 'is_numeric' ],
				];
				break;
			default:
				return [];
				break;
		}
		return $args;
	}

	/**
	 * Get points model
	 *
	 * @return Karma\Models\Points
	 */
	public function model() {
		return Karma\Models\Points::get_instance();
	}

}
