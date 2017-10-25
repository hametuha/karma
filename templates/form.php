<?php
defined( 'ABSPATH' ) || die();
$model = \Hametuha\Karma\Models\Points::get_instance();
/** @var stdClass $point */
?>

<div class="notice notice-info">
	<p><?php printf( __( 'Return to <a href="%s">point list &raquo;</a>', 'karma' ), admin_url( 'users.php?page=karma_points' ) ) ?></p>
</div>

<h2>
	<?php
	if ( $point ) {
		printf( esc_html__( 'Edit point record #%d', 'karma' ), $point->point_id );
	} else {
		esc_html_e( 'Add new point record.', 'karma' );
	} ?>
</h2>

<form id="karma-point-editor" action="<?php echo rest_url( 'karma/v1/points/manager' ) ?>" data-return="<?php echo admin_url( 'users.php?page=karma_points&karma_action=edit&point_id=' ) ?>" method="post" data-method="<?php echo $point ? 'PUT' : 'POST' ?>">
    <?php wp_nonce_field( 'wp_rest', '_wpnonce', false ) ?>
    <?php if ( $point ): ?>
    <input type="hidden" name="point_id" value="<?php echo esc_attr( $point->point_id ) ?>" />
    <?php endif; ?>
	<table class="form-table">
		<tr>
			<th><label for="type"><?php esc_html_e( 'Type', 'karma' ) ?></label></th>
			<td>
				<select id="type" name="type">
					<?php foreach ( $model->get_types() as $key => $values ) : ?>
					<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, $point ? $point->type : '' ) ?>>
						<?php echo esc_html( $values['label'] ) ?>
					</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
            <tr>
                <th><label for="user_id"><?php esc_html_e( 'User', 'karma' ) ?></label></th>
                <td>
                <?php if ( ! $point ) : ?>
                    <select id="user_id" class="karma-user-input" name="user_id" data-end-point="<?php echo rest_url( '/karma/v1/users' ) ?>">
                    </select>
                <?php else : ?>
                    <?php echo get_the_author_meta( 'display_name', $point->user_id ) ?: esc_html_e( 'Deleted User', 'karma' ) ?>
                <?php endif; ?>
                </td>
            </tr>
        <tr>
            <th><label for="point"><?php esc_html_e( 'Point', 'karma' ) ?></label></th>
            <td>
                <input id="point" name="point" type="number" class="regular-text" value="<?php echo $point ? $point->point : '' ?>" />
            </td>
        </tr>
        <tr>
            <th><label for="status"><?php esc_html_e( 'Status', 'karma' ) ?></label></th>
            <td>
                <select name="status" id="status">
                    <?php foreach ( [
                        '1' => __( 'Valid', 'karma' ),
                        '0' => __( 'Invalid', 'karma' ),
                    ] as $value => $label) : ?>
                    <option value="<?php echo esc_attr( $value ) ?>" <?php selected( $value, $point ? $point->status : '' ) ?>>
                        <?php echo esc_html( $label ) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
		<?php if ( $point ) : ?>
            <tr>
                <th><?php esc_html_e( 'Point ID', 'karma' ) ?></th>
                <td><?php echo $point->point_id ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Created', 'karma' ) ?></th>
                <td>
                    <?php echo get_date_from_gmt( $point->created ) ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Updated', 'karma' ) ?></th>
                <td>
					<?php echo get_date_from_gmt( $point->updated ) ?>
                </td>
            </tr>
		<?php endif; ?>
	</table>

    <?php submit_button( __( 'Submit', 'karma' ) ) ?>

</form>
