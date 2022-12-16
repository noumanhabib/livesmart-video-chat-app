<?php
/**
 * Get Earnigs Status
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if( !function_exists(  'doctreat_get_earning_status_list' ) ) {
	function doctreat_get_earning_status_list(){
		$list	= array(
			'pending' 	=> esc_html__('Pending','doctreat'),
			'completed' => esc_html__('Completed','doctreat'),
			'cancelled' => esc_html__('Cancelled','doctreat'),
			'processed' => esc_html__('Processed','doctreat')
		);
		
		return $list;
	}
}

/**
 * Upload temp files to WordPress media
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_temp_upload_to_media')) {
    function doctreat_temp_upload_to_media($file_url, $post_id) {
		global $wp_filesystem;
		if (empty($wp_filesystem)) {
			require_once (ABSPATH . '/wp-admin/includes/file.php');
			WP_Filesystem();
		}
		
        $json   =  array();
        $upload_dir = wp_upload_dir();
		$folderRalativePath = $upload_dir['baseurl']."/doctreat-temp";
		$folderAbsolutePath = $upload_dir['basedir']."/doctreat-temp";

		$args = array(
			'timeout'     => 15,
			'headers' => array('Accept-Encoding' => ''),
			'sslverify' => false
		);
		
		$response   	= wp_remote_get( $file_url, $args );
		$file_data		= wp_remote_retrieve_body($response);
		
		if(empty($file_data)){
			$json['attachment_id']  = '';
			$json['url']            = '';
			$json['name']			= '';
			return $json;
		}
		
        $filename 		= basename($file_url);
		
        if (wp_mkdir_p($upload_dir['path'])){
			 $file = $upload_dir['path'] . '/' . $filename;
		}  else {
            $file = $upload_dir['basedir'] . '/' . $filename;
		}

		//Rename file before update
		if (file_exists($file)) { 
			$i			= 1; 
			$new_path	= $file;

			while (file_exists($new_path)) { 
				$extension 		= pathinfo($file, PATHINFO_EXTENSION); 
				$actual_filename 	= pathinfo($file, PATHINFO_FILENAME); 
				$new_filename 	= $actual_filename . '-' . $i . '.' . $extension; 
				$new_path 		= $upload_dir['path'] . '/' . $new_filename; 
				$i++;  
			}
			
			$file	= $new_path;
		}
		
		$filename 			= basename($file);
		$actual_filename 	= pathinfo($file, PATHINFO_FILENAME); 
		
		//put content to the file
		file_put_contents($file, $file_data);
		
        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' 	=> $wp_filetype['type'],
            'post_title' 		=> sanitize_file_name($actual_filename),
            'post_content' 		=> '',
            'post_status' 		=> 'inherit'
        );
        
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        
        $json['attachment_id']  = $attach_id;
        $json['url']            = $upload_dir['url'] . '/' . basename( $filename );
		$json['name']			= $filename;
		$target_path = $folderAbsolutePath . "/" . $filename;
        unlink($target_path); //delete file after upload
        return $json;
    }
}

/**
 * Prepare social sharing links for job
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if( !function_exists( 'doctreat_get_term_name') ){
    function doctreat_get_term_name($term_id = '', $taxonomy = ''){
        if( !empty( $term_id ) && !empty( $taxonomy ) ){
            $term = get_term_by( 'id', $term_id, $taxonomy);  
            if( !empty( $term ) ){
                return $term->name;
            }
        }
        return '';
    }
}

/**
 * Get waiting time
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if( !function_exists(  'doctreat_get_waiting_time' ) ) {
	function doctreat_get_waiting_time(){
		$list	= array(
			'1' 	=> esc_html__('0 < 15 min','doctreat'),
			'2' 	=> esc_html__('15 to 30 min','doctreat'),
			'3' 	=> esc_html__('30 to 1 hr','doctreat'),
			'4' 	=> esc_html__('More then hr','doctreat')
		);
		
		return $list;
	}
}

/**
 * Get user review meta data
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_review_data')) {

    function doctreat_get_review_data($user_id, $review_key = '', $type = '') {
        $review_meta = get_user_meta($user_id, 'review_data', true);
        if ($type === 'value') {
            return !empty($review_meta[$review_key]) ? $review_meta[$review_key] : '';
        }
        return !empty($review_meta) ? $review_meta : array();
    }

}

/**
 * Get Average Ratings
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_everage_rating')) {

    function doctreat_get_everage_rating($user_id = '') {
		$data = array();
        $meta_query_args = array('relation' => 'AND');
        $meta_query_args[] = array(
            'key' 		=> 'user_to',
            'value' 	=> $user_id,
            'compare' 	=> '=',
            'type' 		=> 'NUMERIC'
        );

        $args = array('posts_per_page' => -1,
            'post_type' 		=> 'reviews',
            'post_status' 		=> 'publish',
            'order' 			=> 'ASC',
        );

        $args['meta_query'] = $meta_query_args;

        $average_rating = 0;
        $total_rating   = 0;
		
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                global $post;
                $user_rating = get_post_meta($post->ID, 'user_rating', true);
			
                $average_rating = $average_rating + $user_rating;
                $total_rating++;

            endwhile;
            wp_reset_postdata();
        }

        $data['wt_average_rating'] 			= 0;
        $data['wt_total_rating'] 			= 0;
        $data['wt_total_percentage'] 		= 0;
		
        if (isset($average_rating) && $average_rating > 0) {
            $data['wt_average_rating'] 			= $average_rating / $total_rating;
            $data['wt_total_rating'] 			= $total_rating;
            $data['wt_total_percentage'] 		= ( $average_rating / $total_rating) * 5;
        }

        return $data;
    }

}

/**
 * Count items in array
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_count_items')) {
    function doctreat_count_items($items) {
        if( is_array($items) ){
			return count($items);
		} else{
			return 0;
		}
    }
}

/**
 * Get doctor Ratings Headings
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_doctor_ratings' ) ) {
	function doctreat_doctor_ratings(){
		global $theme_settings;
		if ( $theme_settings ) {
			$ratings_headings	= !empty( $theme_settings['feedback_questions'] ) ? $theme_settings['feedback_questions'] : array();
			
			if( !empty( $ratings_headings ) ){
				$ratings_headings = array_filter($ratings_headings);
				$ratings_headings = array_combine(array_map('sanitize_title', $ratings_headings), $ratings_headings);
				return $ratings_headings;
			} else{
				return array();
			}
			
		} else {
			return array();
		}
	}
}

/**
 * Get search page uri
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_search_page_uri' ) ) {
    function doctreat_get_search_page_uri( $type = '' ) {
		global $theme_settings;
		$tpl_dashboard 	= !empty( $theme_settings['dashboard_tpl'] ) ? get_permalink( (int) $theme_settings['dashboard_tpl']) : '';
		$tpl_search 	= !empty( $theme_settings['search_result_page'] ) ? get_permalink( (int) $theme_settings['search_result_page']) : '';
               
        $search_page = '';
		
        if ( !empty( $type ) && ( $type === 'doctors' || $type === 'hospitals' ) ) {
            $search_page = esc_url( $tpl_search );
        }  elseif ( !empty( $type ) && $type === 'dashboard' ) {
            $search_page = esc_url( $tpl_dashboard ) ;           
        }
        
        return $search_page;
    }
}

/**
 * Match Cart items
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_matched_cart_items')) {

    function doctreat_matched_cart_items($product_id) {
        // Initialise the count
        $count = 0;

        if (!WC()->cart->is_empty()) {
            foreach (WC()->cart->get_cart() as $cart_item):
                $items_id = $cart_item['product_id'];

                // for a unique product ID (integer or string value)
                if ($product_id == $items_id) {
                    $count++; // incrementing the counted items
                }
            endforeach;
            // returning counted items 
            return $count;
        }

        return $count;
    }

}

/**
 * Get package type
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_package_type')) {

	 function doctreat_get_package_type($key, $value) {
		global $wpdb;
		$meta_query_args = array();
		$args = array(
			'post_type' 			=> 'product',
			'posts_per_page' 		=> 1,
			'order' 				=> 'DESC',
			'orderby' 				=> 'ID',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts' 	=> 1
		);
		 
		$meta_query_args[] = array(
			'key' 			=> $key,
			'value' 		=> $value,
			'compare' 		=> '=',
		);	
		 
		$query_relation 		= array('relation' => 'AND',);
		$meta_query_args 		= array_merge($query_relation, $meta_query_args);
		$args['meta_query'] 	= $meta_query_args;
		
		$trial_product = get_posts($args);
		
		if (!empty($trial_product)) {
            return (int) $trial_product[0]->ID;
        } else{
			 return 0;
		}
	}
	
}

/**
 * Get subscription metadata
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_subscription_metadata')) {

    function doctreat_get_subscription_metadata($key = '', $user_id) {
		$listing_type	= doctreat_theme_option('listing_type');
		if( $listing_type === 'free' ){
			return 'yes';
		}

        $dc_subscription 	= get_user_meta($user_id, 'dc_subscription', true);
		$current_date 		= current_time('mysql');
        if ( is_array( $dc_subscription ) && !empty( $dc_subscription[$key] ) ) {			
			if (!empty($dc_subscription['subscription_package_string']) && $dc_subscription['subscription_package_string'] > strtotime($current_date)) {
				return $dc_subscription[$key];
			} else {
				return '';
			}
        } else {
			return '';	
		}
    }

}

/**
 * Update Package vaule
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( !function_exists( 'doctreat_update_package_attribute_value' ) ) {

	function doctreat_update_package_attribute_value( $user_id, $attribute,$min_val=1) {
		$dc_subscription 	= get_user_meta($user_id, 'dc_subscription', true);
		$attribut_val		= !empty($dc_subscription) ? intval($dc_subscription[$attribute]) : 0;
		if(!empty($attribute) && !empty($dc_subscription)){
			$dc_subscription[$attribute]	= $attribut_val - $min_val;
			update_user_meta( $user_id, 'dc_subscription',$dc_subscription );
		}
	}
}

/**
 * Get Packages Type 
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( !function_exists( 'doctreat_packages_types' ) ) {

	function doctreat_packages_types( $post = '') {
		if ( !empty( $post ) ) {
			$package_type	= get_post_meta( $post->ID , 'package_type', true);
		}
		
		$packages						= array();
		$packages[0]					= esc_html__('Package Type', 'doctreat');
		$packages['doctors']			= esc_html__('Default', 'doctreat');
		$trail_doctors_package_id		= doctreat_get_package_type( 'package_type','trail_doctors');
		
		if( empty($trail_doctors_package_id ) || ($trail_doctors_package_id == $post->ID) ) {
			$packages['trail_doctors']		= esc_html__('Trial', 'doctreat');
		}
		
		return $packages;
	}
}

/**
 * Get Pakages Featured attribute
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_pakages_features_attributes')) {

    function doctreat_get_pakages_features_attributes( $key ='' , $attr = 'title' ) {
		$features		= doctreat_get_pakages_features();
		if( !empty ( $key ) && !empty ( $attr )) {
			$attribute	= $features[$key][$attr];
		} else {
			$attribute ='';
		}
		
		return $attribute;
	}
}

/**
 * Get user profile ID
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_linked_profile_id')) {

    function doctreat_get_linked_profile_id($user_identity, $type='users') {
		if( $type === 'post') {
			$linked_profile   	= get_post_meta($user_identity, '_linked_profile', true);
		}else {
			$linked_profile   	= get_user_meta($user_identity, '_linked_profile', true);
		}
		
        $linked_profile	= !empty( $linked_profile ) ? $linked_profile : '';
		
        return intval( $linked_profile );
    }
}

/**
 * Filter dashboard menu
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_dashboard_menu' ) ) {
	function doctreat_get_dashboard_menu() {
		global $current_user;
		
		$menu_settings = get_option( 'dc_dashboard_menu_settings' );
		
		$list	= array(		
			'insights'	=> array(
				'title' => esc_html__('Dashboard','doctreat'),
				'type'	=> 'none'
			),
			'view-profile'	=> array(
				'title' => esc_html__('View My Profile','doctreat'),
				'type'	=> 'doctors'
			),
			'chat'	=> array(
				'title' => esc_html__('Inbox','doctreat'),
				'type'	=> 'none'
			),
			'profile-settings'	=> array(
				'title' => esc_html__('Edit my profile','doctreat'),
				'type'	=> 'none'
			),
			'specialities'	=> array(
				'title' => esc_html__('Specialities &amp; Services','doctreat'),
				'type'	=> 'none'
			),
			'account-settings'	=> array(
				'title' => esc_html__('Account Settings','doctreat'),
				'type'	=> 'none'
			),
			'appointments-listing'	=> array(
				'title' => esc_html__('Appointment Listing','doctreat'),
				'type'	=> 'doctors'
			),
			'appointments-listings'	=> array(
				'title' => esc_html__('Appointment Listing','doctreat'),
				'type'	=> 'regular_users'
			),
			'appointments-settings'	=> array(
				'title' => esc_html__('Appointment Settings','doctreat'),
				'type'	=> 'doctors'
			),
			'manage-article'	=> array(
				'title' => esc_html__('Manage Articles','doctreat'),
				'type'	=> 'doctors'
			),
			'payouts-settings'	=> array(
				'title' => esc_html__('Payouts Settings','doctreat'),
				'type'	=> 'doctors'
			),
			'livesmart-video'	=> array(
				'title' => esc_html__('Video Chat','doctreat'),
				'type'	=> 'doctors'
			),
			'manage-team'	=> array(
				'title' => esc_html__('Manage Team','doctreat'),
				'type'	=> 'hospitals'
			),
			
			'saved'	=> array(
				'title' => esc_html__('My Saved Items','doctreat'),
				'type'	=> 'none'
			),
			'packages'	=> array(
				'title' => esc_html__('Packages','doctreat'),
				'type'	=> 'doctors'
			),
			'invoices'	=> array(
				'title' => esc_html__('Invoices','doctreat'),
				'type'	=> 'doctors'
			),
			'invoices-regular-users'	=> array(
				'title' => esc_html__('Invoices','doctreat'),
				'type'	=> 'regular_users'
			),

			'logout'	=> array(
				'title' => esc_html__('Logout','doctreat'),
				'type'	=> 'none'
			)
		);
		
		$remove_payouts	= doctreat_theme_option('enable_checkout_page');
		
		if( empty($remove_payouts)){
			unset($list['payouts-settings']);
		}

		$listing_type	= doctreat_theme_option('listing_type');
		if( !empty($listing_type) && $listing_type ==='free' ){
			unset($list['packages']);
		}

		$final_list	= !empty( $menu_settings ) ? $menu_settings : $list;
		$menu_list 	= apply_filters('doctreat_filter_dashboard_menu',$final_list);
		return $menu_list;
	}
}

/**
 * Get doctor avatar
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( !function_exists( 'doctreat_get_doctor_avatar' ) ) {
	function doctreat_get_doctor_avatar( $sizes = array(), $user_identity = '' ) {
		extract( shortcode_atts( array(
			"width" => '100',
			"height" => '100',
		), $sizes ) );
		
		global $theme_settings;
		
		$default_avatar = !empty($theme_settings['default_doctor_avatar'])  ? $theme_settings['default_doctor_avatar'] : array();
		
		$thumb_id 		= get_post_thumbnail_id( $user_identity );
		
		if ( !empty( $thumb_id ) ) {
			$thumb_url = wp_get_attachment_image_src( $thumb_id, array( $width, $height ), true );
			if ( $thumb_url[1] == $width and $thumb_url[2] == $height ) {
				return !empty( $thumb_url[0] ) ? $thumb_url[0] : '';
			} else {
				$thumb_url = wp_get_attachment_image_src( $thumb_id, 'full', true );
				if (strpos($thumb_url[0],'media/default.png') !== false) {
					return '';
				} else{
					return !empty( $thumb_url[0] ) ? $thumb_url[0] : '';
				}
			}
		} else {
			if ( !empty( $default_avatar['id'] ) ) {
				$thumb_url = wp_get_attachment_image_src( $default_avatar['id'], array( $width, $height ), true );

				if ( $thumb_url[1] == $width and $thumb_url[2] == $height ) {
					return $thumb_url[0];
				} else {
					$thumb_url = wp_get_attachment_image_src( $default_avatar['id'], "full", true );
					if (strpos($thumb_url[0],'media/default.png') !== false) {
						return '';
					} else{
						if ( !empty( $thumb_url[0] ) ) {
							return $thumb_url[0];
						} else {
							return false;
						}
					}
				}
			} else {
				return false;
			}
		}
	}
}

/**
 * Get doctor avatar
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( !function_exists( 'doctreat_get_others_avatar' ) ) {
	function doctreat_get_others_avatar( $sizes = array(), $user_identity = '' ) {
		extract( shortcode_atts( array(
			"width" => '100',
			"height" => '100',
		), $sizes ) );
		
		global $theme_settings;
		
		$default_avatar = !empty($theme_settings['default_others_users'])  ? $theme_settings['default_others_users'] : array();
		
		$thumb_id 		= get_post_thumbnail_id( $user_identity );
		
		if ( !empty( $thumb_id ) ) {
			$thumb_url = wp_get_attachment_image_src( $thumb_id, array( $width, $height ), true );
			if ( $thumb_url[1] == $width and $thumb_url[2] == $height ) {
				return !empty( $thumb_url[0] ) ? $thumb_url[0] : '';
			} else {
				$thumb_url = wp_get_attachment_image_src( $thumb_id, 'full', true );
				if (strpos($thumb_url[0],'media/default.png') !== false) {
					return '';
				} else{
					return !empty( $thumb_url[0] ) ? $thumb_url[0] : '';
				}
			}
		} else {
			if ( !empty( $default_avatar['id'] ) ) {
				$thumb_url = wp_get_attachment_image_src( $default_avatar['id'], array( $width, $height ), true );

				if ( $thumb_url[1] == $width and $thumb_url[2] == $height ) {
					return $thumb_url[0];
				} else {
					$thumb_url = wp_get_attachment_image_src( $default_avatar['id'], "full", true );
					if (strpos($thumb_url[0],'media/default.png') !== false) {
						return '';
					} else{
						if ( !empty( $thumb_url[0] ) ) {
							return $thumb_url[0];
						} else {
							return false;
						}
					}
				}
			} else {
				return false;
			}
		}
	}
}

/**
 * User verification check
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if( !function_exists(  'doctreat_get_username' ) ) {
	function doctreat_get_username( $user_id = '' , $linked_profile = '' ){
		
		if( !empty( $linked_profile ) ){
			return get_the_title($linked_profile);
		} 
		
		if ( empty($user_id) ) {
            return esc_html__('unnamed', 'doctreat');
        }
		
        $userdata = get_userdata($user_id);
        $user_role = '';
        if (!empty($userdata->roles[0])) {
            $user_role = $userdata->roles[0];
        }

        if (!empty($user_role) && $user_role === 'doctors' || $user_role === 'hospitals' || $user_role === 'regular_users' ) {
			$linked_profile   	= doctreat_get_linked_profile_id($user_id);
			if( !empty( $linked_profile ) ){
				return doctreat_full_name($linked_profile);
			} else{
				if (!empty($userdata->first_name) && !empty($userdata->last_name)) {
					return $userdata->first_name . ' ' . $userdata->last_name;
				} else if (!empty($userdata->first_name) && empty($userdata->last_name)) {
					return $userdata->first_name;
				} else if (empty($userdata->first_name) && !empty($userdata->last_name)) {
					return $userdata->last_name;
				} else {
					return esc_html__('No Name', 'doctreat');
				}
			}
			
		} else{
			if (!empty($userdata->first_name) && !empty($userdata->last_name)) {
                return $userdata->first_name . ' ' . $userdata->last_name;
            } else if (!empty($userdata->first_name) && empty($userdata->last_name)) {
                return $userdata->first_name;
            } else if (empty($userdata->first_name) && !empty($userdata->last_name)) {
                return $userdata->last_name;
            } else {
                return esc_html__('No Name', 'doctreat');
            }
		}
	}
}

/**
 * Report reasons
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if( !function_exists(  'doctreat_get_report_reasons' ) ) {
	function doctreat_get_report_reasons(){
		$list	= array(
			'fake' 		=> esc_html__('This is the fake', 'doctreat'),
			'bahavior' 	=> esc_html__('Their behavior is inappropriate or abusive', 'doctreat'),
			'Other' 	=> esc_html__('Other', 'doctreat'),
		);
		
		$list	= apply_filters('doctreat_filter_reasons',$list);
		return $list;
	}
}

/**
 * Get user avatar
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( !function_exists( 'doctreat_get_hospital_avatar' ) ) {
	function doctreat_get_hospital_avatar( $sizes = array(), $user_identity = '' ) {
		global $theme_settings;
		extract( shortcode_atts( array(
			"width" => '100',
			"height" => '100',
		), $sizes ) );

		$default_avatar = !empty( $theme_settings['default_hospital_image'] ) ? $theme_settings['default_hospital_image'] : '';

		$thumb_id = get_post_thumbnail_id( $user_identity );
		
		if ( !empty( $thumb_id ) ) {
			$thumb_url = wp_get_attachment_image_src( $thumb_id, array( $width, $height ), true );
			if ( $thumb_url[1] == $width and $thumb_url[2] == $height ) {
				return !empty( $thumb_url[0] ) ? $thumb_url[0] : '';
			} else {
				$thumb_url = wp_get_attachment_image_src( $thumb_id, 'full', true );
				if (strpos($thumb_url[0],'media/default.png') !== false) {
					return '';
				} else{
					return !empty( $thumb_url[0] ) ? $thumb_url[0] : '';
				}
			}
		} else {
			if ( !empty( $default_avatar['id'] ) ) {
				$thumb_url = wp_get_attachment_image_src( $default_avatar['id'], array( $width, $height ), true );

				if ( $thumb_url[1] == $width and $thumb_url[2] == $height ) {
					return $thumb_url[0];
				} else {
					$thumb_url = wp_get_attachment_image_src( $default_avatar['id'], "full", true );
					if (strpos($thumb_url[0],'media/default.png') !== false) {
						return '';
					} else{
						if ( !empty( $thumb_url[0] ) ) {
							return $thumb_url[0];
						} else {
							return false;
						}
					}
				}
			} else {
				return false;
			}
		}
	}
}

/**
 * Add http from URL
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_add_http')) {

    function doctreat_add_http($url) {
        $protolcol = is_ssl() ? "https" : "http";
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = $protolcol . ':' . $url;
        }
        return $url;
    }

}

/**
 * Get page id by slug
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_page_by_slug')) {

    function doctreat_get_page_by_slug($slug = '', $post_type = 'post', $return = 'id') {
        $args = array(
            'name' => $slug,
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => 1
        );

        $post_data = get_posts($args);

        if (!empty($post_data)) {
            return (int) $post_data[0]->ID;
        }

        return false;
    }

}

/**
 * Add http from URL
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_matched_cart_items')) {

    function doctreat_matched_cart_items($product_id) {
        // Initialise the count
        $count = 0;

        if (!WC()->cart->is_empty()) {
            foreach (WC()->cart->get_cart() as $cart_item):
                $items_id = $cart_item['product_id'];

                // for a unique product ID (integer or string value)
                if ($product_id == $items_id) {
                    $count++; // incrementing the counted items
                }
            endforeach;
            // returning counted items 
            return $count;
        }

        return $count;
    }

}

/**
 * Get the terms
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_taxonomy_options')) {

    function doctreat_get_taxonomy_options($current = '', $taxonomyName = '', $parent = '') {
		
		if( taxonomy_exists($taxonomyName) ){
			//This gets top layer terms only.  This is done by setting parent to 0.  
			$parent_terms = get_terms($taxonomyName, array('parent' => 0, 'orderby' => 'slug', 'hide_empty' => false));


			$options = '';
			if (!empty($parent_terms)) {
				foreach ($parent_terms as $pterm) {
					$selected = '';
					if (!empty($current) && is_array($current) && in_array($pterm->slug, $current)) {
						$selected = 'selected';
					} else if (!empty($current) && !is_array($current) && $pterm->slug == $current) {
						$selected = 'selected';
					}

					$options .= '<option ' . $selected . ' value="' . $pterm->slug . '">' . $pterm->name . '</option>';
				}
			}

			echo do_shortcode($options);
		}else{
			echo '';
		}
    }

}

/**
 * Get taxonomy array
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_taxonomy_array')) {

    function doctreat_get_taxonomy_array($taxonomyName = '',$parent='') {
		
		if( taxonomy_exists($taxonomyName) ){
			if(!empty( $parent )){
				return get_terms($taxonomyName, array('parent' => $parent, 'orderby' => 'slug', 'hide_empty' => false));
			} else{
				return get_terms($taxonomyName, array('orderby' => 'slug', 'hide_empty' => false));
			}
			
		} else{
			return array();
		}  
	}
}

/**
 * List user specilities and services
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */

