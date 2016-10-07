/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_List_Js("Notification_List_Js", {
	setAsMarked: function (id) {
		var params = {
			module: app.getModuleName(),
			action: 'Notification',
			mode: 'setMark',
			ids: id
		}
		AppConnector.request(params).then(function (data) {
			var row = $('.noticeRow[data-id="' + id + '"]');
			Vtiger_Helper_Js.showPnotify({
				title: app.vtranslate('JS_MESSAGE'),
				text: app.vtranslate('JS_MARKED_AS_READ'),
				type: 'info'
			});
			var badge = $(".notificationsNotice .badge");
			var number = parseInt(badge.text()) - 1;
			if (number > 0) {
				badge.text(number);
			} else {
				badge.text('');
			}
			Vtiger_List_Js.getInstance().getListViewRecords();
		});
	}
}, {});
