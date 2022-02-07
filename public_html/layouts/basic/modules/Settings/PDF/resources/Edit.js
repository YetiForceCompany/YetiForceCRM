/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

Settings_Vtiger_Edit_Js(
	'Settings_PDF_Edit_Js',
	{
		instance: {}
	},
	{
		currentInstance: false,
		editContainer: false,
		init: function () {
			this.initiate();
		},
		/**
		 * Function to get the container which holds all the workflow elements
		 * @return jQuery object
		 */
		getContainer: function () {
			return this.editContainer;
		},
		/**
		 * Function to set the reports container
		 * @params : element - which represents the workflow container
		 * @return : current instance
		 */
		setContainer: function (element) {
			this.editContainer = element;
			return this;
		},
		/*
		 * Function to return the instance based on the step of the Workflow
		 */
		getInstance: function (step) {
			if (step in Settings_PDF_Edit_Js.instance) {
				return Settings_PDF_Edit_Js.instance[step];
			} else {
				var moduleClassName = 'Settings_PDF_Edit' + step + '_Js';
				Settings_PDF_Edit_Js.instance[step] = new window[moduleClassName]();
				return Settings_PDF_Edit_Js.instance[step];
			}
		},
		/*
		 * Function to get the value of the step
		 * returns 1 or 2 or 3
		 */
		getStepValue: function () {
			var container = this.currentInstance.getContainer();
			return jQuery('.step', container).val();
		},
		/*
		 * Function to initiate the step 1 instance
		 */
		initiate: function (container) {
			if (typeof container === 'undefined') {
				container = jQuery('.pdfTemplateContents');
			}
			if (container.is('.pdfTemplateContents')) {
				this.setContainer(container);
			} else {
				this.setContainer(jQuery('.pdfTemplateContents', container));
			}
			this.initiateStep('1');
			this.currentInstance.registerEvents();
		},
		/*
		 * Function to initiate all the operations for a step
		 * @params step value
		 */
		initiateStep: function (stepVal) {
			var step = 'step' + stepVal;
			this.activateHeader(step);
			this.currentInstance = this.getInstance(stepVal);
		},
		/*
		 * Function to activate the header based on the class
		 * @params class name
		 */
		activateHeader: function (step) {
			var headersContainer = jQuery('.crumbs ');
			headersContainer.find('.active').removeClass('active');
			jQuery('#' + step, headersContainer).addClass('active');
		},
		/*
		 * Function to register the click event for next button
		 */
		registerFormSubmitEvent: function (form) {
			var thisInstance = this;
			if (jQuery.isFunction(thisInstance.currentInstance.submit)) {
				form.on('submit', function (e) {
					var form = jQuery(e.currentTarget);
					var specialValidation = true;
					if (jQuery.isFunction(thisInstance.currentInstance.isFormValidate)) {
						specialValidation = thisInstance.currentInstance.isFormValidate();
					}
					if (form.validationEngine('validate') && specialValidation) {
						thisInstance.currentInstance.submit().done(function (data) {
							thisInstance.getContainer().prepend(data);
							var stepVal = thisInstance.getStepValue();
							var nextStepVal = parseInt(stepVal) + 1;
							thisInstance.initiateStep(nextStepVal);
							thisInstance.currentInstance.initialize();
							var container = thisInstance.currentInstance.getContainer();
							thisInstance.registerFormSubmitEvent(container);
							thisInstance.currentInstance.registerEvents();
							thisInstance.registerEditors(container);
						});
					}
					e.preventDefault();
				});
			}
		},
		back: function () {
			var step = this.getStepValue();
			var prevStep = parseInt(step) - 1;
			this.currentInstance.initialize();
			var container = this.currentInstance.getContainer();
			var pdfRecordElement = jQuery('[name="record"]', container);
			var pdfId = pdfRecordElement.val();
			container.remove();
			this.initiateStep(prevStep);
			var currentContainer = this.currentInstance.getContainer();
			currentContainer.show();
			jQuery('[name="record"]', currentContainer).val(pdfId);
		},
		registerCancelStepClickEvent: function (form) {
			jQuery('button.cancelLink', form).on('click', function () {
				window.history.back();
			});
		},
		/*
		 * Function to register the click event for back step
		 */
		registerBackStepClickEvent: function () {
			var thisInstance = this;
			var container = this.getContainer();
			container.on('click', '.backStep', function (e) {
				thisInstance.back();
			});
		},
		registerMetatagsClickEvent: function (form) {
			var metaTagsStatus = form.find('#metatags_status');
			if (!metaTagsStatus.is(':checked')) {
				form.find('.metatags').addClass('d-none');
			} else {
				form.find('.metatags').removeClass('d-none');
			}

			metaTagsStatus.on('change', function () {
				const status = $(this).is(':checked');
				if (!status) {
					$('.metatags', form).addClass('d-none');
				} else {
					$('#set_subject', form).val($('#secondary_name', form).val());
					$('#set_title', form).val($('#primary_name', form).val());
					$('.metatags', form).removeClass('d-none');
				}
			});
		},
		/**
		 * Register wysiwyg editors
		 * @param {jQuery} container
		 * @param {array} fonts
		 */
		registerEditors(container, fonts = ['DejaVu Sans']) {
			container.find('.js-editor').each(function () {
				const editor = $(this);
				if (typeof CONFIG.fonts !== 'undefined' && fonts.length === 1) {
					fonts = CONFIG.fonts.map((font) => font);
					fonts.unshift('DejaVu Sans');
				}
				App.Fields.Text.Editor.register(editor, {
					entities_latin: false,
					toolbar: 'PDF',
					font_defaultLabel: 'DejaVu Sans',
					fontSize_defaultLabel: '10px',
					font_names: fonts.join(';'),
					contentsCss: CONFIG.siteUrl + 'layouts/resources/fonts/fonts.css',
					height: editor.attr('id') === 'body_content' ? '800px' : '80px',
					stylesSet: [
						{
							name: 'Komorka 14',
							element: 'td',
							attributes: {
								style: 'font-size:14px'
							}
						}
					],
					allowedContent: {
						$1: {
							elements: CKEDITOR.dtd,
							attributes: true,
							classes: true,
							styles: {
								display: true,
								color: true,
								'background-color': true,
								'background-image': true,
								'font-size': true,
								'font-weight': true,
								'font-family': true,
								'text-align': true,
								'text-transform': true,
								width: true,
								height: true,
								border: true,
								'border-collapse': true,
								'cell-spacing': true,
								'vertical-align': true,
								'margin-top': true,
								'margin-bottom': true,
								'margin-left': true,
								'margin-right': true,
								'padding-top': true,
								'padding-bottom': true,
								'padding-left': true,
								'padding-right': true,
								margin: true,
								padding: true,
								'border-color': true,
								'border-width': true,
								'border-style': true,
								'border-top-color': true,
								'border-top-width': true,
								'border-top-style': true,
								'border-right-color': true,
								'border-right-width': true,
								'border-right-style': true,
								'border-bottom-color': true,
								'border-bottom-width': true,
								'border-bottom-style': true,
								'border-left-color': true,
								'border-left-width': true,
								'border-left-style': true,
								'line-height': true
							}
						}
					}
				});
			});
		},
		/**
		 * Register wysiwyg editors with fonts
		 * @param form
		 */
		registerEditorsWithFonts(form) {
			$.ajax({
				url: CONFIG.siteUrl + 'layouts/resources/fonts/fonts.json',
				method: 'GET',
				dataType: 'json'
			})
				.done((response) => {
					if (response.length === 0) {
						return this.registerEditors(form);
					}
					const fonts = response.map((font) => font.family).filter((val, index, self) => self.indexOf(val) === index);
					CONFIG.fonts = fonts;
					this.registerEditors(form, fonts);
				})
				.fail(() => {
					this.registerEditors(form);
					app.errorLog('Could not load fonts.');
				});
		},
		/**
		 * Register events
		 */
		registerEvents() {
			const form = this.currentInstance.getContainer();
			this.registerFormSubmitEvent(form);
			this.registerBackStepClickEvent();
			this.registerCancelStepClickEvent(form);
			this.registerMetatagsClickEvent(form);
			this.registerEditorsWithFonts(form);
		}
	}
);
