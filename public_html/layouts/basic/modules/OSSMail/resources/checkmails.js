/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
		module: 'OSSMail',
		action: 'SetUser',
		user: $(this).val()
	};
	AppConnector.request(params).done(function (_) {
		if (app.getModuleName() == 'OSSMail') {
			window.location.href = window.location.href;
		} else {
			window.location.href = 'index.php?module=OSSMail&view=Index';
		}
	});
}

function startCheckMails() {
	var users = [];
	var timeCheckingMails = $('.js-header__btn--mail').data('interval');
	$('.js-header__btn--mail .noMails').each(function (_) {
		users.push($(this).data('id'));
	});
	if (users.length > 0) {
		checkMails(users, true);
		var refreshIntervalId = setInterval(function () {
			if (window.stopScanMails == false) {
				checkMails(users);
			} else {
				clearInterval(refreshIntervalId);
			}
		}, timeCheckingMails * 1000);
	}
}

function checkMails(users, initial = false) {
	let reloadSelect = false;
	AppConnector.request({
		module: 'OSSMail',
		action: 'CheckMails',
		users: users
	})
		.done(function (response) {
			if (response.success && response.success.error != true && response.result.error != true) {
				let result = response.result;
				$('.js-header__btn--mail .noMails').each(function (_) {
					let element = jQuery(this);
					let id = element.data('id');
					if (jQuery.inArray(id, result)) {
						let num = result[id];
						if (element.is('option')) {
							element.data('nomail', num);
							reloadSelect = true;
						} else {
							let prevVal = element.data('nomail');
							element.data('nomail', num);
							let text = '';
							if (num > 0) {
								text = ' <span class="badge badge-danger mr-1">' + num + '</span>';
							}
							element.html(text);
							if (
								initial === false &&
								(this.tagName === 'SPAN' || this.selected) &&
								((prevVal < num && prevVal >= 0) || (!prevVal && num > 0))
							) {
								element.parent().effect('pulsate', 1500);
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
		})
		.fail(function () {
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
