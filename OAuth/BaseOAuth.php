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

/**
 * Class BaseOAuth
 * @package OAuth
 */
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
	const ITEMS_DETAIL = 'items/detail/';
	/**
	 * Orders
	 *
	 * GET /1/orders - 注文情報の一覧を取得
	 * GET /1/orders/detail/:unique_key - 注文情報を取得
	 * POST /1/orders/edit_status - 注文情報のステータスを更新
	 */
	const ORDERS = 'orders';
	const ORDERS_DETAIL = 'orders/detail/';
	/**
	 * Savings
	 *
	 * GET /1/savings - 引き出し申請情報の一覧を取得
	 */
	const SAVINGS = 'savings';


	public $client_id;
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


	/**
	 * @param null $client_id
	 * @param null $client_secret
	 * @param null $redirect_uri
	 * @param null $access_token
	 * @param null $refresh_token
	 */
	public function __construct( $client_id = null, $client_secret = null, $redirect_uri = null, $access_token = null, $refresh_token = null ) {
		$this->client_id     = $client_id;
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
	 * @throws \Exception
	 * @return string
	 * @internal param $params
	 */
	public function getAuthorize( $client_id = null, $redirect_uri = null, $scope = null, $state = null ) {
		// Set
		$client_id and ( $this->client_id = $client_id );
		$redirect_uri and ( $this->redirect_uri = $redirect_uri );
		// Check
		if ( ! $this->client_id ) {
			throw new \Exception( 'Error: ' . 'client_id' );
		} elseif ( ! $this->redirect_uri ) {
			throw new \Exception( 'Error: ' . 'redirect_uri' );
		}

		/**
		 * response_type code (必須)
		 * client_id     クライアントID (必須)
		 * redirect_uri  登録したコールバックURL (必須)
		 * scope         スコープをスペース区切りで指定 (任意 デフォルト: read_users)
		 * state         リダイレクト先URLにそのまま返すパラメーター (任意)
		 */
		$params = array(
			'response_type' => 'code',
			'client_id'     => $this->client_id,
			'redirect_uri'  => $this->redirect_uri,
		);
		$scope and ( $params['scope'] = $scope );
		$state and ( $params['state'] = $state );

		return $this->url = self::build_url( self::OAUTH_AUTHORIZE, $params );
	}


	/**
	 * POST /1/oauth/token - 認可コードからアクセストークンを取得
	 * memo
	 * リクエストするたびに違うaccess_token,refresh_tokenが取得できる
	 * 認可コードの有効期限は？←「アプリを認証する」をポチポチするたびに発行される
	 * Error: bool(false)
	 * POST /1/oauth/token - リフレッシュトークンからアクセストークンを取得
	 *
	 * @param string $grant_type
	 * @param $code_or_refresh_token
	 * @param $client_id
	 * @param $client_secret
	 * @param $redirect_uri
	 *
	 * @throws \Exception
	 * @return array|mixed|string
	 */
	public function getToken( $grant_type = 'refresh_token', $code_or_refresh_token = null, $client_id = null, $client_secret = null, $redirect_uri = null ) {
		//Set
		$client_id and ( $this->client_id = $client_id );
		$client_secret and ( $this->client_secret = $client_secret );
		$redirect_uri and ( $this->redirect_uri = $redirect_uri );
		//Set and Check
		if ( $grant_type === 'refresh_token' ) {
			$code_or_refresh_token and ( $this->refresh_token = $code_or_refresh_token );
			if ( ! $this->refresh_token )
				throw new \Exception( 'Error: ' . 'refresh_token' );
		} elseif ( $grant_type === 'authorization_code' ) {
			if ( ! $code_or_refresh_token )
				throw new \Exception( 'Error: ' . 'authorization_code' );
		} else {
			throw new \Exception( 'Error: ' . 'grant_type' );
		}
		//Check
		if ( ! $this->client_id ) {
			throw new \Exception( 'Error: ' . 'client_id' );
		} elseif ( ! $this->client_secret ) {
			throw new \Exception( 'Error: ' . 'client_secret' );
		} elseif ( ! $this->redirect_uri ) {
			throw new \Exception( 'Error: ' . 'redirect_uri' );
		}

		/**
		 * grant_type           authorization_code / refresh_token (必須)
		 * client_id            クライアントID (必須)
		 * client_secret        クライアントシークレット (必須)
		 * code/refresh_token   認可コード / リフレッシュトークン (必須)
		 * redirect_uri         登録したコールバックURL (必須)
		 */
		$params    = array(
			'grant_type'                                                        => $grant_type,
			'client_id'                                                         => $this->client_id,
			'client_secret'                                                     => $this->client_secret,
			( $grant_type === 'authorization_code' ) ? 'code' : 'refresh_token' => ( $grant_type === 'authorization_code' ) ? $code_or_refresh_token : $this->refresh_token,
			'redirect_uri'                                                      => $this->redirect_uri
		);
		$this->url = self::$host . self::OAUTH_TOKEN;

		/**
		 * access_token - APIにアクセスするために必要なトークン。有効期限は1時間。
		 * token_type - bearer
		 * expires_in - アクセストークンの有効期限
		 * refresh_token - アクセストークンを再発行するために必要なトークン。有効期限は30日。
		 */
		$response = $this->_post( $this->url, $params );

		if ( $response ) {
			$response = json_decode( $response );
			//Set
			$this->access_token  = $response->access_token;
			$this->refresh_token = $response->refresh_token;
		}

		return $response;
	}

	/**
	 * GET /1/users/me
	 * ユーザー情報を取得
	 *
	 * shop_id - ユーザーを識別するユニークなID。文字列型。
	 * background - ショップの背景画像
	 * logo - ショップのロゴ画像
	 * mail_address - read_users_mailのscopeがある時のみ取得できます。
	 *
	 * @return array|mixed
	 */
	public function getUsers() {
		$this->url = self::build_url( self::USERS_ME );
		$response = $this->_get( $this->url );
		return self::json_parse( $response );
	}

	/**
	 * GET /1/items
	 * 商品情報の一覧を取得
	 *
	 * order    並び替え項目。list_order か created のいずれか (任意 デフォルト: list_order)
	 * sort    並び順。asc か desc のいずれか (任意 デフォルト: asc)
	 * limit    リミット (任意 デフォルト: 20, MAX: 100)
	 * offset    オフセット (任意 デフォルト: 0)
	 *
	 * @param array $params
	 *
	 * @internal param null $id
	 * @return mixed
	 */
	public function getItems($params=array()) {
		$this->url = self::build_url( self::ITEMS, $params );
		$response = $this->_get( $this->url );
		return self::json_parse( $response );
	}
	/**
	 * GET /1/items/detail/:item_id
	 * 商品情報を取得
	 *
	 * @param $id
	 *
	 * @return array|mixed
	 */
	public function getItem($id) {
		$this->url = self::build_url(self::ITEMS_DETAIL.$id);
		$response = $this->_get( $this->url );
		return self::json_parse( $response );
	}

	public function addItem() {
	}

	/**
	 * GET /1/orders
	 * 注文情報の一覧を取得
	 *
	 * start_ordered	注文日時はじめ yyyy-mm-dd (任意)
	 * end_ordered	注文日時おわり yyyy-mm-dd (任意)
	 * limit	リミット (任意 デフォルト: 20, MAX: 100)
	 * offset	オフセット (任意 デフォルト: 0)
	 */
	public function getOrders($params=array()) {
		$this->url = self::build_url(self::ORDERS, $params);
		$response = $this->_get( $this->url );
		return self::json_parse( $response );
	}
	/**
	 * GET /1/orders/detail/:unique_key
	 * 注文情報を取得
	 *
	 * @param $unique_key
	 * @return array|mixed
	 */
	public function getOrder($unique_key) {
		$this->url = self::build_url(self::ORDERS_DETAIL.$unique_key);
		$response = $this->_get( $this->url );
		return self::json_parse( $response );
	}

	/**
	 * GET /1/savings
	 * 引き出し申請情報の一覧を取得
	 *
	 * @param array $params
	 *
	 * @return array|mixed
	 */
	public function getSavings($params=array()) {
		$this->url = self::build_url(self::SAVINGS, $params);
		$response = $this->_get( $this->url );
		if ( $response )
			$response = self::json_parse( $response );
		return $response;
	}


	private function _get( $url ) {
		$this->_request( 'GET', $url );
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
	private function _post( $url, $params ) {
		$this->_request( 'POST', $url, $params );

		return $this->response;
	}


	/**
	 * @param $method
	 * @param $url
	 * @param array $params
	 *
	 * @throws \Exception
	 * @return string
	 */
	public function _request( $method, $url, $params = array() ) {
		//FIXME CURLに変更する

		// Headerを生成する
		$headers = array();
		if ( $this->access_token ) {
			$headers = array(
				'Authorization: Bearer ' . $this->access_token,
			);
		}
		if ( $method === 'GET' ) {
		} elseif ( $method === 'POST' ) {
			$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		} else {
			return false;
		}

		$request_options = array(
			'http' => array(
				'method'  => $method,
				'content' => http_build_query( $params ), //POSTの場合
				'header'  => implode( "\r\n", $headers ),
			),
		);
		$context         = stream_context_create( $request_options );

		$this->response  = @file_get_contents( $url, false, $context );
		$this->http_code = ( $this->response ) ? 200 : 400; //FIXME CURLに変更
		if (! $this->response)
			throw new \Exception('400 Bad Request.',400);

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
	public static function build_url( $url, $params = null ) {

		if ( ! is_array( $params ) )
			return self::$host . $url;

		if ( phpversion() >= 5.4 ) {
			$str = http_build_query( $params, null, "&", PHP_QUERY_RFC3986 );
		} else {
			$str = http_build_query( $params, null, "&" );
			$str = str_replace( '+', '%20', $str );
		}

		return self::$host . $url . '?' . $str;
	}


	private function _request___() {
//		//cURLを初期化して使用可能にする
//		$curl=curl_init();
//		//オプションにURLを設定する
//		curl_setopt($curl,CURLOPT_URL,$authorize_uri);
//		//CA証明書の検証をしない
//		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
//		//文字列で結果を返させる
//		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
//		// User-Agent
//		curl_setopt($curl, CURLOPT_USERAGENT, $user_agent='Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');
//		// referrer
//		curl_setopt($curl, CURLOPT_REFERER, $ref='http://google.com/');
//		//URLにアクセスし、結果を表示させる
//		//$html = curl_exec($curl);
//		//cURLのリソースを解放する
//		curl_close($curl);
//		//var_dump($html);

	}


	/**
	 * @param null $response
	 * @param bool $return
	 *
	 * @return bool|string
	 */
	public function render_list( $response=null, $return=false ) {
		//Set
		is_null( $response ) and $response = $this->response;
		//Check
		if (empty($response)) return '';
		//jsonならobjectにparse　FIXME 判定めんどいだれか
		if ( is_string( $response ) )
			$response = self::json_parse( $response );
		// 配列でないなら配列に
		!is_array( $response ) and $response = array( $response );
		/**
		 * @param $arr_obj
		 * @return bool|string
		 */
		$render_object_recursive = function ($arr_obj) use (&$render_object_recursive) {
			foreach ( $arr_obj as $key => $value ) {
				if (is_array($arr_obj))
					echo '<dl style="padding: 1em;border: 1px solid red;">' . PHP_EOL;
				if (is_array($value)) {
					echo '<dt style="font-weight: bold;">' . $key . '</dt>' . PHP_EOL;
					echo '<dd>';
					$render_object_recursive( $value );
					echo '</dd>' . PHP_EOL;
				} elseif (is_object($value)) {
					$render_object_recursive( $value );
				} else {
					echo '<dt style="font-weight: bold;">' . $key . '</dt>' . PHP_EOL;
					echo '<dd>' . $value . '</dd>' . PHP_EOL;
				}
				if (is_array($arr_obj))
					echo '</dl>'.PHP_EOL;
			}
		};

		ob_start();

		$render_object_recursive($response);

		if ( $return ) {
			$body = ob_get_contents();
			ob_end_clean();
			return $body;
		} else {
			return ob_end_flush();
		}
	}

	/**
	 * @param $response
	 *
	 * @return array|mixed
	 */
	public static function json_parse( $response ) {
		$response = json_decode( $response );
		if ( is_object( $response ) ) {
			foreach ( $response as $key => $value ) {
				$response = $value; //一階層はずす
				break;
			}
		}
		return $response;
	}

}

endif;