/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

'use strict';
$(document).ready(() => {
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
	$('form.forgot-form').on('submit', event => {
		if ($('#usernameFp').val() === '' || $('#emailId').val() === '') {
			event.preventDefault();
		}
	});
});
