/* {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} */
new Clipboard('#copy-button', {
	text: function (trigger) {
		Vtiger_Helper_Js.showPnotify({
			text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
			type: 'success'
		});
		return jQuery('#detailPassword').text();
	}
});
