/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Notification_NotificationConfig_Js", {}, {
	registerEventForModal: function (container) {
		var thisInstance = this;
		var table = app.registerDataTables(container.find('.modalDataTable'));
		app.showBtnSwitch(container.find('.switchBtn'));
		app.showPopoverElementView(container.find('.infoPopover'));
		container.on('switchChange.bootstrapSwitch', '.sendNotificationsSwitch', function (e, state) {
			if (state) {
				container.find('.schedule').removeClass('hide');
			} else {
				container.find('.schedule').addClass('hide');
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
				module: app.getModuleName(),
				action: 'Notification',
				mode: 'saveWatchingModules',
				selctedModules: selectedModules,
				sendNotifications: sendNoticeModules,
				frequency: container.find('select[name="frequency"]').val()
			};
			var progress = jQuery.progressIndicator();
			AppConnector.request(params).then(
					function (data) {
						progress.progressIndicator({'mode': 'hide'});
						app.hideModalWindow();
					},
					function (textStatus, errorThrown) {
						progress.progressIndicator({'mode': 'hide'});
						app.hideModalWindow();
						app.errorLog(textStatus, errorThrown);
					}
			);
		});
		container.find('.selectAllModules').on('click', function (e) {
			e.stopPropagation();
			table.$('.watchingModule:not(:disabled)').prop('checked', $(this).is(':checked'));
		});
		container.find('.sentNotice').on('click', function (e) {
			e.stopPropagation();
			var element = jQuery(e.currentTarget);
			var val = !element.hasClass('sandNoticeOn');
			thisInstance.changeSendNoticeState(element);
			table.$('.sandNoticeOn,.sandNoticeOff').each(function () {
				thisInstance.changeSendNoticeState($(this), val);
			});
		});
		table.$('.sandNoticeOn,.sandNoticeOff').on('click', function (e) {
			e.stopPropagation();
			var element = jQuery(e.currentTarget);
			thisInstance.changeSendNoticeState(element);
		});
	},
	changeSendNoticeState: function (element, val) {
		if (val !== undefined) {
			if (val === true) {
				element.addClass('fa-envelope').removeClass('fa-envelope-o');
				element.addClass('sandNoticeOn').removeClass('sandNoticeOff');
			} else {
				element.addClass('fa-envelope-o').removeClass('fa-envelope');
				element.addClass('sandNoticeOff').removeClass('sandNoticeOn');
			}
		} else {
			element.toggleClass('fa-envelope fa-envelope-o');
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
