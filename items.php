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
//	var_dump($e->getMessage());
}

?>
<div class="wrap">
<?php if($_GET['action']==='edit' && $_GET['item'] > 0): ?>
	<h2><?php _e('BASE To WordPress 商品の編集', BASE_TO_WP_NAMEDOMAIN); ?></h2>
	<?php
	if (empty($e)) {
		$BaseOAuthWP->render_list($item);
	?>
		<div id="poststuff">

			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content" class="edit-form-section">
					<div id="namediv" class="stuffbox">
						<h3><label for="name">商品編集</label></h3>
						<div class="inside">
							<table class="form-table editcomment">
								<tbody>
								<tr>
									<td class="first">商品名</td>
									<td><input type="text" name="title" size="30" value="<?php esc_attr_e($item->title) ?>" id="title"></td>
								</tr>
								<tr>
									<td class="first">価格（税込）</td>
									<td><input type="text" name="price" size="30" value="<?php esc_attr_e($item->price) ?>" id="price"></td>
								</tr>
								<tr>
									<td class="first">商品説明</td>
									<td><input type="text" name="detail" size="30" value="<?php esc_attr_e($item->detail) ?>" id="detail"></td>
								</tr>
								<tr>
									<td class="first">在庫数</td>
									<td><input type="text" name="stock" size="30" value="<?php esc_attr_e($item->stock) ?>" id="stock"></td>
								</tr>
								<tr>
									<td class="first">商品画像</td>
									<td><input type="text" name="gagaga" size="30" value="<?php esc_attr_e($item->title) ?>" id="gagaga"></td>
								</tr>
								<tr>
									<td class="first">公開状態</td>
									<td><input type="text" name="visible" size="30" value="<?php esc_attr_e($item->visible) ?>" id="visible"></td>
								</tr>
								<tr>
									<td class="first">表示順</td>
									<td><input type="text" name="list_order" size="30" value="<?php esc_attr_e($item->list_order) ?>" id="list_order"></td>
								</tr>
								</tbody>
							</table>
							<br>
						</div>
					</div>
				</div><!-- /post-body-content -->

				<div id="postbox-container-1" class="postbox-container">
					<div id="submitdiv" class="stuffbox">
						<h3><span class="hndle">ステータス</span></h3>
						<div class="inside">
							<div class="submitbox" id="submitcomment">
								<div id="minor-publishing">

									<div id="minor-publishing-actions">
<!--										<div id="preview-action">-->
<!--											<a class="preview button" href="http://www.luck2.localhost/company#comment-177" target="_blank">コメントを表示</a>-->
<!--										</div>-->
<!--										<div class="clear"></div>-->
									</div>

									<div id="misc-publishing-actions">

										<div class="misc-pub-section misc-pub-comment-status" id="comment-status-radio">
											<label class="approved"><input type="radio" checked="checked" name="visible" value="1">公開</label>&nbsp;|&nbsp;
											<label class="spam"><input type="radio" name="visible" value="0">非公開</label>
										</div>

										<div class="misc-pub-section misc-pub-comment-author-ip">
											<strong>並び順</strong><br>
											<input type="text" name="order_list" size="3" />
										</div>
										<div class="misc-pub-section misc-pub-comment-author-ip">
											<input type="checkbox" name="display_above" />一番上に表示する
										</div>

									</div> <!-- misc actions -->
									<div class="clear"></div>
								</div>

								<div id="major-publishing-actions">
									<div id="delete-action">
										<a class="submitdelete deletion" href="#">ゴミ箱へ移動</a>
									</div>
									<div id="publishing-action">
										<input type="submit" name="save" id="save" class="button button-primary" value="更新"></div>
									<div class="clear"></div>
								</div>

							</div>
						</div>
					</div><!-- /submitdiv -->
				</div>






				<div id="postbox-container-2" class="postbox-container">
					<div id="normal-sortables" class="meta-box-sortables ui-sortable"><div id="akismet-status" class="postbox ">
							<div class="handlediv" title="クリックで切替"><br></div><h3 class="hndle"><span>コメント履歴</span></h3>
							<div class="inside">
							</div>
						</div>
					</div></div>



			</div><!-- /post-body -->
		</div>









	<?php
	} else {
		var_dump($e->getMessage());
	}
	?>

<?php elseif($_GET['action']==='new'): ?>
	<h2><?php _e('BASE To WordPress 商品の新規追加', BASE_TO_WP_NAMEDOMAIN); ?></h2>
	<?php
	if (empty($e)) {
		echo '<p>にゅうううううううううううううううううううううううううううう</p>';
	} else {
		var_dump($e->getMessage());
	}
	?>

<?php else: ?>
	<h2><?php _e('BASE To WordPress 商品管理', BASE_TO_WP_NAMEDOMAIN); ?><a href="<?php echo $items_uri.'&action=new'?>" class="add-new-h2">新規追加</a></h2>
	<?php if (empty($e)):?>
	<?php //$BaseOAuthWP->render_list($items_obj); ?>
	<form id="base-items-filter" method="get">
		<?php $ItemListTable->search_box('商品を検索','base-item-search-input'); ?>
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $ItemListTable->display(); ?>
	</form>
	<?php else: ?>
		<?php var_dump($e->getMessage()); ?>
	<?php endif; ?>

<?php endif; ?>
</div>
