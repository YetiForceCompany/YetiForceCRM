/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_BruteForce_Index_Js',
	{},
	{
		container: false,
		getContainer: function () {
			if (this.container == false) {
				this.container = jQuery('div.contentsDiv');
			}
			return this.container;
		},
		saveAjax: function (mode, params) {
			app
				.saveAjax(mode, params)
				.done(function (data) {
					var response = data.result;
					var params = {
						text: app.vtranslate(response.message),
						type: 'info'
					};
					app.showNotify(params);
				})
				.fail(function (textStatus, errorThrown) {
					app.showNotify({
						text: app.vtranslate('JS_COULD_NOT_FINNISH_REACTION'),
						type: 'error'
					});
					app.errorLog(textStatus, errorThrown);
				});
		},
		registerSwitchEvents: function () {
			this.getContainer()
				.find('.js-switch--sent')
				.on('change', (e) => {
					$(e.currentTarget).closest('.form-group').find('.selectedUsersForm').toggleClass('d-none');
				});
		},
		registerEvents: function () {
			var thisInstance = this;
			thisInstance.registerSwitchEvents();
			var forms = thisInstance.getContainer().find('form');
			forms.on('submit', function (e) {
				var form = jQuery(e.currentTarget);
				if (form.validationEngine('validate') == true) {
					var paramsForm = form.serializeFormData();
					paramsForm['active'] = form.find('[name="active"]').prop('checked') ? 1 : 0;
					paramsForm['sent'] = form.find('[name="sent"]').prop('checked') ? 1 : 0;
					delete paramsForm['_csrf'];
					thisInstance.saveAjax(form.data('mode'), paramsForm);
					return false;
				} else {
					app.formAlignmentAfterValidation(form);
				}
			});
			forms.validationEngine(app.validationEngineOptions);
			jQuery('.unblock').on('click', function (e) {
				var progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				var element = jQuery(e.currentTarget);
				app.saveAjax('unBlock', element.data('id')).done(function (data) {
					var response = data.result;
					var params = { text: app.vtranslate(response.message) };
					if (response.success) {
						params.type = 'info';
						element.parents('tr').hide();
					}
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					app.showNotify(params);
				});
			});
		}
	}
);
