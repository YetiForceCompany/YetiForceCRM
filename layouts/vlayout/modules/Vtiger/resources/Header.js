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
		window.location.href = 'layouts/vlayout/modules/Vtiger/browsercompatibility/Browser_compatibility.html';
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
	 * Function which will align the contents container at specified height depending on the top fixed menu
	 * It will caliculate the height by following formaula menuContianer.height+1	 *
	 */
	alignContentsContainer: function (show, speed, effect) {
		var navTop = jQuery('nav.navbar-fixed-top').outerHeight();
		if (show) {
			var announcement = jQuery('#announcement').outerHeight();
			navTop = (navTop + announcement);
		}	
		var contentsContainer = this.getContentsContainer();
		contentsContainer.animate({'margin-top': navTop}, speed, effect);
		return this;
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
					//TODO: App Message should be shown
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
	setAnnouncement: function () {
		var announcementoff = app.cacheGet('announcement.turnoff', false);
		var announcementBtn = jQuery('#announcementBtn');
		var thisInstance = this;
		if (announcementoff === true) {
			jQuery('#announcement').hide();
			announcementBtn.attr('src', app.vimage_path('btnAnnounceOff.png'));
			thisInstance.alignContentsContainer(false, 0, 'linear');
		}
		else {
			jQuery('#announcement').show();
			announcementBtn.attr('src', app.vimage_path('btnAnnounce.png'));
			thisInstance.alignContentsContainer(true, 0, 'linear');
		}
	},
	registerAnnouncement: function () {
		var thisInstance = this;
		var announcementBtn = jQuery('#announcementBtn');
		var announcementTurnOffKey = 'announcement.turnoff';

		announcementBtn.click(function (e, manual) {
			thisInstance.hideActionMenu();
			var displayStatus = jQuery('#announcement').css('display');

			if (displayStatus == 'none') {
				jQuery('#announcement').show();
				thisInstance.alignContentsContainer(true, 200, 'linear');
				announcementBtn.attr('src', app.vimage_path('btnAnnounce.png'));

				// Turn-on always
				if (!manual) {
					app.cacheSet(announcementTurnOffKey, false);
				}
			} else {
				thisInstance.alignContentsContainer(false, 200, 'linear');
				jQuery('#announcement').hide();
				announcementBtn.attr('src', app.vimage_path('btnAnnounceOff.png'));

				// Turn-off always
				// NOTE: Add preference on server - to reenable on announcement content change.
				if (!manual) {
					app.cacheSet(announcementTurnOffKey, true);
				}

			}
		});

		if (app.cacheGet(announcementTurnOffKey, false)) {
			announcementBtn.trigger('click', true);
		}
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
			thisInstance.registerHelpInfo(quickCreateForm);
			var moduleName = quickCreateForm.find('[name="module"]').val();
			var editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
			editViewInstance.registerBasicEvents(quickCreateForm);
			thisInstance.registerChangeNearCalendarEvent(quickCreateForm, moduleName);
			quickCreateForm.validationEngine(app.validationEngineOptions);
			if (typeof params.callbackPostShown != "undefined") {
				params.callbackPostShown(quickCreateForm);
			}
			thisInstance.registerQuickCreatePostLoadEvents(quickCreateForm, params);
			thisInstance.toggleTimesInputs(quickCreateForm);
			app.registerEventForDatePickerFields(quickCreateForm);
			var quickCreateContent = quickCreateForm.find('.quickCreateContent');
			var quickCreateContentHeight = quickCreateContent.height();
			var contentHeight = parseInt(quickCreateContentHeight);
			if (contentHeight > 300) {
				app.showScrollBar(jQuery('.quickCreateContent'), {
					'height': '300px'
				});
			}

			var customConfig = {};
			customConfig.toolbar = 'Basic';
			customConfig.height = '5em';
			jQuery.each(data.find('.ckEditorSource'), function (key, element) {
				var ckEditorInstance = new Vtiger_CkEditor_Js();
				ckEditorInstance.loadCkEditor(jQuery(element), customConfig);
			});
		});
	},
	getNearCalendarEvent: function (data, module) {
		var thisInstance = this;
		typeActive = data.find('ul li.active a').data('tab-name');
		var user = data.find('[name="assigned_user_id"]');
		var dateStartEl = data.find('[name="date_start"]');
		var dateStartVal = dateStartEl.val();
		var dateStartFormat = dateStartEl.data('date-format');
		if (typeof dateStartVal == 'undefined') {
			return;
		}
		var validDateFromat = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, '-1', ' ');
		var currentDate = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, ' ', ' ');
		var dateEndFirst = Vtiger_Helper_Js.convertToDateString(dateStartVal, dateStartFormat, '+1', ' ');
		var dateEnd = Vtiger_Helper_Js.convertToDateString(dateEndFirst, 'yyyy-mm-dd', '+1', ' ');

		var params = {
			module: 'Calendar',
			action: 'Calendar',
			mode: 'getEvents',
			start: validDateFromat,
			end: dateEnd,
			user: user.val()
		}
		AppConnector.request(params).then(function (events) {
			if (typeof events.result != 'undefined' && events.result.length > 0) {
				events = events.result;
				data.find('.modal-body').css({'max-height': '500px', 'overflow-y': 'auto'});
				for (var ev in events) {
					icon = 'glyphicon glyphicon-calendar';
					linkHtml = '';
					hidden = '';
					helpIcon = ''
					if (events[ev]['set'] == 'Task') {
						icon = 'icon-tasks';
					}
					if (events[ev]['linkl']) {
						linkHtml = '<div class="cut-string"><i class="calIcon modIcon_' + events[ev]['linkm'] + '"></i> ' + events[ev]['linkl'] + '</div>';
					}
					helpIcon = '<div><label> ' + app.vtranslate('JS_START_DATE') + ': &nbsp</label>' + events[ev]['start'] + ' </div>\n\
								<div><label> ' + app.vtranslate('JS_END_DATE') + ': &nbsp</label>' + events[ev]['end'] + ' </div>\n\
								<div class=' + 'textOverflowEllipsis' + '><label> ' + app.vtranslate('JS_SUBJECT') + ': &nbsp</label>' + events[ev]['title'] + '</div>\n\
								<div><label> ' + app.vtranslate('JS_STATE') + ': &nbsp</label>' + events[ev]['labels']['state'] + ' </div>\n\
								<div><label> ' + app.vtranslate('JS_STATUS') + ': &nbsp</label>' + events[ev]['labels']['sta'] + ' </div>\n\
								<div><label> ' + app.vtranslate('JS_PRIORITY') + ': &nbsp</label>' + events[ev]['labels']['pri'] + ' </div>'
					/*if(typeActive == 'Task' && events[ev]['set'] != 'Task'){
					 hidden = 'hide';
					 }else if(typeActive == 'Event' && events[ev]['set'] == 'Task'){
					 hidden = 'hide';
					 }*/
					if (events[ev]['start'].indexOf(validDateFromat) > -1) {
						data.find('#prev_events .table').append('<tr class="mode_' + events[ev]['set'] + ' ' + hidden + ' addedNearCalendarEvent" ><td><a target="_blank" href="' + events[ev]['url'] + '"><div class="cut-string"><i class="' + icon + '" style="vertical-align:middle; margin-bottom:4px;"></i><span><strong> ' + events[ev]['start'] + '</strong></span><span> ' + events[ev]['title'].substring(0, 22) + ' </span><span style="margin-left: 5px;margin-top: 2px;"  class="HelpInfoPopover " title="" data-placement="top" data-content="' + helpIcon + '"><i class="glyphicon glyphicon-info-sign"></i></span</div></a>' + linkHtml + '</td></tr>');
					} else if (events[ev]['start'].indexOf(currentDate) > -1) {
						data.find('#cur_events .table').append('<tr class="mode_' + events[ev]['set'] + ' ' + hidden + ' addedNearCalendarEvent" ><td><a target="_blank" href="' + events[ev]['url'] + '"><div class="cut-string"><i class="' + icon + '" style="vertical-align:middle; margin-bottom:4px;"></i><span><strong> ' + events[ev]['start'] + '</strong></span><span> ' + events[ev]['title'].substring(0, 22) + ' </span><span style="margin-left: 5px;margin-top: 2px;"  class="HelpInfoPopover " title="" data-placement="top" data-content="' + helpIcon + '"><i class="glyphicon glyphicon-info-sign"></i></span></div></a>' + linkHtml + '</td></tr>');
					} else if (events[ev]['start'].indexOf(dateEndFirst) > -1) {
						data.find('#next_events .table').append('<tr class="mode_' + events[ev]['set'] + ' ' + hidden + ' addedNearCalendarEvent"><td><a target="_blank" href="' + events[ev]['url'] + '"><div class="cut-string"><i class="' + icon + '" style="vertical-align:middle; margin-bottom:4px;"></i><span><strong> ' + events[ev]['start'] + '</strong></span><span> ' + events[ev]['title'].substring(0, 22) + ' </span><span style="margin-left: 5px;margin-top: 2px;"  class="HelpInfoPopover " title="" data-placement="top" data-content="' + helpIcon + '"><i class="glyphicon glyphicon-info-sign"></i></span</div></a>' + linkHtml + '</td></tr>');
					}
				}
				var quickCreateForm
				thisInstance.registerHelpInfo(quickCreateForm);

			} else {
				data.find('.modal-body').css({'max-height': '', 'overflow-y': ''});
			}
		})
	},
	registerChangeNearCalendarEvent: function (data, module) {
		var thisInstance = this;
		if (!data || module != 'Calendar' || typeof module == 'undefined') {
			return;
		}
		var user = data.find('[name="assigned_user_id"]');
		var dateStartEl = data.find('[name="date_start"]');
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
		thisInstance.getNearCalendarEvent(data, module);
	},
	toggleTimesInputs: function (form) {
		form.find(':checkbox').change(function () {
			var checkboxName = $(this).attr('name');
			if ('allday' == checkboxName) {
				var checkboxIsChecked = $(this).is(':checked');
				if (checkboxIsChecked) {
					form.find('.active .time').hide();
				} else {
					form.find('.active .time').show();
				}
			}
		});
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

				// save ckeditor values to inputs before submitting
				jQuery('textarea[id$="_qc"]').each(function () {
					var thisId = jQuery(this).attr('id');
					var ckValue = CKEDITOR.instances[thisId].getData();
					jQuery(this).val(ckValue);
				});

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
								//fix for Refresh list view after Quick create 
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
		jQuery('#globalSearchValue, #globalMobileSearchValue').keypress(function (e) {
			var currentTarget = jQuery(e.currentTarget)
			if (e.which == 13) {
				thisInstance.labelSearch(currentTarget);
			}
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
			jQuery('#globalSearchValue, #globalMobileSearchValue').gsAutocomplete({
				minLength: jQuery('#gsMinLength').val(),
				source: function (request, response) {
					var basicSearch = new Vtiger_BasicSearch_Js();
					basicSearch.reduceNumberResults = jQuery('#gsAmountResponse').val();
					basicSearch.returnHtml = false;
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
				},
				close: function (event, ui) {
					jQuery('#globalSearchValue, #globalMobileSearchValue').val('');
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
		var basicSearch = new Vtiger_BasicSearch_Js();
		var progress = jQuery.progressIndicator();
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
		var history = localStorage.history;
		if (history != "" && history != null) {
			var sp = history.toString().split(",");
			var item = sp[sp.length - 1].toString().split("|");
			BtnText = item[0];
			BtnLink = item[1];
			
		}
		var htmlContent = '<ul class="dropdown-menu pull-right historyList" role="menu">';
		var date = new Date().getTime();
		var howmanyDays = -1;
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
					if(writeSelector && (howmanyDays != app.howManyDaysFromDate(d))){
						howmanyDays = app.howManyDaysFromDate(d);
						if(howmanyDays == 0){
							htmlContent += '<li class="selectorHistory">' + app.vtranslate('JS_TODAY') + '</li>';
						}
						else if(howmanyDays == 1){
							htmlContent += '<li class="selectorHistory">' + app.vtranslate('JS_YESTERDAY') + '</li>';
						}
						else{
							htmlContent += '<li class="selectorHistory">' + app.vtranslate('JS_OLDER') + '</li>';
							writeSelector = false;
						}
					}
					if(writeSelector)
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
			localStorage.history = sp.toString();
		} else {
			var stack = new Array();
			var Label = this.getHistoryLabel();
			if (Label.length > 1) {
				stack.push(this.getHistoryLabel() + '|' + document.URL + '|' + date);
				localStorage.history = stack.toString();
			}
		}
		htmlContent += '<li class="divider"></li><li><a class="clearHistory" href="#">' + app.vtranslate('JS_CLEAR_HISTORY') + '</a></li>';
		htmlContent += '</ul>';
		$(".showHistoryBtn").after(htmlContent);
		this.registerClearHistory();
	},
	getHistoryLabel: function () {
		var label = "";
		$(".breadcrumbsLinks span").each(function (index) {
			label += $(this).text();
		});
		return label;
	},
	registerClearHistory: function () {
		$(".historyBtn .clearHistory").click(function () {
			localStorage.history = "";
			var htmlContent = '<li class="divider"></li><li><a class="clearHistory" href="#">' + app.vtranslate('JS_CLEAR_HISTORY') + '</a></li>';
			$(".historyBtn .dropdown-menu").html(htmlContent);
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
		if (app.getViewName() === 'Detail' || app.getViewName() === 'Edit') {
			url += '&sourceModule=' + app.getModuleName();
			url += '&sourceRecord=' + app.getRecordId();
		}
		var progress = jQuery.progressIndicator();
		thisInstance.getQuickCreateForm(url, moduleName, params).then(function (data) {
			thisInstance.handleQuickCreateData(data, params);
			progress.progressIndicator({
				'mode': 'hide'
			});
		});
	},
	registerReminderNotice: function () {
		var thisInstance = this;
		$('#page').before('<div class="remindersNoticeContainer"></div>');
		var block = $('.remindersNoticeContainer');
		$('.remindersNotice').click(function () {
			thisInstance.hideActionMenu();
			block.toggleClass("toggled");
		});
		block.css('top', $('.commonActionsContainer').height());
		block.height($(window).height() - $('footer.navbar-default').height() - $('.commonActionsContainer').height() + 2);
	},
	registerMobileEvents: function (){
		var thisInstance = this;
		$('.rightHeaderBtnMenu').click(function(){
			thisInstance.hideActionMenu();
			thisInstance.hideSearchMenu();
			$('.mobileLeftPanel ').toggleClass('mobileMenuOn');
		});
		$('.actionMenuBtn').click(function (){
			thisInstance.hideSearchMenu();
			thisInstance.hideMobileMenu();
			$('.actionMenu').toggleClass('actionMenuOn');
		});
		$('.searchMenuBtn').click(function(){
			thisInstance.hideActionMenu();
			thisInstance.hideMobileMenu();
			$('.searchMenu').toggleClass('toogleSearchMenu');
		});
		$('#searchMobileIcon').on('click', function (e) {
			var currentTarget = $('#globalMobileSearchValue');
			thisInstance.hideSearchMenu();
			var pressEvent = $.Event("keypress");
			pressEvent.which = 13;
			currentTarget.trigger(pressEvent);
		});
	},
	hideMobileMenu: function (){
		$('.mobileLeftPanel ').removeClass('mobileMenuOn');
	},
	hideSearchMenu: function() {
		$('.searchMenu').removeClass('toogleSearchMenu');
	},
	hideActionMenu: function (){
		$('.actionMenu').removeClass('actionMenuOn');
	},
	showPdfModal: function (url) {
		var params = {};
		if (app.getViewName() == 'List') {
			var selected = Vtiger_List_Js.getSelectedRecordsParams();
			if(selected === false){
				return false;
			}
			jQuery.extend(params, selected);
		}
		url += '&' + jQuery.param(params);
		app.showModalWindow(null, url);
	},
	registerEvents: function () {
		var thisInstance = this;
		thisInstance.recentPageViews();

		jQuery('#globalSearch').click(function () {
			thisInstance.hideSearchMenu();
			var advanceSearchInstance = new Vtiger_AdvanceSearch_Js();
			advanceSearchInstance.initiateSearch().then(function () {
				advanceSearchInstance.selectBasicSearchValue();
			});
		});
		jQuery('#searchIcon').on('click', function (e) {
			var currentTarget = jQuery('#globalSearchValue');
			var pressEvent = jQuery.Event("keypress");
			pressEvent.which = 13;
			currentTarget.trigger(pressEvent);
		});
		thisInstance.registerAnnouncement();
		this.setAnnouncement();

		thisInstance.registerHotKeys();
		//this.registerCalendarButtonClickEvent();
		//After selecting the global search module, focus the input element to type
		jQuery('#basicSearchModulesList').change(function () {
			jQuery('#globalSearchValue').focus();
		});

		thisInstance.basicSearch();
		jQuery('#quickCreateModules,#compactquickCreate,#topMenus').on("click", ".quickCreateModule", function (e, params) {
			var moduleName = jQuery(e.currentTarget).data('name');
			thisInstance.quickCreateModule(moduleName);
		});
		
		thisInstance.registerMobileEvents();
		
		if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
			jQuery('#basicSearchModulesList_chosen').find('.chzn-results').css({'max-height': '350px', 'overflow-y': 'scroll'});
		}
		else {
			app.showScrollBar(jQuery('#basicSearchModulesList_chosen').find('.chzn-results'),
					{
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
