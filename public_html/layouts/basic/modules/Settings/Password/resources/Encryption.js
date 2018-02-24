/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
	 * @returns {undefined}
	 */
	registerChangeMethodName: function () {
		var container = this.getContainer();
		var methodElement = container.find('[name="methods"]');
		var mapLengthVector = JSON.parse($('[name="lengthVectors"]').val());
		methodElement.on('change', function () {
			var length = mapLengthVector[methodElement.val()];
			var validator;
			var passwordElement = container.find('[name="password"]');
			if (length === 0) {
				validator = '';
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
	 * @returns {undefined}
	 */
	registerForm: function () {
		var thisInstance = this;
		var container = thisInstance.getContainer();
		container.submit(function (event) {
			event.preventDefault();
			container.validationEngine(app.validationEngineOptions);
			if (container.validationEngine('validate')) {
				var progressIndicatorElement = jQuery.progressIndicator({
					'position': 'html',
					'blockInfo': {
						'enabled': true
					}
				});
				AppConnector.request(container.serializeFormData()).then(
						function (response) {
							progressIndicatorElement.progressIndicator({'mode': 'hide'});
							Vtiger_Helper_Js.showPnotify({
								text: response.result,
								type: 'info',
							});
						},
						function (data, err) {
							progressIndicatorElement.progressIndicator({'mode': 'hide'});
							app.errorLog(data, err);
						}
				);
			}
		});
	},
	/**
	 * Register all events in view
	 * @returns {undefined}
	 */
	registerEvents: function () {
		this.setContainer($('.formEncryption'));
		this.registerForm();
		this.registerChangeMethodName();
	}
});
