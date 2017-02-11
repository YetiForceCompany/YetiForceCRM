/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/

//Show Alert if user is on a unsupported browser (IE7, IE8, ..etc)
if (/MSIE 6.0/.test(navigator.userAgent) || /MSIE 7.0/.test(navigator.userAgent) || /MSIE 8.0/.test(navigator.userAgent) || /MSIE 9.0/.test(navigator.userAgent)) {
	if (app.getCookie('oldbrowser') != 'true') {
		app.setCookie("oldbrowser", true, 365);
		window.location.href = 'layouts/basic/modules/Vtiger/browsercompatibility/Browser_compatibility.html';
	}
}

jQuery.Class("Vtiger_Header_Js", {
	quickCreateModuleCache: {},
	self: false,
	getInstance: function () {
		if (this.self != false) {
			return this.self;
		}
		this.self = new Vtiger_Header_Js();
		return this.self;
	}
}, {
	menuContainer: false,
	contentContainer: false,
	quickCreateCallBacks: [],
	init: function () {
		this.setContentsContainer('.bodyContent');
	},
	setContentsContainer: function (element) {
		if (element instanceof jQuery) {
			this.contentContainer = element;
		} else {
			this.contentContainer = jQuery(element);
		}
		return this;
	},
	getContentsContainer: function () {
		return this.contentContainer;
	},
	getQuickCreateForm: function (url, moduleName, params) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var requestParams;
		if (typeof params == 'undefined') {
			params = {};
		}
		if ((!params.noCache) || (typeof (params.noCache) == "undefined")) {
			if (typeof Vtiger_Header_Js.quickCreateModuleCache[moduleName] != 'undefined') {
				aDeferred.resolve(Vtiger_Header_Js.quickCreateModuleCache[moduleName]);
				return aDeferred.promise();
			}
		}
		requestParams = url;
		if (typeof params.data != "undefined") {
			var requestParams = {};
			requestParams['data'] = params.data;
			requestParams['url'] = url;
		}
		AppConnector.request(requestParams).then(function (data) {
			if ((!params.noCache) || (typeof (params.noCache) == "undefined")) {
				Vtiger_Header_Js.quickCreateModuleCache[moduleName] = data;
			}
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},
	registerQuickCreateCallBack: function (callBackFunction) {
		if (typeof callBackFunction != 'function') {
			return false;
		}
		this.quickCreateCallBacks.push(callBackFunction);
		return true;
	},
	/**
	 * Function to save the quickcreate module
	 * @param accepts form element as parameter
	 * @return returns deferred promise
	 */
	quickCreateSave: function (form) {
		var aDeferred = jQuery.Deferred();
		var quickCreateSaveUrl = form.serializeFormData();
		AppConnector.request(quickCreateSaveUrl).then(
				function (data) {
					aDeferred.resolve(data);
				},
				function (textStatus, errorThrown) {
					aDeferred.reject(textStatus, errorThrown);
				}
		);
		return aDeferred.promise();
	},
	/**
	 * Function to navigate from quickcreate to editView Fullform
	 * @param accepts form element as parameter
	 */
	quickCreateGoToFullForm: function (form, editViewUrl) {
		//As formData contains information about both view and action removed action and directed to view
		form.find('input[name="action"]').remove();
		form.append('<input type="hidden" name="view" value="Edit" />');
		$.each(form.find('[data-validation-engine]'), function (key, data) {
			jQuery(data).removeAttr('data-validation-engine');
		});
		form.addClass('not_validation');
		form.submit();
	},
	showAnnouncement: function () {
		var thisInstance = this;
		var announcementContainer = jQuery('#announcements');
		var announcements = announcementContainer.find('.announcement');
		if (announcements.length > 0) {
			var announcement = announcements.first();
			var aid = announcement.data('id')

			app.showModalWindow(announcement.find('.modal'), function (modal) {
				announcement.remove();
				modal.find('button').click(function (e) {
					AppConnector.request({
						module: 'Announcements',
						action: 'BasicAjax',
						mode: 'mark',
						record: aid,
						type: $(this).data('type')
					}).then(function (res) {
						app.hideModalWindow(modal);
						thisInstance.showAnnouncement();
					})
				});
			}, '', {backdrop: 'static'});
		}
	},
	registerAnnouncements: function () {
		var thisInstance = this;
		var announcementContainer = jQuery('#announcements');
		if (announcementContainer.length == 0) {
			return false;
		}
		thisInstance.showAnnouncement();
	},
	registerCalendarButtonClickEvent: function () {
		var element = jQuery('#calendarBtn');
		var dateFormat = element.data('dateFormat');
		var currentDate = element.data('date');
		var vtigerDateFormat = app.convertToDatePickerFormat(dateFormat);
		element.on('click', function (e) {
			e.stopImmediatePropagation();
			element.closest('div.nav').find('div.open').removeClass('open');
			var calendar = jQuery('#' + element.data('datepickerId'));
			if (jQuery(calendar).is(':visible')) {
				element.DatePickerHide();
			} else {
				element.DatePickerShow();
			}
		})
		element.DatePicker({
			format: vtigerDateFormat,
			date: currentDate,
			calendars: 1,
			starts: 1,
			className: 'globalCalendar'
		});
	},
	registerHelpInfo: function (container) {
		if (typeof container == 'undefined') {
			container = jQuery('form[name="QuickCreate"]');
		}
		app.showPopoverElementView(container.find('.HelpInfoPopover'));
	},
	handleQuickCreateData: function (data, params) {
		if (typeof params == 'undefined') {
			params = {};
		}
		var thisInstance = this;
		app.showModalWindow(data, function (data) {
			var quickCreateForm = data.find('form[name="QuickCreate"]');
			var moduleName = quickCreateForm.find('[name="module"]').val();
			var editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
			editViewInstance.registerBasicEvents(quickCreateForm);
			thisInstance.registerChangeNearCalendarEvent(quickCreateForm, moduleName);
			quickCreateForm.validationEngine(app.validationEngineOptions);
			if (typeof params.callbackPostShown != "undefined") {
				params.callbackPostShown(quickCreateForm);
			}
			thisInstance.registerQuickCreatePostLoadEvents(quickCreateForm, params);
			thisInstance.registerHelpInfo(quickCreateForm);
			var quickCreateContent = quickCreateForm.find('.quickCreateContent');
			var quickCreateContentHeight = quickCreateContent.height();
			var contentHeight = parseInt(quickCreateContentHeight);
			var maxHeight = app.getScreenHeight(70);
			if (contentHeight > maxHeight) {
				app.showScrollBar(jQuery('.quickCreateContent'), {
					'height': maxHeight + 'px'
				});
			}
			var customConfig = {
				height: '5em',
				toolbar: 'Min'
			};
			jQuery.each(data.find('.ckEditorSource'), function (key, element) {
				var ckEditorInstance = new Vtiger_CkEditor_Js();
				ckEditorInstance.loadCkEditor(jQuery(element), customConfig);
			});
		});
	},
	isFreeDay: function (dayOfWeek) {

		if (dayOfWeek == 0 || dayOfWeek == 6) {
			return true;
		}
		return false;
	},
	getNearCalendarEvent: function (data, module) {
		var showCompanies = $('[name="showCompanies"]').val();
		var daysWork = app.getMainParams('hiddenDays', true);
		var thisInstance = this;
		var typeActive = data.find('ul li.active a').data('tab-name');
		var user = data.find('[name="assigned_user_id"]');
		var dateStartEl = data.find('[name="date_start"]');
		var dateStartVal = dateStartEl.val();
		var dateStartFormat = dateStartEl.data('date-format');
		if (typeof dateStartVal == 'undefined' || !data.find('.eventsTable').length) {
			return;
		}
		var days = [];
		var countDaysToEnd = 7 + 2 * daysWork.length;
		var dayStart = -1;
		while (days.length < 3) {
			var day = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, dayStart, ' ');
			if (jQuery.inArray(Vtiger_Helper_Js.getDay(day), daysWork) == -1) {
				days.push(day);
			}
			dayStart--;
		}
		days.reverse();
		dayStart = 1;
		days.push(Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, 0, ' '));
		while (days.length < 7) {
			var day = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, dayStart, ' ');
			if (jQuery.inArray(Vtiger_Helper_Js.getDay(day), daysWork) == -1) {
				days.push(day);
			}
			dayStart++;
		}
		var firstDay = days[0];
		var secondDay = days[1];
		var thirdDay = days[2];
		var currentDay = days[3];
		var fifthDay = days[4];
		var sixthDay = days[5];
		var seventhDay = days[6];
		var dateEnd = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, dayStart, ' ');
		var firstDayWeek = Vtiger_Helper_Js.getDay(firstDay);
		var secondDayWeek = Vtiger_Helper_Js.getDay(secondDay);
		var thirdDayWeek = Vtiger_Helper_Js.getDay(thirdDay);
		var currentDayWeek = Vtiger_Helper_Js.getDay(currentDay);
		var fifthDayWeek = Vtiger_Helper_Js.getDay(fifthDay);
		var sixthDayWeek = Vtiger_Helper_Js.getDay(sixthDay);
		var seventhDayWeek = Vtiger_Helper_Js.getDay(seventhDay);
		var params = {
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getEvents',
			start: app.getDateInVtigerFormat(dateStartFormat, Date.parse(firstDay)),
			end: app.getDateInVtigerFormat(dateStartFormat, Date.parse(dateEnd)),
			user: user.val(),
			time: 'current'
		}
		AppConnector.request(params).then(function (events) {
			if (typeof events.result != 'undefined' && events.result.length > 0) {
				events = events.result;
				data.find('.modal-body').css({'max-height': '500px', 'overflow-y': 'auto'});
				for (var ev in events) {
					var icon = 'glyphicon glyphicon-calendar';
					var linkHtml = '';
					var hidden = '';
					var helpIcon = '';
					if (events[ev]['set'] == 'Task') {
						icon = 'glyphicon glyphicon-tasks';
					}
					if (showCompanies) {
						if (events[ev]['linkl']) {
							linkHtml = '<div class="cut-string"><i class="calIcon modIcon_' + events[ev]['linkm'] + '"></i> ' + events[ev]['linkl'] + '</div>';
						}
					}
					helpIcon = '<div><label> ' + app.vtranslate('JS_START_DATE') + ': &nbsp</label>' + events[ev]['start_display'] + ' </div>\n\
								<div><label> ' + app.vtranslate('JS_END_DATE') + ': &nbsp</label>' + events[ev]['end_display'] + ' </div>\n\
								<div class=' + 'textOverflowEllipsis' + '><label> ' + app.vtranslate('JS_SUBJECT') + ': &nbsp</label>' + events[ev]['title'] + '</div>\n\
								<div><label> ' + app.vtranslate('JS_STATE') + ': &nbsp</label>' + events[ev]['labels']['state'] + ' </div>\n\
								<div><label> ' + app.vtranslate('JS_STATUS') + ': &nbsp</label>' + events[ev]['labels']['sta'] + ' </div>\n\
								<div><label> ' + app.vtranslate('JS_PRIORITY') + ': &nbsp</label>' + events[ev]['labels']['pri'] + ' </div>\n';
					helpIcon += events[ev]['link'] != 0 && events[ev]['link'] != null ? '<div><label>' + app.vtranslate('JS_RELATION') + '</label>:' + events[ev]['linkl'] : '';
					helpIcon += events[ev]['process'] != 0 && events[ev]['process'] != null ? '<div><label>' + app.vtranslate('JS_PROCESS') + '</label>:' + events[ev]['procl'] : '';
					helpIcon += events[ev]['subprocess'] != 0 && events[ev]['subprocess'] != null ? '<div><label>' + app.vtranslate('JS_SUB_PROCESS') + '</label>:' + events[ev]['subprocl'] : '';

					if (events[ev]['start'].indexOf(firstDay) > -1) {
						data.find('#threeDaysAgo .table').append('<tr class="mode_' + events[ev]['set'] + ' ' + hidden + ' addedNearCalendarEvent" ><td colspan="2"><a target="_blank" href="' + events[ev]['url'] + '"><div class="cut-string"><i class="' + icon + '" style="vertical-align:middle; margin-bottom:4px;"></i><span><strong> ' + events[ev]['hour_start'] + '</strong></span><span> ' + events[ev]['title'].substring(0, 16) + ' </span><span style="margin-left: 5px;margin-top: 2px;"  class="HelpInfoPopover " title="" data-placement="top" data-content="' + helpIcon + '"><i class="pull-right glyphicon glyphicon-info-sign"></i></span</div></a>' + linkHtml + '</td></tr>');
					} else if (events[ev]['start'].indexOf(secondDay) > -1) {
						data.find('#twoDaysAgo .table').append('<tr class="mode_' + events[ev]['set'] + ' ' + hidden + ' addedNearCalendarEvent" ><td><a target="_blank" href="' + events[ev]['url'] + '"><div class="cut-string"><i class="' + icon + '" style="vertical-align:middle; margin-bottom:4px;"></i><span><strong> ' + events[ev]['hour_start'] + '</strong></span><span> ' + events[ev]['title'].substring(0, 16) + ' </span><span style="margin-left: 5px;margin-top: 2px;"  class="HelpInfoPopover " title="" data-placement="top" data-content="' + helpIcon + '"><i class="pull-right glyphicon glyphicon-info-sign"></i></span></div></a>' + linkHtml + '</td></tr>');
					} else if (events[ev]['start'].indexOf(thirdDay) > -1) {

						data.find('#oneDaysAgo .table').append('<tr class="mode_' + events[ev]['set'] + ' ' + hidden + ' addedNearCalendarEvent" ><td><a target="_blank" href="' + events[ev]['url'] + '"><div class="cut-string"><i class="' + icon + '" style="vertical-align:middle; margin-bottom:4px;"></i><span><strong> ' + events[ev]['hour_start'] + '</strong></span><span> ' + events[ev]['title'].substring(0, 16) + ' </span><span style="margin-left: 5px;margin-top: 2px;"  class="HelpInfoPopover " title="" data-placement="top" data-content="' + helpIcon + '"><i class="pull-right glyphicon glyphicon-info-sign"></i></span></div></a>' + linkHtml + '</td></tr>');
					} else if (events[ev]['start'].indexOf(currentDay) > -1) {
						data.find('#cur_events .table').append('<tr class="mode_' + events[ev]['set'] + ' ' + hidden + ' addedNearCalendarEvent"><td><a target="_blank" href="' + events[ev]['url'] + '"><div class="cut-string"><i class="' + icon + '" style="vertical-align:middle; margin-bottom:4px;"></i><span><strong> ' + events[ev]['hour_start'] + '</strong></span><span> ' + events[ev]['title'].substring(0, 16) + ' </span><span style="margin-left: 5px;margin-top: 2px;"  class="HelpInfoPopover " title="" data-placement="top" data-content="' + helpIcon + '"><i class="pull-right glyphicon glyphicon-info-sign"></i></span</div></a>' + linkHtml + '</td></tr>');
					} else if (events[ev]['start'].indexOf(fifthDay) > -1) {
						data.find('#oneDaysLater .table').append('<tr class="mode_' + events[ev]['set'] + ' ' + hidden + ' addedNearCalendarEvent"><td><a target="_blank" href="' + events[ev]['url'] + '"><div class="cut-string"><i class="' + icon + '" style="vertical-align:middle; margin-bottom:4px;"></i><span><strong> ' + events[ev]['hour_start'] + '</strong></span><span> ' + events[ev]['title'].substring(0, 16) + ' </span><span style="margin-left: 5px;margin-top: 2px;"  class="HelpInfoPopover " title="" data-placement="top" data-content="' + helpIcon + '"><i class="pull-right glyphicon glyphicon-info-sign"></i></span</div></a>' + linkHtml + '</td></tr>');
					} else if (events[ev]['start'].indexOf(sixthDay) > -1) {
						data.find('#twoDaysLater .table').append('<tr class="mode_' + events[ev]['set'] + ' ' + hidden + ' addedNearCalendarEvent"><td><a target="_blank" href="' + events[ev]['url'] + '"><div class="cut-string"><i class="' + icon + '" style="vertical-align:middle; margin-bottom:4px;"></i><span><strong> ' + events[ev]['hour_start'] + '</strong></span><span> ' + events[ev]['title'].substring(0, 16) + ' </span><span style="margin-left: 5px;margin-top: 2px;"  class="HelpInfoPopover " title="" data-placement="top" data-content="' + helpIcon + '"><i class="pull-right glyphicon glyphicon-info-sign"></i></span</div></a>' + linkHtml + '</td></tr>');
					} else if (events[ev]['start'].indexOf(seventhDay) > -1) {
						data.find('#threeDaysLater .table').append('<tr class="mode_' + events[ev]['set'] + ' ' + hidden + ' addedNearCalendarEvent"><td colspan="2"><a target="_blank" href="' + events[ev]['url'] + '"><div class="cut-string"><i class="' + icon + '" style="vertical-align:middle; margin-bottom:4px;"></i><span><strong> ' + events[ev]['hour_start'] + '</strong></span><span> ' + events[ev]['title'].substring(0, 16) + ' </span><span style="margin-left: 5px;margin-top: 2px;"  class="HelpInfoPopover " title="" data-placement="top" data-content="' + helpIcon + '"><i class="pull-right glyphicon glyphicon-info-sign"></i></span</div></a>' + linkHtml + '</td></tr>');
					}
				}
				var quickCreateForm;
				thisInstance.registerHelpInfo(quickCreateForm);
			} else {
				data.find('.modal-body').css({'max-height': '', 'overflow-y': ''});
			}
		});
		var day1 = app.getDateInVtigerFormat(dateStartFormat, Date.parse(firstDay));
		var day2 = app.getDateInVtigerFormat(dateStartFormat, Date.parse(secondDay));
		var day3 = app.getDateInVtigerFormat(dateStartFormat, Date.parse(thirdDay));
		var day4 = app.getDateInVtigerFormat(dateStartFormat, Date.parse(currentDay));
		var day5 = app.getDateInVtigerFormat(dateStartFormat, Date.parse(fifthDay));
		var day6 = app.getDateInVtigerFormat(dateStartFormat, Date.parse(sixthDay));
		var day7 = app.getDateInVtigerFormat(dateStartFormat, Date.parse(seventhDay));

		data.find('.taskPrevThreeDaysAgo').html('<span class="cursorPointer dateBtn">' + day1 + '</span> (' + Vtiger_Helper_Js.getLabelDayFromDate(firstDayWeek) + ')');
		data.find('.taskPrevTwoDaysAgo').html('<span class="cursorPointer dateBtn">' + day2 + '</span> (' + Vtiger_Helper_Js.getLabelDayFromDate(secondDayWeek) + ')');
		data.find('.taskPrevOneDayAgo').html('<span class="cursorPointer dateBtn">' + day3 + '</span> (' + Vtiger_Helper_Js.getLabelDayFromDate(thirdDayWeek) + ')');
		data.find('.taskCur').html('<span class="cursorPointer dateBtn">' + day4 + '</span> (' + Vtiger_Helper_Js.getLabelDayFromDate(currentDayWeek) + ')');
		data.find('.taskNextOneDayLater').html('<span class="cursorPointer dateBtn">' + day5 + '</span> (' + Vtiger_Helper_Js.getLabelDayFromDate(fifthDayWeek) + ')');
		data.find('.taskNextTwoDaysLater').html('<span class="cursorPointer dateBtn">' + day6 + '</span> (' + Vtiger_Helper_Js.getLabelDayFromDate(sixthDayWeek) + ')');
		data.find('.taskNextThreeDaysLater').html('<span class="cursorPointer dateBtn">' + day7 + '</span> (' + Vtiger_Helper_Js.getLabelDayFromDate(seventhDayWeek) + ')');

		data.find('.dateBtn').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			dateStartEl.val(element.html());
			data.find('[name="due_date"]').val(element.html());
			data.find('[name="date_start"]').trigger('change');
		});
	},
	registerChangeNearCalendarEvent: function (data, module) {
		var thisInstance = this;
		if (!data || module != 'Calendar' || typeof module == 'undefined') {
			return;
		}
		var user = data.find('[name="assigned_user_id"]');
		var dateStartEl = data.find('[name="date_start"]');
		var dateEnd = data.find('[name="due_date"]');
		user.on('change', function (e) {
			var element = jQuery(e.currentTarget);
			var data = element.closest('form');
			data.find('.addedNearCalendarEvent').remove();
			thisInstance.getNearCalendarEvent(data, module);
		});
		dateStartEl.on('change', function (e) {
			var element = jQuery(e.currentTarget);
			var data = element.closest('form');
			data.find('.addedNearCalendarEvent').remove();
			thisInstance.getNearCalendarEvent(data, module);
		});
		data.find('ul li a').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			var data = element.closest('form');
			data.find('.addedNearCalendarEvent').remove();
			thisInstance.getNearCalendarEvent(data, module);
		});
		data.find('.nextDayBtn').on('click', function () {
			var dateStartEl = data.find('[name="date_start"]')
			var startDay = dateStartEl.val();
			var dateStartFormat = dateStartEl.data('date-format');
			startDay = app.getDateInVtigerFormat(dateStartFormat, Date.parse(Vtiger_Helper_Js.convertToDateString(startDay, dateStartFormat, '+7', ' ')));
			dateStartEl.val(startDay);
			dateEnd.val(startDay);
			data.find('.addedNearCalendarEvent').remove();
			thisInstance.getNearCalendarEvent(data, module);
		});
		data.find('.previousDayBtn').on('click', function () {
			var dateStartEl = data.find('[name="date_start"]')
			var startDay = dateStartEl.val();
			var dateStartFormat = dateStartEl.data('date-format');
			startDay = app.getDateInVtigerFormat(dateStartFormat, Date.parse(Vtiger_Helper_Js.convertToDateString(startDay, dateStartFormat, '-7', ' ')));
			dateStartEl.val(startDay);
			dateEnd.val(startDay);
			data.find('.addedNearCalendarEvent').remove();
			thisInstance.getNearCalendarEvent(data, module);
		});

		thisInstance.getNearCalendarEvent(data, module);
		thisInstance.registerHelpInfo();
	},
	registerQuickCreatePostLoadEvents: function (form, params) {
		var thisInstance = this;
		var submitSuccessCallbackFunction = params.callbackFunction;
		var goToFullFormCallBack = params.goToFullFormcallback;
		if (typeof submitSuccessCallbackFunction == 'undefined') {
			submitSuccessCallbackFunction = function () {
			};
		}

		form.on('submit', function (e) {
			var form = jQuery(e.currentTarget);
			if (form.hasClass('not_validation')) {
				return true;
			}
			var module = form.find('[name="module"]').val();
			//Form should submit only once for multiple clicks also
			if (typeof form.data('submit') != "undefined") {
				return false;
			} else {
				var invalidFields = form.data('jqv').InvalidFields;
				if (invalidFields.length > 0) {
					//If validation fails, form should submit again
					form.removeData('submit');
					form.closest('#globalmodal').find('.modal-header h3').progressIndicator({
						'mode': 'hide'
					});
					e.preventDefault();
					return;
				} else {
					//Once the form is submiting add data attribute to that form element
					form.data('submit', 'true');
					form.closest('#globalmodal').find('.modal-header h3').progressIndicator({
						smallLoadingImage: true,
						imageContainerCss: {
							display: 'inline',
							'margin-left': '18%',
							position: 'absolute'
						}
					});
				}

				var recordPreSaveEvent = jQuery.Event(Vtiger_Edit_Js.recordPreSave);
				form.trigger(recordPreSaveEvent, {
					'value': 'edit',
					'module': module
				});
				if (!(recordPreSaveEvent.isDefaultPrevented())) {
					var targetInstance = thisInstance;
					var moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName(module);
					if (typeof (moduleInstance.quickCreateSave) === 'function') {
						targetInstance = moduleInstance;
					}

					targetInstance.quickCreateSave(form).then(
							function (data) {
								app.hideModalWindow();
								var parentModule = app.getModuleName();
								var viewname = app.getViewName();
								if ((module == parentModule) && (viewname == "List")) {
									var listinstance = new Vtiger_List_Js();
									listinstance.getListViewRecords();
								}
								submitSuccessCallbackFunction(data);
								var registeredCallBackList = thisInstance.quickCreateCallBacks;
								for (var index = 0; index < registeredCallBackList.length; index++) {
									var callBack = registeredCallBackList[index];
									callBack({
										'data': data,
										'name': form.find('[name="module"]').val()
									});
								}
								jQuery('body').trigger(jQuery.Event('QuickCreateSave.PostLoad'), data);
							},
							function (error, err) {
							}
					);
				} else {
					//If validation fails in recordPreSaveEvent, form should submit again
					form.removeData('submit');
					form.closest('#globalmodal').find('.modal-header h3').progressIndicator({
						'mode': 'hide'
					});
				}
				e.preventDefault();
			}
		});

		form.find('#goToFullForm').on('click', function (e) {
			var form = jQuery(e.currentTarget).closest('form');
			var editViewUrl = jQuery(e.currentTarget).data('editViewUrl');
			if (typeof goToFullFormCallBack != "undefined") {
				goToFullFormCallBack(form);
			}
			thisInstance.quickCreateGoToFullForm(form, editViewUrl);
		});

		this.registerTabEventsInQuickCreate(form);
	},
	registerTabEventsInQuickCreate: function (form) {
		var tabElements = form.find('.nav.nav-pills , .nav.nav-tabs').find('a');
		//This will remove the name attributes and assign it to data-element-name . We are doing this to avoid
		//Multiple element to send as in calendar
		var quickCreateTabOnHide = function (target) {
			var container = jQuery(target);
			container.find('[name]').each(function (index, element) {
				element = jQuery(element);
				element.attr('data-element-name', element.attr('name')).removeAttr('name');
			});
		}
		//This will add the name attributes and get value from data-element-name . We are doing this to avoid
		//Multiple element to send as in calendar
		var quickCreateTabOnShow = function (target) {
			var container = jQuery(target);
			container.find('[data-element-name]').each(function (index, element) {
				element = jQuery(element);
				element.attr('name', element.attr('data-element-name')).removeAttr('data-element-name');
			});
		}
		tabElements.on('click', function (e) {
			quickCreateTabOnHide(tabElements.not('[aria-expanded="false"]').attr('data-target'));
			quickCreateTabOnShow($(this).attr('data-target'));
			//while switching tabs we have to clear the invalid fields list
			form.data('jqv').InvalidFields = [];

		});
		//To show aleady non active element , this we are doing so that on load we can remove name attributes for other fields
		var liElements = tabElements.closest('li');
		liElements.filter(':not(.active)').find('a').each(function (e) {
			quickCreateTabOnHide(jQuery(this).attr('data-target'));
		});
	},
	basicSearch: function () {
		var thisInstance = this;
		jQuery('.globalSearchValue').keypress(function (e) {
			var currentTarget = jQuery(e.currentTarget)
			if (e.which == 13) {
				thisInstance.hideSearchMenu();
				thisInstance.labelSearch(currentTarget);
			}
		});
		jQuery('.globalSearchOperator').on('click', function (e) {
			var currentTarget = jQuery(e.target);
			var block = currentTarget.closest('.globalSearchInput');
			block.find('.globalSearchValue').data('operator', currentTarget.data('operator'));
			block.find('.globalSearchOperator li').removeClass('active');
			currentTarget.closest('li').addClass('active');
		});
		if (jQuery('#gsAutocomplete').val() == 1) {
			$.widget("custom.gsAutocomplete", $.ui.autocomplete, {
				_create: function () {
					this._super();
					this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
				},
				_renderMenu: function (ul, items) {
					var that = this, currentCategory = "";
					$.each(items, function (index, item) {
						var li;
						if (item.category != currentCategory) {
							ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
							currentCategory = item.category;
						}
						that._renderItemData(ul, item);
					});
				},
				_renderItemData: function (ul, item) {
					return this._renderItem(ul, item).data("ui-autocomplete-item", item);
				},
				_renderItem: function (ul, item) {
					return $("<li>")
							.data("item.autocomplete", item)
							.append($("<a></a>").html(item.label))
							.appendTo(ul);
				},
			});
			jQuery('.globalSearchValue').gsAutocomplete({
				minLength: app.getMainParams('gsMinLength'),
				source: function (request, response) {
					var basicSearch = new Vtiger_BasicSearch_Js();
					basicSearch.reduceNumberResults = app.getMainParams('gsAmountResponse');
					basicSearch.returnHtml = false;
					basicSearch.setMainContainer(this.element.closest('.globalSearchInput'));
					basicSearch.search(request.term).then(function (data) {
						var data = jQuery.parseJSON(data);
						var serverDataFormat = data.result;
						var reponseDataList = new Array();
						for (var id in serverDataFormat) {
							var responseData = serverDataFormat[id];
							reponseDataList.push(responseData);
						}
						response(reponseDataList);
					});
				},
				select: function (event, ui) {
					var selectedItemData = ui.item;
					if (selectedItemData.permitted) {
						var url = 'index.php?module=' + selectedItemData.module + '&view=Detail&record=' + selectedItemData.id;
						window.location.href = url;
					}
					return false;
				},
				close: function (event, ui) {
					//jQuery('.globalSearchValue').val('');
				}
			});
		}
	},
	labelSearch: function (currentTarget) {
		var val = currentTarget.val();
		if (val == '') {
			alert(app.vtranslate('JS_PLEASE_ENTER_SOME_VALUE'));
			currentTarget.focus();
			return false;
		}
		var progress = jQuery.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var basicSearch = new Vtiger_BasicSearch_Js();
		basicSearch.setMainContainer(currentTarget.closest('.globalSearchInput'));
		basicSearch.search(val).then(function (data) {
			basicSearch.showSearchResults(data);
			progress.progressIndicator({
				'mode': 'hide'
			});
		});
	},
	recentPageViews: function () {
		var thisInstance = this;
		var maxValues = 20;
		var BtnText = '';
		var BtnLink = 'javascript:void();';
		var userId = app.getMainParams('current_user_id');
		if (userId == undefined) {
			return false;
		}
		var key = 'yf_history_' + userId;
		var history = localStorage.getItem(key);
		if (history != "" && history != null) {
			var sp = history.toString().split("_|_");
			var item = sp[sp.length - 1].toString().split("|");
			BtnText = item[0];
			BtnLink = item[1];
		}
		var htmlContent = '<ul class="dropdown-menu pull-right historyList" role="menu">';
		var date = new Date().getTime();
		var howManyDays = -1;
		var writeSelector = true;
		if (sp != null) {
			for (var i = sp.length - 1; i >= 0; i--) {
				item = sp[i].toString().split("|");
				var d = new Date();
				var t = '';
				if (item[2] != undefined) {
					d.setTime(item[2]);
					var hours = app.formatDateZ(d.getHours());
					var minutes = app.formatDateZ(d.getMinutes());
					if (writeSelector && (howManyDays != app.howManyDaysFromDate(d))) {
						howManyDays = app.howManyDaysFromDate(d);
						if (howManyDays == 0) {
							htmlContent += '<li class="selectorHistory">' + app.vtranslate('JS_TODAY') + '</li>';
						} else if (howManyDays == 1) {
							htmlContent += '<li class="selectorHistory">' + app.vtranslate('JS_YESTERDAY') + '</li>';
						} else {
							htmlContent += '<li class="selectorHistory">' + app.vtranslate('JS_OLDER') + '</li>';
							writeSelector = false;
						}
					}
					if (writeSelector)
						t = '<span class="historyHour">' + hours + ":" + minutes + "</span> | ";
					else
						t = app.formatDate(d) + ' | ';
				}
				var format = $('#userDateFormat').val() + '' + $('#userDateFormat').val();
				htmlContent += '<li><a href="' + item[1] + '">' + t + item[0] + '</a></li>';
			}
			var Label = this.getHistoryLabel();
			if (Label.length > 1 && document.URL != BtnLink) {
				sp.push(this.getHistoryLabel() + '|' + document.URL + '|' + date);
			}
			if (sp.length >= maxValues) {
				sp.splice(0, 1);
			}
			localStorage.setItem(key, sp.join('_|_'));
		} else {
			var stack = new Array();
			var Label = this.getHistoryLabel();
			if (Label.length > 1) {
				stack.push(this.getHistoryLabel() + '|' + document.URL + '|' + date);
				localStorage.setItem(key, stack.join('_|_'));
			}
		}
		htmlContent += '<li class="divider"></li><li><a class="clearHistory" href="#">' + app.vtranslate('JS_CLEAR_HISTORY') + '</a></li>';
		htmlContent += '</ul>';
		$(".showHistoryBtn").after(htmlContent);
		this.registerClearHistory();
	},
	getHistoryLabel: function () {
		var label = "";
		$(".breadcrumbsLinks span:not(.hideToHistory)").each(function (index) {
			label += $(this).text();
		});
		return label;
	},
	registerClearHistory: function () {
		$(".historyList .clearHistory").click(function () {
			var key = 'yf_history_' + app.getMainParams('current_user_id');
			localStorage.removeItem(key);
			var htmlContent = '<li class="divider"></li><li><a class="clearHistory" href="#">' + app.vtranslate('JS_CLEAR_HISTORY') + '</a></li>';
			$(".historyList.dropdown-menu").html(htmlContent);
		});
	},
	registerHotKeys: function () {
		$(".hotKey").each(function (index) {
			var thisObject = this;
			var key = $(thisObject).data('hotkeys');
			if (key != '') {
				Mousetrap.bind(key, function () {
					thisObject.click();
				});
			}
		});
	},
	quickCreateModule: function (moduleName, params) {
		var thisInstance = this;
		if (typeof params == 'undefined') {
			params = {};
		}
		if (typeof params.callbackFunction == 'undefined') {
			params.callbackFunction = function () {
			};
		}
		var url = 'index.php?module=' + moduleName + '&view=QuickCreateAjax';
		if ((app.getViewName() === 'Detail' || app.getViewName() === 'Edit') && app.getParentModuleName() != 'Settings') {
			url += '&sourceModule=' + app.getModuleName();
			url += '&sourceRecord=' + app.getRecordId();
		}
		var progress = jQuery.progressIndicator();
		thisInstance.getQuickCreateForm(url, moduleName, params).then(function (data) {
			thisInstance.handleQuickCreateData(data, params);
			app.registerEventForClockPicker();
			progress.progressIndicator({
				'mode': 'hide'
			});
		});
	},
	registerReminderNotice: function () {
		var thisInstance = this;
		$('#page').before('<div class="remindersNoticeContainer"></div>');
		var block = $('.remindersNoticeContainer');
		var remindersNotice = $('.remindersNotice');
		remindersNotice.click(function () {
			if (!remindersNotice.hasClass('autoRefreshing')) {
				Vtiger_Index_Js.requestReminder();
			}
			thisInstance.hideActionMenu();
			block.toggleClass("toggled");
		});
		block.css('top', $('.commonActionsContainer').height());
		block.height($(window).height() - $('footer.navbar-default').height() - $('.commonActionsContainer').height() + 2);
	},
	registerMobileEvents: function () {
		var thisInstance = this;
		$('.rightHeaderBtnMenu').click(function () {
			thisInstance.hideActionMenu();
			thisInstance.hideSearchMenu();
			$('.mobileLeftPanel ').toggleClass('mobileMenuOn');
		});
		$('.actionMenuBtn').click(function () {
			thisInstance.hideSearchMenu();
			thisInstance.hideMobileMenu();
			$('.actionMenu').toggleClass('actionMenuOn');
			$('.quickCreateModules').click(function () {
				thisInstance.hideActionMenu();
			});
		});
		$('.searchMenuBtn').click(function () {
			thisInstance.hideActionMenu();
			thisInstance.hideMobileMenu();
			$('.searchMenu').toggleClass('toogleSearchMenu');
		});
	},
	hideMobileMenu: function () {
		$('.mobileLeftPanel ').removeClass('mobileMenuOn');
	},
	hideSearchMenu: function () {
		$('.searchMenu').removeClass('toogleSearchMenu');
	},
	hideActionMenu: function () {
		$('.actionMenu').removeClass('actionMenuOn');
	},
	showPdfModal: function (url) {
		var params = {};
		if (app.getViewName() == 'List') {
			var selected = Vtiger_List_Js.getSelectedRecordsParams(false, true);
			jQuery.extend(params, selected);
		}
		url += '&' + jQuery.param(params);
		app.showModalWindow(null, url);
	},
	registerFooTable: function () {
		var container = $('.tableRWD');
		container.find('thead tr th:gt(1)').attr('data-hide', 'phone');
		container.find('thead tr th:gt(3)').attr('data-hide', 'tablet,phone');
		container.find('thead tr th:last').attr('data-hide', '');
		var whichColumnEnable = container.find('thead').attr('col-visible-alltime');
		container.find('thead tr th:eq(' + whichColumnEnable + ')').attr('data-hide', '');
		$('.tableRWD, .customTableRWD').footable({
			breakpoints: {
				phone: 768,
				tablet: 1024
			},
			addRowToggle: true,
			toggleSelector: ' > tbody > tr:not(.footable-row-detail)'
		});
		$('.footable-toggle').click(function (event) {
			event.stopPropagation();
			$(this).trigger('footable_toggle_row');
		});
		var records = $('.customTableRWD').find('[data-toggle-visible=false]');
		records.find('.footable-toggle').css("display", "none");
	},
	registerShowHideRightPanelEvent: function (container) {
		var thisInstance = this;
		var key = 'ShowHideRightPanel' + app.getModuleName();
		if (app.cacheGet(key) == 'show') {
			thisInstance.showSiteBar(container, container.find('.toggleSiteBarRightButton'));
		}

		if (app.cacheGet(key) == null) {
			if (container.find('.siteBarRight').data('showpanel') == 1) {
				thisInstance.showSiteBar(container, container.find('.toggleSiteBarRightButton'));
			}
		}
		container.find('.toggleSiteBarRightButton').click(function (e) {
			var toogleButton = $(this);
			if (toogleButton.closest('.siteBarRight').hasClass('hideSiteBar')) {
				app.cacheSet(key, 'show');
				thisInstance.showSiteBar(container, toogleButton);
			} else {
				app.cacheSet(key, 'hide');
				thisInstance.hideSiteBar(container, toogleButton);
			}
		});
	},
	hideSiteBar: function (container, toogleButton) {
		var key, toogleButton, siteBarRight, content, buttonImage;
		siteBarRight = toogleButton.closest('.siteBarRight');
		content = container.find('.rowContent');
		buttonImage = toogleButton.find('.glyphicon');

		siteBarRight.addClass('hideSiteBar');
		content.removeClass('col-md-9').addClass('col-md-12');
		buttonImage.removeClass('glyphicon-chevron-right').addClass("glyphicon-chevron-left");
		toogleButton.addClass('hideToggleSiteBarRightButton');
	},
	showSiteBar: function (container, toogleButton) {
		var key, toogleButton, siteBarRight, content, buttonImage;
		siteBarRight = toogleButton.closest('.siteBarRight');
		content = container.find('.rowContent');
		buttonImage = toogleButton.find('.glyphicon');

		siteBarRight.removeClass('hideSiteBar');
		content.removeClass('col-md-12').addClass('col-md-9');
		buttonImage.removeClass('glyphicon-chevron-left').addClass("glyphicon-chevron-right");
		toogleButton.removeClass('hideToggleSiteBarRightButton');
	},
	registerScrollForMenu: function () {
		$(".slimScrollMenu").perfectScrollbar({
			useBothWheelAxes: true,
		});
		app.showScrollBar($(".slimScrollMenu"), {
			height: '100%',
			width: '100%',
			position: 'left',
			railVisible: true,
			railOpacity: 0.5,
		});
		app.showScrollBar($(".slimScrollSubMenu"), {
			height: '100%',
		});
		$(".slimScrollSubMenu .slimScrollDiv").each(function () {
			$(this).closest(' .slimScrollSubMenu').css('overflow', 'initial');
		});
	},
	registerToggleButton: function () {
		$(".buttonTextHolder .dropdown-menu li a").click(function () {
			$(this).parents('.btn-group').find('.dropdown-toggle .textHolder').html($(this).text());
		});
	},
	listenTextAreaChange: function () {
		var thisInstance = this;
		$('textarea').live('keyup', function () {
			var elem = $(this);
			if (!elem.data('has-scroll'))
			{
				elem.data('has-scroll', true);
				elem.bind('scroll keyup', function () {
					thisInstance.resizeTextArea($(this));
				});
			}
			thisInstance.resizeTextArea($(this));
		});
	},
	resizeTextArea: function (elem) {
		elem.height(1);
		elem.scrollTop(0);
		elem.height(elem[0].scrollHeight - elem[0].clientHeight + elem.height());
	},
	registerEvents: function () {
		var thisInstance = this;
		thisInstance.listenTextAreaChange();
		thisInstance.recentPageViews();
		thisInstance.registerFooTable(); //Enable footable
		thisInstance.registerScrollForMenu();
		thisInstance.registerShowHideRightPanelEvent($('#centerPanel'));
		jQuery('.globalSearch').click(function () {
			var currentTarget = $(this);
			thisInstance.hideSearchMenu();
			var advanceSearchInstance = new Vtiger_AdvanceSearch_Js();
			advanceSearchInstance.setParentContainer(currentTarget.closest('.globalSearchInput'));
			advanceSearchInstance.initiateSearch().then(function () {
				advanceSearchInstance.selectBasicSearchValue();
			});
		});
		jQuery('.searchIcon').on('click', function (e) {
			var currentTarget = $(this).closest('.globalSearchInput').find('.globalSearchValue');
			var pressEvent = jQuery.Event("keypress");
			pressEvent.which = 13;
			currentTarget.trigger(pressEvent);
		});
		thisInstance.registerAnnouncements();
		thisInstance.registerHotKeys();
		thisInstance.registerToggleButton();
		//this.registerCalendarButtonClickEvent();
		//After selecting the global search module, focus the input element to type
		jQuery('.basicSearchModulesList').change(function () {
			var value = $(this).closest('.globalSearchInput').find('.globalSearchValue')
			setTimeout(function () {
				value.focus();
			}, 100);
		});

		thisInstance.basicSearch();
		$('.bodyHeader .dropdownMenu').on("click", function (e) {
			$(this).next('ul').toggle();
		});
		jQuery('.quickCreateModules').on("click", ".quickCreateModule", function (e, params) {
			var moduleName = jQuery(e.currentTarget).data('name');
			thisInstance.quickCreateModule(moduleName);
		});

		thisInstance.registerMobileEvents();

		if (/Android|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
			jQuery('#basicSearchModulesList_chosen').find('.chzn-results').css({'max-height': '350px', 'overflow-y': 'scroll'});
		} else {
			app.showScrollBar(jQuery('#basicSearchModulesList_chosen').find('.chzn-results'), {
				height: '450px',
				railVisible: true,
				alwaysVisible: true,
				size: '6px'
			});
			//Added to support standard resolution 1024x768
			if (window.outerWidth <= 1024) {
				//$('.headerLinksContainer').css('margin-right', '8px');
			}
			thisInstance.registerReminderNotice();
		}
	},
});
jQuery(document).ready(function () {
	Vtiger_Header_Js.getInstance().registerEvents();
});
