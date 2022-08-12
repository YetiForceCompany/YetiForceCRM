/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 *************************************************************************************/
'use strict';

var Vtiger_Index_Js = {
	showLocation: function (element) {
		app.showModalWindow(null, 'index.php?module=OpenStreetMap&view=MapModal', function (container) {
			let mapView = new OpenStreetMap_Map_Js();
			mapView.registerModalView(container);
			container.find('.searchValue').val($(element).data('location'));
			container.find('.searchBtn').trigger('click');
		});
	},
	getEmailFromRecord(record, module, maxEmails) {
		const aDeferred = $.Deferred();
		const progress = $.progressIndicator({ position: 'html', blockInfo: { enabled: true } });
		AppConnector.request({
			dataType: 'html',
			data: {
				module: 'OSSMail',
				action: 'GetMail',
				sourceModule: module,
				sourceRecord: record,
				maxEmails: maxEmails
			}
		})
			.done((data) => {
				progress.progressIndicator({ mode: 'hide' });
				if (data.substring(0, 1) == '{') {
					data = JSON.parse(data);
					data = data['result'];
					aDeferred.resolve(data);
				} else {
					app.showModalWindow(data, (data) => {
						data.find('.selectButton').on('click', (e) => {
							if (data.find('input:checked').length) {
								let email = data.find('input:checked').val();
								app.hideModalWindow();
								aDeferred.resolve(email);
							} else {
								app.showNotify({
									text: app.vtranslate('JS_SELECT_AN_OPTION'),
									type: 'info'
								});
							}
						});
					});
				}
			})
			.fail((error, err) => {
				progress.progressIndicator({ mode: 'hide' });
				aDeferred.reject(error);
			});
		return aDeferred.promise();
	},
	registerMailButtons: function (container) {
		let thisInstance = this;
		container.find('.sendMailBtn:not(.mailBtnActive)').each(function (e) {
			let sendButton = $(this);
			sendButton.addClass('mailBtnActive');
			sendButton.on('click', function (e) {
				e.stopPropagation();
				let url = sendButton.data('url');
				let popup = sendButton.data('popup');
				let toMail = sendButton.data('to');
				if (toMail) {
					url += '&to=' + encodeURIComponent(toMail);
				}
				if (app.getRecordId() && sendButton.data('record') !== app.getRecordId()) {
					url += '&crmModule=' + app.getModuleName() + '&crmRecord=' + app.getRecordId();
				}
				thisInstance.sendMailWindow(url, popup);
			});
		});
	},
	sendMailWindow: function (url, popup, postData) {
		if (popup) {
			let width = screen.width - 15;
			let height = screen.height - 150;
			let left = 0;
			let top = 30;
			let popupParams = 'width=' + width + ', height=' + height + ', left=' + left + ', top=' + top;
			if (postData == undefined) {
				window.open(
					url,
					'_blank',
					popupParams + ',resizable=yes,scrollbars=yes,toolbar=no,menubar=no,location=no,status=no,menubar=no'
				);
				return;
			}
			let form = $('<form/>', { action: 'index.php' });
			url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
				form.append($('<input>', { name: key, value: value }));
			});
			for (let i in postData) {
				form.append($('<input>', { name: i, value: JSON.stringify(postData[i]) }));
			}
			$('body').append(form);
			form.trigger('submit');
		} else {
			window.location.href = url;
		}
	},
	/**
	 * Function to change user theme(colour)
	 * @params : colour name
	 */
	changeSkin: function () {
		$('.themeElement').on('click', function (e) {
			e.stopPropagation();
			let currentElement = $(e.currentTarget);
			currentElement.closest('#themeContainer').hide();
			let progressElement = $('#progressDiv');
			progressElement.progressIndicator();
			let params = {
				module: 'Users',
				action: 'SaveAjax',
				record: CONFIG.userId,
				field: 'theme',
				value: currentElement.data('skinName')
			};
			AppConnector.request(params)
				.done(function (data) {
					if (data.success && data.result) {
						progressElement.progressIndicator({ mode: 'hide' });
						$('.settingIcons').removeClass('open');
						window.location.reload();
					}
				})
				.fail(function (error, err) {});
		});
	},
	markNotifications: function (id) {
		let aDeferred = $.Deferred();
		let thisInstance = this;
		let params = {
			module: 'Notification',
			action: 'Notification',
			mode: 'setMark',
			record: id
		};
		AppConnector.request(params)
			.done(function (data) {
				let row = $('.notificationEntries .noticeRow[data-id="' + id + '"]');
				app.showNotify({
					title: app.vtranslate('JS_MESSAGE'),
					text: app.vtranslate('JS_MARKED_AS_READ'),
					type: 'info'
				});
				if (row.length) {
					row.fadeOut(300, function () {
						let entries = row.closest('.notificationEntries');
						row.remove();
						entries.each(function (index) {
							let block = $(this);
							if (block.find('.noticeRow').length == 0) {
								block.closest('.panel').hide();
							}
						});
					});
					thisInstance.getNotificationsForReminder();
				}
				aDeferred.resolve(data);
			})
			.fail(function (textStatus, errorThrown) {
				app.errorLog(textStatus, errorThrown);
				aDeferred.reject(textStatus, errorThrown);
			});
		return aDeferred.promise();
	},
	/**
	 * Function registers event for Reminder popups
	 */
	registerReminders: function () {
		let activityReminder = (parseInt(app.getMainParams('activityReminder')) || 0) * 1000;
		if (activityReminder != 0 && $('.remindersNotice.autoRefreshing').length) {
			Vtiger_Index_Js.requestReminder();
			window.reminder = setInterval(function () {
				Vtiger_Index_Js.requestReminder();
			}, activityReminder);
		}
		let reminder = (parseInt(app.getMainParams('intervalForNotificationNumberCheck')) || 0) * 1000;
		if (reminder != 0 && $('.notificationsNotice.autoRefreshing').length) {
			Vtiger_Index_Js.getNotificationsForReminder();
			window.reminderNotifications = setInterval(function () {
				Vtiger_Index_Js.getNotificationsForReminder();
			}, reminder);
		}
	},
	getNotificationsForReminder: function () {
		let thisInstance = this;
		let content = $('.remindersNotificationContainer');
		let element = $('.notificationsNotice');
		let url = 'index.php?module=Notification&view=Reminders';
		AppConnector.request(url)
			.done(function (data) {
				content.html(data);
				thisInstance.refreshReminderCount(content, element, 'js-count-notifications-reminder');
				content.find('.js-set-marked').on('click', function (e) {
					let currentElement = $(e.currentTarget);
					let recordID = currentElement.closest('.js-notification-panel').data('record');
					thisInstance.markNotifications(recordID).done(function (data) {
						currentElement.closest('.js-notification-panel').fadeOut(300, function () {
							$(this).remove();
							thisInstance.refreshReminderCount(content, element, 'js-count-notifications-reminder');
						});
					});
				});
			})
			.fail(function (data, err) {
				clearInterval(window.reminderNotifications);
			});
	},
	/**
	 * Function request for reminder popups
	 */
	requestReminder: function () {
		let thisInstance = this;
		let content = $('.remindersNoticeContainer');
		let element = $('.remindersNotice');
		let url = 'index.php?module=Calendar&view=Reminders&type_remainder=true';
		AppConnector.request(url)
			.done(function (data) {
				content.html(data);
				thisInstance.refreshReminderCount(content, element, 'countRemindersNotice');
				app.registerModal(content);
				content.find('.reminderPostpone').on('click', function (e) {
					let currentElement = $(e.currentTarget);
					let recordID = currentElement.closest('.js-toggle-panel').data('record');
					let url =
						'index.php?module=Calendar&action=ActivityReminder&mode=postpone&record=' +
						recordID +
						'&time=' +
						currentElement.data('time');
					AppConnector.request(url).done(function (data) {
						currentElement.closest('.js-toggle-panel').fadeOut(300, function () {
							$(this).remove();
							thisInstance.refreshReminderCount(content, element, 'countRemindersNotice');
						});
					});
				});
			})
			.fail(function (data, err) {
				clearInterval(window.reminder);
			});
	},
	refreshReminderCount: function (content, element, tag) {
		let badge = element.find('.badge');
		let count = content.find('.js-toggle-panel').length;
		badge.text(count);
		badge.removeClass('d-none');
		if (count > 0 && element.hasClass('autoRefreshing')) {
			element.effect('pulsate', 1500);
			if (app.cacheGet(tag) != count) {
				app.playSound('REMINDERS');
				app.cacheSet(tag, count);
			}
		} else {
			badge.addClass('d-none');
		}
	},
	registerResizeEvent: function () {
		$(window).on('resize', function () {
			if (this.resizeTO) clearTimeout(this.resizeTO);
			this.resizeTO = setTimeout(function () {
				$(this).trigger('resizeEnd');
			}, 600);
		});
	},
	changeWatching: function (instance) {
		let value, module, state, className, user, record;
		if (instance != undefined) {
			instance = $(instance);
			value = instance.data('value');
			if (instance.data('module') != undefined) {
				module = instance.data('module');
			} else {
				module = app.getModuleName();
			}
			if (instance.data('user') != undefined) {
				user = instance.data('user');
			}
			if (instance.data('record') != undefined) {
				record = instance.data('record');
			}
		}
		app.showConfirmModal({
			title: app.vtranslate('JS_WATCHING_TITLE'),
			text: app.vtranslate('JS_WATCHING_MESSAGE' + value),
			icon: 'fas fa-eye',
			confirmButtonLabel: 'LBL_YES',
			rejectedButtonLabel: 'LBL_NO',
			confirmedCallback: () => {
				Vtiger_Index_Js.updateWatching(module, value, user, record).done(function (data) {
					if (instance != undefined) {
						let buttonIcon = instance.find('.fas');
						state = data.result == 1 ? 0 : 1;
						instance.data('value', state);
						if (state == 1) {
							instance.toggleClass(instance.data('off') + ' ' + instance.data('on'));
							buttonIcon.removeClass('fas fa-eye');
							buttonIcon.addClass('fas fa-eye-slash');
						} else {
							instance.toggleClass(instance.data('on') + ' ' + instance.data('off'));
							buttonIcon.removeClass('fas fa-eye-slash');
							buttonIcon.addClass('fas fa-eye');
						}
					}
				});
			}
		});
	},
	updateWatching: function (module, value, user, record) {
		let aDeferred = $.Deferred();
		let params = {
			module: module,
			action: 'Watchdog',
			state: value
		};
		if (user != undefined) {
			params['user'] = user;
		}
		if (record != undefined && record != 0) {
			params['record'] = record;
		}
		AppConnector.request(params)
			.done(function (data) {
				aDeferred.resolve(data);
			})
			.fail(function (textStatus, errorThrown) {
				aDeferred.reject(textStatus, errorThrown);
				app.errorLog(textStatus, errorThrown);
			});
		return aDeferred.promise();
	},
	assignToOwner: function (element, userId) {
		element = $(element);
		if (userId == undefined) {
			userId = CONFIG.userId;
		}
		let params = {
			module: element.data('module'),
			record: element.data('record'),
			field: 'assigned_user_id',
			value: userId
		};
		app.saveAjax('', null, params).done(function (e) {
			app.hideModalWindow();
			if (app.getViewName() === 'List') {
				let listinstance = new Vtiger_List_Js();
				listinstance.getListViewRecords();
			} else if (app.getViewName() === 'Detail') {
				document.location.reload();
			}
		});
	},
	sendNotification: function () {
		App.Components.QuickCreate.createRecord('Notification');
	},
	performPhoneCall: function (phoneNumber, record) {
		AppConnector.request({
			module: app.getModuleName(),
			view: 'BasicAjax',
			mode: 'performPhoneCall',
			phoneNumber: phoneNumber,
			record: record
		}).done(function (response) {
			response = JSON.parse(response);
			Vtiger_Helper_Js.showMessage({ text: response.result });
		});
	},
	registerEvents: function () {
		Vtiger_Index_Js.registerReminders();
		Vtiger_Index_Js.changeSkin();
		Vtiger_Index_Js.registerResizeEvent();
	}
};
//On Page Load
jQuery(function () {
	Vtiger_Index_Js.registerEvents();
});
