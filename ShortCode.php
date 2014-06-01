<?php
/**
 * Created by PhpStorm.
 * User: sasaki
 * Date: 2014/05/29
 * Time: 9:38
 */

namespace BaseToWP;


class ShortCode {

	/**
	 * ショートコード
	 *
	 * @param array $atts
	 * @return string
	 */
	public function base($atts)
	{
		error_reporting(E_ALL);//FIXME DEBUG
		ini_set( 'display_errors', 1 );


		$foo='';$bar='';
		extract(shortcode_atts(array(
			'foo' => 'something',
			'bar' => 'something else',
		), $atts));

		try {
			$BaseOAuthWP = new \BaseOAuthWP();
			$BaseOAuthWP->checkToken();

			$items = $BaseOAuthWP->getItems();

			$shop_info = get_option('base_to_wp_shop_info');

			ob_start();

			foreach ( $items as $item ) : ?>

				<div class="item part">
					<div class="itemImg">
						<a href="<?php esc_attr_e($shop_info['shop_url'].'/items/'.$item->item_id); ?>"><img src="<?php esc_attr_e($item->img1_origin); ?>" alt="<?php esc_attr_e($item->title); ?>" title="<?php esc_attr_e($item->title); ?>" class="image-resize"></a>
					</div>
					<a href="<?php esc_attr_e($shop_info['shop_url'].'/items/'.$item->item_id); ?>">
						<div class="itemTitle">
							<h2><?php _e($item->title); ?></h2>
						</div>
						<ul class="itemDetail">
							<li class="itemPrice">
								<?php esc_html_e($item->price); ?>円
							</li>
						</ul>
					</a>
				</div>

			<?php endforeach; ?>
			<?php
			$BaseOAuthWP->render_list();
			$return = ob_get_contents();ob_end_clean();
			return $return;


		} catch ( \Exception $e ) {
			return $e->getMessage();
		}

	}


} 