/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/
jQuery.Class('Settings_Module_Manager_Js', {
	validateField: function(field, rules, i, options){
		var specialChars = /[&\<\>\:\'\"\,]/;
		if (specialChars.test(field.val())) {
			return app.vtranslate('JS_SPECIAL_CHARACTERS_NOT_ALLOWED');
		}
		return true;
	},
	registerMondalCreateModule: function (data) {
		data.find('[name="saveButton"]').attr("disabled", true);
		var form = data.find('form');
		form.validationEngine(app.validationEngineOptions);
		var thisInstance = new Settings_Module_Manager_Js();
		data.find('input').on('change', function (e) {
			if ($(this).attr("name") == 'module_name') {
				thisInstance.checkModuleName($(this).val(), data);
			} else {
				if ($(this).val() != '') {
					$(this).attr("check", true);
				} else {
					$(this).attr("check", false);
				}
			}
			var status = true;
			data.find('input[name]').each(function () {
				if ($(this).attr("check") == 'false' || $(this).attr("check") == undefined) {
					status = false;
				}
			});
			if (status) {
				data.find('[name="saveButton"]').attr("disabled", false);
			} else {
				data.find('[name="saveButton"]').attr("disabled", true);
			}
		})
		data.find('[name="saveButton"]').click(function (e) {
			if (form.validationEngine('validate')) {
				var formData = form.serializeFormData();
				var progress = $.progressIndicator({
					'message': app.vtranslate('Adding a Key'),
					'blockInfo': {
						'enabled': true
					}
				});
				var params = {}
				params['module'] = app.getModuleName();
				params['parent'] = app.getParentModuleName();
				params['action'] = 'Basic';
				params['mode'] = 'createModule';
				params['formData'] = formData;
				AppConnector.request(params).then(
						function (data) {
							var result = data.result;
							if (!result.success) {
								var params = {
									text: result.text,
									animation: 'show',
									type: 'error'
								};
								Vtiger_Helper_Js.showPnotify(params);
							} else {
								window.location.href = 'index.php?parent=Settings&module=LayoutEditor&sourceModule=' + result.text;
							}
						}
				);
				progress.progressIndicator({'mode': 'hide'});
			}
		});
	}
}, {
	/*
	 * function to update the module status for the module
	 * @params: currentTarget - checkbox related to module.
	 */
	updateModuleStatus: function (currentTarget) {
		var aDeferred = jQuery.Deferred();
		var forModule = currentTarget.data('module');
		var status = currentTarget.is(':checked');

		var progressIndicatorElement = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});

		var params = {}
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['updateStatus'] = status;
		params['forModule'] = forModule
		params['action'] = 'Basic';
		params['mode'] = 'updateModuleStatus';

		AppConnector.request(params).then(
				function (data) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.resolve(data);
				},
				function (error) {
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					aDeferred.reject(error);
				}
		);
		return aDeferred.promise();
	},
	createModule: function (currentTarget) {
		var progressIndicatorElement = jQuery.progressIndicator();
		app.showModalWindow(null, "index.php?module=ModuleManager&parent=Settings&view=CreateModule", function (wizardContainer) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			Settings_Module_Manager_Js.registerMondalCreateModule(wizardContainer);
		});
	},
	checkModuleName: function (name, wizardContainer) {
		var progressIndicatorElement = jQuery.progressIndicator();
		wizardContainer.find('[name="module_name"]').attr("check", false);
		var params = {}
		params.data = {
			module: app.getModuleName(),
			action: 'Basic',
			parent: app.getParentModuleName(),
			mode: 'checkModuleName',
			moduleName: name
		}
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).then(
				function (data) {
					var result = data.result;
					if (result.success) {
						wizardContainer.find('[name="module_name"]').attr("check", true);
					} else {
						wizardContainer.find('[name="module_name"]').attr("check", false);
						var params = {
							text: result.text,
							animation: 'show',
							type: 'error'
						};
						Vtiger_Helper_Js.showPnotify(params);
						wizardContainer.find('[name="saveButton"]').attr("disabled", true);
					}
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
				}
		);
	},
	//This will show the notification message using pnotify
	showNotify: function (customParams) {
		var params = {
			title: app.vtranslate('JS_MESSAGE'),
			text: '',
			animation: 'show',
			type: 'info'
		};
		$.extend(params, customParams);
		Vtiger_Helper_Js.showPnotify(params);
	},
	deleteModule: function (container) {
		container.on('click', '.deleteModule', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var forModule = currentTarget.attr('name');
			var params = {}
			params.data = {
				module: app.getModuleName(),
				action: 'Basic',
				parent: app.getParentModuleName(),
				mode: 'deleteModule',
				forModule: forModule
			}
			AppConnector.request(params).then(
					function (data) {
						var params = {
							title: app.vtranslate('JS_REMOVED_MODULE'),
							animation: 'show',
							type: 'info'
						};
						Vtiger_Helper_Js.showPnotify(params);
						window.location.href = 'index.php?module=ModuleManager&parent=Settings&view=List';
					},
					function (error) {}
			);
		});
	},
	registerEvents: function (e) {
		var thisInstance = this;
		var container = jQuery('#moduleManagerContents');
		container.find('.createModule').click(thisInstance.createModule);
		thisInstance.deleteModule(container)
		//register click event for check box to update the module status
		container.on('click', '[name="moduleStatus"]', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var moduleBlock = currentTarget.closest('.moduleManagerBlock');
			var actionButtons = moduleBlock.find('.actions');
			var forModule = currentTarget.data('moduleTranslation');
			var moduleDetails = moduleBlock.find('.moduleImage, .moduleName');

			if (currentTarget.is(':checked')) {
				//show the settings button for the module.
				actionButtons.removeClass('hide');

				//changing opacity of the block if the module is enabled
				moduleDetails.removeClass('dull');

				//update the module status as enabled
				thisInstance.updateModuleStatus(currentTarget).then(function (data) {
					var params = {
						text: forModule + ' ' + app.vtranslate('JS_MODULE_ENABLED'),
					}
					if (data.success == false) {
						params.type = 'error';
						params.text = data.error.message;

					}
					thisInstance.showNotify(params);
				}, function (error) {

				});

			} else {
				//hide the settings button for the module.
				actionButtons.addClass('hide');

				//changing opacity of the block if the module is disabled
				moduleDetails.addClass('dull');

				//update the module status as disabled
				thisInstance.updateModuleStatus(currentTarget).then(function (data) {
					var params = {
						text: forModule + ' ' + app.vtranslate('JS_MODULE_DISABLED')
					}
					thisInstance.showNotify(params);
				}, function (error) {

				});
			}

		});
	}
});
jQuery(document).ready(function () {
	var settingModuleManagerInstance = new Settings_Module_Manager_Js();
	settingModuleManagerInstance.registerEvents();
})
