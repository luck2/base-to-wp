<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/05/22
 * Time: 13:21
 */


error_reporting(E_ALL);
//TODO client_id, client_secretがない場合、BASE API developer登録を行ってもらい（済の場合省略可）、client_id, client_secretを入力させる。

// $_GET initialize
empty($_GET['step']) and $_GET['step'] = null;
empty($_GET['oauth']) and $_GET['oauth'] = null;
empty($_GET['reset_account']) and $_GET['reset_account'] = null;
empty($_GET['installing']) and $_GET['installing'] = null;//FIXME Deprecated
//var_dump($_GET);

$admin_uri = admin_url('admin.php');
$install_uri = admin_url('admin.php?page=base_to_wp_install');
$redirect_uri = $install_uri . '&step=5&oauth=1';
list($next_uri) = explode('&step', $admin_uri . '?' . $_SERVER['QUERY_STRING']);

//FIXME DEVELOP define $REDIRECT_URI_DEV and BASE_HOST_DEV
include BASE_TO_WP_ABSPATH . '/config.php';
$redirect_uri = REDIRECT_URI_DEV;
\OAuth\BaseOAuth::$host = BASE_HOST_DEV;

var_dump(array(
	'base_to_wp_client_key' => get_option('base_to_wp_client_key'),
	'base_to_wp_client_secret' => get_option('base_to_wp_client_secret'),
	'base_to_wp_install_stage' => get_option('base_to_wp_install_stage'),
	'base_to_wp_access_token' => get_option('base_to_wp_access_token'),
	'base_to_wp_refresh_token' => get_option('base_to_wp_refresh_token'),
	'base_to_wp_request_oauth' => get_option('base_to_wp_request_oauth'),
	'base_to_wp_account_activated' => get_option('base_to_wp_account_activated'),
	//初期設定
	'base_to_wp_hogehoge' => get_option("base_to_wp_hogehoge"),
	'base_to_wp_piyopiyo' => get_option('base_to_wp_piyopiyo'),
));


if ($_POST) {
	// client key 保存
	if ($_GET['step'] == '3') {
		if ($_POST['base_to_wp_client_key'] != '' && $_POST['base_to_wp_client_secret'] != '') {
			update_option('base_to_wp_client_key', $_POST['base_to_wp_client_key']);
			update_option('base_to_wp_client_secret', $_POST['base_to_wp_client_secret']);
		} else {
			$_GET['step'] = '2';
		}
	}
}

//BASE API 認可コードのリダイレクト
if ($_GET['oauth']=='1') {
	$_GET['step'] = '5';
}

// Step 保存
if ($_GET['step']) {
	update_option('base_to_wp_install_stage', $_GET['step']);
}

// Stage 呼び出し
$stage = get_option('base_to_wp_install_stage');
if ($stage == '') {
	add_option('base_to_wp_install_stage', '1');
	$stage = '1';
}

//var_dump($stage);
?>


