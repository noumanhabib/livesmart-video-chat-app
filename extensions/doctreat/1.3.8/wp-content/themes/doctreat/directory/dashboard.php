<?php
/**
 * Template Name: Dashboard
 *
 * @package Doctreat
 * @since Doctreat 1.0
 * @desc Template used for front end dashboard.
 */
/* Define Global Variables */
global $current_user, $wp_roles, $userdata, $post,$theme_settings;
get_header();
$user_identity 	= $current_user->ID;
$url_identity 	= !empty($_GET['identity']) ? intval($_GET['identity']) : '';
$user_type		= apply_filters('doctreat_get_user_type', $user_identity );
$post_id		= doctreat_get_linked_profile_id( $user_identity );
$is_verified 	= get_post_meta($post_id, '_is_verified', true);
$ref 			= !empty($_GET['ref']) ? esc_html( $_GET['ref'] ) : '';
$mode 			= !empty($_GET['mode']) ? esc_html( $_GET['mode'] ) : '';
$verify_user	= !empty( $theme_settings['verify_user'] ) ? $theme_settings['verify_user'] : '';
$doctor_location	= !empty($theme_settings['doctor_location']) ? $theme_settings['doctor_location'] : '';

$doctor_booking_option			= '';
if( function_exists( 'doctreat_get_booking_oncall_doctors_option' ) ) {
	$doctor_booking_option	= doctreat_get_booking_oncall_doctors_option();
}

if( have_posts() ) {
	while ( have_posts() ) : the_post();
	the_content();
	wp_link_pages( array(
		'before'      => '<div class="dc-paginationvtwo"><nav class="dc-pagination"><ul>',
		'after'       => '</ul></nav></div>',
		) );
	endwhile;
	wp_reset_postdata();
}

$remove_payouts	= doctreat_theme_option('enable_checkout_page');

