/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
jQuery.Class('Settings_Users_Auth_Js', {}, {
	registerChangeVal: function (content) {
		var thisInstance = this;
		content.find('.configField').on('change', function (e) {
			var target = $(e.currentTarget);
			var params = {};
			params['type'] = target.data('type');
			params['param'] = target.attr('name');
			if (target.attr('type') == 'checkbox') {
				params['val'] = this.checked;
			} else {
				params['val'] = target.val();
			}
			app.saveAjax('updateConfig', params).then(function (data) {
				Settings_Vtiger_Index_Js.showMessage({type: 'success', text: data.result.message});
			});
		});
	},
	registerEvents: function () {
		var content = $('.usersAuth');
		this.registerChangeVal(content);
	}
});
