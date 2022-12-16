<?php
/**
 * Packages options
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_packages_option')) {
	add_action( 'init', 'doctreat_packages_option' );

	function doctreat_packages_option(){
		add_filter( 'woocommerce_cod_process_payment_order_status','doctreat_update_order_status', 10, 2 );
		add_filter( 'woocommerce_cheque_process_payment_order_status','doctreat_update_order_status', 10, 2 );
		add_filter( 'woocommerce_bacs_process_payment_order_status','doctreat_update_order_status', 10, 2 );
			
		if( is_admin() ){
			add_action( 'woocommerce_order_status_completed','doctreat_on_hold_payment_complete',10,1 );
		}
	}
}

/**
 * On-hold Package Complete order
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_on_hold_payment_complete')) {
    function doctreat_on_hold_payment_complete($order_id) {
		$order 					= wc_get_order($order_id);
		$user 					= $order->get_user();
        $items 					= $order->get_items();
		
		$admin_updated 	= get_post_meta($order_id,'_admin_updated',true);
		$admin_updated	= !empty($admin_updated) ? $admin_updated : 'no';

		if ( !empty($admin_updated) && $admin_updated === 'yes' ){
			return;
		}

        foreach ($items as $key => $item) {
            $product_id 	= $item['product_id'];
			$quantity 		= $item->get_quantity();
			$quantity		= !empty( $item['qty'] ) ?  $item['qty'] : 1;
			
            if ($user) {
				$payment_type = wc_get_order_item_meta( $key, 'payment_type', true );
				if( !empty( $payment_type ) && $payment_type === 'subscription' ) {
					doctreat_update_package_data( $product_id ,$user->ID,$order_id,$quantity );
				}else if( !empty( $payment_type ) && $payment_type === 'bookings' ) {
					doctreat_update_bookings_data( $order_id ,$user);
				}
				
				update_post_meta($order_id,'_admin_updated','yes');
            }
        }

    }
}

/**
 * PayPal Order process
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */

if (!function_exists('doctreat_update_order_data')) {
	function doctreat_update_order_data( $order_id ){
		global $product,$woocommerce,$wpdb,$theme_settings;
		$booking_id 	= get_post_meta($order_id,'_booking_id',true);
		$admin_updated 	= get_post_meta($order_id,'_admin_updated',true);
		$admin_updated	= !empty($admin_updated) ? $admin_updated : 'no';

		if ( !empty($admin_updated) && $admin_updated === 'yes' ){
			return;
		}

		$current_date 	= current_time('mysql');
		$gmt_time		= current_time( 'mysql', 1 );
		$time_format 	= get_option('time_format');
		$date_formate	= get_option('date_format');
		$order 			= new WC_Order( $order_id );
		$items 			= $order->get_items();
		$order_detail	= array();
		$post_meta		= get_post_meta($booking_id,'_am_booking',true);
		$emailData		= array();
		$earning		= array();
		
		if( !empty( $items ) ) {
			$counter	= 0;
			foreach( $items as $key => $order_item ){
				$counter++;
				$order_detail					= wc_get_order_item_meta( $key, 'cus_woo_product_data', true );
				$earning['admin_amount'] 		= wc_get_order_item_meta( $key, 'admin_shares', true );
				$earning['doctor_amount']		= wc_get_order_item_meta( $key, 'doctors_shares', true );
			}

			$update_post['ID'] 			= $booking_id;
			$update_post['post_status'] = 'publish';
			wp_update_post( $update_post );
			
			if( !empty( $user->ID ) ) {
				$profile_id	= doctreat_get_linked_profile_id($user->ID);
				$user_name	= doctreat_full_name($profile_id);
				$auther_id	= $user->ID;
				$email		= get_userdata( $user->ID )->user_email;
			}else{
				$user_name	= !empty( $post_meta['_user_details']['bk_username'] ) ? $post_meta['_user_details']['bk_username'] : '';
				$auther_id	= 1;
				$email		= !empty( $post_meta['_user_details']['bk_email'] ) ? $post_meta['_user_details']['bk_email'] : '';
			}
			
			$contents		= !empty( $order_detail['content'] ) ? $order_detail['content'] : '';
			
			if( !empty( $order_detail ) ){
				
				$doctor_id	= get_post_meta($booking_id,'_doctor_id',true);;
				$auther_id	= 1;
				
				$earning['order_id']		= $order_id;
				$earning['process_date'] 	= date('Y-m-d H:i:s', strtotime($current_date));
				$earning['date_gmt'] 		= date('Y-m-d H:i:s', strtotime($gmt_time));
				$earning['year'] 			= date('Y', strtotime($current_date));
				$earning['month'] 			= date('m', strtotime($current_date));
				$earning['timestamp'] 		= strtotime($current_date);
				$earning['status'] 			= 'completed';
				$earning['amount']			= $order_detail['price'];
				
				if( function_exists('doctreat_get_current_currency') ) {
					$currency					= doctreat_get_current_currency();
					$earning['currency_symbol']	= $currency['symbol'];
				} else {
					$earning['currency_symbol']	= '$';
				}

				
				$doctor_name	= doctreat_full_name( $doctor_id );
				$doctor_location	= !empty($theme_settings['doctor_location']) ? $theme_settings['doctor_location'] : '';
				if(!empty($doctor_location) && $doctor_location === 'hospitals'){
					$hospital_id				= get_post_meta($order_detail['hospital'],'hospital_id',true);
				} else {
					$hospital_id				= $order_detail['hospital'];
				}
				
				if( !empty( $booking_id ) ) {
					$slot_key_val 	= explode('-', $post_meta['_slots']);
					$start_time		= date($time_format, strtotime('2016-01-01' . $slot_key_val[0]));
					$end_time		= date($time_format, strtotime('2016-01-01' . $slot_key_val[1]));
					
					$start_time		= !empty( $start_time ) ? $start_time : '';
					$end_time		= !empty( $end_time ) ? $end_time : '';
					
					$earning['user_id']			= doctreat_get_linked_profile_id( $doctor_id,'post' );
					$earning['booking_id']		= $booking_id;
					
					if (class_exists('Doctreat_Email_helper')) {
						$emailData['user_name']		= $user_name;		
						$emailData['doctor_name']	= doctreat_full_name($doctor_id);
						$emailData['doctor_link']	= get_the_permalink($doctor_id);
						$emailData['hospital_name']	= doctreat_full_name($hospital_id);
						$emailData['hospital_link']	= get_the_permalink($hospital_id);
						
						$emailData['appointment_date']	= !empty($post_meta['_appointment_date']) ? date($date_formate,strtotime($post_meta['_appointment_date'])) : '';
						$emailData['appointment_time']	= $start_time.' '.esc_html__('to','doctreat').' '.$end_time;
						$emailData['price']				= doctreat_price_format($post_meta['_price'],'return');
						$emailData['consultant_fee']	= doctreat_price_format($post_meta['_consultant_fee'],'return');
						$emailData['description']		= $contents;

						if (class_exists('DoctreatBookingNotify')) {
                                                    
	                        //LiveSmart integration starts
	                        if (get_option('livesmart_server_url')) {
	                            if(!isset($_SESSION)){ session_start(array('livesmart_content_visitor', 'livesmart_content'));}
	                            $lsvTime = !empty($post_meta['_slots']) ? explode('-',$post_meta['_slots']) : array();
	                            $lsv_visitor_url = $doctor_id.$post_meta['_appointment_date'].$lsvTime[0];
	                            $lsv_url = get_option('livesmart_server_url');
	                            $_SESSION['livesmart_content_visitor'] = '<br/>' . $lsv_url . $lsv_visitor_url;
	                            
	                            $lsv_agent_url = $lsv_visitor_url.'_a';
	                            $lsvContent = '<br/><br/>Meeting agent URL: &#13;&#10;' . $lsv_url . $lsv_agent_url . '&#13;&#10;<br/>Meeting visitor URL: &#13;&#10;' . $lsv_url . $lsv_visitor_url;
	                            $_SESSION['livesmart_content'] = $lsvContent;
	                            
	                            
	                        }
	                        //LiveSmart integration ends
                    
                    
							$email_helper				= new DoctreatBookingNotify();

							$emailData['email']			= $email;
							$email_helper->send_request_email($emailData);

							$doctor_info				= get_userdata($earning['user_id']);
							$emailData['email']			= $doctor_info->user_email;
							$email_helper->send_doctor_email($emailData);
							
						}
					}
					
					//update message 
					if( function_exists('doctreat_send_booking_message') ){
						doctreat_send_booking_message($booking_id);
					}
					
					//update earning
					$table_name = $wpdb->prefix . "dc_earnings";
					
					if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name ) {
						$tablename = $wpdb->prefix.'dc_earnings';
						$wpdb->insert( $tablename,$earning);
					}

					$order->update_status( 'completed' );
					$order->save();
					
					update_post_meta($booking_id,'_admin_updated','yes');
				}
				
			}
		}
	}
}

