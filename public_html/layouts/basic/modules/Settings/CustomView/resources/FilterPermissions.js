/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_CustomView_FilterPermissions_Js',
	{},
	{
		formElement: false,
		registerButtonsEvent: function () {
			var thisInstance = this;
			var container = this.getForm();
			container.find('button.moveItem').on('click', function (e) {
				thisInstance.setDefaultPreferences(e);
			});
		},
		registerDisabledButtons: function () {
			var container = this.getForm();
			container.find('button.moveItem').each(function (n, e) {
				var currentTarget = jQuery(e);
				var sourceClass = currentTarget.data('source');
				var source = container.find('.' + sourceClass);
				if (source.find('option').length && !source.is(':disabled')) {
					currentTarget.prop('disabled', false);
				} else {
					currentTarget.prop('disabled', true);
				}
			});
		},
		move: function (currentTarget) {
			var container = this.getForm();
			var sourceClass = currentTarget.data('source');
			var targetClass = currentTarget.data('target');
			var target = container.find('.' + targetClass);
			var source = container.find('.' + sourceClass).find('option:selected');

			var values = {};
			values.val = source.val();
			values.label = source.text();
			values.blockLabel = source.closest('optgroup').attr('label');

			var targetOption = '<option value="' + values.val + '">' + values.label + '</option>';
			var targetOpt = target.find('optgroup[label="' + values.blockLabel + '"]');
			if (targetOpt.length) {
				targetOpt.append(targetOption);
			} else {
				target.append('<optgroup label="' + values.blockLabel + '">' + targetOption + '</optgroup>');
			}
			source.remove();
			App.Fields.Picklist.showSelect2ElementView(container.find('.select2'));
		},
		/**
		 * Saves permission for filter
		 * @param {jQuery.Event} e
		 */
		setDefaultPreferences: function (e) {
			var thisInstance = this;
			var container = this.getForm();
			var progressIndicatorElement = $.progressIndicator({
				message: app.vtranslate('JS_SAVE_LOADER_INFO'),
				position: this.getForm(),
				blockInfo: {
					enabled: true
				}
			});
			var currentTarget = $(e.currentTarget);
			var sourceClass = currentTarget.data('source');
			var params = {
				tabid: container.find('#sourceModule').val(),
				user: container.find('.' + sourceClass).val(),
				cvid: container.find('#cvid').val(),
				operator: currentTarget.data('operator'),
				type: container.find('#type').val()
			};
			app.saveAjax('setFilterPermissions', {}, params).done(function (data) {
				if (data.success) {
					if (data.result.success) {
						thisInstance.move(currentTarget);
						thisInstance.registerDisabledButtons();
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						app.showNotify({ text: data.result.message, type: 'success' });
					} else {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						app.showNotify({ text: data.result.message, type: 'error' });
					}
				} else {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
				}
			});
		},
		getForm: function () {
			if (this.formElement == false) {
				this.setForm(jQuery('#modalFilterPermissions'));
			}
			return this.formElement;
		},
		setForm: function (element) {
			this.formElement = element;
			return this;
		},
		registerEvents: function () {
			this.registerButtonsEvent();
			this.registerDisabledButtons();
		}
	}
);

jQuery(document).ready(function (e) {
	var instance = new Settings_CustomView_FilterPermissions_Js();
	instance.registerEvents();
});
