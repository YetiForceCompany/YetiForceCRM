/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery(function () {
	if ($('.js-header__btn--mail').data('numberunreademails') != undefined) {
		window.stopScanMails = false;
		if (getUrlVars()['view'] != 'Popup') {
			startCheckMails();
		}
	}
	if ($('.js-header__btn--mail select').length > 0) {
		registerUserList();
	}
});

function registerUserList() {
	var selectUsers = $('.js-header__btn--mail select');
	if (selectUsers.data('select2')) {
		selectUsers.select2('destroy');
	} else {
		selectUsers.on('change', handleChangeUserEvent);
	}
	App.Fields.Picklist.showSelect2ElementView(selectUsers, {
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
	$('.js-mail-list').on('click', '.js-mail-link', handleChangeUserEvent);
}

function handleChangeUserEvent() {
	var params = {
		'module': 'OSSMail',
		'action': "SetUser",
		'user': $(this).val()
	};
	AppConnector.request(params).done(function (response) {
		if (app.getModuleName() == 'OSSMail') {
			location.reload();
		} else {
			window.location.href = "index.php?module=OSSMail&view=Index";
		}
	});
}

function startCheckMails() {
	var users = [];
	var timeCheckingMails = $('.js-header__btn--mail').data('interval');
	$(".js-header__btn--mail .noMails").each(function (index) {
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
		'action': "CheckMails",
		'users': users,
	};
	var reloadSelect = false;
	AppConnector.request(params).done(function (response) {
		if (response.success && response.success.error != true && response.result.error != true) {
			var result = response.result;
			$(".js-header__btn--mail .noMails").each(function (index) {
				var element = jQuery(this);
				var id = element.data('id');
				if (jQuery.inArray(id, result)) {
					var num = result[id];
					if (element.is('option')) {
						element.data('nomail', num);
						reloadSelect = true;
					} else {
						let prevVal = element.data('nomail');
						element.data('nomail', num);
						var text = '';
						if (num > 0) {
							text = ' <span class="badge badge-danger mr-1">' + num + '</span>';
						}
						element.html(text);
						if ((prevVal < num && prevVal >= 0) || (!prevVal && num > 0)) {
							element.parent().effect("pulsate", 1500);
							app.playSound('MAILS');
						}
					}
				}
			});
			if (reloadSelect) {
				registerUserList();
			}
		} else {
			window.stopScanMails = true;
		}
	}).fail(function () {
		window.stopScanMails = true;
	});
}

function getUrlVars() {
	var vars = {};
	window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
		vars[key] = value;
	});
	return vars;
}
