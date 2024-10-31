<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Ebuyers_Reviewed
 * @subpackage Ebuyers_Reviewed/admin/partials
 */
?>

<script type="text/javascript">
		var reviewTitles = ['Excellent buyer! No problems whatsoever!','Easy transaction! I highly recommend this buyer!','It was a pleasure dealing with this buyer!'];
	
		jQuery(document).ready(function() {

			// auth
			jQuery.ajax({
				type       : "GET",
				url        : "http://www.ebuyersreviewed.com/api/auth/",
				dataType   : "json",
				headers    : {
					"Accept-Language": "bg",
					"Accept"         : "json"
				},
				processData: true,
				data       : {
					"api_key" : "<?php echo $this->api_key; ?>"
				}
			}).done(function( returnedData, textStatus, jqXHRobj ) {
				if( returnedData.error ) {
					jQuery('#review-message').html('<div class="error">'+returnedData.error+'</div>').fadeIn(300).delay(6000).fadeOut(300);
				}
				else {
					jQuery('.logged_customer_name').html(returnedData.data.customer_name);
					jQuery('.logged_customer_membership span').html(returnedData.data.membership_name + ((returnedData.data.membership_expire)? " [expires:" + returnedData.data.membership_expire + "]":""));
					if( returnedData.data.test_mode == 1 ) {
						jQuery('.logged_customer').append('<div>Test mode</div>');
					}
					jQuery('.logged_customer').fadeIn(300);
				}
			}).fail(function( jqXHRobj, textStatus, errorThrown ) {
				jQuery('#review-message').html('<div class="error">'+jqXHRobj.responseJSON+'</div>').fadeIn(300).delay(6000).fadeOut(300);
			});

			// post review
			jQuery('#submitButton').on('click', function(event) {

				event.stopPropagation();
				event.preventDefault();
				jQuery("#submitButton").prop('disabled', true);
				jQuery('.ajax-loading').removeClass('display-none');
	
				var summaries = [];
				jQuery('input[name=summaries]:checked').each(function() {
					summaries.push( jQuery(this).val() );				
				});
					
				jQuery.ajax({
					type       : "POST",
					url        : "http://www.ebuyersreviewed.com/api/set/",
					dataType   : "json",
					headers    : {
						"Accept-Language": "bg",
						"Accept"         : "json"
					},
					processData: true,
					//processData: false,
					//contentType: false,
					//data       : formData
					data       : {
						"api_key"			: "<?php echo $this->api_key; ?>",
						"name"				: jQuery('#name').val(),
						"address"			: jQuery('#address').val(),
						"country"			: jQuery('#country').val(),
						"product_category": jQuery('#product_category').val(),
						"anonymous"			: jQuery('#anonymous:checked').val(),
						"notify_buyer"		: jQuery('#notify_buyer:checked').val(),
						"risk_level"		: jQuery('.star-rating.selected').attr('id'),
						"courier"			: jQuery('#courier').val(),
						"other_courier"	: jQuery('#other_courier').val(),
						"tracking"			: jQuery('#tracking').val(),
						"email"				: jQuery('#email').val(),
						"phone"				: jQuery('#phone').val(),
						"summaries"			: summaries,
						"title"				: jQuery('#review-title').val(),
						"details"			: jQuery('#details').val()
					}
				}).done(function( returnedData, textStatus, jqXHRobj ) {
					jQuery("#submitButton").prop('disabled', false);
					jQuery('.ajax-loading').addClass('display-none');
					if( returnedData.error ) {
						jQuery('#review-message').html('<div class="error">'+returnedData.error+'</div>').fadeIn(300);
						jQuery('#review-meta-box input[type="text"]').each(function() {
							if(jQuery(this).prev().hasClass('mb2')) {
								if(!(jQuery(this).val())) {
									jQuery(this).addClass('warning');
								}
							}
						});
						if (!jQuery("#address").val()) {
							console.log('asdf');
							jQuery("#address").addClass('warning');
						}
					}
					else {
						jQuery('#review-message').html('<div class="message">Thank you for submitting your review! It may take up to 24 hours for it appear on the site.</div>').fadeIn(300).delay(6000).fadeOut(300);

						// Update Wordpress Meta for user/order
						var data = {
							'action': 'update_customer_review',
							'order_id': ajax_review_object.order_id,
							'customer_id': ajax_review_object.customer_id,
							'risk_level' : jQuery('.star-rating.selected').attr('id'),
							'summaries' : summaries,
							'title' : jQuery('#review-title').val(),
							'details' : jQuery('#details').val()
						};
						jQuery.post(ajax_review_object.ajax_url, data, function(response) {
							console.log('Updated Customer/Post info: ' + response);
						});

						// Subtract from notification menu on admin toolbar
						jQuery(".notification-count").text(jQuery(".notification-count").text()-1)
						jQuery("#wp-admin-bar-ebuyer_reviewed_user_" + ajax_review_object.order_id).hide();

					}
				}).fail(function( jqXHRobj, textStatus, errorThrown ) {
					jQuery("#submitButton").prop('disabled', false);
					jQuery('.ajax-loading').addClass('display-none');
					jQuery('#review-message').html('<div class="error">'+jqXHRobj.responseJSON+'</div>').fadeIn(300).delay(6000).fadeOut(300);
				});
			});

			// Notify buyer by paid
			jQuery(".paid-notify").change(function() {
				if(this.checked) {
			    jQuery('.paid-warning').show();
				} else {
					if(!jQuery('#paid-notify-1').is(':checked') && !jQuery('#paid-notify-2').is(':checked')) {
						jQuery('.paid-warning').hide();
					}
				}
			});

			// Risk Level hover
			jQuery( ".star-rating" ).hover(
			  function() {
			  	if(this.id == '1') {
			  		jQuery( this ).removeClass('dark-red');
			  		jQuery('.star-rating').removeClass('dark-red');
			    	jQuery( this ).addClass('green');
			    } else {
			    	jQuery('.star-rating#1').removeClass('dark-green');
			    	var current_id = this.id;
			    	jQuery(".star-rating").each(function() { 
			    		if(this.id <= current_id)
						  	jQuery(this).addClass('red');
						  else
						  	jQuery(this).removeClass('dark-red');
						});
			    }
			  }, function() {
			  	if(this.id == '1') {
			    	jQuery( this ).removeClass('green');
						jQuery('.checklist input:checked').each(function() {
							jQuery(this).prop("checked",false);
						});
			    } else {
			    	var current_id = this.id;
			    	jQuery(".star-rating").each(function() { 
			    		if(this.id <= current_id)
						  	jQuery(this).removeClass('red');
						});
			    }
			  }
			);
			// Risk level click
			jQuery( ".star-rating" ).click(function() {
				jQuery('.star-rating').removeClass('selected');
				jQuery(this).addClass('selected');
			  if(this.id == '1') {
			  	jQuery('.star-rating').removeClass('dark-red');
			  	jQuery(this).addClass('dark-green');
			  } else {
			  	if(jQuery('#H').is(':checked')) {
			  		jQuery('#H').prop( "checked", false );
			  		jQuery('#review-title').val('');
			  	}
			  	jQuery('.star-rating#1').removeClass('dark-green');
			  	var current_id = this.id;
			  	jQuery(".star-rating").each(function() {
			  		if(this.id <= current_id)
			  			jQuery(this).addClass('dark-red');
			  		else
			  			jQuery(this).removeClass('dark-red');
			  	});
			  }
			});
			
			// tooltips
			jQuery('.explain').tooltip();

			// show other courier box on select
			changeCourier( jQuery('#courier').val() );
			jQuery('#courier').change(function() {
				changeCourier( jQuery(this).val() );
			});
			
			jQuery('select#country option[value="<?php echo $order->billing_country; ?>"]').attr("selected","selected");

			// How did the buyer do
			jQuery('.checklist input').on({
				change: function() {
					if( jQuery(this).prop('checked') && jQuery(this).val() == 'H' ) {
						jQuery('.star-rating').removeClass('selected');
						jQuery('.star-rating').removeClass('dark-red');
						jQuery('.star-rating#1').addClass('selected dark-green');
						jQuery('.checklist input:checked').each(function() {
							if( jQuery(this).attr('id') != 'H' ) jQuery(this).prop("checked",false);
						});
						if( reviewTitles.indexOf(jQuery('#review-title').val()) !== -1 || jQuery('#review-title').val()=='' ) {
							jQuery('#review-title').val(reviewTitles[randomIntFromInterval(0,2)]);
						}
					} 
					else {
						jQuery('.checklist input:checked').each(function() {
							if( jQuery(this).attr('id') == 'H' ) {
								jQuery(this).prop("checked",false);
							}
						});
						if( reviewTitles.indexOf(jQuery('#review-title').val()) !== -1 ) {
							jQuery('#review-title').val('');
						}
						if( jQuery('.checklist input:checked').length > 0 ) {
							if( jQuery('.star-rating#1').hasClass('selected') ) {
								jQuery('.star-rating#1').removeClass('selected');
								jQuery('.star-rating#1').removeClass('dark-green');
							}
							jQuery('.star-rating#1').addClass('dark-red');
							jQuery('.star-rating#2').addClass('selected dark-red');
						}
					}
				}
			});
			
			// remove special chars
			jQuery('input, textarea').not('#address, #name').keyup(function(e) {
				var regex = new RegExp(/[^A-Za-zІ0-9ÄäÖöÜüß~`!@#jQuery%^&*()_+-=\[\]{};'\\:"|,.\/<>?\s]/g);
				if(regex.test(jQuery(this).val())) {
					jQuery(this).val(jQuery(this).val().replace(regex, ''));
				}
			});
		});
	</script>

<div class="box-content">
	<div name="eBR" method="POST" action="">
		<div class="header">
			<div class="detailed-report-header">Review Your Buyer</div>
			<div class="logged_customer" style="display: block;">
				<div class="logged_customer_name"></div>
				<div class="logged_customer_membership">Membership: <span></span></div>
			</div>
		</div>
		<div id="review-message"></div>
		<div class="container set_form">
			<div class="column">
				<label>
					<div class="mb2">Name</div>
					<input type="text" name="name" id="name" value="<?php echo $order->get_formatted_billing_full_name(); ?>">
				</label>
				<label>
					<div class="mb2">Shipping address <a href="javascript:;" class="explain" data-toggle="tooltip" data-placement="right" data-original-title="Enter complete shipping address as provided by buyer (e.g. 27 Westmount Dr Livingston NJ 07039)."><small>Explain?</small></a></div>
					<textarea name="address" id="address"><?php echo $order->billing_address_1 . ' ' . $order->billing_city . ', ' . $order->billing_postcode; ?></textarea>
				</label>
				<label>
					<div class="mb2">Country</div>
					<select name="country" id="country" class="chosen chzn-done">
					  <option value="AF" class="flag-AF" key="AF">Afganistan</option>
					  <option value="AL" class="flag-AL" key="AL">Albania</option>
					  <option value="DZ" class="flag-DZ" key="DZ">Algeria</option>
					  <option value="AS" class="flag-AS" key="AS">American Samoa</option>
					  <option value="AD" class="flag-AD" key="AD">Andorra</option>
					  <option value="AO" class="flag-AO" key="AO">Angola</option>
					  <option value="AI" class="flag-AI" key="AI">Anguilla</option>
					  <option value="AG" class="flag-AG" key="AG">Antigua</option>
					  <option value="AR" class="flag-AR" key="AR">Argentina</option>
					  <option value="AM" class="flag-AM" key="AM">Armenia</option>
					  <option value="AW" class="flag-AW" key="AW">Aruba</option>
					  <option value="AU" class="flag-AU" key="AU">Australia</option>
					  <option value="AT" class="flag-AT" key="AT">Austria</option>
					  <option value="AZ" class="flag-AZ" key="AZ">Azerbaijan</option>
					  <option value="BS" class="flag-BS" key="BS">Bahamas</option>
					  <option value="BH" class="flag-BH" key="BH">Bahrain</option>
					  <option value="BD" class="flag-BD" key="BD">Bangladesh</option>
					  <option value="BB" class="flag-BB" key="BB">Barbados</option>
					  <option value="BY" class="flag-BY" key="BY">Belarus</option>
					  <option value="BE" class="flag-BE" key="BE">Belgium</option>
					  <option value="BZ" class="flag-BZ" key="BZ">Belize</option>
					  <option value="BJ" class="flag-BJ" key="BJ">Benin</option>
					  <option value="BM" class="flag-BM" key="BM">Bermuda</option>
					  <option value="BT" class="flag-BT" key="BT">Bhutan</option>
					  <option value="BO" class="flag-BO" key="BO">Bolivia</option>
					  <option value="BL" class="flag-BL" key="BL">Bonaire (Netherlands Antilles)</option>
					  <option value="BA" class="flag-BA" key="BA">Bosnia Herzegovina</option>
					  <option value="BW" class="flag-BW" key="BW">Botswana</option>
					  <option value="BR" class="flag-BR" key="BR">Brazil</option>
					  <option value="BN" class="flag-BN" key="BN">Brunei</option>
					  <option value="BG" class="flag-BG" key="BG">Bulgaria</option>
					  <option value="BF" class="flag-BF" key="BF">Burkina Faso</option>
					  <option value="BI" class="flag-BI" key="BI">Burundi</option>
					  <option value="KH" class="flag-KH" key="KH">Cambodia</option>
					  <option value="CM" class="flag-CM" key="CM">Cameroon</option>
					  <option value="CA" class="flag-CA" key="CA">Canada</option>
					  <option value="CV" class="flag-CV" key="CV">Cape Verde</option>
					  <option value="KY" class="flag-KY" key="KY">Cayman Islands</option>
					  <option value="TD" class="flag-TD" key="TD">Chad</option>
					  <option value="CL" class="flag-CL" key="CL">Chile</option>
					  <option value="CN" class="flag-CN" key="CN">China</option>
					  <option value="CO" class="flag-CO" key="CO">Colombia</option>
					  <option value="CG" class="flag-CG" key="CG">Congo</option>
					  <option value="CK" class="flag-CK" key="CK">Cook Islands</option>
					  <option value="CR" class="flag-CR" key="CR">Costa Rica</option>
					  <option value="HR" class="flag-HR" key="HR">Croatia</option>
					  <option value="CB" class="flag-CB" key="CB">Curacao (Netherlands Antilles)</option>
					  <option value="CY" class="flag-CY" key="CY">Cyprus</option>
					  <option value="CZ" class="flag-CZ" key="CZ">Czech Republic</option>
					  <option value="DK" class="flag-DK" key="DK">Denmark</option>
					  <option value="DJ" class="flag-DJ" key="DJ">Djibouti</option>
					  <option value="DM" class="flag-DM" key="DM">Dominica</option>
					  <option value="DO" class="flag-DO" key="DO">Dominican Republic</option>
					  <option value="EC" class="flag-EC" key="EC">Ecuador</option>
					  <option value="EG" class="flag-EG" key="EG">Egypt</option>
					  <option value="SV" class="flag-SV" key="SV">El Salvador</option>
					  <option value="ER" class="flag-ER" key="ER">Eritrea</option>
					  <option value="EE" class="flag-EE" key="EE">Estonia</option>
					  <option value="ET" class="flag-ET" key="ET">Ethiopia</option>
					  <option value="FJ" class="flag-FJ" key="FJ">Fiji</option>
					  <option value="FI" class="flag-FI" key="FI">Finland</option>
					  <option value="FR" class="flag-FR" key="FR">France</option>
					  <option value="GF" class="flag-GF" key="GF">French Guiana</option>
					  <option value="PF" class="flag-PF" key="PF">French Polynesia</option>
					  <option value="GA" class="flag-GA" key="GA">Gabon</option>
					  <option value="GM" class="flag-GM" key="GM">Gambia</option>
					  <option value="GE" class="flag-GE" key="GE">Georgia</option>
					  <option value="DE" class="flag-DE" key="DE">Germany</option>
					  <option value="GH" class="flag-GH" key="GH">Ghana</option>
					  <option value="GI" class="flag-GI" key="GI">Gibraltar</option>
					  <option value="GR" class="flag-GR" key="GR">Greece</option>
					  <option value="GD" class="flag-GD" key="GD">Grenada</option>
					  <option value="GP" class="flag-GP" key="GP">Guadeloupe</option>
					  <option value="GU" class="flag-GU" key="GU">Guam</option>
					  <option value="GT" class="flag-GT" key="GT">Guatemala</option>
					  <option value="GN" class="flag-GN" key="GN">Guinea</option>
					  <option value="GW" class="flag-GW" key="GW">Guinea Bissau</option>
					  <option value="GY" class="flag-GY" key="GY">Guyana</option>
					  <option value="HT" class="flag-HT" key="HT">Haiti</option>
					  <option value="HN" class="flag-HN" key="HN">Honduras</option>
					  <option value="HK" class="flag-HK" key="HK">Hong Kong</option>
					  <option value="HU" class="flag-HU" key="HU">Hungary</option>
					  <option value="IS" class="flag-IS" key="IS">Iceland</option>
					  <option value="IN" class="flag-IN" key="IN">India</option>
					  <option value="ID" class="flag-ID" key="ID">Indonesia</option>
					  <option value="IR" class="flag-IR" key="IR">Iran</option>
					  <option value="IQ" class="flag-IQ" key="IQ">Iraq</option>
					  <option value="IE" class="flag-IE" key="IE">Ireland</option>
					  <option value="IL" class="flag-IL" key="IL">Israel</option>
					  <option value="IT" class="flag-IT" key="IT">Italy</option>
					  <option value="CI" class="flag-CI" key="CI">Ivory Coast</option>
					  <option value="JM" class="flag-JM" key="JM">Jamaica</option>
					  <option value="JP" class="flag-JP" key="JP">Japan</option>
					  <option value="JO" class="flag-JO" key="JO">Jordan</option>
					  <option value="KZ" class="flag-KZ" key="KZ">Kazakhstan</option>
					  <option value="KE" class="flag-KE" key="KE">Kenya</option>
					  <option value="KI" class="flag-KI" key="KI">Kiribati</option>
					  <option value="XK" class="flag-XK" key="XK">Kosovo</option>
					  <option value="XE" class="flag-XE" key="XE">Kosrae Island</option>
					  <option value="KW" class="flag-KW" key="KW">Kuwait</option>
					  <option value="KG" class="flag-KG" key="KG">Kyrgyzstan</option>
					  <option value="LA" class="flag-LA" key="LA">Laos</option>
					  <option value="LV" class="flag-LV" key="LV">Latvia</option>
					  <option value="LB" class="flag-LB" key="LB">Lebanon</option>
					  <option value="LS" class="flag-LS" key="LS">Lesotho</option>
					  <option value="LR" class="flag-LR" key="LR">Liberia</option>
					  <option value="LY" class="flag-LY" key="LY">Libya</option>
					  <option value="LI" class="flag-LI" key="LI">Liechtenstein</option>
					  <option value="LT" class="flag-LT" key="LT">Lithuania</option>
					  <option value="LU" class="flag-LU" key="LU">Luxembourg</option>
					  <option value="MO" class="flag-MO" key="MO">Macao</option>
					  <option value="MK" class="flag-MK" key="MK">Macedonia (FYROM)</option>
					  <option value="MG" class="flag-MG" key="MG">Madagascar</option>
					  <option value="MW" class="flag-MW" key="MW">Malawi</option>
					  <option value="MY" class="flag-MY" key="MY">Malaysia</option>
					  <option value="MV" class="flag-MV" key="MV">Maldives</option>
					  <option value="ML" class="flag-ML" key="ML">Mali</option>
					  <option value="MT" class="flag-MT" key="MT">Malta</option>
					  <option value="MH" class="flag-MH" key="MH">Marshall Islands</option>
					  <option value="MQ" class="flag-MQ" key="MQ">Martinique</option>
					  <option value="MR" class="flag-MR" key="MR">Mauritania</option>
					  <option value="MU" class="flag-MU" key="MU">Mauritius</option>
					  <option value="MX" class="flag-MX" key="MX">Mexico</option>
					  <option value="MD" class="flag-MD" key="MD">Moldova</option>
					  <option value="MC" class="flag-MC" key="MC">Monaco</option>
					  <option value="MN" class="flag-MN" key="MN">Mongolia</option>
					  <option value="ME" class="flag-ME" key="ME">Montenegro</option>
					  <option value="MS" class="flag-MS" key="MS">Montserrat</option>
					  <option value="MA" class="flag-MA" key="MA">Morocco</option>
					  <option value="MZ" class="flag-MZ" key="MZ">Mozambique</option>
					  <option value="NP" class="flag-NP" key="NP">Nepal</option>
					  <option value="NL" class="flag-NL" key="NL">Netherlands</option>
					  <option value="NC" class="flag-NC" key="NC">New Caledonia</option>
					  <option value="NZ" class="flag-NZ" key="NZ">New Zealand</option>
					  <option value="NI" class="flag-NI" key="NI">Nicaragua</option>
					  <option value="NE" class="flag-NE" key="NE">Niger</option>
					  <option value="NG" class="flag-NG" key="NG">Nigeria</option>
					  <option value="MP" class="flag-MP" key="MP">Northern Mariana Islands</option>
					  <option value="NO" class="flag-NO" key="NO">Norway</option>
					  <option value="OM" class="flag-OM" key="OM">Oman</option>
					  <option value="PK" class="flag-PK" key="PK">Pakistan</option>
					  <option value="PW" class="flag-PW" key="PW">Palau</option>
					  <option value="PA" class="flag-PA" key="PA">Panama</option>
					  <option value="PG" class="flag-PG" key="PG">Papua New Guinea</option>
					  <option value="PY" class="flag-PY" key="PY">Paraguay</option>
					  <option value="PE" class="flag-PE" key="PE">Peru</option>
					  <option value="PH" class="flag-PH" key="PH">Philippines</option>
					  <option value="PL" class="flag-PL" key="PL">Poland</option>
					  <option value="XP" class="flag-XP" key="XP">Ponape</option>
					  <option value="PT" class="flag-PT" key="PT">Portugal</option>
					  <option value="PR" class="flag-PR" key="PR">Puerto Rico</option>
					  <option value="QA" class="flag-QA" key="QA">Qatar</option>
					  <option value="RE" class="flag-RE" key="RE">Reunion</option>
					  <option value="RO" class="flag-RO" key="RO">Romania</option>
					  <option value="XC" class="flag-XC" key="XC">Rota</option>
					  <option value="RU" class="flag-RU" key="RU">Russia</option>
					  <option value="RW" class="flag-RW" key="RW">Rwanda</option>
					  <option value="XZ" class="flag-XZ" key="XZ">Saba (Netherlands Antilles)</option>
					  <option value="XS" class="flag-XS" key="XS">Saipan</option>
					  <option value="SM" class="flag-SM" key="SM">San Marino</option>
					  <option value="SA" class="flag-SA" key="SA">Saudi Arabia</option>
					  <option value="SN" class="flag-SN" key="SN">Senegal</option>
					  <option value="RS" class="flag-RS" key="RS">Serbia</option>
					  <option value="SC" class="flag-SC" key="SC">Seychelles</option>
					  <option value="SG" class="flag-SG" key="SG">Singapore</option>
					  <option value="SK" class="flag-SK" key="SK">Slovakia</option>
					  <option value="SI" class="flag-SI" key="SI">Slovenia</option>
					  <option value="SB" class="flag-SB" key="SB">Solomon Islands</option>
					  <option value="ZA" class="flag-ZA" key="ZA">South Africa</option>
					  <option value="KR" class="flag-KR" key="KR">South Korea</option>
					  <option value="ES" class="flag-ES" key="ES">Spain</option>
					  <option value="LK" class="flag-LK" key="LK">Sri Lanka</option>
					  <option value="NT" class="flag-NT" key="NT">St. Barthelemy</option>
					  <option value="EU" class="flag-EU" key="EU">St. Eustatius (Netherlands Antilles)</option>
					  <option value="KN" class="flag-KN" key="KN">St. Kitts and Nevis</option>
					  <option value="LC" class="flag-LC" key="LC">St. Lucia</option>
					  <option value="MB" class="flag-MB" key="MB">St. Maarten (Netherlands Antilles)</option>
					  <option value="SR" class="flag-SR" key="SR">Suriname</option>
					  <option value="SZ" class="flag-SZ" key="SZ">Swaziland</option>
					  <option value="SE" class="flag-SE" key="SE">Sweden</option>
					  <option value="CH" class="flag-CH" key="CH">Switzerland</option>
					  <option value="SY" class="flag-SY" key="SY">Syria</option>
					  <option value="TJ" class="flag-TJ" key="TJ">Tadjikistan</option>
					  <option value="TW" class="flag-TW" key="TW">Taiwan</option>
					  <option value="TZ" class="flag-TZ" key="TZ">Tanzania</option>
					  <option value="TH" class="flag-TH" key="TH">Thailand</option>
					  <option value="XN" class="flag-XN" key="XN">Tinian</option>
					  <option value="TG" class="flag-TG" key="TG">Togo</option>
					  <option value="TO" class="flag-TO" key="TO">Tonga</option>
					  <option value="TT" class="flag-TT" key="TT">Trinidad and Tobago</option>
					  <option value="XA" class="flag-XA" key="XA">Truk</option>
					  <option value="TN" class="flag-TN" key="TN">Tunisia</option>
					  <option value="TR" class="flag-TR" key="TR">Turkey</option>
					  <option value="TM" class="flag-TM" key="TM">Turkmenistan</option>
					  <option value="TC" class="flag-TC" key="TC">Turks and Caicos</option>
					  <option value="TV" class="flag-TV" key="TV">Tuvalu</option>
					  <option value="UG" class="flag-UG" key="UG">Uganda</option>
					  <option value="UA" class="flag-UA" key="UA">Ukraine</option>
					  <option value="VC" class="flag-VC" key="VC">Union Island</option>
					  <option value="AE" class="flag-AE" key="AE">United Arab Emirates</option>
					  <option value="GB" class="flag-GB" key="GB">United Kingdom</option>
					  <option value="US" class="flag-US" key="US">United States</option>
					  <option value="UY" class="flag-UY" key="UY">Uruguay</option>
					  <option value="VI" class="flag-VI" key="VI">US Virgin Islands</option>
					  <option value="UZ" class="flag-UZ" key="UZ">Uzbekistan</option>
					  <option value="VU" class="flag-VU" key="VU">Vanuatu</option>
					  <option value="VE" class="flag-VE" key="VE">Venezuela</option>
					  <option value="VN" class="flag-VN" key="VN">Vietnam</option>
					  <option value="VG" class="flag-VG" key="VG">Virgin Gorda</option>
					  <option value="WF" class="flag-WF" key="WF">Wallis and Futuna</option>
					  <option value="WS" class="flag-WS" key="WS">Western Samoa</option>
					  <option value="XY" class="flag-XY" key="XY">Yap</option>
					  <option value="YE" class="flag-YE" key="YE">Yemen</option>
					  <option value="ZM" class="flag-ZM" key="ZM">Zambia</option>
					  <option value="ZW" class="flag-ZW" key="ZW">Zimbabwe</option>
					</select>

				</label>
				<label>
					<div class="mb2">Product category</div>
					<select name="product_category" id="product_category" class="chosen chzn-done">
					  <option value="product_category_0">Books &amp; Audible</option>
					  <option value="product_category_1">Movies, Music &amp; Games</option>
					  <option value="product_category_2">Electronics &amp; Computers</option>
					  <option value="product_category_3">Home, Garden &amp;Tools</option>
					  <option value="product_category_4">Beauty, Health &amp; Grocery</option>
					  <option value="product_category_5">Toys, Kids &amp; Baby</option>
					  <option value="product_category_6">Clothing, Shoes &amp; Jewelry</option>
					  <option value="product_category_7">Sports &amp; Outdoors</option>
					  <option value="product_category_8">Automotive &amp; Industrial</option>
					  <option value="product_category_9">Art &amp; Collectables</option>
					  <option value="product_category_10">Other</option>
					</select>
				</label>
				<label>
					<input type="checkbox" style="padding: -2px 0 0 0 !important; margin: -4px 0 0 0;" value="1" name="anonymous" id="anonymous" checked="checked"> 
					Remain an anonymous author
				</label>
				<label>
					Notify this buyer by <br/>
					<input type="checkbox" style="padding: -2px 0 0 0 !important; margin: -4px 0 0 0;" value="1" name="notify_buyer" id="notify_buyer"> 
					Email <span class="bright-green">(free)</span>
				</label>
				<label>
					<input type="checkbox" style="padding: -2px 0 0 0 !important; margin: -4px 0 0 0;" value="1" class="paid-notify" name="paid-notify" id="paid-notify-1">
					SMS
				</label>
				<label>
					<input type="checkbox" style="padding: -2px 0 0 0 !important; margin: -4px 0 0 0;" value="1" class="paid-notify" name="paid-notify" id="paid-notify-2"> 
					Mail
				</label>
				<div class="paid-warning" style="display: none;">
					<p>To use the paid notification options please login to your account at <a href="http://www.ebuyersreviewed.com/en/login/Log-In-(Members).html" target="_blank">ebuyersreviewed.com</a></p>
				</div>
			</div>
			<div class="column">
				<div class="mb19">
					<div class="align-center">
						<div class="mb2">Assign risk level</div>
						<div class="rating">
 							<span class="star-rating-control">
 								<span class="star-rating r-1" id="1"></span>
 								<span class="star-rating r-2" id="2"></span>
 								<span class="star-rating r-3" id="3"></span>
 								<span class="star-rating r-4" id="4"></span>
 								<span class="star-rating r-5" id="5"></span>
							</span>
							<input id="risk_1" name="risk_level" type="radio" class="star star-rating-applied" value="1" title="low" style="display: none;">
							<input id="risk_2" name="risk_level" type="radio" class="star star-rating-applied" value="2" title="low-to-medium" style="display: none;">
							<input id="risk_3" name="risk_level" type="radio" class="star star-rating-applied" value="3" title="medium" style="display: none;">
							<input id="risk_4" name="risk_level" type="radio" class="star star-rating-applied" value="4" title="medium-to-high" style="display: none;">
							<input id="risk_5" name="risk_level" type="radio" class="star star-rating-applied" value="5" title="high" style="display: none;">
							<span class="sprite-lowhigh-full">
							</span> 
						</div>
					</div>
				</div>
				<label>
					<div class="mb2">Shipping method (Carrier)</div>
					<select name="courier" id="courier" class="" >
					  <option value="courier_0">USPS</option>
					  <option value="courier_1">UPS</option>
					  <option value="courier_2">FedEx</option>
					  <option value="courier_3">FedEx Ground</option>
					  <option value="courier_4">FedEx SmartPost</option>
					  <option value="courier_5">DHL</option>
					  <option value="courier_6">DHL Global Mail</option>
					  <option value="courier_7">UPS Mail Innovations</option>
					  <option value="courier_8">OSM</option>
					  <option value="courier_9">OnTrac</option>
					  <option value="courier_10">Streamlite</option>
					  <option value="courier_11">Newgistics</option>
					  <option value="courier_12">Blue Package</option>
					  <option value="courier_13">Canada Post</option>
					  <option value="courier_14">Other (specify)</option>
					  <option value="courier_15">Not Applicable</option>
					</select>

				</label>
				<label id="field-other-courirer" class="display-none" style="display: none;">
					<div class="mb2">Name of carrier</div>
					<input type="text" name="other_courier" id="other_courier" value="">
				</label>
				<label id="field-tracking">
					<div class="mb2">Tracking number <a href="javascript:;" class="explain" data-toggle="tooltip" data-placement="right" data-original-title="Reviews with verified tracking numbers qualify for earning free screening credits."><small>Explain?</small></a></div>
					<input type="text" name="tracking" id="tracking" value="">
				</label>
				<label>
					<div class="mb2">E-mail address*</div>
					<input type="text" name="email" id="email" value="<?php echo $order->billing_email; ?>">
				</label>
				<label>
					<div class="mb2">Telephone (last 4 digits)*</div>
					<div class="phone">
						<input type="text" name="phone" id="phone" value="<?php echo substr( str_replace(' ', '', $order->billing_phone), -4 ); ?>" maxlength="4">
					</div>
				</label>
			</div>
			<div class="column">
				<div class="checklist">
					<div class="mb2">How did the buyer do?</div>
					<label><input type="checkbox" name="summaries" id="A" value="A"> made unreasonable demands</label>
					<label><input type="checkbox" name="summaries" id="B" value="B"> gave unfair negative feedback</label>
					<label><input type="checkbox" name="summaries" id="C" value="C"> misused returns</label>
					<label><input type="checkbox" name="summaries" id="D" value="D"> mistakenly bought the wrong item (wrong size)</label>
					<label><input type="checkbox" name="summaries" id="E" value="E"> cancelled an order for no reason</label>
					<label><input type="checkbox" name="summaries" id="F" value="F"> provided wrong shipping address / was unreachable for delivery</label>
					<label><input type="checkbox" name="summaries" id="G" value="G"> other problems</label>
					<label><input type="checkbox" name="summaries" id="H" value="H"> no problems whatsoever</label>
				</div>
				<label>
					<div class="mb2">Enter a title for your review</div>
					<input type="text" name="review-title" id="review-title" value="">
				</label>
				<label>
					<div class="mb2">Give more details of your experience*</div>
					<textarea name="details" id="details"></textarea>
				</label>
			</div>
		</div>
		<br>
		<div class="optional">* Optional fields</div>
		<br>
		<button id="submitButton" type="submit">Submit</button>&nbsp;&nbsp;<img class="ajax-loading display-none" src="<?php echo plugin_dir_url( __FILE__ ); ?>/../../images/sloading.gif">
	</div>
</div>