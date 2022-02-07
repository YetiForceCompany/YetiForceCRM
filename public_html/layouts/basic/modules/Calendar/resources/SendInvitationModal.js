/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Calendar_SendInvitationModal_JS',
	{},
	{
		/**
		 * Modal container
		 */
		container: false,
		/**
		 * Open mail client
		 */
		openMailClient() {
			$('.js-modal__save', this.container).on('click', (_) => {
				let url = 'index.php?module=OSSMail&view=Compose';
				let formData = this.container.find('form').serializeFormData();
				for (let i in formData) {
					let value = typeof formData[i] === 'object' ? formData[i].join(',') : formData[i];
					url += `&${i}=` + encodeURIComponent(value);
				}
				Vtiger_Index_Js.sendMailWindow(url, true);
				app.hideModalWindow(false, this.container.closest('.js-modal-container')[0].id);
			});
		},
		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			this.container = modalContainer;
			this.openMailClient();
		}
	}
);
