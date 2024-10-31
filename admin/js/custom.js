jQuery(function( $ ) {
	
	// Show Hide forgot password form
	$( 'a#forgot_password_link').click(function() {
	  	$( "#forgot_password_form" ).toggle( "slow", function() { });
	});
	
	var container = $('.error-container');
	// Sign up form validation
	var validator = $("#ebuyers_sign_up_form").validate({
		errorLabelContainer: container,
        wrapper: "p",
      	invalidHandler: function(form, validator) {
      		if ($(".container-data-success").length > 0) {
      			$('.error-container').addClass('container-data-error').removeClass('container-data-success');
      			$('.error-container p').remove();
      		}
	  	}
   	});
	
	// Sign in form validation
	var validator_sign_in = $("#ebuyers_sign_in_form").validate({
		errorLabelContainer: container,
        wrapper: "p",
      	invalidHandler: function(form, validator) {
      		if ($(".container-data-success").length > 0) {
      			$('.error-container').addClass('container-data-error').removeClass('container-data-success');
      			$('.error-container p').remove();
      		}
	  	}
	});
	
	// Forgot password form validation
	var validator_forgot_password_form = $("#forgot_password_form").validate({
		errorLabelContainer: container,
        wrapper: "p",
      	invalidHandler: function(form, validator) {
      		if ($(".container-data-success").length > 0) {
      			$('.error-container').addClass('container-data-error').removeClass('container-data-success');
      			$('.error-container p').remove();
      		}
	  	}
	});
	
	// Pop Up
	
	var appendthis =  ("<div class='modal-overlay js-modal-close'></div>");

	$('a[data-modal-id]').click(function(e) {
		e.preventDefault();
    $("body").append(appendthis);
    $(".modal-overlay").fadeTo(10000, 0.7);
    //$(".js-modalbox").fadeIn(500);
		var modalBox = $(this).attr('data-modal-id');
		$('#'+modalBox).fadeIn($(this).data());
	});  
  
  
	$(".js-modal-close, .modal-overlay").click(function() {
		//$("#popup1").hide();
		$("#popup1").hide();
	    $(".modal-overlay").hide();
	 
	});
	 
	$(window).resize(function() {		
		$(".modal-box").css({
		    top: ($('#wpbody').height() - $(".modal-box").outerHeight()) / 2,
		    left: ($('#wpbody').width() - $(".modal-box").outerWidth()) / 2
		});
	});
	 
	$(window).resize();


	
});
