/*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*/
// show/hide password
$('.show_pass').click(function (e) {
	var id = $(this).attr('id').substr(4);
	showRelatedListPassword(id, '');
	return false;
});

// related modules
function showRelatedListPassword(record) {
	var element = $('#' + record);
	var iconElement = $('a#btn_' + record + ' span');
	var passVal = element.html(); // current value of password
	// button labels
	var showPassText = iconElement.data('titleShow') ? iconElement.data('titleShow') : app.vtranslate('LBL_ShowPassword');
	var hidePassText = iconElement.data('titleHide') ? iconElement.data('titleHide') : app.vtranslate('LBL_HidePassword');

	// if password is hashed, show it
	if (passVal == '**********') {
		var params = {
			'module': "OSSPasswords",
			'action': "GetPass",
			'record': record
		}
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		AppConnector.request(params).then(
				function (data) {
					var response = data['result'];
					if (response['success']) {

						// show password
						element.html(response['password']);
						// change button title to 'Hide Password'
						iconElement.attr('title', hidePassText);
						// change icon
						iconElement.removeClass('adminIcon-passwords-encryption');
						iconElement.addClass('glyphicon-lock');
						// show copy to clipboard button
						$('a#copybtn_' + record).removeClass('hide');
					}
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
				},
				function (data, err) {

				}
		);
	}
	// if password is not hashed, hide it
	else {
		// hide password
		element.html('**********');
		// change button title to 'Show Password'
		iconElement.attr('title', showPassText);
		// change icon
		iconElement.removeClass('glyphicon-lock');
		iconElement.addClass('adminIcon-passwords-encryption');
		// hide copy to clipboard button
		$('a#copybtn_' + record).addClass('hide');
	}
}
new Clipboard('.copy_pass', {
	text: function (trigger) {
		Vtiger_Helper_Js.showPnotify({
			text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
			type: 'success'
		});
		return $('#' + trigger.getAttribute('data-id')).text();
	}
});

