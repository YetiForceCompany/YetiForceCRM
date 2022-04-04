/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 *************************************************************************************/
'use strict';

jQuery.Class(
	'Settings_Module_Manager_Js',
	{
		validateField(field, rules, i, options) {
			const specialChars = /[&\<\>\:\'\"\,]/;
			if (specialChars.test(field.val())) {
				return app.vtranslate('JS_SPECIAL_CHARACTERS_NOT_ALLOWED');
			}
			return true;
		},
		validateModuleName(field, rules, i, options) {
			let returnVal = false;
			const progressIndicatorElement = jQuery.progressIndicator();
			AppConnector.request({
				async: false,
				dataType: 'json',
				data: {
					module: app.getModuleName(),
					action: 'Basic',
					parent: app.getParentModuleName(),
					mode: 'checkModuleName',
					moduleName: field.val()
				}
			})
				.done((data) => {
					const result = data.result;
					if (result.success) {
						returnVal = true;
					} else {
						returnVal = result.text;
					}
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				})
				.fail((data, error) => {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					returnVal = app.vtranslate('JS_NOT_ALLOWED_VALUE');
				});
			return returnVal;
		},
		sendForm(form) {
			const formData = form.serializeFormData();
			const progress = $.progressIndicator({
				message: app.vtranslate('Adding a Key'),
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'Basic',
				mode: 'createModule',
				formData: formData
			}).done(function (data) {
				progress.progressIndicator({ mode: 'hide' });
				const result = data.result;
				if (!result.success) {
					app.showNotify({
						text: result.text,
						type: 'error'
					});
				} else {
					window.location.href = 'index.php?parent=Settings&module=LayoutEditor&sourceModule=' + result.text;
				}
			});
		},
		registerModalCreateModule(data) {
			const form = data.find('form');
			form.validationEngine(app.validationEngineOptions);
			data.find('[name="saveButton"]').on('click', (e) => {
				if (form.validationEngine('validate')) {
					this.sendForm(form);
				}
			});
		}
	},
	{
		/*
		 * function to update the module status for the module
		 * @params: currentTarget - checkbox related to module.
		 */
		updateModuleStatus: function (currentTarget) {
			var aDeferred = jQuery.Deferred();
			var forModule = currentTarget.data('module');
			var status = currentTarget.is(':checked');

			var progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			var params = {};
			params['module'] = app.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['updateStatus'] = status;
			params['forModule'] = forModule;
			params['action'] = 'Basic';
			params['mode'] = 'updateModuleStatus';

			AppConnector.request(params)
				.done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.resolve(data);
				})
				.fail(function (error) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					aDeferred.reject(error);
				});
			return aDeferred.promise();
		},
		createModule: function (currentTarget) {
			var progressIndicatorElement = jQuery.progressIndicator();
			app.showModalWindow(
				null,
				'index.php?module=ModuleManager&parent=Settings&view=CreateModule',
				function (wizardContainer) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					Settings_Module_Manager_Js.registerModalCreateModule(wizardContainer);
				}
			);
		},
		//This will show the notification message using pnotify
		showNotify: function (customParams) {
			var params = {
				title: app.vtranslate('JS_MESSAGE'),
				text: '',
				type: 'info'
			};
			$.extend(params, customParams);
			app.showNotify(params);
		},
		frameProgress: false,
		deleteModule: function (container) {
			const self = this;
			container.on('click', '.deleteModule', function () {
				let forModule = $(this).attr('name');
				app.showConfirmModal({
					title: app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE'),
					confirmedCallback: () => {
						self.frameProgress = $.progressIndicator({
							position: 'html',
							message: app.vtranslate('JS_FRAME_IN_PROGRESS'),
							blockInfo: {
								enabled: true
							}
						});
						AppConnector.request({
							module: app.getModuleName(),
							action: 'Basic',
							parent: app.getParentModuleName(),
							mode: 'deleteModule',
							forModule: forModule
						}).done(function (data) {
							app.showNotify({
								title: app.vtranslate('JS_REMOVED_MODULE'),
								type: 'info'
							});
							app.openUrl('index.php?module=ModuleManager&parent=Settings&view=List');
						});
					}
				});
			});
		},
		registerEvents: function (e) {
			var thisInstance = this;
			var container = jQuery('#moduleManagerContents');
			container.find('.createModule').on('click', thisInstance.createModule);
			var scrollbar = container.find('.js-scrollbar');
			app.showNewScrollbarTopBottom(scrollbar);
			thisInstance.deleteModule(container);
			//register click event for check box to update the module status
			container.on('click', '[name="moduleStatus"]', function (e) {
				var currentTarget = jQuery(e.currentTarget);
				var moduleBlock = currentTarget.closest('.moduleManagerBlock');
				var actionButtons = moduleBlock.find('.actions');
				var forModule = currentTarget.data('moduleTranslation');
				var moduleDetails = moduleBlock.find('.moduleImage, .moduleName');

				if (currentTarget.is(':checked')) {
					//show the settings button for the module.
					actionButtons.removeClass('d-none');

					//changing opacity of the block if the module is enabled
					moduleDetails.removeClass('dull');

					//update the module status as enabled
					thisInstance.updateModuleStatus(currentTarget).done(function (data) {
						var params = {
							text: forModule + ' ' + app.vtranslate('JS_MODULE_ENABLED')
						};
						if (data.success == false) {
							params.type = 'error';
							params.text = data.error.message;
						}
						thisInstance.showNotify(params);
					});
				} else {
					//hide the settings button for the module.
					actionButtons.addClass('d-none');

					//changing opacity of the block if the module is disabled
					moduleDetails.addClass('dull');

					//update the module status as disabled
					thisInstance.updateModuleStatus(currentTarget).done(function (data) {
						var params = {
							text: forModule + ' ' + app.vtranslate('JS_MODULE_DISABLED')
						};
						thisInstance.showNotify(params);
					});
				}
			});
		}
	}
);
jQuery(document).ready(function () {
	var settingModuleManagerInstance = new Settings_Module_Manager_Js();
	settingModuleManagerInstance.registerEvents();
});
