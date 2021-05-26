/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

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
				if (data.result.notify.type === 'success') {
					formForgot.find('.js-email-content').addClass('d-none');
					formForgot.find('#retrievePassword').attr('disabled', 'disabled');
					app.showNotify(data.result.notify);
				}
			})
			.fail((error) => {
				app.showNotify(JSON.parse(error.responseText).error.message.notify);
			});
	});

	let formChange = $('.js-change-password');
	formChange.on('submit', (event) => {
		event.preventDefault();
		$.post('index.php?module=Users&action=LoginPassChange', {
			password: formChange.find('[name="password"]').val(),
			confirm_password: formChange.find('[name="confirm_password"]').val(),
			token: formChange.find('[name="token"]').val()
		})
			.done((data) => {
				if (data.result.notify.type === 'success') {
					window.location.href = 'index.php';
					app.showNotify(data.result.notify);
				}
			})
			.fail((error) => {
				app.showNotify(JSON.parse(error.responseText).error.message.notify);
			});
	});
});
