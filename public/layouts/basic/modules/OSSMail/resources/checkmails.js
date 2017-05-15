/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/

jQuery(function () {
	if ($('#OSSMailBoxInfo').data('numberunreademails') != undefined) {
		window.stopScanMails = false;
		if (getUrlVars()['view'] != 'Popup') {
			startCheckMails();
		}
	}
	if ($('#OSSMailBoxInfo select').length > 0) {
		registerUserList();
	}
});
function registerUserList() {
	var selectUsers = $('#OSSMailBoxInfo select');
	if (selectUsers.data('select2')) {
		selectUsers.select2('destroy');
	} else {
		selectUsers.on('change', function () {
			var params = {
				'module': 'OSSMail',
				'action': "SetUser",
				'user': $(this).val(),
			};
			AppConnector.request(params).then(
					function (response) {
						if (app.getModuleName() == 'OSSMail') {
							location.reload();
						} else {
							window.location.href = "index.php?module=OSSMail&view=index";
						}
					}
			);
		});
	}
	app.showSelect2ElementView(selectUsers, {
		templateResult: function (state) {
			if (!state.id) {
				return state.text;
			}
			var element = jQuery(state.element);
			var text = element.data('nomail') ? ' (' + element.data('nomail') + ')' : '';
			var $state = $('<span>' + state.text + '<span class"text-left"><span>' + text + '</span>');
			return $state;
		},
		templateSelection: function (data) {
			var element = jQuery(data.element);
			var text = element.data('nomail') ? ' (' + element.data('nomail') + ')' : '';
			var resultContainer = jQuery('<span></span>');
			resultContainer.append(data.text + text);
			return resultContainer;
		},
		closeOnSelect: true
	});
	var select2Instance = selectUsers.data('select2');
	select2Instance.$dropdown.on('mouseup', 'li', function (e) {
		if (jQuery(e.currentTarget).attr('aria-selected') == 'true') {
			selectUsers.trigger('change');
		}
	});
	select2Instance.$container.find('.select2-selection__rendered').on('mousedown', function (e) {
		e.stopPropagation();
		selectUsers.trigger('change');
	});
}
function startCheckMails() {
	var users = [];
	var timeCheckingMails = $('#OSSMailBoxInfo').data('interval');
	$("#OSSMailBoxInfo .noMails").each(function (index) {
		users.push($(this).data('id'));
	});
	if (users.length > 0) {
		checkMails(users);
		var refreshIntervalId = setInterval(function () {
			if (window.stopScanMails == false) {
				checkMails(users);
			} else {
				clearInterval(refreshIntervalId);
			}
		}, timeCheckingMails * 1000);
	}
}
function checkMails(users) {
	var params = {
		'module': 'OSSMail',
		'action': "checkMails",
		'users': users,
	};
	var reloadSelect = false;
	AppConnector.request(params).then(
			function (response) {
				if (response.success && response.success.error != true && response.result.error != true) {
					var result = response.result;
					$("#OSSMailBoxInfo .noMails").each(function (index) {
						var element = jQuery(this);
						var id = element.data('id');
						if (jQuery.inArray(id, result)) {
							var num = result[id];
							if (element.is('option')) {
								element.data('nomail', num);
								reloadSelect = true;
							} else {
								var text = '';
								if (num > 0) {
									text = '(' + num + ')';
								}
								element.text(text);
							}
						}
					});
					if (reloadSelect) {
						registerUserList();
					}
				} else {
					window.stopScanMails = true;
				}
			},
			function (data, err) {
				window.stopScanMails = true;
			}
	);
}
function getUrlVars() {
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
		vars[key] = value;
	});
	return vars;
}
