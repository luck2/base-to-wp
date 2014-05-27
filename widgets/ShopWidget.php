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
class ShopWidget extends WP_Widget {

	private $BaseOAuth;

	/** constructor */
	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_base_shop', 'description' => __( "BASEショップへのロゴと説明を表示します" ) );
		parent::WP_Widget(false, __('BASE Shop Info'), $widget_ops);

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

		$users = $this->BaseOAuth->getUsers();

//		var_dump($users);
//		$BaseOAuth->render_list();
	?>
		<a href="<?php esc_attr_e($users->shop_url); ?>"><img src="<?php esc_attr_e($users->logo); ?>" alt="logo" style="width: 100%;"/></a>
<!--		<p>--><?php //esc_html_e($users->shop_name); ?><!--</p>-->
		<p><?php esc_html_e($users->shop_introduction); ?></p>
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