if ( is_user_logged_in() 
	&& ( $user_type === 'doctors' || $user_type === 'hospitals' || $user_type === 'regular_users' ) ) {
	Doctreat_Profile_Menu::doctreat_profile_menu_left(); 

	if( empty( $is_verified ) || $is_verified === 'no' ){
		$verify   = doctreat_get_signup_page_url(); 
		?>
		  <div class="dc-haslayout dc-jobalertsdashboard re-sendverification">
			<div class="row">
				<div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
					<?php if(!empty($verify_user) && $verify_user === 'no' ){
							Doctreat_Prepare_Notification::doctreat_warning(esc_html__('Verification', 'doctreat'), esc_html__('Your account will be approved after the verification', 'doctreat'),'','');
						} elseif(!empty($verify_user) && $verify_user === 'yes' ){
							Doctreat_Prepare_Notification::doctreat_warning(esc_html__('Verification', 'doctreat'), esc_html__('Your account is not verified. Please check your email for the verification', 'doctreat'),'javascript:;',esc_html__('Resend email', 'doctreat'));
						}
					?>
				</div>
			</div>
		  </div>
	<?php }?>
	<section class="dc-haslayout dc-dbsectionspace">
		<div class="row">
			<?php 
				if( !empty($ref) && $_GET['ref'] === 'profile' && $mode === 'settings' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/'.$user_type.'/dashboard', 'profile-settings');
				}elseif ( !empty($ref) && $_GET['ref'] === 'profile' && $mode === 'social' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/dashboard', 'social-profile');
				} elseif( $mode === 'educations') {
					get_template_part('directory/front-end/templates/'.$user_type.'/dashboard', 'profile-settings-educations');
				} elseif( !empty($ref) && $ref === 'prescription' && $mode === 'view' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/'.$user_type.'/dashboard', 'update-prescription');
				} elseif( $mode === 'awards') {
					get_template_part('directory/front-end/templates/'.$user_type.'/dashboard', 'profile-settings-awards');
				} elseif( $mode === 'registrations') {
					get_template_part('directory/front-end/templates/'.$user_type.'/dashboard', 'profile-settings-registrations');
				} elseif( $mode === 'location') {
					get_template_part('directory/front-end/templates/'.$user_type.'/dashboard', 'profile-location');
				} elseif( $mode === 'gallery') {
					get_template_part('directory/front-end/templates/'.$user_type.'/dashboard', 'profile-gallery');
				} elseif( $mode === 'booking' && $user_type ==='doctors' && empty($doctor_booking_option)) {
					get_template_part('directory/front-end/templates/'.$user_type.'/dashboard', 'profile-booking-options');
				} else if( $user_type === 'doctors' && $ref === 'manage-article' && $mode === 'listings' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/doctors/dashboard', 'manage-article');
				} else if( $user_type === 'doctors' && $ref === 'appointment' && $mode === 'setting' && ($url_identity === $user_identity) ) {
					if( !empty($doctor_location) && $doctor_location !== 'clinic' && apply_filters('doctreat_is_appointment_allowed', 'dc_bookings', $url_identity) === true ){
						get_template_part('directory/front-end/templates/doctors/dashboard', 'appointment-setting');
					}
				} else if( $user_type === 'doctors' && $ref === 'appointment' && $mode === 'location-settings' && ($url_identity === $user_identity) ) {
					if( !empty($doctor_location) && $doctor_location !== 'hospitals' ){
						get_template_part('directory/front-end/templates/doctors/dashboard', 'appointment-location-settings');
					}
				} else if( $user_type === 'doctors' && $ref === 'appointment' && $mode === 'details' && ($url_identity === $user_identity) ) {
					if(apply_filters('doctreat_is_appointment_allowed', 'dc_bookings', $url_identity) === true ){
						get_template_part('directory/front-end/templates/doctors/dashboard', 'appointment-detials');
					}
				} else if( $user_type === 'doctors' && $ref === 'appointment' && $mode === 'listing' && ($url_identity === $user_identity) ) {
					if(apply_filters('doctreat_is_appointment_allowed', 'dc_bookings', $url_identity) === true ){
						get_template_part('directory/front-end/templates/doctors/dashboard', 'appointment-listing');
					}
				} else if ( $user_type === 'doctors' && !empty($ref) && $_GET['ref'] === 'package' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/dashboard', 'packages');
				} else if ( $user_type === 'doctors' && !empty($ref) && $_GET['ref'] === 'invoices' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/dashboard', 'invoices');
				} else if ( $user_type === 'doctors' && !empty($ref) && $_GET['ref'] === 'payouts' && ($url_identity === $user_identity) ) {
					if( !empty($remove_payouts) ){
						get_template_part('directory/front-end/templates/doctors/dashboard', 'payouts');
					}
				} else if ( $user_type === 'doctors' && !empty($ref) && $_GET['ref'] === 'livesmart-video' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/doctors/dashboard', 'livesmart-video');
				} elseif( $user_type === 'doctors' && !empty( $ref ) && $ref === 'specialities' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/dashboard', 'profile-settings-specialities');
				} else if( $user_type === 'hospitals' && $ref === 'team' && $mode === 'manage' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/hospitals/dashboard', 'team');
				} else if( $user_type === 'hospitals' && $ref === 'location' && $mode === 'details') {
					get_template_part('directory/front-end/templates/hospitals/dashboard', 'location-details');
				} elseif( $user_type === 'hospitals' && !empty( $ref ) && $ref === 'specialities' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/dashboard', 'profile-settings-specialities');
				} else if( $user_type === 'regular_users' && $ref === 'appointment' && $mode === 'listing' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/regular_users/dashboard', 'appointment-listing');
				} else if ( $user_type === 'regular_users' && !empty($ref) && $_GET['ref'] === 'invoices' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/dashboard', 'invoices');
				} else if ( !empty($ref) && $_GET['ref'] === 'account-settings' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/dashboard', 'account-manage');
				}elseif ( !empty($ref) && $_GET['ref'] === 'saved' && ($url_identity === $user_identity) ) {
					get_template_part('directory/front-end/templates/dashboard', 'saved-items');
				} else if( !empty( $ref ) && $ref === 'chat' && ($url_identity === $user_identity) ) {
					if( ( !empty( $user_type ) 
						 && ( $user_type === 'doctors' && apply_filters('doctreat_is_feature_allowed', 'dc_chat', $user_identity) === true ) ) 
						|| $user_type === 'hospitals' 
						|| $user_type === 'regular_users'
					) {
						get_template_part('directory/front-end/templates/dashboard', 'messaging');
					}
				} else{
					get_template_part('directory/front-end/templates/'.$user_type.'/dashboard', 'insights');
				}	
			?>
		</div>
	</section>
	<?php } else {?>
		<div class="container">
		 <div class="dc-haslayout page-data">
			<?php Doctreat_Prepare_Notification::doctreat_warning(esc_html__('Restricted Access', 'doctreat'), esc_html__('You have not any privilege to view this page.', 'doctreat'));?>
		  </div>
		</div>
<?php }
get_footer(); 