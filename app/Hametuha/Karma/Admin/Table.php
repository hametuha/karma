<?php

namespace Hametuha\Karma\Admin;


use Hametuha\Karma\Models\Points;

class Table extends \WP_List_Table {

	public function __construct( $args = array() ) {
		parent::__construct( [
			'singular' => 'point',
			'plural' => 'points',
			'ajax'     => true,
		] );
	}

	public function get_columns() {
		return [
			'type' => __( 'Type', 'karma' ),
			'user' => __( 'User', 'karma' ),
			'point' => __( 'Point', 'karma' ),
			'created' => __( 'Created', 'karma' ),
			'updated' => __( 'Updated', 'karma' ),
			'status' => __( 'Status', 'karma' ),
		];
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * @return array
	 */
	protected function get_sortable_columns() {
		return [
			'created' => [ 'created', 'DESC' ],
			'updated' => [ 'updated', 'DESC' ],
			'point'   => [ 'point', 'ASC' ],
		];
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function no_items() {
		esc_html_e( 'No record exists.', 'karma' );
	}

	/**
	 * Prepares the list of items for displaying.
	 * @uses WP_List_Table::set_pagination_args()
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function prepare_items() {
		//Set column header
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];
		$per_page = isset( $_GET['per_page'] ) ? (int) $_GET['per_page'] : 20;
		$query = [
			'type'      => isset( $_GET['type'] ) ? (int) $_GET['type'] : '',
			'user_id'  => isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : 0,
			'per_page' => $per_page,
			'status'   => isset( $_GET['status'] ) ? (int) $_GET['status'] : '',
			'page'     => $this->get_pagenum(),
			'orderby'  => isset( $_GET['orderby'] ) ? $_GET['orderby'] : '',
			'order'    => isset( $_GET['order'] ) ? strtoupper( $_GET['order'] ) : '',
		];
		$model = Points::get_instance();
		$this->items = $model->search( $query );
		$this->set_pagination_args( [
			'total_items' => $model->found_rows(),
			'per_page'    => $per_page,
		] );
	}

	/**
	 * Render row
	 *
	 * @param object $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'type':
				printf( '<a href="%s&amp;type=%s">%s</a>', esc_url( admin_url( 'users.php?page=karma_points' ) ), rawurlencode( $item->type ), esc_html( $item->label ) );
				echo $this->row_actions( [
					'edit' => sprintf( '<a href="%s">%s</a>', '', __( 'Edit', 'karma' ) ),
					'delete' => sprintf(
						'<a class="karma-elete-link" href="#" onclick="return ! window.confirm(\'%s\');">%s</a>',
						esc_js( __( 'Are you sure to delete this?' ) ),
						__( 'Delete', 'karma' )
					),
				] );
				break;
			case 'user':
				if ( $item->display_name ) {
					printf( '<a href="%s&amp;user_id=%d">%s</a>', admin_url( 'users.php?page=karam_points' ), $item->ID, esc_html( $item->display_name ) );
				} else {
					printf( '<span class="karmar-column-user-disabled">%s</span>', esc_html__( 'Deleted User', 'karma' ) );
				}
				break;
			case 'point':
				echo number_format( $item->point );
				break;
			case 'status':
				if ( $item->status ) {
					printf( '<span class="dashicons dashicons-yes"></span> <em>%s</em>', esc_html__( 'Valid', 'karma' ) );
				} else {
					printf( '<span class="dashicons dashicons-no"></span> <em>%s</em>', esc_html__( 'Invalid', 'karma' ) );
				}
				break;
			case 'created':
			case 'updated':
				echo mysql2date( get_option( 'date_format' ), $item->{$column_name} );
				break;
		}
	}
}
