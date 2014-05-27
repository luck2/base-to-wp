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

	//TODO option のkeyをチェック　不正ならinstall reset account
	$BaseOAuth = new \OAuth\BaseOAuth(
		$client_id     = get_option('base_to_wp_client_key'),
		$client_secret = get_option('base_to_wp_client_secret'),
		$redirect_uri  = get_option('base_to_wp_redirect_uri'),
		$access_token  = get_option('base_to_wp_access_token'),
		$refresh_token = get_option('base_to_wp_refresh_token')
	);

	//TODO アクセストークンの有効期限を調べて切れてるならリフレッシュトークンから新しく取得
	if (date_i18n('U') > get_option('base_to_wp_access_token_expires')) {

		// リフレッシュトークンの期限が切れている場合
		if ( date_i18n('U') > get_option('base_to_wp_refresh_token_expires') )
			throw new Exception( 'Refresh token expired.', 2 );

		$response = $BaseOAuth->getToken();
		if ( $BaseOAuth->http_code != 200 )
			throw new Exception( 'Bad response.', 400 );
		//Update WP options
		update_option('base_to_wp_access_token', $response->access_token);
		update_option('base_to_wp_access_token_expires', (int) date_i18n('U') + (int) $response->expires_in);
		update_option('base_to_wp_refresh_token', $response->refresh_token);
		update_option('base_to_wp_refresh_token_expires', (int) date_i18n('U') + (60 * 60 * 24 * 30) - 60);//30日後まで

//		echo '<hr>' . date('Y/m/d H:i:s', get_option('base_to_wp_access_token_expires'));;
//		echo '<hr>' . date('Y/m/d H:i:s', get_option('base_to_wp_refresh_token_expires'));;
	}
?>
	<h3>BASEショップ情報</h3>
	<?php
	$user = $BaseOAuth->getUsers();
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
	$items = $BaseOAuth->getItems(array());
	$BaseOAuth->render_list();
	?>
	<h4>個別(ID:<?php echo $id=26371;?>)</h4>
	<?php
	$item = $BaseOAuth->getItems($id);
	$BaseOAuth->render_list();

	?>

	<h3>カテゴリー情報の一覧</h3>
	<h3>商品のカテゴリー情報</h3>
	<h3>注文情報の一覧</h3>


	<h3>引き出し申請情報</h3>



<?php
} catch (Exception $e) {
	// 有効なアクセストークンが取得できないならinstallページに移動させるリンクを表示
	if ($e->getCode() === 2) {
	?>
		<p>アクセストークが取得できません。BASEクライアント認証の<a href="<?php echo $reset_account_uri; ?>">再セットアップ</a>を行ってください。</p>
	<?php
	} else {
		die($e->getMessage());
	}
}
?>



