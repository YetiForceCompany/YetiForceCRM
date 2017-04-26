/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
Vtiger_List_Js("Notification_List_Js", {
	setAsMarked: function (id) {
		Vtiger_Index_Js.markNotifications(id).then(function () {
			Vtiger_Index_Js.getNotificationsForReminder();
			Vtiger_List_Js.getInstance().getListViewRecords();
		})
	}
}, {});
