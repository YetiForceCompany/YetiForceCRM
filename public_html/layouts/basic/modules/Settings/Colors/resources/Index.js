/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Index_Js(
	'Settings_Colors_Index_Js',
	{},
	{
		/**
		 * Var to store colors container
		 */
		container: false,
		/**
		 * Function to get the colors container
		 */
		getContainer: function () {
			if (this.container == false) {
				this.container = jQuery('.js-colors-container');
			}
			return this.container;
		},
		registerEvents: function () {
			this.registerModuleTabEvent();
			this.registerModuleChangeEvent();
			this.registerModulePickListChangeEvent();
			this.initEvents();
		},
		initEvents: function () {
			var thisInstance = this;
			var container = $('.UserColors');
			container.find('.updateUserColor').on('click', function (e) {
				thisInstance.updateUserColor(e, thisInstance);
			});
			container.find('.generateUserColor').on('click', this.generateUserColor);
			container.find('.removeUserColor').on('click', this.removeUserColor);
			container.find('.updateGroupColor').on('click', function (e) {
				thisInstance.updateGroupColor(e, thisInstance);
			});
			container.find('.generateGroupColor').on('click', this.generateGroupColor);
			container.find('.removeGroupColor').on('click', this.removeGroupColor);
			container.find('.updateModuleColor').on('click', function (e) {
				thisInstance.updateModuleColor(e, thisInstance);
			});
			container.find('.generateModuleColor').on('click', this.generateModuleColor);
			container.find('.removeModuleColor').on('click', this.removeModuleColor);
			container.find('.activeModuleColor').on('click', this.activeModuleColor);
			container.find('.addPicklistColorColumn').on('click', this.addPicklistColorColumn);
			container.find('.updatePicklistValueColor').on('click', function (e) {
				thisInstance.updatePicklistValueColor(e, thisInstance);
			});
			container.find('.generatePicklistValueColor').on('click', this.generatePicklistValueColor);
			container.find('.removePicklistValueColor').on('click', this.removePicklistValueColor);
			container.find('.js-generate-color').on('click', this.generateFieldColor);
			container.find('.js-remove-color').on('click', this.removeFieldColor);
			container.find('.js-update-color').on('click', function (e) {
				thisInstance.updateFieldColor(e);
			});
		},
		registerColorPicker: function (container, color) {
			return window.ColorPicker.mount({
				el: container.find('.js-color-picker')[0],
				currentColor: color
			});
		},
		updateColorModal(colorPreview, request) {
			let cb = (color) => {
				let progress = $.progressIndicator({
					message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
					blockInfo: {
						enabled: true
					}
				});
				request(color).then((data) => {
					app.showNotify({
						text: data['result']['message'],
						type: 'success'
					});
					colorPreview.data('color', color);
					progress.progressIndicator({ mode: 'hide' });
				});
			};
			App.Fields.Colors.showPicker({
				color: colorPreview.data('color'),
				bgToUpdate: colorPreview,
				cb
			});
		},
		updateUserColor: function (e) {
			let target = $(e.currentTarget);
			let colorPreview = $('#calendarColorPreviewUser' + target.data('record'));
			let request = (color) => {
				return new Promise((resolve, reject) => {
					AppConnector.request({
						module: 'Colors',
						parent: 'Settings',
						action: 'SaveAjax',
						mode: 'updateUserColor',
						color,
						record: target.data('record')
					}).done((data) => {
						resolve(data);
					});
				});
			};
			this.updateColorModal(colorPreview, request);
		},
		generateUserColor: function (e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewUser' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'updateUserColor',
				record: target.data('record')
			}).done(function (data) {
				colorPreview.css('background', data['result'].color);
				colorPreview.data('color', data['result'].color);
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		removeUserColor: function (e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewUser' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'removeUserColor',
				record: target.data('record')
			}).done(function (data) {
				colorPreview.css('background', '');
				colorPreview.data('color', '');
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		updateGroupColor: function (e) {
			let target = $(e.currentTarget);
			let colorPreview = $('#calendarColorPreviewGroup' + target.data('record'));
			let request = (color) => {
				return new Promise((resolve, reject) => {
					AppConnector.request({
						module: 'Colors',
						parent: 'Settings',
						action: 'SaveAjax',
						mode: 'updateGroupColor',
						color,
						record: target.data('record')
					}).done((data) => {
						resolve(data);
					});
				});
			};
			this.updateColorModal(colorPreview, request);
		},
		generateGroupColor: function (e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewGroup' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'updateGroupColor',
				record: target.data('record')
			}).done(function (data) {
				colorPreview.css('background', data['result'].color);
				colorPreview.data('color', data['result'].color);
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		removeGroupColor: function (e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewGroup' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'removeGroupColor',
				record: target.data('record')
			}).done(function (data) {
				colorPreview.css('background', '');
				colorPreview.data('color', '');
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		updateModuleColor: function (e) {
			let target = $(e.currentTarget);
			let colorPreview = $('#calendarColorPreviewModule' + target.data('record'));
			let request = (color) => {
				return new Promise((resolve, reject) => {
					AppConnector.request({
						module: 'Colors',
						parent: 'Settings',
						action: 'SaveAjax',
						mode: 'updateModuleColor',
						color,
						record: target.data('record')
					}).done((data) => {
						resolve(data);
					});
				});
			};
			this.updateColorModal(colorPreview, request);
		},
		generateModuleColor: function (e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewModule' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'updateModuleColor',
				record: target.data('record')
			}).done(function (data) {
				colorPreview.css('background', data['result'].color);
				colorPreview.data('color', data['result'].color);
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		removeModuleColor: function (e) {
			var target = $(e.currentTarget);
			var colorPreview = $('#calendarColorPreviewModule' + target.data('record'));
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'removeModuleColor',
				record: target.data('record')
			}).done(function (data) {
				colorPreview.css('background', '');
				colorPreview.data('color', '');
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		activeModuleColor: function (e) {
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
			}).done(function (data) {
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
				colorPreview.css('background', data['result'].color);
				colorPreview.data('color', data['result'].color);
			});
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
			}).done(function (data) {
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
				container.find('.modulePickList').trigger('change');
			});
		},
		updatePicklistValueColor: function (e) {
			let target = $(e.currentTarget);
			let colorPreview = $('#calendarColorPreviewPicklistValue' + target.data('fieldvalueid'));
			let request = (color) => {
				return new Promise((resolve, reject) => {
					AppConnector.request({
						module: 'Colors',
						parent: 'Settings',
						action: 'SaveAjax',
						mode: 'updatePicklistValueColor',
						color,
						fieldId: target.data('fieldid'),
						fieldValueId: target.data('fieldvalueid')
					}).done((data) => {
						resolve(data);
					});
				});
			};
			this.updateColorModal(colorPreview, request);
		},
		generatePicklistValueColor: function (e) {
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
			}).done(function (data) {
				colorPreview.css('background', data['result'].color);
				colorPreview.data('color', data['result'].color);
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
			progress.progressIndicator({ mode: 'hide' });
			app.hideModalWindow();
		},
		removePicklistValueColor: function (e) {
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
			}).done(function (data) {
				colorPreview.css('background', '');
				colorPreview.data('color', '');
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
			progress.progressIndicator({ mode: 'hide' });
			app.hideModalWindow();
		},
		registerModuleTabEvent: function () {
			const self = this;
			const container = self.getContainer();
			container.find('.js-change-tab').on('click', (e) => {
				const clickedTab = $(e.currentTarget);
				const mode = clickedTab.attr('data-mode');
				if (mode) {
					let progressIndicatorElement = jQuery.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					AppConnector.request({
						module: 'Colors',
						parent: app.getParentModuleName(),
						view: 'IndexAjax',
						mode: mode
					}).done(function (data) {
						const activeTabPanelHref = clickedTab.attr('href');
						const activeTabPanel = container.find(activeTabPanelHref + ' .js-color-contents');
						activeTabPanel.html(data);
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						activeTabPanel.find('.modulePickList').trigger('change');
						self.registerModuleChangeEvent();
						App.Fields.Picklist.changeSelectElementView(container);
					});
				}
			});
		},
		registerModuleChangeEvent: function () {
			const self = this;
			const container = self.getContainer();
			container.find('.js-selected-module').on('change', function (e) {
				const selectedModule = jQuery(e.currentTarget).val();
				const activeTab = container.find('.js-change-tab.active');
				if (selectedModule.length <= 0) {
					activeTab.trigger('click');
					return false;
				}
				let progressIndicatorElement = jQuery.progressIndicator({
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
					mode: activeTab.attr('data-mode')
				}).done(function (data) {
					container.find(activeTab.attr('href') + ' .js-color-contents').html(data);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					App.Fields.Picklist.changeSelectElementView(container);
					self.registerModulePickListChangeEvent();
					self.registerModuleChangeEvent();
					self.registerModulePickListChangeEvent();
					container.find('.js-remove-color').on('click', (e) => {
						self.removeFieldColor(e, self);
					});
					container.find('.js-generate-color').on('click', (e) => {
						self.generateFieldColor(e);
					});
					container.find('.js-update-color').on('click', function (e) {
						self.updateFieldColor(e);
					});
					jQuery('#modulePickList').trigger('change');
				});
			});
		},
		registerModulePickListChangeEvent: function () {
			var thisInstance = this;
			var container = jQuery('.picklistViewContentDiv');
			container.find('.modulePickList').on('change', function (e) {
				var progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request({
					module: 'Colors',
					parent: app.getParentModuleName(),
					source_module: jQuery('.js-selected-module').val(),
					view: 'IndexAjax',
					mode: 'getPickListView',
					fieldId: jQuery(e.currentTarget).val()
				}).done(function (data) {
					container.html(data);
					App.Fields.Picklist.changeSelectElementView(jQuery('.pickListModulesSelectContainer'));
					App.Fields.Picklist.changeSelectElementView(jQuery('.pickListModulesPicklistSelectContainer'));
					thisInstance.registerModuleChangeEvent();
					thisInstance.registerModulePickListChangeEvent();
					$('.UserColors .addPicklistColorColumn').on('click', thisInstance.addPicklistColorColumn);
					$('.UserColors .updatePicklistValueColor').on('click', function (e) {
						thisInstance.updatePicklistValueColor(e, thisInstance);
					});
					$('.UserColors .generatePicklistValueColor').on('click', thisInstance.generatePicklistValueColor);
					$('.UserColors .removePicklistValueColor').on('click', thisInstance.removePicklistValueColor);
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				});
			});
		},
		generateFieldColor: function (e) {
			const fieldId = $(e.currentTarget).attr('data-field-id');
			let colorPreview = $('.js-color-preview[data-field-id="' + fieldId + '"]');
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'updateFieldColor',
				fieldId: fieldId
			}).done(function (data) {
				colorPreview.css('background', data['result'].color);
				colorPreview.data('color', data['result'].color);
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		},
		updateFieldColor: function (e) {
			const fieldId = $(e.currentTarget).attr('data-field-id');
			let colorPreview = $('.js-color-preview[data-field-id="' + fieldId + '"]');
			let request = (color) => {
				return new Promise((resolve, reject) => {
					AppConnector.request({
						module: 'Colors',
						parent: 'Settings',
						action: 'SaveAjax',
						mode: 'updateFieldColor',
						color,
						fieldId: fieldId
					}).done((data) => {
						resolve(data);
					});
				});
			};
			this.updateColorModal(colorPreview, request);
		},
		removeFieldColor: function (e) {
			const fieldId = $(e.currentTarget).attr('data-field-id');
			let colorPreview = $('.js-color-preview[data-field-id="' + fieldId + '"]');
			AppConnector.request({
				module: 'Colors',
				parent: 'Settings',
				action: 'SaveAjax',
				mode: 'removeFieldColor',
				fieldId: fieldId
			}).done(function (data) {
				colorPreview.css('background', '');
				colorPreview.data('color', '');
				app.showNotify({
					text: data['result']['message'],
					type: 'success'
				});
			});
		}
	}
);
