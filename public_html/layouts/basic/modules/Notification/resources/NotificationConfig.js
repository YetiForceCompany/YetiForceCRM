/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class(
	'Notification_NotificationConfig_Js',
	{},
	{
		registerEventForModal: function (container) {
			const self = this;
			let table = app.registerDataTables(container.find('.js-watching-data-table'));
			app.showPopoverElementView(container.find('.infoPopover'));
			container.on('switchChange.bootstrapSwitch', '.sendNotificationsSwitch', function (e, state) {
				if (state) {
					container.find('.schedule').removeClass('d-none');
				} else {
					container.find('.schedule').addClass('d-none');
				}
			});
			container.find('[name="saveButton"]').on('click', function () {
				let selectedModules = [];
				let sendNoticeModules = [];
				table.$('[type="checkbox"]').each(function () {
					let currentTarget = $(this);
					if (currentTarget.is(':checked')) {
						selectedModules.push(currentTarget.val());
					}
				});
				table.$('.sandNoticeOn').each(function (e) {
					let value = $(this).closest('tr').data('id');
					sendNoticeModules.push(value);
				});
				let params = {
					module: 'Notification',
					action: 'Notification',
					mode: 'saveWatchingModules',
					selectedModules: selectedModules,
					sendNotifications: sendNoticeModules,
					frequency: container.find('select[name="frequency"]').val()
				};
				let progress = jQuery.progressIndicator();
				AppConnector.request(params)
					.done(function () {
						progress.progressIndicator({ mode: 'hide' });
						app.hideModalWindow();
					})
					.fail(function () {
						progress.progressIndicator({ mode: 'hide' });
						app.hideModalWindow();
					});
			});
			container.find('.selectAllModules').on('click', function (e) {
				e.stopPropagation();
				table.$('.watchingModule:not(:disabled)').prop('checked', $(this).is(':checked'));
			});
			container.find('.sentNoticeAll').on('click', function (e) {
				e.stopPropagation();
				let element = $(e.currentTarget).find('.fas');
				let val = !element.hasClass('sandNoticeOn');
				self.changeSendNoticeState(element, val);
				table.$('.sandNoticeOn,.sandNoticeOff').each(function () {
					self.changeSendNoticeState($(this), val);
				});
			});
			table.$('.sentNotice').on('click', function (e) {
				e.stopPropagation();
				let element = $(e.currentTarget).find('.fas');
				self.changeSendNoticeState(element);
			});
		},
		changeSendNoticeState: function (element, val) {
			if (val !== undefined) {
				if (val === true) {
					element.addClass('fa-bell').removeClass('fa-bell-slash');
					element.addClass('sandNoticeOn').removeClass('sandNoticeOff');
				} else {
					element.addClass('fa-bell-slash').removeClass('fa-bell');
					element.addClass('sandNoticeOff').removeClass('sandNoticeOn');
				}
			} else {
				element.toggleClass('fa-bell fa-bell-slash');
				element.toggleClass('sandNoticeOn sandNoticeOff');
			}
		},
		registerEvents: function () {
			let container = $('.modalNotificationNotificationConfig');
			this.registerEventForModal(container);
		}
	}
);
jQuery(document).ready(function () {
	let instance = new Notification_NotificationConfig_Js();
	instance.registerEvents();
});
