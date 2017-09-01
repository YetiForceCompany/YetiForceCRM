/* {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} */
var Colors_Js = {
	initEvants: function () {
		$('.UserColors .updateUserColor').click(Colors_Js.updateUserColor);
		$('.UserColors .generateUserColor').click(Colors_Js.generateUserColor);
		$('.UserColors .removeUserColor').click(Colors_Js.removeUserColor);
		$('.UserColors .updateGroupColor').click(Colors_Js.updateGroupColor);
		$('.UserColors .generateGroupColor').click(Colors_Js.generateGroupColor);
		$('.UserColors .removeGroupColor').click(Colors_Js.removeGroupColor);
		$('.UserColors .updateModuleColor').click(Colors_Js.updateModuleColor);
		$('.UserColors .generateModuleColor').click(Colors_Js.generateModuleColor);
		$('.UserColors .removeModuleColor').click(Colors_Js.removeModuleColor);
		$('.UserColors .activeModuleColor').click(Colors_Js.activeModuleColor);
		$('.UserColors .addPicklistColorColumn').click(Colors_Js.addPicklistColorColumn);
		$('.UserColors .updatePicklistValueColor').click(Colors_Js.updatePicklistValueColor);
		$('.UserColors .generatePicklistValueColor').click(Colors_Js.generatePicklistValueColor);
		$('.UserColors .removePicklistValueColor').click(Colors_Js.removePicklistValueColor);
	},
	updateUserColor: function (e) {
		var target = $(e.currentTarget);
		var editColorModal = jQuery('.UserColors .editColorContainer');
		var clonedContainer = editColorModal.clone(true, true);
		var colorPreview = $('#calendarColorPreviewUser' + target.data('id'));
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
					'module': 'Colors',
					'parent': 'Settings',
					'action': 'SaveAjax',
					'mode': 'updateUserColor',
					'color': selectedColor.val(),
					'id': target.data('id')
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
	generateUserColor: function (e) {
		var target = $(e.currentTarget);
		var colorPreview = $('#calendarColorPreviewUser' + target.data('id'));
		AppConnector.request({
			module: 'Colors',
			parent: 'Settings',
			action: 'SaveAjax',
			mode: 'updateUserColor',
			id: target.data('id')
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
	},
	removeUserColor: function (e) {
		var target = $(e.currentTarget);
		var colorPreview = $('#calendarColorPreviewUser' + target.data('id'));
		AppConnector.request({
			module: 'Colors',
			parent: 'Settings',
			action: 'SaveAjax',
			mode: 'removeUserColor',
			id: target.data('id')
		}).then(
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show',
						type: 'success'
					};
					colorPreview.css('background', '');
					colorPreview.data('color', '');
					Vtiger_Helper_Js.showPnotify(params);
				},
				function (data, err) {
				}
		);
	},
	updateGroupColor: function (e) {
		var target = $(e.currentTarget);
		var editColorModal = jQuery('.UserColors .editColorContainer');
		var clonedContainer = editColorModal.clone(true, true);
		var colorPreview = $('#calendarColorPreviewGroup' + target.data('id'));
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
					'module': 'Colors',
					'parent': 'Settings',
					'action': 'SaveAjax',
					'mode': 'updateGroupColor',
					'color': selectedColor.val(),
					'id': target.data('id')
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
	generateGroupColor: function (e) {
		var target = $(e.currentTarget);
		var colorPreview = $('#calendarColorPreviewGroup' + target.data('id'));
		AppConnector.request({
			module: 'Colors',
			parent: 'Settings',
			action: 'SaveAjax',
			mode: 'updateGroupColor',
			id: target.data('id')
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
	},
	removeGroupColor: function (e) {
		var target = $(e.currentTarget);
		var colorPreview = $('#calendarColorPreviewGroup' + target.data('id'));
		AppConnector.request({
			module: 'Colors',
			parent: 'Settings',
			action: 'SaveAjax',
			mode: 'removeGroupColor',
			id: target.data('id')
		}).then(
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show',
						type: 'success'
					};
					colorPreview.css('background', '');
					colorPreview.data('color', '');
					Vtiger_Helper_Js.showPnotify(params);
				},
				function (data, err) {
				}
		);
	},
	updateModuleColor: function (e) {
		var target = $(e.currentTarget);
		var editColorModal = jQuery('.UserColors .editColorContainer');
		var clonedContainer = editColorModal.clone(true, true);
		var colorPreview = $('#calendarColorPreviewModule' + target.data('id'));
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
					'module': 'Colors',
					'parent': 'Settings',
					'action': 'SaveAjax',
					'mode': 'updateModuleColor',
					'color': selectedColor.val(),
					'id': target.data('id')
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
	generateModuleColor: function (e) {
		var target = $(e.currentTarget);
		var colorPreview = $('#calendarColorPreviewModule' + target.data('id'));
		AppConnector.request({
			module: 'Colors',
			parent: 'Settings',
			action: 'SaveAjax',
			mode: 'updateModuleColor',
			id: target.data('id')
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
	},
	removeModuleColor: function (e) {
		var target = $(e.currentTarget);
		var colorPreview = $('#calendarColorPreviewModule' + target.data('id'));
		AppConnector.request({
			module: 'Colors',
			parent: 'Settings',
			action: 'SaveAjax',
			mode: 'removeModuleColor',
			id: target.data('id')
		}).then(
				function (data) {
					var response = data['result'];
					var params = {
						text: response['message'],
						animation: 'show',
						type: 'success'
					};
					colorPreview.css('background', '');
					colorPreview.data('color', '');
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
	activeModuleColor: function (e) {
		var target = $(e.currentTarget);
		var colorPreview = $('#calendarColorPreviewModule' + target.data('id'));
		var params = {};
		params.data = {
			module: 'Colors',
			parent: 'Settings',
			action: 'SaveAjax',
			mode: 'activeModuleColor',
			status: target.is(':checked'),
			color: colorPreview.data('color'),
			id: target.data('id')
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
				module: 'Colors',
				parent: app.getParentModuleName(),
				view: 'IndexAjax',
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
				module: 'Colors',
				parent: app.getParentModuleName(),
				source_module: selectedModule,
				view: 'IndexAjax',
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
				module: 'Colors',
				parent: app.getParentModuleName(),
				source_module: jQuery('#pickListModules').val(),
				view: 'IndexAjax',
				mode: 'getPickListView',
				fieldId: jQuery(e.currentTarget).val()
			}).then(function (data) {
				container.html(data);
				app.changeSelectElementView(jQuery('.pickListModulesSelectContainer'));
				app.changeSelectElementView(jQuery('.pickListModulesPicklistSelectContainer'));
				Colors_Js.registerModuleChangeEvent();
				Colors_Js.registerModulePickListChangeEvent();
				$('.UserColors .addPicklistColorColumn').click(Colors_Js.addPicklistColorColumn);
				$('.UserColors .updatePicklistValueColor').click(Colors_Js.updatePicklistValueColor);
				$('.UserColors .generatePicklistValueColor').click(Colors_Js.generatePicklistValueColor);
				$('.UserColors .removePicklistValueColor').click(Colors_Js.removePicklistValueColor);
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
			module: 'Colors',
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
					module: 'Colors',
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
			module: 'Colors',
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
	},
	removePicklistValueColor: function (e) {
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
			module: 'Colors',
			parent: 'Settings',
			action: 'SaveAjax',
			mode: 'removePicklistValueColor',
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
					colorPreview.css('background', '');
					colorPreview.data('color', '');
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