if( !function_exists(  'doctreat_list_services_with_specility' ) ) {
	
	function doctreat_list_services_with_specility( $profile_id = ''){
		$specialities_array	= array();
		
		if( !empty($profile_id) ){
			$am_specialities 		= doctreat_get_post_meta( $profile_id,'am_specialities');
			
			if( !empty( $am_specialities ) ) {
				foreach( $am_specialities as $key => $values ){ 
					$specialities_title	= doctreat_get_term_name($key ,'specialities');

					$logo 			= get_term_meta( $key, 'logo', true );
					$current_logo	= !empty( $logo['url'] ) ? esc_url($logo['url']) : '';
					$specialities_array[$key]['id']			= $key;
					$specialities_array[$key]['title']		= $specialities_title;
					$specialities_array[$key]['logo']		= $current_logo;

					$services_array		= array();
					if( !empty( $values ) ) {
						$service_index	= 0;
						foreach( $values as $index => $val ) {
							$service_index	++;
							$service_title							= doctreat_get_term_name($index ,'services');
							$services_array[$service_index]['title']		= $service_title;
							$services_array[$service_index]['service_id']	= $index;
							$services_array[$service_index]['price']		= !empty($val['price']) ? $val['price'] : '';
							$services_array[$service_index]['description']	= !empty($val['description']) ? $val['description'] : '';
						}
					}
					$specialities_array[$key]['services']	= array_values($services_array);
				}
			}
		}
		return $specialities_array;
	}
}


