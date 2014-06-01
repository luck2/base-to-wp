<?php
/**
 * Created by PhpStorm => 
 * User: K => Sasaki
 * Date: 2014/05/25
 * Time: 0:46
 */

//TODO DEBUG
ini_set('display_errors', true);
error_reporting(E_ALL);
debug_base();

$reset_account_uri = admin_url('admin.php?page=base_to_wp_install&reset_account=1&step=1');

try {
	$BaseOAuthWP = new BaseOAuthWP();
	$BaseOAuthWP->checkToken();

	$items_obj = $BaseOAuthWP->getItems();
	$response = json_decode($BaseOAuthWP->response,true);
	$items = $response['items'];

//	$item = $BaseOAuthWP->getItem($id=26371);

	require_once BASE_TO_WP_ABSPATH.'/ItemListTable.php';
	$ItemListTable = new \BaseToWP\ItemListTable();
	$ItemListTable->data = $items;
	$ItemListTable->prepare_items();


} catch (Exception $e) {
	var_dump($e->getMessage());
}


?>
<div class="wrap">
	<h2><?php _e('BASE To WordPress Items', BASE_TO_WP_NAMEDOMAIN); ?></h2>


	<h3>商品情報一覧</h3>

	<form id="movies-filter" method="get">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $ItemListTable->display() ?>
	</form>

<!--	--><?php //$BaseOAuthWP->render_list($items_obj); ?>
<!--	<h3>商品情報(ID:--><?php //echo $id;?><!--)</h3>-->
<!--	--><?php //$BaseOAuthWP->render_list($item); ?>


	<p><a href="<?php echo $reset_account_uri ;?>">[再セットアップ]</a></p>
</div>
