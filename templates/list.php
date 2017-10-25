<?php defined( 'ABSPATH' ) || die(); ?>
<form method="get" action="<?php echo admin_url( 'users.php' ) ?>">
	<input type="hidden" name="page" value="karam_points" />
<?php
$table = new \Hametuha\Karma\Admin\Table();
$table->prepare_items();
$table->search_box( __( 'Search', 'karma' ), 's' );
$table->display();
?>
</form>
