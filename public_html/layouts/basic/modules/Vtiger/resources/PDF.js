/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class('Vtiger_PDF_Js', {
	/**
	 * Proceed form submission
	 */
	proceedSubmit(mode) {
		const loader = $.progressIndicator({
			message: app.vtranslate('JS_PDF_GENERATING'),
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		switch (mode) {
			case 'generate_pdf':
				this.container.find('#pdfExportModal').submit();
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
	registerPreSubmitEvent: function (container) {
		const self = this;
		container.find('#generate_pdf, #single_pdf, #email_pdf').on('click', (e) => {
			e.preventDefault();
			self.proceedSubmit.apply(self, [$(e.currentTarget).attr('id')]);
		});
	},

	/**
	 * Register list view check records
	 *
	 * @param   {jQuery}  container
	 */
	registerListViewCheckRecords(container) {
		let templateElements = container.find('[name="pdf_template[]"]');
		templateElements.on('change', function () {
			document.progressLoader = $.progressIndicator({
				message: app.vtranslate('JS_PDF_RECALCULATING'),
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			let templateContainer = $(this).closest('.js-pdf-template-content');
			templateContainer.find('.js-pdf-user-variable').toggleClass('d-none');

			let templates = [];
			templateElements.filter(':checked').each(function () {
				templates.push($(this).val());
			});

			let params = container.find('form').serializeFormData();
			params.mode = 'validateRecords';
			params.templates = templates;

			AppConnector.request(params)
				.done(function (data) {
					var response = data.result;
					if (data.success) {
						let valid = response.valid;
						let info = container.find('.js-records-info').text(response.message).removeClass('d-none');
						if (valid) {
							info.addClass('d-none');
						}
						setTimeout(function () {
							document.progressLoader.progressIndicator({ mode: 'hide' });
						}, 500);

						container.find('.js-submit-button').each(function () {
							$(this).attr('disabled', !valid);
						});
					}
				})
				.fail(function (data, err) {
					app.errorLog(data, err);
				});
		});
	},

	/**
	 * Register select custom columns change
	 */
	registerSelectCustomColumnsChange() {
		this.container.find('[name="isCustomMode"]').on('change', (ev) => {
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
		this.container.find('.js-save-scheme').on('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			const loader = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});

			let params = this.container.find('form').serializeFormData();
			params.mode = 'saveInventoryColumnScheme';
			params.isCustomMode = this.container.find('[name="isCustomMode"]').is(':checked');

			AppConnector.request(params)
				.done(function (data) {
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
				.fail(function (data, err) {
					app.errorLog(data, err);
				});
		});
	},
	/**
	 * Register events
	 */
	registerEvents() {
		const container = (this.container = $('#pdfExportModal').closest('.js-modal-container'));
		this.dynamicTemplatesCount = 0;
		this.recordId = parseInt(container.find('[name="record"]').val());
		this.registerPreSubmitEvent(container);
		this.registerSaveInventoryColumnSchemeClick();
		this.registerSelectCustomColumnsChange();
		this.registerListViewCheckRecords(container);
	}
});
$(function () {
	new Vtiger_PDF_Js().registerEvents();
});
