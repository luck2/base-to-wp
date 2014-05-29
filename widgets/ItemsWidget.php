<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/05/25
 * Time: 22:24
 */

//namespace BaseToWP;//TODO register_widget('ClassName') で呼び出すクラスはnamespase使えないmeta character がダメっぽい

/**
 * Class BaseToWPItemsWidget
 */
class BaseToWPItemsWidget extends \WP_Widget {

	/** constructor */
	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_base_items', 'description' => __( "BASEショップの商品一覧" ) );
		parent::__construct(false, __('BASE Shop Items'), $widget_ops);
	}

	/** @see WP_Widget::widget */
	public function widget($args, $instance) {
		$before_widget='';$after_widget='';$before_title='';$after_title='';
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);

		echo $before_widget;//<div>
		echo ( $title ) ? $before_title . $title . $after_title : '';//<h3>$title</h3>

		try {
			$BaseOAuthWP = new BaseOAuthWP();
			$BaseOAuthWP->checkToken();
			$items = $BaseOAuthWP->getItems();

			foreach ( $items as $item ) :
				?>
				<h4><?php _e($item->title, BASE_TO_WP_NAMEDOMAIN); ?></h4>
				<a href="<?php esc_attr_e("http://sample.com/items/".$item->item_id);/*FIXME url*/ ?>"><img src="<?php esc_attr_e($item->img1_origin); ?>" alt="item photo"/></a>
				<p><?php echo sprintf( __('Price: %d', BASE_TO_WP_NAMEDOMAIN), $item->price); ?></p>
				<hr/>
			<?php
			endforeach;

		} catch (\Exception $e) {
			echo $e->getMessage();
		}

		echo $after_widget;//<div>
	}

	/** @see WP_Widget::update */
	public function update($new_instance, $old_instance) {
		return $new_instance;
	}

	/** @see WP_Widget::form */
	public function form($instance) {
		try {
			$BaseOAuthWP = new BaseOAuthWP();
			$BaseOAuthWP->checkToken();
			$users = $BaseOAuthWP->getUsers();

			$title = ($instance['title']) ?: $users->shop_name;
			?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>">
					<?php _e('Title:'); ?>
					<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php esc_attr_e($title); ?>" />
				</label>
			</p>
			<?php

		} catch (\Exception $e) {
			echo $e->getMessage();
		}

	}

}
