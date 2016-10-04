/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/

var Vtiger_Index_Js = {
	/**
	 * Function to show email preview in popup
	 */
	showEmailPreview: function (recordId, parentId) {
		var popupInstance = Vtiger_Popup_Js.getInstance();
		var params = {};
		params['module'] = "Emails";
		params['view'] = "ComposeEmail";
		params['mode'] = "emailPreview";
		params['record'] = recordId;
		params['parentId'] = parentId;
		params['relatedLoad'] = true;
		popupInstance.show(params);
	},
	getEmailFromRecord: function (record, module, maxEmails) {
		var aDeferred = jQuery.Deferred();
		AppConnector.request({
			dataType: 'html',
			data: {
				module: 'OSSMail',
				action: 'GetMail',
				sourceModule: module,
				sourceRecord: record,
				maxEmails: maxEmails,
			}
		}).then(function (data) {
			if (data.substring(0, 1) == '{') {
				data = $.parseJSON(data);
				data = data['result'];
				aDeferred.resolve(data);
			} else {
				app.showModalWindow(data, function (data) {
					data.find('.selectButton').click(function (e) {
						var email = data.find('input:checked').val();
						app.hideModalWindow(data);
						aDeferred.resolve(email);
					});
				});
			}
		}, function (error, err) {
			aDeferred.reject(error);
		})
		return aDeferred.promise();
	},
	registerMailButtons: function (container) {
		var thisInstance = this;
		container.find('.sendMailBtn:not(.mailBtnActive)').each(function (e) {
			var sendButton = jQuery(this);
			sendButton.addClass('mailBtnActive');
			sendButton.click(function (e) {
				e.stopPropagation();
				var url = sendButton.data("url");
				var module = sendButton.data("module");
				var record = sendButton.data("record");
				var popup = sendButton.data("popup");
				var toMail = sendButton.data("to");
				if (toMail) {
					url += '&to=' + toMail;
				}
				if (module != undefined && record != undefined && !toMail) {
					thisInstance.getEmailFromRecord(record, module).then(function (data) {
						if (data != '') {
							url += '&to=' + data;
						}
						thisInstance.sendMailWindow(url, popup);
					});
				} else {
					thisInstance.sendMailWindow(url, popup);
				}
			});
		});
	},
	sendMailWindow: function (url, popup, postData) {
		if (popup) {
			var width = screen.width - 15;
			var height = screen.height - 150;
			var left = 0;
			var top = 30;
			var popupParams = 'width=' + width + ', height=' + height + ', left=' + left + ', top=' + top;
			if (postData == undefined) {
				window.open(url, '_blank', popupParams + ',resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,status=nomenubar=no');
				return;
			}
			var form = $("<form/>", {action: 'index.php'});
			var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
				form.append($("<input>", {name: key, value: value}));
			});
			for (var i in postData) {
				form.append($("<input>", {name: i, value: JSON.stringify(postData[i])}));
			}
			$('body').append(form);
			form.submit();
		} else {
			window.location.href = url;
		}
	},
	registerWidgetsEvents: function () {
		var widgets = jQuery('div.widgetContainer');
		widgets.on('shown.bs.collapse', function (e) {
			var widgetContainer = jQuery(e.currentTarget);
			Vtiger_Index_Js.loadWidgets(widgetContainer);
			var key = widgetContainer.attr('id');
			app.cacheSet(key, 1);
		});
		widgets.on('hidden.bs.collapse', function (e) {
			var widgetContainer = jQuery(e.currentTarget);
			var imageEle = widgetContainer.parent().find('.imageElement');
			var imagePath = imageEle.data('rightimage');
			imageEle.attr('src', imagePath);
			var key = widgetContainer.attr('id');
			app.cacheSet(key, 0);
		});
	},
	/**
	 * Function is used to load the sidebar widgets
	 * @param widgetContainer - widget container
	 * @param open - widget should be open or closed
	 */
	loadWidgets: function (widgetContainer, open) {
		var message = jQuery('.loadingWidgetMsg').html();
		if (widgetContainer.find('.panel-body').html().trim()) {
			var imageEle = widgetContainer.parent().find('.imageElement');
			var imagePath = imageEle.data('downimage');
			imageEle.attr('src', imagePath);
			widgetContainer.css('height', 'auto');
			return;
		}

		widgetContainer.progressIndicator({'message': message});
		var url = widgetContainer.data('url');
		var listViewWidgetParams = {
			"type": "GET", "url": "index.php",
			"dataType": "html", "data": url
		}
		AppConnector.request(listViewWidgetParams).then(
				function (data) {

					if (typeof open == 'undefined')
						open = true;
					if (open) {
						widgetContainer.progressIndicator({'mode': 'hide'});
						var imageEle = widgetContainer.parent().find('.imageElement');
						var imagePath = imageEle.data('downimage');
						imageEle.attr('src', imagePath);
						widgetContainer.css('height', 'auto');
					}
					widgetContainer.html(data);
					if (data == '') {
						widgetContainer.closest('.quickWidget').addClass('hide');
					} else {
						var label = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader').data('label');
					}
					jQuery('.bodyContents').trigger('Vtiger.Widget.Load.' + label, jQuery(widgetContainer));
				}
		);
	},
	loadWidgetsOnLoad: function () {
		var widgets = jQuery('div.widgetContainer');
		widgets.each(function (index, element) {
			Vtiger_Index_Js.loadWidgets(jQuery(element));
		});
	},
	/**
	 * Function to change user theme(colour)
	 * @params : colour name
	 */
	changeSkin: function () {
		jQuery('.themeElement').on('click', function (e) {
			e.stopPropagation();
			var currentElement = jQuery(e.currentTarget);
			currentElement.closest('#themeContainer').hide();
			var progressElement = jQuery('#progressDiv');
			progressElement.progressIndicator();
			var params = {
				'module': 'Users',
				'action': 'SaveAjax',
				'record': jQuery('#current_user_id').val(),
				'field': 'theme',
				'value': currentElement.data('skinName')
			}
			AppConnector.request(params).then(function (data) {
				if (data.success && data.result) {
					progressElement.progressIndicator({'mode': 'hide'});
					jQuery('.settingIcons').removeClass('open');
					window.location.reload();
				}
			},
					function (error, err) {
					});
		})
	},
	/**
	 * Function to show compose email popup based on number of
	 * email fields in given module,if email fields are more than
	 * one given option for user to select email for whom mail should
	 * be sent,or else straight away open compose email popup
	 * @params : accepts params object
	 *
	 * @cb: callback function to recieve the child window reference.
	 */

	showComposeEmailPopup: function (params, cb) {
		var currentModule = "Emails";
		Vtiger_Helper_Js.checkServerConfig(currentModule).then(function (data) {
			if (data == true) {
				var css = jQuery.extend({'text-align': 'left'}, css);
				AppConnector.request(params).then(
						function (data) {
							var cbargs = [];
							if (data) {
								data = jQuery(data);
								var form = data.find('#SendEmailFormStep1');
								var emailFields = form.find('.emailField');
								var length = emailFields.length;
								var emailEditInstance = new Emails_MassEdit_Js();
								if (length > 1) {
									app.showModalWindow(data, function (data) {
										emailEditInstance.registerEmailFieldSelectionEvent();
										if (jQuery('#multiEmailContainer').height() > 300) {
											jQuery('#multiEmailContainer').slimScroll({
												height: '300px',
												railVisible: true,
												alwaysVisible: true,
												size: '6px'
											});
										}
									}, css);
								} else {
									emailFields.attr('checked', 'checked');
									var params = form.serializeFormData();
									// http://stackoverflow.com/questions/13953321/how-can-i-call-a-window-child-function-in-javascript
									// This could be useful for the caller to invoke child window methods post load.
									var win = emailEditInstance.showComposeEmailForm(params);
									cbargs.push(win);
								}
							}
							if (typeof cb == 'function')
								cb.apply(null, cbargs);
						},
						function (error, err) {

						}
				);
			} else {
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
			}
		})

	},
	registerCheckNotifications: function (repeat) {
		var thisInstance = this;
		var notificationsButton = jQuery('.notificationsNotice.quickAction.autoRefreshing');
		if (notificationsButton.length < 1) {
			return false;
		}
		var delay = parseInt(app.getMainParams('intervalForNotificationNumberCheck')) * 1000;
		var currentTime = new Date().getTime();
		var nextActivityReminderCheck = app.cacheGet('NotificationsNextCheckTime', 0);
		if ((currentTime - delay) > nextActivityReminderCheck) {
			Vtiger_Index_Js.requestNotification();
			var currentTime = new Date().getTime();
			app.cacheSet('NotificationsNextCheckTime', (currentTime + delay));
		} else {
			thisInstance.setNotification(app.cacheGet('NotificationsData', 0));
		}
		if (repeat !== false) {
			setTimeout('Vtiger_Index_Js.registerCheckNotifications()', delay);
		}

	},
	requestNotification: function () {
		var thisInstance = this;
		AppConnector.request({
			async: false,
			dataType: 'json',
			data: {
				module: 'Home',
				action: 'Notification',
				mode: 'getNumberOfNotifications'
			}
		}).then(function (data) {
			var notificationsCount = data.result;
			app.cacheSet('NotificationsData', notificationsCount);
			thisInstance.setNotification(notificationsCount);
		})
	},
	setNotification: function (notificationsCount) {
		var badge = $(".notificationsNotice .badge");
		badge.text(notificationsCount);
		badge.removeClass('hide');
		if (notificationsCount > 0) {
			$(".notificationsNotice .isBadge").effect("pulsate", 1500);
		} else {
			badge.addClass('hide');
		}
	},
	markNotifications: function (id) {
		var thisInstance = this;
		var params = {
			module: 'Home',
			action: 'Notification',
			mode: 'setMark',
			ids: id
		}
		AppConnector.request(params).then(function (data) {
			var row = $('.notificationEntries .noticeRow[data-id="' + id + '"]');
			Vtiger_Helper_Js.showPnotify({
				title: app.vtranslate('JS_MESSAGE'),
				text: app.vtranslate('JS_MARKED_AS_READ'),
				type: 'info'
			});
			if (data.result) {
				row.fadeOut(300, function () {
					var entries = row.closest('.notificationEntries')
					row.remove();
					entries.each(function (index) {
						var block = $(this);
						if (block.find(".noticeRow").length == 0) {
							block.closest('.panel').hide();
						}
					});
				});
			}
			app.cacheSet('NotificationsNextCheckTime', 0);
			Vtiger_Index_Js.registerCheckNotifications(false);
		});
	},
	markAllNotifications: function (element) {
		var ids = [];
		var li = $(element).closest('.notificationContainer');
		li.find('.notificationEntries .noticeRow').each(function (index) {
			ids.push($(this).data('id'));
		});
		if (ids.length == 0) {
			element.remove();
			return false;
		}
		var params = {
			module: 'Home',
			action: 'Notification',
			mode: 'setMark',
			ids: ids
		}
		li.progressIndicator({'position': 'html'});
		AppConnector.request(params).then(function (data) {
			li.progressIndicator({'mode': 'hide'});
			Vtiger_Helper_Js.showPnotify({
				title: app.vtranslate('JS_MESSAGE'),
				text: app.vtranslate('JS_MARKED_AS_READ'),
				type: 'info'
			});
			app.cacheSet('NotificationsNextCheckTime', 0);
			Vtiger_Index_Js.registerCheckNotifications(false);
		});
	},
	/**
	 * Function registers event for Calendar Reminder popups
	 */
	registerActivityReminder: function () {
		var activityReminder = (parseInt(app.getMainParams('activityReminder')) || 0) * 1000;
		if (activityReminder != 0 && jQuery('.remindersNotice.quickAction.autoRefreshing').length) {
			Vtiger_Index_Js.requestReminder();
			window.reminder = setInterval(function () {
				Vtiger_Index_Js.requestReminder();
			}, activityReminder);
		}
	},
	/**
	 * Function request for reminder popups
	 */
	requestReminder: function () {
		var thisInstance = this;
		var content = $('.remindersNoticeContainer');
		var url = 'index.php?module=Calendar&view=Reminders&type_remainder=true';
		AppConnector.request(url).then(function (data) {
			content.html(data);
			thisInstance.refreshNumberNotifications(content);
			app.registerModal(content);
			content.find('.reminderPostpone').on('click', function (e) {
				var currentElement = jQuery(e.currentTarget);
				var recordID = currentElement.closest('.panel').data('record');
				var url = 'index.php?module=Calendar&action=ActivityReminder&mode=postpone&record=' + recordID + '&time=' + currentElement.data('time');
				AppConnector.request(url).then(function (data) {
					currentElement.closest('.panel').fadeOut(300, function () {
						$(this).remove();
						thisInstance.refreshNumberNotifications(content);
					});
				});
			});
		}, function (data, err) {
			clearInterval(window.reminder);
		});
	},
	refreshNumberNotifications: function (content) {
		var remindersNotice = $(".remindersNotice");
		var badge = remindersNotice.find('.badge');
		var count = content.find('.panel:visible').length;
		badge.text(count);
		badge.removeClass('hide');
		if (count > 0 && remindersNotice.hasClass('autoRefreshing')) {
			$(".remindersNotice .isBadge").effect("pulsate", 1500);
			if (app.cacheGet('countRemindersNotice') != count) {
				app.playSound('REMINDERS');
				app.cacheSet('countRemindersNotice', count);
			}
		} else {
			badge.addClass('hide');
		}
	},
	registerResizeEvent: function () {
		$(window).resize(function () {
			if (this.resizeTO)
				clearTimeout(this.resizeTO);
			this.resizeTO = setTimeout(function () {
				$(this).trigger('resizeEnd');
			}, 600);
		});
		$(window).bind('resizeEnd', function () {
			Vtiger_Index_Js.adjustTopMenuBarItems();
		});
	},
	/**
	 * Function to make top-bar menu responsive.
	 */
	adjustTopMenuBarItems: function () {
		// Dedicated space for all dropdown text
		var TOLERANT_MAX_GAP = 125; // px
		var menuBarWrapper = ($(window).outerWidth() < 1161) ? jQuery('#mediumNav') : jQuery('#largeNav');
		var topMenuBarWidth = menuBarWrapper.parent().outerWidth();
		var optionalBarItems = jQuery('.opttabs', menuBarWrapper), optionalBarItemsCount = optionalBarItems.length;
		var optionalBarItemIndex = optionalBarItemsCount;
		function enableOptionalTopMenuItem() {
			var opttab = (optionalBarItemIndex > 0) ? optionalBarItems[optionalBarItemIndex - 1] : null;
			if (opttab) {
				opttab = jQuery(opttab);
				opttab.hide();
				optionalBarItemIndex--;
			}
			return opttab;
		}
		// Loop and enable hidden menu item until the tolerant width is reached.
		var stopLoop = false;
		do {
			if ((topMenuBarWidth - menuBarWrapper.outerWidth()) < TOLERANT_MAX_GAP) {
				var lastOptTab = enableOptionalTopMenuItem();
				if (lastOptTab == null || (topMenuBarWidth - menuBarWrapper.outerWidth()) > TOLERANT_MAX_GAP) {
					if (lastOptTab)
						lastOptTab.hide();
					stopLoop = true;
					break;
				}
			} else {
				stopLoop = true;
				break;
			}
		} while (!stopLoop);
		// Required to get the functionality of All drop-down working.
		jQuery(window).load(function () {
			jQuery("#topMenus").css({'overflow': 'visible'});
		});
	},
	/**
	 * Function to trigger tooltip feature.
	 */
	registerTooltipEvents: function () {
		var references = jQuery.merge(jQuery('[data-field-type="reference"] > a'), jQuery('[data-field-type="multireference"] > a'));
		var lastPopovers = [];
		// Fetching reference fields often is not a good idea on a given page.
		// The caching is done based on the URL so we can reuse.
		var CACHE_ENABLED = true;

		function prepareAndShowTooltipView() {
			hideAllTooltipViews();
			var el = jQuery(this);
			var url = el.attr('href') ? el.attr('href') : '';
			if (url == '') {
				return;
			}

			// Rewrite URL to retrieve Tooltip view.
			url = url.replace('view=', 'xview=') + '&view=TooltipAjax';
			var cachedView = CACHE_ENABLED ? jQuery('[data-url-cached="' + url + '"]') : null;
			if (cachedView && cachedView.length) {
				showTooltip(el, cachedView.html());
			} else {
				AppConnector.request(url).then(function (data) {
					cachedView = jQuery('<div>').css({display: 'none'}).attr('data-url-cached', url);
					cachedView.html(data);
					jQuery('body').append(cachedView);
					showTooltip(el, data);
				});
			}
		}

		function get_popover_placement(el) {
			var width = window.innerWidth;
			var left_pos = jQuery(el).offset().left;
			if (width - left_pos < 400 || checkLastElement(el))
				return 'left';
			return 'right';
		}

		//The function checks if the selected element is the last element of the table in list view.
		function checkLastElement(el) {
			var parent = el.closest('tr');
			var lastElementTd = parent.find('td.listViewEntryValue:last a');
			if (el.attr('href') == lastElementTd.attr('href')) {
				return true;
			}
			return false;
		}

		function showTooltip(el, data) {
			var the_placement = get_popover_placement(el);
			el.popover({
				//title: '', - Is derived from the Anchor Element (el).
				trigger: 'manual',
				content: data,
				animation: false,
				html: true,
				placement: the_placement,
				template: '<div class="popover popover-tooltip"><div class="arrow"></div><div class="popover-inner"><button name="vtTooltipClose" class="close" style="color:white;opacity:1;font-weight:lighter;position:relative;top:3px;right:3px;">x</button><h3 class="popover-title"></h3><div class="popover-content"><div></div></div></div></div>'
			});
			lastPopovers.push(el.popover('show'));
			registerToolTipDestroy();
		}

		function hideAllTooltipViews() {
			// Hide all previous popover
			var lastPopover = null;
			while (lastPopover = lastPopovers.pop()) {
				lastPopover.popover('hide');
			}
		}

		references.each(function (index, el) {
			jQuery(el).hoverIntent({
				interval: 100,
				sensitivity: 7,
				timeout: 10,
				over: prepareAndShowTooltipView,
				out: hideAllTooltipViews
			});
		});
		function registerToolTipDestroy() {
			jQuery('button[name="vtTooltipClose"]').on('click', function (e) {
				var lastPopover = lastPopovers.pop();
				lastPopover.popover('hide');
				jQuery('.popover').css("display", "none", "important");
			});
		}
	},
	changeWatching: function (instance) {
		var value, module, state, className, user, record;
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
			title: app.vtranslate('JS_WATCHING_TITLE'),
			buttons: {
				success: {
					label: app.vtranslate('LBL_YES'),
					className: "btn-success",
					callback: function () {
						Vtiger_Index_Js.updateWatching(module, value, user, record).then(function (data) {
							if (instance != undefined) {
								state = data.result == 1 ? 0 : 1;
								instance.data('value', state);
								if (state == 1) {
									instance.toggleClass(instance.data('off') + ' ' + instance.data('on'));
									instance.children().toggleClass(instance.data('iconOff') + ' ' + instance.data('iconOn'));
								} else {
									instance.toggleClass(instance.data('on') + ' ' + instance.data('off'));
									instance.children().toggleClass(instance.data('iconOn') + ' ' + instance.data('iconOff'));
								}
							}
						});
					}
				},
				danger: {
					label: app.vtranslate('LBL_NO'),
					className: "btn-warning",
					callback: function () {
					}
				}
			}
		});
	},
	updateWatching: function (module, value, user, record) {
		var aDeferred = jQuery.Deferred();
		var params = {
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
		AppConnector.request(params).then(function (data) {
			aDeferred.resolve(data);
		}, function (textStatus, errorThrown) {
			aDeferred.reject(textStatus, errorThrown);
			app.errorLog(textStatus, errorThrown);
		});
		return aDeferred.promise();
	},
	sendNotification: function () {
		var modalWindowParams = {
			url: 'index.php?module=Home&view=CreateNotificationModal',
			id: 'CreateNotificationModal',
			cb: function (container) {
				var form, text, link, htmlLink;
				text = container.find('#notificationMessage');
				form = container.find('form');
				container.find('#notificationTitle').val(app.getPageTitle());
				link = $("<a/>", {
					name: "link",
					href: window.location.href,
					text: app.vtranslate('JS_NOTIFICATION_LINK')
				});
				htmlLink = $('<div>').append(link.clone()).html();
				text.val('<br/><hr/>' + htmlLink);
				var ckEditorInstance = new Vtiger_CkEditor_Js();
				ckEditorInstance.loadCkEditor(text);
				container.find(".externalMail").click(function (e) {
					if (form.validationEngine('validate')) {
						var editor = CKEDITOR.instances.notificationMessage;
						var text = $('<div>' + editor.getData() + '</div>');
						text.find("a[href]").each(function (i, el) {
							var href = $(this);
							href.text(href.attr('href'));
						});
						var emails = [];
						container.find("#notificationUsers option:selected").each(function (index) {
							emails.push($(this).data('mail'))
						});
						$(this).attr('href', 'mailto:' + emails.join() + '?subject=' + encodeURIComponent(container.find("#notificationTitle").val()) + '&body=' + encodeURIComponent(text.text()))
						app.hideModalWindow(container, 'CreateNotificationModal');
					} else {
						e.preventDefault();
					}
				});
				container.find('[type="submit"]').click(function (e) {
					var element = $(this);
					form.find('[name="mode"]').val(element.data('mode'));
				});
				form.submit(function (e) {
					if (form.validationEngine('validate')) {
						app.hideModalWindow(container, 'CreateNotificationModal');
					}
				});
			},
		}
		app.showModalWindow(modalWindowParams);
	},
	loadPreSaveRecord: function (form) {
		SaveResult = new SaveResult()
		return SaveResult.checkData(form);
	},
	registerEvents: function () {
		Vtiger_Index_Js.registerWidgetsEvents();
		Vtiger_Index_Js.loadWidgetsOnLoad();
		Vtiger_Index_Js.registerActivityReminder();
		Vtiger_Index_Js.registerCheckNotifications();
		Vtiger_Index_Js.adjustTopMenuBarItems();
		Vtiger_Index_Js.registerPostAjaxEvents();
		Vtiger_Index_Js.changeSkin();
		Vtiger_Index_Js.registerResizeEvent();
	},
	registerPostAjaxEvents: function () {
		Vtiger_Index_Js.registerTooltipEvents();
	}
}
//On Page Load
jQuery(document).ready(function () {
	Vtiger_Index_Js.registerEvents();
	app.listenPostAjaxReady(function () {
		Vtiger_Index_Js.registerPostAjaxEvents();
	});
});
