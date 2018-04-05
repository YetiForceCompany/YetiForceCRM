/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
jQuery.Class("Settings_OSSMail_Index_Js", {}, {
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
	 * Register the field with hosts
	 */
	registerDefaultHost: function () {
		App.Fields.Picklist.showSelectizeElementView(this.getContainer().find('[name="default_host"]'), {
			delimiter: ',',
			persist: false,
			create: function (input) {
				return {
					value: input,
					text: input
				}
			}
		});
	},

	/**
	 * Register events for form
	 * @returns {undefined}
	 */
	registerForm: function () {
		var thisInstance = this;
		var container = thisInstance.getContainer();
		container.on('submit', function (event) {
			event.preventDefault();
			container.validationEngine(app.validationEngineOptions);
			if (container.validationEngine('validate')) {
				AppConnector.request(container.serializeFormData()).then(
					function (data) {
						var response = data['result'];
						if (response['success']) {
							var params = {
								text: response['data'],
								type: 'info',
							};
							Vtiger_Helper_Js.showPnotify(params);
						} else {
							var params = {
								text: response['data'],
							};
							Vtiger_Helper_Js.showPnotify(params);
						}
					},
					function (data, err) {
						app.errorLog(data, err);
					}
				);
			}
		});
	},
	/**
	 * Main function
	 */
	registerEvents: function () {
		this.setContainer($('.roundcubeConfig'));
		this.registerDefaultHost();
		this.registerForm();
	}
});
