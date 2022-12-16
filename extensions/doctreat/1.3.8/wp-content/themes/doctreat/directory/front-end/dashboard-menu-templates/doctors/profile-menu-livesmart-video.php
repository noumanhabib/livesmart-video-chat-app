<?php
/**
 *
 * The template part for displaying the dashboard menu
 *
 * @package   Doctreat
 * @author    Amentotech
 * @link      http://amentotech.com/
 * @since 1.0
 */

global $current_user, $wp_roles, $userdata, $post;

$reference 		 = (isset($_GET['ref']) && $_GET['ref'] <> '') ? $_GET['ref'] : '';
$mode 			 = (isset($_GET['mode']) && $_GET['mode'] <> '') ? $_GET['mode'] : '';
$user_identity   = $current_user->ID;
?>
<li class="<?php echo esc_attr( $reference  === 'livesmart-video' ? 'tg-active' : ''); ?>">
	<a href="<?php Doctreat_Profile_Menu::doctreat_profile_menu_link('livesmart-video', $user_identity); ?>">
		<i class="ti-headphone-alt"></i>
		<span><?php esc_html_e('Video Chat','doctreat');?></span>
	</a>
</li>
