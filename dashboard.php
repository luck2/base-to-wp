<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/05/22
 * Time: 22:17
 */

//TODO DEBUG
var_dump(array(
	'base_to_wp_install_stage' => get_option('base_to_wp_install_stage'),
	'base_to_wp_account_activated' => get_option('base_to_wp_account_activated'),

	'base_to_wp_client_key' => get_option('base_to_wp_client_key'),
	'base_to_wp_client_secret' => get_option('base_to_wp_client_secret'),
	'base_to_wp_redirect_uri' => get_option('base_to_wp_redirect_uri'),

	'base_to_wp_access_token' => get_option('base_to_wp_access_token'),
	'base_to_wp_access_token_expires' => get_option('base_to_wp_access_token_expires'),
	'base_to_wp_refresh_token' => get_option('base_to_wp_refresh_token'),
	'base_to_wp_refresh_token_expires' => get_option('base_to_wp_refresh_token_expires'),

//	'base_to_wp_request_oauth' => get_option('base_to_wp_request_oauth'),
	//初期設定
//	'base_to_wp_hogehoge' => get_option("base_to_wp_hogehoge"),
//	'base_to_wp_piyopiyo' => get_option('base_to_wp_piyopiyo'),
));


//FIXME DEVELOP define $REDIRECT_URI_DEV and BASE_HOST_DEV
include BASE_TO_WP_ABSPATH . '/config.php';
\OAuth\BaseOAuth::$host = BASE_HOST_DEV;


// option のkeyをチェック　不正ならinstall reset account

$BaseOAuth = new \OAuth\BaseOAuth(
	$client_id     = get_option('base_to_wp_client_key'),
	$client_secret = get_option('base_to_wp_client_secret'),
	$redirect_uri  = get_option('base_to_wp_redirect_uri'),
	$access_token  = get_option('base_to_wp_access_token'),
	$refresh_token = get_option('base_to_wp_refresh_token')
);

// アクセストークンの有効期限を調べて切れてるなら新しく取得
// リフレッシュトークンが有効ならリフレッシュトークンからアクセストークンを取得
// リフレッシュトークンが無効なら認可コードからアクセストークン、リフレッシュトークンを取得
// 有効なアクセストークンが取得できないならinstallページに移動させるリンクを表示

?>
<h2><?php _e('BASE To WordPress Dashboard', BASE_TO_WP_NAMEDOMAIN); ?></h2>

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

<h3>注文情報の一覧</h3>


<h3>引き出し申請情報</h3>



