<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/05/22
 * Time: 13:21
 */

error_reporting(E_ALL);

// $_GET initialize
empty($_GET['step']) and $_GET['step'] = '1';
empty($_GET['oauth']) and $_GET['oauth'] = null;
empty($_GET['reset_account']) and $_GET['reset_account'] = null;
//var_dump($_GET);

$admin_uri = admin_url('admin.php');
$install_uri = admin_url('admin.php?page=base_to_wp_install');
$redirect_uri = $install_uri . '&step=5&oauth=1';
$redirect_uri = $install_uri . '&installing=1&step=5&oauth=1';//FIXME DEVELOP
list($next_uri) = explode('&step', $admin_uri . '?' . $_SERVER['QUERY_STRING']);

if ($_POST) {
	// client id 保存
	if ($_GET['step'] == '3') {
		if ($_POST['base_to_wp_client_id'] != '' && $_POST['base_to_wp_client_secret'] != '') {
			update_option('base_to_wp_client_id', $_POST['base_to_wp_client_id']);
			update_option('base_to_wp_client_secret', $_POST['base_to_wp_client_secret']);
			update_option('base_to_wp_redirect_uri', $redirect_uri);
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

//TODO DEBUG
debug_show_options();

?>


<div class="wrap">
	<h2>BASE To WordPress Setup</h2>

	<?php
	if (get_option('base_to_wp_account_activated') != '1' && $_GET['step'] == '1' ) {
		$message = "BASE APIのDeveloper登録を完了してください。";
	} elseif (isset($_GET['reset_account']) && $_GET['step']==='1') {
		$message = "BASE APIクライアント登録が完了している場合は同じクライアントが使用できます。「Step1」をスキップして次のステップへ進んでください。";
	}
	?>
	<?php if ( isset($message) ) : ?>
		<div id="message" class="updated fade">
			<p><?php _e($message, BASE_TO_WP_NAMEDOMAIN); ?></p>
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
		<h3><span style="font-size: 150%">Step 2 :</span> BASE APP で取得した Client ID と Client Secret を入力</h3>
		<p>BASE APIとのインターフェイスにワードプレスを有効にするには、キーを入力する必要があります。あなたは<a href="http://thebase.in/apps/" target="_blank">このBASE APPページ</a>を訪問して、前のステップで登録されたアプリケーションを選択することでそれらを見つけることができます。</p>
		<p style="text-align:center"><img src="<?php echo plugin_dir_url(__FILE__) ?>/images/wizard_2.png" alt="Finding the Application Keys" /></p>
		<p><strong>Client ID</strong> と <strong>Client Secret</strong> を入力してください。</p>
		<form method="post" action="<?php echo $next_uri . '&step=3'; ?>">
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="base_to_wp_client_id">Client ID</label></th>
					<td><input name="base_to_wp_client_id" type="text" id="base_to_wp_client_id" value="<?php echo get_option('base_to_wp_client_id'); ?>" class="regular-text" /></td>
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
			$client_id = get_option('base_to_wp_client_id'),
			$redirect_uri = get_option('base_to_wp_redirect_uri'),
			$scope = 'read_users read_users_mail read_items read_orders write_items write_orders',//使用範囲
			$stage = 'hogehoge'
		);
	?>
		<h3><span style="font-size: 150%">Step 4 :</span> BASE アカウントの認証</h3>
		<p>もう少し！今すぐあなたのBASEアカウントにアクセスできるようにあなたのブログを承認する必要があります。</p>
		<p>下のボタンをクリックすると、api.thebase.in へ移動します。あなたがすでにログインしている場合は、あなたのブログを認可するためのオプションが表示されます。「アプリを認証する」ボタンを押して、自動でここへ戻って来ます。</p>
		<p style="text-align:center">
			<a href="<?php echo $authorize_uri; ?>" class="button">BASE API おーそりぼたん</a>
			　/　<a href="<?php echo $admin_uri . '?page=base_to_wp_install&step=5&oauth=1&code=111&state=debug'; ?>" class="button">#DEBUG BASE API おーそりが成功したていぼたん</a>
		</p>
	<?php endif; ?>

	<?php if ($stage == '5') :

		try {

			if ($_GET['state'] !== 'debug' ) : //FIXME DEBUG

				//認可コードからaccess_token,refresh_tokenを取得して保存する
				$BaseOAuth = new \OAuth\BaseOAuth(
					get_option('base_to_wp_client_id'),
					get_option('base_to_wp_client_secret'),
					get_option('base_to_wp_redirect_uri')
				);
				$response = $BaseOAuth->getToken(
					$grant_type = 'authorization_code',
					$code = $_GET['code']
				);
//				var_dump($response);

			//FIXME DEBUG
			else :
				$BaseOAuth = new \OAuth\BaseOAuth();
				$BaseOAuth->http_code = 200;
				$response = new stdClass();
				$response->access_token = 'debug_access_token';
				$response->expires_in = '3600';
				$response->refresh_token = 'debug_refresh_token';
			endif;
			//FIXME DEBUG

			if ($BaseOAuth->http_code != 200)
				throw new Exception('Error: bad response.',400);

			//Update WP options
			update_option('base_to_wp_access_token', $response->access_token);
			update_option('base_to_wp_access_token_expires', (int) date_i18n('U') + (int) $response->expires_in);
			update_option('base_to_wp_refresh_token', $response->refresh_token);
			update_option('base_to_wp_refresh_token_expires', (int) date_i18n('U') + (60 * 60 * 24 * 30) - 10);//30日後まで
			update_option('base_to_wp_account_activated', '1');
			//初期設定
			update_option("base_to_wp_hogehoge", '0');
			update_option('base_to_wp_piyopiyo', array(
				1 => 'bar',
				2 => get_bloginfo('name'),
				3 => 'from:foo',
				'aaa' => 'bbbb',
			));
			?>
			<h3><span style="font-size: 150%">Step 5 :</span> おめでとう！すべてが完了しました！</h3>
			<p>あなたのBASEアカウントにアクセスするためにこのブログを承認しました。あなたのBASEショップはワードプレスと連携することができます。</p>
			<p style="text-align:right">
				<a class="button" href="<?php echo admin_url('admin.php?page=base_to_wp'); ?>">完了</a>
			</p>
		<?php
		} catch (Exception $e) {
		?>
			<h3>何かが間違っていた...</h3>
			<p><?php $e->getMessage(); ?></p>
			<p>BASE To WordPressは、あなたのBASEアカウントを認証することができませんでした。</p>
		<?php } ?>

	<?php endif; ?>
		<br />
		<small><a href="<?php echo $install_uri . '&reset_account=1&step=1'; ?>">[再セットアップ]</a></small>
	</div>
</div>