/**
 * Get the list hospital
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_list_hospital')) {
    function doctreat_get_list_hospital( $type = '', $author = '') {
        $args = array(
				'posts_per_page' 	=> '-1',
				'post_type' 		=> $type,
				'post_status' 		=> 'publish',
				'suppress_filters' 	=> false,
				'author'			=> $author
			);
		
        $options = '';
        $cust_query = get_posts($args);

        if (!empty($cust_query)) {
            foreach ($cust_query as $key => $dir) {
				$hospital_id	= get_post_meta( $dir->ID, 'hospital_id',true);
                $options .= '<option value="' . $dir->ID . '">' . get_the_title($hospital_id) . '</option>';
            }
        }

        echo do_shortcode($options);
    }

}

/**
 * Get time slots for booking app
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_time_slots_slots')) {
    function doctreat_get_time_slots_slots( $post_id = '', $day = '',$date ='') {
		$time_format 	= get_option('time_format');
		$slots			= get_post_meta($post_id,'am_slots_data',true);
		$slot_list		= array();
		if( !empty( $slots ) ){
			$slot_array	= $slots[$day]['slots'];
			
			if( !empty( $slot_array ) ) {
				$slots_array	= array();
				foreach( $slot_array as $key	=> $val ) {
					$post_meta		= array(
											'_appointment_date'		=> $date,
											'_booking_slot' 		=> $key ,
											'_booking_hospitals' 	=> $post_id ,
										   );
					$count_posts	= doctreat_get_total_posts_by_multiple_meta('booking',array('publish','pending'),$post_meta);
					
					$spaces			= $val['spaces'];
					if( $count_posts >= $spaces ) { 
						$disabled	= 'disabled'; 
						$spaces		= 0;
					} else { 
						$spaces		= $spaces - $count_posts;
						$disabled 	= ''; 
					}
					
					$slot_key_val 	= explode('-', $key);
					$slots_array['start_time']		= !empty($slot_key_val[0]) ? date($time_format, strtotime('2016-01-01' . $slot_key_val[0])) : '';
					$slots_array['end_time']		= !empty($slot_key_val[1]) ? date($time_format, strtotime('2016-01-01' . $slot_key_val[1])) : '';
					$slots_array['key']				= $key;
					$slots_array['status']			= $disabled;
					$slots_array['spaces']			= $spaces;
					$slot_list[]					= $slots_array;	
				}
			}
		} 	

        return $slot_list;
    }

}

/**
 * Get time slots for booking
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_time_slots_spaces')) {
    function doctreat_get_time_slots_spaces( $post_id = '', $day = '',$date ='') {
		$time_format 	= get_option('time_format');
		$date_format 	= get_option('date_format');
		
		$current_time	= current_time('timestamp' );
		$current_time	= date('Hi',$current_time);
		$slots			= get_post_meta($post_id,'am_slots_data',true);
		$slots_html		= '';
		
		if( !empty( $slots ) ){
			$slot_array	= !empty($slots[$day]['slots']) ? $slots[$day]['slots'] : array();
			if( !empty( $slot_array ) ) {
				foreach( $slot_array as $key	=> $val ) {
					$post_meta		= array(
											'_appointment_date'		=> $date,
											'_booking_slot' 		=> $key ,
											'_booking_hospitals' 	=> $post_id ,
										   );
					$count_posts			= doctreat_get_total_posts_by_multiple_meta('booking',array('publish','pending'),$post_meta);
					$slot_key_val 			= explode('-', $key);
					$spaces					= !empty($val['spaces']) ? $val['spaces'] : 0;
					$current_number 		= strtotime(date_i18n($date_format));
					$date_number			= strtotime($date);
					$slotnumber				= !empty($slot_key_val[0]) ? $slot_key_val[0] : 0;
					
					if( ($count_posts >= $spaces) ) { 
						$disabled	= 'disabled'; 
						$spaces		= 0;
					} else { 
						$spaces		= $spaces - $count_posts;
						$disabled 	= ''; 
					}
					
					if( ( $current_number == $date_number ) && ($current_time >= $slotnumber) ){
						$disabled	= 'disabled';
					}

					$slots_html	.= '<span class="dc-radio next-step"> ';
						$slots_html	.= '<input type="radio" id="firstavailableslot'.$key.'" name="booking_slot" value="'.$key.'" '.$disabled.'>';
						$slots_html	.= '<label for="firstavailableslot'.$key.'"><span>'.date($time_format, strtotime('2016-01-01' . $slot_key_val[0])).'</span><em>'.esc_html__('Spaces','doctreat').':'.$spaces.'</em></label>';
					$slots_html	.= '</span>';
				}
			}
		} 	

        return do_shortcode($slots_html);
    }

}

/**
 * Get total post by multiple meta
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_total_posts_by_multiple_meta')) {

    function doctreat_get_total_posts_by_multiple_meta($type='booking',$status,$metas='',$post_author='' ) {
		if( !empty( $metas ) ) {
			foreach( $metas as $key => $val ) {
				$meta_query_args[] = array(
					'key' 			=> $key,
					'value' 		=> $val,
					'compare' 		=> '='
				);
			}
		}
		
		$query_args = array(
			'posts_per_page'      => -1,
			'post_type' 	      => $type,
			'post_status'	 	  => $status,
			'ignore_sticky_posts' => 1
		);
		
		if(!empty ( $post_author ) ){
			$query_args['author']	= $post_author;
		}
		
		if (!empty($meta_query_args)) {
			$query_relation = array('relation' => 'AND',);
			$meta_query_args = array_merge($query_relation, $meta_query_args);
			$query_args['meta_query'] = $meta_query_args;
		}
		
        $query = new WP_Query($query_args);
        return $query->post_count;
    }
}

/**
 * Prepare Business Hours Settings
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_prepare_business_hours_settings')) {

    function doctreat_prepare_business_hours_settings() {
        return array(
            'monday' 	=> esc_html__('Monday', 'doctreat'),
            'tuesday' 	=> esc_html__('Tuesday', 'doctreat'),
            'wednesday' => esc_html__('Wednesday', 'doctreat'),
            'thursday' 	=> esc_html__('Thursday', 'doctreat'),
            'friday' 	=> esc_html__('Friday', 'doctreat'),
            'saturday' 	=> esc_html__('Saturday', 'doctreat'),
            'sunday' 	=> esc_html__('Sunday', 'doctreat')
        );
    }

}

/**
 * Get Week Array
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_week_array')) {

    function doctreat_get_week_array() {
        return array(
          	'mon' => esc_html__('Monday', 'doctreat'),
            'tue' => esc_html__('Tuesday', 'doctreat'),
            'wed' => esc_html__('Wednesday', 'doctreat'),
            'thu' => esc_html__('Thursday', 'doctreat'),
            'fri' => esc_html__('Friday', 'doctreat'),
            'sat' => esc_html__('Saturday', 'doctreat'),
            'sun' => esc_html__('Sunday', 'doctreat'),
        );
    }

}

/**
 * Get Week keys translation
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_week_keys_translation')) {

    function doctreat_get_week_keys_translation($key='') {
        $list	= array(
					'mon' => esc_html__('Mon', 'doctreat'),
					'tue' => esc_html__('Tue', 'doctreat'),
					'wed' => esc_html__('Wed', 'doctreat'),
					'thu' => esc_html__('Thu', 'doctreat'),
					'fri' => esc_html__('Fri', 'doctreat'),
					'sat' => esc_html__('Sat', 'doctreat'),
					'sun' => esc_html__('Sun', 'doctreat'),
				);
		
		return !empty($list[$key]) ? $list[$key] : '';
    }

}
/**
 * Time formate
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_date_24midnight')) {

    function doctreat_date_24midnight($format, $ts) {
        if (date("Hi", $ts) == "0000") {
            $replace = array(
                "H" => "24",
                "G" => "24",
                "i" => "00",
            );

            return date(
                    str_replace(
                            array_keys($replace), $replace, $format
                    ), $ts - 60 // take a full minute off, not just 1 second
            );
        } else {
            return date($format, $ts);
        }
    }

}

/**
 * Get distance between two points
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_GetDistanceBetweenPoints')) {
	function doctreat_GetDistanceBetweenPoints($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'Km') {
		$unit	= doctreat_get_distance_scale();
		
		$theta = $longitude1 - $longitude2;
		$distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
		$distance = acos($distance);
		$distance = rad2deg($distance);
		$distance = $distance * 60 * 1.1515; switch($unit) {
		  case 'Mi': break;
		  case 'Km' : $distance = $distance * 1.60934;
		}
		return (round($distance,2)).'&nbsp;'. strtolower( $unit );
	}
}

/**
 * Get distance between two points
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_distance_scale')) {
	function doctreat_get_distance_scale() {
		global $theme_settings;
		$dir_distance_type = !empty( $theme_settings['dir_distance_type'] ) ? $theme_settings['dir_distance_type']: 'km';
		$unit = !empty( $dir_distance_type ) && $dir_distance_type === 'mi' ? 'Mi' : 'Km';
		
		return $unit;
	}
}

/**
 * Get min/max lat/long
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_min_max_lat_lon')) {
	function doctreat_get_min_max_lat_lon(){
		global $theme_settings;
		$radius		= !empty( $_GET['geo_distance'] ) ? esc_html( $_GET['geo_distance'] ) : 10;
		$dir_distance_type = !empty( $theme_settings['dir_distance_type'] ) ? $theme_settings['dir_distance_type']: 'km';
		
		if ($dir_distance_type === 'km') {
			$radius = $radius * 0.621371;
		}

		$Latitude	= !empty( $_GET['lat'] ) ? esc_html( $_GET['lat'] ) : '';
		$Longitude	= !empty( $_GET['long'] ) ?  esc_html( $_GET['long'] ) : '';

		$minLat = $maxLat = $minLong = $maxLong = 0;
		if( !empty( $Latitude ) && !empty( $Longitude ) ){
			$zcdRadius = new RadiusCheck($Latitude, $Longitude, $radius);
			$minLat = $zcdRadius->MinLatitude();
			$maxLat = $zcdRadius->MaxLatitude();
			$minLong = $zcdRadius->MinLongitude();
			$maxLong = $zcdRadius->MaxLongitude();
		}
		
		$data	= array(
			'default_lat'   => $Latitude,
			'default_long'  => $Longitude,
			'minLat'  => $minLat,
			'maxLat'  => $maxLat,
			'minLong' => $minLong,
			'maxLong' => $maxLong,
		);
		
		return $data;
	}
}

/**
 * Get atitude and longitude for search
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_location_lat_long' ) ) {
	function doctreat_get_location_lat_long() {
		global $theme_settings;
		$protocol 		= is_ssl() ? 'https' : 'http';
		$dir_longitude = !empty( $theme_settings['dir_longitude'] ) ? $theme_settings['dir_longitude']: '-0.1262362';
		$dir_latitude = !empty( $theme_settings['dir_latitude'] ) ? $theme_settings['dir_latitude']: '51.5001524';
		
		$current_latitude	= $dir_latitude;
		$current_longitude	= $dir_longitude;

		if( !empty( $_GET['lat'] ) && !empty( $_GET['long'] ) ){
			$current_latitude	= esc_html( $_GET['lat'] );
			$current_longitude	= esc_html( $_GET['long'] );
		} else{
			
			$args = array(
				'timeout'     => 15,
				'headers' => array('Accept-Encoding' => ''),
				'sslverify' => false
			);
			
			$address	= !empty($_GET['geo']) ?  $_GET['geo'] : '';
			$prepAddr	= str_replace(' ','+',$address);
			
			$url	    = $protocol.'://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false';
			$response   = wp_remote_get( $url, $args );
			$geocode	= wp_remote_retrieve_body($response);

			$output	  = json_decode($geocode);

			if( isset( $output->results ) && !empty( $output->results ) ) {
				$Latitude	 = $output->results[0]->geometry->location->lat;
				$Longitude   = $output->results[0]->geometry->location->lng;
			}
			
			if( !empty( $Latitude ) && !empty( $Longitude ) ){
				$current_latitude	= $Latitude;
				$current_longitude	= $Longitude;
			} else{
				$current_latitude	= $dir_latitude;
				$current_longitude	= $dir_longitude;
			}
		}
		
		$location	= array();
		
		$location['lat']	= $current_latitude;
		$location['long']	= $current_longitude;
		
		return $location;
	}
}

/**
 * Get woocommmerce currency settings
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_current_currency' ) ) {
	function doctreat_get_current_currency() {
		$currency	= array();
		
		if (class_exists('WooCommerce')) {
			$currency['code']	= get_woocommerce_currency();
			$currency['symbol']	= get_woocommerce_currency_symbol();
		} else{
			$currency['code']	= 'USD';
			$currency['symbol']	= '$';
		}
		
		return $currency;
	}
}

/**
 * Get calendar date format
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_calendar_format' ) ) {
	function doctreat_get_calendar_format() {
		global $theme_settings;
		$calendar_format = !empty( $theme_settings['calendar_locale'] ) ? $theme_settings['calendar_locale']: 'Y-m-d';
		
		return $calendar_format;
	}
}


/**
 * Get term by slug
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_term_by_type')) {

    function doctreat_get_term_by_type($from = 'slug', $value = "", $taxonomy = 'sub_category', $return = 'id') {

        $term = get_term_by($from, $value, $taxonomy);
        if (!empty($term)) {
            if ($from === 'slug' && $return === 'id') {
                return $term->term_id;
            } elseif ($from === 'id' && $return === 'slug') {
                return $term->slug;
            } elseif ($from === 'name' && $return === 'id') {
                return $term->term_id;
            } elseif ($from === 'id' && $return === 'name') {
                return $term->name;
            } elseif ($from === 'name' && $return === 'slug') {
                return $term->slug;
            } elseif ($from === 'slug' && $return === 'name') {
                return $term->name;
            }elseif ($from === 'id' && $return === 'all') {
                return $term;
            } else {
                return $term->term_id;
            }
        }

        return false;
    }
}

/**
 * Get total post by user id
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_total_posts_by_user')) {

    function doctreat_get_total_posts_by_user($user_id = '',$type='sp_ads',$status='publish') {
        if (empty($user_id)) {
            return 0;
        }

        $args = array(
			'posts_per_page'	=> '-1',
            'post_type' 		=> $type,
            'post_status' 		=> $status,
            'author' 			=> $user_id,
            'suppress_filters' 	=> false
        );
        $query = new WP_Query($args);
        return $query->post_count;
    }
}

/**
 * Get total post by met key and value
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_total_posts_by_meta')) {

    function doctreat_get_total_posts_by_meta($type='doctors',$meta_key='',$meta_val,$status,$post_author ) {
		$meta_query_args	= array();
		
        //default
		$meta_query_args[] = array(
				'key' 			=> $meta_key,
				'value' 		=> $meta_val,
				'compare' 		=> '='
			); 

		$query_args = array(
			'posts_per_page'      => -1,
			'post_type' 	      => $type,
			'post_status'	 	  => $status,
			'ignore_sticky_posts' => 1
		);
		
		if(!empty ( $post_author ) ){
			$query_args['author']	= $post_author;
		}

		//Meta Query
		if (!empty($meta_query_args)) {
			$query_relation = array('relation' => 'AND',);
			$meta_query_args = array_merge($query_relation, $meta_query_args);
			$query_args['meta_query'] = $meta_query_args;
		}
		
        $query = new WP_Query($query_args);
        return $query->post_count;
    }
}

/**
 * Get Tag Line
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if(!function_exists('doctreat_get_tagline') ) {
	function doctreat_get_tagline($post_id ='') {
		$shoert_des		= doctreat_get_post_meta( $post_id, 'am_short_description');
		$shoert_des		= !empty( $shoert_des ) ? esc_html( $shoert_des ) : '';
		return $shoert_des;
	} 
}

/**
 * Get Location
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if(!function_exists('doctreat_get_location') ) {
	function doctreat_get_location($post_id ='') {
		global $theme_settings;
		$multiple_locations			= !empty( $theme_settings['multiple_locations'] ) ? $theme_settings['multiple_locations'] : 'no';	
		$args	= array();
		$terms 						= apply_filters('doctreat_get_tax_query',array(),$post_id,'locations',$args);
		
		if(!empty($multiple_locations) && $multiple_locations === 'yes'){
			$locations_name		= !empty( $terms ) ?  implode(',',wp_list_pluck($terms,'name'))  : '';
		}else{
			$locations_name		= !empty( $terms[0]->name ) ?  $terms[0]->name  : '';
		}
		
		if(!empty($locations_name) ) {
			$item['_country']	= $locations_name;
		} else {
			$item['_country']	= '';
		}
		
		return $item;
	} 
}

/**
 * Get doctors days
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if(!function_exists('doctreat_get_booking_days') ) {
	function doctreat_get_booking_days( $user_identity ='' ) {
		global $theme_settings;
		$doctor_location	= !empty($theme_settings['doctor_location']) ? $theme_settings['doctor_location'] : '';
		$days				= array();
		$slots				= array();
		
		if(!empty($doctor_location) && $doctor_location == 'hospitals'){
			$args 	= array(
						'fields'          	=> 'ids',
						'post_type'      	=> 'hospitals_team',
						'author' 			=> $user_identity,
						'post_status'    	=> 'publish',
						'posts_per_page' 	=> -1
					);

			$team_hospitals = get_posts( $args );
			if( !empty( $team_hospitals ) ){

				foreach( $team_hospitals as $item ){
					$slots	= get_post_meta( $item,'am_slots_data',true);
					if(!empty($slots) && is_array($slots)){
						if( !empty( $days ) ){
							$days	= array_merge($days, array_keys( $slots ));
						} else {
							$days	=  array_keys( $slots );
						}
					}

				}

			}
		}else if(!empty($doctor_location) && $doctor_location == 'clinic'){
			$post_id  		 = doctreat_get_linked_profile_id($user_identity);
			$location_id	= get_post_meta($post_id, '_doctor_location', true);
			
			$slots	= get_post_meta( $location_id,'am_slots_data',true);
			if(!empty($slots) && is_array($slots)){
				$days	=  array_keys( $slots );
			}
		}else{
			
			$post_id  		 = doctreat_get_linked_profile_id($user_identity);
			$location_id	= get_post_meta($post_id, '_doctor_location', true);
			
			$slots	= get_post_meta( $location_id,'am_slots_data',true);
			if(!empty($slots) && is_array($slots)){
				$days	=  array_keys( $slots );
			}
			
			$args 	= array(
						'fields'          	=> 'ids',
						'post_type'      	=> 'hospitals_team',
						'author' 			=> $user_identity,
						'post_status'    	=> 'publish',
						'posts_per_page' 	=> -1
					);

			$team_hospitals = get_posts( $args );
			if( !empty( $team_hospitals ) ){

				foreach( $team_hospitals as $item ){
					$slots	= get_post_meta( $item,'am_slots_data',true);
					if(!empty($slots) && is_array($slots)){
						if( !empty( $days ) ){
							$days	= array_merge($days, array_keys( $slots ));
						} else {
							$days	=  array_keys( $slots );
						}
					}

				}

			}
		}

		if( !empty( $days ) && is_array($slots) ){
			$days	= array_unique( $days );
		}

		
		return $days;
		
	} 
}

/**
 * Get doctors days
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if(!function_exists('doctreat_get_booking_clinic_days') ) {
	function doctreat_get_booking_clinic_days( $user_identity ='' ) {
		global $theme_settings;
		$days				= array();
		$sloats				= array();

		$args 	= array(
					'fields'          	=> 'ids',
					'post_type'      	=> 'dc_locations',
					'author' 			=> $user_identity,
					'post_status'    	=> 'publish',
					'posts_per_page' 	=> -1
				);

		$team_hospitals = get_posts( $args );
		if( !empty( $team_hospitals ) ){
			
			foreach( $team_hospitals as $item ){
				$sloats	= get_post_meta( $item,'am_slots_data',true);
				if(!empty($sloats) && is_array($sloats)){
					if( !empty( $days ) ){
						$days	= array_merge($days, array_keys( $sloats ));
					} else {
						$days	=  array_keys( $sloats );
					}
				}
				
			}
			
		}
		
		if( !empty( $days ) && is_array($sloats) ){
			$days	= array_unique( $days );
		}
		
		return $days;
		
	} 
}

/**
 * Get signup uri
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if( !function_exists( 'doctreat_get_signup_page_url' ) ) {    

    function doctreat_get_signup_page_url($key = 'step', $slug = '1') {
		global $theme_settings;
        $login_register		= !empty( $theme_settings['registration_form'] ) && !empty( $theme_settings['login_page'] ) ? $theme_settings['login_page'] : '';

        if(!empty( $login_register )){
            $signup_page_slug = esc_url(get_permalink((int) $login_register));            
        }

        if( !empty( $signup_page_slug ) ){
            return $signup_page_slug;
        }

        return '';
    }
}


/**
 * List Months
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_list_month' ) ) {
    function doctreat_list_month( ) {
		$month_names = array(
						'01'	=> esc_html__("January",'doctreat'),
						'02'	=> esc_html__("February",'doctreat'),
						'03' 	=> esc_html__("March",'doctreat'),
						'04'	=> esc_html__("April",'doctreat'),
						'05'	=> esc_html__("May",'doctreat'),
						'06'	=> esc_html__("June",'doctreat'),
						'07'	=> esc_html__("July",'doctreat'),
						'08'	=> esc_html__("August",'doctreat'),
						'09'	=> esc_html__("September",'doctreat'),
						'10'	=> esc_html__("October",'doctreat'),
						'11'	=> esc_html__("November",'doctreat'),
						'12'	=> esc_html__("December",'doctreat'),
					);
		
		return $month_names;
		
	}
}

/**
 * List Users Types
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_list_user_types' ) ) {
    function doctreat_list_user_types( ) {
		global $theme_settings;
		$user_types_names = array(
						'hospitals'		=> esc_html__("Hospital",'doctreat'),
						'doctors'		=> esc_html__("Doctor",'doctreat'),
						'regular_users' => esc_html__("Patient",'doctreat'),
						'seller'		=> esc_html__('Pharmacy(Vendor)','doctreat')
					);

		return $user_types_names;
		
	}
}

/**
 * List services by specialities
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_list_service_by_specialities' ) ) {
    function doctreat_list_service_by_specialities($speciality_id ) {
		$args = array(
			'hide_empty' => false, // also retrieve terms which are not used yet
			'meta_query' => array(
				array(
				   'key'       => 'speciality',
				   'value'     => $speciality_id,
				   'compare'   => '='
				)
			)
		);
		
		$services_array	 = array();
		if( taxonomy_exists('services') ){
			$services = get_terms( 'services', $args );
			if( !empty( $services ) ){
				foreach( $services as $service ) {
					$services_array[$service->term_id] = $service;
				}
			}
		}
		
		return $services_array;
	}
}

/**
 * Get full Dr name
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_full_name' ) ) {
    function doctreat_full_name( $post_id ) {
		global $theme_settings;
		$base_name_disable		= !empty( $theme_settings['base_name_disable'] ) ? $theme_settings['base_name_disable'] : '';
			
		$title 		= get_the_title($post_id);
		$title		= !empty( $title ) ? $title : '';
		if( !empty($base_name_disable) ){
			$dr_name	= doctreat_get_post_meta($post_id,'am_name_base');
			$user_identity	= doctreat_get_linked_profile_id($post_id,'post');
			$user_type		= apply_filters('doctreat_get_user_type', $user_identity );
			
			if( !empty( $dr_name ) && $user_type === 'doctors' ){
				$name_bases	= doctreat_get_name_bases($dr_name,'doctor');
				$dr_name	= $name_bases;
				$full_name	= $dr_name.' '.$title;
			} else {
				$full_name	= $title;
			}
		} else {
			$full_name	= $title;
		}
		
		return ucfirst( $full_name );
	}
}

/**
 * Get user post meta
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_post_meta' ) ) {
    function doctreat_get_post_meta( $post_id ='' , $meta_key = '') {
		$post_meta = array();
		
		if( !empty( $post_id )) {
			$post_type		= get_post_type($post_id);
			$post_meta		= get_post_meta($post_id, 'am_' . $post_type . '_data',true);	
			$post_meta		= !empty( $post_meta) ? $post_meta : array();
		}
		
		if( !empty( $meta_key ) ){
			$post_meta		= !empty( $post_meta[$meta_key] ) ? $post_meta[$meta_key] : array();
		}
		
		return $post_meta;
	}
}

/**
 * Check wishlist
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_check_wishlist' ) ) {
    function doctreat_check_wishlist( $post_id,$key = '' ) {
		global $current_user;
		$return = false;
		$linked_profile   	= doctreat_get_linked_profile_id($current_user->ID);
		$saved_doctors 		= get_post_meta($linked_profile, $key, true);
		$wishlist   		= !empty( $saved_doctors ) && is_array( $saved_doctors ) ? $saved_doctors : array();
		
		if( !empty( $post_id ) ) {
			if( in_array( $post_id, $wishlist ) ){ 
				$return = true;
			} else {
				$return = false;
			}
		}
		
		return $return;
	}
}

/**
 * Get account settings
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_account_settings' ) ) {
	function doctreat_get_account_settings($key='') {
		global $current_user;
		$settings = array(
			'doctors' => array(
				'_profile_blocked' 		=> esc_html__('Disable my account temporarily','doctreat'),
			),
			'hospitals' => array(
				'_profile_blocked' 		=> esc_html__('Disable my account temporarily','doctreat'),
			),
		);

		$settings	= apply_filters('doctreat_filters_account_settings',$settings);
		
		return !empty( $settings[$key] ) ? $settings[$key] : array();
	}
}

/**
 * Get leave reasons list
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_account_delete_reasons' ) ) {
	function doctreat_get_account_delete_reasons($key='') {
		global $current_user;
		$list = array(
			'not_satisfied' => esc_html__('No satisfied with the system','doctreat'),
			'support' 		=> esc_html__('Support is not good','doctreat'),
			'other' 		=> esc_html__('Others','doctreat'),
		);

		$reasons	= apply_filters('doctreat_filters_account_delete_reasons',$list);
		
		if( !empty( $key ) ){
			return !empty( $list[$key] ) ? $list[$key] : '';
		}
		
		return $reasons;
	}
}

/**
 * Get Search page
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_search_page' ) ) {
	function doctreat_get_search_page( $type='') {
		global $theme_settings;
		$search_settings	= !empty( $theme_settings['search_form'] ) ? $theme_settings['search_form'] : '';
		$search_page		= !empty( $theme_settings[$type] ) && !empty( $search_settings )  ? get_the_permalink($theme_settings[$type]) : '';
		
		return $search_page;
	}
}

/**
 * Get profile ID by post ID
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_pofile_ID_by_post' ) ) {
	function doctreat_get_pofile_ID_by_post( $post_id='') {
		$profile_id	= '';
		if( !empty( $post_id ) ){
			$author_id = get_post_field( 'post_author', $post_id );
			if( !empty( $author_id ) ){
				$profile_id	= doctreat_get_linked_profile_id($author_id);
			}
		}
		
		return $profile_id;
	}
}

/**
 * Get time
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_time' ) ) {
	function doctreat_get_time() {
		$time_settings = get_option( 'dc_time_settings' );
		
		$list	= array(		
					'0000'	=> esc_html__('12:00 am','doctreat'),
					'0100'	=> esc_html__('1:00 am','doctreat'),
					'0200'	=> esc_html__('2:00 am','doctreat'),
					'0300'	=> esc_html__('3:00 am','doctreat'),
					'0400'	=> esc_html__('4:00 am','doctreat'),
					'0500'	=> esc_html__('5:00 am','doctreat'),
					'0600'	=> esc_html__('6:00 am','doctreat'),
					'0700'	=> esc_html__('7:00 am','doctreat'),
					'0800'	=> esc_html__('8:00 am','doctreat'),
					'0900'	=> esc_html__('9:00 am','doctreat'),
					'1000'	=> esc_html__('10:00 am','doctreat'),
					'1100'	=> esc_html__('11:00 am','doctreat'),
					'1200'	=> esc_html__('12:00 am','doctreat'),
					'1300'	=> esc_html__('1:00 pm','doctreat'),
					'1400'	=> esc_html__('2:00 pm','doctreat'),
					'1500'	=> esc_html__('3:00 pm','doctreat'),
					'1600'	=> esc_html__('4:00 pm','doctreat'),
					'1700'	=> esc_html__('5:00 pm','doctreat'),
					'1800'	=> esc_html__('6:00 pm','doctreat'),
					'1900'	=> esc_html__('7:00 pm','doctreat'),
					'2000'	=> esc_html__('8:00 pm','doctreat'),
					'2100'	=> esc_html__('9:00 pm','doctreat'),
					'2200'	=> esc_html__('10:00 pm','doctreat'),
					'2300'	=> esc_html__('11:00 pm','doctreat'),
					'2400'	=> esc_html__('12:00 pm (night)','doctreat')			
				);
		
		$final_list	= !empty( $time_settings ) ? $time_settings : $list;
		$time_list 	= apply_filters('doctreat_filter_time',$final_list);
		return $time_list;
	}
}

/**
 * Get time slots
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_time_slots' ) ) {
	function doctreat_get_time_slots() {
		$slots_settings = get_option( 'dc_time_slots_settings' );
		
		$list	= array(		
					'1'	=> esc_html__('1 time slots','doctreat'),
					'2'	=> esc_html__('2 time slots','doctreat'),
					'3'	=> esc_html__('3 time slots','doctreat'),
					'4'	=> esc_html__('4 time slots','doctreat'),
					'5'	=> esc_html__('5 time slots','doctreat'),
					'6'	=> esc_html__('6 time slots','doctreat'),
					'7'	=> esc_html__('7 time slots','doctreat'),
					'8'	=> esc_html__('8 time slots','doctreat'),
					'9'	=> esc_html__('9 time slots','doctreat'),
					'10'	=> esc_html__('10 time slots','doctreat'),
					'11'	=> esc_html__('11 time slots','doctreat'),
					'12'	=> esc_html__('12 time slots','doctreat'),
					'13'	=> esc_html__('13 time slots','doctreat'),
					'14'	=> esc_html__('14 time slots','doctreat'),
					'15'	=> esc_html__('15 time slots','doctreat'),
					'16'	=> esc_html__('16 time slots','doctreat'),
					'17'	=> esc_html__('17 time slots','doctreat'),
					'18'	=> esc_html__('18 time slots','doctreat'),
					'19'	=> esc_html__('19 time slots','doctreat'),
					'20'	=> esc_html__('20 time slots','doctreat')
			
				);
		
		$final_list		= !empty( $slots_settings ) ? $slots_settings : $list;
		$slots_list 	= apply_filters('doctreat_filter_time_slots',$final_list);
		return $slots_list;
	}
}

/**
 * Get time slots
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_meeting_time' ) ) {
	function doctreat_get_meeting_time() {
		$slots_settings = get_option( 'dc_meeting_time_settings' );
		
		$list	= array(		
					''	=> esc_html__('Appointment Durations','doctreat'),
					'5'	=> esc_html__('5 minutes','doctreat'),
					'10'	=> esc_html__('10 minutes','doctreat'),
					'15'	=> esc_html__('15 minutes','doctreat'),
					'20'	=> esc_html__('20 minutes','doctreat'),
					'30'	=> esc_html__('30 minutes','doctreat'),
					'45'	=> esc_html__('45 minutes','doctreat'),
					'60'	=> esc_html__('1 hours','doctreat'),
					'90'	=> esc_html__('1 hours, 30 minutes','doctreat'),
					'120'	=> esc_html__('2 hours','doctreat'),
					'180'	=> esc_html__('3 hours','doctreat'),
					'240'	=> esc_html__('4 hours','doctreat'),
					'300'	=> esc_html__('5 hours','doctreat'),
					'360'	=> esc_html__('6 hours','doctreat'),
					'420'	=> esc_html__('7 hours','doctreat'),
					'480'	=> esc_html__('8 hours','doctreat')
				);
		
		$final_list		= !empty( $slots_settings ) ? $slots_settings : $list;
		$slots_list 	= apply_filters('doctreat_filter_meeting_time',$final_list);
		return $slots_list;
	}
}

/**
 * Get time padding
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_padding_time' ) ) {
	function doctreat_get_padding_time() {
		$slots_settings = get_option( 'dc_padding_time_settings' );
		
		$list	= array(		
					''	=> esc_html__('Appointment Intervals','doctreat'),
					'0'	=> esc_html__('0 minutes','doctreat'),
					'5'	=> esc_html__('5 minutes','doctreat'),
					'10'	=> esc_html__('10 minutes','doctreat'),
					'15'	=> esc_html__('15 minutes','doctreat'),
					'20'	=> esc_html__('20 minutes','doctreat'),
					'30'	=> esc_html__('30 minutes','doctreat'),
					'45'	=> esc_html__('45 minutes','doctreat'),
					'60'	=> esc_html__('1 hours','doctreat'),
					'90'	=> esc_html__('1 hours, 30 minutes','doctreat'),
					'120'	=> esc_html__('2 hours','doctreat'),
				);
		
		$final_list		= !empty( $slots_settings ) ? $slots_settings : $list;
		$slots_list 	= apply_filters('doctreat_filter_padding_time',$final_list);
		return $slots_list;
	}
}

/**
 * Get slots by day and post id
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_day_spaces' ) ) {
	function doctreat_get_day_spaces( $day = '', $post_id = '') {
		
		$li_data	= '';
		if( !empty( $day ) && !empty( $post_id ) ) {
			$time_format 		= get_option('time_format');
			$am_slots_data 		= get_post_meta( $post_id,'am_slots_data',true);
			$am_slots_data		= !empty( $am_slots_data ) ? $am_slots_data : array();
			$slots				= $am_slots_data[$day]['slots'];
			if( !empty( $slots ) ){
				foreach( $slots as $slot_key => $slot_val ) { 
					$slot_key_val = explode('-', $slot_key);
					$li_data .='<li>
					<a href="javascript:;" class="dc-spaces">
						<span>'.date($time_format, strtotime('2016-01-01' . $slot_key_val[0])).'</span>
						<span>'.esc_html('Spaces','doctreat').': '. esc_html( $slot_val["spaces"] ).'</span>
						<i class="lnr lnr-cross" data-id="'.intval( $post_id ).'" data-day="'.esc_attr( $day ).'" data-key="'.esc_attr( $slot_key ).'"></i>
					</a>
				</li>';
				}
			} 
		} 
		
		return $li_data;
	}
}

/**
 * Generate google link
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( !function_exists( 'doctreat_generate_GoogleLink' ) ) {
	function doctreat_generate_GoogleLink ($title,$from,$to,$description,$address){
		$start  = new DateTime($from);
		$end 	= new DateTime($to);
		$from	= $start->format('Ymd\THis');
		$to		= $end->format('Ymd\THis');
		$protolcol  = is_ssl() ? "https" : "http";
		$url 		= $protolcol.'://calendar.google.com/calendar/render?action=TEMPLATE';


		$url .= '&text='.urlencode($title);
		$url .= '&dates='.$from.'/'.$to;

		if ($description) {
			$url .= '&details='.urlencode($description);
		}

		if ($address) {
			$url .= '&location='.urlencode($address);
		}

		$url .= '&sprop=&sprop=name:';

		return $url;
	}
}

/**
 * Generate google link
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( !function_exists( 'doctreat_generate_YahooLink' ) ) {
	function doctreat_generate_YahooLink ($title,$from,$to,$description,$address){
		$start  = new DateTime($from);
		$end 	= new DateTime($to);
		$protolcol  = is_ssl() ? "https" : "http";
		$url 		= $protolcol.'://calendar.yahoo.com/?v=60&view=d&type=20';

		$url .= '&title='.urlencode($title);
		$url .= '&st='.$start->format('Ymd\THis\Z');
		$url .= '&dur='.date_diff($start, $end)->format('%H%I');

		if ($description) {
			$url .= '&desc='.urlencode($description);
		}

		if ($address) {
			$url .= '&in_loc='.urlencode($address);
		}

		return $url;
	}
}
/*
**
 * Get total earning for doctor
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_total_earning_doctor' ) ) {
    function doctreat_get_total_earning_doctor( $user_id='',$status='',$colum_name='') {
		global $wpdb;
		$table_name = $wpdb->prefix . "dc_earnings";
		
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
			if( !empty($user_id) && !empty($status) && !empty($colum_name) ) {
				$e_query	= $wpdb->prepare("SELECT sum(".$colum_name.") FROM ".$table_name." WHERE user_id = %d and ( status = %s || status = %s )",$user_id,$status[0],$status[1]);
				$total_earning	= $wpdb->get_var( $e_query );
			} else {
				$total_earning	= 0;
			}
		} else{
			$total_earning	= 0;
		}
		
		return $total_earning;
		
	}
}

/**
 * Get earning for doctreat
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_payments_doctreat' ) ) {
    function doctreat_get_payments_doctreat( $user_identity,$limit=6  ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "dc_payouts_history";
		$month		= date('m');
		
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
			if( !empty($user_identity) ) {
				$e_query	= $wpdb->prepare("SELECT * FROM $table_name where ( user_id =%d and status= 'completed' AND month=%d) ORDER BY id DESC LIMIT %d",$user_identity,$month,$limit);
				$payments = $wpdb->get_results( $e_query );
			} else {
				$payments	= 0;
			}
		} else{
			$payments	= 0;
		}
		
		return $payments;
		
	}
}

/**
 * Get sum payments for doctor
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_sum_payments_doctor' ) ) {
    function doctreat_get_sum_payments_doctor( $user_id='',$status='',$colum_name='') {
		global $wpdb;

		return $current_balance	= doctreat_get_total_earning_doctor($user_id,array('completed','processed'),'doctor_amount');
	}
}

/**
 * Get total earning for doctor
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_total_earning_doctor' ) ) {
    function doctreat_get_total_earning_doctor( $user_id='',$status='',$colum_name='') {
		global $wpdb;
		$table_name = $wpdb->prefix . "dc_earnings";
		
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
			if( !empty($user_id) && !empty($status) && !empty($colum_name) ) {
				$e_query	= $wpdb->prepare("SELECT sum(".$colum_name.") FROM ".$table_name." WHERE user_id = %d and ( status = %s || status = %s )",$user_id,$status[0],$status[1]);
				$total_earning	= $wpdb->get_var( $e_query );
			} else {
				$total_earning	= 0;
			}
		} else{
			$total_earning	= 0;
		}
		
		return $total_earning;
		
	}
}

/**
 * Get sum earning for doctor
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_get_sum_earning_doctor' ) ) {
    function doctreat_get_sum_earning_doctor( $user_id='',$status='',$colum_name='') {
		global $wpdb;
		$table_name = $wpdb->prefix . "dc_earnings";
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
			if( !empty($user_id) && !empty($status) && !empty($colum_name) ) {
				$e_query	= $wpdb->prepare("SELECT sum(".$colum_name.") FROM ".$table_name." WHERE user_id = %d and status = %s",$user_id,$status);
				$total_earning	= $wpdb->get_var( $e_query );
			} else {
				$total_earning	= 0;
			}
		} else{
			$total_earning	= 0;
		}
		
		return $total_earning;
		
	}
}

/**
 * Get prefix
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('dc_unique_increment')) {

    function dc_unique_increment($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}

/**
 * Get sum earning for payouts
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( ! function_exists( 'doctreat_sum_earning_doctor_payouts' ) ) {
    function doctreat_sum_earning_doctor_payouts( $status='',$colum_name='') {
		global $wpdb;
		$table_name = $wpdb->prefix . "dc_earnings";
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
			if( !empty($status) && !empty($colum_name) ) {
				$e_query	= $wpdb->prepare("SELECT user_id, sum(".$colum_name.") as total_amount FROM ".$table_name." WHERE status = %s GROUP BY user_id",$status);
				$total_earning	= $wpdb->get_results( $e_query );
			} else {
				$total_earning	= 0;
			}
		} else{
			$total_earning	= 0;
		}
		
		return $total_earning;
		
	}
}

/**
 * Update doctor earning
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( !function_exists( 'doctreat_update_earning' ) ) {

	function doctreat_update_earning( $where, $update, $table_name ) {
		
		global $wpdb;
		if( !empty($where) && !empty($update) && !empty($table_name) ) {
			$wpdb->update($wpdb->prefix.$table_name, $update, $where);
		} else {
			return false;
		}
	}
}

/**
 * theme setting options
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_theme_option')) {

    function doctreat_theme_option($option_type='system_booking_oncall') {
		global $theme_settings;
		$theme_option	= !empty($theme_settings[$option_type]) ? $theme_settings[$option_type] : '';
		return $theme_option;
    }
}

/**
 * System booking on call option
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_booking_oncall_option')) {

    function doctreat_get_booking_oncall_option($is_active='') {

		$payment_type				= doctreat_theme_option('payment_type');
		$system_booking_oncall		= doctreat_theme_option('system_booking_oncall');
		$booking_option				= (!empty($payment_type) && $payment_type === 'offline') && !empty($system_booking_oncall) ? $system_booking_oncall : '';
		
		if(!empty($booking_option) && empty($is_active)){
			$booking_option			= doctreat_theme_option('booking_system_contact');
		}

		return $booking_option;
    }
}

/**
 * System booking on call doctors option
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_booking_oncall_doctors_option')) {

    function doctreat_get_booking_oncall_doctors_option() {

		$payment_type				= doctreat_theme_option('payment_type');
		$booking_option				= 1;
		
		if( (!empty($payment_type) && $payment_type ==='offline' ) ){
			$system_booking_oncall		= doctreat_theme_option('system_booking_oncall');
			if( !empty($system_booking_oncall) ){
				$booking_option			= doctreat_theme_option('booking_system_contact');
				
				$booking_option = !empty($booking_option) && $booking_option === 'doctor' ? false : true;
			}
		}

		return $booking_option;
    }
}

if( !function_exists('doctreat_send_booking_message')){
	function doctreat_send_booking_message($post_id,$defult_message = ''){
		global $current_user;
		$patient_id		= get_post_field( 'post_author', $post_id );
		$date_format	= get_option('date_format');
		$current_time   = current_time('mysql');
		$gmt_time       = get_gmt_from_date($current_time);
		
		$doctor_profile_id	= get_post_meta( $post_id, '_doctor_id', true );
		$doctor_id			= doctreat_get_linked_profile_id($doctor_profile_id,'post');
		
		$patient_profile_id	= doctreat_get_linked_profile_id($patient_id);
		$patient_name		= doctreat_full_name($patient_profile_id);
		
		$sender_id		= $doctor_id;
		$receiver_id	= $patient_id;
		
		if( !empty($defult_message) ){
			if( $current_user->ID == $patient_id ){
				$sender_id		= $patient_id;
				$receiver_id	= $doctor_id;
			}
			
			$message		= $defult_message;
			
		} else {
			$appointment_date	= get_post_meta( $post_id, '_appointment_date', true );
			$appointment_date	= !empty($appointment_date) ?  date_i18n($date_format, strtotime($appointment_date)) : '';
		
			$message		= esc_html__('Hi %username%, your booking has been received on %date%','doctreat');
			$message 	= str_replace("%username%", $patient_name, $message); 
			$message 	= str_replace("%date%", $appointment_date, $message); 
		}

		$insert_data = array(
			'sender_id' 		=> $sender_id,
			'receiver_id' 		=> $receiver_id,
			'chat_message' 		=> $message,
			'status' 			=> 1,
			'timestamp' 		=> time(),
			'time_gmt' 			=> $gmt_time,
		);
				
		//plugin core active
		if (class_exists('ChatSystem')) {
			$msg_id = ChatSystem::getUsersThreadListData($doctor_id, $patient_id, 'insert_msg', $insert_data, '');
			if( !empty($defult_message) ){
				return $receiver_id;
			}
		}
	}
}

/**
 * Return order status text
 * PDF header
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */

