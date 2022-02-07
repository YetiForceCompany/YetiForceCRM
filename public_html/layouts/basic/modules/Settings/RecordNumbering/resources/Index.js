/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Settings_RecordNumbering_Index_Js',
	{},
	{
		form: false,
		clipBoardInstances: [],
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
			const sequenceBtn = editViewForm.find('.js-adavanced-sequence');
			editViewForm.find('[name="sourceModule"]').on('change', (e) => {
				$('.saveButton').removeAttr('disabled');
				sequenceBtn.addClass('d-none');
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					view: 'CustomRecordNumbering',
					sourceModule: $(e.currentTarget).val()
				}).done((data) => {
					if (data) {
						$('.js-container').html(data);
						this.form = false;
						this.registerEvents();
					}
				});
			});
		},

		/**
		 * Function to register event for saving module custom numbering
		 */
		saveModuleCustomNumbering() {
			if ($('.saveButton').attr('disabled')) {
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
			if (
				sequenceNumber < oldSequenceNumber &&
				currentPrefix === prefix.data('oldPrefix') &&
				currentPostfix === postfix.data('oldPostfix')
			) {
				sequenceNumberElement.validationEngine(
					'showPrompt',
					app.vtranslate('JS_SEQUENCE_NUMBER_MESSAGE') + ' ' + oldSequenceNumber,
					'error',
					'topLeft',
					true
				);
				return;
			}
			editViewForm.find('.saveButton').attr('disabled', 'disabled');
			AppConnector.request({
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'saveModuleCustomNumberingData',
				sourceModule: sourceModule,
				prefix: currentPrefix,
				leading_zeros: leadingZeros,
				postfix: currentPostfix,
				sequenceNumber: sequenceNumber,
				reset_sequence: editViewForm.find('[name="reset_sequence"]').val()
			}).done(function (data) {
				if (data.success === true) {
					Settings_Vtiger_Index_Js.showMessage({
						text:
							app.vtranslate('JS_RECORD_NUMBERING_SAVED_SUCCESSFULLY_FOR') +
							' ' +
							editViewForm.find('option[value="' + sourceModule + '"]').text()
					});
				} else {
					Settings_Vtiger_Index_Js.showMessage({
						text: currentPrefix + ' ' + app.vtranslate(data.error.message),
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
			$('[name="updateRecordWithSequenceNumber"]')
				.off('click')
				.on('click', function () {
					const sourceModule = editViewForm.find('[name="sourceModule"]').val();
					AppConnector.request({
						module: app.getModuleName(),
						parent: app.getParentModuleName(),
						action: 'SaveAjax',
						mode: 'updateRecordsWithSequenceNumber',
						sourceModule: sourceModule
					}).done(function (data) {
						if (data.success === true) {
							Settings_Vtiger_Index_Js.showMessage({
								text:
									app.vtranslate('JS_RECORD_NUMBERING_UPDATED_SUCCESSFULLY_FOR') +
									' ' +
									editViewForm.find('option[value="' + sourceModule + '"]').text()
							});
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
			this.getForm()
				.find('[name="prefix"],[name="leading_zeros"],[name="sequenceNumber"],[name="postfix"],[name="reset_sequence"]')
				.on('change', this.checkPrefix.bind(this));
		},

		registerCopyClipboard: function (editViewForm) {
			for (let i in this.clipBoardInstances) {
				this.clipBoardInstances[i].destroy();
			}
			this.clipBoardInstances[0] = new ClipboardJS('#customVariableCopy', {
				text: function (trigger) {
					app.showNotify({
						text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
						type: 'success'
					});
					return '{{' + editViewForm.find('#customVariables').val() + '}}';
				}
			});
			this.clipBoardInstances[1] = new ClipboardJS('#picklistVariableCopy', {
				text: function (trigger) {
					app.showNotify({
						text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
						type: 'success'
					});
					return '{{' + editViewForm.find('#picklistVariables').val() + '}}';
				}
			});
			this.clipBoardInstances[2] = new ClipboardJS('#referenceVariableCopy', {
				text: function (trigger) {
					app.showNotify({
						text: app.vtranslate('JS_NOTIFY_COPY_TEXT'),
						type: 'success'
					});
					return editViewForm.find('#referenceVariables').val();
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
					if (
						prefix.indexOf('{{YY}}') === -1 &&
						prefix.indexOf('{{YYYY}}') === -1 &&
						postfix.indexOf('{{YY}}') === -1 &&
						postfix.indexOf('{{YYYY}}') === -1
					) {
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
					if (
						prefix.indexOf('{{MM}}') === -1 &&
						prefix.indexOf('{{M}}') === -1 &&
						postfix.indexOf('{{MM}}') === -1 &&
						postfix.indexOf('{{M}}') === -1
					) {
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
					if (
						prefix.indexOf('{{DD}}') === -1 &&
						prefix.indexOf('{{D}}') === -1 &&
						postfix.indexOf('{{DD}}') === -1 &&
						postfix.indexOf('{{D}}') === -1
					) {
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
				let regex = new RegExp('{{picklist:([a-z0-9_]+)}}', 'g');
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
			this.checkAdvancedSequenceBtn();
			return sequenceExists;
		},

		/**
		 * Function to enable button if prefix or postfix has picklist value
		 */
		checkAdvancedSequenceBtn: function () {
			const editViewForm = this.getForm();
			const sequenceBtn = editViewForm.find('.js-adavanced-sequence');
			const prefix = editViewForm.find('[name="prefix"]').val();
			const postfix = editViewForm.find('[name="postfix"]').val();
			let regex = new RegExp('{{picklist:([a-z0-9_]+)}}|\\$\\(relatedRecord', 'g');
			let regexResult = (postfix + prefix).match(regex);
			if ((regexResult && regexResult.length > 1) || !regexResult) {
				sequenceBtn.addClass('d-none');
			} else {
				sequenceBtn.removeClass('d-none');
			}
		},

		/**
		 * Function to register event on button
		 */
		registerAdavancedSequenceEvent: function () {
			const editViewForm = this.getForm();
			const sequenceBtn = editViewForm.find('.js-adavanced-sequence');
			sequenceBtn.on('click', function () {
				let sourceModule = editViewForm.find('[name="sourceModule"]').val();
				let picklistName = '';
				let prefix = editViewForm.find('[name="prefix"]').val();
				let postfix = editViewForm.find('[name="postfix"]').val();
				let regex = new RegExp('{{picklist:([a-z0-9_]+)}}', 'g');
				if (prefix.match(regex)) {
					picklistName = prefix;
				} else if (postfix.match(regex)) {
					picklistName = postfix;
				}
				AppConnector.request({
					module: app.getModuleName(),
					parent: app.getParentModuleName(),
					view: 'Advanced',
					sourceModule: sourceModule,
					picklist: picklistName
				}).done(function (data) {
					if (data) {
						app.showModalWindow(data, function (container) {
							let modalForm = container.find('form');
							modalForm.validationEngine(app.validationEngineOptionsForRecord);
							container.on('click', '.js-modal__save', function (e) {
								if (modalForm.validationEngine('validate')) {
									let progressIndicatorElement = $.progressIndicator({
										position: 'html',
										blockInfo: {
											enabled: true
										}
									});
									AppConnector.request({
										module: app.getModuleName(),
										parent: app.getParentModuleName(),
										action: 'SaveAjax',
										mode: 'saveModuleCustomNumberingAdvanceData',
										sourceModule: sourceModule,
										sequenceNumber: modalForm.find('.js-picklist-sequence').serializeFormData()
									}).done(function (data) {
										progressIndicatorElement.progressIndicator({ mode: 'hide' });
										if (data.success === true) {
											Settings_Vtiger_Index_Js.showMessage({
												text:
													app.vtranslate('JS_RECORD_NUMBERING_SAVED_SUCCESSFULLY_FOR') +
													' ' +
													editViewForm.find('option[value="' + sourceModule + '"]').text()
											});
											app.hideModalWindow();
										}
									});
								}
							});
						});
					}
				});
			});
		},

		/**
		 * Function to register events
		 */
		registerEvents: function () {
			const thisInstance = this;
			const editViewForm = this.getForm();
			App.Fields.Picklist.showSelect2ElementView(editViewForm.find('select'));
			this.registerOnChangeEventOfSourceModule();
			this.registerEventToUpdateRecordsWithSequenceNumber();
			this.registerChangeEvent();
			this.checkAdvancedSequenceBtn();
			this.registerAdavancedSequenceEvent();
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
	}
);
