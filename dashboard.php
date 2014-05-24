<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/05/22
 * Time: 22:17
 */


wp_safe_redirect( admin_url('admin.php?page=base_to_wp_install') );



// アクセストークン取得
//POST /1/oauth/token
$api = '/1/oauth/token';
$code = $_GET['code'];
echo '認証コード：' . esc_html($_GET['code']) . PHP_EOL;

// アクセストークンを取得するサンプルコード
$params = array(
	'client_id'     => $client_id,
	'client_secret' => $client_secret,
	'code'          => $code,
	'grant_type'    => 'authorization_code',
	'redirect_uri'  => $redirect_uri,
);
$headers = array(
	'Content-Type: application/x-www-form-urlencoded',
);
$request_options = array(
	'http' => array(
		'method'  => 'POST',
		'content' => http_build_query($params),
		'header'  => implode("\r\n", $headers),
	),
);
$context = stream_context_create($request_options);
$response_body = file_get_contents($api_uri.$api, false, $context);
$token = json_decode($response_body);
var_dump($token);

//GET /1/users/me
//ユーザー情報を取得
$api = '/1/users/me';
$headers = array(
	'Authorization: Bearer ' . $token->access_token,
);
$request_options = array(
	'http' => array(
		'method' => 'GET',
		'header' => implode("\r\n", $headers),
	),
);
$context = stream_context_create($request_options);
$response_body = file_get_contents($api_uri.$api, false, $context);
var_dump($response_body);

//GET /1/items
//商品情報の一覧を取得
$api = '/1/items';

$params = http_build_query(array(
//					'limit'  => 10,
//					'offset' => 0,
), null, "&", PHP_QUERY_RFC3986);

$headers = array(
	'Authorization: Bearer ' . $token->access_token,
);
$request_options = array(
	'http' => array(
		'method' => 'GET',
		'header' => implode("\r\n", $headers),
	),
);
$context = stream_context_create($request_options);
$response_body = file_get_contents($api_uri.$api.'?'.$params, false, $context);
var_dump($response_body);

?>
<h2>Test Toplevel</h2>

aaaaa
