/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

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
		App.Fields.Picklist.showSelect2ElementView(this.getContainer().find('[name="default_host"]'), {
			delimiter: ',',
			persist: false,
			tags: true,
			placeholder : app.vtranslate('JS_SELECT_OR_WRITE_AND_PRESS_ENTER'),
			create: function (input) {
				return {
					value: input,
					text: input
				};
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
				AppConnector.request(container.serializeFormData()).done(function (data) {
					var response = data['result'], params;
					if (response['success']) {
						params = {
							text: response['data'],
							type: 'info',
						};
						Vtiger_Helper_Js.showPnotify(params);
					} else {
						params = {
							text: response['data'],
						};
						Vtiger_Helper_Js.showPnotify(params);
					}
				});
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
