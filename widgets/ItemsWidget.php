<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/05/25
 * Time: 22:24
 */


/**
 * Class ShopWidget
 */
class ItemsWidget extends WP_Widget {

	private $BaseOAuth;

	/** constructor */
	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_base_items', 'description' => __( "BASEショップの商品一覧" ) );
		parent::WP_Widget(false, __('BASE Shop Items'), $widget_ops);

		//FIXME DEVELOP define $REDIRECT_URI_DEV and BASE_HOST_DEV
		include BASE_TO_WP_ABSPATH . '/config.php';
		\OAuth\BaseOAuth::$host = BASE_HOST_DEV;

		$this->BaseOAuth = new \OAuth\BaseOAuth(
			$client_id     = get_option('base_to_wp_client_key'),
			$client_secret = get_option('base_to_wp_client_secret'),
			$redirect_uri  = get_option('base_to_wp_redirect_uri'),
			$access_token  = get_option('base_to_wp_access_token'),
			$refresh_token = get_option('base_to_wp_refresh_token')
		);

	}

	/** @see WP_Widget::widget */
	public function widget($args, $instance) {
		$before_widget='';$after_widget='';$before_title='';$after_title='';
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);

		echo $before_widget;//<div>
		echo ( $title ) ? $before_title . $title . $after_title : '';//<h3>$title</h3>

		$items = $this->BaseOAuth->getItems();
//		var_dump($items);
//		$this->BaseOAuth->render_list();

		foreach ( $items as $item ) :
		?>
			<h4><?php _e($item->title, BASE_TO_WP_NAMEDOMAIN); ?></h4>
			<a href="<?php esc_attr_e("http://luck2dev.thebasedev.in/items/".$item->item_id);/*FIXME url*/ ?>"><img src="<?php esc_attr_e($item->img1_origin); ?>" alt="item photo"/></a>
			<p><?php echo sprintf( __('Price: %d', BASE_TO_WP_NAMEDOMAIN), $item->price); ?></p>
			<hr/>
		<?php
		endforeach;

//		array(2) {
//			[0]=>
//  object(stdClass)#3292 (14) {
//  ["item_id"]=>
//    int(26370)
//    ["title"]=>
//    string(19) "テストTシャツ"
//			["detail"]=>
//    string(57) "テスト商品です！販売はしておりません。"
//			["price"]=>
//    int(2980)
//    ["stock"]=>
//    int(479)
//    ["visible"]=>
//    int(1)
//    ["list_order"]=>
//    int(5)
//    ["identifier"]=>
//    NULL
//    ["img1_origin"]=>
//    string(91) "https://baseecdev2.s3.amazonaws.com/images/item/origin/02640d9e547ba984cfb27c00fd02d220.jpg"
//			["img2_origin"]=>
//    string(91) "https://baseecdev2.s3.amazonaws.com/images/item/origin/fb7ff7d94436642eba83afb49584fb75.jpg"
//			["img3_origin"]=>
//    NULL
//    ["img4_origin"]=>
//    NULL
//    ["img5_origin"]=>
//    NULL
//    ["variations"]=>
//    array(3) {
//				[0]=>
//      object(stdClass)#3294 (4) {
//      ["variation_id"]=>
//        int(11136)
//        ["variation"]=>
//        string(1) "S"
//				["variation_stock"]=>
//        int(22)
//        ["variation_identifier"]=>
//        NULL
//      }
//      [1]=>
//      object(stdClass)#3295 (4) {
//      ["variation_id"]=>
//        int(11137)
//        ["variation"]=>
//        string(1) "M"
//			["variation_stock"]=>
//        int(33)
//        ["variation_identifier"]=>
//        NULL
//      }
//      [2]=>
//      object(stdClass)#3296 (4) {
//      ["variation_id"]=>
//        int(11138)
//        ["variation"]=>
//        string(1) "L"
//		["variation_stock"]=>
//        int(424)
//        ["variation_identifier"]=>
//        NULL
//      }
//}
//}

	?>
	<?php
		echo $after_widget;//<div>
	}

	/** @see WP_Widget::update */
	public function update($new_instance, $old_instance) {
		return $new_instance;
	}

	/** @see WP_Widget::form */
	public function form($instance) {

		$users = $this->BaseOAuth->getUsers();

		$title = ($instance['title']) ?: $users->shop_name;
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:'); ?>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php esc_attr_e($title); ?>" />
			</label>
		</p>
	<?php
	}

}
