<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/05/22
 * Time: 22:17
 */

$reset_account_uri = admin_url('admin.php?page=base_to_wp_install&reset_account=1&step=1');


#TODO DEBUG
debug_show_options();
?>
<h2><?php _e('BASE To WordPress Dashboard', BASE_TO_WP_NAMEDOMAIN); ?></h2>
<?php

try {

	$BaseOAuthWP = new BaseOAuthWP();
	$BaseOAuthWP->checkToken();

	$user = $BaseOAuthWP->getUsers();
	$items = $BaseOAuthWP->getItems(array());
	$item = $BaseOAuthWP->getItems($id);

	?>
	<h3>BASEショップ情報</h3>
	<?php
	//var_dump($user);
	?>
	<dl>
		<?php
		foreach ( $user as $key => $value ) :
			if ($key==='logo') $value = '<img src="'.$value.'" style="width: 300px;" />';
			?>
			<dt><?php echo $key; ?></dt>
			<dd><?php echo $value; ?></dd>
		<?php endforeach; ?>
	</dl>
	<hr/>

	<h3>商品情報</h3>
	<h4>一覧</h4>
	<?php
	$BaseOAuthWP->render_list($items);//FIXME
	?>
	<h4>個別(ID:<?php echo $id=26371;?>)</h4>
	<?php
	$BaseOAuthWP->render_list($item);//FIXME

	?>

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



