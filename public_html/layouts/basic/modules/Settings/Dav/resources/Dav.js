/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Dav_Keys_Js',
	{},
	{
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
				App.Fields.Picklist.showSelect2ElementView(data.find('.select'));
				data.find('[name="saveButton"]').on('click', function (e) {
					var form = data.find('form');
					var formData = form.serializeFormData();
					var progress = $.progressIndicator({
						message: app.vtranslate('Adding a Key'),
						blockInfo: {
							enabled: true
						}
					});
					var settingMobileInstance = new Settings_Dav_Keys_Js();
					settingMobileInstance.registerSaveEvent('addKey', formData, true);
					progress.progressIndicator({ mode: 'hide' });
				});
			};
			app.showModalWindow(
				clonedContainer,
				function (data) {
					if (typeof callBackFunction == 'function') {
						callBackFunction(data);
					}
				},
				{ width: '1000px' }
			);
		},
		deleteKey: function (e) {
			var target = $(e.currentTarget);
			var closestTrElement = target.closest('.js-tr-row');
			var settingMobileInstance = new Settings_Dav_Keys_Js();
			settingMobileInstance.registerSaveEvent('deleteKey', {
				user: closestTrElement.data('user')
			});
			closestTrElement.remove();
		},
		registerSaveEvent: function (mode, data, reload) {
			var params = {};
			params.data = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: mode
			};
			if (typeof data !== 'undefined') {
				for (var i in data) {
					params.data[i] = data[i];
				}
			}
			params.async = false;
			params.dataType = 'json';
			AppConnector.request(params)
				.done(function (data) {
					var response = data['result'];
					var params = {
						text: response['message']
					};
					if (response.success == true) {
						params.type = 'success';
					} else {
						params.type = 'error';
					}
					app.showNotify(params);
					if (reload == true && response.success == true) {
						window.location.reload();
					}
				})
				.fail(function (data, err) {});
		},
		registerEvents: function (e) {
			var thisInstance = this;
			var container = thisInstance.getContainer();
			container.find('.js-add-key').on('click', thisInstance.addKey);
			container.find('.js-delete-key').on('click', thisInstance.deleteKey);
			App.Fields.Text.registerCopyClipboard(container);
		}
	}
);
