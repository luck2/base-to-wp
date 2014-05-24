<?php
/*
Plugin Name: BASE To WordPress
Plugin URI: https://github.com/luck2/base-to-wp
Description: BASE To WordPress
Author: Luck2, Inc.
Version: 0.0.1
Author URI: http://www.luck2.co.jp
*/

namespace BaseToWP;
//use SplClassLoader;
//use SnsTrend\Model\Trends;

define( 'BASE_TO_WP_ABSPATH', dirname( __FILE__ ) );

//require BASE_TO_WP_ABSPATH . '/libs/SplClassLoader.php';
//$class_loader_sns_trend = new SplClassLoader('BaseToWP', dirname(__DIR__));
//$class_loader_sns_trend->register();

include_once BASE_TO_WP_ABSPATH . '/OAuth/BaseOAuth.php';

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
		//Register Activation Hook.
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));

		//cron schedule
//		$cron = new Cron();

		// カスタムポストタイプ登録
//		$trend = new CustomPostType();

		// trendsテーブルの一覧ページ
//		$trend_data = new Data();

		// 設定画面を追加
//		$trend_option = new Option();

		// ショートコードを設定
//		$trend_short_code = new ShortCode();

		// アーカイブページ（CustomPostType::POST_TYPE）
//		$archive_Trend = new ArchiveTrend();

		//#TODO widgets namespaceが使えない
//		require_once SNS_TREND_ABSPATH . "/widgets/sns_trend_ranking_widget.php";
//		add_action('widgets_init', function(){register_widget("SnsTrendRankingWidget");});

		//TODO ショートコードとかグローバル関数とか？
//		require_once SNS_TREND_ABSPATH . "/functions.php";

		//#TODO jqueryない場合登録
		add_action('wp_enqueue_scripts', function(){wp_enqueue_script( 'jquery' );});

		//#TODO 管理メニューに追加するフック
		add_action('admin_menu', array($this, 'admin_menus'));

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
//		$trends = new Trends();
//
//		// cron schedule
//		Cron::activate();
//
//		if($trends->table_exists()) {
//			//データベースが最新かどうか確認
//			if(version_compare(get_option($this->option_db_version_name, 0), $this->db_version, ">="))
//				return;
//		}
//		//ここまで実行されているということはデータベース作成が必要
//
//		//データベースが作成されない場合はSQLにエラーがあるので、$wpdb->show_errors(); と書いて確認してください
//		$trends->createTable();
//
//		// create の時のみサンプルデータをinsert
//		$trends->insert_example_data();
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
		// mt_options_page() はTest Optionsサブメニューのページコンテンツを表示
		function mt_options_page() {
			echo "<h2>Test Options</h2>";
		}
		// mt_manage_page()はTest Manageサブメニューにページコンテんツを表示
		function mt_manage_page() {
			echo "<h2>Test Manage</h2>";
		}

		/**
		 * 管理画面トップ
		 */
		function dashboard() {
			include_once BASE_TO_WP_ABSPATH . "/dashboard.php";
		}

		/**
		 * 管理画面　セッティング
		 */
		function settings() {
			echo "<h2>Test Sublevel</h2>";
		}

		/**
		 * 管理画面　インストール
		 */
		function install() {
			include_once BASE_TO_WP_ABSPATH . "/install.php";
		}


		// 設定メニュー下にサブメニューを追加:
//		add_options_page('Test Options', 'Test Options', 'administrator', 'test-options', '\BaseToWP\mt_options_page');
		// 管理メニューにサブメニューを追加
//		add_management_page('Test Manage', 'Test Manage', 'administrator', 'test-manage', '\BaseToWP\mt_manage_page');
		// 新しいトップレベルメニューを追加(分からず屋):
		add_menu_page('BASE To WP', 'BASE To WP', 'administrator', 'base_to_wp', '\BaseToWP\dashboard');
		// カスタムのトップレベルメニューにサブメニューを追加:
		add_submenu_page('base_to_wp', 'BASE settings', 'Settings', 'administrator', 'base_to_wp_settings', '\BaseToWP\settings');
		// カスタムのトップレベルメニューに二つ目のサブメニューを追加:
		add_submenu_page('base_to_wp', 'BASE install', 'Install', 'administrator', 'base_to_wp_install', '\BaseToWP\install');
	}

}


