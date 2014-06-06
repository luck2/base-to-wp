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
debug_base();

try {

	$BaseOAuthWP = new BaseOAuthWP();
	$BaseOAuthWP->checkToken();

	$user  = $BaseOAuthWP->getUsers();

	$savings = $BaseOAuthWP->getSavings($param=array());

} catch (Exception $e) {
//	echo '<pre>';var_dump($e);echo '</pre>';

	// 有効なアクセストークンが取得できないならinstallページに移動させるリンクを表示
	$error_message='';
	if ($e->getCode() === 408) {
		$error_message = '<p>アクセストークが取得できません。BASEクライアント認証の<a href="' . $reset_account_uri.'">再セットアップ</a>を行ってください。</p>';
	}
}


?>
<div class="wrap">
	<h2><?php _e('BASE To WordPress Dashboard', BASE_TO_WP_NAMEDOMAIN); ?></h2>
	<?php if ( empty($e) ) : ?>
		<h3>BASEショップ情報</h3>
		<?php $BaseOAuthWP->render_list($user); ?>

		<!--<h3>カテゴリー情報の一覧</h3>-->
		<!--<h3>商品のカテゴリー情報</h3>-->

		<h3>引き出し申請情報</h3>
		<?php $BaseOAuthWP->render_list($savings); ?>
	<?php else : ?>
		<?php echo $e->getMessage(); ?>
		<?php echo $error_message; ?>
	<?php endif; ?>
</div>