<div class="wrap">
	<h2>BASE To WordPress Setup</h2>

	<?php if (isset($_GET['reset_account']) && $_GET['step']==='1') : ?>
		<div id="message" class="updated fade">
			<p>BASE APIクライアント登録が完了している場合は同じクライアントが使用できます。「Step1」をスキップして次のステップへ進んでください。</p>
		</div>
	<?php endif; ?>

	<div style="width: 70%;">

	<?php if ($stage == '1') : ?>

		<h3><span style="font-size: 150%">Step 1 :</span> このブログをBASE API クライアントとして登録してください</h3>
		<p>セキュリティ上の理由から、BASE To WordPress はあなたのBASEアカウントを認証し、アクセスするためにBASE APIのOAuthのプロトコルを使用しています。それを動作させるためには、BASE API のクライアントとしてあなたのサイトを登録する必要があります。BASE.inにアクセスし、クライアント登録するには、以下のボタンをクリックしてください。</p>
		<p style="text-align:center"><a class="button" href="http://apps.thebase.in/" target="_blank">BASE API クライアント登録</a></p>
		<p>あなたはBASE APPに到着すると、あなたは下の図のようなフォームが表示されます。「新しいアプリケーションを登録」をクリックしてください。いくつかのフィールドを入力することになります。</p>
		<p style="text-align:center"><img src="<?php echo plugin_dir_url(__FILE__) ?>/images/wizard_1.png" alt="BASE APP Form" /></p>
		<p>あなたは、アイコンをアップロードする必要はありません。それは完全に任意だし、誰もそれを見ていないんです。</p>
		<ol style="list-style:decimal; margin: 0 25px 15px 25px;">
			<li><strong>[名前]</strong>フィールドにブログの名前を入力します。</li>
			<li><strong>[説明]</strong>ボックスにあなたのブログの簡単な説明を入れてください。アプリケーションを識別するいくつかの単語。</li>
			<li>あなたのブログのURLをフィールドに入力します。（ex. <em>http://www.luck2.co.jp</em>）</li>
			<li>コールバックURLフィールドに<strong><code><?php echo $redirect_uri; ?></code></strong>を貼り付けます。</li>
			<li>[保存]ボタンを押して、 [次へ]表示されたページで[設定]タブをクリックします。</li>
			<li>読み書きするアクセスオプションを設定します。</li>
			<li>アプリケーションの設定を保存し、セットアップを続行するためにこのページに戻って来てください。</li>
		</ol>

		<p style="text-align:right"><a class="button" href="<?php echo $next_uri . '&step=2'; ?>">次へ進む</a></p>
	<?php endif;?>

	<?php if ($stage == '2') : ?>
		<h3><span style="font-size: 150%">Step 2 :</span> BASE APP で取得した Client Key と Client Secret を入力</h3>
		<p>BASE APIとのインターフェイスにワードプレスを有効にするには、キーを入力する必要があります。あなたは<a href="http://thebase.in/apps/" target="_blank">このBASE APPページ</a>を訪問して、前のステップで登録されたアプリケーションを選択することでそれらを見つけることができます。</p>
		<p style="text-align:center"><img src="<?php echo plugin_dir_url(__FILE__) ?>/images/wizard_2.png" alt="Finding the Application Keys" /></p>
		<p><strong>Client Key</strong> と <strong>Client Secret</strong> を入力してください。</p>
		<form method="post" action="<?php echo $next_uri . '&step=3'; ?>">
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="base_to_wp_client_key">Client Key</label></th>
					<td><input name="base_to_wp_client_key" type="text" id="base_to_wp_client_key" value="<?php echo get_option('base_to_wp_client_key'); ?>" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="base_to_wp_client_secret">Client Secret</label></th>
					<td><input name="base_to_wp_client_secret" type="text" id="base_to_wp_client_secret" value="<?php echo get_option('base_to_wp_client_secret'); ?>" class="regular-text" /></td>
				</tr>
			</table>
			<input type="hidden" name="save_app_keys" value="yes" />
			<p class="submit" style="text-align:right"><input type="submit" name="Submit" class="button-primary" value="保存して次へ" /></p>
		</form>
	<?php endif ?>

	<?php
	if ($stage == '3') :

		//認可コードを取得
		$BaseOAuth = new \OAuth\BaseOAuth();
		$authorize_uri = $BaseOAuth->getAuthorize(
			$client_id = get_option('base_to_wp_client_key'),
			$redirect_uri,
			$scope = 'read_users read_items read_orders',
			$stage = 'hogehoge'
		);
	?>
		<h3><span style="font-size: 150%">Step 4 :</span> BASE アカウントの認証</h3>
		<p>もう少し！今すぐあなたのBASEアカウントにアクセスできるようにあなたのブログを承認する必要があります。</p>
		<p>下のボタンをクリックすると、api.thebasedev.in へ移動します。あなたがすでにログインしている場合は、あなたのブログを認可するためのオプションが表示されます。「アプリを認証する」ボタンを押して、自動でここへ戻って来ます。</p>
		<p style="text-align:center">
			<a href="<?php echo $authorize_uri; ?>" class="button">BASE API おーそりぼたん</a>
			　/　<a href="<?php echo $admin_uri . '?page=base_to_wp_install&step=5&oauth=1&code=111&state=debug'; ?>" class="button">#DEBUG BASE API おーそりが成功した体ぼたん</a>
		</p>
	<?php endif; ?>

	<?php if ($stage == '5') :

//		var_dump($_GET['code']);

		if ($_GET['state'] !== 'debug' ) : //FIXME DEBUG

		//認可コードからaccess_token,refresh_tokenを取得して保存する
		$BaseOAuth = new \OAuth\BaseOAuth();
		$response = $BaseOAuth->getToken(
			$grant_type = 'authorization_code',
			$client_id = get_option('base_to_wp_client_key'),
			$client_secret = get_option('base_to_wp_client_secret'),
			$code = $_GET['code'],//FIXME Sanitize
			$redirect_uri
		);

		var_dump($response);

		//FIXME DEBUG
		else :
			$BaseOAuth = new \OAuth\BaseOAuth();
			$BaseOAuth->http_code = 200;
			$response = new stdClass();
			$response->access_token = 'debug_access_token';
			$response->refresh_token = 'debug_refresh_token';
		endif;
		//FIXME DEBUG

		if ($BaseOAuth->http_code == 200) :
			//Update WP options
			update_option('base_to_wp_access_token', $response->access_token);
			update_option('base_to_wp_refresh_token', $response->refresh_token);
			delete_option('base_to_wp_request_oauth');
			update_option('base_to_wp_account_activated', '1');
			//初期設定
			update_option("base_to_wp_hogehoge", '0');
			update_option('base_to_wp_piyopiyo', array(
				1 => 'bar',
				2 => get_bloginfo('name'),
				3 => 'from:foo'
			));
		?>
			<h3><span style="font-size: 150%">Step 5 :</span> おめでとう！すべてが完了しました！</h3>
			<p>あなたのBASEアカウントにアクセスするためにこのブログを承認しました。あなたのBASEショップはワードプレスと連携することができます。</p>
			<p style="text-align:right">
				<a class="button" href="<?php echo admin_url('admin.php?page=base_to_wp'); ?>">完了</a>
			</p>
		<?php else : ?>
			<h3>何かが間違っていた...</h3>
			<p>BASE To WordPressは、あなたのBASEアカウントを認証することができませんでした。</p>
		<?php endif; ?>
	<?php endif; ?>

		<br />
		<small><a href="<?php echo $install_uri . '&reset_account=1&step=1'; ?>">[再セットアップ]</a></small>
	</div>
</div>



