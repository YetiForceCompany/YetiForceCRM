/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
'use strict';

$.Class('Settings_CustomRecordNumbering_Js', {}, {

	form: false,
	getForm: function () {
		if (this.form == false) {
			this.form = jQuery('#EditView');
		}
		return this.form;
	},

	/**
	 * Function to register change event for source module field
	 */
	registerOnChangeEventOfSourceModule() {
		const editViewForm = this.getForm();
		editViewForm.find('[name="sourceModule"]').on('change', function (e) {
			$('.saveButton').removeAttr('disabled');
			AppConnector.request({
				'module': app.getModuleName(),
				'parent': app.getParentModuleName(),
				'action': "CustomRecordNumberingAjax",
				'mode': "getModuleCustomNumberingData",
				'sourceModule': $(e.currentTarget).val()
			}).done(function (data) {
				if (data) {
					editViewForm.find('[name="prefix"]').val(data.result.prefix);
					editViewForm.find('[name="leading_zeros"]').val(data.result.leading_zeros).trigger('change');
					editViewForm.find('[name="reset_sequence"]').val(data.result.reset_sequence).trigger('change');
					editViewForm.find('[name="postfix"]').val(data.result.postfix);
					editViewForm.find('[name="sequenceNumber"]').val(data.result.cur_id);
					editViewForm.find('[name="sequenceNumber"]').data('oldSequenceNumber', data.result.cur_id);
					let options = '';
					for (var index in data.result.picklists) {
						options += '<option value="picklist:' + index + '">' + data.result.picklists[index] + '</option>';
					}
					editViewForm.find('#picklistVariables').html(options)
				}
			});
		});
	},

	/**
	 * Function to register event for saving module custom numbering
	 */
	saveModuleCustomNumbering() {
		if ($('.saveButton').attr("disabled")) {
			return;
		}
		const editViewForm = this.getForm();
		const sourceModule = editViewForm.find('[name="sourceModule"]').val();
		const prefix = editViewForm.find('[name="prefix"]');
		const leadingZeros = editViewForm.find('[name="leading_zeros"]').val();
		const currentPrefix = $.trim(prefix.val());
		const postfix = editViewForm.find('[name="postfix"]');
		const currentPostfix = jQuery.trim(postfix.val());
		const sequenceNumberElement = editViewForm.find('[name="sequenceNumber"]');
		const sequenceNumber = sequenceNumberElement.val();
		const oldSequenceNumber = sequenceNumberElement.data('oldSequenceNumber');
		if ((sequenceNumber < oldSequenceNumber) && (currentPrefix === prefix.data('oldPrefix')) && (currentPostfix === postfix.data('oldPostfix'))) {
			sequenceNumberElement.validationEngine('showPrompt', app.vtranslate('JS_SEQUENCE_NUMBER_MESSAGE') + " " + oldSequenceNumber, 'error', 'topLeft', true);
			return;
		}
		editViewForm.find('.saveButton').attr("disabled", "disabled");
		AppConnector.request({
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: "CustomRecordNumberingAjax",
			mode: "saveModuleCustomNumberingData",
			sourceModule: sourceModule,
			prefix: currentPrefix,
			leading_zeros: leadingZeros,
			postfix: currentPostfix,
			sequenceNumber: sequenceNumber,
			reset_sequence: editViewForm.find('[name="reset_sequence"]').val(),
		}).done(function (data) {
			if (data.success === true) {
				Settings_Vtiger_Index_Js.showMessage({
					text: app.vtranslate('JS_RECORD_NUMBERING_SAVED_SUCCESSFULLY_FOR') + " " + editViewForm.find('option[value="' + sourceModule + '"]').text()
				});
			} else {
				Settings_Vtiger_Index_Js.showMessage({
					text: currentPrefix + " " + app.vtranslate(data.error.message),
					type: 'error'
				});
			}
		});
	},

	/**
	 * Function to handle update record with the given sequence number
	 */
	registerEventToUpdateRecordsWithSequenceNumber() {
		const editViewForm = this.getForm();
		editViewForm.find('[name="updateRecordWithSequenceNumber"]').on('click', function () {
			const sourceModule = editViewForm.find('[name="sourceModule"]').val();
			AppConnector.request({
				'module': app.getModuleName(),
				'parent': app.getParentModuleName(),
				'action': "CustomRecordNumberingAjax",
				'mode': "updateRecordsWithSequenceNumber",
				'sourceModule': sourceModule
			}).done(function (data) {
				if (data.success === true) {
					Settings_Vtiger_Index_Js.showMessage({text: app.vtranslate('JS_RECORD_NUMBERING_UPDATED_SUCCESSFULLY_FOR') + " " + editViewForm.find('option[value="' + sourceModule + '"]').text()});
				} else {
					Settings_Vtiger_Index_Js.showMessage(data.error.message);
				}
			});
		});
	},

	/**
	 * Function to register change event for prefix,postfix,reset_sequence and sequence number
	 */
	registerChangeEvent() {
		this.getForm().find('[name="prefix"],[name="leading_zeros"],[name="sequenceNumber"],[name="postfix"],[name="reset_sequence"]').on('change', this.checkPrefix.bind(this))
	},

	registerCopyClipboard: function (editViewForm) {
		new ClipboardJS('#customVariableCopy', {
			text: function (trigger) {
				Vtiger_Helper_Js.showPnotify({
					text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
					type: 'success'
				});
				return '{{' + editViewForm.find('#customVariables').val() + '}}';
			}
		});
		new ClipboardJS('#picklistVariableCopy', {
			text: function (trigger) {
				Vtiger_Helper_Js.showPnotify({
					text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
					type: 'success'
				});
				return '{{' + editViewForm.find('#picklistVariables').val() + '}}';
			}
		});
	},

	/**
	 * Check if reset sequence appears in prefix or postfix to prevent duplicate number generation
	 * @returns {boolean}
	 */
	checkPrefix() {
		let sequenceExists = false;
		const editViewForm = this.getForm();
		const value = editViewForm.find('[name="reset_sequence"]').val();
		const prefix = editViewForm.find('[name="prefix"]').val();
		const postfix = editViewForm.find('[name="postfix"]').val();
		const saveBtn = editViewForm.find('.saveButton');
		switch (value) {
			case 'Y':
				if (prefix.indexOf('{{YY}}') === -1 && prefix.indexOf('{{YYYY}}') === -1 && postfix.indexOf('{{YY}}') === -1 && postfix.indexOf('{{YYYY}}') === -1) {
					saveBtn.attr('disabled', 'disabled');
					Vtiger_Helper_Js.showMessage({
						type: 'error',
						text: app.vtranslate('JS_RS_ADD_YEAR_VARIABLE')
					});
				} else {
					saveBtn.removeAttr('disabled');
					sequenceExists = true;
				}
				break;
			case 'M':
				if (prefix.indexOf('{{MM}}') === -1 && prefix.indexOf('{{M}}') === -1 && postfix.indexOf('{{MM}}') === -1 && postfix.indexOf('{{M}}') === -1) {
					saveBtn.attr('disabled', 'disabled');
					Vtiger_Helper_Js.showMessage({
						type: 'error',
						text: app.vtranslate('JS_RS_ADD_MONTH_VARIABLE')
					});
				} else {
					saveBtn.removeAttr('disabled');
					sequenceExists = true;
				}
				break;
			case 'D':
				if (prefix.indexOf('{{DD}}') === -1 && prefix.indexOf('{{D}}') === -1 && postfix.indexOf('{{DD}}') === -1 && postfix.indexOf('{{D}}') === -1) {
					saveBtn.attr('disabled', 'disabled');
					Vtiger_Helper_Js.showMessage({
						type: 'error',
						text: app.vtranslate('JS_RS_ADD_DAY_VARIABLE')
					});
				} else {
					saveBtn.removeAttr('disabled');
					sequenceExists = true;
				}
				break;
			case 'X':
			default:
				saveBtn.removeAttr('disabled');
				sequenceExists = true;
				break;
		}
		if (sequenceExists) {
			let regex = new RegExp("{{picklist:([a-z0-9_]+)}}", 'g');
			let regexResult = (postfix + prefix).match(regex);
			if (regexResult && regexResult.length > 1) {
				Vtiger_Helper_Js.showMessage({
					type: 'error',
					text: app.vtranslate('JS_PICKLIST_TOO_MANY')
				});
				sequenceExists = false;
				saveBtn.attr('disabled', 'disabled');
			} else {
				saveBtn.removeAttr('disabled');
				sequenceExists = true;
			}
		}
		return sequenceExists;
	},

	/**
	 * Function to register events
	 */
	registerEvents: function () {
		const thisInstance = this;
		const editViewForm = this.getForm();
		this.registerOnChangeEventOfSourceModule();
		this.registerEventToUpdateRecordsWithSequenceNumber();
		this.registerChangeEvent();
		let params = app.validationEngineOptions;
		params.onValidationComplete = function (editViewForm, valid) {
			if (valid) {
				thisInstance.saveModuleCustomNumbering();
			}
			return false;
		};
		editViewForm.validationEngine('detach');
		editViewForm.validationEngine('attach', params);
		this.registerCopyClipboard(editViewForm);
	}
});
jQuery(document).ready(function () {
	var customRecordNumberingInstance = new Settings_CustomRecordNumbering_Js();
	customRecordNumberingInstance.registerEvents();
});
