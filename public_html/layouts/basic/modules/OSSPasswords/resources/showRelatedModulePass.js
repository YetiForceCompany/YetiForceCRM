/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
// show/hide password
'use strict';
var clipBoardInstances = [];
$('.show_pass').on('click', function (e) {
	let btn = $(this);
	let record = btn.data('id');
	let iconElement = btn.find('span');
	let text = btn.data('title-copy');
	let copyKey = `.copy_pass${record}`;
	let copyBtn = $(`<button type="button" id="copybtn_${record}" data-id="pass_${record}"
		class="copy_pass${record} btn btn-light btn-sm mr-2 js-popover-tooltip" data-content="${text}" data-js="popover"><span class="fas fa-download"></span></button>`);
	let element = $('[id="pass_' + record + '"]');
	let passVal = element.html();
	let showPassText = btn.data('titleShow') ? btn.data('titleShow') : app.vtranslate('LBL_ShowPassword');
	let hidePassText = btn.data('titleHide') ? btn.data('titleHide') : app.vtranslate('LBL_HidePassword');

	if (clipBoardInstances[copyKey] === undefined) {
		let clipboard = new ClipboardJS(copyKey, {
			text: function (trigger) {
				app.showNotify({
					text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
					type: 'success'
				});
				return $('#' + trigger.getAttribute('data-id')).text();
			}
		});
		clipBoardInstances[copyKey] = clipboard;
	}
	if (passVal == '**********') {
		var params = {
			module: 'OSSPasswords',
			action: 'GetPass',
			record: record
		};
		var progressIndicatorElement = jQuery.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		AppConnector.request(params).done(function (data) {
			var response = data['result'];
			if (response['success']) {
				element.text(response['password']);
				// change button title to 'Hide Password'
				btn.on('show.bs.popover', function () {
					this.dataset.content = hidePassText;
				});
				iconElement.removeClass('adminIcon-passwords-encryption').addClass('fas fa-lock');
				// show copy to clipboard button
				copyBtn.insertBefore(btn);
			}
			progressIndicatorElement.progressIndicator({ mode: 'hide' });
		});
	} else {
		element.html('**********');
		// change button title to 'Show Password'
		btn.on('show.bs.popover', function () {
			this.dataset.content = showPassText;
		});
		app.showPopoverElementView(element);
		iconElement.removeClass('fas fa-lock').addClass('adminIcon-passwords-encryption');
		// hide copy to clipboard button
		$(copyKey).remove();
		clipBoardInstances[copyKey].destroy();
		delete clipBoardInstances[copyKey];
	}
	return false;
});
