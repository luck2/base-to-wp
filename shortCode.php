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

			$response = $BaseOAuthWP->getItems();
			if ( $BaseOAuthWP->http_code == 400 )
				throw new \Exception( '400 Bad Request.', 400 );

			return $BaseOAuthWP->render_list(null,true);

		} catch ( \Exception $e ) {
			return $e->getMessage();
		}

	}


} 