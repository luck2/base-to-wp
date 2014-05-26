<?php
/*
Plugin Name: BASE To WordPress
Plugin URI: https://github.com/luck2/base-to-wp
Description: BASE To WordPress
Version: 0.0.1
Author: Luck2, Inc.
Author URI: http://www.luck2.co.jp
License:
*/

namespace BaseToWP;
//use SplClassLoader;

define( 'BASE_TO_WP_ABSPATH', dirname( __FILE__ ) );
define( 'BASE_TO_WP_NAMEDOMAIN', 'base-to-wp' );

//require BASE_TO_WP_ABSPATH . '/libs/SplClassLoader.php';
//$class_loader_base_to_wp = new SplClassLoader('BaseToWP', dirname(__DIR__));
//$class_loader_base_to_wp->register();

$base_to_wp = new BaseToWP();

/**
 * Class BaseToWP
 * @package BaseToWP
 */
class BaseToWP {
	/**
	 * @var string DBをアップデートする場合は更新
	 */
	public $db_version = "0.0.1";
	public $option_db_version_name = 'Base_to_wp_db_version';

	const NAME_DOMAIN = "base-to-wp";

	public function __construct() {

		require_once BASE_TO_WP_ABSPATH . '/OAuth/BaseOAuth.php';

		//FIXME DEVELOP define BASE_HOST_DEV
		require_once BASE_TO_WP_ABSPATH . '/config.php';
		\OAuth\BaseOAuth::$host = BASE_HOST_DEV;

		//Register Activation Hook.
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));


		// ショートコードを設定
//		$trend_short_code = new ShortCode();

		// register widget
		require_once BASE_TO_WP_ABSPATH . "/widgets/ShopWidget.php";
		add_action('widgets_init', function(){register_widget("ShopWidget");});
		require_once BASE_TO_WP_ABSPATH . "/widgets/ItemsWidget.php";
		add_action('widgets_init', function(){register_widget("ItemsWidget");});


		//TODO ショートコードとかグローバル関数とか？
//		require_once BASE_TO_WP_ABSPATH . "/functions.php";

		//#TODO jqueryない場合登録
		add_action('wp_enqueue_scripts', function(){wp_enqueue_script( 'jquery' );});

		// Plugin TextDomain
		load_plugin_textdomain( 'base_to_wp' );


		//管理画面
		add_action('init', array($this, 'install_check'));// Install check
		add_action('admin_menu', array($this, 'admin_menus'));// 管理メニューに追加するフック

		// TODO DEBUG
		add_action('wp_head', function(){
			//global $wp_query;var_dump($wp_query);
		});
	}

	/**
	 * プラグインアクティブ時
	 */
	public function activate() {
//		// 複数テーブルのアクティベート化 tableをmodel化してmodel単位で扱う
//		$bases = new bases();
//
//		// cron schedule
//		Cron::activate();
//
//		if($bases->table_exists()) {
//			//データベースが最新かどうか確認
//			if(version_compare(get_option($this->option_db_version_name, 0), $this->db_version, ">="))
//				return;
//		}
//		//ここまで実行されているということはデータベース作成が必要
//
//		//データベースが作成されない場合はSQLにエラーがあるので、$wpdb->show_errors(); と書いて確認してください
//		$bases->createTable();
//
//		// create の時のみサンプルデータをinsert
//		$bases->insert_example_data();
//
//		//データベースのバージョンを保存する
//		update_option($this->option_db_version_name, $this->db_version);

	}

	/**
	 * プラグインディアクティブ時
	 */
	public function deactivate() {
		// cron schedule
//		Cron::deactivate();
	}

	/**
	 * メニュー画面追加
	 */
	public function admin_menus() {

		function options_page() {
			echo "<h2>Test Options</h2>";
		}
		function manage_page() {
			echo "<h2>Test Manage</h2>";
		}

		/**
		 * 管理画面トップ
		 */
		function dashboard() {
			include_once BASE_TO_WP_ABSPATH . "/dashboard.php";
		}


		/**
		 * 管理画面　インストール
		 */
			function install() {
				include_once BASE_TO_WP_ABSPATH . "/install.php";
			}


		// 設定メニュー下にサブメニューを追加:
//		add_options_page('Test Options', 'Test Options', 'administrator', 'test-options', '\BaseToWP\options_page');
		// 管理メニューにサブメニューを追加
//		add_management_page('Test Manage', 'Test Manage', 'administrator', 'test-manage', '\BaseToWP\manage_page');
		// 新しいトップレベルメニューを追加
		add_menu_page('BASE To WP', 'BASE To WP', 'administrator', 'base_to_wp', '\BaseToWP\dashboard');
		// カスタムのトップレベルメニューにサブメニューを追加
		add_submenu_page('base_to_wp', 'BASE settings', 'Settings', 'administrator', 'base_to_wp_settings', '\BaseToWP\settings');
		// カスタムのトップレベルメニューに二つ目のサブメニューを追加
		if (!get_option('base_to_wp_account_activated') || isset($_GET['reset_account']) || isset($_GET['oauth']))
			add_submenu_page('base_to_wp', 'BASE install', 'Install', 'administrator', 'base_to_wp_install', '\BaseToWP\install');

	}

	/**
	 * 初期設定完了をチェック
	 */
	public function install_check(){
		//base_to_wp以外
		if (strpos($_SERVER['REQUEST_URI'],'/wp-admin/admin.php?page=base_to_wp') === false)
			return;

		//未インストールかつインストールページでない
		if ( !get_option('base_to_wp_account_activated') && !(isset($_GET['page']) && $_GET['page'] == 'base_to_wp_install' ) ) {
			wp_redirect(admin_url('admin.php?page=base_to_wp_install'));
		}
	}

}


/**
 * 管理画面　セッティング
 */
function settings() {
	include_once BASE_TO_WP_ABSPATH . "/settings.php";
}
