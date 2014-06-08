<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/06/08
 * Time: 23:57
 */

namespace BaseToWP;

/**
 * Class AdminMenus
 * @package BaseToWP
 */
class AdminMenus {

	/**
	 * @var String Hook name
	 */
	public $hook_dashboard;
	public $hook_items;
	public $hook_design;
	public $hook_order;
	public $hook_settings;
	public $hook_install;

	public function __construct() {

	}

	/**
	 * メニュー画面追加
	 */
	public function admin_menus() {
		// BASE To WP
		add_menu_page( 'BASE To WP', 'BASE To WP', 'administrator', 'base_to_wp');
		// BASE To WP > ダッシュボード
		$this->hook_dashboard = add_submenu_page( 'base_to_wp', 'BASE To WP > dashboard', 'Dashboard', 'administrator', 'base_to_wp', function () {
			include_once BASE_TO_WP_ABSPATH."/dashboard.php";
		} );
		// BASE To WP > 商品管理
		$this->hook_items = add_submenu_page( 'base_to_wp', 'BASE To WP > Items', 'Items', 'administrator', 'base_to_wp_items', function () {
			include_once BASE_TO_WP_ABSPATH."/items.php";
		} );
		// BASE To WP > デザイン編集
		$this->hook_design = add_submenu_page( 'base_to_wp', 'BASE To WP > Design', 'Design', 'administrator', 'base_to_wp_design', function () {
			include_once BASE_TO_WP_ABSPATH."/design.php";
		} );
		// BASE To WP > 注文管理
		$this->hook_order = add_submenu_page( 'base_to_wp', 'BASE To WP > Orders', 'Orders', 'administrator', 'base_to_wp_orders', function () {
			include_once BASE_TO_WP_ABSPATH."/orders.php";
		} );
		// BASE To WP > セッティング
		$this->hook_settings = add_submenu_page( 'base_to_wp', 'BASE To WP > settings', 'Settings', 'administrator', 'base_to_wp_settings', function () {
			include_once BASE_TO_WP_ABSPATH."/settings.php";
		} );
		// BASE To WP > インストール
		if (!get_option( 'base_to_wp_account_activated') || isset($_GET['reset_account']) || isset($_GET['oauth'])) {
			$this->hook_install = add_submenu_page( 'base_to_wp', 'BASE To WP > install', 'Install', 'administrator', 'base_to_wp_install', function () {
				include BASE_TO_WP_ABSPATH . "/install.php";
			} );
		}

		//Contextual helps
		add_filter('contextual_help', array($this, 'attach_contextual_helps'), 10, 3);


		//Screen options #FIXME Save
		$add_option = function() {
			$option = 'per_page';
			$args = array(
				'label' => 'items',
				'default' => 10,
				'option' => 'item_per_page'
			);
			add_screen_option( $option, $args );
		};
		add_action( "load-{$this->hook_items}", $add_option );

		#FIXME 保存側
		function base_to_wp_set_option($status, $option, $value) {
			if ( 'item_per_page' == $option ) return $value;
			return $status;
		}
		add_filter('set-screen-option', 'base_to_wp_set_option', 10, 3);


		// 設定メニュー下にサブメニューを追加
//		add_options_page('Test Options', 'Test Options', 'administrator', 'test-options', function(){ echo "<h2>Test Options</h2>"; });
		// 管理メニューにサブメニューを追加
//		add_management_page('Test Manage', 'Test Manage', 'administrator', 'test-manage', function(){ echo "<h2>Test Manage</h2>";});

	}

	/**
	 * Hook contextual_help
	 * ヘルプのセッティングにフック WP_Screen
	 * 
	 * @param $contextual_help
	 * @param $screen_id
	 * @param $screen
	 *
	 * @return string
	 */
	public function attach_contextual_helps ($contextual_help, $screen_id, $screen) {
		//var_dump($contextual_help,$screen_id, $screen);
		switch ($screen_id) {
			case $this->hook_dashboard :
				$contextual_help = '<p>ダッシュボードのヘルプ</p>';
				break;
			case $this->hook_items :
				$contextual_help = '<p>商品管理のヘルプ</p>';
				break;
			case $this->hook_design :
				$contextual_help = '<p>デザインのヘルプ</p>';
				break;
			case $this->hook_order :
				$contextual_help = '<p>注文管理のヘルプ</p>';
				break;
			case $this->hook_settings :
				$contextual_help = '<p>セッティングのヘルプ</p>';
				break;
			case $this->hook_install :
				$contextual_help = '<p>インストールのヘルプ</p>';
				break;
		}
		return $contextual_help;
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