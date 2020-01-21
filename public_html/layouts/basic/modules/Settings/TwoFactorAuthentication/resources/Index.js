/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class('Settings_TwoFactorAuthentication_Index_Js', {}, {
	/**
	 * Container (Form)
	 */
	container: null,
	/**
	 * Get Container (Form)
	 * @returns {Object}
	 */
	getContainer(){
		if( this.container===null ){
			this.container = $('form.js-two-factor-auth__form');
		}
		return this.container;
	},
	/**
	 * Register events for form
	 */
	registerForm(){
		let thisInstance = this;
		this.container.on('change', (event) => {
			event.preventDefault();
			thisInstance.sendForm();
		});
	},
	/**
	 * Send form data
	 */
	sendForm: function(){
		this.container.validationEngine(app.validationEngineOptions);
		if (this.container.validationEngine('validate')) {
			let progressIndicatorElement = jQuery.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			let params = this.container.serializeFormData();
			let ipAddresses = [];
			let ipAddressContainer = this.container.find('.js-ip-container_element').not('.js-base-element');
			ipAddressContainer.find('.js-ip-address').each(function() {
				ipAddresses.push($(this).val());
			});
			params['ip[]'] = ipAddresses;
			AppConnector.request(params).done((response) => {
				progressIndicatorElement.progressIndicator({mode: 'hide'});
				Vtiger_Helper_Js.showPnotify({
					text: response.result.message,
					type: 'info',
				});
			});
		}
	},
	/**
	 * Add new row
	 */
	addRow: function() {
		let sortContainer = this.container
			.find(".js-base-element")
			.clone(true, true)
			.removeClass("js-base-element");
		this.container.find(".js-ip-container").append(sortContainer);
		return sortContainer.removeClass("d-none");
	},
	/**
	 * Register list events
	 */
	registerListEvents: function() {
		this.container.find(".js-add").on("click", e => {
			this.addRow();
		});
		this.container.find(".js-clear").on("click", e => {
			$(e.currentTarget)
				.closest(".js-ip-container_element")
				.remove();
			this.sendForm();
		});
	},
	/**
	 * Register all events in view
	 */
	registerEvents() {
		this.container = this.getContainer();
		this.registerListEvents();
		this.registerForm();
	}
});
