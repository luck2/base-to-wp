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
class ItemNewPage
{
	public $hook;
	public $title;
	public $menu;
	public $permissions;
	public $slug;
	public $screen_id;


	public $items_uri;
	public $items_new_uri;
	public $item;
	public $error;
	public $debug;
	public $validate_errors;
	public $message;


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
		$this->screen_id = add_submenu_page($this->hook,$this->title, $this->menu, $this->permissions,$this->slug, array($this,'render_page'),10);

		// Add callbacks for this screen only
		add_action('load-'.$this->screen_id,  array($this,'page_actions'),9);

		/* Enqueue WordPress' script for handling the metaboxes */
		add_action('admin_print_scripts-'.$this->screen_id, function(){wp_enqueue_script( 'postbox' );});
		add_action('admin_footer-'.$this->screen_id,array($this,'footer_scripts'));
		//Add some metaboxes to the page
		add_action('add_meta_boxes_'.$this->screen_id, array($this, 'metaboxes'));

		/*@deprecated 3.3.0 Use get_current_screen()->add_help_tab() or get_current_screen()->remove_help_tab() instead. */
		//add_filter('contextual_help', array($this, 'attach_contextual_helps'), 10, 3);
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
		$screen->add_option('layout_columns', array('max' => 2, 'default' => 2));
//		var_dump($screen);
	}

	/**
	 * __('Overview') => contextual_help
	 *
	 * @Hook contextual_help
	 * @deprecated 3.3.0 Use get_current_screen()->add_help_tab() or
	 *                   get_current_screen()->remove_help_tab() instead.
	 *
	 * @param $contextual_help
	 * @param $screen_id
	 * @param $screen
	 *
	 * @return string
	 */
	public function attach_contextual_helps ($contextual_help, $screen_id, $screen) {
//		var_dump($contextual_help,$screen_id, $screen);
		if ($screen_id === $this->screen_id) {
			$contextual_help .= '<p>Help contents.</p>';
		}
		return $contextual_help;
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
	 *
	 */
	public function metaboxes() {
		$screen = get_current_screen();//WP_Screen
		//Normal
		add_meta_box('debug','Debug',array($this,'render_debug'),$screen,'normal','high');
		//Advanced
		add_meta_box('example2','Example 2',array($this,'render_metabox'));
		add_meta_box('example3','Example 3',array($this,'render_metabox'));
		//Side
		add_meta_box('submitdiv',__('Publish'),array($this,'render_submit_box'),$screen,'side','high');
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
		empty( $_GET['item'] ) and $_GET['item'] = null;

		$this->items_uri     = admin_url( 'admin.php?page=base_to_wp_items' );
		$this->items_new_uri = admin_url( 'admin.php?page=base_to_wp_new_item' );

		try {

			$BaseOAuthWP = new BaseOAuthWP();
			$BaseOAuthWP->checkToken();

			if ( $_GET['item'] > 0 ) {
				//Edit
				$item = $BaseOAuthWP->getItem( $_GET['item'] );
				//FIXME DEBUG
				$this->debug = $BaseOAuthWP->render_list($item, true);
			} else {
				//New
				$item             = new stdClass();
				$item->title      = '';
				$item->detail     = '';
				$item->price      = '';
				$item->stock      = '';
				$item->visible    = '';
				$item->list_order = '';
			}

			if ($_POST) {
				echo '<pre>';var_dump($_POST);echo '</pre>';
				check_admin_referer('some-action-nonce');

				$item->title      = $_POST['title'];
				$item->price      = $_POST['price'];
				$item->detail     = $_POST['detail'];
				$item->visible    = $_POST['visible'];
				$item->stock      = $_POST['stock'];
				$item->list_order = $_POST['list_order'];

				if ($this->validate_item($item)) {//Save

					if (empty($item->item_id)) {//Add
						$response = $BaseOAuthWP->addItem($item);
						add_settings_error(
							'item-new',
							esc_attr( 'item_updated' ),
							$message='新規商品を追加しました。<a href="#">商品を表示する</a>',
							$type='updated'
						);

						//TODO リロードで同じ商品ができる
						//TODO 追加後新規作成した商品が表示されるが、そのまま編集すると違う商品が作成される。
					} else {//Edit
						$response = $BaseOAuthWP->editItem($item);
						add_settings_error(
							'item-new',
							esc_attr( 'item_updated' ),
							$message='商品を編集しました。<a href="http://www.luck2.localhost/?p=3445">商品を表示する</a>',
							$type='updated'
						);

						//TODO ブラウザリロードで再書込される
					}
					var_dump($response);
				} else {//Validate error
					add_settings_error(
						'item-new',
						esc_attr( 'item_error' ),
						$message=$this->validate_errors,
						'error'
					);
				}
			}

			$this->item = $item;
		} catch ( Exception $e ) {
			add_settings_error(
				'item-new',
				esc_attr($e->getCode()),
				__($message=$e->getMessage()),
				'error'
			);
		}
		?>
		<div class="wrap">
			<h2> <?php echo esc_html($this->title);?> </h2>
			<?php settings_errors('item-new'); ?>
			<?php settings_errors('validate'); ?>
			<?php if (empty($e)) : ?>
				<form name="my_form" method="post">
					<input type="hidden" name="action" value="some-action">
					<?php
					wp_nonce_field( 'some-action-nonce' );
					/* Used to save closed metaboxes and their order */
					wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
					wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
					?>
					<div id="poststuff">
						<div id="post-body" class="metabox-holder columns-<?php echo 1==get_current_screen()->get_columns()?'1':'2'; ?>">
							<div id="post-body-content">
								<?php $this->render_contents($this->item); ?>
							</div>
							<div id="postbox-container-1" class="postbox-container">
								<?php do_meta_boxes('','side',null); ?>
							</div>
							<div id="postbox-container-2" class="postbox-container">
								<?php do_meta_boxes('','normal',null);  ?>
								<?php do_meta_boxes('','advanced',null); ?>
							</div>
						</div> <!-- #post-body -->
					</div> <!-- #poststuff -->
				</form>
			<?php else : ?>
			<?php endif; ?>
		</div><!-- .wrap -->
		<?php
	}

	private function render_contents($item) {
		?>
		<div id="titlediv">
			<div id="titlewrap">
				<label class="screen-reader-text" id="title-prompt-text" for="title">ここにタイトルを入力</label>
				<input type="text" name="title" size="30" value="<?php esc_attr_e( $item->title ) ?>" id="title"
				       autocomplete="off" placeholder="商品名をここに入力">
			</div>
			<div class="inside">
				<div id="edit-slug-box" class="hide-if-no-js">
					<strong>パーマリンク:</strong>
					<span id="sample-permalink" tabindex="-1">http://しょうひんしょうさいurl</span>
					<span id="view-post-btn"><a href="http://しょうひんしょうさいurl" class="button button-small">Itemを表示</a></span>
					<span id="view-post-btn2"><a href="http://baseしょうひんしょうさいurl" class="button button-small">BaseShopのItemを表示</a></span>
				</div>
			</div>
			<input type="hidden" id="samplepermalinknonce" name="samplepermalinknonce" value="b9dbdfa0cb"></div>

		<div id="namediv" class="stuffbox">
			<h3><label for="name">商品編集</label></h3>

			<div class="inside">
				<table class="form-table editcomment">
					<tbody>
					<tr>
						<td class="first"><label for="item_price">価格（税込）</label></td>
						<td><input type="text" name="price" size="30" value="<?php esc_attr_e( $item->price ) ?>" id="item_price"></td>
					</tr>
					<tr>
						<td class="first"><label for="item_detail">商品説明</label></td>
						<td>
							<textarea name="detail" style="width: 98%;" rows="6" cols="30" class="" id="item_detail"><?php esc_attr_e( $item->detail ) ?></textarea>
						</td>
					</tr>
					<tr>
						<td class="first"><label for="item_stock">在庫数</label></td>
						<td><input type="text" name="stock" size="30" value="<?php esc_attr_e( $item->stock ) ?>" id="item_stock"></td>
					</tr>
					<tr>
						<td class="first"><label for="gagaga">商品画像</label></td>
						<td><input type="text" name="gagaga" size="30" value="<?php esc_attr_e( $item->title ) ?>" id="gagaga"></td>
					</tr>
					</tbody>
				</table>
				<br>
			</div>
		</div>
	<?php
	}

	private function validate_item( $item ) {
		$this->validate_errors=array('key'=>'error',);

		return true;
	}


	public function render_submit_box() {
		?>
		<div class="submitbox" id="submititem">
			<div id="minor-publishing">
				<div id="minor-publishing-actions">
					<div id="preview-action">
						<a class="preview button" href="http://www.luck2.co.jp/?p=3659&amp;preview=true" target="wp-preview-3659" id="post-preview">プレビュー</a>
						<input type="hidden" name="wp-preview" id="wp-preview" value="">
					</div>
					<div class="clear"></div>
				</div><!-- #minor-publishing-actions -->

				<div id="misc-publishing-actions">
					<div class="misc-pub-section misc-pub-visibility" id="visibility">
						公開状態: <span id="post-visibility-display">公開</span>
						<a href="#visibility" class="edit-visibility hide-if-no-js"><span aria-hidden="true">編集</span> <span class="screen-reader-text">公開状態を編集</span></a>

						<div id="post-visibility-select" class="hide-if-js_FIXMEDEBUG">
							<input type="hidden" name="hidden_post_password" id="hidden-post-password" value="">
							<input type="checkbox" style="display:none;" name="hidden_post_sticky" id="hidden-post-sticky" value="sticky">
							<input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="public">
							<?php //var_dump($this->item); ?>
							<input type="radio" name="visible" id="visibility-radio-public" value="1" <?php echo ($this->item->visible==1)?'checked="checked"':''; ?>> <label for="visibility-radio-public" class="selectit">公開</label><br>
							<span id="sticky-span"><input id="sticky" name="sticky" type="checkbox" value="sticky"> <label for="sticky" class="selectit">この商品を先頭に固定表示</label><br></span>
							<input type="radio" name="visible" id="visibility-radio-private" value="0" <?php echo ($this->item->visible==0)?'checked="checked"':''; ?>> <label for="visibility-radio-private" class="selectit">非公開</label><br>

							<p>
								<a href="#visibility" class="save-post-visibility hide-if-no-js button">OK</a>
								<a href="#visibility" class="cancel-post-visibility hide-if-no-js button-cancel">キャンセル</a>
							</p>
						</div>
					</div><!-- .misc-pub-section -->
					<div class="misc-pub-section misc-pub-order_list">
						<label>
							<strong>並び順</strong><br>
							<input type="text" name="list_order" size="3" value="<?php echo $this->item->list_order; ?>"/>
						</label>
					</div>


				</div>
				<div class="clear"></div>
			</div>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<a class="submitdelete deletion" href="http://www.luck2.co.jp/wp-admin/post.php?post=3659&amp;action=trash&amp;_wpnonce=dbdc5cc134">ゴミ箱へ移動</a></div>

				<div id="publishing-action">
					<span class="spinner"></span>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php _e('Publish') ?>">
					<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php _e('Publish') ?>" accesskey="p"></div>
				<div class="clear"></div>
			</div>
		</div>


	<?php
	}

	public function render_debug() {
		echo $this->debug;
	}

	public function render_metabox() {
		?>
		<p> An example of a metabox <p>
	<?php
	}



}

