<?php
function wpst_login_register_modal() {
	$siteKey = xbox_get_field_value( 'wpst-options', 'recaptcha-site-key' );
	$secret = xbox_get_field_value( 'wpst-options', 'recaptcha-secret-key' );
	// only show the registration/login form to non-logged-in members
	if( ! is_user_logged_in() ){ 
	?>
		<div class="modal fade wpst-user-modal" id="wpst-user-modal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog" data-active-tab="">
				<div class="modal-content">
					<div class="modal-body">
					<a href="#" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-remove"></i></a>
						<!-- Register form -->
						<div class="wpst-register">	
							<?php if( get_option('users_can_register') ) : ?>						 
								<h3><?php printf( esc_html__('Join %s', 'wpst'), get_bloginfo('name') ); ?></h3>									

								<form id="wpst_registration_form" action="<?php echo home_url( '/' ); ?>" method="POST">

									<div class="form-field">
										<label><?php esc_html_e('Username', 'wpst'); ?></label>
										<input class="form-control input-lg required" name="wpst_user_login" type="text"/>
									</div>
									<div class="form-field">
										<label for="wpst_user_email"><?php esc_html_e('Email', 'wpst'); ?></label>
										<input class="form-control input-lg required" name="wpst_user_email" id="wpst_user_email" type="email"/>
									</div>
									<div class="form-field">
										<label for="wpst_user_pass"><?php esc_html_e('Password', 'wpst'); ?></label>
										<input class="form-control input-lg required" name="wpst_user_pass" type="password"/>
									</div>
									<?php if ( xbox_get_field_value( 'wpst-options', 'enable-recaptcha' ) == 'on' && $siteKey != '' && $secret != '' ) : ?>
										<div class="g-recaptcha" data-sitekey="<?php echo $siteKey; ?>" data-theme="dark"></div>
									<?php endif; ?>
									<div class="form-field">
										<input type="hidden" name="action" value="wpst_register_member"/>
										<button class="btn btn-theme btn-lg" data-loading-text="<?php esc_html_e('Loading...', 'wpst') ?>" type="submit"><?php esc_html_e('Sign up', 'wpst'); ?></button>
									</div>
									<?php wp_nonce_field( 'ajax-login-nonce', 'register-security' ); ?>
								</form>
								<div class="wpst-errors"></div>
							<?php else : ?>
								<div class="alert alert-danger"><?php esc_html_e('Registration is disabled.', 'wpst'); ?></div>
							<?php endif; ?>
						</div>

						<!-- Login form -->
						<div class="wpst-login">							 
							<h3><?php echo apply_filters('update_title', sprintf(__('Login to %s', 'wpst'), get_bloginfo('name')), 'login_popup' ); ?></h3>
						
							<form id="wpst_login_form" action="<?php echo home_url( '/' ); ?>" method="post">

								<div class="form-field">
									<label><?php esc_html_e('Username', 'wpst') ?></label>
									<input class="form-control input-lg required" name="wpst_user_login" type="text"/>
								</div>
								<div class="form-field">
									<label for="wpst_user_pass"><?php esc_html_e('Password', 'wpst')?></label>
									<input class="form-control input-lg required" name="wpst_user_pass" id="wpst_user_pass" type="password"/>
								</div>
								<div class="form-field lost-password">
									<input type="hidden" name="action" value="wpst_login_member"/>
									<button class="btn btn-theme btn-lg" data-loading-text="<?php esc_html_e('Loading...', 'wpst') ?>" type="submit"><?php esc_html_e('Login', 'wpst'); ?></button> <a class="alignright" href="#wpst-reset-password"><?php esc_html_e('Lost Password?', 'wpst') ?></a>
								</div>
								<?php wp_nonce_field( 'ajax-login-nonce', 'login-security' ); ?>
							</form>
							<div class="wpst-errors"></div>
						</div>

						<!-- Lost Password form -->
						<div class="wpst-reset-password">							 
							<h3><?php esc_html_e('Reset Password', 'wpst'); ?></h3>
							<p><?php esc_html_e('Enter the username or e-mail you used in your profile. A password reset link will be sent to you by email.', 'wpst'); ?></p>
						
							<form id="wpst_reset_password_form" action="<?php echo home_url( '/' ); ?>" method="post">
								<div class="form-field">
									<label for="wpst_user_or_email"><?php esc_html_e('Username or E-mail', 'wpst') ?></label>
									<input class="form-control input-lg required" name="wpst_user_or_email" id="wpst_user_or_email" type="text"/>
								</div>
								<div class="form-field">
									<input type="hidden" name="action" value="wpst_reset_password"/>
									<button class="btn btn-theme btn-lg" data-loading-text="<?php esc_html_e('Loading...', 'wpst') ?>" type="submit"><?php esc_html_e('Get new password', 'wpst'); ?></button>
								</div>
								<?php wp_nonce_field( 'ajax-login-nonce', 'password-security' ); ?>
							</form>
							<div class="wpst-errors"></div>
						</div>

						<div class="wpst-loading">
							<p><i class="fa fa-refresh fa-spin"></i><br><?php esc_html_e('Loading...', 'wpst') ?></p>
						</div>
					</div>
					<div class="modal-footer">
						<span class="wpst-register-footer"><?php esc_html_e('Don\'t have an account?', 'wpst'); ?> <a href="#wpst-register"><?php esc_html_e('Sign up', 'wpst'); ?></a></span>
						<span class="wpst-login-footer"><?php esc_html_e('Already have an account?', 'wpst'); ?> <a href="#wpst-login"><?php esc_html_e('Login', 'wpst'); ?></a></span>
					</div>				
				</div>
			</div>
		</div>
<?php
	}
}
add_action('wp_footer', 'wpst_login_register_modal');

