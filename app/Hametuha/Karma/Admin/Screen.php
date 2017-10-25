<?php

namespace Hametuha\Karma\Admin;


use Hametuha\Karma;
use Hametuha\Karma\Pattern\Singleton;
use Hametuha\Karma\Models\Points;

/**
 * Generate admin screen
 * @package karma
 */
class Screen extends Singleton {

	/**
	 * Initialize
	 */
	protected function init() {
		add_action( 'admin_menu', function() {
			add_users_page( __( 'User Points', 'karma' ), __( 'Points', 'karma' ), Karma::get_admin_caps(), 'karma_points', [ $this, 'list_points' ] );
		} );
		add_action( 'admin_enqueue_scripts', function( $page ) {
            wp_enqueue_style( 'karma-admin', Karma::asset_url( '/assets/css/admin.css' ), [], Karma::version() );
            // Form helper scripts.
            if ( 'users_page_karma_points' === $page ) {
                wp_register_script( 'select2', Karma::asset_url( '/assets/js/slect2.min.js' ), ['jquery'], '4.0.4', true );
                wp_enqueue_style( 'select2', Karma::asset_url( 'assets/css/select2.min.css' ), [], '4.0.4' );
                wp_enqueue_script( 'karma-form', Karma::asset_url( '/assets/js/karma-form-helper.js' ), ['select2'], Karma::version(), true );
            }
        } );
	}

	/**
	 * Render Points
	 */
	public function list_points() {
		?>
		<div class="wrap">
			<h2>
				<?php esc_html_e( 'User Points', 'karma' ) ?>
				<a href="<?php echo admin_url( 'users.php?page=karma_points&karma_action=add_new' ) ?>" class="page-title-action">
					<?php esc_html_e( 'Add New', 'karma' ) ?>
				</a>
			</h2>
			<?php
				$action = isset( $_GET['karma_action'] ) ? $_GET['karma_action'] : '';
				try {
					switch ( $action ) {
						case 'add_new':
							$point = null;
							$template = 'form.php';
							break;
						case 'edit':
							$point = Points::get_instance()->get( isset( $_GET[ 'point_id' ] ) ? $_GET[ 'point_id' ] : 0 );
							$template = 'form.php';
							break;
						default:
							$template = 'list.php';
							break;
					}
					$path = Karma::dir() . "/templates/{$template}";
					if ( !file_exists( $path ) ) {
						throw new \Exception( sprintf( __( 'Template file %s doesn\'t exist.', 'karma' ), $path ), 404 );
					}
					include $path;
                } catch ( \Exception $e ) {
				    ?>
                    <div class="karma-error-message">
                        <?php echo wp_kses_post( $e->getMessage() ) ?>
                        <img src="<?php echo Karma::asset_url( '/assets/img/jigoku.jpg' ) ?>" alt="Jigoku">
                    </div>
                    <?php
                }
			?>
		</div>
		<?php
	}
}