/**
 * PayPal Order process
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */

if (!function_exists('doctreat_paypal_payment_complete_order_status')) {
	//add_filter('woocommerce_payment_complete_order_status', 'doctreat_paypal_payment_complete_order_status', 10, 2 );
	function doctreat_paypal_payment_complete_order_status( $order_status, $order_id ){
		$order = wc_get_order( $order_id );
		if( $order->get_payment_method() === 'paypal' ){
			$order_status = 'completed';
		}

		return $order_status;
	}
}


/**
 * cahnge status on cash on delivery 
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */

if (!function_exists('doctreat_update_order_status')) {

	function doctreat_update_order_status( $status,$order  ) {
		return 'on-hold';
	}
}

/**
 * check order status
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */

if (!function_exists('doctreat_check_order_option')) {

	function doctreat_check_order_option( $order_id='' ) {
		$offline_package	= doctreat_theme_option('payment_type');
		$payment_method		= get_post_meta($order_id,'_payment_method',true);
		$default_payment	= array();
		
		if( function_exists('doctreat_get_offline_method_array') ){
			$default_payment	= doctreat_get_offline_method_array();
		}
		
		$access				= true;

		$offline_status	= get_post_meta( $order_id, '_offline_payment', true );

		if ( !empty($offline_status) || ( !empty($offline_package) && $offline_package === 'offline' && !empty($payment_method) && in_array($payment_method, $default_payment) )){
			$access			= false;
		} 

		return $access;
	}
	add_filter('doctreat_check_order_option', 'doctreat_check_order_option', 10, 1);
}


