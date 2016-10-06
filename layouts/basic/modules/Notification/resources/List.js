/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
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
