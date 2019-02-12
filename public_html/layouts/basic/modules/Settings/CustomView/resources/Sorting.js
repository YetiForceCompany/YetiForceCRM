/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("Settings_CustomView_Sorting_Js", {}, {
	formElement: false,
	/**
	 * Register events for buttons in form
	 */
	registerButtonsEvent: function () {
		var thisInstance = this;
		var form = this.getForm();
		form.find('.js-clear').on('click', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			currentTarget.closest('.js-sort-container').find('[name="defaultOrderBy"]').val('').trigger('change');
		});
		form.find('.js-sort-order-button').on('click', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			currentTarget.find('.fas').each(function (n, e) {
				if (jQuery(this).hasClass('d-none')) {
					jQuery(this).removeClass('d-none');
					form.find('[name="sortOrder"]').val(jQuery(this).data('val'));
				} else {
					jQuery(this).addClass('d-none');
				}
			});
		});
		form.on('submit', function (e) {
			var form = jQuery(e.currentTarget);
			thisInstance.saveSorting(form);
			e.preventDefault();
		});
	},
	/**
	 * Saves sort the filter
	 * @param {jQuery} form
	 */
	saveSorting: function (form) {
		var progress = $.progressIndicator({
			message: app.vtranslate('JS_SAVE_LOADER_INFO'),
			blockInfo: {
				enabled: true
			}
		});
		var data = form.serializeFormData();
		var params = {
			cvid: data.cvid,
			name: 'sort',
			value: data.defaultOrderBy ? data.defaultOrderBy + ',' + data.sortOrder : ''
		};
		app.saveAjax('updateField', {}, params).done(function (data) {
			app.hideModalWindow();
			if (data.success) {
				Vtiger_Helper_Js.showPnotify({text: data.result.message, type: 'success'});
			}
			progress.progressIndicator({mode: 'hide'});
		});
	},
	/**
	 * Returns form as jQuery object
	 * @returns {jQuery}
	 */
	getForm: function () {
		if (this.formElement === false) {
			this.setForm(jQuery('#js-sorting-filter'));
		}
		return this.formElement;
	},
	setForm: function (element) {
		this.formElement = element;
		return this;
	},
	registerEvents: function () {
		this.registerButtonsEvent();
	}
});

jQuery(document).ready(function (e) {
	var instance = new Settings_CustomView_Sorting_Js();
	instance.registerEvents();
});
