/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

$.Class(
	'Base_InterestsConflictModal_JS',
	{},
	{
		/**
		 * Register modal events
		 * @param {jQuery} modalContainer
		 */
		registerEvents: function (modalContainer) {
			switch (modalContainer.data('mode')) {
				case 'users':
					this.registerUsersEvents(modalContainer);
					break;
				case 'unlock':
					this.registerUnlockEvents(modalContainer);
					break;
				case 'confirmation':
					this.registerConfirmationEvents(modalContainer);
					break;
			}
		},
		/**
		 * Register users events
		 * @param {jQuery} modalContainer
		 */
		registerUsersEvents: function (modalContainer) {
			modalContainer.find('.js-ic-canceled-btn').click(function (e) {
				let btn = $(e.currentTarget);
				let tr = btn.closest('tr');
				let icon = tr.find('.js-change-icon');
				app.hideModalWindow();
				app.showConfirmModal({
					title: app.vtranslate('JS_ENTER_A_REASON'),
					showDialog: true,
					multiLineDialog: true,
					confirmedCallback: (notice, value) => {
						AppConnector.request(
							$.extend(
								{
									module: app.getModuleName(),
									action: 'InterestsConflict',
									mode: 'usersCancel',
									id: tr.data('id'),
									comment: value
								},
								modalContainer.find('.js-modal-form').serializeFormData()
							)
						).done(function (data) {
							if (data.result) {
								btn.hide();
								if (icon.length) {
									icon.removeClass('fa-times text-danger').addClass('fa-slash text-dark');
								}
								app.showNotify({
									text: data.result.message,
									type: data.result.type
								});
							}
						});
					}
				});
			});
		},
		/**
		 * Register unlock events
		 * @param {jQuery} modalContainer
		 */
		registerUnlockEvents: function (modalContainer) {
			let form = modalContainer.find('.js-modal-form');
			modalContainer.find('.js-ic-send-btn').click(function (e) {
				if (!form.validationEngine('validate')) {
					return;
				}
				AppConnector.request(
					$.extend(
						{
							module: app.getModuleName(),
							action: 'InterestsConflict',
							mode: 'unlock'
						},
						form.serializeFormData()
					)
				).done(function (data) {
					if (data.result) {
						app.showNotify({
							text: data.result.message,
							type: data.result.type
						});
						if (data.result.type === 'success') {
							app.hideModalWindow();
						}
					}
				});
			});
		},
		/**
		 * Register confirmation events
		 * @param {jQuery} modalContainer
		 */
		registerConfirmationEvents: function (modalContainer) {
			modalContainer.find('.js-ic-confirmation').click(function (e) {
				let value = $(e.currentTarget).data('value');
				AppConnector.request(
					$.extend(
						{
							module: app.getModuleName(),
							action: 'InterestsConflict',
							mode: 'confirmation',
							value: value
						},
						modalContainer.find('.js-modal-form').serializeFormData()
					)
				).done(function (data) {
					if (data.result) {
						app.showNotify({
							text: data.result.message,
							type: data.result.type
						});
						app.hideModalWindow();
						window.location.reload();
					}
				});
			});
		}
	}
);
