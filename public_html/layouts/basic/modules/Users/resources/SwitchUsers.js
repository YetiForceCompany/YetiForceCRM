/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Vtiger_SwitchUsers_Js',
	{},
	{
		/**
		 * Modal container
		 */
		container: false,
		/**
		 * Register change user
		 */
		registerChangeUser: function () {
			this.container.find('.js-switch-user').on('change', (e) => {
				let showElement = $(e.currentTarget).find('option:selected').data('admin');
				let subContainer = this.container.find('.js-sub-container');
				if (showElement) {
					subContainer.removeClass('d-none');
				} else {
					subContainer.addClass('d-none');
				}
				subContainer.find('.js-text-element').attr('disabled', !showElement);
			});
		},
		/**
		 * Register save
		 */
		registerSave: function () {
			let form = this.container.find('form');
			this.container.find('.js-switch-btn').on('click', (e) => {
				e.preventDefault();
				if (form.validationEngine('validate')) {
					document.progressLoader = jQuery.progressIndicator({
						message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					var userId = form.find('[name="user"]').val();
					form.find('[name="id"]').val(userId);
					form.submit();
				}
			});
			this.container.find('.js-switch-to-yourself').on('click', (e) => {
				e.preventDefault();
				document.progressLoader = jQuery.progressIndicator({
					message: app.vtranslate('JS_LOADING_PLEASE_WAIT'),
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				this.container.find('.js-text-element').attr('disabled', true);
				form.submit();
			});
		},
		/**
		 * Register Events
		 */
		registerEvents: function () {
			this.container = jQuery('.switchUsersContainer');
			this.registerSave();
			this.registerChangeUser();
		}
	}
);

jQuery(document).ready(function (e) {
	var instance = new Vtiger_SwitchUsers_Js();
	instance.registerEvents();
});