if( !function_exists('doctreat_get_order_status_text') ){
	function doctreat_get_order_status_text($key = ''){
		$order_status	= wc_get_order_statuses();
		$order_status	= !empty($order_status[$key]) ? $order_status[$key] : '';
		return esc_html($order_status);
	}
}
/**
 * Return order payment gateways text
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if( !function_exists('doctreat_get_payment_gateways_text') ){
	function doctreat_get_payment_gateways_text($key = '') {
		global $woocommerce;
		$active_gateways = array();
		$gateways        = WC()->payment_gateways->payment_gateways();
		foreach ( $gateways as $id => $gateway ) {
			if ( isset( $gateway->enabled ) && 'yes' === $gateway->enabled && $id === $key ) {
				$active_gateways	= $gateway->title;
			}
		}
		return esc_html($active_gateways);
	}
}


/**
 * Render header
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */

if( !function_exists('doctreat_pdf')){
	function doctreat_pdf($booknig_id=''){
		global $theme_settings;
		$precription_details	= !empty($theme_settings['precription_details']) ? $theme_settings['precription_details'] : '';
		
		$border_image 		= get_template_directory().'/images/pdf/shape-02.png';
		$logo_image 		= !empty($theme_settings['pdf_logo']) ? $theme_settings['pdf_logo']['url'] : '';
		$prescription_id	= get_post_meta( $booknig_id, '_prescription_id', true );
		$doctor_id			= get_post_meta( $prescription_id, '_doctor_id', true );
		$doctor_profile_id	= !empty($doctor_id) ? doctreat_get_linked_profile_id($doctor_id) : '';

		$doctordata 		= get_userdata($doctor_id);
		$doctor_email		= !empty($doctordata->user_email) ? $doctordata->user_email : '';

		$doctor_name		= doctreat_full_name($doctor_profile_id);
		$doctor_name		= !empty($doctor_name) ? $doctor_name : '';
		$doctor_location	= !empty($theme_settings['doctor_location']) ? $theme_settings['doctor_location'] : '';
		$web_url			= '';
		
		if(!empty($doctor_location) && $doctor_location === 'hospitals'){
			$hospital_location_id	= get_post_meta( $prescription_id, '_hospital_id',  true);
			$mobile_number			= doctreat_get_post_meta( $hospital_location_id,'am_mobile_number' );
			$web_url				= doctreat_get_post_meta( $hospital_location_id,'am_web_url' );
			$hospital_id			= !empty($hospital_location_id) ?  doctreat_get_linked_profile_id($hospital_location_id,'post') : '';
			$user_details			= !empty($hospital_id) ?  get_userdata($hospital_id) : '';
		}else{
			$hospital_location_id	= get_post_meta( $doctor_profile_id, '_doctor_location',  true);
			$mobile_number			= doctreat_get_post_meta( $doctor_profile_id,'am_mobile_number' );
			$user_details			= !empty($doctor_id) ?  get_userdata($doctor_id) : '';
		}

		$email_info				= !empty($user_details->user_email) ? $user_details->user_email : '';
		$prescription_details	= get_post_meta( $prescription_id, '_detail', true );
		$prescription_details	= !empty($prescription_details) ? $prescription_details : array();

		$medicine				= !empty($prescription_details['_medicine']) ? $prescription_details['_medicine'] : array();

		$hospital_location_id	= !empty($hospital_location_id) ? $hospital_location_id : '';
		$location_title			= !empty($hospital_location_id) ? get_the_title($hospital_location_id) : '';

		$address		= get_post_meta( $hospital_location_id , '_address',true );
		$address		= !empty( $address ) ? $address : '';

		$laboratory_tests_obj_list 	= get_the_terms( $prescription_id, 'laboratory_tests' );
		$laboratory_tests_name		= !empty($laboratory_tests_obj_list) ? join(', ', wp_list_pluck($laboratory_tests_obj_list, 'name')) : '';
		
		if( !empty($precription_details) && $precription_details === 'doctor' ){
			$attachment_id 			= get_post_thumbnail_id($doctor_profile_id);
			$image_url 				= !empty( $attachment_id ) ? wp_get_attachment_url( $attachment_id, 'doctreat_doctors_type', true ) : '';
			$logo_image				= !empty($image_url) ? wp_make_link_relative($image_url) : $logo_image;
			$logo_image				= get_attached_file($attachment_id);
			
			$location_title			= doctreat_get_username($doctor_id);
			$address				= get_post_meta( $doctor_profile_id , '_address',true );
			$address				= !empty( $address ) ? $address : '';
			$web_url				= doctreat_get_post_meta( $doctor_profile_id,'am_web_url' );
			$mobile_number			= doctreat_get_post_meta( $doctor_profile_id,'am_mobile_number' );
			$user_details			= !empty($doctor_id) ?  get_userdata($doctor_id) : '';
			$email_info				= !empty($user_details->user_email) ? $user_details->user_email : '';
		}else{
			if( !empty($hospital_location_id) && has_post_thumbnail($hospital_location_id) ){
				$attachment_id 			= get_post_thumbnail_id($hospital_location_id);
				$image_url 				= !empty( $attachment_id ) ? wp_get_attachment_url( $attachment_id, 'doctreat_doctors_type', true ) : '';
				$logo_image				= !empty($image_url) ? wp_make_link_relative($image_url) : $logo_image;
				$logo_image				= get_attached_file($attachment_id);
			}
		}
		
		$html = '<html>
		<head>
			<style>
				@page {
					margin: 10px 0px 50px 0px;
				}
				*{box-sizing: border-box;}
				header {
					top: -30px;
					left: 0px;
					right: 0px;
					height: 180px;
					position:absolute;
					border-radius:5px;
					font-family: sans-serif;
					background: url('.$border_image.');
					background-position: top;
					background-size: 100% 100%;
					background-repeat: no-repeat;
				}
				table { border-collapse: collapse; }
			</style>
		</head>
		<body style="font-family: sans-serif; margin-top:0;padding-top:30px;position:relative;">
			<header><img src="'.get_template_directory().'/images/pdf/shape-02.png" style=" display:block; position:absolute;top:0px;right:0;width:100%; height:180px;"></header>
			<div style="width:100%; display: inline-block; text-align:center; font-family: sans-serif;padding:0 0 30px;">
				<table style="width:96%; margin:0 auto 0;">
					<tr style="text-align:left;">
						<td width="70%">';

							if( !empty($logo_image)){
								$html	.= '<h1 style="font-size: 26px;line-height: 26px;margin: 0 0 10px; font-weight: 500; color: #3d4461;" ><img style="max-width:125px;border-radius:5px;" src="'.$logo_image.'" ></h1>';
							}
			
							if( !empty($location_title) ){
								$html	.= '<h4 style="font-size: 1.3em;line-height: 1.2;">'.$location_title.'</h4>';
							}
				
							if( !empty($address) ){
								$html	.= '<span style="margin-top: 6px; line-height: 20px; font-size: 14px; display: block;text-decoration: none;">'.$address.'</span>';
							}
				
							if( !empty($location) ){
								$html	.= '<span style="margin-top: 0px; line-height: 20px; font-size: 14px; display: block;text-decoration: none;">'.$location.'</span>';
							}
				
							if( !empty($mobile_number) ){
								$html	.= '<a style="margin-top: 6px; line-height: 20px; font-size: 14px; display: block;color:#3fabf3;text-decoration: none;" href="tel:+'.$mobile_number.'">+'.$mobile_number.'</a>';
							}
				
							if( !empty($email_info) ){
								$html	.= '<a style="margin-top: 6px; line-height: 20px; font-size: 14px; display: block;color:#3fabf3;text-decoration: none;" href="mailto:'.is_email($email_info).'">'.is_email($email_info).'</a>	';
							}
				
							if( !empty($web_url) ){
								$html	.= '<a style="margin-top: 6px; line-height: 20px; font-size: 14px; display: block;color:#3fabf3;text-decoration: none;" href="'.esc_url($web_url).'">'.esc_url($web_url).'</a>	';
							}
							
						$html	.= '</td>
					</tr>
				</table>';
				$html	.='<table style="width:96%; margin:20px auto 0;">
								<tr style="text-align:left;">';
									if( !empty($prescription_details['_patient_name'])){
										$html	.= '<td width="100%" style="text-align:left;box-sizing: border-box;"><span style="display: inline-block; padding: 7px 15px 7px; line-height: 1.3em;width: 100%; font-size: 14px; border-bottom: 1px solid #ddd; margin: 0 1% 10px;">'.esc_html__('Name:','doctreat').' '.$prescription_details['_patient_name'].'</span></td>';
									}

									if( !empty($prescription_details['_age'])){

										$html		.= '<td width="100%" style="text-align:left;box-sizing: border-box;"><span style="display: inline-block; padding: 7px 15px 7px; line-height: 1.3em;width: 100%; font-size: 14px; border-bottom: 1px solid #ddd; margin: 0 1% 10px;">'.esc_html__('Age:','doctreat').' '.$prescription_details['_age'].' '.esc_html__('year','doctreat').'</span></td>';

									}
		
					$html		.='</tr>';
				
				if( !empty($prescription_details['_gender']) || !empty($prescription_details['_address'])){
					$html		.='<tr style="text-align:left;">';
						if( !empty($prescription_details['_gender'])){
							$gender_title	= $prescription_details['_gender'] === 'female' ? esc_html__('Female','doctreat') : esc_html__('Male','doctreat');
							$html		.= '<td width="100%" style="text-align:left;box-sizing: border-box;"><span style="display: inline-block; padding: 7px 15px 7px; line-height: 1.3em;width: 100%; font-size: 14px; border-bottom: 1px solid #ddd; margin: 0 1% 10px;">'.esc_html__('Gender:','doctreat').' '.$gender_title.'</span></td>';
						}

						if( !empty($prescription_details['_address'])){
							$html		.= '<td width="100%" style="text-align:left;box-sizing: border-box;"><span style="display: inline-block; padding: 7px 15px 7px; line-height: 1.3em;width: 100%; font-size: 14px; border-bottom: 1px solid #ddd; margin: 0 1% 10px;">'.esc_html__('Address:','doctreat').' '.$prescription_details['_address'].'</span></td>';

						}

					$html		.='</tr>';
				}
				
				if( !empty($prescription_details['_marital_status']) || !empty($prescription_details['_childhood_illness'])){
					$html		.='<tr style="text-align:left;">';
					if( !empty($prescription_details['_marital_status'])){
						$term 			= !empty($prescription_details['_marital_status']) ? get_term( $prescription_details['_marital_status'], 'marital_status' ) : '';
						$status_name	= !empty($term->name) ? $term->name : '';

						$html		.= '<td width="100%" style="text-align:left;box-sizing: border-box;"><span style="display: inline-block; padding: 7px 15px 7px; line-height: 1.3em;width: 100%; font-size: 14px; border-bottom: 1px solid #ddd; margin: 0 1% 10px;">'.esc_html__('Marital status:','doctreat').' '.$status_name.'</span></td>';

					}

					if( !empty($prescription_details['_childhood_illness'])){
						$child_illness		= '';
						$counter_illness	= 0;
						$total_illness		= count($prescription_details['_childhood_illness']);
						foreach($prescription_details['_childhood_illness'] as $illness){
							$counter_illness++;
							$term 			= !empty($illness) ? get_term_by('id', $illness, 'childhood_illness') : '';
							$illness_name	= !empty($term->name) ? $term->name : '';
							$child_illness	.= $total_illness > $counter_illness ? $illness_name.',' : $illness_name;
						}

						$html		.= '<td width="100%" style="text-align:left;box-sizing: border-box;"><span style="display: inline-block; padding: 7px 15px 7px; line-height: 1.3em;width: 100%; font-size: 14px; border-bottom: 1px solid #ddd; margin: 0 1% 10px;">'.esc_html__('Child illness:','doctreat').' '.esc_html($child_illness).'</span></td>';

					}

					$html		.='</tr>';
				}
				
				if( !empty($prescription_details['_diseases']) || !empty( $prescription_details['_vital_signs'] )){
					$html		.='<tr style="text-align:left;">';

					if( !empty($prescription_details['_diseases'])){
						$diseases_name		= '';
						$counter_diseases	= 0;
						$total_diseases		= count($prescription_details['_diseases']);

						foreach($prescription_details['_diseases'] as $diseases){
							$counter_diseases++;
							$term 			= !empty($diseases) ? get_term_by('id', $diseases, 'diseases') : '';
							$dis_name		= !empty($term->name) ? $term->name : '';
							$diseases_name	.= $total_diseases > $counter_diseases ? $dis_name.',' : $dis_name;
						}


						$html		.= '<td width="100%" style="text-align:left;box-sizing: border-box;"><span style="display: inline-block; padding: 7px 15px 7px; line-height: 1.3em;width: 100%; font-size: 14px; border-bottom: 1px solid #ddd; margin: 0 1% 10px;">'.esc_html__('Diseases:','doctreat').' '.esc_html($diseases_name).'</span></td>';

					}


					if( !empty( $prescription_details['_vital_signs'] ) ){

						$counter_sign		= 0;
						$vital_signs_name	= '';
						$total_sign			= count($prescription_details['_vital_signs']);
						foreach($prescription_details['_vital_signs'] as $key => $val ) { 
							$counter_sign++;
							if( !empty($val) ){
								$term 				= !empty($key) ? get_term_by('id', $key, 'vital_signs') : '';
								$sing_val			= !empty($val['value']) ? $val['value'] : '';
								$vital_signs		= !empty($term->name) ? $term->name. '('.$sing_val.')' : '';
								$vital_signs_name	.= $total_diseases > $counter_diseases ? $vital_signs.',' : $vital_signs;

							}
						}

						$html		.= '<td width="100%" style="text-align:left;box-sizing: border-box;"><span style="display: inline-block; padding: 7px 15px 7px; line-height: 1.3em;width: 100%; font-size: 14px; border-bottom: 1px solid #ddd; margin: 0 1% 10px;">'.esc_html__('Vital signs:','doctreat').' '.esc_html($vital_signs_name).'</span></td>';

					}

					$html	.='</tr>';
				}
					
				$html	.='</table>';

				if( !empty($prescription_details['_medical_history'] ) ){
					$html	.= '<em style="font-size: 20px; line-height: 1.3em; color: #3d4461; display: block; width: 95%; margin: 20px auto; text-align: left; font-style: normal;">'.esc_html__('Diagnosis:','doctreat').'</em>';
					$html	.= '<p style="text-align:left; font-size: 14px; line-height:1.5em; width: 95%; margin:0 auto;">'.esc_html($prescription_details['_medical_history']).'</p>';
				}
		
				if( !empty( $medicine ) ){
					$html	.= '<em style="font-size: 20px; line-height: 1.3em; color: #3d4461; display: block; width: 95%; margin: 20px auto; text-align: left; font-style: normal;">'.esc_html__('Medications:','doctreat').'</em>';
					$html	.= '<table style="width: 95%; margin: 0 auto;font-family: sans-serif;">';
					$html .= '<thead>
						<tr style="text-align: left; border-radius:5px 0 0;">
							<th style="width:10%; padding: 15px 20px;background: #f5f5f5; font-size:14px;">'.esc_html__('Name','doctreat').'</th>
							<th style="width:10%; padding: 15px 20px;background: #f5f5f5; font-size:14px;">'.esc_html__('Types','doctreat').'</th>
							<th style="width:15%; padding: 15px 20px;background: #f5f5f5; font-size:14px;">'.esc_html__('Duration','doctreat').'</th>
							<th style="width:15%; padding: 15px 20px;background: #f5f5f5; font-size:14px;">'.esc_html__('Usage','doctreat').'</th>
							<th style="width:25%; padding: 15px 20px;background: #f5f5f5; font-size:14px;">'.esc_html__('Details','doctreat').'</th>
						</tr>
					</thead>
					<tbody>';
					foreach($medicine as $vals ) { 
						$name					= !empty($vals['name']) ? esc_html($vals['name']) : '';
						$medicine_duration 		= !empty($vals['medicine_duration']) ? get_term_by('id', $vals['medicine_duration'], 'medicine_duration',ARRAY_A) : '';
						$medicine_duration		= !empty($medicine_duration['name']) ? $medicine_duration['name'] : '';
						$medicine_types 		= !empty($vals['medicine_types']) ? doctreat_get_term_by_type('id', $vals['medicine_types'], 'medicine_types','name') : '';
						$medicine_types			= !empty($medicine_types) ? $medicine_types : '';

						$medicine_usage 			= !empty($vals['medicine_usage']) ? doctreat_get_term_by_type('id', $vals['medicine_usage'], 'medicine_usage','name') : '';
						$medicine_usage				= !empty($medicine_usage) ? $medicine_usage : '';

						$detail						= !empty($vals['detail']) ? esc_html($vals['detail']) : '';

						$html	.= '<tr>';
						if( !empty($vals) ){
								$html	.= '<td style="padding: 15px 20px; border-top: 1px solid #e2e2e2; font-size:14px;">'.esc_html($name).'</td>';
								$html	.= '<td style="padding: 15px 20px; border-top: 1px solid #e2e2e2; font-size:14px;">'.esc_html($medicine_types).'</td>';
								$html	.= '<td style="padding: 15px 20px; border-top: 1px solid #e2e2e2; font-size:14px;">'.esc_html($medicine_duration).'</td>';
								$html	.= '<td style="padding: 15px 20px; border-top: 1px solid #e2e2e2; font-size:14px;">'.esc_html($medicine_usage).'</td>';
								$html	.= '<td style="padding: 15px 20px; border-top: 1px solid #e2e2e2; font-size:14px;">'.esc_html($detail).'</td>';
							}			
						$html	.= '</tr>';
					}
					
					$html	.= '</table>';
				}
		
				if( !empty( $laboratory_tests_name ) ){

					$html	.= '<em style="font-size: 20px; line-height: 1.3em; color: #3d4461; display: block; width: 95%; margin: 20px auto; text-align: left; font-style: normal;">'.esc_html__('Required laboratory tests*','doctreat').'</em>';
					$html		.= '<p style="text-align:left; font-size: 14px; line-height:1.5em; width: 95%; margin:0 auto;">'.esc_html($laboratory_tests_name).'</p>';
					
				}
		
			$html	.='</div>

			
			<footer style="text-align: center;margin-top:0;padding: 0 0 0;position:fixed; bottom:0;padding:0; min-height:100px;">
				<img src="'.get_template_directory().'/images/pdf/shape-01.png" style=" display:block; position:absolute;bottom:-60px;left:0;width:100%; height:150px;">
			</footer>';
			$html .= '</body></html>';
		return $html;
	}
	add_filter('doctreat_pdf', 'doctreat_pdf',10,1);
}

/**
 * Check Video URL
*/
if(!function_exists('doctreat_check_video_url')) {
    function doctreat_check_video_url($url){
		$return 	= false;
		$video_platform 	= array('youtube','vimeo','dailymotion','yahoo','bliptv','veoh','viddler');
		$video_platform		= apply_filters('doctreat_filter_video_url',$video_platform);
		if (filter_var($url, FILTER_VALIDATE_URL)) {
			foreach ($video_platform as $val) {
				if (strpos($url, $val) !== FALSE) { 
					$return = true;
				}
			}
		}
		return $return;
	}
}