/**
 * Complete order
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_payment_complete')) {
    add_action('woocommerce_payment_complete', 'doctreat_payment_complete',10,1);
    function doctreat_payment_complete($order_id) {
		$order 					= wc_get_order($order_id);
		$user 					= $order->get_user();
        $items 					= $order->get_items();
		
        foreach ($items as $key => $item) {
            $product_id 	= $item['product_id'];
			$quantity 		= $item->get_quantity();
            if ($user) {
				$payment_type = wc_get_order_item_meta( $key, 'payment_type', true );
				if( !empty( $payment_type ) && $payment_type === 'bookings') {
					doctreat_update_bookings_data( $order_id ,$user);
				} else if( !empty( $payment_type ) && $payment_type === 'subscription' ) {
					doctreat_update_package_data( $product_id ,$user->ID,$order_id,$quantity );
				}
            }
        }
    }
}


/**
 * Update User Booking
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_update_bookings_data')) {
    function doctreat_update_bookings_data( $order_id,$user ) {
		global $product,$woocommerce,$wpdb,$theme_settings;
		$current_date 	= current_time('mysql');
		$gmt_time		= current_time( 'mysql', 1 );
		$time_format 	= get_option('time_format');
		$date_formate	= get_option('date_format');
		$order 			= new WC_Order( $order_id );
		$items 			= $order->get_items();
		$order_detail	= array();
		$post_meta		= array();
		$emailData		= array();
		$earning		= array();
		$payment_type	= !empty( $theme_settings['payment_type'] ) ? $theme_settings['payment_type'] : '';
		
		if( !empty( $items ) ) {
			$counter	= 0;
			foreach( $items as $key => $order_item ){
				$counter++;
				$order_detail					= wc_get_order_item_meta( $key, 'cus_woo_product_data', true );
				$earning['admin_amount'] 		= wc_get_order_item_meta( $key, 'admin_shares', true );
				$earning['doctor_amount']		= wc_get_order_item_meta( $key, 'doctors_shares', true );
				$quantity 						= $order_item->get_quantity();
			}
			
			if( !empty( $order_detail ) ){
				
				$doctor_id	= !empty( $order_detail['doctor_id'] ) ? intval( $order_detail['doctor_id'] ) : '';
				$auther_id	= 1;
				
				$earning['order_id']		= $order_id;
				$earning['process_date'] 	= date('Y-m-d H:i:s', strtotime($current_date));
				$earning['date_gmt'] 		= date('Y-m-d H:i:s', strtotime($gmt_time));
				$earning['year'] 			= date('Y', strtotime($current_date));
				$earning['month'] 			= date('m', strtotime($current_date));
				$earning['timestamp'] 		= strtotime($current_date);
				$earning['status'] 			= 'completed';
				
				
				$post_meta['_price']		= !empty( $order_detail['price'] ) ? $order_detail['price'] : '';
				$earning['amount']			= $post_meta['_price'];
				
				if( function_exists('doctreat_get_current_currency') ) {
					$currency					= doctreat_get_current_currency();
					$earning['currency_symbol']	= $currency['symbol'];
				} else {
					$earning['currency_symbol']	= '$';
				}
				
				if( !empty( $user->ID ) ) {
					$profile_id	= doctreat_get_linked_profile_id($user->ID);
					$user_name	= doctreat_full_name($profile_id);
					$auther_id	= $user->ID;
					$email		= get_userdata( $user->ID )->user_email;
					$booking_post['post_author']	= $auther_id;
				} else {
					$post_meta['_user_details']['user_type']	= !empty( $order_detail['user_type'] ) ? $order_detail['user_type'] : '';
					$post_meta['_user_details']['full_name']	= !empty( $order_detail['full_name'] ) ? $order_detail['full_name'] : '';
					$post_meta['_user_details']['phone_number']	= !empty( $order_detail['phone_number'] ) ? $order_detail['phone_number'] : '';
					$post_meta['_user_details']['email']		= !empty( $order_detail['email'] ) ? $order_detail['email'] : '';
				}
				
				
				$post_meta['_user_details']['bk_email']		= !empty( $order_detail['bk_email'] ) ? $order_detail['bk_email'] : '';
				$post_meta['_user_details']['bk_phone']		= !empty( $order_detail['bk_phone'] ) ? $order_detail['bk_phone'] : '';
				$post_meta['_user_details']['bk_username']	= !empty( $order_detail['other_name'] ) ? $order_detail['other_name'] : '';
				
				$doctor_name	= doctreat_full_name( $doctor_id );
				$post_title		= !empty( $theme_settings['appointment_prefix'] ) ? $theme_settings['appointment_prefix'] : esc_html__('APP#','doctreat');
				$contents		= !empty( $order_detail['content'] ) ? $order_detail['content'] : '';
                if(!isset($_SESSION)){ session_start(array('livesmart_content', 'livesmart_content_visitor'));}
                $contents		.= @$_SESSION['livesmart_content'];
				$booking_post 	= array(
									'post_title'    => wp_strip_all_tags( $post_title ).$order_id,
									'post_status'   => 'publish',
									'post_author'   => intval($auther_id),
									'post_type'     => 'booking',
									'post_content'	=> $contents
								);
				
				$booking_id    			= wp_insert_post( $booking_post );
				$post_meta['myself']	= !empty( $order_detail['myself'] ) ? $order_detail['myself'] : '';

				$post_meta['_with_patient']['other_name']	= !empty( $order_detail['other_name'] ) ? $order_detail['other_name'] : '';
				$post_meta['_with_patient']['relation']		= !empty( $order_detail['relation'] ) ? $order_detail['relation'] : '';
				$post_meta['_consultant_fee']	= !empty( $order_detail['consultant_fee'] ) ? $order_detail['consultant_fee'] : 0;
				$booking_service				= !empty( $order_detail['service'] ) ? $order_detail['service'] : array();
				
				$am_specialities 		= doctreat_get_post_meta( $doctor_id,'am_specialities');
				$am_specialities		= !empty( $am_specialities ) ? $am_specialities : array();
				
				$update_services		= array();
				
				if( !empty($booking_service) ){
					
					foreach($booking_service as $key => $service_single){
						if( !empty( $service_single ) ){
							foreach( $service_single as $service ){
								$single_price		= !empty( $am_specialities[$key][$service]['price'] ) ?  $am_specialities[$key][$service]['price'] : 0;
								$single_price		= !empty( $single_price ) ? $single_price : 0;
								$update_services[$key][$service]	= $single_price;
							}
						}
					}
				}

				
				$post_meta['_services']			= !empty($update_services) ? $update_services : array();
				
				$post_meta['_appointment_date']	= !empty( $order_detail['appointment_date'] ) ? $order_detail['appointment_date'] : '';
				
				
				$post_meta['_slots']			= !empty( $order_detail['slots'] ) ? $order_detail['slots'] : '';
				$post_meta['_hospital_id']		= !empty( $order_detail['hospital'] ) ? $order_detail['hospital'] : '';
				
				$doctor_location	= !empty($theme_settings['doctor_location']) ? $theme_settings['doctor_location'] : '';
				if(!empty($doctor_location) && $doctor_location === 'hospitals'){
					$hospital_id				= get_post_meta($post_meta['_hospital_id'],'hospital_id',true);
				} else {
					$hospital_id				= $post_meta['_hospital_id'];
				}
				
				if( !empty( $booking_id ) ) {
					
					update_post_meta($booking_id,'bk_email',$post_meta['_user_details']['bk_email'] );
					update_post_meta($booking_id,'bk_phone',$post_meta['_user_details']['bk_phone'] );
					update_post_meta($booking_id,'bk_username',$post_meta['_user_details']['bk_username'] );
					update_post_meta($booking_id,'_price',$post_meta['_price'] );
					update_post_meta($booking_id,'_appointment_date',$post_meta['_appointment_date'] );
					update_post_meta($booking_id,'_booking_service',$post_meta['_services'] );
					update_post_meta($booking_id,'_booking_slot',$post_meta['_slots'] );
					update_post_meta($booking_id,'_booking_hospitals',$post_meta['_hospital_id'] );
					update_post_meta($booking_id,'_hospital_id',$hospital_id );
					update_post_meta($booking_id,'_doctor_id',$doctor_id );
					update_post_meta($booking_id,'_am_booking',$post_meta );
					
					$slot_key_val 	= explode('-', $post_meta['_slots']);
					
					$start_time		= date($time_format, strtotime('2016-01-01' . $slot_key_val[0]));
					$end_time		= date($time_format, strtotime('2016-01-01' . $slot_key_val[1]));
					
					$start_time		= !empty( $start_time ) ? $start_time : '';
					$end_time		= !empty( $end_time ) ? $end_time : '';
					
					$earning['user_id']			= doctreat_get_linked_profile_id( $doctor_id,'post' );
					
					$earning['booking_id']		= $booking_id;
					
					if (class_exists('Doctreat_Email_helper')) {
						$emailData['user_name']		= $user_name;
											
						$emailData['doctor_name']	= doctreat_full_name($doctor_id);
						$emailData['doctor_link']	= get_the_permalink($doctor_id);
						$emailData['hospital_name']	= doctreat_full_name($hospital_id);
						$emailData['hospital_link']	= get_the_permalink($hospital_id);
						
						$emailData['appointment_date']	= !empty($post_meta['_appointment_date']) ? date($date_formate,strtotime($post_meta['_appointment_date'])) : '';
						$emailData['appointment_time']	= $start_time.' '.esc_html__('to','doctreat').' '.$end_time;
						$emailData['price']				= doctreat_price_format($post_meta['_price'],'return');
						$emailData['consultant_fee']	= doctreat_price_format($post_meta['_consultant_fee'],'return');
						$emailData['description']		= $contents;

						if (class_exists('DoctreatBookingNotify')) {
                                                    
                            //LiveSmart integration starts
                            if (get_option('livesmart_server_url', 'livesmart_content')) {
                                $lsvTime = !empty($post_meta['_slots']) ? explode('-',$post_meta['_slots']) : array();
                                $lsv_visitor_url = $doctor_id.$post_meta['_appointment_date'].$lsvTime[0];
                                $lsv_url = get_option('livesmart_server_url');
                                $_SESSION['livesmart_content_visitor'] = '<br/>' . $lsv_url . $lsv_visitor_url;
                                
                                $lsv_agent_url = $lsv_visitor_url.'_a';
                                $lsvContent = '<br/><br/>Meeting agent URL: &#13;&#10;' . $lsv_url . $lsv_agent_url . '&#13;&#10;<br/>Meeting visitor URL: &#13;&#10;' . $lsv_url . $lsv_visitor_url;
                                $_SESSION['livesmart_content'] = $lsvContent;
                            }
                            //LiveSmart integration ends
                                                    
                                                    
							$email_helper				= new DoctreatBookingNotify();

							$emailData['email']			= $email;
							$email_helper->send_request_email($emailData);

							$doctor_info				= get_userdata($earning['user_id']);
							$emailData['email']			= $doctor_info->user_email;
							$email_helper->send_doctor_email($emailData);
							
						}
					}
					
					//update message 
					if( function_exists('doctreat_send_booking_message') ){
						doctreat_send_booking_message($booking_id);
					}
					
					//update earning
					$table_name = $wpdb->prefix . "dc_earnings";
					
					if ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name ) {
						$tablename = $wpdb->prefix.'dc_earnings';
						$wpdb->insert( $tablename,$earning);
					}

					if(!empty($payment_type) && $payment_type === 'online' ){
						$order->update_status( 'completed' );
						$order->save();
					}
				}
				
			}
		}
    }
}

/**
 * Update User Pakage
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_update_package_data')) {
    function doctreat_update_package_data( $product_id, $user_id,$order_id,$quantity ) {
		global $theme_settings;
		$payment_type				= !empty( $theme_settings['payment_type'] ) ? $theme_settings['payment_type'] : '';
        $user_type					= doctreat_get_user_type( $user_id );
		$date_formate				= get_option('date_format');
		$package_data				= array();
		$quantity					= !empty($quantity) ? intval($quantity) : 1;
		$pakeges_features			= doctreat_get_pakages_features();
		$profile_id					= doctreat_get_linked_profile_id($user_id);
		$doctor_name				= doctreat_full_name($profile_id);
		$current_date 	= current_time('mysql');
		$wt_duration	= 0;

		if ( !empty ( $pakeges_features )) {
			foreach( $pakeges_features as $key => $vals ) {
				if( $vals['user_type'] === $user_type || $vals['user_type'] === 'common' ) {
					$item				= get_post_meta($product_id,$key,true);

					if( $key === 'dc_duration' ) {
						$wt_duration 		= doctreat_get_duration_types($item,'value');
						$feature			= $wt_duration * intval( $quantity );
					}else {
						if( !empty($vals['type']) && $vals['type'] === 'number' ){
							$feature	= $item * intval( $quantity );
						}else{
							$feature 	= $item;
						}

					}
					
					$package_data[$key]	= $feature;
				}
			}
		}
		
		$duration = $wt_duration * intval( $quantity ); //no of days for a featured listings
		
		if ( $duration > 0 ) {
			$package_date_string = strtotime("+" . $duration . " days", strtotime($current_date));
			$package_date = date('Y-m-d H:i:s', $package_date_string);
		}
		
		$package_data['subscription_id'] 				= $product_id;
		$package_data['subscription_package_expiry'] 	= $package_date;
		$package_data['subscription_package_string'] 	= $package_date_string;
		
		if( !empty( $package_data['dc_featured'] ) && !empty( $package_data['dc_featured_duration'] ) && $package_data['dc_featured'] === 'yes' ){
			$dc_featured_duration 	= $package_data['dc_featured_duration'];
			$dc_featured_duration 	= $dc_featured_duration * intval( $quantity );
			$featured_string = strtotime("+" . $dc_featured_duration . " days", strtotime($current_date));
			$featured = date('Y-m-d H:i:s', $featured_string);
			
			update_post_meta($profile_id, '_featured_date', $featured);
			update_post_meta($profile_id, 'is_featured', 1);
		} else{
			update_post_meta($profile_id, 'is_featured', 0);
		}

		update_user_meta( $user_id, 'dc_subscription', $package_data);
		
		if( !empty( $order_id ) ) {
			//Send email to users
			if (class_exists('Doctreat_Email_helper')) {
				if (class_exists('DoctreatSubscribePackage')) {
					$email_helper = new DoctreatSubscribePackage();
					$emailData 	= array();
					$user_type		= apply_filters('doctreat_get_user_type', $user_id );
					//update order
					$order 			= wc_get_order($order_id);
					$order->update_status( 'completed' );
					$order->save();
					$order 			= wc_get_order($order_id);

					$product 		= wc_get_product($product_id);
					$invoice_id 	= esc_html__('Order #','doctreat').$order_id;
					$package_name   = $product->get_title();
					$amount 		= $product->get_price() * intval( $quantity );
					$status 		= $order->get_status();
					$method 		= $order->payment_method;
					$name 			= $order->billing_first_name . '&nbsp;' . $order->billing_last_name;
					$user_email 	= get_userdata( $user_id )->user_email;

					$amount 		= wc_price( $amount );

					if( empty( $name ) ){
						$name 		= doctreat_get_username( $user_id );
					}
					
					$emailData['doctor_name'] 	= esc_html( $doctor_name );
					$emailData['invoice'] 		= esc_html( $invoice_id );
					$emailData['package_name'] 	= esc_html( $package_name );
					$emailData['amount'] 		= wp_strip_all_tags( $amount );
					$emailData['status']        = !empty($status) ? doctreat_get_order_status_text('wc-'.$status) :'';
					$emailData['method']        = !empty($method) ? doctreat_get_payment_gateways_text( $method ) : '';
					$emailData['date']      	= !empty($current_date) ? date( $date_formate,strtotime( $current_date ) ) : '';
					$emailData['expiry'] 		= !empty($package_date) ? date( $date_formate,strtotime( $package_date ) ) : '';
					$emailData['name'] 			= esc_html( $name );
					$emailData['email_to'] 		= esc_html( $user_email );
					$emailData['link'] 			= esc_url( get_the_permalink( $profile_id ) );

					if ( $user_type === 'doctors' ) {
						$email_helper->send_subscription_email_to_doctor($emailData);
						$email_helper->send_subscription_email_to_admin($emailData);
					} 
				}
			}
		}

    }
}

/**
 * Remove payment gateway
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_unused_payment_gateways')) {
    function doctreat_unused_payment_gateways($load_gateways) {
        $remove_gateways = array(
            'WC_Gateway_BACS',
            'WC_Gateway_Cheque',
            'WC_Gateway_COD',
        );
		
        foreach ($load_gateways as $key => $value) {
            if (in_array($value, $remove_gateways)) {
                unset($load_gateways[$key]);
            }
        }
		
        return $load_gateways;
    }

}


/**
 * Get packages features
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_package_features')) {

    function doctreat_get_package_features($key='') {
		$features	= doctreat_get_pakages_features();
		if( !empty( $features[$key] ) ){
			return $features[$key]['title'];
		} else{
			return '';
		}
    }
}

/**
 * Get Hiring title
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_booking_payment_title')) {

    function doctreat_get_booking_payment_title($key,$cart_items='') {
		
		$bookings	= doctreat_get_booking_payment($cart_items);
		
		if( !empty( $bookings[$key] ) ){
			return $bookings[$key]['title'];
		} else{
			return '';
		}
	}
}


/**
 * Get Booking meta
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_booking_value')) {

    function doctreat_get_booking_value($val='',$key='' ,$cart_data = '') {
		
		if( !empty($key) && $key === 'hospital' ) {
			global $theme_settings;
			$posttype	= get_post_type($val);
			if( !empty($posttype) && $posttype === 'hospitals_team' ){
				$hospital_id	= get_post_meta($val,'hospital_id',true);
				$val 			= esc_html( get_the_title( $hospital_id ) );
			} else {
				$val 			= esc_html( get_the_title( $val ) );
			}
			
		} else if( $key === 'doctor_id' ) {
			$val			= doctreat_full_name( $val );
		} else if( $key === 'slots' ) {
			$time_format 	= get_option('time_format');
			$slot_key_val 	= explode('-', $val);
			$val			= date($time_format, strtotime('2016-01-01' . $slot_key_val[0]) );
		}else if( $key === 'consultant_fee' || $key === 'price'  ) {
			$val			= doctreat_price_format($val,'return');
		} else if( $key === 'myself' ) {
			$val			= !empty($val) && $val === 'someelse' ? esc_html__('Other Person','doctreat') : esc_html__('My Self','doctreat');
		}
		
		return $val;
	}
}

/**
 * Get booking array
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_booking_payment')) {

    function doctreat_get_booking_payment($cart_items) {
		$booking	= array(
				'content'  		=> array('title' => esc_html__('Description','doctreat')),
				'service' 		=> array('title' => esc_html__('Service','doctreat')),
				'slots' 		=> array('title' => esc_html__('Appointment time','doctreat')),
				'hospital' 		=> array('title' => esc_html__('Location','doctreat')),
				'doctor_id' 	=> array('title' => esc_html__('Doctor name','doctreat')),
				'user_type' 	=> array('title' => esc_html__('User type','doctreat')),
				'appointment_date' 	=> array('title' => esc_html__('Appointment date','doctreat')),
				'consultant_fee' 	=> array('title' => esc_html__('Consultation fee','doctreat')),
				'price' 			=> array('title' => esc_html__('Total amount','doctreat')),
				'other_name' 		=> array('title' => esc_html__('Other person name','doctreat')),
				'relation' 			=> array('title' => esc_html__('Relation with patient','doctreat')),
				'myself' 			=> array('title' => esc_html__('Booking for','doctreat')),
				'full_name' 		=> array('title' => esc_html__('Name','doctreat')),
				'phone_number' 		=> array('title' => esc_html__('Phone','doctreat')),
				'email' 			=> array('title' => esc_html__('Email','doctreat')),
				'bk_email' 			=> array('title' => esc_html__('Email address','doctreat')),
				'bk_phone' 			=> array('title' => esc_html__('Phone','doctreat')),
			
			);

		if( !empty($cart_items) ){
			if( !empty($cart_items['cart_data']['myself']) && ($cart_items['cart_data']['myself'] === 'myself')){
				unset($booking['relation']);
				$booking['other_name']['title'] = esc_html__('Patient name','doctreat');
			}
		}
		
		return $booking;
	}
}

/**
 * Update checkout fields
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if ( !function_exists('doctreat_checkout_fields') ) {
	add_filter( 'woocommerce_checkout_get_value', 'doctreat_checkout_fields', 10, 2 );
	function doctreat_checkout_fields ( $value, $input ) {
		global $current_user;
		$token = ( ! empty( $_GET['token'] ) ) ? $_GET['token'] : '';

		if( empty($token) && is_user_logged_in() ) {
			$first_name	= !empty($current_user->first_name) ? $current_user->first_name : '';
			$last_name	= !empty($current_user->last_name) ? $current_user->last_name : '';
			$user_email	= !empty($current_user->user_email) ? $current_user->user_email : '';
			

			$checkout_fields = array(
				'billing_first_name'    => $first_name,
				'billing_last_name'     => $last_name,
				'billing_email'         => $user_email,
			);
			
			foreach( $checkout_fields as $key_field => $field_value ){
				if( $input == $key_field && ! empty( $field_value ) ){
					$value = $field_value;
				}
			}
		}
		return $value;
	}
}

/**
 * Get Services meta
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_booking_services_value')) {

    function doctreat_get_booking_services_value($val='',$key='' ,$cart_data = '') {
		
		$doctor_id				= $cart_data['doctor_id'];
		$am_specialities 		= doctreat_get_post_meta( $doctor_id,'am_specialities');
		$am_specialities		= !empty( $am_specialities ) ? $am_specialities : array();
		$price					= !empty( $am_specialities[$val][$key]['price'] ) ? doctreat_price_format( $am_specialities[$val][$key]['price'],'return') : 0;
		$price					= !empty( $price ) ? $price : 0;
		
		return $price;
	}
}

/**
 * Get package Feature values
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_package_feature_value')) {

    function doctreat_get_package_feature_value($val='',$key='') {
		if( isset( $val ) && $val === 'yes' ){
			$return	= '<i class="fa fa-check-circle sp-pk-allowed"></i>';
		} elseif( isset( $val ) && $val === 'no' ){
			$return	= '<i class="fa fa-times-circle sp-pk-not-allowed"></i>';
		} else{
			$return	= $val;
		}
		
		return $return;
	}
}


/**
 * Add data in checkout
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_add_new_fields_checkout')) {
	add_filter( 'woocommerce_checkout_after_customer_details', 'doctreat_add_new_fields_checkout', 10, 1 );
	function doctreat_add_new_fields_checkout() {
		global $product,$woocommerce;
		$cart_data = WC()->session->get( 'cart', null );
		if( !empty( $cart_data ) ) {
			foreach( $cart_data as $key => $cart_items ){
				if( !empty( $cart_items['payment_type'] ) && $cart_items['payment_type'] =='bookings') {
					$title		= esc_html__('Boooking information','doctreat');
					$quantity	= !empty( $cart_items['quantity'] ) ?  $cart_items['quantity'] : 1;
					if( !empty( $cart_items['cart_data'] ) ){
					?>
					<div class="cart-data-wrap">
					  <h3><?php echo esc_html($title);?>( <span class="cus-quantity">×<?php echo esc_html( $quantity );?></span> )</h3>
					  <div class="selection-wrap">
						<?php 
							$counter	= 0;
							foreach( $cart_items['cart_data'] as $key => $value ){
								$counter++;
								if( $key === 'service' ) { ?>
									<div class="cart-style">
										<span class="style-lable"><b><?php echo doctreat_get_booking_payment_title( $key );?></b></span> 
									</div>
									<?php 		  
										foreach( $value as $key => $vals ) { 
											foreach( $vals as $k => $v ) {
											?>
											<div class="cart-style"> 
												<span class="style-lable"><?php echo doctreat_get_term_name( $v ,'services');?></span> 
												<span class="style-name"><?php echo doctreat_get_booking_services_value( $key,$v,$cart_items['cart_data'] );?></span> 
											</div>
										<?php } ?>
									<?php } ?>
								<?php } else { ?>
									<div class="cart-style"> 
										<span class="style-lable"><?php echo doctreat_get_booking_payment_title( $key,$cart_items );?></span> 
										<span class="style-name"><?php echo doctreat_get_booking_value( $value,$key,$cart_items );?></span> 
									</div>
								<?php }?>
							<?php }?>
					  </div>
					</div>
					<?php
					}
				} elseif( !empty( $cart_items['payment_type'] ) && $cart_items['payment_type'] === 'subscription') {
					$title		= esc_html(get_the_title($cart_items['product_id']));
					$quantity	= !empty( $cart_items['quantity'] ) ?  $cart_items['quantity'] : 1;

					if( !empty( $cart_items['cart_data'] ) ){
					?>
					<div class="cart-data-wrap">
					  <h3><?php echo esc_html($title);?>( <span class="cus-quantity">×<?php echo esc_html( $quantity );?></span> )</h3>
					  <div class="selection-wrap">
						<?php 
							$counter	= 0;
							foreach( $cart_items['cart_data'] as $key => $value ){
								$counter++;
							?>
								<div class="cart-style"> 
									<span class="style-lable"><?php echo doctreat_get_package_features( $key );?></span> 
									<span class="style-name"><?php echo doctreat_get_package_feature_value( $value,$key );?></span> 
								</div>
							<?php }?>
					  </div>
					</div>
					<?php
					}
				}
				
			}
		}
	}
}


/**
 * Add meta on order item
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_update_order_meta')) {
	add_action( 'woocommerce_new_order_item', 'doctreat_update_order_meta',  1, 3 );
	function doctreat_update_order_meta( $item_id, $item, $order_id ) {
		if ( !empty( $item->legacy_values['cart_data'] ) ) {
			wc_add_order_item_meta( $item_id, 'cus_woo_product_data', $item->legacy_values['cart_data'] );
			update_post_meta( $order_id, 'cus_woo_product_data', $item->legacy_values['cart_data'] );
		}
		
		if ( !empty( $item->legacy_values['payment_type'] ) ) {
			wc_add_order_item_meta( $item_id, 'payment_type', $item->legacy_values['payment_type'] );
			update_post_meta( $order_id, 'payment_type', $item->legacy_values['payment_type'] );
		}
		
		if ( !empty( $item->legacy_values['admin_shares'] ) ) {
			wc_add_order_item_meta( $item_id, 'admin_shares', $item->legacy_values['admin_shares'] );
			update_post_meta( $order_id, 'admin_shares', $item->legacy_values['admin_shares'] );
		}
		
		if ( !empty( $item->legacy_values['doctors_shares'] ) ) {
			wc_add_order_item_meta( $item_id, 'doctors_shares', $item->legacy_values['doctors_shares'] );
			update_post_meta( $order_id, 'doctors_shares', $item->legacy_values['doctors_shares'] );
		}
		
		if ( !empty( $item->legacy_values['doctor_id'] ) ) {
			wc_add_order_item_meta( $item_id, 'doctor_id', $item->legacy_values['doctor_id'] );
			update_post_meta( $order_id, 'doctor_id', $item->legacy_values['doctor_id'] );
		}
		
		if ( !empty( $item->legacy_values['patient_id'] ) ) {
			wc_add_order_item_meta( $item_id, 'patient_id', $item->legacy_values['patient_id'] );
			update_post_meta( $order_id, 'patient_id', $item->legacy_values['patient_id'] );
		}
	}
}

/**
 * Display order detail
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_display_order_data')) {
	add_action( 'woocommerce_thankyou', 'doctreat_display_order_data', 20 ); 
	add_action( 'woocommerce_view_order', 'doctreat_display_order_data', 20 );
	function doctreat_display_order_data( $order_id ) {
		global $product,$woocommerce,$wpdb,$current_user;
		
		$order = new WC_Order( $order_id );
		$items = $order->get_items();
		if( !empty( $items ) ) {
			$counter	= 0;
			foreach( $items as $key => $order_item ){
				$counter++;
				$payment_type 	= wc_get_order_item_meta( $key, 'payment_type', true );
				$order_detail 	= wc_get_order_item_meta( $key, 'cus_woo_product_data', true );
				$item_id    	= $order_item['product_id'];
				if( !empty($payment_type)  && $payment_type ==='hiring' ) {
					$order_item['name'] 	= doctreat_get_hiring_value($order_detail['project_id'],'project_id');
				}
				$name		= !empty( $order_item['name'] ) ?  $order_item['name'] : '';
				$quantity	= !empty( $order_item['qty'] ) ?  $order_item['qty'] : 1;
				if( !empty( $order_detail ) ) {?>
					<div class="row">
						<div class="col-md-12">
							<div class="cart-data-wrap">
							  <h3><?php echo esc_html($name);?>( <span class="cus-quantity">×<?php echo esc_html( $quantity );?></span> )</h3>
							  <div class="selection-wrap">
								<?php 
									$counter	= 0;
									foreach( $order_detail as $key => $value ){
										$counter++;
										if( !empty($payment_type) && $payment_type === 'bookings') {?>
											<?php if( $key === 'service' ) { ?>
												<div class="cart-style">
													<label><span><b><?php echo doctreat_get_booking_payment_title( $key );?></b></span></label>
												</div>
												<?php 
													foreach( $value as $key => $vals ) { 
														foreach( $vals as $k => $v ) {
													?>
														<div class="cart-style"> 
															<label><span class="style-lable"><?php echo doctreat_get_term_name( $v ,'services');?></span></label> 
															<span class="style-name"><?php echo doctreat_get_booking_services_value( $key,$v,$order_detail );?></span> 
														</div>
													<?php } ?>
												<?php } ?>
											<?php } else { ?>
												<div class="cart-style">
													<label><span class="style-lable"><?php echo doctreat_get_booking_payment_title( $key );?></span></label> 
													<span class="style-name"><?php echo doctreat_get_booking_value( $value,$key,$order_detail );?></span> 
												</div>
											<?php }?>
										<?php }  else if( !empty( $payment_type ) && $payment_type ==='subscription' ) { ?>
											<div class="cart-style"> 
												<span class="style-lable"><?php echo doctreat_get_package_features($key);?></span> 
												<span class="style-name"><?php echo doctreat_get_package_feature_value( $value,$key );?></span> 
											</div>
										<?php } ?>
									<?php }?>
							  </div>
							</div>
						 </div>
						 <?php if( !empty( $current_user->ID ) ){?>
							 <div class="col-md-12">
								<a class="dc-btn" href="<?php Doctreat_Profile_Menu::doctreat_profile_menu_link('insights', $current_user->ID); ?>"><?php esc_html_e('Return to dashboard','doctreat');?></a>
							 </div>
						 <?php }?>	
					</div>
				<?php
				}
			}
		}
	}
}

/**
 * Print order meta at back-end in order detail page
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_woo_order_meta')) {
	add_filter( 'woocommerce_after_order_itemmeta', 'doctreat_woo_order_meta', 10, 3 );
	function doctreat_woo_order_meta( $item_id, $item, $_product ) {
		global $product,$woocommerce,$wpdb;
		$order_detail = wc_get_order_item_meta( $item_id, 'cus_woo_product_data', true );
		
		$order_item = new WC_Order_Item_Product($item_id);
		$order				= $order_item->get_order();
		$order_status		= $order->get_status();
  		$customer_user 		= get_post_meta( $order->get_id(), '_customer_user', true );
		$payment_type 		= wc_get_order_item_meta( $item_id, 'payment_type', true );

		if( !empty( $order_detail ) ) {?>
			<div class="order-edit-wrap">
				<div class="view-order-detail">
					<a href="javascript:;" data-target="#cus-order-modal-<?php echo esc_attr( $item_id );?>" class="cus-open-modal cus-btn cus-btn-sm"><?php esc_html_e('View order detail?','doctreat');?></a>
				</div>
				<div class="cus-modal" id="cus-order-modal-<?php echo esc_attr( $item_id );?>">
					<div class="cus-modal-dialog">
						<div class="cus-modal-content">
							<div class="cus-modal-header">
								<a href="javascript:;" data-target="#cus-order-modal-<?php echo esc_attr( $item_id );?>" class="cus-close-modal">×</a>
								<h4 class="cus-modal-title"><?php esc_html_e('Order Detail','doctreat');?></h4>
							</div>
							<div class="cus-modal-body">
								<div class="sp-order-status">
									<p><?php echo ucwords( $order_status );?></p>
								</div>
								<div class="cus-form cus-form-change-settings">
									<div class="edit-type-wrap">
										<?php 
										$counter	= 0;
										foreach( $order_detail as $key => $value ){
											$counter++;
											
											if( !empty($payment_type) && $payment_type === 'bookings') {?>
												<?php if( $key === 'service' ) { ?>
													<div class="cus-options-data">
														<label><span><b><?php echo doctreat_get_booking_payment_title( $key );?></b></span></label>
													</div>
													<?php 
														foreach( $value as $key => $vals ) { 
															foreach( $vals as $k => $v ) {
														?>
															<div class="cus-options-data"> 
																<label><span><?php echo doctreat_get_term_name( $v ,'services');?></span></label> 
																<div class="step-value"><?php echo doctreat_get_booking_services_value( $key,$v,$order_detail );?></div> 
															</div>
														<?php } ?>
													<?php } ?>
												<?php } else { ?>
													<div class="cus-options-data">
														<label><span><?php echo doctreat_get_booking_payment_title( $key );?></span></label> 
														<div class="step-value"><?php echo doctreat_get_booking_value( $value,$key,$order_detail );?></div> 
													</div>
												<?php }?>
											<?php } else if( !empty($payment_type) && $payment_type === 'subscription' ) { ?>
												<div class="cus-options-data">
													<label><span><?php echo doctreat_get_package_features($key);?></span></label>
													<div class="step-value">
														<span><?php echo doctreat_get_package_feature_value( $value, $key );?></span>
													</div>
												</div>
											<?php }
											}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php						
		}
	}
}

/**
 * Filter woocommerce display itme meta
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_filter_woocommerce_display_item_meta')) {
	function doctreat_filter_woocommerce_display_item_meta( $html, $item, $args ) {
		// make filter magic happen here... 
		return ''; 
	}; 

	// add the filter 
	add_filter( 'woocommerce_display_item_meta', 'doctreat_filter_woocommerce_display_item_meta', 10, 3 ); 
}

/**
 * Woocommerce account menu
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_my_account_menu_items')) {
	add_filter( 'woocommerce_account_menu_items', 'doctreat_my_account_menu_items' );
	function doctreat_my_account_menu_items( $items ) {
		unset($items['dashboard']);
		unset($items['downloads']);
		unset($items['edit-address']);
		unset($items['payment-methods']);
		unset($items['edit-account']);
		return $items;
	}
}

/**
 * Hired product ID
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_get_booking_product_id')) {

    function doctreat_get_booking_product_id() {
		$args = array(
			'post_type' 			=> 'product',
			'posts_per_page' 		=> -1,
			'order' 				=> 'DESC',
			'orderby' 				=> 'ID',
			'post_status' 			=> 'publish',
			'ignore_sticky_posts' 	=> 1
		);


		$meta_query_args[] = array(
			'key' 			=> '_doctreat_booking',
			'value' 		=> 'yes',
			'compare' 		=> '=',
		);
		
		$query_relation 		= array('relation' => 'AND',);
		$meta_query_args 		= array_merge($query_relation, $meta_query_args);
		$args['meta_query'] 	= $meta_query_args;
		
		$booking_product = get_posts($args);
		
		if (!empty($booking_product)) {
            return (int) $booking_product[0]->ID;
        } else{
			 return 0;
		}
		
	}
}

/**
 * Price override
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_apply_custom_price_to_cart_item')) {
	
	add_action( 'woocommerce_before_calculate_totals', 'doctreat_apply_custom_price_to_cart_item', 99 );
	function doctreat_apply_custom_price_to_cart_item( $cart_object ) {  
		global $theme_settings;
			$payment_type	= !empty( $theme_settings['payment_type'] ) ? $theme_settings['payment_type'] : '';
			if( !empty( $payment_type ) ){
			if( !WC()->session->__isset( "reload_checkout" )) {
				foreach ( $cart_object->cart_contents as $key => $value ) {
					if( !empty( $value['payment_type'] ) && $value['payment_type'] == 'bookings' ){
						if( isset( $value['cart_data']['price'] ) ){
							$bk_price = floatval( $value['cart_data']['price'] );
							$value['data']->set_price($bk_price);
						}
					}
				}   
			}
		}
	}
}

/**
 * Remove Product link in checkout
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
add_filter( 'woocommerce_order_item_permalink', '__return_false' );

if (!function_exists('doctreat_create_packages_product_type')) {
	add_filter( 'product_type_selector', 'doctreat_create_packages_product_type' );

	function doctreat_create_packages_product_type( $types ){
		$types[ 'packages' ] = esc_html__('Packages','doctreat');
		return $types;
	}
}

if (class_exists('WooCommerce')) {
	if (!function_exists('doctreat_packages_product_type')) {
		add_action( 'init', 'doctreat_packages_product_type' );

		function doctreat_packages_product_type(){
			class WC_Product_Packages extends WC_Product {
			  public function get_type() {
				 return 'packages';
			  }
			}
		}
	}
}

if (!function_exists('doctreat_packages_product_tab')) {
	add_filter( 'woocommerce_product_data_tabs', 'doctreat_packages_product_tab' );
	function doctreat_packages_product_tab( $tabs) {
		$tabs['packages'] = array(
							  'label'	 	=> esc_html__( 'Package Options', 'doctreat' ),
							  'target' 		=> 'packages_product_options',
							  'class'  		=> 'show_if_packages',
							 );
		return $tabs;
	}
}

if (!function_exists('doctreat_packages_tab_product_tab_content')) {
	
	add_action( 'woocommerce_product_data_panels', 'doctreat_packages_tab_product_tab_content' );

	function doctreat_packages_tab_product_tab_content() { ?>
		<div id='packages_product_options' class='panel woocommerce_options_panel'>
			<div class='options_group'>
				<?php

					global $woocommerce, $post;
					woocommerce_wp_select(
							array(
								'id' 			=> 'package_type',
								'class' 		=> 'dc_package_type',
								'label' 		=> esc_html__('Package Type?', 'doctreat'),
								'desc_tip' 		=> 'false',
								'disabled'		=> True,
								'description' 	=> esc_html__('If packages type will be doctors then package will be display in doctors dashboard.', 'doctreat'),
								'options' 		=> doctreat_packages_types( $post )
							)
					);
					$pakeges_features 	= doctreat_get_pakages_features();
					foreach( $pakeges_features as $key => $vals ) {
						if ( $vals['type'] === 'number') {
							woocommerce_wp_text_input(
								array(
										'id' 			=> $key,
										'class' 		=> $vals['classes'],
										'label' 		=> $vals['title'],
										'desc_tip' 		=> 'true',
										'type' 			=> $vals['type'],
										'description'	=> $vals['hint'],
										'custom_attributes' => doctreat_get_numaric_values( 1,1 )
									)
							);
						} elseif ( $vals['type'] === 'select') {
							woocommerce_wp_select(
										array(
											'id' 			=> $key,
											'class' 		=> $vals['classes'],
											'label' 		=> $vals['title'],
											'type' 			=> $vals['type'],
											'description'	=> $vals['hint'],
											'options' 		=> $vals['options']
										)
								);
						} elseif ( $vals['type'] === 'input') {
							woocommerce_wp_text_input(
								array(
										'id' 			=> $key,
										'class' 		=> $vals['classes'],
										'label' 		=> $vals['title'],
										'desc_tip' 		=> 'true',
										'description'	=> $vals['hint'],
										'type' 			=> $vals['type']
									)
							);
						}
					}
				?>
		</div>
	 </div>
	 <?php
	}
}

/**
 * Add product type options
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_product_type_options')) {
	add_filter('product_type_options', 'doctreat_product_type_options', 10, 1);
	function doctreat_product_type_options( $options ) {
		if(current_user_can('administrator')) {
			$options['doctreat_booking'] = array(
				'id' 			=> '_doctreat_booking',
				'wrapper_class' => 'show_if_simple',
				'label' 		=> esc_html__('Booking', 'doctreat'),
				'description' 	=> esc_html__('Booking product will be used to make the payment for the Booking', 'doctreat'),
				'default' 		=> 'no'
			);
		}
		
		return $options;
	}
}

/**
 * Save products meta
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_woocommerce_process_product_meta')) {
	add_action('woocommerce_process_product_meta_simple', 'doctreat_woocommerce_process_product_meta', 10, 1);
	function doctreat_woocommerce_process_product_meta( $post_id ) {
		doctreat_update_booking_product(); //update default booking product
		$is_doctreat_booking	= isset($_POST['_doctreat_booking']) ? 'yes' : 'no';
		update_post_meta($post_id, '_doctreat_booking', $is_doctreat_booking);
	}
}

/**
 * Update hiring product
 *
 * @throws error
 * @author Amentotech <theamentotech@gmail.com>
 * @return 
 */
if (!function_exists('doctreat_update_booking_product')) {

    function doctreat_update_booking_product() {
		$meta_query_args = array();
		$args = array(
			'post_type' 		=> 'product',
			'posts_per_page' 	=> -1,
			'order' 			=> 'DESC',
			'orderby'			=> 'ID',
			'post_status' 		=> 'publish',
			'ignore_sticky_posts' => 1
		);


		$meta_query_args[] = array(
			'key' 			=> '_doctreat_booking',
			'value' 		=> 'yes',
			'compare' 		=> '=',
		);
		
		$query_relation 		= array('relation' => 'AND',);
		$meta_query_args 		= array_merge($query_relation, $meta_query_args);
		$args['meta_query'] 	= $meta_query_args;
		
		$booking_product = get_posts($args);
		
		if (!empty($booking_product)) {
            $counter = 0;
            foreach ($booking_product as $key => $product) {
                update_post_meta($product->ID, '_doctreat_booking', 'no');
            }
        }
		
	}
}