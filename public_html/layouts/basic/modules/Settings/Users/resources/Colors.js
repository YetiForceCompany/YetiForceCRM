/* {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} */
var Colors_Js = {
	initEvants: function () {
		$('.UserColors .updateColor').click(Colors_Js.updateColor);
		$('.UserColors .generateColor').click(Colors_Js.generateColor);
		$('.UserColors .activeColor').click(Colors_Js.activeColor);
		$('.UserColors .addPicklistColorColumn').click(Colors_Js.addPicklistColorColumn);
		$('.UserColors .updatePicklistValueColor').click(Colors_Js.updatePicklistValueColor);
		$('.UserColors .generatePicklistValueColor').click(Colors_Js.generatePicklistValueColor);
	},
	updateColor: function (e) {
		var target = $(e.currentTarget);
		var closestTrElement = target.closest('tr');
		var closestTableElement = target.closest('table');
		var editColorModal = jQuery('.UserColors .editColorContainer');
		var clonedContainer = editColorModal.clone(true, true);
		var metod = target.data('metod');
		var colorPreview = $('#calendarColorPreview' + target.data('type') + target.data('id'));
		var callBackFunction = function (data) {
			data.find('.editColorContainer').removeClass('hide').show();
			var selectedColor = data.find('.selectedColor');
			selectedColor.val(colorPreview.data('color'));
			//register color picker
			var params = {
				flat: true,
				color: colorPreview.data('color'),
				onChange: function (hsb, hex, rgb) {
					selectedColor.val('#' + hex);
					colorPreview.data('color', '#' + hex);
				}
			};
			if (typeof customParams != 'undefined') {
				params = jQuery.extend(params, customParams);
			}
			data.find('.calendarColorPicker').ColorPicker(params);
			//save the user calendar with color
			data.find('[name="saveButton"]').click(function (e) {
				var progress = $.progressIndicator({
					'message': app.vtranslate('JS_LOADING_PLEASE_WAIT'),
					'blockInfo': {
						'enabled': true
					}
				});
				Colors_Js.registerSaveEvent(metod, {
					'color': selectedColor.val(),
					'id': target.data('id'),
					'fieldId': target.data('fieldid'),
					'fieldValueId': target.data('fieldvalueid'),
					'table': closestTrElement.data('table'),
					'field': closestTableElement.data('fieldname')
				});
				colorPreview.css('background', selectedColor.val());
				target.data('color', selectedColor.val());
				progress.progressIndicator({'mode': 'hide'});
				app.hideModalWindow();
			});
		};
		app.showModalWindow(clonedContainer, function (data) {
			if (typeof callBackFunction == 'function') {
				callBackFunction(data);
			}
		}, {'width': '1000px'});
	},
	generateColor: function (e) {
		var target = $(e.currentTarget);
		var closestTableElement = target.closest('table');
		var metod = target.data('metod');
		var colorPreview = $('#calendarColorPreview' + target.data('type') + target.data('id'));
		var params = {
			module: app.getModuleName(),
			action: 'SaveAjax',
			mode: 'generateColor',
			params: {id: target.data('id'),
				fieldId: target.data('fieldid'),
				fieldValueId: target.data('fieldvalueid'),
				color: colorPreview.data('color'),
				table: target.data('table'),
				field: closestTableElement.data('fieldname'),
				mode: metod
			}
		};
		AppConnector.request(params).then(
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show',
						type: 'success'
					};
					colorPreview.css('background', response.color);
					colorPreview.data('color', response.color);
					Vtiger_Helper_Js.showPnotify(params);
				},
				function (data, err) {
				}
		);
	},
	registerSaveEvent: function (mode, data) {
		var params = {};
		params.data = {
			module: app.getModuleName(),
			action: 'SaveAjax',
			mode: mode,
			params: data
		};
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).done(
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show',
						type: 'success'
					};
					Vtiger_Helper_Js.showPnotify(params);
					return response;
				},
				function (data, err) {
				}
		);
	},
	activeColor: function (e) {
		var target = $(e.currentTarget);
		var colorPreview = $('#calendarColorPreview' + target.data('type') + target.data('id'));
		var params = {};
		params.data = {
			module: app.getModuleName(),
			action: 'SaveAjax',
			mode: 'activeColor',
			params: {
				'status': target.is(':checked'),
				'color': colorPreview.data('color'),
				'id': target.data('id')
			}
		};
		params.async = false;
		params.dataType = 'json';
		AppConnector.request(params).done(
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show',
						type: 'success'
					};
					Vtiger_Helper_Js.showPnotify(params);
					colorPreview.css('background', response.color);
					colorPreview.data('color', response.color);
				}
		);
	},
	registerModuleTabEvent: function () {
		jQuery('#picklistsColorsTab').on('click', function (e) {
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request({
				module: 'Users',
				parent: app.getParentModuleName(),
				view: 'ColorsAjax',
				mode: 'getPickListView'
			}).then(function (data) {
				var container = jQuery('.picklistViewContentDiv');
				container.html(data);
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				app.changeSelectElementView(jQuery('.pickListModulesSelectContainer'));
				Colors_Js.registerModuleChangeEvent();
				container.find('.modulePickList').trigger('change');
			});
		});
	},
	registerModuleChangeEvent: function () {
		var container = jQuery('.picklistViewContentDiv');
		container.find('.pickListModules').on('change', function (e) {
			var selectedModule = jQuery(e.currentTarget).val();
			if (selectedModule.length <= 0) {
				Settings_Vtiger_Index_Js.showMessage({'type': 'error', 'text': app.vtranslate('JS_PLEASE_SELECT_MODULE')});
			}
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request({
				module: 'Users',
				parent: app.getParentModuleName(),
				source_module: selectedModule,
				view: 'ColorsAjax',
				mode: 'getPickListView'
			}).then(function (data) {
				container.html(data);
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				app.changeSelectElementView(jQuery('.pickListModulesSelectContainer'));
				app.changeSelectElementView(jQuery('.pickListModulesPicklistSelectContainer'));
				Colors_Js.registerModuleChangeEvent();
				Colors_Js.registerModulePickListChangeEvent();
				jQuery('#modulePickList').trigger('change');
			});
		});
	},
	registerModulePickListChangeEvent: function () {
		var container = jQuery('.picklistViewContentDiv');
		container.find('.modulePickList').on('change', function (e) {
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': 'html',
				'blockInfo': {
					'enabled': true
				}
			});
			AppConnector.request({
				module: 'Users',
				parent: app.getParentModuleName(),
				source_module: jQuery('#pickListModules').val(),
				view: 'ColorsAjax',
				mode: 'getPickListView',
				fieldId: jQuery(e.currentTarget).val()
			}).then(function (data) {
				container.html(data);
				app.changeSelectElementView(jQuery('.pickListModulesSelectContainer'));
				app.changeSelectElementView(jQuery('.pickListModulesPicklistSelectContainer'));
				Colors_Js.registerModuleChangeEvent();
				Colors_Js.registerModulePickListChangeEvent();
				Colors_Js.initEvants();
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
			});
		});
	},
	registerEvents: function () {
		Colors_Js.registerModuleTabEvent();
		Colors_Js.registerModuleChangeEvent();
		Colors_Js.registerModulePickListChangeEvent();
		Colors_Js.initEvants();
	},
	addPicklistColorColumn: function (e) {
		var container = jQuery('.picklistViewContentDiv');
		var target = $(e.currentTarget);
		AppConnector.request({
			module: 'Picklist',
			parent: 'Settings',
			action: 'SaveAjax',
			mode: 'addPicklistColorColumn',
			picklistModule: target.data('fieldmodule'),
			fieldId: target.data('fieldid')
		}).then(
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show',
						type: 'success'
					};
					Vtiger_Helper_Js.showPnotify(params);
					container.find('.modulePickList').trigger('change');
				}
		);
	},
	updatePicklistValueColor: function (e) {
		var container = jQuery('.picklistViewContentDiv');
		var target = $(e.currentTarget);
		var editColorModal = jQuery('.UserColors .editColorContainer');
		var clonedContainer = editColorModal.clone(true, true);
		var colorPreview = container.find('#calendarColorPreviewPicklistValue' + target.data('fieldvalueid'));
		var callBackFunction = function (data) {
			data.find('.editColorContainer').removeClass('hide').show();
			var selectedColor = data.find('.selectedColor');
			selectedColor.val(colorPreview.data('color'));
			//register color picker
			var params = {
				flat: true,
				color: colorPreview.data('color'),
				onChange: function (hsb, hex, rgb) {
					selectedColor.val('#' + hex);
					colorPreview.data('color', '#' + hex);
				}
			};
			if (typeof customParams != 'undefined') {
				params = jQuery.extend(params, customParams);
			}
			data.find('.calendarColorPicker').ColorPicker(params);
			//save the user calendar with color
			data.find('[name="saveButton"]').click(function (e) {
				var progress = $.progressIndicator({
					'message': app.vtranslate('JS_LOADING_PLEASE_WAIT'),
					'blockInfo': {
						'enabled': true
					}
				});

				AppConnector.request({
					module: 'Picklist',
					parent: 'Settings',
					action: 'SaveAjax',
					mode: 'updatePicklistValueColor',
					color: selectedColor.val(),
					fieldId: target.data('fieldid'),
					fieldValueId: target.data('fieldvalueid')
				}).then(
						function (data) {
							var response = data['result'];
							var params = {
								text: response['message'],
								animation: 'show',
								type: 'success'
							};
							Vtiger_Helper_Js.showPnotify(params);
							return response;
						},
						function (data, err) {
						}
				);
				colorPreview.css('background', selectedColor.val());
				target.data('color', selectedColor.val());
				progress.progressIndicator({'mode': 'hide'});
				app.hideModalWindow();
			});
		};
		app.showModalWindow(clonedContainer, function (data) {
			if (typeof callBackFunction == 'function') {
				callBackFunction(data);
			}
		}, {'width': '1000px'});
	},
	generatePicklistValueColor: function (e) {
		var container = jQuery('.picklistViewContentDiv');
		var target = $(e.currentTarget);
		var colorPreview = container.find('#calendarColorPreviewPicklistValue' + target.data('fieldvalueid'));
		var progress = $.progressIndicator({
			'message': app.vtranslate('JS_LOADING_PLEASE_WAIT'),
			'blockInfo': {
				'enabled': true
			}
		});
		AppConnector.request({
			module: 'Picklist',
			parent: 'Settings',
			action: 'SaveAjax',
			mode: 'updatePicklistValueColor',
			fieldId: target.data('fieldid'),
			fieldValueId: target.data('fieldvalueid')
		}).then(
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show',
						type: 'success'
					};
					colorPreview.css('background', response.color);
					colorPreview.data('color', response.color);
					Vtiger_Helper_Js.showPnotify(params);
				},
				function (data, err) {
				}
		);
		progress.progressIndicator({'mode': 'hide'});
		app.hideModalWindow();
	}
};
$(document).ready(function () {
	Colors_Js.registerEvents();
});
