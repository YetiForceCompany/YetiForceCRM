/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
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
	massAddDocuments: function (url) {
		app.showModalWindow(null, url, function (container) {
			let uploadButton = container.find('#filesToUpload');
			let template = container.find('.fileContainer');
			let uploadContainer = container.find('.uploadFileContainer');
			let form = container.find('form');
			uploadButton.on('change', function () {
				uploadContainer.find('.fileItem').remove();
				let files = uploadButton[0].files;
				for (let i = 0; i < files.length; i++) {
					uploadContainer.append(template.html());
					uploadContainer.find('[name="nameFile[]"]:last').val(files[i].name);
				}
			});
			form.on('submit', function (e) {
				e.preventDefault();
				app.removeEmptyFilesInput(form[0]);
				let formData = new FormData(form[0]);
				url = 'index.php';
				if (app.getViewName() === 'Detail') {
					formData.append('createmode', 'link');
					formData.append('return_module', app.getModuleName());
					formData.append('return_id', app.getRecordId());
				}
				let params = {
					url: url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false
				};
				let progressIndicatorElement = $.progressIndicator({
					blockInfo: { enabled: true }
				});
				AppConnector.request(params).done(function (data) {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					app.hideModalWindow();
					let relatedModuleName = 'Documents';
					if (app.getViewName() === 'Detail') {
						let detailView = Vtiger_Detail_Js.getInstance();
						let selectedTabElement = detailView.getSelectedTab();
						if (selectedTabElement.data('reference') === relatedModuleName) {
							detailView.reloadTabContent();
						} else if (
							detailView.getContentHolder().find('.detailViewBlockLink').data('reference') === relatedModuleName
						) {
							Vtiger_RelatedList_Js.getInstance(
								detailView.getRecordId(),
								app.getModuleName(),
								selectedTabElement,
								relatedModuleName
							).loadRelatedList();
						} else {
							let updatesWidget = detailView
								.getContentHolder()
								.find("[data-type='RelatedModule'][data-name='Documents']");
							if (updatesWidget.length > 0) {
								let params = detailView.getFiltersData(updatesWidget);
								detailView.loadWidget(updatesWidget, params['params']);
							}
						}
					} else {
						Vtiger_List_Js.getInstance().getListViewRecords();
					}
				});
			});
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
			form.submit();
		} else {
			window.location.href = url;
		}
	},
	registerWidgetsEvents: function () {
		let widgets = $('div.widgetContainer');
		widgets.on('shown.bs.collapse', function (e) {
			let widgetContainer = $(e.currentTarget);
			Vtiger_Index_Js.loadWidgets(widgetContainer);
			let key = widgetContainer.attr('id');
			app.cacheSet(key, 1);
		});
		widgets.on('hidden.bs.collapse', function (e) {
			let widgetContainer = $(e.currentTarget);
			let imageEle = widgetContainer.parent().find('.imageElement');
			let imagePath = imageEle.data('rightimage');
			imageEle.attr('src', imagePath);
			let key = widgetContainer.attr('id');
			app.cacheSet(key, 0);
		});
	},
	/**
	 * Function is used to load the sidebar widgets
	 * @param widgetContainer - widget container
	 * @param open - widget should be open or closed
	 */
	loadWidgets: function (widgetContainer, open) {
		let message = $('.loadingWidgetMsg').html();
		if (widgetContainer.find('.card-body').html().trim()) {
			let imageEle = widgetContainer.parent().find('.imageElement');
			let imagePath = imageEle.data('downimage');
			imageEle.attr('src', imagePath);
			widgetContainer.css('height', 'auto');
			return;
		}

		widgetContainer.progressIndicator({ message: message });
		let url = widgetContainer.data('url');
		let listViewWidgetParams = {
			type: 'GET',
			url: 'index.php',
			dataType: 'html',
			data: url
		};
		AppConnector.request(listViewWidgetParams).done(function (data) {
			if (typeof open === 'undefined') open = true;
			if (open) {
				widgetContainer.progressIndicator({ mode: 'hide' });
				let imageEle = widgetContainer.parent().find('.imageElement');
				let imagePath = imageEle.data('downimage');
				imageEle.attr('src', imagePath);
				widgetContainer.css('height', 'auto');
			}
			widgetContainer.html(data);
			if (data == '') {
				widgetContainer.closest('.quickWidget').addClass('d-none');
			} else {
				let label = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader').data('label');
			}
			$('.bodyContents').trigger('Vtiger.Widget.Load.' + label, $(widgetContainer));
		});
	},
	loadWidgetsOnLoad: function () {
		let widgets = $('div.widgetContainer');
		widgets.each(function (index, element) {
			Vtiger_Index_Js.loadWidgets($(element));
		});
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
			ids: id
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
	markAllNotifications: function (element) {
		let ids = [];
		let li = $(element).closest('.notificationContainer');
		li.find('.notificationEntries .noticeRow').each(function (index) {
			ids.push($(this).data('id'));
		});
		if (ids.length == 0) {
			element.remove();
			return false;
		}
		let params = {
			module: 'Notification',
			action: 'Notification',
			mode: 'setMark',
			ids: ids
		};
		li.progressIndicator({ position: 'html' });
		AppConnector.request(params).done(function (data) {
			li.progressIndicator({ mode: 'hide' });
			app.showNotify({
				title: app.vtranslate('JS_MESSAGE'),
				text: app.vtranslate('JS_MARKED_AS_READ'),
				type: 'info'
			});
			Vtiger_Index_Js.getNotificationsForReminder();
		});
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
		bootbox.dialog({
			message: app.vtranslate('JS_WATCHING_MESSAGE' + value),
			title: '<span class="fas fa-eye mr-1"></span>' + app.vtranslate('JS_WATCHING_TITLE'),
			buttons: {
				success: {
					label: '<span class="fas fa-check mr-1"></span>' + app.vtranslate('LBL_YES'),
					className: 'btn-success',
					callback: function () {
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
				},
				danger: {
					label: '<span class="fas fa-times mr-1"></span>' + app.vtranslate('LBL_NO'),
					className: 'btn-danger',
					callback: function () {}
				}
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
	registerAfterLoginEvents: function () {
		const thisInstance = this;
		let modalContainer = false;
		if (typeof CONFIG.ShowAuthy2faModal !== 'undefined') {
			modalContainer = app.showModalWindow(
				null,
				'index.php?module=Users&view=TwoFactorAuthenticationModal&record=' + CONFIG.userId,
				() => {
					delete CONFIG.ShowAuthy2faModal;
				}
			);
		} else if (typeof CONFIG.showVisitPurpose !== 'undefined') {
			modalContainer = app.showModalWindow(null, 'index.php?module=Users&view=VisitPurpose', () => {
				delete CONFIG.showVisitPurpose;
			});
		} else if (typeof CONFIG.ShowUserPwnedPasswordChange !== 'undefined') {
			modalContainer = app.showModalWindow(
				null,
				'index.php?module=Users&view=PasswordModal&mode=change&type=pwned&record=' + CONFIG.userId,
				() => {
					delete CONFIG.ShowUserPwnedPasswordChange;
				}
			);
		} else if (typeof CONFIG.ShowUserPasswordChange !== 'undefined') {
			modalContainer = app.showModalWindow(
				null,
				'index.php?module=Users&view=PasswordModal&mode=change&record=' + CONFIG.userId,
				() => {
					delete CONFIG.ShowUserPasswordChange;
				}
			);
		} else {
			$('.js-system-modal[data-url]').each((_, e) => {
				modalContainer = app.showModalWindow(null, e.dataset.url, () => {
					e.remove();
				});
			});
		}
		if (modalContainer) {
			modalContainer.one('hidden.bs.modal', function () {
				thisInstance.registerAfterLoginEvents();
			});
		}
	},
	registerEvents: function () {
		Vtiger_Index_Js.registerWidgetsEvents();
		Vtiger_Index_Js.loadWidgetsOnLoad();
		Vtiger_Index_Js.registerReminders();
		Vtiger_Index_Js.changeSkin();
		Vtiger_Index_Js.registerResizeEvent();
		Vtiger_Index_Js.registerAfterLoginEvents();
	}
};
//On Page Load
$(document).ready(function () {
	Vtiger_Index_Js.registerEvents();
});
