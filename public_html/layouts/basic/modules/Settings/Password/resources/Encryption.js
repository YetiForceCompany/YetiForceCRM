/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Settings_Password_Encryption_Js',
	{},
	{
		/**
		 * Container
		 */
		container: null,
		/**
		 * Set container
		 * @param {Object} element
		 */
		setContainer: function (element) {
			this.container = element;
		},
		/**
		 * Get Container
		 * @returns {Object}
		 */
		getContainer: function () {
			return this.container;
		},
		/**
		 * Get nav tab
		 * @returns {Object}
		 */
		getActiveTabNav: function () {
			return this.getContainer().find('.js-nav-container a.active');
		},
		/**
		 * Get tab (Form)
		 * @returns {Object}
		 */
		getTabForm: function () {
			return this.getContainer().find('.tab-pane.active form');
		},
		/**
		 * Register change tab
		 */
		registerChangeTab: function () {
			let container = this.getContainer();
			this.passwordAlert(container);
			container.find('.js-nav-container a').on('click', (e) => {
				this.loadTab(e.currentTarget.dataset.url);
			});
		},
		/**
		 * Load content from URL
		 * @param {string} url
		 */
		loadTab: function (url) {
			let tabContainer = this.getContainer().find('.js-tab-container');
			let progressIndicatorElement = $.progressIndicator({
				blockInfo: {
					elementToBlock: tabContainer,
					enabled: true
				}
			});
			AppConnector.request(url)
				.done((data) => {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					tabContainer.html(data);
					this.registerBasicEvents(tabContainer);
					App.Fields.Picklist.showSelect2ElementView(tabContainer.find('select'));
				})
				.fail(function (textStatus, errorThrown) {
					app.errorLog(textStatus, errorThrown);
				});
		},

		/**
		 * Register events for change method encryption
		 */
		registerChangeMethodName: function () {
			let container = this.getTabForm();
			if (container.length) {
				this.passwordAlert(container);
			}
			container.find('[name="methods"]').on('change', (e) => {
				this.passwordAlert($(e.currentTarget).closest('form'));
			});
		},
		/**
		 * Register events for change method encryption
		 */
		registerChangeModule: function () {
			this.getTabForm()
				.find('[name="target"]')
				.on('change', (e) => {
					let url = this.getActiveTabNav().data('url') + '&target=' + e.currentTarget.value;
					this.loadTab(url);
				});
		},
		/**
		 * Password alerts
		 * @param {jQuery} container
		 */
		passwordAlert: function (container) {
			let methodElement = container.find('[name="methods"]');
			let mapLengthVector = JSON.parse($('[name="lengthVectors"]').val());
			let length = mapLengthVector[methodElement.val()];
			let validator = '';
			let passwordElement = container.find('#password');
			let vectorElement = container.find('#vector');
			let passwordInfoAlert = container.find('.js-password-alert');
			if (length == undefined) {
				passwordInfoAlert.addClass('d-none');
				vectorElement.attr('disabled', 'disabled');
				passwordElement.attr('disabled', 'disabled');
			} else if (length === 0) {
				passwordInfoAlert.addClass('d-none');
				vectorElement.val('');
				vectorElement.attr('disabled', 'disabled');
			} else {
				passwordInfoAlert.removeClass('d-none');
				passwordInfoAlert.find('.js-password-length').text(length);
				vectorElement.removeAttr('disabled');
				passwordElement.removeAttr('disabled');
				validator = 'validate[required,maxSize[' + length + '],minSize[' + length + ']]';
			}
			vectorElement.attr('data-validation-engine', validator);
		},
		/**
		 * Register events for form
		 */
		registerForm: function () {
			let form = this.getTabForm();
			form.on('submit', (event) => {
				event.preventDefault();
				form.validationEngine(app.validationEngineOptions);
				if (form.validationEngine('validate')) {
					let save = () => {
						let progressElement = $.progressIndicator({ blockInfo: { enabled: true } });
						AppConnector.request(form.serializeFormData()).done((response) => {
							progressElement.progressIndicator({ mode: 'hide' });
							app.showNotify({
								text: response.result,
								type: 'info',
								hide: false
							});
							this.getActiveTabNav().trigger('click');
						});
					};
					let progressIndicatorElement = $.progressIndicator({ blockInfo: { enabled: true } });
					let formData = form.serializeFormData();
					formData.mode = 'checkEncryptionStatus';
					AppConnector.request(formData).done((response) => {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						if (response.result.message) {
							app.showConfirmModal({
								text: response.result.message + `<div>${app.vtranslate('JS_CHANGE_CONFIRMATION')}</div>`,
								confirmedCallback: () => {
									save();
								}
							});
						} else if (response.result.result) {
							save();
						}
					});
				}
			});
		},
		/**
		 * Register events to preview password
		 */
		registerPreviewPassword: function () {
			var container = this.getTabForm();
			var button = container.find('.previewPassword');
			button.on('mousedown', function () {
				$('#' + $(this).data('id')).attr('type', 'text');
			});
			button.on('mouseup', function () {
				$('#' + $(this).data('id')).attr('type', 'password');
			});
			button.on('mouseout', function () {
				$('#' + $(this).data('id')).attr('type', 'password');
			});
		},
		/**
		 * Register basic events in view
		 */
		registerBasicEvents: function () {
			this.registerForm();
			this.registerChangeMethodName();
			this.registerChangeModule();
			this.registerPreviewPassword();
		},
		/**
		 * Register all events in view
		 */
		registerEvents: function () {
			this.setContainer($('.contentsDiv'));
			this.registerChangeTab();
			this.registerBasicEvents();
		}
	}
);
