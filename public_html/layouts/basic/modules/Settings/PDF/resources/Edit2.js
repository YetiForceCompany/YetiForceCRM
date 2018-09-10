/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_PDF_Edit_Js("Settings_PDF_Edit2_Js", {}, {
	step2Container: false,
	advanceFilterInstance: false,
	init: function () {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the reports step1 elements
	 * @return $ object
	 */
	getContainer: function () {
		return this.step2Container;
	},
	/**
	 * Function to set the reports step1 container
	 * @params : element - which represents the reports step1 container
	 * @return : current instance
	 */
	setContainer: function (element) {
		this.step2Container = element;
		return this;
	},
	/**
	 * Function  to intialize the reports step1
	 */
	initialize(container) {
		if (typeof container === 'undefined') {
			container = $('#pdf_step2');
		}
		if (container.is('#pdf_step2')) {
			this.setContainer(container);
		} else {
			this.setContainer($('#pdf_step2'));
		}
	},
	submit() {
		var aDeferred = $.Deferred();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		var progressIndicatorElement = $.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var saveData = form.serializeFormData();
		saveData['action'] = 'Save';
		saveData['step'] = 2;
		AppConnector.request(saveData).done(function (data) {
			data = JSON.parse(data);
			if (data.success === true) {
				AppConnector.request(formData).done(function (data) {
					form.hide();
					aDeferred.resolve(data);
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					Settings_Vtiger_Index_Js.showMessage({text: app.vtranslate('JS_PDF_SAVED_SUCCESSFULLY')});
				}).fail(function (error, err) {
						app.errorLog(error, err);
					}
				);
			}
		}).fail(function (error, err) {
			app.errorLog(error, err);
		});
		return aDeferred.promise();
	},
	registerCancelStepClickEvent: function (form) {
		$('button.cancelLink', form).on('click', function () {
			window.history.back();
		});
	},
	registerEditors(container) {
		new App.Fields.Text.Editor($(container).find('.js-editor'), {toolbar: 'Full'});
	},
	registerWatermarkTypeChange: function (container) {
		var watermarkType = container.find('#watermark_type');

		watermarkType.on('change', function () {
			container.find('.watertext').toggleClass('d-none')
			container.find('.waterimage').toggleClass('d-none')
		});
	},
	registerUploadButton: function (form) {
		form.find('#uploadWM').on('click', function (e) {
			e.preventDefault();
			var fileSelect = form.find('#watermark_image');
			// Get the selected files from the input.
			var files = fileSelect[0].files;

			// Create a new FormData object.
			var formData = new FormData();
			// Loop through each of the selected files.
			for (var i = 0; i < files.length; i++) {
				var file = files[i];

				// Check the file type.
				if (!file.type.match('image.*')) {
					continue;
				}

				// Add the file to the request.
				formData.append('watermark[]', file, file.name);
			}
			formData.append('template_id', form.find('[name="record"]').val());
			// Set up the request.
			var xhr = new XMLHttpRequest();

			// Open the connection.
			xhr.open('POST', 'index.php?module=PDF&parent=Settings&action=Watermark&mode=upload', true);

			// Set up a handler for when the request finishes.
			xhr.onload = function () {
				if (xhr.status === 200) {
					const response = JSON.parse(xhr.response);
					const templateId = form.find('[name="record"]').val();
					form.find('#watermark').html('<img src="' + response.result.base64 + '" class="col-md-9" />');
					form.find('[name="watermark_image"]').val(response.result.fileName);
					form.find('#deleteWM').removeClass('d-none');
				}
			};

			// Send the Data.
			xhr.send(formData);
		});
	},
	registerDeleteUploadButton: function (form) {
		form.find('#deleteWM').on('click', function (e) {
			e.preventDefault();
			var params = {};
			params.data = {
				parent: app.getParentModuleName(),
				module: app.getModuleName(),
				action: 'Watermark',
				mode: 'delete',
				id: form.find('[name="record"]').val()
			};
			params.dataType = 'json';
			AppConnector.request(params).done(function (data) {
				var response = data['result'];
				if (response) {
					form.find('#watermark').html('');
					form.find('[name="watermark_image"]').val('');
					form.find('#deleteWM').addClass('d-none');
				}
			}).fail(function (data, err) {
				app.errorLog(data, err);
			});
		});
	},
	registerEvents: function () {
		const container = this.getContainer();
		var opts = app.validationEngineOptions;
		// to prevent the page reload after the validation has completed
		opts['onValidationComplete'] = function (form, valid) {
			//returns the valid status
			return valid;
		};
		opts['promptPosition'] = "topLeft";
		container.validationEngine(opts);
		App.Fields.Picklist.showSelect2ElementView(container.find('.select2'));
		this.registerCancelStepClickEvent(container);
		app.showPopoverElementView(container.find('.js-popover-tooltip'));
		this.registerEditors(container);
		this.registerWatermarkTypeChange(container);
		this.registerUploadButton(container);
		this.registerDeleteUploadButton(container);
	}
});
