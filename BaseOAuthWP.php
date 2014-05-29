<?php
/**
 * Created by PhpStorm.
 * User: K.Sasaki
 * Date: 2014/05/27
 * Time: 23:24
 */

/**
 * Class BaseOAuthWP
 */
class BaseOAuthWP extends \OAuth\BaseOAuth {


	public $access_token_expires = null;
	public $refresh_token_expires = null;

	public function __construct($client_id=null, $client_secret=null, $redirect_uri=null, $access_token=null, $refresh_token=null,$access_token_expires=null, $refresh_token_expires=null ) {
		$this->client_id     = $client_id ?: get_option('base_to_wp_client_id') ;
		$this->client_secret = $client_secret ?: get_option('base_to_wp_client_secret');
		$this->redirect_uri  = $redirect_uri ?: get_option('base_to_wp_redirect_uri');
		$this->access_token  = $access_token ?: get_option('base_to_wp_access_token');
		$this->refresh_token = $refresh_token ?: get_option('base_to_wp_refresh_token');
		$this->access_token_expires = $access_token_expires ?: get_option('base_to_wp_access_token_expires');
		$this->refresh_token_expires = $refresh_token_expires ?: get_option('base_to_wp_refresh_token_expires');
	}

	/**
	 * @return array|mixed|stdClass|string
	 * @throws Exception
	 */
	public function checkToken() {

		$response = new stdClass;
		$response->access_token = '';
		$response->token_type = "bearer";
		$response->expires_in = 3600;
		$response->refresh_token = '';

		//TODO アクセストークンの有効期限を調べて切れてるならリフレッシュトークンから新しく取得
		if ( date_i18n('U') > $this->access_token_expires ) {

			// リフレッシュトークンの期限が切れている場合
			if ( date_i18n('U') > $this->refresh_token_expires )
				throw new Exception( 'Refresh token expired.', 408 );


			$response = $this->getToken();
			//Update WP options
			$this->_setTokenExpires($response);

		}
		return $response;
	}

	/**
	 * @param string $grant_type
	 * @param null $code_or_refresh_token
	 * @param null $client_id
	 * @param null $client_secret
	 * @param null $redirect_uri
	 *
	 * @return array|mixed|string
	 * @throws Exception
	 */
	public function getToken($grant_type='refresh_token', $code_or_refresh_token=null, $client_id=null, $client_secret=null, $redirect_uri=null) {
		$response = parent::getToken($grant_type, $code_or_refresh_token, $client_id, $client_secret, $redirect_uri);
		$this->_setTokenExpires($response);
		return $response;
	}

	/**
	 * @param $response
	 */
	private function _setTokenExpires($response) {
		update_option('base_to_wp_access_token', $response->access_token);
		update_option('base_to_wp_access_token_expires', (int) date_i18n('U') + (int) $response->expires_in);
		update_option('base_to_wp_refresh_token', $response->refresh_token);
		update_option('base_to_wp_refresh_token_expires', (int) date_i18n('U') + (60 * 60 * 24 * 30));//30日後まで
	}


}