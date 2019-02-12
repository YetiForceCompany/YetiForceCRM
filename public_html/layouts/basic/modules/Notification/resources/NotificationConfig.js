/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery.Class("Notification_NotificationConfig_Js", {}, {
	registerEventForModal: function (container) {
		var thisInstance = this;
		var table = app.registerDataTables(container.find('.modalDataTable'));
		app.showPopoverElementView(container.find('.infoPopover'));
		container.on('switchChange.bootstrapSwitch', '.sendNotificationsSwitch', function (e, state) {
			if (state) {
				container.find('.schedule').removeClass('d-none');
			} else {
				container.find('.schedule').addClass('d-none');
			}
		});
		container.find('[name="saveButton"]').on('click', function () {
			var selectedModules = [];
			var sendNoticeModules = [];
			table.$('[type="checkbox"]').each(function () {
				var currentTarget = $(this);
				if (currentTarget.is(':checked')) {
					selectedModules.push(currentTarget.val());
				}
			});
			table.$('.sandNoticeOn').each(function (e) {
				var value = $(this).closest('tr').data('id');
				sendNoticeModules.push(value);
			});
			var params = {
				module: 'Notification',
				action: 'Notification',
				mode: 'saveWatchingModules',
				selctedModules: selectedModules,
				sendNotifications: sendNoticeModules,
				frequency: container.find('select[name="frequency"]').val()
			};
			var progress = jQuery.progressIndicator();
			AppConnector.request(params).done(function () {
				progress.progressIndicator({'mode': 'hide'});
				app.hideModalWindow();
			}).fail(function () {
				progress.progressIndicator({'mode': 'hide'});
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
			thisInstance.changeSendNoticeState(element, val);
			table.$('.sandNoticeOn,.sandNoticeOff').each(function () {
				thisInstance.changeSendNoticeState($(this), val);
			});
		});
		table.$('.sentNotice').on('click', function (e) {
			e.stopPropagation();
			let element = $(e.currentTarget).find('.fas');
			thisInstance.changeSendNoticeState(element);
		});
	},
	changeSendNoticeState: function (element, val) {
		if (val !== undefined) {
			if (val === true) {
				element.addClass('fa-envelope').removeClass('fa-envelope-open');
				element.addClass('sandNoticeOn').removeClass('sandNoticeOff');
			} else {
				element.addClass('fa-envelope-open').removeClass('fa-envelope');
				element.addClass('sandNoticeOff').removeClass('sandNoticeOn');
			}
		} else {
			element.toggleClass('fa-envelope fa-envelope-open');
			element.toggleClass('sandNoticeOn sandNoticeOff');
		}
	},
	registerEvents: function () {
		var container = $('.modalNotificationNotificationConfig');
		this.registerEventForModal(container);
	}
});
jQuery(document).ready(function () {
	var instance = new Notification_NotificationConfig_Js();
	instance.registerEvents();
});
