<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/05/25
 * Time: 0:46
 */

//TODO DEBUG
ini_set('display_errors', true);
error_reporting(E_ALL);
debug_base();

//Init
empty($_GET['action']) and $_GET['action']=null;
empty($_GET['unique_key']) and $_GET['unique_key']=null;
$order=null;

$orders_uri = admin_url('admin.php?page=base_to_wp_orders');


try {

	$BaseOAuthWP = new BaseOAuthWP();
	$BaseOAuthWP->checkToken();

	if ($_GET['action']==='detail' && $_GET['unique_key'] !== null ) {
		$order = $BaseOAuthWP->getOrder($_GET['unique_key']);

	} else {
		$orders_obj = $BaseOAuthWP->getOrders();
		$response = json_decode($BaseOAuthWP->response,true);
		$orders = $response['orders'];

		require_once BASE_TO_WP_ABSPATH.'/OrderListTable.php';
		$OrderListTable = new \BaseToWP\OrderListTable();
		$OrderListTable->data = $orders;
		$OrderListTable->prepare_items();
	}
} catch (Exception $e) {
	var_dump($e->getMessage());

}
?>
<div class="wrap">
	<?php if ($_GET['action']==='detail' && $_GET['unique_key'] !== null ): ?>
		<h2><?php _e('BASE To WordPress 注文詳細', BASE_TO_WP_NAMEDOMAIN); ?></h2>
		<?php $BaseOAuthWP->render_list($order); ?>

	<?php else: ?>
		<h2><?php _e('BASE To WordPress 注文管理', BASE_TO_WP_NAMEDOMAIN); ?></h2>
		<?php //$BaseOAuthWP->render_list($orders_obj); ?>
		<form id="base-items-filter" method="get">
			<?php $OrderListTable->search_box('注文を検索','base-order-search-input'); ?>
			<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>" />
			<?php $OrderListTable->display(); ?>
		</form>
	<?php endif; ?>
</div>