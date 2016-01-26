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
class OrdersPage
{
	public $hook;
	public $title;
	public $menu;
	public $permissions;
	public $slug;
	public $screen_id;

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
		$this->screen_id = add_submenu_page($this->hook,$this->title, $this->menu, $this->permissions,$this->slug, array($this,'render_page'));

		// Add callbacks for this screen only
		add_action('load-'.$this->screen_id,  array($this,'page_actions'),9);

		/* Enqueue WordPress' script for handling the metaboxes */
		add_action('admin_print_scripts-'.$this->screen_id, function(){wp_enqueue_script( 'postbox' );});
		add_action('admin_footer-'.$this->screen_id,array($this,'footer_scripts'));

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
//		$screen->add_option('layout_columns', array('max' => 2, 'default' => 2));
		$screen->add_option('per_page', array(
			'label' => 'items',
			'default' => 10,
			'option' => 'item_per_page'
		));
//		var_dump($screen);

		#FIXME 保存側
		$set_option = function ($status, $option, $value) {
			if ( 'item_per_page' == $option ) return $value;
			return $status;
		};
		add_filter('set-screen-option', $set_option, 10, 3);


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
	 * Renders the page
	 */
	public function render_page(){

		//TODO DEBUG
		ini_set('display_errors', true);
		error_reporting(E_ALL);
		debug_base();

		//Init
		empty($_GET['action']) and $_GET['action']=null;
		empty($_GET['unique_key']) and $_GET['unique_key']=null;
		$order=null;

		$orders_uri = admin_url('admin.php?page=base_to_wp_orders');

		try {

			$BaseOAuthWP = new BaseOAuthWP();
			$BaseOAuthWP->checkToken();

			if ($_GET['action']==='detail' && $_GET['unique_key'] !== null ) {
				$order = $BaseOAuthWP->getOrder($_GET['unique_key']);

			} else {
				$orders_obj = $BaseOAuthWP->getOrders();
				$response = json_decode($BaseOAuthWP->response,true);
				$orders = $response['orders'];

				require_once BASE_TO_WP_ABSPATH.'/OrderListTable.php';
				$OrderListTable = new \BaseToWP\OrderListTable();
				$OrderListTable->data = $orders;
				$OrderListTable->prepare_items();
			}
		} catch (Exception $e) {
			add_settings_error(
				'orders',
				esc_attr($e->getCode()),
				__($message=$e->getMessage()),
				'error'
			);
		}
		?>
		<div class="wrap">
			<?php if ($_GET['action']==='detail' && $_GET['unique_key'] !== null ): ?>
				<h2><?php _e('BASE To WordPress 注文管理 > 注文詳細', BASE_TO_WP_NAMEDOMAIN); ?></h2>
				<?php
				if (empty($e)) {
//					$BaseOAuthWP->render_list($order);
					?>
					<div id="welcome-panel" class="welcome-panel">
						<input type="hidden" id="welcomepanelnonce" name="welcomepanelnonce" value="8dd93a33d0">
						<a class="welcome-panel-close" href="http://www.luck2.localhost/wp-admin/?welcome=0">非表示にする</a>
						<div class="welcome-panel-content">
							<h3>WordPress へようこそ !</h3>
							<p class="about-description">初めての方に便利なリンクを集めました。</p>
							<div class="welcome-panel-column-container">
								<div class="welcome-panel-column">
									<h4>始めてみよう</h4>
									<a class="button button-primary button-hero load-customize hide-if-no-customize" href="http://www.luck2.localhost/wp-admin/customize.php">サイトをカスタマイズ</a>
									<a class="button button-primary button-hero hide-if-customize" href="http://www.luck2.localhost/wp-admin/themes.php">サイトをカスタマイズ</a>
									<p class="hide-if-no-customize">または、<a href="http://www.luck2.localhost/wp-admin/themes.php">別のテーマに変更する</a></p>
								</div>
								<div class="welcome-panel-column">
									<h4>次のステップ</h4>
									<ul>
										<li><a href="http://www.luck2.localhost/wp-admin/post-new.php" class="welcome-icon welcome-write-blog">ブログに投稿する</a></li>
										<li><a href="http://www.luck2.localhost/wp-admin/post-new.php?post_type=page" class="welcome-icon welcome-add-page">「サイトについて」固定ページを追加</a></li>
										<li><a href="http://www.luck2.localhost/" class="welcome-icon welcome-view-site">サイトを表示</a></li>
									</ul>
								</div>
								<div class="welcome-panel-column welcome-panel-last">
									<h4>その他の操作</h4>
									<ul>
										<li><div class="welcome-icon welcome-widgets-menus"><a href="http://www.luck2.localhost/wp-admin/widgets.php">ウィジェット</a>または<a href="http://www.luck2.localhost/wp-admin/nav-menus.php">メニュー</a>の管理</div></li>
										<li><a href="http://www.luck2.localhost/wp-admin/options-discussion.php" class="welcome-icon welcome-comments">コメントを表示/非表示</a></li>
										<li><a href="http://wpdocs.sourceforge.jp/First_Steps_With_WordPress" class="welcome-icon welcome-learn-more">最初のステップについて詳細を読む</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>

					<div id="dashboard-widgets-wrap">
					<div id="dashboard-widgets" class="metabox-holder">
					<div id="postbox-container-1" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable"><div id="dashboard_right_now" class="postbox " style="display: block;">
								<div class="handlediv" title="クリックで切替"><br></div><h3 class="hndle"><span>概要</span></h3>
								<div class="inside">
									<div class="main">
										<ul>
											<li class="post-count"><a href="edit.php?post_type=post">41件の投稿</a></li><li class="page-count"><a href="edit.php?post_type=page">4件の固定ページ</a></li>		<li class="comment-count"><a href="edit-comments.php">82件のコメント</a></li>
										</ul>
										<p id="wp-version-message">WordPress 3.9.1 (<a href="themes.php">Gorgeous1 TCD013</a> テーマ)</p>	</div>
									<div class="sub">
										<p class="akismet-right-now"><a href="https://akismet.com/wordpress/">Akismet</a> は、295件のスパムコメントからあなたのサイトを保護しました。<br>現在<a href="http://www.luck2.localhost/wp-admin/edit-comments.php?comment_status=spam">保留中のスパム</a>はありません。</p>
									</div>
								</div>
							</div>
							<div id="dashboard_activity" class="postbox " style="display: block;">
								<div class="handlediv" title="クリックで切替"><br></div><h3 class="hndle"><span>アクティビティ</span></h3>
								<div class="inside">
									<div id="activity-widget"><div id="published-posts" class="activity-block"><h4>最近公開</h4><ul><li><span>5月18日 4:56 PM</span> <a href="http://www.luck2.localhost/wp-admin/post.php?post=3378&amp;action=edit">ホームページリニューアルしております</a></li><li><span>5月16日 6:54 PM</span> <a href="http://www.luck2.localhost/wp-admin/post.php?post=3336&amp;action=edit">ウェブ制作実績：「京都の地図ポータルサイト」祗園、先斗町、木屋町のグルメ・お店ガイド</a></li><li><span>5月16日 6:43 PM</span> <a href="http://www.luck2.localhost/wp-admin/post.php?post=3333&amp;action=edit">ウェブ制作実績：「1枚の写真が人生を変える」10代〜20代のリアルマーケットを独占！関西最大級規模のファッションスナップサイト</a></li><li><span>5月16日 6:40 PM</span> <a href="http://www.luck2.localhost/wp-admin/post.php?post=3330&amp;action=edit">ウェブ制作実績：こだわりの卵「竹鶏物語」と米粉を使ったカスタードフォンデュを販売する通販サイト</a></li><li><span>5月16日 6:37 PM</span> <a href="http://www.luck2.localhost/wp-admin/post.php?post=3326&amp;action=edit">ウェブ制作実績：「提供するすべてのサービス・商品に、本物の価値を」美容化粧品卸・顧客管理アプリ販売・メディア運営会社コーポレートサイト</a></li></ul></div><div id="latest-comments" class="activity-block"><h4>コメント</h4><div id="the-comment-list" data-wp-lists="list:comment">
												<div id="comment-177" class="pingback even thread-even depth-1 comment-item approved">


													<div class="dashboard-comment-wrap">
														<h4 class="comment-meta"><a href="http://www.luck2.localhost/wp-admin/post.php?post=3320&amp;action=edit">会社案内</a> <a class="comment-link" href="http://www.luck2.localhost/company#comment-177">#</a> への <strong>ピンバック</strong></h4>
														<p class="comment-author"><a href="http://www.luck2.localhost/3378.html" rel="external nofollow" class="url">ホームページリニューアルしております | 京都でウェブ制作なら らくらく株式会社</a></p>

														<blockquote><p>[…] 会社案内 […] </p></blockquote>
														<p class="row-actions"><span class="approve"><a href="comment.php?action=approvecomment&amp;p=3320&amp;c=177&amp;_wpnonce=a7b9004262" data-wp-lists="dim:the-comment-list:comment-177:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" title="このコメントを承認">承認する</a></span><span class="unapprove"><a href="comment.php?action=unapprovecomment&amp;p=3320&amp;c=177&amp;_wpnonce=a7b9004262" data-wp-lists="dim:the-comment-list:comment-177:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" title="このコメントを承認しない">承認しない</a></span><span class="reply hide-if-no-js"> | <a onclick="window.commentReply &amp;&amp; commentReply.open('177','3320');return false;" class="vim-r hide-if-no-js" title="このコメントに返信する" href="#">返信</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=177" title="コメントの編集">編集</a></span><span class="spam"> | <a href="comment.php?action=spamcomment&amp;p=3320&amp;c=177&amp;_wpnonce=f0f8dac962" data-wp-lists="delete:the-comment-list:comment-177::spam=1" class="vim-s vim-destructive" title="このコメントをスパムとしてマーク">スパム</a></span><span class="trash"> | <a href="comment.php?action=trashcomment&amp;p=3320&amp;c=177&amp;_wpnonce=f0f8dac962" data-wp-lists="delete:the-comment-list:comment-177::trash=1" class="delete vim-d vim-destructive" title="コメントをゴミ箱へ移動">ゴミ箱</a></span></p>
													</div>
												</div>

												<div id="comment-169" class="comment odd alt thread-odd thread-alt depth-1 comment-item approved">

													<img alt="" src="http://0.gravatar.com/avatar/c23362858cff5f563b25b3ac8698527b?s=50&amp;d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D50&amp;r=G" class="avatar avatar-50 photo" height="50" width="50">

													<div class="dashboard-comment-wrap">
														<h4 class="comment-meta">
															<a href="http://www.luck2.localhost/wp-admin/post.php?post=3027&amp;action=edit">【続編】PHP＋Twitter API 1.1 複数のキーワードでつぶやきを検索して公式RTする</a> <a class="comment-link" href="http://www.luck2.localhost/3027.html#comment-169">#</a> に <cite class="comment-author">ame</cite> より  <span class="approve">[承認待ち]</span>			</h4>

														<blockquote><p>初めまして

																自分宛に来たリプライをRTするようなものを作りた位と思いこちら…</p></blockquote>
														<p class="row-actions"><span class="approve"><a href="comment.php?action=approvecomment&amp;p=3027&amp;c=169&amp;_wpnonce=b671340bff" data-wp-lists="dim:the-comment-list:comment-169:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" title="このコメントを承認">承認する</a></span><span class="unapprove"><a href="comment.php?action=unapprovecomment&amp;p=3027&amp;c=169&amp;_wpnonce=b671340bff" data-wp-lists="dim:the-comment-list:comment-169:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" title="このコメントを承認しない">承認しない</a></span><span class="reply hide-if-no-js"> | <a onclick="window.commentReply &amp;&amp; commentReply.open('169','3027');return false;" class="vim-r hide-if-no-js" title="このコメントに返信する" href="#">返信</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=169" title="コメントの編集">編集</a></span><span class="history"> | <a href="comment.php?action=editcomment&amp;c=169#akismet-status" title="コメント履歴を表示"> 履歴</a></span><span class="spam"> | <a href="comment.php?action=spamcomment&amp;p=3027&amp;c=169&amp;_wpnonce=dbcc766382" data-wp-lists="delete:the-comment-list:comment-169::spam=1" class="vim-s vim-destructive" title="このコメントをスパムとしてマーク">スパム</a></span><span class="trash"> | <a href="comment.php?action=trashcomment&amp;p=3027&amp;c=169&amp;_wpnonce=dbcc766382" data-wp-lists="delete:the-comment-list:comment-169::trash=1" class="delete vim-d vim-destructive" title="コメントをゴミ箱へ移動">ゴミ箱</a></span></p>
													</div>
												</div>

												<div id="comment-140" class="pingback even thread-even depth-1 comment-item approved">


													<div class="dashboard-comment-wrap">
														<h4 class="comment-meta"><a href="http://www.luck2.localhost/wp-admin/post.php?post=2999&amp;action=edit">Twitter API 1.1を使用してPHPでつぶやきの検索結果を取得したときのメモ</a> <a class="comment-link" href="http://www.luck2.localhost/2999.html#comment-140">#</a> への <strong>ピンバック</strong></h4>
														<p class="comment-author"><a href="http://www.mrlittlebig.com/blog/055/" rel="external nofollow" class="url">TwitterAPIでリツイートランキングを作成する « trace</a></p>

														<blockquote><p>[…] ・PHP twitteroauth – GitH…</p></blockquote>
														<p class="row-actions"><span class="approve"><a href="comment.php?action=approvecomment&amp;p=2999&amp;c=140&amp;_wpnonce=e99fd955f1" data-wp-lists="dim:the-comment-list:comment-140:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" title="このコメントを承認">承認する</a></span><span class="unapprove"><a href="comment.php?action=unapprovecomment&amp;p=2999&amp;c=140&amp;_wpnonce=e99fd955f1" data-wp-lists="dim:the-comment-list:comment-140:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" title="このコメントを承認しない">承認しない</a></span><span class="reply hide-if-no-js"> | <a onclick="window.commentReply &amp;&amp; commentReply.open('140','2999');return false;" class="vim-r hide-if-no-js" title="このコメントに返信する" href="#">返信</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=140" title="コメントの編集">編集</a></span><span class="history"> | <a href="comment.php?action=editcomment&amp;c=140#akismet-status" title="コメント履歴を表示"> 履歴</a></span><span class="spam"> | <a href="comment.php?action=spamcomment&amp;p=2999&amp;c=140&amp;_wpnonce=b5fafe9db9" data-wp-lists="delete:the-comment-list:comment-140::spam=1" class="vim-s vim-destructive" title="このコメントをスパムとしてマーク">スパム</a></span><span class="trash"> | <a href="comment.php?action=trashcomment&amp;p=2999&amp;c=140&amp;_wpnonce=b5fafe9db9" data-wp-lists="delete:the-comment-list:comment-140::trash=1" class="delete vim-d vim-destructive" title="コメントをゴミ箱へ移動">ゴミ箱</a></span></p>
													</div>
												</div>

												<div id="comment-168" class="comment odd alt thread-odd thread-alt depth-1 comment-item approved">

													<img alt="" src="http://0.gravatar.com/avatar/471447d2dc1b3d80b76c711e52bb38a5?s=50&amp;d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D50&amp;r=G" class="avatar avatar-50 photo" height="50" width="50">

													<div class="dashboard-comment-wrap">
														<h4 class="comment-meta">
															<a href="http://www.luck2.localhost/wp-admin/post.php?post=3027&amp;action=edit">【続編】PHP＋Twitter API 1.1 複数のキーワードでつぶやきを検索して公式RTする</a> <a class="comment-link" href="http://www.luck2.localhost/3027.html#comment-168">#</a> に <cite class="comment-author">キャプテン・クロ</cite> より  <span class="approve">[承認待ち]</span>			</h4>

														<blockquote><p>試してみたらできました！
																ありがとうございます！
																ワン・ツー・ジャンゴでした…</p></blockquote>
														<p class="row-actions"><span class="approve"><a href="comment.php?action=approvecomment&amp;p=3027&amp;c=168&amp;_wpnonce=0266086354" data-wp-lists="dim:the-comment-list:comment-168:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" title="このコメントを承認">承認する</a></span><span class="unapprove"><a href="comment.php?action=unapprovecomment&amp;p=3027&amp;c=168&amp;_wpnonce=0266086354" data-wp-lists="dim:the-comment-list:comment-168:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" title="このコメントを承認しない">承認しない</a></span><span class="reply hide-if-no-js"> | <a onclick="window.commentReply &amp;&amp; commentReply.open('168','3027');return false;" class="vim-r hide-if-no-js" title="このコメントに返信する" href="#">返信</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=168" title="コメントの編集">編集</a></span><span class="history"> | <a href="comment.php?action=editcomment&amp;c=168#akismet-status" title="コメント履歴を表示"> 履歴</a></span><span class="spam"> | <a href="comment.php?action=spamcomment&amp;p=3027&amp;c=168&amp;_wpnonce=fe3437dfe5" data-wp-lists="delete:the-comment-list:comment-168::spam=1" class="vim-s vim-destructive" title="このコメントをスパムとしてマーク">スパム</a></span><span class="trash"> | <a href="comment.php?action=trashcomment&amp;p=3027&amp;c=168&amp;_wpnonce=fe3437dfe5" data-wp-lists="delete:the-comment-list:comment-168::trash=1" class="delete vim-d vim-destructive" title="コメントをゴミ箱へ移動">ゴミ箱</a></span></p>
													</div>
												</div>

												<div id="comment-167" class="comment byuser comment-author-ken_kishimoto bypostauthor even thread-even depth-1 comment-item approved">

													<img alt="ken_kishimoto" src="http://www.luck2.localhost/wp-content/uploads/2014/05/ken_kishimoto_avatar_1400393766-50x50.jpg" class="avatar avatar-50 photo" height="50" width="50">

													<div class="dashboard-comment-wrap">
														<h4 class="comment-meta">
															<a href="http://www.luck2.localhost/wp-admin/post.php?post=3027&amp;action=edit">【続編】PHP＋Twitter API 1.1 複数のキーワードでつぶやきを検索して公式RTする</a> <a class="comment-link" href="http://www.luck2.localhost/3027.html#comment-167">#</a> に <cite class="comment-author">もちお</cite> より  <span class="approve">[承認待ち]</span>			</h4>

														<blockquote><p>キャプテン・クロさん、コメントありがとうございます。
																お役に立てて何よりです。…</p></blockquote>
														<p class="row-actions"><span class="approve"><a href="comment.php?action=approvecomment&amp;p=3027&amp;c=167&amp;_wpnonce=d6f33a236d" data-wp-lists="dim:the-comment-list:comment-167:unapproved:e7e7d3:e7e7d3:new=approved" class="vim-a" title="このコメントを承認">承認する</a></span><span class="unapprove"><a href="comment.php?action=unapprovecomment&amp;p=3027&amp;c=167&amp;_wpnonce=d6f33a236d" data-wp-lists="dim:the-comment-list:comment-167:unapproved:e7e7d3:e7e7d3:new=unapproved" class="vim-u" title="このコメントを承認しない">承認しない</a></span><span class="reply hide-if-no-js"> | <a onclick="window.commentReply &amp;&amp; commentReply.open('167','3027');return false;" class="vim-r hide-if-no-js" title="このコメントに返信する" href="#">返信</a></span><span class="edit"> | <a href="comment.php?action=editcomment&amp;c=167" title="コメントの編集">編集</a></span><span class="history"> | <a href="comment.php?action=editcomment&amp;c=167#akismet-status" title="コメント履歴を表示"> 履歴</a></span><span class="spam"> | <a href="comment.php?action=spamcomment&amp;p=3027&amp;c=167&amp;_wpnonce=9fe4c30366" data-wp-lists="delete:the-comment-list:comment-167::spam=1" class="vim-s vim-destructive" title="このコメントをスパムとしてマーク">スパム</a></span><span class="trash"> | <a href="comment.php?action=trashcomment&amp;p=3027&amp;c=167&amp;_wpnonce=9fe4c30366" data-wp-lists="delete:the-comment-list:comment-167::trash=1" class="delete vim-d vim-destructive" title="コメントをゴミ箱へ移動">ゴミ箱</a></span></p>
													</div>
												</div>
											</div><ul class="subsubsub">
												<li class="all"><a href="edit-comments.php?comment_status=all">すべて</a> |</li>
												<li class="moderated"><a href="edit-comments.php?comment_status=moderated">承認待ち <span class="count">(<span class="pending-count">0</span>)</span></a> |</li>
												<li class="approved"><a href="edit-comments.php?comment_status=approved">承認済み</a> |</li>
												<li class="spam"><a href="edit-comments.php?comment_status=spam">スパム <span class="count">(<span class="spam-count">0</span>)</span></a> |</li>
												<li class="trash"><a href="edit-comments.php?comment_status=trash">ゴミ箱 <span class="count">(<span class="trash-count">90</span>)</span></a></li>
											</ul><form method="get" action="">
												<div id="com-reply" style="display:none;"><div id="replyrow" style="display:none;">
														<div id="replyhead" style="display:none;"><h5>コメントに返信</h5></div>
														<div id="addhead" style="display:none;"><h5>新しいコメントを追加する</h5></div>
														<div id="edithead" style="display:none;">
															<div class="inside">
																<label for="author">名前</label>
																<input type="text" name="newcomment_author" size="50" value="" id="author">
															</div>

															<div class="inside">
																<label for="author-email">メール</label>
																<input type="text" name="newcomment_author_email" size="50" value="" id="author-email">
															</div>

															<div class="inside">
																<label for="author-url">URL</label>
																<input type="text" id="author-url" name="newcomment_author_url" size="103" value="">
															</div>
															<div style="clear:both;"></div>
														</div>

														<div id="replycontainer">
															<div id="wp-replycontent-wrap" class="wp-core-ui wp-editor-wrap html-active"><link rel="stylesheet" id="editor-buttons-css" href="http://www.luck2.localhost/wp-includes/css/editor.min.css?ver=3.9.1" type="text/css" media="all">
																<div id="wp-replycontent-editor-container" class="wp-editor-container"><div id="qt_replycontent_toolbar" class="quicktags-toolbar"><input type="button" id="qt_replycontent_strong" accesskey="b" class="ed_button button button-small" title="" value="b"><input type="button" id="qt_replycontent_em" accesskey="i" class="ed_button button button-small" title="" value="i"><input type="button" id="qt_replycontent_link" accesskey="a" class="ed_button button button-small" title="" value="link"><input type="button" id="qt_replycontent_block" accesskey="q" class="ed_button button button-small" title="" value="b-quote"><input type="button" id="qt_replycontent_del" accesskey="d" class="ed_button button button-small" title="" value="del"><input type="button" id="qt_replycontent_ins" accesskey="s" class="ed_button button button-small" title="" value="ins"><input type="button" id="qt_replycontent_img" accesskey="m" class="ed_button button button-small" title="" value="img"><input type="button" id="qt_replycontent_ul" accesskey="u" class="ed_button button button-small" title="" value="ul"><input type="button" id="qt_replycontent_ol" accesskey="o" class="ed_button button button-small" title="" value="ol"><input type="button" id="qt_replycontent_li" accesskey="l" class="ed_button button button-small" title="" value="li"><input type="button" id="qt_replycontent_code" accesskey="c" class="ed_button button button-small" title="" value="code"><input type="button" id="qt_replycontent_close" class="ed_button button button-small" title="開いているすべてのタグを閉じる" value="タグを閉じる"></div><textarea class="wp-editor-area" rows="20" cols="40" name="replycontent" id="replycontent"></textarea></div>
															</div>

														</div>

														<p id="replysubmit" class="submit">
															<a href="#comments-form" class="save button-primary alignright">
																<span id="addbtn" style="display:none;">コメントする</span>
																<span id="savebtn" style="display:none;">コメントを更新</span>
																<span id="replybtn" style="display:none;">返事を送信</span></a>
															<a href="#comments-form" class="cancel button-secondary alignleft">キャンセル</a>
															<span class="waiting spinner"></span>
															<span class="error" style="display:none;"></span>
															<br class="clear">
														</p>

														<input type="hidden" name="user_ID" id="user_ID" value="2">
														<input type="hidden" name="action" id="action" value="">
														<input type="hidden" name="comment_ID" id="comment_ID" value="">
														<input type="hidden" name="comment_post_ID" id="comment_post_ID" value="">
														<input type="hidden" name="status" id="status" value="">
														<input type="hidden" name="position" id="position" value="-1">
														<input type="hidden" name="checkbox" id="checkbox" value="0">
														<input type="hidden" name="mode" id="mode" value="dashboard">
														<input type="hidden" id="_ajax_nonce-replyto-comment" name="_ajax_nonce-replyto-comment" value="dc2ebac5b1"><input type="hidden" id="_wp_unfiltered_html_comment" name="_wp_unfiltered_html_comment" value="aad42a90f6"></div></div>
											</form>
											<div class="hidden" id="trash-undo-holder">
												<div class="trash-undo-inside"><strong></strong>からのコメントをゴミ箱に移動しました。 <span class="undo untrash"><a href="#">取り消し</a></span></div>
											</div>
											<div class="hidden" id="spam-undo-holder">
												<div class="spam-undo-inside"><strong></strong>からのコメントをスパムとしてマークしました。 <span class="undo unspam"><a href="#">取り消し</a></span></div>
											</div>
										</div></div></div>
							</div>
							<div id="tweetable_widget" class="postbox " style="display: block;">
								<div class="handlediv" title="クリックで切替"><br></div><h3 class="hndle"><span>Twitter (@safa)</span></h3>
								<div class="inside">
									<div id="twitter-submit-widget">
										<form action="" name="post-twitter">
											<p id="tweet-tools" style="width:100%">
												<span id="twitter-tools"><a href="#" id="shorten-url" title="Shorten Link"><img src="http://www.luck2.localhost/wp-content/plugins/tweetable/images/page_link.png" alt="Shorten Link"></a></span> &nbsp;
												<span id="chars-left"><strong>140</strong> characters left</span>
											</p>
											<textarea name="tweet" id="tweet" rows="2" cols="50" style="width:100%"></textarea>
											<input type="hidden" name="do" id="do_action" value="update-status">
											<input type="hidden" name="token" id="js_token" value="b18058a4881009baeffe6563cf173127">
											<input type="hidden" name="post_to" id="post_to" value="http://www.luck2.localhost/wp-content/plugins/tweetable/form_post.php">
											<p class="submit" style="width:100%; text-align:right;"><span id="loading-send-tweet" style="display:none;"><img src="http://www.luck2.localhost/wp-content/plugins/tweetable/images/loading.gif" alt="Loading..." style="vertical-align:middle"></span> <input type="submit" class="button-primary" id="update-status" value="Update Status" name="submit"></p>
										</form>
									</div>
								</div>
							</div>
						</div>	</div>
					<div id="postbox-container-2" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable"><div id="dashboard_quick_press" class="postbox " style="display: block;">
								<div class="handlediv" title="クリックで切替"><br></div><h3 class="hndle"><span><span class="hide-if-no-js">クイックドラフト</span> <span class="hide-if-js">下書き</span></span></h3>
								<div class="inside">

									<form name="post" action="http://www.luck2.localhost/wp-admin/post.php" method="post" id="quick-press" class="initial-form hide-if-no-js">


										<div class="input-text-wrap" id="title-wrap">
											<label class="prompt" for="title" id="title-prompt-text">

												タイトル			</label>
											<input type="text" name="post_title" id="title" autocomplete="off">
										</div>

										<div class="textarea-wrap" id="description-wrap">
											<label class="prompt" for="content" id="content-prompt-text">アイディアを書き留めてみましょう。</label>
											<textarea name="content" id="content" class="mceEditor" rows="3" cols="15"></textarea>
										</div>

										<p class="submit">
											<input type="hidden" name="action" id="quickpost-action" value="post-quickdraft-save">
											<input type="hidden" name="post_ID" value="3426">
											<input type="hidden" name="post_type" value="post">
											<input type="hidden" id="_wpnonce" name="_wpnonce" value="4f02a1d5f4"><input type="hidden" name="_wp_http_referer" value="/wp-admin/index.php">			<input type="submit" name="save" id="save-post" class="button button-primary" value="下書きとして保存">			<br class="clear">
										</p>

									</form>
									<div class="drafts"><h4 class="hide-if-no-js">下書き</h4>
										<ul><li>
												<div class="draft-title"><a href="http://www.luck2.localhost/wp-admin/post.php?post=3257&amp;action=edit" title="“Twitter検索で“http://”を含む検索がヒットしない件” を編集する">Twitter検索で“http://”を含む検索がヒットしない件</a><time datetime="2014-06-02T22:42:46+00:00">2014年6月2日</time></div><p>ブログではもはや定番のソーシャルボタン。 Twitterのツイートボタンもその一…</p></li>
											<li>
												<div class="draft-title"><a href="http://www.luck2.localhost/wp-admin/post.php?post=3419&amp;action=edit" title="“3Dプリンタを手に入れたWebデザイナーが作ってしまう5つのアイテム” を編集する">3Dプリンタを手に入れたWebデザイナーが作ってしまう5つのアイテム</a><time datetime="2014-06-02T22:42:35+00:00">2014年6月2日</time></div><p>（写真） こんにちは、岸本です。 3Dプリンタの話題が絶えない毎日です。 工場や…</p></li>
										</ul>
									</div></div>
							</div>
							<div id="dashboard_primary" class="postbox " style="display: block;">
								<div class="handlediv" title="クリックで切替"><br></div><h3 class="hndle"><span>WordPress ニュース</span></h3>
								<div class="inside">
									<div class="rss-widget"><ul><li><a class="rsswidget" href="http://ja.wordpress.org/2014/07/11/wordpress-4-0-beta-1/">WordPress 4.0 ベータ 1</a> <span class="rss-date">2014年7月10日</span><div class="rssSummary">WordPress 4.0 ベータ 1 がご利用いただけるようになりました !</div></li></ul></div><div class="rss-widget"><ul><li><a class="rsswidget" href="http://ja.forums.wordpress.org/topic/134888#post-185792" title="gblsmさん、お世話になります。 var_dumpで確認しましたところ、 $user_idは、 int(1) $myTermsは、 array(1) { [0]=> object(stdClass)#2854 (10) { [&quot;term_id&quot;]=> int(5) [&quot;name&quot;]=> string(21) &quot;テストグループ&quot; [&quot;slug&quot;]=> string(6) &quot;group1&quot; [&quot;term_group&quot;]=> int(0) [&quot;term_taxonomy_id&quot;]=> int(5) [&quot;taxonomy&quot;]">JunichiK :  "同じカスタムタクソノミーに属しているユーザーIDを変数に格納"</a></li><li><a class="rsswidget" href="http://ja.forums.wordpress.org/topic/135208#post-185791" title="WP_Query の Custom Field Parameters を使い、type の指定を DATE （ただし、入力されている経緯式がPHPが日付型と判断でき、全角などが混じっていない前提）を指定していただければ実現可能かと思われます。">jim912 :  "date_queryについて"</a></li><li><a class="rsswidget" href="http://ja.forums.wordpress.org/topic/135217#post-185790" title="Touchfolioのテーマを使用してポートフォリオサイトを作成しています。 Touchfolioはギャラリーを作成すると自動で説明部分にpinterestのput itボタン と facebookのshareボタンがついてきます。 これを後からプラグインで入れたものと取り替えたいのですが、 テーマ編集で該当のコード部分を探しても見当たりません。 どなたかわかる方いらっしゃいませんか？ http://dimsemenov.com/themes/touchfolio/demo/">kyoheihattori :  "【Touchfolio】テーマのSNSボタンについて"</a></li></ul></div><div class="rss-widget"><ul><li class="dashboard-news-plugin"><span>人気のプラグイン:</span> <a href="http://wordpress.org/plugins/iwp-client/" class="dashboard-news-plugin-link">InfiniteWP Client</a>&nbsp;<span>(<a href="plugin-install.php?tab=plugin-information&amp;plugin=iwp-client&amp;_wpnonce=49176e449e&amp;TB_iframe=true&amp;width=830&amp;height=504" class="thickbox" title="InfiniteWP Client">インストール</a>)</span></li></ul></div></div>
							</div>
						</div>	</div>
					<div id="postbox-container-3" class="postbox-container">
						<div id="column3-sortables" class="meta-box-sortables ui-sortable empty-container"></div>	</div>
					<div id="postbox-container-4" class="postbox-container">
						<div id="column4-sortables" class="meta-box-sortables ui-sortable empty-container"></div>	</div>
					</div>

					<input type="hidden" id="closedpostboxesnonce" name="closedpostboxesnonce" value="32e6277ec2"><input type="hidden" id="meta-box-order-nonce" name="meta-box-order-nonce" value="26d9814d3c">

					</div>




					<div id="poststuff">
						<div id="post-body">

							<div id="order-info" class="postbox">
								<div class="handlediv" title="クリックで切替"><br></div><h3 class="hndle"><span>注文情報</span></h3>
								<div class="inside">

								</div>
							</div>
							<div id="customer" class="postbox">
								<div class="handlediv" title="クリックで切替"><br></div><h3 class="hndle"><span>購入者情報</span></h3>
								<div class="inside">
								</div>
							</div>

						</div>
					</div>


					<table class="widefat" id="update-plugins-table">
						<thead>
						<tr>
							<th scope="col" class="manage-column check-column"><input type="checkbox" id="plugins-select-all"></th>
							<th scope="col" class="manage-column"><label for="plugins-select-all">すべて選択</label></th>
						</tr>
						</thead>

						<tfoot>
						<tr>
							<th scope="col" class="manage-column check-column"><input type="checkbox" id="plugins-select-all-2"></th>
							<th scope="col" class="manage-column"><label for="plugins-select-all-2">すべて選択</label></th>
						</tr>
						</tfoot>
						<tbody class="plugins">

						<tr>
							<th scope="row" class="check-column"><input type="checkbox" name="checked[]" value="contact-form-7/wp-contact-form-7.php"></th>
							<td><p><strong>Contact Form 7</strong><br>現在お使いのバージョンは 3.8.1 です。3.9 に更新します。 <a href="http://www.luck2.co.jp/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=contact-form-7&amp;section=changelog&amp;TB_iframe=true&amp;width=640&amp;height=662" class="thickbox" title="Contact Form 7">バージョン 3.9 の詳細を見る</a>.<br>WordPress 3.9.1 との互換性: 100% (作者による評価)</p></td>
						</tr>
						<tr>
							<th scope="row" class="check-column"><input type="checkbox" name="checked[]" value="social-networks-auto-poster-facebook-twitter-g/NextScripts_SNAP.php"></th>
							<td><p><strong>NextScripts: Social Networks Auto-Poster</strong><br>現在お使いのバージョンは 3.3.9 です。3.4.2 に更新します。 <a href="http://www.luck2.co.jp/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=social-networks-auto-poster-facebook-twitter-g&amp;section=changelog&amp;TB_iframe=true&amp;width=640&amp;height=662" class="thickbox" title="NextScripts: Social Networks Auto-Poster">バージョン 3.4.2 の詳細を見る</a>.<br>WordPress 3.9.1 との互換性: 100% (作者による評価)</p></td>
						</tr>	</tbody>
					</table>


					<?php
				} else {
					settings_errors('orders');
				}
				?>
			<?php else: ?>
				<h2><?php _e('BASE To WordPress 注文管理', BASE_TO_WP_NAMEDOMAIN); ?></h2>
				<?php if (empty($e)) { ?>
					<?php //$BaseOAuthWP->render_list($orders_obj); ?>
					<form id="base-items-filter" method="get">
						<?php $OrderListTable->search_box('注文を検索','base-order-search-input'); ?>
						<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>" />
						<?php $OrderListTable->display(); ?>
					</form>
				<?php
				} else {
					settings_errors('orders');
				}
				?>
			<?php endif; ?>
		</div>
		<?php

	}

}

