<div class="wrap ebr-ad-main" id="eBuyersReviewed-main">
		<h1><?php _e('eBuyersReviewed Settings', 'ebuyers-reviewed'); ?></h1>
		<?php
		$ebuyer_opts = get_option( 'ebuyer_opts' );
		//settings_errors('ebuyer_opts');
		if($validation) { ?>
			<div style="display:block" class="error-container wrap container-data-<?php echo $validation['status']; ?>">
				<?php echo "<p>".$validation['msg']."</p>"; ?>
			</div>
		<?php } else { ?>
		<div class="error-container wrap container-data-error"></div>
		<?php } ?>
		<table cellspacing="0" cellpadding="0" border="0" class="form-table">
			<tbody>
				<tr>
					<td class="tdform1">
						<form id="ebuyers_sign_up_form" method="post" action="">
							<!-- Sign Up Form -->
							<?php if(!isset( $ebuyer_opts['api_key'] )) { ?>
							<table cellspacing="0" cellpadding="0" border="0" width="100%">
								<tbody>
										<tr><th><?php _e("Sign Up.", "ebuyers-reviewed"); ?></th></tr>
									<tr>
										<td>
											<label><?php _e('Your Email', 'ebuyers-reviewed'); ?></label>
											<input required title="<?php _e('Enter Valid E-mail.', 'ebuyers-reviewed'); ?>" type="email" name="ebuyer_opts[buyer_email]" value="<?php if (isset( $_POST['ebuyer_opts']['buyer_email']) && ($_POST['ebuyer_opts']['buyer_email'] != '')) { echo $_POST['ebuyer_opts']['buyer_email']; } ?>">
										</td>
										<td>
											<label><?php _e('Password', 'ebuyers-reviewed'); ?></label>
											<input required title="<?php _e('Password should be at least 8 characters.', 'ebuyers-reviewed'); ?>" minlength="8" type="password" name="ebuyer_opts[buyer_pass]" value="<?php if (isset( $_POST['ebuyer_opts']['buyer_pass']) && ($_POST['ebuyer_opts']['buyer_pass'] != '')) { echo $_POST['ebuyer_opts']['buyer_pass']; } ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label><?php _e('First Name', 'ebuyers-reviewed'); ?></label>
											<input title="<?php _e('First Name is required.', 'ebuyers-reviewed'); ?>" required type="text" name="ebuyer_opts[buyer_first_name]" value="<?php if (isset( $_POST['ebuyer_opts']['buyer_first_name']) && ($_POST['ebuyer_opts']['buyer_first_name'] != '')) { echo $_POST['ebuyer_opts']['buyer_first_name']; } ?>">
										</td>
										<td>
											<label><?php _e('Last Name', 'ebuyers-reviewed'); ?></label>
											<input title="<?php _e('Last Name is required.', 'ebuyers-reviewed'); ?>" required type="text" name="ebuyer_opts[buyer_last_name]" value="<?php if (isset( $_POST['ebuyer_opts']['buyer_last_name']) && ($_POST['ebuyer_opts']['buyer_last_name'] != '')) { echo $_POST['ebuyer_opts']['buyer_last_name']; } ?>">
										</td>
									</tr>
									<tr>
										<td>
											<label><?php _e('Company Name ', 'ebuyers-reviewed'); ?><small><?php _e('(optional)', 'ebuyers-reviewed'); ?></small></label>
											<input type="text" name="ebuyer_opts[buyer_company_name]" value="<?php if (isset( $_POST['ebuyer_opts']['buyer_company_name']) && ($_POST['ebuyer_opts']['buyer_company_name'] != '')) { echo $_POST['ebuyer_opts']['buyer_company_name']; } ?>">
										</td>
										<td><input type="hidden" name="sign_up" value="sign_up"></td>
									</tr>
									<tr>
										<td>
											<label><input <?php if (isset( $_POST['ebuyer_opts']['terms_conditions']) && ($_POST['ebuyer_opts']['terms_conditions'] == '1')) { echo 'checked="checked"'; } ?> value="1" name="ebuyer_opts[terms_conditions]" type="checkbox" title="<?php _e('Terms of Service must be accepted.', 'ebuyers-reviewed'); ?>" class="required agreebox"> <?php printf(__('I agree with the <a class="js-open-modal" href="#" data-modal-id="popup1">Terms of Service.</a>', 'ebuyers-reviewed')); ?></label>
										</td>
									</tr>
									<tr>
										<td>
											<input type="submit" class="button btnform" value="<?php _e('Sign Up >', 'ebuyers-reviewed'); ?>">
										</td>
										<td></td>
									</tr>
								</tbody>
							</table>
							<?php } ?>
							
							<!-- Update Profile Form -->
							
							<?php if(isset( $ebuyer_opts['api_key'] )) { ?>
							<table cellspacing="0" cellpadding="0" border="0" width="100%">
								<tbody>
									<tr>
										<td>
											<label><?php _e('Your Email', 'ebuyers-reviewed'); ?></label>
											<input required title="<?php _e('Enter Valid E-mail.', 'ebuyers-reviewed'); ?>" type="email" name="ebuyer_opts[buyer_email]" value="<?php if(isset( $ebuyer_opts['buyer_email'] ) && ( $ebuyer_opts['buyer_email'] != '')) { echo  esc_attr( $ebuyer_opts['buyer_email']); } ?>">
										</td>
										<td>
											<!-- class="validate[required,minSize[8]]" -->
											<label><?php _e('Current Password', 'ebuyers-reviewed'); ?></label>
											<input type="password" name="ebuyer_opts[buyer_pass]" value="">
										</td>
									</tr>
									<tr>
										<td>
											<label><?php _e('First Name', 'ebuyers-reviewed'); ?></label>
											<input required title="<?php _e('First Name is required.', 'ebuyers-reviewed'); ?>" type="text" name="ebuyer_opts[buyer_first_name]" value="<?php if(isset( $ebuyer_opts['buyer_first_name'] ) && ( $ebuyer_opts['buyer_first_name'] != '')) { echo  esc_attr( $ebuyer_opts['buyer_first_name']); } ?>">
				
										</td>
										<td>
											<label><?php _e('Enter New Password', 'ebuyers-reviewed'); ?></label>
											<input id="new_pass" type="password" name="ebuyer_opts[new_buyer_pass]" value="">
				
										</td>
									</tr>
									<tr>
										<td>
											<label><?php _e('Last Name', 'ebuyers-reviewed'); ?></label>
											<input required title="<?php _e('Last Name is required.', 'ebuyers-reviewed'); ?>" type="text" name="ebuyer_opts[buyer_last_name]" value="<?php if(isset( $ebuyer_opts['buyer_last_name'] ) && ( $ebuyer_opts['buyer_last_name'] != '')) { echo  esc_attr( $ebuyer_opts['buyer_last_name']); } ?>">
				
										</td>
										<td>
											<label><?php _e('Confirm New Password', 'ebuyers-reviewed'); ?></label>
											<input type="password" name="ebuyer_opts[confirm_new_buyer_pass]" value="">
										</td>
									</tr>
									<tr>
										<td>
											<label><?php _e('Company Name ', 'ebuyers-reviewed'); ?><small><?php _e('(optional)', 'ebuyers-reviewed'); ?></small></label>
											<input type="text" name="ebuyer_opts[buyer_company_name]" value="<?php if(isset( $ebuyer_opts['buyer_company_name'] ) && ( $ebuyer_opts['buyer_company_name'] ) != '') { echo  esc_attr( $ebuyer_opts['buyer_company_name']); } ?>">
										</td>
										<td><input type="hidden" name="update_user" value="update_user"></td>
									</tr>
									<tr>
										<td><br /><br /></td>
									</tr>
									<tr>
										<td>
											<input type="submit" class="button btnform" value="<?php _e('Save Changes', 'ebuyers-reviewed'); ?>">
										</td>
										<td></td>
									</tr>
								</tbody>
							</table>
							<?php } ?>
						</form>
					</td>
					
					<!-- Login Form -->
					
					<?php if(!isset( $ebuyer_opts['api_key'] )) { ?>
					
					<td class="tdform2"><span><?php _e('or', 'ebuyers-reviewed'); ?></span></td>
					<td class="tdform3">
						<form id="ebuyers_sign_in_form" method="post" action="">
							<table cellspacing="0" cellpadding="0" border="0" width="100%">
								<tbody>
									<tr><th><?php _e('Already a Member? Log in here.', 'ebuyers-reviewed'); ?></th></tr>
									<tr>
										<td>
											<label><?php _e('Your Email', 'ebuyers-reviewed'); ?></label>
											<input required title="<?php _e('Enter Valid E-mail.', 'ebuyers-reviewed'); ?>" type="email" name="ebuyer_opts[buyer_email]" value="<?php if (isset( $_POST['ebuyer_opts']['buyer_email']) && ($_POST['ebuyer_opts']['buyer_email'] != '')) { echo $_POST['ebuyer_opts']['buyer_email']; } ?>">
										</td>
										<td>
											<label><?php _e('Password', 'ebuyers-reviewed'); ?></label>
											<input required title="<?php _e('Enter Password.', 'ebuyers-reviewed'); ?>" type="password" name="ebuyer_opts[buyer_pass]" value="">
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="sign_in" value="sign_in">
											<input type="submit" class="button btnform" value="<?php _e('Log in >', 'ebuyers-reviewed'); ?>">
										</td>
										<td></td>
									</tr>
									<tr>
										<td>
											<label>
												<a id="forgot_password_link" href="#"><?php _e('Forgot your password?', 'ebuyers-reviewed'); ?></a>
											</label>
										</td>
									<tr>
								</tbody>
							</table>
						</form>
						<!-- Forgot Password Form -->
						<form method="post" action="" id="forgot_password_form" style="display:none;">
							<table cellspacing="0" cellpadding="0" border="0" width="100%">
								<tbody>
									<tr>
										<td>
											<label><?php _e('Your Email', 'ebuyers-reviewed'); ?></label>
											<input required title="<?php _e('Enter Valid E-mail.', 'ebuyers-reviewed'); ?>" type="email" name="ebuyer_opts[buyer_email]" value="">
										</td>
									</tr>
									<tr>
										<td>
											<input type="hidden" name="forgot_password" value="forgot_password">
											<input type="submit" class="button btnform" value="<?php _e('Reset Password', 'ebuyers-reviewed'); ?>">
										</td>
										<td></td>
									</tr>
								</tbody>
							</table>
						</form>
					</td>
					<?php } else { ?>
						<td class="tdform3"></td>
						<td class="tdform3"></td>
					<?php } ?>
				</tr>
			</tbody>
		</table>
		<table cellspacing="0" cellpadding="0" border="0" width="40%" class="api-table">
			<tbody>
				<tr>
					<td><label><?php _e('eBuyersReviewed API KEY', 'ebuyers-reviewed'); ?></label></td>
				</tr>
				<tr>
					<td><input type="text" value="<?php if(isset( $ebuyer_opts['api_key'] ) && ( $ebuyer_opts['api_key'] != '')) { echo $ebuyer_opts['api_key']; } ?>" class="large-text" placeholder="<?php _e('API Key will be automatically generated after signing or logging.', 'ebuyers-reviewed'); ?>" readonly="true"></td>
				</tr>
				<?php if(isset( $ebuyer_opts['api_key'] )) { ?>
				<tr>
					<td>
						<!-- Log Out Form -->
						<form method="post" action="">
							<input type="hidden" name="logout" value="logout">
							<input type="submit" class="button btnform" value="<?php _e('Log Out', 'ebuyers-reviewed'); ?>">
						</form>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<table cellspacing="0" cellpadding="0" border="0" width="40%" class="api-table">
			<tbody>
				<tr>
					<td><label><a href="admin.php?page=ebuyers-reviewed-setup"><?php _e('How to use this plugin?', 'ebuyers-reviewed'); ?></a></label></td>
				</tr>
			</tbody>
		</table>
		<!-- Pop Up Content Start-->
	 
		<div id="popup1" class="modal-box">
		  	<header>
		  		<a class="js-modal-close close">×</a>
				<h3><?php _e('Terms of Service', 'ebuyers-reviewed'); ?></h3>
		  	</header>
		  	<div class="modal-body">
			<?php
				$url   = 'http://www.ebuyersreviewed.com/api/textPages/?page_code=terms_of_service';
				$response = $this->ebuyers_curl($url, '', '');
				if($response['data']) {
					echo $response['data'];
				}
			?>
		  	</div>
	  		<footer>
	  			<a class="btnform button js-modal-close"><?php _e('Close', 'ebuyers-reviewed'); ?></a>
			</footer>
		</div>
	
		<!-- Pop Up Content End-->
	</div>
