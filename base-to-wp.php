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

//use SplClassLoader;

define( 'BASE_TO_WP_ABSPATH', dirname( __FILE__ ) );
define( 'BASE_TO_WP_NAMEDOMAIN', 'base-to-wp' );

// BaseOAuth
require_once BASE_TO_WP_ABSPATH . '/OAuth/BaseOAuth.php';
//FIXME DEVELOP define BASE_HOST_DEV
require_once BASE_TO_WP_ABSPATH . '/config.php';
\OAuth\BaseOAuth::$host = BASE_HOST_DEV;

require_once BASE_TO_WP_ABSPATH . '/BaseOAuthWP.php';


//require BASE_TO_WP_ABSPATH . '/libs/SplClassLoader.php';
//$class_loader_base_to_wp = new SplClassLoader('BaseToWP', dirname(__DIR__));
//$class_loader_base_to_wp->register();

$BaseToWP = new BaseToWP();

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

		// Plugin TextDomain
		load_plugin_textdomain( self::NAME_DOMAIN, false, dirname(plugin_basename(__FILE__)).'/languages');

		//グローバル関数とか？
//		require_once BASE_TO_WP_ABSPATH . "/functions.php";

		//Register Activation Hook.
		register_activation_hook(__FILE__, array($this, 'activate'));
		register_deactivation_hook(__FILE__, array($this, 'deactivate'));
		register_uninstall_hook(__FILE__, array($this, 'uninstall'));

		// Register Short Code.
		require_once BASE_TO_WP_ABSPATH . '/ShortCode.php';
		add_shortcode('base', array( new \BaseToWP\ShortCode(), 'base' ));

		// Register widget
		require_once BASE_TO_WP_ABSPATH . "/widgets/ShopWidget.php";
		add_action('widgets_init', function(){ register_widget('BaseToWPShopWidget'); });
		require_once BASE_TO_WP_ABSPATH . "/widgets/ItemsWidget.php";
		add_action('widgets_init', function(){ register_widget('BaseToWPItemsWidget'); });

		// jqueryない場合登録
		add_action('wp_enqueue_scripts', function(){wp_enqueue_script( 'jquery' );});

		//管理メニュー TODO ページクラスに分ける
		require_once BASE_TO_WP_ABSPATH . '/AdminMenus.php';
		$AdminMenus = new \BaseToWP\AdminMenus();
		add_action('init', array($AdminMenus, 'install_check'));// Install check
		add_action('admin_menu', array($AdminMenus, 'admin_menus'));// 管理メニューに追加するフック


		require_once BASE_TO_WP_ABSPATH . '/items.php';
		$items_page = new ItemsPage('base_to_wp',__('BASE To WP > Items',BaseToWP::NAME_DOMAIN),__('Items',BaseToWP::NAME_DOMAIN), 'administrator','base_to_wp_items');

		require_once BASE_TO_WP_ABSPATH . '/item-new.php';
		$item_new_page = new ItemNewPage('base_to_wp',__('BASE To WP > New Item',BaseToWP::NAME_DOMAIN),__('New item',BaseToWP::NAME_DOMAIN), 'administrator','base_to_wp_new_item');

		require_once BASE_TO_WP_ABSPATH . '/orders.php';
		$orders_page = new OrdersPage('base_to_wp',__('BASE To WP > Orders',BaseToWP::NAME_DOMAIN),__('Orders',BaseToWP::NAME_DOMAIN), 'administrator','base_to_wp_orders');

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
	 * プラグインアンインストール時
	 */
	public function uninstall() {
	}



}
