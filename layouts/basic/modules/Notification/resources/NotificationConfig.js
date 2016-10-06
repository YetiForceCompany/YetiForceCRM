/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Notification_NotificationConfig_Js", {}, {
	registerEventForModal: function (container) {
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
			container.find('.watchingModule').each(function () {
				var currentTarget = $(this);
				if (currentTarget.is(':checked')) {
					selectedModules.push(currentTarget.data('nameModule'));
				}
			});
			var params = {
				module: app.getModuleName(),
				action: 'Notification',
				mode: 'saveWatchingModules',
				selctedModules: selectedModules,
				sendNotifications: container.find('.sendNotificationsSwitch').prop('checked') ? 1 : 0,
				frequency: container.find('select[name="frequency"]').val()
			};
			var progress = jQuery.progressIndicator();
			AppConnector.request(params).then(function (data) {
				progress.progressIndicator({'mode': 'hide'});
				app.hideModalWindow();
			});
		});
		container.find('.selectAllModules').on('click', function () {
			if ($(this).is(':checked')) {
				var value = true;
			} else {
				var value = false;
			}
			container.find('.watchingModule').each(function () {
				$(this).prop("checked", value);
			});
		});
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
