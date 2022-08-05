/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 * Class Documents_MassAddDocuments_Js.
 * @type {window.Documents_MassAddDocuments_Js}
 */
window.Documents_MassAddDocuments_Js = class Documents_MassAddDocuments_Js {
	/**
	 * Create a class object
	 * @param {jQuery} modal
	 * @param {jQuery.Event} e
	 */
	static register(modal, e) {
		const self = new window[this.name]();
		self.registerEvents(modal, e);
	}
	registerSubmit() {
		const uploadButton = this.modal.find('#filesToUpload'),
			template = this.modal.find('.fileContainer'),
			uploadContainer = this.modal.find('.uploadFileContainer');
		uploadButton.on('change', function () {
			uploadContainer.find('.fileItem').remove();
			let files = uploadButton[0].files;
			for (let file of files) {
				uploadContainer.append(template.html());
				uploadContainer.find('[name="nameFile[]"]:last').val(file.name);
			}
		});
		this.form.on('submit', (e) => {
			e.preventDefault();
			this.submit();
		});
	}
	/**
	 * Submit
	 */
	submit() {
		let moduleName = app.getModuleName(),
			recordId = app.getRecordId();
		if (this.modal.find('[name="sourceModule"]').length > 0) {
			moduleName = this.modal.find('[name="sourceModule"]').val();
		}
		if (this.modal.find('[name="sourceRecord"]').length > 0) {
			recordId = this.modal.find('[name="sourceRecord"]').val();
		}
		app.removeEmptyFilesInput(this.form[0]);
		let formData = new FormData(this.form[0]);
		if (this.button.data('view') === 'Detail') {
			formData.append('createmode', 'link');
			formData.append('return_module', moduleName);
			formData.append('return_id', recordId);
		}
		const progressIndicatorElement = $.progressIndicator({
			blockInfo: { enabled: true }
		});
		AppConnector.request({
			url: 'index.php',
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false
		}).done((response) => {
			progressIndicatorElement.progressIndicator({ mode: 'hide' });
			app.hideModalWindow(null, this.modal.parent().attr('id'));
			switch (this.button.data('view')) {
				case 'Detail':
					const detailView = Vtiger_Detail_Js.getInstance(),
						selectedTabElement = detailView.getSelectedTab();
					if (selectedTabElement.data('reference') === 'Documents') {
						detailView.reloadTabContent();
					} else if (detailView.getContentHolder().find('.detailViewBlockLink').data('reference') === 'Documents') {
						Vtiger_RelatedList_Js.getInstance(
							detailView.getRecordId(),
							app.getModuleName(),
							selectedTabElement,
							'Documents'
						).loadRelatedList();
					} else {
						const updatesWidget = this.button.closest('.widgetContentBlock');
						if (updatesWidget.length > 0) {
							detailView.loadWidget(updatesWidget, detailView.getFiltersData(updatesWidget)['params']);
						}
					}
					break;
				case 'List':
					const listInstance = Vtiger_List_Js.getInstance();
					listInstance.getListViewRecords().done(function () {
						listInstance.calculatePages().done(function () {
							listInstance.updatePagination();
						});
					});
					break;
				case 'Edit':
					if (response['result']) {
						const fieldBlock = this.button.closest('.js-field-block-column'),
							field = fieldBlock.find('[name="' + fieldBlock.data('field') + '[]"]');
						App.Fields.Utils.setValue(field, response['result'], { extend: true }, false);
					}
					break;
			}
		});
	}
	/**
	 * Register events.
	 * @param {jQuery} modal
	 * @param {jQuery.Event} e
	 */
	registerEvents(modal, e) {
		this.modal = modal;
		this.form = modal.find('form');
		this.button = $(e.currentTarget);
		this.registerSubmit();
	}
};
