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


		$this->BaseOAuth = new \OAuth\BaseOAuth(
			$client_id     = get_option('base_to_wp_client_id'),
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
			<a href="<?php esc_attr_e("http://sample.com/items/".$item->item_id);/*FIXME url*/ ?>"><img src="<?php esc_attr_e($item->img1_origin); ?>" alt="item photo"/></a>
			<p><?php echo sprintf( __('Price: %d', BASE_TO_WP_NAMEDOMAIN), $item->price); ?></p>
			<hr/>
		<?php
		endforeach;

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
