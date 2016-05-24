/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("Home_NotificationsList_Js", {
	setAsMarked: function (id) {
		var thisInstance = this;
		var params = {
			module: app.getModuleName(),
			action: 'Notification',
			mode: 'setMark',
			id: id
		}
		AppConnector.request(params).then(function (data) {
			var row = $('.noticeRow[data-id="' + id + '"]');
			Vtiger_Helper_Js.showPnotify({
				title: app.vtranslate('JS_MESSAGE'),
				text: app.vtranslate('JS_MARKED_AS_READ'),
				type: 'info'
			});
			if (data.result == 'hide') {
				row.fadeOut(300, function () {
					row.remove();
					thisInstance.checkHiddenBlock();
				});
			}
			var badge = $(".notificationsNotice .badge");
			var number = parseInt(badge.text()) - 1;
			if (number > 0) {
				badge.text(number);
			} else {
				badge.text('');
			}
		});
	},
	checkHiddenBlock: function () {
		var thisInstance = this;
		$(".notificationEntries").each(function (index) {
			var block = $(this);
			if (block.find(".noticeRow").length == 0) {
				block.closest('.panel').hide();
			}
		});
	},
}, {
	gridster: false,
	getGridster: function () {
		if (!this.gridster) {
			this.gridster = $("ul.gridster");
		}
		return this.gridster;
	},
	registerGridster: function () {
		var thisInstance = this;
		var gridsterObj = this.getGridster().gridster({
			widget_base_dimensions: [thisInstance.getGridster().width() / 12 - 14, 100],
			widget_margins: [7, 7],
			min_cols: 6,
			min_rows: 20,
			max_size_x: 12
		});
		gridsterObj.gridster().data('gridster').disable()
	},
	registerButtons: function () {
		var thisInstance = this;
		$('.notificationConf').on('click', function () {
			var progress = jQuery.progressIndicator();
			var url = 'index.php?module=' + app.getModuleName() + '&view=NotificationConfig';
			app.showModalWindow(null, url, function (container) {
				progress.progressIndicator({'mode': 'hide'});
				thisInstance.registerEventForModal(container);
			});
		});
	},
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
	},
	registerEvents: function () {
		this.registerGridster();
		this.registerButtons();
	}
});
