<?php
/**
 * Created by PhpStorm => 
 * User: K => Sasaki
 * Date: 2014/05/25
 * Time: 0:46
 */


$reset_account_uri = admin_url('admin.php?page=base_to_wp_install&reset_account=1&step=1');

//TODO DEBUG
if (isset($_GET['delete']) && $_GET['delete']==1)
	delete_option('base_to_wp_account_activated');


//TODO DEBUG
debug_show_options();

?>
<div class="wrap">
	<h2><?php _e('BASE To WordPress Settings', BASE_TO_WP_NAMEDOMAIN); ?></h2>
	<p><a href="<?php echo $reset_account_uri ;?>">[再セットアップ]</a></p>
	<p><a href="<?php echo '?page=base_to_wp_settings&delete=1' ;?>">#TODO DEBUG Delete option 'base_to_wp_account_activated'</a></p>
</div>

