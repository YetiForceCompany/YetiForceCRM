/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Index_Js(
	'Settings_Colors_Index_Js',
	{},
	{
		registerEvents: function() {
			this.registerModuleTabEvent();
			this.registerModuleChangeEvent();
			this.registerModulePickListChangeEvent();
			this.initEvents();
		},
		initEvents: function() {
			var thisInstance = this;
			var container = $('.UserColors');
			container.find('.updateUserColor').on('click', function(e) {
				thisInstance.updateUserColor(e, thisInstance);
			});
			container.find('.generateUserColor').on('click', this.generateUserColor);
			container.find('.removeUserColor').on('click', this.removeUserColor);
			container.find('.updateGroupColor').on('click', function(e) {
				thisInstance.updateGroupColor(e, thisInstance);
			});
			container.find('.generateGroupColor').on('click', this.generateGroupColor);
			container.find('.removeGroupColor').on('click', this.removeGroupColor);
			container.find('.updateModuleColor').on('click', function(e) {
				thisInstance.updateModuleColor(e, thisInstance);
			});
			container.find('.generateModuleColor').on('click', this.generateModuleColor);
			container.find('.removeModuleColor').on('click', this.removeModuleColor);
			container.find('.activeModuleColor').on('click', this.activeModuleColor);
			container.find('.addPicklistColorColumn').on('click', this.addPicklistColorColumn);
			container.find('.updatePicklistValueColor').on('click', function(e) {
				thisInstance.updatePicklistValueColor(e, thisInstance);
			});
			container.find('.generatePicklistValueColor').on('click', this.generatePicklistValueColor);
			container.find('.removePicklistValueColor').on('click', this.removePicklistValueColor);
			container.find('#update_event').on('click', this.updateEvent);
		},
		registerColorPicker: function(container, color) {
			return window.ColorPicker.mount({ el: container.find('.js-color-picker')[0], currentColor: color });
		},
		updateColorModal(colorPreview, request) {
			let cb = color => {
				let progress = $.progressIndicator({
					message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
					blockInfo: {
						enabled: true
					}
				});
				request(color).then(data => {
					Vtiger_Helper_Js.showPnotify({
						text: data['result']['message'],
						type: 'success'
					});
					colorPreview.data('color', color);
					progress.progressIndicator({ mode: 'hide' });
				});
			};
			App.Fields.Colors.showPicker({ color: colorPreview.data('color'), bgToUpdate: colorPreview, cb });
		},
		updateUserColor: function(e) {
			let target = $(e.currentTarget);
			let colorPreview = $('#calendarColorPreviewUser' + target.data('record'));
			let request = color => {
				return new Promise((resolve, reject) => {
					AppConnector.request({
						module: 'Colors',
						parent: 'Settings',
						action: 'SaveAjax',
						mode: 'updateUserColor',
						color,
						record: target.data('record')
					}).done(data => {
						resolve(data);
					});
				});
			};
			this.updateColorModal(colorPreview, request);
		},
		generateUserColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewUser' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'updateUserColor',
				record: target.data('record')
			}).done(function(data) {
				colorPreview.css('background', data['result'].color);
				colorPreview.data('color', data['result'].color);
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		removeUserColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewUser' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'removeUserColor',
				record: target.data('record')
			}).done(function(data) {
				colorPreview.css('background', '');
				colorPreview.data('color', '');
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		updateGroupColor: function(e) {
			let target = $(e.currentTarget);
			let colorPreview = $('#calendarColorPreviewGroup' + target.data('record'));
			let request = color => {
				return new Promise((resolve, reject) => {
					AppConnector.request({
						module: 'Colors',
						parent: 'Settings',
						action: 'SaveAjax',
						mode: 'updateGroupColor',
						color,
						record: target.data('record')
					}).done(data => {
						resolve(data);
					});
				});
			};
			this.updateColorModal(colorPreview, request);
		},
		generateGroupColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewGroup' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'updateGroupColor',
				record: target.data('record')
			}).done(function(data) {
				colorPreview.css('background', data['result'].color);
				colorPreview.data('color', data['result'].color);
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		removeGroupColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewGroup' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'removeGroupColor',
				record: target.data('record')
			}).done(function(data) {
				colorPreview.css('background', '');
				colorPreview.data('color', '');
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		updateModuleColor: function(e) {
			let target = $(e.currentTarget);
			let colorPreview = $('#calendarColorPreviewModule' + target.data('record'));
			let request = color => {
				return new Promise((resolve, reject) => {
					AppConnector.request({
						module: 'Colors',
						parent: 'Settings',
						action: 'SaveAjax',
						mode: 'updateModuleColor',
						color,
						record: target.data('record')
					}).done(data => {
						resolve(data);
					});
				});
			};
			this.updateColorModal(colorPreview, request);
		},
		generateModuleColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewModule' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'updateModuleColor',
				record: target.data('record')
			}).done(function(data) {
				colorPreview.css('background', data['result'].color);
				colorPreview.data('color', data['result'].color);
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		removeModuleColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewModule' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'removeModuleColor',
				record: target.data('record')
			}).done(function(data) {
				colorPreview.css('background', '');
				colorPreview.data('color', '');
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		activeModuleColor: function(e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewModule' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'activeModuleColor',
				status: target.is(':checked'),
				color: colorPreview.data('color'),
				record: target.data('record')
			}).done(function(data) {
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: 'success'
				});
				colorPreview.css('background', data['result'].color);
				colorPreview.data('color', data['result'].color);
			});
		},
		addPicklistColorColumn: function(e) {
			var container = jQuery('.picklistViewContentDiv');
			var target = $(e.currentTarget);
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'addPicklistColorColumn',
				picklistModule: target.data('fieldmodule'),
				fieldId: target.data('fieldid')
			}).done(function(data) {
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: 'success'
				});
				container.find('.modulePickList').trigger('change');
			});
		},
		updatePicklistValueColor: function(e) {
			let target = $(e.currentTarget);
			let colorPreview = $('#calendarColorPreviewPicklistValue' + target.data('fieldvalueid'));
			let request = color => {
				return new Promise((resolve, reject) => {
					AppConnector.request({
						module: 'Colors',
						parent: 'Settings',
						action: 'SaveAjax',
						mode: 'updatePicklistValueColor',
						color,
						fieldId: target.data('fieldid'),
						fieldValueId: target.data('fieldvalueid')
					}).done(data => {
						resolve(data);
					});
				});
			};
			this.updateColorModal(colorPreview, request);
		},
		generatePicklistValueColor: function(e) {
			var container = jQuery('.picklistViewContentDiv');
			var target = $(e.currentTarget);
			var colorPreview = container.find('#calendarColorPreviewPicklistValue' + target.data('fieldvalueid'));
			var progress = $.progressIndicator({
				message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'updatePicklistValueColor',
				fieldId: target.data('fieldid'),
				fieldValueId: target.data('fieldvalueid')
			}).done(function(data) {
				colorPreview.css('background', data['result'].color);
				colorPreview.data('color', data['result'].color);
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
			progress.progressIndicator({ mode: 'hide' });
			app.hideModalWindow();
		},
		removePicklistValueColor: function(e) {
			var container = jQuery('.picklistViewContentDiv');
			var target = $(e.currentTarget);
			var colorPreview = container.find('#calendarColorPreviewPicklistValue' + target.data('fieldvalueid'));
			var progress = $.progressIndicator({
				message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
				blockInfo: {
					enabled: true
				}
			});
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'removePicklistValueColor',
				fieldId: target.data('fieldid'),
				fieldValueId: target.data('fieldvalueid')
			}).done(function(data) {
				colorPreview.css('background', '');
				colorPreview.data('color', '');
				Vtiger_Helper_Js.showPnotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
			progress.progressIndicator({ mode: 'hide' });
			app.hideModalWindow();
		},
		registerModuleTabEvent: function() {
			var thisInstance = this;
			jQuery('#picklistsColorsTab').on('click', function(e) {
				var progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					module: 'Colors',
					parent: app.getParentModuleName(),
					view: 'IndexAjax',
					mode: 'getPickListView'
				}).done(function(data) {
					var container = jQuery('.picklistViewContentDiv');
					container.html(data);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					App.Fields.Picklist.changeSelectElementView(jQuery('.pickListModulesSelectContainer'));
					thisInstance.registerModuleChangeEvent();
					container.find('.modulePickList').trigger('change');
				});
			});
		},
		registerModuleChangeEvent: function() {
			var thisInstance = this;
			var container = jQuery('.picklistViewContentDiv');
			container.find('.pickListModules').on('change', function(e) {
				var selectedModule = jQuery(e.currentTarget).val();
				if (selectedModule.length <= 0) {
					jQuery('#picklistsColorsTab').trigger('click');
					return false;
				}
				var progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					module: 'Colors',
					parent: app.getParentModuleName(),
					source_module: selectedModule,
					view: 'IndexAjax',
					mode: 'getPickListView'
				}).done(function(data) {
					container.html(data);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					App.Fields.Picklist.changeSelectElementView(jQuery('.pickListModulesSelectContainer'));
					App.Fields.Picklist.changeSelectElementView(jQuery('.pickListModulesPicklistSelectContainer'));
					thisInstance.registerModuleChangeEvent();
					thisInstance.registerModulePickListChangeEvent();
					jQuery('#modulePickList').trigger('change');
				});
			});
		},
		registerModulePickListChangeEvent: function() {
			var thisInstance = this;
			var container = jQuery('.picklistViewContentDiv');
			container.find('.modulePickList').on('change', function(e) {
				var progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					module: 'Colors',
					parent: app.getParentModuleName(),
					source_module: jQuery('#pickListModules').val(),
					view: 'IndexAjax',
					mode: 'getPickListView',
					fieldId: jQuery(e.currentTarget).val()
				}).done(function(data) {
					container.html(data);
					App.Fields.Picklist.changeSelectElementView(jQuery('.pickListModulesSelectContainer'));
					App.Fields.Picklist.changeSelectElementView(jQuery('.pickListModulesPicklistSelectContainer'));
					thisInstance.registerModuleChangeEvent();
					thisInstance.registerModulePickListChangeEvent();
					$('.UserColors .addPicklistColorColumn').on('click', thisInstance.addPicklistColorColumn);
					$('.UserColors .updatePicklistValueColor').on('click', function(e) {
						thisInstance.updatePicklistValueColor(e, thisInstance);
					});
					$('.UserColors .generatePicklistValueColor').on('click', thisInstance.generatePicklistValueColor);
					$('.UserColors .removePicklistValueColor').on('click', thisInstance.removePicklistValueColor);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
			});
		}
	}
);
