/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("Vtiger_SwitchUsers_Js", {}, {
	registerSave: function (container) {
		container.find('.modal-body button').on('click', function () {
			document.progressLoader = jQuery.progressIndicator({
				message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			var userId = container.find('[name="user"]').val();
			container.find('[name="id"]').val(userId);
			container.find('form').submit();
			return;
		});
	},
	registerEvents: function () {
		var container = jQuery('.switchUsersContainer');
		this.registerSave(container);
	}
});

jQuery(document).ready(function (e) {
	var instance = new Vtiger_SwitchUsers_Js();
	instance.registerEvents();
});
