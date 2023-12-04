/** LOGIN / REGISTER POPUP */
function wpst_open_login_dialog(href) {

	jQuery('#wpst-user-modal .modal-dialog').removeClass('registration-complete');

	var modal_dialog = jQuery('#wpst-user-modal .modal-dialog');
	modal_dialog.attr('data-active-tab', '');

	switch (href) {

		case '#wpst-register':
			modal_dialog.attr('data-active-tab', '#wpst-register');
			break;

		case '#wpst-login':
		default:
			modal_dialog.attr('data-active-tab', '#wpst-login');
			break;
	}

	jQuery('#wpst-user-modal').modal('show');
}

function wpst_close_login_dialog() {

	jQuery('#wpst-user-modal').modal('hide');
}

jQuery(function ($) {

	"use strict";
	/***************************
	**  LOGIN / REGISTER DIALOG
	***************************/

	// Open login/register modal
	$('[href="#wpst-login"], [href="#wpst-register"]').click(function (e) {

		e.preventDefault();

		wpst_open_login_dialog($(this).attr('href'));

	});

	// Switch forms login/register
	$('.modal-footer a, a[href="#wpst-reset-password"]').click(function (e) {
		e.preventDefault();
		$('#wpst-user-modal .modal-dialog').attr('data-active-tab', $(this).attr('href'));
	});

	// Post login form
	$('#wpst_login_form').on('submit', function (e) {

		e.preventDefault();

		var button = $(this).find('button');
		button.button('loading');

		$.post(wpst_ajax_var.url, $('#wpst_login_form').serialize(), function (data) {

			var obj = $.parseJSON(data);

			$('.wpst-login .wpst-errors').html(obj.message);

			if (obj.error == false) {
				$('#wpst-user-modal .modal-dialog').addClass('loading');
				window.location.reload(true);
				button.hide();
			}

			button.button('reset');
		});

	});


	// Post register form
	$('#wpst_registration_form').on('submit', function (e) {

		e.preventDefault();

		var button = $(this).find('button');
		button.button('loading');

		$.post(wpst_ajax_var.url, $('#wpst_registration_form').serialize(), function (data) {

			var obj = $.parseJSON(data);

			$('.wpst-register .wpst-errors').html(obj.message);

			if (obj.error == false) {
				$('#wpst-user-modal .modal-dialog').addClass('registration-complete');
				// window.location.reload(true);
				button.hide();
			}

			button.button('reset');

		});

	});


	// Reset Password
	$('#wpst_reset_password_form').on('submit', function (e) {

		e.preventDefault();

		var button = $(this).find('button');
		button.button('loading');

		$.post(wpst_ajax_var.url, $('#wpst_reset_password_form').serialize(), function (data) {

			var obj = $.parseJSON(data);

			$('.wpst-reset-password .wpst-errors').html(obj.message);

			// if(obj.error == false){
			// $('#wpst-user-modal .modal-dialog').addClass('loading');
			// $('#wpst-user-modal').modal('hide');
			// }

			button.button('reset');
		});

	});

	if (window.location.hash == '#login') {
		wpst_open_login_dialog('#wpst-login');
	}

});