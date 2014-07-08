<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/06/14
 * Time: 21:57
 */


/**
 * Class AdminPage
 */
class OrdersPage
{
	public $hook;
	public $title;
	public $menu;
	public $permissions;
	public $slug;
	public $screen_id;

	/**
	 * @param $hook
	 * @param $title
	 * @param $menu
	 * @param $permissions
	 * @param $slug
	 */
	public function __construct($hook, $title, $menu, $permissions, $slug){
		$this->hook = $hook;
		$this->title = $title;
		$this->menu = $menu;
		$this->permissions = $permissions;
		$this->slug = $slug;

		add_action('admin_menu', array($this,'add_page'));
	}

	/**
	 * Add page
	 */
	public function add_page(){
		// Add the page
		$this->screen_id = add_submenu_page($this->hook,$this->title, $this->menu, $this->permissions,$this->slug,  array($this,'render_page'),10);

		// Add callbacks for this screen only
		add_action('load-'.$this->screen_id,  array($this,'page_actions'),9);

		/* Enqueue WordPress' script for handling the metaboxes */
		add_action('admin_print_scripts-'.$this->screen_id, function(){wp_enqueue_script( 'postbox' );});
		add_action('admin_footer-'.$this->screen_id,array($this,'footer_scripts'));

	}

	/*
	 * Actions to be taken prior to page loading. This is after headers have been set.
	 * call on load-$hook
	 * This calls the add_meta_boxes hooks, adds screen options and enqueue the postbox.js script.
	 */
	public function page_actions(){
		//Do add_metaboxes_{hook} , add_meta_boxes
		do_action('add_meta_boxes_'.$this->screen_id, null);
		do_action('add_meta_boxes', $this->screen_id, null);

		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id'	=> 'over_view',
			'title'	=> __('Overview'),
			'content'	=> '<p>' . __( '概要概要概要概要概要概要概要概要概要概要概要概要' ) . '</p>',
		) );
		$screen->add_help_tab( array(
			'id'	=> 'my_help_tab',
			'title'	=> __('My help tab'),
			'content'	=> '<p>' . __( 'Descriptive content that will show in My Help Tab-body goes here.' ) . '</p>',
		) );
		$screen->set_help_sidebar(
			__('<p><a href="#">ヘルプサイドバー</a></p><p><a href="#">ヘルプサイドバー2</a></p>')
		);
//		$screen->add_option('layout_columns', array('max' => 2, 'default' => 2));
		$screen->add_option('per_page', array(
			'label' => 'items',
			'default' => 10,
			'option' => 'item_per_page'
		));
//		var_dump($screen);

		#FIXME 保存側
		$set_option = function ($status, $option, $value) {
			if ( 'item_per_page' == $option ) return $value;
			return $status;
		};
		add_filter('set-screen-option', $set_option, 10, 3);


	}

	/**
	 * Prints the jQuery script to initiliase the metaboxes
	 * Called on admin_footer-*
	 */
	public function footer_scripts(){
		?>
		<script>jQuery(function(){ postboxes.add_postbox_toggles(pagenow); });</script>
	<?php
	}

	/**
	 * Renders the page
	 */
	public function render_page(){

		//TODO DEBUG
		ini_set('display_errors', true);
		error_reporting(E_ALL);
		debug_base();

		//Init
		empty($_GET['action']) and $_GET['action']=null;
		empty($_GET['unique_key']) and $_GET['unique_key']=null;
		$order=null;

		$orders_uri = admin_url('admin.php?page=base_to_wp_orders');

		try {

			$BaseOAuthWP = new BaseOAuthWP();
			$BaseOAuthWP->checkToken();

			if ($_GET['action']==='detail' && $_GET['unique_key'] !== null ) {
				$order = $BaseOAuthWP->getOrder($_GET['unique_key']);

			} else {
				$orders_obj = $BaseOAuthWP->getOrders();
				$response = json_decode($BaseOAuthWP->response,true);
				$orders = $response['orders'];

				require_once BASE_TO_WP_ABSPATH.'/OrderListTable.php';
				$OrderListTable = new \BaseToWP\OrderListTable();
				$OrderListTable->data = $orders;
				$OrderListTable->prepare_items();
			}
		} catch (Exception $e) {
			$e->getMessage();
		}
		?>
		<div class="wrap">
			<?php if ($_GET['action']==='detail' && $_GET['unique_key'] !== null ): ?>
				<h2><?php _e('BASE To WordPress 注文詳細', BASE_TO_WP_NAMEDOMAIN); ?></h2>
				<?php
				if (empty($e)) {
					$BaseOAuthWP->render_list($order);
				} else {
					var_dump($e->getMessage());
				}
				?>
			<?php else: ?>
				<h2><?php _e('BASE To WordPress 注文管理', BASE_TO_WP_NAMEDOMAIN); ?></h2>
				<?php if (empty($e)) { ?>
					<?php //$BaseOAuthWP->render_list($orders_obj); ?>
					<form id="base-items-filter" method="get">
						<?php $OrderListTable->search_box('注文を検索','base-order-search-input'); ?>
						<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>" />
						<?php $OrderListTable->display(); ?>
					</form>
				<?php
				} else {
					var_dump($e->getMessage());
				}
				?>
			<?php endif; ?>
		</div>
		<?php

	}

}

