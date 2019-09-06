/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class('Vtiger_PDF_Js', {
	validateSubmit: function(container) {
		var templateIds = [];
		var i = 0;
		container.find('[name="pdf_template[]"]').each(function() {
			if ($(this).is(':checked')) {
				templateIds[i] = $(this).val();
				i++;
			}
		});
		if (templateIds.length > 0) {
			container.find('#generate_pdf').attr('disabled', false);
			var view = app.getUrlVar('view');
			if (
				templateIds.length > 1 ||
				(view &&
					view.replace('#', '') === 'List' &&
					JSON.parse(container.find('[name="validRecords"]').val()).length > 0)
			) {
				container.find('#single_pdf').show();
			} else {
				container.find('#single_pdf').hide();
			}
		} else {
			container.find('#generate_pdf').attr('disabled', true);
			container.find('#single_pdf').hide();
		}
	},

	/**
	 * Proceed form submission
	 */
	proceedSubmit(templateIds, mode) {
		const loader = $.progressIndicator({
			message: app.vtranslate('JS_PDF_GENERATING'),
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		this.container.find('[name="template"]').val(templateIds);
		switch (mode) {
			case 'generate_pdf':
				break;
			case 'single_pdf':
				this.container.find('[name="single_pdf"]').val(1);
				this.container.find('#pdfExportModal').submit();
				break;
			case 'email_pdf':
				this.container.find('[name="email_pdf"]').val(1);
				this.container.find('#pdfExportModal').submit();
				break;
		}
		loader.progressIndicator({ mode: 'hide' });
		app.hideModalWindow();
	},

	/*
	 * Function to register the click event for generate button
	 */
	registerPreSubmitEvent: function(container) {
		const self = this;
		container.find('#generate_pdf, #single_pdf, #email_pdf').on('click', function(e) {
			const templateIds = [];
			container.find('[name="pdf_template[]"]').each(function() {
				if ($(this).is(':checked')) {
					templateIds.push($(this).val());
				}
			});
			const view = app.getUrlVar('view');
			if (view && view.replace('#', '') === 'List') {
				container.find('[name="record"]').val(container.find('[name="validRecords"]').val());
			}
			self.proceedSubmit.apply(self, [templateIds, $(this).attr('id')]);
		});
	},
	/**
	 * Register validate submit
	 *
	 * @param   {jQuery}  container
	 */
	registerValidateSubmit(container) {
		this.validateSubmit(container);
		container.find('[name="pdf_template[]"]').on('change', () => {
			this.validateSubmit(container);
		});
	},
	/**
	 * Register list view check records
	 *
	 * @param   {jQuery}  container
	 */
	registerListViewCheckRecords(container) {
		const thisInstance = this;
		container.find('[name="pdf_template[]"]').on('change', function() {
			document.progressLoader = jQuery.progressIndicator({
				message: app.vtranslate('JS_PDF_RECALCULATING'),
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			const selectedRecords = container.find('[name="selectedRecords"]').val();
			const selectedTemplates = [];
			container.find('[name="pdf_template[]"]:checked').each(() => {
				selectedTemplates.push($(this).val());
			});
			const params = {};
			params.data = {
				module: app.getModuleName(),
				action: 'PDF',
				mode: 'validateRecords',
				records: selectedRecords,
				view: app.getViewName(),
				templates: selectedTemplates
			};
			params.dataType = 'json';
			AppConnector.request(params)
				.done(function(data) {
					var response = data['result'];
					if (data['success']) {
						container.find('[name="validRecords"]').val(JSON.stringify(response.valid_records));
						container.find('#recordsInfo').text(response.message);
						setTimeout(function() {
							document.progressLoader.progressIndicator({ mode: 'hide' });
						}, 500);
						thisInstance.validateSubmit(container);
					}
				})
				.fail(function(data, err) {
					app.errorLog(data, err);
				});
		});
	},
	/**
	 * Count selected records
	 *
	 * @param   {jQuery}  container
	 *
	 * @return  {number}
	 */
	countSelectedRecords(container) {
		return JSON.parse(container.find('[name="selectedRecords"]').val()).length;
	},

	/**
	 * Register select custom columns change
	 */
	registerSelectCustomColumnsChange() {
		this.container.find('[name="isCustomMode"]').on('change', ev => {
			if ($(ev.target).is(':checked')) {
				this.container.find('[name="inventoryColumns[]"]').prop('disabled', null);
				this.container.find('.js-save-scheme').prop('disabled', null);
			} else {
				this.container.find('[name="inventoryColumns[]"]').prop('disabled', 'disabled');
				this.container.find('.js-save-scheme').prop('disabled', 'disabled');
			}
		});
	},

	/**
	 * Register save scheme button click
	 */
	registerSaveInventoryColumnSchemeClick() {
		this.container.find('.js-save-scheme').on('click', e => {
			e.preventDefault();
			e.stopPropagation();
			const loader = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			const records = JSON.parse(this.container.find('[name="selectedRecords"]').val());
			const record = parseInt(this.container.find('[name="record"]').val());
			if (record) {
				records.push(record);
			}
			AppConnector.request({
				data: {
					module: app.getModuleName(),
					mode: 'saveInventoryColumnScheme',
					action: 'PDF',
					records,
					inventoryColumns: this.container.find('[name="inventoryColumns[]"]').val(),
					isCustomMode: this.container.find('[name="isCustomMode"]').is(':checked')
				},
				dataType: 'json'
			})
				.done(function(data) {
					const response = data['result'];
					if (data['success']) {
						loader.progressIndicator({ mode: 'hide' });
					}
					if (response['message'] && data['success']) {
						app.showNotify({
							text: response['message'],
							type: 'success'
						});
					}
				})
				.fail(function(data, err) {
					app.errorLog(data, err);
				});
		});
	},
	/**
	 * Register events
	 */
	registerEvents() {
		const container = (this.container = $('div.modal-content'));
		this.dynamicTemplatesCount = 0;
		this.registerPreSubmitEvent(container);
		this.registerSaveInventoryColumnSchemeClick();
		this.registerSelectCustomColumnsChange();
		if (app.getViewName() === 'Detail') {
			this.registerValidateSubmit(container);
		}
		if (app.getViewName() === 'List') {
			this.validateSubmit(container);
			this.registerListViewCheckRecords(container);
			const selectedRecords = JSON.parse(container.find('#all_records').val());
			const recordsInput = container.find('[name="selectedRecords"]');
			const validInput = container.find('[name="validRecords"]');
			container.find('div.modal-body').append('<p id="recordsInfo">' + app.vtranslate('JS_RECORD_INFO'), +'</p>');
			recordsInput.val(JSON.stringify(selectedRecords));
			validInput.val(JSON.stringify(selectedRecords));
			$('#recordsInfo').text(
				selectedRecords.length + ' from ' + selectedRecords.length + ' are valid for chosen template.'
			);
			this.validateSubmit(container);
		}
	}
});
$(function() {
	new Vtiger_PDF_Js().registerEvents();
});
