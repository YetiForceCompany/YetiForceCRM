/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
// show/hide password
'use strict';

$('.show_pass').on('click', function (e) {
	var id = $(this).attr('id').substr(4);
	showRelatedListPassword(id);
	return false;
});

// related modules
function showRelatedListPassword(record) {
	var element = $('#' + record);
	var iconElement = $('a#btn_' + record + ' span');
	var btn = $('a#btn_' + record);
	var copybtn = $('a#copybtn_' + record);
	var passVal = element.html(); // current value of password
	// button labels
	var showPassText = btn.data('titleShow') ? btn.data('titleShow') : app.vtranslate('LBL_ShowPassword');
	var hidePassText = btn.data('titleHide') ? btn.data('titleHide') : app.vtranslate('LBL_HidePassword');

	// if password is hashed, show it
	if (passVal == '**********') {
		var params = {
			'module': "OSSPasswords",
			'action': "GetPass",
			'record': record
		};
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		AppConnector.request(params).done(function (data) {
			var response = data['result'];
			if (response['success']) {
				// show password
				element.html(response['password']);
				// change button title to 'Hide Password'
				btn.on('show.bs.popover', function () {
					this.dataset.content = hidePassText;
				})
				// change icon
				iconElement.removeClass('adminIcon-passwords-encryption');
				iconElement.addClass('fas fa-lock');
				// show copy to clipboard button
				copybtn.removeClass('d-none');
			}
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
		});
	}
	// if password is not hashed, hide it
	else {
		// hide password
		element.html('**********');
		// change button title to 'Show Password'
		btn.on('show.bs.popover', function () {
			this.dataset.content = showPassText;
		})
		app.showPopoverElementView(element);
		// change icon
		iconElement.removeClass('fas fa-lock');
		iconElement.addClass('adminIcon-passwords-encryption');
		// hide copy to clipboard button
		copybtn.addClass('d-none');
	}
}

new ClipboardJS('.copy_pass', {
	text: function (trigger) {
		Vtiger_Helper_Js.showPnotify({
			text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
			type: 'success'
		});
		return $('#' + trigger.getAttribute('data-id')).text();
	}
});

