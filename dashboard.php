<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/05/22
 * Time: 22:17
 */

#TODO DEBUG
ini_set('display_errors', true);
error_reporting(E_ALL);
debug_show_options();


$reset_account_uri = admin_url('admin.php?page=base_to_wp_install&reset_account=1&step=1');


?>
<h2><?php _e('BASE To WordPress Dashboard', BASE_TO_WP_NAMEDOMAIN); ?></h2>
<?php

try {

	$BaseOAuthWP = new BaseOAuthWP();
	$BaseOAuthWP->checkToken();

	$user = $BaseOAuthWP->getUsers();
	$items = $BaseOAuthWP->getItems();
	$item = $BaseOAuthWP->getItems($id=26371);//FIXME getItem()にしようかな

	?>
	<h3>BASEショップ情報</h3>
	<?php $BaseOAuthWP->render_list($user); ?>
	<hr/>

	<h3>商品情報</h3>
	<h4>一覧</h4>
	<?php $BaseOAuthWP->render_list($items); ?>
	<hr/>
	<h4>個別(ID:<?php echo $id;?>)</h4>
	<?php $BaseOAuthWP->render_list($item); ?>
	<hr/>

	<h3>カテゴリー情報の一覧</h3>
	<h3>商品のカテゴリー情報</h3>
	<h3>注文情報の一覧</h3>
	<h3>引き出し申請情報</h3>



<?php
} catch (Exception $e) {
	// 有効なアクセストークンが取得できないならinstallページに移動させるリンクを表示
	if ($e->getCode() === 408) {
	?>
		<p>アクセストークが取得できません。BASEクライアント認証の<a href="<?php echo $reset_account_uri; ?>">再セットアップ</a>を行ってください。</p>
	<?php
	} else {
		die($e->getMessage());
	}
}
?>



