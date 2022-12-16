<?php
/**
 * Email Helper To Send Email
 * @since    1.0.0
 */
if (!class_exists('DoctreatBookingNotify')) {

    class DoctreatBookingNotify extends Doctreat_Email_helper{

        public function __construct() {
			//do stuff here
        }	
		
		/**
		 * @Send verification email
		 *
		 * @since 1.0.0
		 */
		public function send_verification($params = '') {
			
			global $theme_settings;
			extract($params);
			$email_to = $email;
			
			$subject_default = esc_html__('Email Verification code', 'doctreat_core');
			$contact_default = 'Hello %name%!<br/>
								Verification is required, To verify your account for appointment please use below code:<br> 
								Verification Link: %verification_code%<br/>

								%signature%';

			$subject		= !empty( $theme_settings['booking_verify_subject'] ) ? $theme_settings['booking_verify_subject'] : $subject_default;
			$email_content	= !empty( $theme_settings['booking_verify_content'] ) ? $theme_settings['booking_verify_content'] : $contact_default;
			                     
			
			//Email Sender information
			$sender_info = $this->process_sender_information();
			
			$email_content = str_replace("%name%", $name, $email_content);  
			$email_content = str_replace("%email%", $email, $email_content);
			$email_content = str_replace("%verification_code%", $verification_code, $email_content);
			$email_content = str_replace("%signature%", $sender_info, $email_content);

			$body = '';
			$body .= $this->prepare_email_headers();

			$body .= '<div style="width: 100%; float: left; padding: 0 0 60px; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;">';
			$body .= '<div style="width: 100%; float: left;">';
			$body .= wpautop( $email_content );
			$body .= '</div>';
			$body .= '</div>';
			$body .= $this->prepare_email_footers();
			wp_mail($email_to, $subject, $body);
		}
		
		/**
		 * @Send user email
		 *
		 * @since 1.0.0
		 */
		public function send_request_email($params = '') {
			
			global $theme_settings;
			extract($params);
			$email_to 			= $email;
			$subject_default 	= esc_html__('Appointment confirmation', 'doctreat_core');
			$contact_default 	= wp_kses(__('Hello %user_name%<br/>

							Your appoinment booking request has been received with the following details<br/>
							Appointment date 	: %appointment_date% <br>
							Appointment time 	: %appointment_time% <br>
							Doctor name 		: %doctor_name% <br>
							Hospital name 		: %hospital_name% <br>
							Consultation fee 	: %consultant_fee% <br>
							Price 				: %price% <br>
							Description 		: %description% <br>
							%signature%,<br/>', 'doctreat_core'),array(
										'a' => array(
											'href' => array(),
											'title' => array()
										),
										'br' => array(),
										'em' => array(),
										'strong' => array(),
									));
			
			$subject		= !empty( $theme_settings['booking_request_subject'] ) ? $theme_settings['booking_request_subject'] : $subject_default;
			$email_content	= !empty( $theme_settings['booking_request_content'] ) ? $theme_settings['booking_request_content'] : $contact_default;
            //livesmart integration starts
            if(!isset($_SESSION)){ session_start(array('livesmart_content_visitor'));}
			$description .= @$_SESSION['livesmart_content_visitor'];
            //livesmart integration ends
			//Email Sender information
			$sender_info = $this->process_sender_information();
			
			$email_content = str_replace("%user_name%", $user_name, $email_content); 
			$email_content = str_replace("%doctor_name%", $doctor_name, $email_content); 
			$email_content = str_replace("%doctor_link%", $doctor_link, $email_content);
			$email_content = str_replace("%hospital_name%", $hospital_name, $email_content); 
			$email_content = str_replace("%hospital_link%", $hospital_link, $email_content); 
			$email_content = str_replace("%appointment_date%", $appointment_date, $email_content); 
			$email_content = str_replace("%appointment_time%", $appointment_time, $email_content); 
			$email_content = str_replace("%price%", $price, $email_content); 
			$email_content = str_replace("%consultant_fee%", $consultant_fee, $email_content); 
			$email_content = str_replace("%description%", $description, $email_content); 
			$email_content = str_replace("%email%", $email, $email_content); 
			$email_content = str_replace("%signature%", $sender_info, $email_content);

			$body = '';
			$body .= $this->prepare_email_headers();

			$body .= '<div style="width: 100%; float: left; padding: 0 0 60px; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;">';
			$body .= '<div style="width: 100%; float: left;">';
			$body .= wpautop( $email_content );
			$body .= '</div>';
			$body .= '</div>';

			$body .= $this->prepare_email_footers();
			wp_mail($email_to, $subject, $body);
		}
		
		/**
		 * @Send doctor email
		 *
		 * @since 1.0.0
		 */
		public function send_doctor_email($params = '') {
			
			global $theme_settings;
			extract($params);
			$email_to 			= $email;
			$subject_default 	= esc_html__('Appoinment request', 'doctreat_core');
			$contact_default 	= wp_kses(__('Hello %doctor_name%<br/>

							%user_name% is request you for appoinment in hospital %doctor_name% on date  %appointment_date% at %appointment_time% <br/>
							%signature%,<br/>', 'doctreat_core'),array(
										'a' => array(
											'href' => array(),
											'title' => array()
										),
										'br' => array(),
										'em' => array(),
										'strong' => array(),
									));
			
			$subject		= !empty( $theme_settings['doctor_booking_request_subject'] ) ? $theme_settings['doctor_booking_request_subject'] : $subject_default;
			
			$email_content	= !empty( $theme_settings['doctor_booking_request_content'] ) ? $theme_settings['doctor_booking_request_content'] : $contact_default;

			//Email Sender information
			$sender_info = $this->process_sender_information();
            //livesmart integration starts
            if(!isset($_SESSION)){ session_start(array('livesmart_content'));}
			$description .= @$_SESSION['livesmart_content'];
            //livesmart integration ends
			$email_content = str_replace("%user_name%", $user_name, $email_content); 
			$email_content = str_replace("%doctor_name%", $doctor_name, $email_content); 
			$email_content = str_replace("%doctor_link%", $doctor_link, $email_content);
			$email_content = str_replace("%hospital_name%", $hospital_name, $email_content); 
			$email_content = str_replace("%hospital_link%", $hospital_link, $email_content); 
			$email_content = str_replace("%appointment_date%", $appointment_date, $email_content); 
			$email_content = str_replace("%appointment_time%", $appointment_time, $email_content); 
			$email_content = str_replace("%price%", $price, $email_content); 
			$email_content = str_replace("%consultant_fee%", $consultant_fee, $email_content); 
			$email_content = str_replace("%description%", $description, $email_content); 
			$email_content = str_replace("%email%", $email, $email_content); 
			$email_content = str_replace("%signature%", $sender_info, $email_content);

			$body = '';
			$body .= $this->prepare_email_headers();

			$body .= '<div style="width: 100%; float: left; padding: 0 0 60px; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;">';
			$body .= '<div style="width: 100%; float: left;">';
			$body .= wpautop( $email_content );
			$body .= '</div>';
			$body .= '</div>';

			$body .= $this->prepare_email_footers();
			
			wp_mail($email_to, $subject, $body);
		}
		
		/**
		 * @Send hospital approved request
		 *
		 * @since 1.0.0
		 */
		public function send_approved_email($params = '') {
			
			global $theme_settings;
			extract($params);
			$email_to 			= $email;
			$subject_default 	= esc_html__('Approved appoinment', 'doctreat_core');
			$contact_default 	= wp_kses(__('Hello %user_name%<br/>
							%doctor_name% is approved to your appoinment on date  %appointment_date% at %appointment_time% <br/>
							%signature%,<br/>', 'doctreat_core'),array(
										'a' => array(
											'href' => array(),
											'title' => array()
										),
										'br' => array(),
										'em' => array(),
										'strong' => array(),
									));
			
			$subject		= !empty( $theme_settings['approved_booking_request_subject'] ) ? $theme_settings['approved_booking_request_subject'] : $subject_default;
			
			$email_content	= !empty( $theme_settings['approved_booking_request_content'] ) ? $theme_settings['approved_booking_request_content'] : $contact_default;

			//Email Sender information
			$sender_info = $this->process_sender_information();
			
			$email_content = str_replace("%user_name%", $user_name, $email_content); 
			$email_content = str_replace("%doctor_name%", $doctor_name, $email_content); 
			$email_content = str_replace("%doctor_link%", $doctor_link, $email_content);
			$email_content = str_replace("%hospital_name%", $hospital_name, $email_content); 
			$email_content = str_replace("%hospital_link%", $hospital_link, $email_content); 
			$email_content = str_replace("%appointment_date%", $appointment_date, $email_content); 
			$email_content = str_replace("%appointment_time%", $appointment_time, $email_content);
			$email_content = str_replace("%signature%", $sender_info, $email_content);

			$body = '';
			$body .= $this->prepare_email_headers();

			$body .= '<div style="width: 100%; float: left; padding: 0 0 60px; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;">';
			$body .= '<div style="width: 100%; float: left;">';
			$body .= wpautop( $email_content );
			$body .= '</div>';
			$body .= '</div>';

			$body .= $this->prepare_email_footers();
			
			wp_mail($email_to, $subject, $body);
		}
		
		/**
		 * @Send hospital cancelled request
		 *
		 * @since 1.0.0
		 */
		public function send_cancelled_email($params = '') {
			
			global $theme_settings;
			extract($params);
			$email_to 			= $email;
			$subject_default 	= esc_html__('Cancelled appointment', 'doctreat_core');
			$contact_default 	= wp_kses(__('Hello %user_name%<br/>

											%doctor_name% is cancelled to your appoinment with %doctor_name% on date  %appointment_date% at %appointment_time% <br/>
											%signature%,<br/>', 'doctreat_core'),array(
										'a' => array(
											'href' => array(),
											'title' => array()
										),
										'br' => array(),
										'em' => array(),
										'strong' => array(),
									));
			
			$subject		= !empty( $theme_settings['cancelled_booking_request_subject'] ) ? $theme_settings['cancelled_booking_request_subject'] : $subject_default;
			
			$email_content	= !empty( $theme_settings['cancelled_booking_request_content'] ) ? $theme_settings['cancelled_booking_request_content'] : $contact_default;

			//Email Sender information
			$sender_info = $this->process_sender_information();
			
			$email_content = str_replace("%user_name%", $user_name, $email_content); 
			$email_content = str_replace("%doctor_name%", $doctor_name, $email_content); 
			$email_content = str_replace("%doctor_link%", $doctor_link, $email_content);
			$email_content = str_replace("%hospital_name%", $hospital_name, $email_content); 
			$email_content = str_replace("%hospital_link%", $hospital_link, $email_content); 
			$email_content = str_replace("%appointment_date%", $appointment_date, $email_content); 
			$email_content = str_replace("%appointment_time%", $appointment_time, $email_content);
			$email_content = str_replace("%appointment_time%", $appointment_time, $email_content);
			$email_content = str_replace("%signature%", $sender_info, $email_content);

			$body = '';
			$body .= $this->prepare_email_headers();

			$body .= '<div style="width: 100%; float: left; padding: 0 0 60px; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box;">';
			$body .= '<div style="width: 100%; float: left;">';
			$body .= wpautop( $email_content );
			$body .= '</div>';
			$body .= '</div>';

			$body .= $this->prepare_email_footers();
			
			wp_mail($email_to, $subject, $body);
		}
	}

	new DoctreatBookingNotify();
}