/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

'use strict';
$(document).ready(() => {
	$('input:visible').first().focus();
	$('#fingerPrint').val(new DeviceUUID().get());
	$('button.close').on('click', () => {
		$('.visible-phone').css('visibility', 'hidden');
	});
	$('a#forgotpass').on('click', () => {
		$('#loginDiv').hide();
		$('#forgotPasswordDiv').removeClass('d-none');
		$('#forgotPasswordDiv').show();
	});
	$('a#backButton').on('click', () => {
		$('#loginDiv').removeClass('d-none');
		$('#loginDiv').show();
		$('#forgotPasswordDiv').hide();
	});

	let formForgot = $('.js-forgot-password');
	formForgot.on('submit', (event) => {
		event.preventDefault();
		$.post('index.php?module=Users&action=LoginForgotPassword', {
			email: formForgot.find('[name="email"]').val()
		})
			.done((data) => {
				formForgot.find('.js-email-content').addClass('d-none');
				formForgot.find('#retrievePassword').attr('disabled', 'disabled');
				$('.js-alert-password').removeClass('d-none alert-danger').addClass('alert-success');
				$('.js-alert-text').html(data.result);
			})
			.fail((error) => {
				$('.js-alert-password').removeClass('d-none').addClass('alert-danger');
				$('.js-alert-text').html(JSON.parse(error.responseText).error.message);
			});
	});

	let formChange = $('.js-change-password');
	formChange.on('submit', (event) => {
		event.preventDefault();
		let password = formChange.find('[name="password"]').val();
		let confirmPassword = formChange.find('[name="confirm_password"]').val();
		if (password !== confirmPassword) {
			$('.js-alert-confirm-password').removeClass('d-none');
		} else {
			$.post('index.php?module=Users&action=LoginPassChange', {
				password: password,
				confirm_password: confirmPassword,
				token: formChange.find('[name="token"]').val()
			})
				.done(() => {
					window.location.href = 'index.php';
				})
				.fail((error) => {
					$('.js-alert-confirm-password').addClass('d-none');
					$('.js-alert-password').removeClass('d-none').addClass('alert-danger');
					$('.js-alert-text').html(JSON.parse(error.responseText).error.message);
				});
		}
	});
});
