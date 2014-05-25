<?php
/**
 * BaseOAuth
 *
 * BASE API v1
 * https://gist.github.com/baseinc/9634675
 *
 * 設計志向
 * 必要なコンフィグをつけてクラス化します
 * request()で柔軟にAPIとやりとりできます
 * あとはAPIの数だけエンドポイントを用意します
 *
 * @author K.Sasaki
 * @version 0.1.0
 */

namespace OAuth;

if (!class_exists('BaseOAuth')) :

class BaseOAuth {
	const VERSION = '0.1.0';

	/**
	 * BASE API v1
	 * https://gist.github.com/baseinc/9634675
	 *
	 * @var string
	 */
	public static $host = 'https://api.thebase.in/1/';
	/**
	 * OAuth
	 * GET /1/oauth/authorize - 認可コードを取得
	 * POST /1/oauth/token - 認可コードからアクセストークンを取得
	 * POST /1/oauth/token - リフレッシュトークンからアクセストークンを取得
	 */
	const OAUTH_AUTHORIZE = 'oauth/authorize';
	const OAUTH_TOKEN = 'oauth/token';
	/**
	 * Users
	 * GET /1/users/me - ユーザー情報を取得
	 */
	const USERS_ME = 'users/me';
	/**
	 * Items
	 * GET /1/items - 商品情報の一覧を取得
	 * GET /1/items/detail/:item_id - 商品情報を取得
	 * POST /1/items/add - 商品情報を登録
	 * POST /1/items/edit - 商品情報を更新
	 * POST /1/items/delete - 商品情報を削除
	 * POST /1/items/add_image - 商品情報の画像を登録
	 * POST /1/items/delete_image - 商品情報の画像を削除
	 * POST /1/items/edit_stock - 商品情報の在庫数を更新
	 */
	const ITEMS = 'items';
	/**
	 * Orders
	 *
	 * GET /1/orders - 注文情報の一覧を取得
	 * GET /1/orders/detail/:unique_key - 注文情報を取得
	 * POST /1/orders/edit_status - 注文情報のステータスを更新
	 */
	const ORDERS = 'orders';
	/**
	 * Savings
	 *
	 * GET /1/savings - 引き出し申請情報の一覧を取得
	 */
	const SAVINGS = 'savings';

	public $client_key;
	public $client_secret;
	public $redirect_uri;
	public $access_token = null;
	public $refresh_token = null;

	/* Contains the last API call. */
	public $url = null;
	/* Contains the last HTTP status code returned. */
	public $http_code = null;
	/* Response by API. */
	public $response = array();


	public function __construct( $client_key=null, $client_secret=null, $redirect_uri=null, $access_token=null, $refresh_token=null ) {
		$this->client_key    = $client_key;
		$this->client_secret = $client_secret;
		$this->redirect_uri  = $redirect_uri;
		$this->access_token  = $access_token;
		$this->refresh_token = $refresh_token;
	}


	/**
	 * GET /1/oauth/authorize - 認可コードを取得
	 *
	 * @param $client_id
	 * @param $redirect_uri
	 * @param null $scope
	 * @param null $state
	 *
	 * @return string
	 * @internal param $params
	 */
	public function getAuthorize( $client_id, $redirect_uri, $scope=null, $state=null ) {
		/**
		 * response_type code (必須)
		 * client_id     クライアントID (必須)
		 * redirect_uri  登録したコールバックURL (必須)
		 * scope         スコープをスペース区切りで指定 (任意 デフォルト: read_users)
		 * state         リダイレクト先URLにそのまま返すパラメーター (任意)
		 */
		$params = array(
			'response_type' => 'code',
			'client_id'     => $client_id,
			'redirect_uri'  => $redirect_uri,
		);
		$scope and ($params['scope'] = $scope);
		$state and ($params['state'] = $state);

		return $this->url = self::build_url(self::OAUTH_AUTHORIZE,$params);
	}


