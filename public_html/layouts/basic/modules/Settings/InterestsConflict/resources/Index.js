/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_InterestsConflict_Index_Js',
	{},
	{
		registerConfig: function (container) {
			let form = container.find('.js-filter-form');
			form.validationEngine(app.validationEngineOptionsForRecord);
			form.find('.js-save').click(function (e) {
				if (form.validationEngine('validate')) {
					AppConnector.request(
						$.extend(
							{
								module: app.getModuleName(),
								parent: app.getParentModuleName(),
								action: 'Save',
								mode: 'config'
							},
							form.serializeFormData()
						)
					)
						.done(function (data) {
							app.showNotify({
								text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
								type: 'success'
							});
						})
						.fail(function () {
							app.showNotify({
								text: app.vtranslate('JS_ERROR'),
								type: 'error'
							});
						});
				}
			});
			form.find('[name="confirmationTimeIntervalList"]').on('change', function () {
				if (this.value === '-') {
					form.find('[name="confirmationTimeInterval"]').attr('disabled', 'disabled');
				} else {
					form.find('[name="confirmationTimeInterval"]').removeAttr('disabled');
				}
			});
		},
		registerModules: function (container) {
			let form = container.find('.js-filter-form');
			form.find('.js-change').click(function (e) {
				let modules = form.serializeFormData();
				delete modules['_csrf'];
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					action: 'Save',
					mode: 'modules',
					modules: modules
				})
					.done(function (data) {
						app.showNotify({
							text: app.vtranslate('JS_SAVE_NOTIFY_OK'),
							type: 'success'
						});
					})
					.fail(function () {
						app.showNotify({
							text: app.vtranslate('JS_ERROR'),
							type: 'error'
						});
					});
			});
		},
		registerEvents: function () {
			const self = this;
			$('#tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
				if (this.dataset.name === 'Unlock' || this.dataset.name === 'Confirmations') {
					AppComponents_InterestsConflict_Js['register' + this.dataset.name]($('#' + this.dataset.name));
				} else {
					self['register' + this.dataset.name]($('#' + this.dataset.name));
				}
			});
			let name = $('.js-tab.active').data('name');
			if (name === 'Unlock' || name === 'Confirmations') {
				AppComponents_InterestsConflict_Js['register' + name]($('#' + name));
			} else {
				self['register' + name]($('#' + name));
			}
		}
	}
);
