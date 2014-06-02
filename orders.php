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

try {

	$BaseOAuthWP = new BaseOAuthWP();
	$BaseOAuthWP->checkToken();

	$orders = $BaseOAuthWP->getOrders();
	$order = $BaseOAuthWP->getOrder($unique_key='BA344A40D231FF5B');


} catch (Exception $e) {
	echo '<pre>';var_dump($e);echo '</pre>';

	// 有効なアクセストークンが取得できないならinstallページに移動させるリンクを表示
	if ($e->getCode() === 408) {
		$error_message = '<p>アクセストークが取得できません。BASEクライアント認証の<a href="' . $reset_account_uri.'">再セットアップ</a>を行ってください。</p>';
	}
}
?>
<div class="wrap">
	<h2><?php _e('BASE To WordPress Orders', BASE_TO_WP_NAMEDOMAIN); ?></h2>


	<h3>注文情報の一覧</h3>
	<?php $BaseOAuthWP->render_list($orders); ?>
	<h3>注文情報詳細</h3>
	<?php $BaseOAuthWP->render_list($order); ?>


</div>