	/**
	 * POST /1/oauth/token - 認可コードからアクセストークンを取得
	 * memo
	 * リクエストするたびに違うaccess_token,refresh_tokenが取得できる
	 * 認可コードの有効期限は？←「アプリを認証する」をポチポチするたびに発行される
	 * Error: bool(false)
	 * POST /1/oauth/token - リフレッシュトークンからアクセストークンを取得
	 *
	 * @param $grant_type
	 * @param $client_id
	 * @param $client_secret
	 * @param $code_or_refresh_token
	 * @param $redirect_uri
	 *
	 * @return array|mixed|string
	 */
	public function getToken($grant_type, $client_id, $client_secret, $code_or_refresh_token, $redirect_uri) {
		/**
		 * grant_type           authorization_code / refresh_token (必須)
		 * client_id            クライアントID (必須)
		 * client_secret        クライアントシークレット (必須)
		 * code/refresh_token   認可コード / リフレッシュトークン (必須)
		 * redirect_uri         登録したコールバックURL (必須)
		 */
		$params = array(
			'grant_type'     => $grant_type,
			'client_id'      => $client_id,
			'client_secret'  => $client_secret,
			($grant_type==='authorization_code')?'code':'refresh_token' => $code_or_refresh_token,
			'redirect_uri'   => $redirect_uri
		);
		$this->url = self::$host . self::OAUTH_TOKEN;

		$response = $this->_post($this->url, $params);
		/**
		 * access_token - APIにアクセスするために必要なトークン。有効期限は1時間。
		 * token_type - bearer
		 * expires_in - アクセストークンの有効期限
		 * refresh_token - アクセストークンを再発行するために必要なトークン。有効期限は30日。
		 */
		if ($response)
			$response = json_decode($response);

		return $response;
	}

	public function getUsers($access_token=null) {

		$this->url = self::build_url(self::USERS_ME);

		$response = $this->_get($this->url);

		/**
		 * shop_id - ユーザーを識別するユニークなID。文字列型。
		 * background - ショップの背景画像
		 * logo - ショップのロゴ画像
		 * mail_address - read_users_mailのscopeがある時のみ取得できます。
		 */
		if ($response)
			$response = json_decode($response);

		return $response->user;
	}

	private function _get($url) {
		$this->_request('GET', $url);
		return $this->response;

	}

	/**
	 * $urlに $paramsをPOSTする
	 * Set $http_code
	 * Set $response
	 *
	 * @param $url
	 * @param $params
	 *
	 * @return string
	 */
	private function _post($url, $params) {
		$this->_request('POST', $url, $params);
		return $this->response;
	}


	public function _request( $method, $url, $params=array()) {
		//FIXME CURLに変更する

		// Headerを生成する
		$headers = array();
		if ($this->access_token) {
			$headers = array(
				'Authorization: Bearer ' . $this->access_token,
			);
		}
		if ($method === 'GET') {
		} elseif ($method === 'POST') {
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		} else {
			return false;
		}

		$request_options = array(
			'http' => array(
				'method'  => $method,
				'content' => http_build_query($params),//POSTの場合
				'header'  => implode("\r\n", $headers),
			),
		);
		$context = stream_context_create($request_options);

		$this->response = file_get_contents($url, false, $context);
		$this->http_code = ($this->response)? 200 : 400;//FIXME CURLに変更
		return $this->response;
	}




	/**
	 * Get Request build
	 *
	 * @param $url
	 * @param Array $params
	 *
	 * @return string
	 */
	public static function build_url($url, $params=null) {

		if (! is_array($params))
			return self::$host . $url;

		if (phpversion() >= 5.4) {
			$str = http_build_query($params, null, "&", PHP_QUERY_RFC3986);
		} else {
			$str = http_build_query($params, null, "&");
			$str = str_replace('+','%20', $str);
		}

		return self::$host . $url . '?' . $str;
	}



	public function items() {
	}
	public function orders() {
	}
	public function savings() {
	}




	private function _request___() {
		//cURLを初期化して使用可能にする
		$curl=curl_init();
		//オプションにURLを設定する
		curl_setopt($curl,CURLOPT_URL,$authorize_uri);
		//CA証明書の検証をしない
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
		//文字列で結果を返させる
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		// User-Agent
		curl_setopt($curl, CURLOPT_USERAGENT, $user_agent='Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');
		// referrer
		curl_setopt($curl, CURLOPT_REFERER, $ref='http://google.com/');
		//URLにアクセスし、結果を表示させる
		//$html = curl_exec($curl);
		//cURLのリソースを解放する
		curl_close($curl);
		//var_dump($html);

	}


	/**
	 * Make an HTTP request
	 *
	 * @param string $url
	 * @param string $method
	 * @param null $postfields
	 * @return OAuthRequest results
	 */
	function http($url, $method, $postfields = NULL) {
		$this->http_info = array();
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		$this->setHeaders( $ci );

		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
		}

		if ($this->proxy_host) {
			curl_setopt($ci, CURLOPT_PROXY, $this->proxy_host);
			curl_setopt($ci, CURLOPT_PROXYPORT, $this->proxy_port);
			curl_setopt($ci, CURLOPT_PROXYUSERPWD, $this->proxy_userpwd);
		}
		//curl_setopt($ci, CURLINFO_HEADER_OUT,true); var_dump($postfields);
		$this->setUrl( $ci, $url );
		$response = curl_exec($ci);
		$error = curl_error($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		curl_close ($ci);
		return $response;
	}



}

endif;