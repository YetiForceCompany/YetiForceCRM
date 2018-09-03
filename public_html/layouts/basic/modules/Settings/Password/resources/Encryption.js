/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_Password_Encryption_Js', {}, {
	/**
	 * Container (Form)
	 */
	container: null,
	/**
	 * Set container (Form)
	 * @param {Object} element
	 */
	setContainer: function (element) {
		this.container = element;
	},
	/**
	 * Get Container (Form)
	 * @returns {Object}
	 */
	getContainer: function () {
		return this.container;
	},
	/**
	 * Register events for change method encryption
	 */
	registerChangeMethodName: function () {
		var container = this.getContainer();
		var methodElement = container.find('[name="methods"]');
		var mapLengthVector = JSON.parse($('[name="lengthVectors"]').val());
		methodElement.on('change', function () {
			var length = mapLengthVector[methodElement.val()];
			var validator = '';
			var passwordElement = container.find('[name="password"]');
			if (typeof length === "undefined" || length === 0) {
				passwordElement.val('');
				passwordElement.attr('disabled', 'disabled');
			} else {
				passwordElement.removeAttr('disabled');
				validator = 'validate[required,maxSize[' + length + '],minSize[' + length + ']]';
			}
			passwordElement.attr('data-validation-engine', validator);
		});
	},
	/**
	 * Register events for form
	 */
	registerForm: function () {
		var thisInstance = this;
		var container = thisInstance.getContainer();
		container.on('submit', function (event) {
			event.preventDefault();
			container.validationEngine(app.validationEngineOptions);
			if (container.validationEngine('validate')) {
				var progressIndicatorElement = jQuery.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				AppConnector.request(container.serializeFormData()).done(function (response) {
					progressIndicatorElement.progressIndicator({mode: 'hide'});
					Vtiger_Helper_Js.showPnotify({
						text: response.result,
						type: 'info',
					});
				});
			}
		});
	},
	/**
	 * Register events to preview password
	 */
	registerPreviewPassword: function () {
		var container = this.getContainer();
		var button = container.find('.previewPassword');
		var passwordElement = container.find('[name="password"]');
		button.on('mousedown', function () {
			passwordElement.attr('type', 'text');
		});
		button.on('mouseup', function () {
			passwordElement.attr('type', 'password');
		});
		button.on('mouseout', function () {
			passwordElement.attr('type', 'password');
		});
	},
	/**
	 * Register all events in view
	 */
	registerEvents: function () {
		this.setContainer($('.formEncryption'));
		this.registerForm();
		this.registerChangeMethodName();
		this.registerPreviewPassword();
	}
});
