<?php
/**
 * Created by PhpStorm
 * User: K.Sasaki
 * Date: 2014/05/25
 * Time: 0:46
 */

//TODO DEBUG
ini_set('display_errors', true);
error_reporting(E_ALL);
debug_base();


try {


} catch (Exception $e) {
	var_dump($e->getMessage());
}
?>
<div class="wrap">
	<h2><?php _e('BASE To WordPress デザイン編集', BASE_TO_WP_NAMEDOMAIN); ?></h2>
	<p>#FIXME ここで商品一覧ショートコードとかウィジェットのデザインテーマが変更できます。</p>
	<p>レイアウト、背景、文字色、etc...</p>
	<p>ユーザーがテーマファイル（css）をアップしたらBTWPテーマとしてここに追加されます。</p>



</div>