# 	
# 	AJAX FUNCTION
# 	========================================================================================
#   These function handle the submitted data from the login/register modal forms
# 	========================================================================================
# 		

// LOGIN
function wpst_login_member(){

	// Get variables
	$user_login		= $_POST['wpst_user_login'];	
	$user_pass		= $_POST['wpst_user_pass'];


	// Check CSRF token
	if( !check_ajax_referer( 'ajax-login-nonce', 'login-security', false) ){
		echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">' . esc_html__('Session token has expired, please reload the page and try again', 'wpst') . '</div>'));
	}
	
	// Check if input variables are empty
	elseif( empty($user_login) || empty($user_pass) ){
		echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">' . esc_html__('Please fill all form fields', 'wpst') . '</div>'));
	} else { // Now we can insert this account

		$user = wp_signon( array('user_login' => $user_login, 'user_password' => $user_pass), false );

		if( is_wp_error($user) ){
			echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">' . str_replace('Lost your password?', '', $user->get_error_message()) . '</div>'));
		} else{
			echo json_encode(array('error' => false, 'message'=> '<div class="alert alert-success">' . esc_html__('Login successful, reloading page...', 'wpst') . '</div>'));
		}
	}

	die();
}
add_action('wp_ajax_nopriv_wpst_login_member', 'wpst_login_member');



