/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
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
			data.find('.addKeyContainer').removeClass('hide').show();
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
						animation: 'show',
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
	}
});
jQuery(document).ready(function () {
	var settingMobileInstance = new Settings_DAV_Js();
	settingMobileInstance.registerEvents();
})
