<?php
/**
 *
 * The template part for displaying account settings
 *
 * @package   Workreap
 * @author    Amentotech
 * @link      http://amentotech.com/
 * @since 1.0
 */
global $current_user, $wp_roles, $userdata, $post;
$user_identity 	= $current_user->ID;
$linked_profile = doctreat_get_linked_profile_id($user_identity);
$post_id 		= $linked_profile;
$mode 			= (isset($_GET['mode']) && $_GET['mode'] <> '') ? $_GET['mode'] : '';
$user_type		= apply_filters('workreap_get_user_type', $user_identity );

?>


<div class="col-xs-12 col-sm-12 col-md-12 col-lg-8 col-xl-6 float-left">
	<div class="wt-dashboardbox" style="width:1300px !important;">
		<div class="wt-dashboardboxtitle wt-titlewithsearch">
			<h2><?php esc_html_e('LiveSmart Video Chat', 'doctreat'); ?></h2>
		</div>
		<div class="wt-dashboardboxcontent wt-helpsupporthead wt-registerformmain">
		
				<iframe src="<?php echo get_option('livesmart_server_url') ?>dash/integration.php?wplogin=<?php echo $current_user->user_login; ?>&url=<?php echo base64_encode(get_option('livesmart_server_url')); ?>" style="background-color:#ffffff; padding: 0; margin:0" width="100%" height="600" ></iframe>
				<br/>
				<a href="<?php echo get_option('livesmart_server_url') ?>dash/" target="_blank">LiveSmart Dashboard</a>
		
		</div>
	</div>
</div>