// REGISTER
function wpst_register_member(){
	$siteKey = xbox_get_field_value( 'wpst-options', 'recaptcha-site-key' );
	$secret = xbox_get_field_value( 'wpst-options', 'recaptcha-secret-key' );

	// Get variables
	$user_login	= $_POST['wpst_user_login'];	
	$user_email	= $_POST['wpst_user_email'];
	$user_pass	= $_POST['wpst_user_pass'];
	
	// Check CSRF token
	if( !check_ajax_referer( 'ajax-login-nonce', 'register-security', false) ){
		echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">' . esc_html__('Session token has expired, please reload the page and try again', 'wpst') . '</div>'));
		die();
	}
	
	// Check if input variables are empty
	elseif( empty($user_login) || empty($user_email) || empty($user_pass) ){
		echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">' . esc_html__('Please fill all form fields', 'wpst') . '</div>'));
		die();
	}
	
	if ( xbox_get_field_value( 'wpst-options', 'enable-recaptcha' ) == 'on' && $siteKey != '' && $secret != '' ){
		if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])){
			$captcha = urlencode($_POST['g-recaptcha-response']);
//get verify response data
			$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $captcha);
			$responseData = json_decode($verifyResponse);
			if($responseData->success){
				$new_user_id = wp_insert_user(array(
					'user_login'		=> $user_login,
					'user_pass'	 		=> $user_pass,
					'user_email'		=> $user_email,
					'user_registered'	=> date('Y-m-d H:i:s'),
					'role'				=> 'subscriber'
					)
				);
				echo json_encode(array('error' => false, 'message' => '<div class="alert alert-success">' . esc_html__( 'Registration complete. You can now login.', 'wpst')));
			}else{
				echo json_encode(array('error' => true, 'message' => '<div class="alert alert-danger">' . esc_html__( 'Captcha verification failed, please try again.', 'wpst')));
			}
		}else{
			echo json_encode(array('error' => true, 'message' => '<div class="alert alert-danger">' . esc_html__( 'Please click on the reCAPTCHA box.', 'wpst')));
		}
	}else{
		$new_user_id = wp_insert_user(array(
			'user_login'		=> $user_login,
			'user_pass'	 		=> $user_pass,
			'user_email'		=> $user_email,
			'user_registered'	=> date('Y-m-d H:i:s'),
			'role'				=> 'subscriber'
			)
		);
		if( is_wp_error($new_user_id) ){
			$registration_error_messages = $new_user_id->new_user_id;
			$display_errors = '<div class="alert alert-danger">';
			
			foreach($registration_error_messages as $error){
				$display_errors .= '<p>'.$error[0].'</p>';
			}
			$display_errors .= '</div>';
			echo json_encode(array('error' => true, 'message' => $display_errors));
		}else{
			echo json_encode(array('error' => false, 'message' => '<div class="alert alert-success">' . esc_html__( 'Registration complete. You can now login.', 'wpst')));
		}
	}
	die();
}
add_action('wp_ajax_nopriv_wpst_register_member', 'wpst_register_member');


// RESET PASSWORD
function wpst_reset_password(){
		
  		// Get variables
		$username_or_email = $_POST['wpst_user_or_email'];

		// Check CSRF token
		if( !check_ajax_referer( 'ajax-login-nonce', 'password-security', false) ){
			echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">' . esc_html__('Session token has expired, please reload the page and try again', 'wpst') . '</div>'));
		}		

	 	// Check if input variables are empty
	 	elseif( empty($username_or_email) ){
			echo json_encode(array('error' => true, 'message'=> '<div class="alert alert-danger">' . esc_html__('Please fill all form fields', 'wpst') . '</div>'));
	 	} else {

			$username = is_email($username_or_email) ? sanitize_email($username_or_email) : sanitize_user($username_or_email);

			$user_forgotten = wpst_lostPassword_retrieve($username);
			
			if( is_wp_error($user_forgotten) ){
			
				$lostpass_error_messages = $user_forgotten->errors;

				$display_errors = '<div class="alert alert-warning">';
				foreach($lostpass_error_messages as $error){
					$display_errors .= '<p>'.$error[0].'</p>';
				}
				$display_errors .= '</div>';
				
				echo json_encode(array('error' => true, 'message' => $display_errors));
			}else{
				echo json_encode(array('error' => false, 'message' => '<p class="alert alert-success">' . esc_html__('Password Reset. Please check your email.', 'wpst')));
			}
	 	}

	 	die();
}	
add_action('wp_ajax_nopriv_wpst_reset_password', 'wpst_reset_password');


