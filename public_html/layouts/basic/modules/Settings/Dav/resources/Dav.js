/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
jQuery.Class('Settings_DAV_Js', {}, {
	//This will store the MenuEditor Container
	mobileContainer: false,
	/**
	 * Function to get the MenuEditor container
	 */
	getContainer: function () {
		if (this.mobileContainer == false) {
			this.mobileContainer = jQuery('#DavKeysContainer');
		}
		return this.mobileContainer;
	},
	addKey: function (e) {
		var container = jQuery('#DavKeysContainer');
		var editColorModal = container.find('.addKeyContainer');
		var clonedContainer = editColorModal.clone(true, true);
		var callBackFunction = function (data) {
			data.find('.addKeyContainer').removeClass('d-none').show();
			app.showSelect2ElementView(data.find('.select')); // chzn-select select2
			data.find('[name="saveButton"]').click(function (e) {
				var form = data.find('form');
				var formData = form.serializeFormData();
				var progress = $.progressIndicator({
					'message': app.vtranslate('Adding a Key'),
					'blockInfo': {
						'enabled': true
					}
				});
				var settingMobileInstance = new Settings_DAV_Js();
				settingMobileInstance.registerSaveEvent('addKey', formData, true);
				progress.progressIndicator({'mode': 'hide'});
			});
		}
		app.showModalWindow(clonedContainer, function (data) {
			if (typeof callBackFunction == 'function') {
				callBackFunction(data);
			}
		}, {'width': '1000px'});
	},
	deleteKey: function (e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var settingMobileInstance = new Settings_DAV_Js();
		settingMobileInstance.registerSaveEvent('deleteKey', {
			'user': closestTrElement.data('user'),
			'name': closestTrElement.data('name')
		});
		closestTrElement.remove();
	},
	registerSaveEvent: function (mode, data, reload) {
		var params = {}
		params.data = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'SaveAjax',
			mode: mode,
			params: data
		}
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).then(
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
					};
					if (response.success == true) {
						params.type = 'success'
					} else {
						params.type = 'error'
					}
					Vtiger_Helper_Js.showPnotify(params);
					if (reload == true && response.success == true) {
						window.location.reload();
					}
				},
				function (data, err) {
				}
		);
	},
	registerEvents: function (e) {
		var thisInstance = this;
		var container = thisInstance.getContainer();
		container.find('.addKey').click(thisInstance.addKey);
		container.find('.deleteKey').click(thisInstance.deleteKey);
		App.Fields.Password.registerCopyClipboard();
	}
});
jQuery(document).ready(function () {
	var settingMobileInstance = new Settings_DAV_Js();
	settingMobileInstance.registerEvents();
})
