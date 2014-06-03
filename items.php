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
empty($_GET['item']) and $_GET['item']=null;
$item=null;

$items_uri = admin_url('admin.php?page=base_to_wp_items');


try {

	$BaseOAuthWP = new BaseOAuthWP();
	$BaseOAuthWP->checkToken();

	if ($_GET['action']==='edit' && $_GET['item'] > 0) {
		$item = $BaseOAuthWP->getItem( $_GET['item'] );

	} elseif($_GET['action']==='new') {
		echo 'newwwwwwwwwwwwwwwwww';
	} else {
		$items_obj = $BaseOAuthWP->getItems();
		$response = json_decode($BaseOAuthWP->response,true);
		$items = $response['items'];

		require_once BASE_TO_WP_ABSPATH.'/ItemListTable.php';
		$ItemListTable = new \BaseToWP\ItemListTable();
		$ItemListTable->data = $items;
		$ItemListTable->prepare_items();
	}


} catch (Exception $e) {
	var_dump($e->getMessage());
}
?>
<div class="wrap">
<?php if($_GET['action']==='edit' && $_GET['item'] > 0): ?>
	<h2><?php _e('BASE To WordPress 商品の編集', BASE_TO_WP_NAMEDOMAIN); ?></h2>
	<?php $BaseOAuthWP->render_list($item); ?>


<?php elseif($_GET['action']==='new'): ?>
	<h2><?php _e('BASE To WordPress 商品の新規追加', BASE_TO_WP_NAMEDOMAIN); ?></h2>
	にゅううううううううううううううううううう

<?php else: ?>
	<h2><?php _e('BASE To WordPress 商品管理', BASE_TO_WP_NAMEDOMAIN); ?><a href="<?php echo $items_uri.'&action=new'?>" class="add-new-h2">新規追加</a></h2>
	<?php //$BaseOAuthWP->render_list($items_obj); ?>

	<form id="base-items-filter" method="get">
		<?php $ItemListTable->search_box('商品を検索','base-item-search-input'); ?>
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $ItemListTable->display(); ?>
	</form>


<?php endif; ?>
</div>