function wpst_lostPassword_retrieve( $user_data ) {
		
		global $wpdb, $current_site, $wp_hasher;

		$errors = new WP_Error();

		if( empty($user_data) ){
			$errors->add( 'empty_username', esc_html__( 'Please enter a username or e-mail address.', 'wpst' ) );
		} elseif( strpos($user_data, '@') ){
			$user_data = get_user_by( 'email', trim( $user_data ) );
			if( empty($user_data)){
				$errors->add( 'invalid_email', esc_html__( 'There is no user registered with that email address.', 'wpst'  ) );
			}
		} else {
			$login = trim( $user_data );
			$user_data = get_user_by('login', $login);
		}

		if( $errors->get_error_code() ){
			return $errors;
		}

		if( !$user_data ){
			$errors->add('invalidcombo', esc_html__('Invalid username or e-mail.', 'wpst'));
			return $errors;
		}

		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action('retrieve_password', $user_login);

		$allow = apply_filters('allow_password_reset', true, $user_data->ID);

		if( !$allow ){
			return new WP_Error( 'no_password_reset', esc_html__( 'Password reset is not allowed for this user', 'wpst' ) );
		} elseif ( is_wp_error($allow) ){
			return $allow;
		}

		$key = wp_generate_password(20, false);

		do_action('retrieve_password_key', $user_login, $key);

		if(empty($wp_hasher)){
			require_once ABSPATH.'wp-includes/class-phpass.php';
			$wp_hasher = new PasswordHash(8, true);
		}

		$hashed = $wp_hasher->HashPassword($key);

		$wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));
		
		$message = esc_html__('Someone requested that the password be reset for the following account:', 'wpst' ) . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'wpst' ), $user_login ) . "\r\n\r\n";
		$message .= esc_html__('If this was a mistake, just ignore this email and nothing will happen.', 'wpst' ) . "\r\n\r\n";
		$message .= esc_html__('To reset your password, visit the following address:', 'wpst' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n\r\n";
		
		if ( is_multisite() ) {
			$blogname = $GLOBALS['current_site']->site_name;
		} else {
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$title   = sprintf( __( '[%s] Password Reset', 'wpst' ), $blogname );
		$title   = apply_filters( 'retrieve_password_title', $title );
		$message = apply_filters( 'retrieve_password_message', $message, $key );

		if ( $message && ! wp_mail( $user_email, $title, $message ) ) {
			$errors->add( 'noemail', esc_html__( 'The e-mail could not be sent.<br />Possible reason: your host may have disabled the mail() function.', 'wpst' ) );

			return $errors;

			wp_die();
		}

		return true;
}

/**
 * Automatically add a Login link to Primary Menu
 */
/*add_filter( 'wp_nav_menu_items', 'wpst_login_link_to_menu', 10, 2 );
function wpst_login_link_to_menu ( $items, $args ) {
    if( ! is_user_logged_in() && $args->theme_location == apply_filters('login_menu_location', 'primary') ) {
        $items .= '<li class="menu-item login-link"><a href="#wpst-login">' . esc_html__( 'Login/Register', 'wpst' ) . '</a></li>';
    }
    return $items;
}*/

// Registration bar

<?php if ( 'on' === xbox_get_field_value( 'wpst-options', 'show-social-profiles' ) || 'on' === xbox_get_field_value( 'wpst-options', 'enable-membership' ) ) : ?>
	<div class="top-bar <?php if ( 'boxed' === xbox_get_field_value( 'wpst-options', 'layout' ) ) : ?>br-top-10<?php endif; ?>">
		<div class="top-bar-content row">
			<div class="social-share">
				<?php if ( 'on' === xbox_get_field_value( 'wpst-options', 'show-social-profiles' ) ) : ?>
					<?php if ( '' !== xbox_get_field_value( 'wpst-options', 'social-profiles-text' ) ) : ?>
						<small><?php echo esc_html( xbox_get_field_value( 'wpst-options', 'social-profiles-text' ) ); ?></small>
					<?php endif; ?>
					<?php if ( '' !== xbox_get_field_value( 'wpst-options', 'facebook-profile' ) ) : ?>
						<a href="<?php echo esc_url( xbox_get_field_value( 'wpst-options', 'facebook-profile' ) ); ?>" target="_blank"><i class="fa fa-facebook"></i></a>
					<?php endif; ?>
					<?php if ( '' !== xbox_get_field_value( 'wpst-options', 'google-plus-profile' ) ) : ?>
						<a href="<?php echo esc_url( xbox_get_field_value( 'wpst-options', 'google-plus-profile' ) ); ?>" target="_blank"><i class="fa fa-google-plus"></i></a>
					<?php endif; ?>
					<?php if ( '' !== xbox_get_field_value( 'wpst-options', 'instagram-profile' ) ) : ?>
						<a href="<?php echo esc_url( xbox_get_field_value( 'wpst-options', 'instagram-profile' ) ); ?>" target="_blank"><i class="fa fa-instagram"></i></a>
					<?php endif; ?>
					<?php if ( '' !== xbox_get_field_value( 'wpst-options', 'reddit-profile' ) ) : ?>
						<a href="<?php echo esc_url( xbox_get_field_value( 'wpst-options', 'reddit-profile' ) ); ?>" target="_blank"><i class="fa fa-reddit"></i></a>
					<?php endif; ?>
					<?php if ( '' !== xbox_get_field_value( 'wpst-options', 'tumblr-profile' ) ) : ?>
						<a href="<?php echo esc_url( xbox_get_field_value( 'wpst-options', 'tumblr-profile' ) ); ?>" target="_blank"><i class="fa fa-tumblr"></i></a>
					<?php endif; ?>
					<?php if ( '' !== xbox_get_field_value( 'wpst-options', 'twitter-profile' ) ) : ?>
						<a href="<?php echo esc_url( xbox_get_field_value( 'wpst-options', 'twitter-profile' ) ); ?>" target="_blank"><i class="fa fa-twitter"></i></a>
					<?php endif; ?>
					<?php if ( '' !== xbox_get_field_value( 'wpst-options', 'youtube-profile' ) ) : ?>
						<a href="<?php echo esc_url( xbox_get_field_value( 'wpst-options', 'youtube-profile' ) ); ?>" target="_blank"><i class="fa fa-youtube"></i></a>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php if ( 'on' === xbox_get_field_value( 'wpst-options', 'enable-membership' ) ) : ?>
				<div class="membership">
					<?php if ( is_user_logged_in() ) : ?>
						<span class="welcome"><i class="fa fa-user"></i> <?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
						<?php if ( 'on' === xbox_get_field_value( 'wpst-options', 'display-video-submit-link' ) ) : ?>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>submit-a-video"><i class="fa fa-upload"></i> <span class="topbar-item-text"><?php esc_html_e( 'Submit a Video', 'wpst' ); ?></span></a>
						<?php endif; ?>
						<?php if ( 'on' === xbox_get_field_value( 'wpst-options', 'display-my-channel-link' ) ) : ?>
							<a href="<?php echo esc_url( get_author_posts_url( get_current_user_id() ) ); ?>"><i class="fa fa-video-camera"></i> <span class="topbar-item-text"><?php esc_html_e( 'My Channel', 'wpst' ); ?></span></a>
						<?php endif; ?>
						<?php if ( 'on' === xbox_get_field_value( 'wpst-options', 'display-my-profile-link' ) ) : ?>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>my-profile"><i class="fa fa-user"></i> <span class="topbar-item-text"><?php esc_html_e( 'My Profile', 'wpst' ); ?></span></a>
						<?php endif; ?>
						<a href="<?php echo esc_url( wp_logout_url( is_home() ? home_url() : get_permalink() ) ); ?>"><i class="fa fa-power-off"></i> <span class="topbar-item-text"><?php esc_html_e( 'Logout', 'wpst' ); ?></span></a>
					<?php else : ?>
						<span class="welcome"><i class="fa fa-user"></i> <span><?php esc_html_e( 'Welcome Guest', 'wpst' ); ?></span></span>
						<span class="login"><a href="#wpst-login"><?php esc_html_e( 'Login', 'wpst' ); ?></a></span>
						<span class="or"><?php esc_html_e( 'Or', 'wpst' ); ?></span>
						<span class="login"><a href="#wpst-register"><?php esc_html_e( 'Register', 'wpst' ); ?></a></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php
endif;

