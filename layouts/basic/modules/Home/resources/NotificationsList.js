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
			Vtiger_Index_Js.requestNotifications();
		});
	},
	checkHiddenBlock: function () {
		var thisInstance = this;
		$(".notificationEntries").each(function (index) {
			var block = $(this);
			if(block.find(".noticeRow").length == 0){
				block.closest('.panel').hide();
			}
		});
	},
}, {
	registerEvents: function () {
	}
});
