<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/05/22
 * Time: 13:21
 */

//TODO DEBUG
//ini_set('display_errors', true);
//error_reporting(E_ALL);
//debug_base();


// $_GET initialize
//BASE API 認可コードのリダイレクトを受けた場合はStep5 それ以外は$_GET['step']がある場合は$_GET['step']ない場合はOptionから取得
$_GET['step'] = ( isset($_GET['state']) && $_GET['state'] === 'oauth' ) ?
	'5' :
	( isset($_GET['step']) ? $_GET['step'] : get_option('base_to_wp_install_stage','1') );
empty($_GET['reset_account']) and $_GET['reset_account'] = null;

$admin_uri = admin_url('admin.php');
$install_uri = admin_url('admin.php?page=base_to_wp_install');
$redirect_uri = $install_uri;
$redirect_uri = $install_uri . '&installing=1&step=5&oauth=1';//FIXME DEVELOP
list($next_uri) = explode('&step', $admin_uri . '?' . $_SERVER['QUERY_STRING']);
$message='';$user=null;$authorize_uri=null;



try {

	switch ($_GET['step']) :
		case '1':
			if (get_option('base_to_wp_account_activated') != '1') {
				$message .= "BASE APIのDeveloper登録を完了してください。";
			} elseif (isset($_GET['reset_account'])) {
				$message .= "BASE APIクライアント登録が完了している場合は同じクライアントが使用できます。「Step1」をスキップして次のステップへ進んでください。";
			}
			break;
		case '2':
			break;
		case '3':
			// client id 保存
			if (isset($_POST['submit'])) {
				update_option('base_to_wp_client_secret', $_POST['base_to_wp_client_secret']);
				update_option('base_to_wp_client_id', $_POST['base_to_wp_client_id']);
				update_option('base_to_wp_redirect_uri', $redirect_uri);
				if ($_POST['base_to_wp_client_id'] === '') {
					$_GET['step'] = '2';
					$message .= "Client ID を入力してください。<br>";
				}
				if ($_POST['base_to_wp_client_secret'] === '') {
					$_GET['step'] = '2';
					$message .= "Client Secret を入力してください。<br>";
				}
			}

			//認可コードを取得
			$BaseOAuthWP = new BaseOAuthWP();
			$authorize_uri = $BaseOAuthWP->getAuthorize(
				null, null,
				$scope = 'read_users read_users_mail read_items read_orders write_items write_orders read_savings',//使用範囲
				$stage = 'oauth'
			);
			break;
		case '4':
			break;
		case '5':

			if (isset($_GET['debug'])) {//FIXME DEBUG
				debug_update_options();
				break;
			}

			if ( isset($_GET['code']) ) {
				//code=xxxxxxxxxxxxxxxxxxxx&state=oauth
				//FIXME 認可コードからaccess_token,refresh_tokenを取得して保存する
				$BaseOAuthWP = new BaseOAuthWP();
				$tokens = $BaseOAuthWP->getToken('authorization_code', $_GET['code']);

				//ショップ情報取得
				$user = $BaseOAuthWP->getUsers();

				//Update WP options
				//初期設定
				update_option("base_to_wp_shop_info", (array)$user);

				update_option('base_to_wp_account_activated', '1');

			} else {
				//error=access_denied&error_description=user_reject&state=oauth
				throw new \Exception('Error: user_reject');
			}
			break;
	endswitch;

} catch (Exception $e) {
//	echo '<pre>';
//	var_dump($e);
//	echo '</pre>';
	if ($_GET['step'] === '5') {
		$error_comment = '<p>BASE To WordPressは、あなたのBASEアカウントを認証することができませんでした。</p>';
	}
}

//Update stage
update_option('base_to_wp_install_stage', $_GET['step']);

// Stage 呼び出し
$stage = get_option('base_to_wp_install_stage');
//var_dump($stage);
?>


<div class="wrap">
	<h2>BASE To WordPress Setup</h2>

	<?php if ( $message ) : ?>
		<div id="message" class="updated fade">
			<p><?php _e($message, BASE_TO_WP_NAMEDOMAIN); ?></p>
		</div>
	<?php endif; ?>

	<div style="width: 70%;">
	<?php if ( isset($e) ) : ?>
		<h3>何かが間違っていた...</h3>
		<p><?php _e($e->getMessage(), BASE_TO_WP_NAMEDOMAIN); ?></p>
		<?php echo ($error_comment) ? __($error_comment, BASE_TO_WP_NAMEDOMAIN) :''; ?>

	<?php elseif ($stage == '1') : ?>
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

	<?php elseif ($stage == '2') : ?>
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
			<p class="submit" style="text-align:right"><input type="submit" name="submit" class="button-primary" value="保存して次へ" /></p>
		</form>
	<?php elseif ($stage == '3') : ?>

		<?php if (! isset($e)) : ?>
		<h3><span style="font-size: 150%">Step 4 :</span> BASE アカウントの認証</h3>
		<p>もう少し！今すぐあなたのBASEアカウントにアクセスできるようにあなたのブログを承認する必要があります。</p>
		<p>下のボタンをクリックすると、api.thebase.in へ移動します。あなたがすでにログインしている場合は、あなたのブログを認可するためのオプションが表示されます。「アプリを認証する」ボタンを押して、自動でここへ戻って来ます。</p>
<!--		<p style="text-align:center"><img src="--><?php //echo plugin_dir_url(__FILE__).'/images/wizard_3.png'; ?><!--" alt="アプリを承認する" /></p>-->

			<p style="text-align:center">
			<a href="<?php echo $authorize_uri; ?>" class="button">アプリを承認する</a>
			　/　<a href="<?php echo $next_uri . '&step=5&debug=1'; ?>" class="button">#DEBUG SET AUTH KEY</a>
		</p>
		<?php endif; ?>

	<?php elseif ($stage == '5') : ?>
		<h3><span style="font-size: 150%">Step 5 :</span> おめでとう！すべてが完了しました！</h3>
		<p>あなたのBASEアカウントにアクセスするためにこのアプリを承認しました。あなたのBASEショップ<?php esc_html_e("（{$user->shop_name}）"); ?>はワードプレスと連携することができます。</p>
		<table style="padding-left: 5px;">
			<tbody>
			<tr >
				<td rowspan="5" width="30%"><img src="<?php echo esc_html($user->logo); ?>" alt="shop logo" style="width: 90%;"/></td>
			</tr>
			<tr>
				<td style="width: 16%;">Shop ID</td><td><?php echo esc_html($user->shop_id); ?></td>
			</tr>
			<tr>
				<td>Shop Name</td><td><?php echo esc_html($user->shop_name); ?></td>
			</tr>
			<tr>
				<td>Shop Name</td><td><?php echo esc_html($user->shop_introduction); ?></td>
			</tr>
			<tr>
				<td>Shop URL</td><td><?php echo esc_html($user->shop_url); ?></td>
			</tr>
			</tbody>
		</table>
		<p style="text-align:right">
			<a class="button" href="<?php echo admin_url('admin.php?page=base_to_wp'); ?>">完了</a>
		</p>

	<?php endif; ?>

		<br />
		<small><a href="<?php echo $install_uri . '&reset_account=1&step=1'; ?>">[再セットアップ]</a></small>
	</div>
</div>



