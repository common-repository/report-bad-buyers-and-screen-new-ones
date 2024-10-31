<html><head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<script type="text/javascript">
		var customerData;
		var responseData;
		
		jQuery(document).ready(function() {

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
				customerData = returnedData.data;
				if( returnedData.error ) {
					jQuery('#ebr-message').html('<div class="error">'+returnedData.error+'</div>').fadeIn(300);
				}
			}).fail(function( jqXHRobj, textStatus, errorThrown ) {
				jQuery('#ebr-message').html('<div class="error">'+jqXHRobj.responseJSON+'</div>').fadeIn(300).delay(6000).fadeOut(300);
			});
			
			// tooltips
			jQuery('.explain').tooltip();
			
			// remove special chars
			jQuery('input, textarea').not('#address, #name').keyup(function(e) {
				var regex = new RegExp(/[^A-Za-zІ0-9ÄäÖöÜüß~`!@#jQuery%^&*()_+-=\[\]{};'\\:"|,.\/<>?\s]/g);
				if(regex.test(jQuery(this).val())) {
					jQuery(this).val(jQuery(this).val().replace(regex, ''));
				}
			});
			
		});
		
		// show collective items
		function showCollectiveItems() {
			var collectiveListing = '';
			collectiveListing += '<div class="collective-listing">';
			if( responseData.number_results < 10 ) {
				collectiveListing += '<div class="number-results">'+responseData.number_results+' record'+((parseInt(responseData.number_results)>1)? 's':'')+' matches your search.</div>';
			}
			collectiveListing += '<table class="tbl01">';
			collectiveListing += '<tr>';
			collectiveListing += '<th>Name</th>';
			collectiveListing += '<th>Shipping Information</th>';
			collectiveListing += '<th>Country</th>';
			collectiveListing += '<th>Risk Level</th>';
			collectiveListing += '<th>Date</th>';
			collectiveListing += '<th>&nbsp;</th>';
			collectiveListing += '</tr>';
			jQuery.each(responseData.results, function(k,v) {
				collectiveListing += '<tr>';
				collectiveListing += '<td>'+v.name+'</td>';
				collectiveListing += '<td>'+v.address+'</td>';
				collectiveListing += '<td>'+v.country_name+'</th>';
				collectiveListing += '<td><div class="risk-level-small rls-'+v.average_risk_level_value.toString().replace('.','')+'" title="'+v.average_risk_level_text+'"></div></td>';
				collectiveListing += '<td>'+v.date+'</td>';
				collectiveListing += '<td align="right"><a href="javascript:showItems('+k+')">See Review</a></td>';
				collectiveListing += '</tr>';		
			});
			collectiveListing += '</table>';
			collectiveListing += '</div>';
			
			jQuery('#ebr-results').html(collectiveListing);
			jQuery('html, body').animate({ scrollTop: jQuery('.collective-listing').offset().top }, 1000);
		}
		
		// show items
		function showItems( k ) {
			var reviewsListing = '';
			reviewsListing += '<div class="reviews-listing">';
			reviewsListing += '<div class="reviews-listing-title">Summary review for '+responseData.results[k].name+'</div>';
			reviewsListing += '<div class="reviews-listing-risk-level table"><div class="tr">';
			reviewsListing += '<div class="td">Risk Level: '+((parseInt(responseData.results[k].average_risk_level_value) == 0)? 'neutral':responseData.results[k].average_risk_level_value)+' &nbsp;&nbsp;</div>';
			reviewsListing += '<div class="td"><div class="risk-level-big rlb-'+responseData.results[k].average_risk_level_value.toString().replace('.','')+'" title="'+responseData.results[k].average_risk_level_text+'"></div></div>';
			reviewsListing += '</div></div>';
			reviewsListing += '<div class="reviews-listing-summary"><strong>'+responseData.results[k].summary+'</strong>'+((responseData.results[k].suggestion)? '<br /><br />'+responseData.results[k].suggestion:'')+'</div>';
			reviewsListing += '<table class="tbl01">';
			reviewsListing += '<tr>';
			reviewsListing += '<th>Date</th>';
			reviewsListing += '<th>Author</th>';
			reviewsListing += '<th>Review Title</th>';
			reviewsListing += '<th>Risk Level</th>';
			reviewsListing += '<th>&nbsp;</th>';
			reviewsListing += '</tr>';
			jQuery.each(responseData.results[k].items, function(k,v) {
				reviewsListing += '<tr>';
				reviewsListing += '<td>'+v.date+'</td>';
				reviewsListing += '<td>'+v.author+'</td>';
				reviewsListing += '<td>'+v.title+'</th>';
				reviewsListing += '<td><div class="risk-level-small rls-'+v.risk_level_value.toString().replace('.','')+'" title="'+v.risk_level_text+'"></div></td>';
				reviewsListing += '<td align="right"><a class="view-report" id="btn-'+k+'" href="javascript:fullReview('+k+')">Full Review</a></td>';
				reviewsListing += '</tr>';
				reviewsListing += '<tr>';
				reviewsListing += '<td class="reviews-listing-details" colspan="5">';
				reviewsListing += '<div id="detailed-report-'+k+'" class="detailed-report-container">';
				reviewsListing += '<div class="detailed-report">';
				reviewsListing += ((v.verified)? '<div class="detailed-report-seal-verified"></div>':'');
				reviewsListing += '<div class="detailed-report-header">Buyer Review</div>';
				reviewsListing += '<div class="detailed-report-col-left">';
				reviewsListing += '<div>'+v.date+'</div><br />';
				reviewsListing += '<div><strong>'+v.name+'</strong></div>';
				reviewsListing += '<div><strong>'+v.address+'</strong></div>';
				reviewsListing += '<div><strong>'+v.country_name+'</strong></div>';
				reviewsListing += ((v.email)? '<div><strong>'+v.email+'</strong></div>':'');
				reviewsListing += ((v.phone)? '<div><strong>'+v.phone+'</strong></div>':'');
				reviewsListing += '</div>';
				reviewsListing += '<div class="detailed-report-col-right">';
				reviewsListing += '<br /><p align="center">Risk Level</p>';
				reviewsListing += '<div class="risk-level-big rlb-'+v.risk_level_value.toString().replace('.','')+'" title="'+v.risk_level_text+'"></div>';
				reviewsListing += '</div>';
				reviewsListing += '<div class="clear"></div>';
				reviewsListing += '<div class="detailed-report-summary">'+v.summary+'</div>';
				reviewsListing += '<div class="detailed-report-title">'+v.title+'</div>';
				reviewsListing += '<div class="detailed-report-details">'+v.details+'</div>';
				reviewsListing += '<div class="detailed-report-author">By: '+v.author+'</div>';
				reviewsListing += '</div>';
				reviewsListing += '</div>';
				reviewsListing += '</td>';
				reviewsListing += '</tr>';
			});
			reviewsListing += '</table>';
			reviewsListing += '<div class="reviews-listing-back"><button type="button" onclick="javascript:backToCollectiveListing()">Back</button></div>';
			reviewsListing += '</div>';
	
			jQuery('#results').append(reviewsListing);
			jQuery('.collective-listing').animate({height: 'hide'}, 300, function() {
				jQuery('.reviews-listing').animate({height: 'show'}, 300);
				jQuery('html, body').animate({ scrollTop: jQuery('.reviews-listing').offset().top }, 300);
			});
		}
		
		// back to collective listing
		function backToCollectiveListing() {
			jQuery('.reviews-listing').animate({height: 'hide'}, 300, function() {
				jQuery('.collective-listing').animate({height: 'show'}, 300, function(){
					jQuery('html, body').animate({ scrollTop: jQuery(this).offset().top }, 300);
				});
				jQuery(this).remove();
			});
		}
		
		// show / hide full review
		function fullReview( id ) {
			if( customerData.membership == 1) {
				var object = jQuery('#btn-'+id).parent().parent().next().find('td');
				if( object.has('.alert-warning').length == 0 ) {
					object.append(jQuery('<div class="alert-warning box-alert"><img src="<?php echo plugin_dir_url( __FILE__ ); ?>/../../images/error-icon.png" /> Full reviews can be viewed by premium or corporate members only. To upgrade <a href="http://ebuyersreviewed.com/en/membership/My-Membership.html" target="_blank">click here</a>!<span class="sprite-btn-close" onclick="jQuery(this).parent().remove()"></span></div>').hide().fadeIn().delay(12000).fadeOut(300, function() { jQuery(this).remove(); }));
				}
			}
			else {
				jQuery('.view-report').html('Full Review');
				if( jQuery('.detailed-report-container:visible').attr('id') == 'detailed-report-'+id ) {
					jQuery('#detailed-report-'+id).slideUp(300);
					jQuery('#btn-'+id).html('Full Review');
					jQuery('html, body').animate({ scrollTop: jQuery('#btn-'+id).offset().top }, 300);
				}
				else {
					jQuery('.detailed-report-container:visible').hide();
					jQuery('#detailed-report-'+id).show();
					jQuery('#btn-'+id).html('Close Review');
					jQuery('html, body').animate({ scrollTop: (jQuery('#detailed-report-'+id).offset().top-34) }, 300);
				}
			}
		}
		
		// difference between two dates
		function dateDiff( date1, date2, interval ) {
			 var second=1000, minute=second*60, hour=minute*60, day=hour*24, week=day*7;
			 date1 = new Date(date1);
			 date2 = new Date(date2);
			 var timediff = date2 - date1;
			 if (isNaN(timediff)) return NaN;
			 switch (interval) {
				  case "years": return date2.getFullYear() - date1.getFullYear();
				  case "months": return (
						( date2.getFullYear() * 12 + date2.getMonth() )
						-
						( date1.getFullYear() * 12 + date1.getMonth() )
				  );
				  case "weeks"  : return Math.floor(timediff / week);
				  case "days"   : return Math.floor(timediff / day); 
				  case "hours"  : return Math.floor(timediff / hour); 
				  case "minutes": return Math.floor(timediff / minute);
				  case "seconds": return Math.floor(timediff / second);
				  default: return undefined;
			 }
		}
	</script>
</head>
<body>
	<div class="box-content">
		<form name="screen-eBR" method="POST" action="">
			<?php

			global $woocommerce;
			$order = new WC_Order($object->ID);
			$review_summary = get_post_meta($object->ID, 'ebr-review-summary', true);
			$reviews = get_post_meta($object->ID, 'ebr-reviews');

			if(empty($review_summary)) {
				$review_summary['error'] = "Error Receiving User's Reviews.";
			}
			?>

			<?php
			if(isset($review_summary['error'])) { ?>
				<div id="ebr-message"><?php echo $review_summary['error']; ?></div>
			<?php } else { ?>
					<div class="reviews-listing">
						<div class="reviews-listing-title">
							Summary review for <?php echo $review_summary['name']; ?>
						</div>
						<div class="reviews-listing-risk-level table">
							<div class="tr">
								<div class="td">
									Risk Level: <span style="margin-left: 10px; color: black;"><?php echo ($review_summary['average_risk_level_value'] == 0) ? 'neutral' : $review_summary['average_risk_level_value']; ?></span>
								</div>
								<div class="td">
									<div class="risk-level-big rlb-<?php echo str_replace('.', '', $review_summary['average_risk_level_value']); ?>" title="<?php echo $review_summary['average_risk_level_text']; ?>">
									</div>
								</div>
							</div>
						</div>
						<div class="reviews-listing-summary">
							<?php echo $review_summary['summary']; ?>
							<br /><br />
							<strong>Suggestion:</strong><?php echo str_replace('Suggestion:', '', $review_summary['suggestion']); ?>
						</div>
						<?php if(!empty($reviews[0])) { ?>
						<table class="tbl01">
							<tr>
								<th>Date</th>
								<th>Author</th>
								<th>Review Title</th>
								<th>Risk Level</th>
								<th>&nbsp;</th>
							</tr>
							<?php foreach ($reviews as $key => $review) { 
								if(empty($review))
									continue;
							?>
							<tr>
								<td><?php echo $review[0]['date']; ?></td>
								<td><?php echo $review[0]['author']; ?></td>
								<td><?php echo $review[0]['title']; ?></th>
								<td><div class="risk-level-small rls-<?php echo $review[0]['risk_level_value']; ?>" title="<?php echo $review[0]['risk_level_text']; ?>"></div></td>
								<td align="right"><a class="view-report" id="btn-<?php echo $key; ?>" href="javascript:fullReview('<?php echo $key; ?>')">Full Review</a></td>
							</tr>
							<tr>
								<td class="reviews-listing-details" colspan="5">
									<div id="detailed-report-<?php echo $key; ?>" class="detailed-report-container">
										<div class="detailed-report">
											<?php echo ($review[0]['verified']) ? "<div class=\"detailed-report-seal-verified\"></div>" : ''; ?>
											<div class="detailed-report-header">Buyer Review</div>
											<div class="detailed-report-col-left">
												<div><?php echo $review[0]['date']; ?></div><br />
												<div><strong><?php echo $review[0]['name']; ?></strong></div>
												<div><strong><?php echo $review[0]['address']; ?></strong></div>
												<div><strong><?php echo $review[0]['country_name']; ?></strong></div>
												<?php echo ($review[0]['email'] != '') ? "<div><strong>" . $review[0]['email'] . "</strong></div>" : ""; ?>
												<?php echo ($review[0]['phone'] != '') ? "<div><strong>" . $review[0]['phone'] . "</strong></div>" : ""; ?>
											</div>
											<div class="detailed-report-col-right">
												<br /><p align="center">Risk Level</p>
												<div class="risk-level-big rlb-<?php echo str_replace('.', '', $review[0]['risk_level_value']); ?>" title="<?php echo $review[0]['risk_level_text']; ?>"></div>
											</div>
											<div class="clear"></div>
											<div class="detailed-report-summary"><?php echo $review[0]['summary']; ?></div>
											<div class="detailed-report-title"><?php echo $review[0]['title']; ?></div>
											<div class="detailed-report-details"><?php echo $review[0]['details']; ?></div>
											<div class="detailed-report-author">By: <?php echo $review[0]['author']; ?></div>
										</div>
									</div>
								</td>
							</tr>
							<?php } ?>
						</table>
						<?php } ?>
					</div>

				</div>
			<?php } ?>
		</form>
	</div>

</body></html>