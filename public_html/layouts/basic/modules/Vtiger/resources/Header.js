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

//Show Alert if user is on a unsupported browser (IE7, IE8, ..etc)
if (/MSIE 6.0/.test(navigator.userAgent) || /MSIE 7.0/.test(navigator.userAgent) || /MSIE 8.0/.test(navigator.userAgent) || /MSIE 9.0/.test(navigator.userAgent)) {
	if (app.getCookie('oldbrowser') != 'true') {
		app.setCookie("oldbrowser", true, 365);
		window.location.href = 'layouts/basic/modules/Vtiger/browsercompatibility/Browser_compatibility.html';
	}
}

$.Class("Vtiger_Header_Js", {
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
		this.setContentsContainer('.js-base-container');
	},
	setContentsContainer: function (element) {
		if (element instanceof $) {
			this.contentContainer = element;
		} else {
			this.contentContainer = $(element);
		}
		return this;
	},
	getContentsContainer: function () {
		return this.contentContainer;
	},
	registerQuickCreateSearch() {
		$(".js-quickcreate-search").on('keyup', function () {
			let value = $(this).val().toLowerCase();
			$(".quickCreateModules .js-quickcreate-search-item a").filter(function () {
				let item = $(this).closest('.js-quickcreate-search-item');
				if ($(this).text().toLowerCase().indexOf(value) > -1) {
					item.removeClass('d-none');
				} else {
					item.addClass('d-none');
				}
			});
			$(".js-quickcreate-search-block").hide();
			$(".js-quickcreate-search-item").not(".d-none").each(function () {
				$(this).closest('.js-quickcreate-search-block').show();
			});
		});
	},
	getQuickCreateForm: function (url, moduleName, params) {
		var aDeferred = $.Deferred();
		var requestParams;
		if (typeof params === "undefined") {
			params = {};
		}
		if ((!params.noCache) || (typeof (params.noCache) === "undefined")) {
			if (typeof Vtiger_Header_Js.quickCreateModuleCache[moduleName] !== "undefined") {
				aDeferred.resolve(Vtiger_Header_Js.quickCreateModuleCache[moduleName]);
				return aDeferred.promise();
			}
		}
		requestParams = url;
		if (typeof params.data !== "undefined") {
			requestParams = {};
			requestParams['data'] = params.data;
			requestParams['url'] = url;
		}
		AppConnector.request(requestParams).done(function (data) {
			if ((!params.noCache) || (typeof (params.noCache) === "undefined")) {
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
		var aDeferred = $.Deferred();
		var quickCreateSaveUrl = form.serializeFormData();
		AppConnector.request(quickCreateSaveUrl).done(
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
			$(data).removeAttr('data-validation-engine');
		});
		form.addClass('not_validation');
		form.submit();
	},
	showAnnouncement: function () {
		var thisInstance = this;
		var announcementContainer = $('#announcements');
		var announcements = announcementContainer.find('.announcement');
		if (announcements.length > 0) {
			var announcement = announcements.first();
			var aid = announcement.data('id')

			app.showModalWindow(announcement.find('.modal'), function (modal) {
				announcement.remove();
				modal.find('button').on('click', function (e) {
					AppConnector.request({
						module: 'Announcements',
						action: 'BasicAjax',
						mode: 'mark',
						record: aid,
						type: $(this).data('type')
					}).done(function (res) {
						app.hideModalWindow();
						thisInstance.showAnnouncement();
					})
				});
			}, '', {backdrop: 'static'});
		}
	},
	registerAnnouncements: function () {
		var thisInstance = this;
		var announcementContainer = $('#announcements');
		if (announcementContainer.length == 0) {
			return false;
		}
		thisInstance.showAnnouncement();
	},
	registerCalendarButtonClickEvent: function () {
		var element = $('#calendarBtn');
		var dateFormat = element.data('dateFormat');
		var currentDate = element.data('date');
		var vtigerDateFormat = app.convertToDatePickerFormat(dateFormat);
		element.on('click', function (e) {
			e.stopImmediatePropagation();
			element.closest('div.nav').find('div.open').removeClass('open');
			var calendar = $('#' + element.data('datepickerId'));
			if ($(calendar).is(':visible')) {
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
		if (typeof container === "undefined") {
			container = $('form[name="QuickCreate"]');
		}
		app.showPopoverElementView(container.find('.js-help-info'));
	},
	handleQuickCreateData: function (data, params) {
		if (typeof params === "undefined") {
			params = {};
		}
		var thisInstance = this;
		app.showModalWindow(data, function (container) {
			var quickCreateForm = container.find('form[name="QuickCreate"]');
			var moduleName = quickCreateForm.find('[name="module"]').val();
			var editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
			editViewInstance.registerBasicEvents(quickCreateForm);
			let moduleClassName = moduleName + '_QuickCreate_Js';
			if (typeof window[moduleClassName] !== "undefined") {
				(new window[moduleClassName]()).registerEvents(container);
			}
			quickCreateForm.validationEngine(app.validationEngineOptions);
			if (typeof params.callbackPostShown !== "undefined") {
				params.callbackPostShown(quickCreateForm);
			}
			thisInstance.registerQuickCreatePostLoadEvents(quickCreateForm, params);
			thisInstance.registerHelpInfo(quickCreateForm);
		});
	},
	isFreeDay: function (dayOfWeek) {

		if (dayOfWeek == 0 || dayOfWeek == 6) {
			return true;
		}
		return false;
	},

	registerQuickCreatePostLoadEvents: function (form, params) {
		var thisInstance = this;
		var submitSuccessCallbackFunction = params.callbackFunction;
		var goToFullFormCallBack = params.goToFullFormcallback;
		if (typeof submitSuccessCallbackFunction === "undefined") {
			submitSuccessCallbackFunction = function () {
			};
		}

		form.on('submit', function (e) {
			var form = $(e.currentTarget);
			if (form.hasClass('not_validation')) {
				return true;
			}
			var module = form.find('[name="module"]').val();
			//Form should submit only once for multiple clicks also
			if (typeof form.data('submit') !== "undefined") {
				return false;
			} else {
				var invalidFields = form.data('jqv').InvalidFields;
				if (invalidFields.length > 0) {
					//If validation fails, form should submit again
					form.removeData('submit');
					$.progressIndicator({'mode': 'hide'});
					e.preventDefault();
					return;
				} else {
					//Once the form is submiting add data attribute to that form element
					form.data('submit', 'true');
					$.progressIndicator({'mode': 'hide'});
				}

				var recordPreSaveEvent = $.Event(Vtiger_Edit_Js.recordPreSave);
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
					let progress = $.progressIndicator({
						'message': app.vtranslate('JS_SAVE_LOADER_INFO'),
						'position': 'html',
						'blockInfo': {
							'enabled': true
						}
					});
					targetInstance.quickCreateSave(form).done(function (data) {
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
						app.event.trigger("QuickCreate.AfterSaveFinal", data, form);
						progress.progressIndicator({'mode': 'hide'});
						if (data.success) {
							Vtiger_Helper_Js.showPnotify({
								text: app.vtranslate('JS_SAVE_NOTIFY_SUCCESS'),
								type: 'success'
							});
						}
					});
				} else {
					//If validation fails in recordPreSaveEvent, form should submit again
					form.removeData('submit');
					$.progressIndicator({'mode': 'hide'});
				}
				e.preventDefault();
			}
		});

		form.find('.js-full-editlink').on('click', function (e) {
			var form = $(e.currentTarget).closest('form');
			var editViewUrl = $(e.currentTarget).data('url');
			if (typeof goToFullFormCallBack !== "undefined") {
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
			var container = $(target);
			container.find('[name]').each(function (index, element) {
				element = $(element);
				element.attr('data-element-name', element.attr('name')).removeAttr('name');
			});
		}
		//This will add the name attributes and get value from data-element-name . We are doing this to avoid
		//Multiple element to send as in calendar
		var quickCreateTabOnShow = function (target) {
			var container = $(target);
			container.find('[data-element-name]').each(function (index, element) {
				element = $(element);
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
		tabElements.filter('a:not(.active)').each(function (e) {
			quickCreateTabOnHide($(this).attr('data-target'));
		});
	},
	basicSearch: function () {
		var thisInstance = this;
		$('.js-global-search__value').on('keypress', function (e) {
			var currentTarget = $(e.currentTarget)
			if (e.which == 13) {
				thisInstance.hideSearchMenu();
				thisInstance.labelSearch(currentTarget);
			}
		});
		$('.js-global-search-operator').on('click', function (e) {
			var currentTarget = $(e.target);
			var block = currentTarget.closest('.js-global-search__input');
			block.find('.js-global-search__value').data('operator', currentTarget.data('operator'));
			block.find('.js-global-search-operator .dropdown-item').removeClass('active');
			currentTarget.closest('.dropdown-item').addClass('active');
		});
		if ($('#gsAutocomplete').val() == 1) {
			$.widget("custom.gsAutocomplete", $.ui.autocomplete, {
				_create: function () {
					this._super();
					this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
				},
				_renderMenu: function (ul, items) {
					var that = this, currentCategory = "";
					$.each(items, function (index, item) {
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
					var url = 'index.php?module=' + item.module + '&view=Detail&record=' + item.id;
					return $("<li>")
						.data("item.autocomplete", item)
						.append($("<a href='" + url + "'></a>").html(item.label))
						.appendTo(ul);
				},
			});
			$('.js-global-search__value').gsAutocomplete({
				minLength: app.getMainParams('gsMinLength'),
				source: function (request, response) {
					var basicSearch = new Vtiger_BasicSearch_Js();
					basicSearch.reduceNumberResults = app.getMainParams('gsAmountResponse');
					basicSearch.returnHtml = false;
					basicSearch.setMainContainer(this.element.closest('.js-global-search__input'));
					basicSearch.search(request.term).done(function (data) {
						data = JSON.parse(data);
						var serverDataFormat = data.result;
						var reponseDataList = [];
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
					//$('.js-global-search__value').val('');
				}
			});
		}
	},
	labelSearch: function (currentTarget) {
		var val = currentTarget.val();
		if (val == '') {
			app.showAlert(app.vtranslate('JS_PLEASE_ENTER_SOME_VALUE'));
			currentTarget.focus();
			return false;
		}
		var progress = $.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var basicSearch = new Vtiger_BasicSearch_Js();
		basicSearch.setMainContainer(currentTarget.closest('.js-global-search__input'));
		basicSearch.search(val).done(function (data) {
			basicSearch.showSearchResults(data);
			progress.progressIndicator({
				'mode': 'hide'
			});
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
		if (window !== window.parent) {
			window.parent.Vtiger_Header_Js.getInstance().quickCreateModule(moduleName, params);
			return;
		}
		var thisInstance = this;
		if (typeof params === "undefined") {
			params = {};
		}
		if (typeof params.callbackFunction === "undefined") {
			params.callbackFunction = function () {
			};
		}
		var url = 'index.php?module=' + moduleName + '&view=QuickCreateAjax';
		if ((app.getViewName() === 'Detail' || (app.getViewName() === 'Edit' && app.getRecordId() !== undefined)) && app.getParentModuleName() != 'Settings') {
			url += '&sourceModule=' + app.getModuleName();
			url += '&sourceRecord=' + app.getRecordId();
		}
		var progress = $.progressIndicator();
		thisInstance.getQuickCreateForm(url, moduleName, params).done(function (data) {
			thisInstance.handleQuickCreateData(data, params);
			app.registerEventForClockPicker();
			progress.progressIndicator({
				'mode': 'hide'
			});
		});
	},
	registerReminderNotice: function () {
		var self = this;
		$('#page').before(`<div class="remindersNoticeContainer" tabindex="-1" role="dialog" aria-label="${app.vtranslate('JS_REMINDER')}" aria-hidden="true"></div>`);
		var block = $('.remindersNoticeContainer');
		var remindersNotice = $('.remindersNotice');
		remindersNotice.on('click', function () {
			if (!remindersNotice.hasClass('autoRefreshing')) {
				Vtiger_Index_Js.requestReminder();
			}
			self.hideActionMenu();
			self.hideBreadcrumbActionMenu()
			block.toggleClass("toggled");
			self.hideReminderNotification();
			app.closeSidebar();
			self.hideSearchMenu();
		});
	},
	registerReminderNotification: function () {
		var self = this;
		$('#page').before('<div class="remindersNotificationContainer" tabindex="-1" role="dialog"></div>');
		var block = $('.remindersNotificationContainer');
		var remindersNotice = $('.notificationsNotice');
		remindersNotice.on('click', function () {
			if (!remindersNotice.hasClass('autoRefreshing')) {
				Vtiger_Index_Js.getNotificationsForReminder();
			}
			self.hideActionMenu();
			self.hideBreadcrumbActionMenu();
			block.toggleClass("toggled");
			self.hideReminderNotice();
			app.closeSidebar();
			self.hideSearchMenu();
		});
	},
	toggleBreadcrumActions(container) {
		let actionsContainer = container.find('.js-header-toggle__actions');
		if (!actionsContainer.length) {
			return;
		}
		let actionBtn = container.find('.js-header-toggle__actions-btn'),
			actionBtnMargin = 5,
			cssActionsTop = {top: actionBtn.offset().top + actionBtn.outerHeight() + actionBtnMargin};
		actionsContainer.css(cssActionsTop);
		actionBtn.on('click', () => {
			actionsContainer.toggleClass('is-active');
		});
	},
	registerMobileEvents: function () {
		const self = this,
			container = this.getContentsContainer();
		$('.rightHeaderBtnMenu').on('click', function () {
			self.hideActionMenu();
			self.hideBreadcrumbActionMenu();
			self.hideSearchMenu();
			self.hideReminderNotice();
			self.hideReminderNotification();
			$('.mobileLeftPanel ').toggleClass('mobileMenuOn');
		});
		$('.js-quick-action-btn').on('click', function () {
			let currentTarget = $(this);
			app.closeSidebar();
			self.hideBreadcrumbActionMenu();
			self.hideSearchMenu();
			self.hideReminderNotice();
			self.hideReminderNotification();
			$('.actionMenu').toggleClass('actionMenuOn');
			if (currentTarget.hasClass('active')) {
				currentTarget.removeClass('active');
				currentTarget.attr('aria-expanded', 'false');
				currentTarget.popover();
			} else {
				currentTarget.addClass('active');
				currentTarget.attr('aria-expanded', 'true');
				currentTarget.popover('disable');
			}
			$('.quickCreateModules').on('click', function () {
				self.hideActionMenu();
			});
		});
		$('.searchMenuBtn').on('click', function () {
			let currentTarget = $(this);
			app.closeSidebar();
			self.hideActionMenu();
			self.hideBreadcrumbActionMenu();
			self.hideReminderNotice();
			self.hideReminderNotification();
			$('.searchMenu').toggleClass('toogleSearchMenu');
			if (currentTarget.hasClass('active')) {
				currentTarget.removeClass('active');
				$('.searchMenuBtn .c-header__btn').attr('aria-expanded', 'false');
			} else {
				currentTarget.addClass('active');
				$('.searchMenuBtn .c-header__btn').attr('aria-expanded', 'true');
			}
		});
		$('.js-header__btn--mail .dropdown').on('show.bs.dropdown', function () {
			app.closeSidebar();
			self.hideActionMenu();
			self.hideBreadcrumbActionMenu();
			self.hideReminderNotice();
			self.hideReminderNotification();
			self.hideSearchMenu();
		});
		this.toggleBreadcrumActions(container);
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
	hideBreadcrumbActionMenu: function () {
		$('.js-header-toggle__actions').removeClass('is-active');
	},
	hideReminderNotice: function () {
		$('.remindersNoticeContainer').removeClass('toggled');
	},
	hideReminderNotification: function () {
		$('.remindersNotificationContainer').removeClass('toggled');
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
		$('.footable-toggle').on('click', function (event) {
			event.stopPropagation();
			$(this).trigger('footable_toggle_row');
		});
		var records = $('.customTableRWD').find('[data-toggle-visible=false]');
		records.find('.footable-toggle').css("display", "none");
	},
	registerSiteBarButton(container) {
		const key = 'ShowHideRightPanel' + app.getModuleName();
		if (app.cacheGet(key) == 'show') {
			this.toggleSiteBar(container.find('.toggleSiteBarRightButton'));
		} else if (app.cacheGet(key) == null) {
			if (container.find('.siteBarRight').data('showpanel') == 1) {
				this.toggleSiteBar(container.find('.toggleSiteBarRightButton'));
			}
		}
		container.find('.toggleSiteBarRightButton').on('click', (e) => {
			let toogleButton = $(e.currentTarget);
			if (toogleButton.closest('.siteBarRight').hasClass('hideSiteBar')) {
				app.cacheSet(key, 'show');
			} else {
				app.cacheSet(key, 'hide');
			}
			this.toggleSiteBar(toogleButton);
		});
	},
	toggleSiteBar(toogleButton) {
		$('.rowContent').toggleClass('js-sitebar--active');
		toogleButton.closest('.siteBarRight').toggleClass('hideSiteBar');
		toogleButton.find('.fas').toggleClass("fa-chevron-right fa-chevron-left");
		toogleButton.toggleClass('hideToggleSiteBarRightButton');
	},
	registerToggleButton: function () {
		$(".buttonTextHolder .dropdown-menu a").on('click', function () {
			$(this).parents('.d-inline-block').find('.dropdown-toggle .textHolder').html($(this).text());
		});
	},
	listenTextAreaChange: function () {
		var thisInstance = this;
		$('textarea').on('keyup', function () {
			var elem = $(this);
			if (!elem.data('has-scroll')) {
				elem.data('has-scroll', true);
				elem.on('scroll keyup', function () {
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
		if (typeof Chat_JS !== 'undefined') {
			Chat_JS.registerTrackingEvents();
		}
		const container = thisInstance.getContentsContainer(),
			menuContainer = container.find('.js-menu--scroll'),
			quickCreateModal = container.find('.quickCreateModules');
		app.showNewScrollbarLeft(menuContainer, {suppressScrollX: true});
		app.showNewScrollbar(menuContainer.find('.subMenu').last(), {suppressScrollX: true});
		thisInstance.listenTextAreaChange();
		thisInstance.registerFooTable(); //Enable footable
		$('.js-clear-history').on('click', () => {
			app.clearBrowsingHistory();
		});
		$('.globalSearch').on('click', function () {
			var currentTarget = $(this);
			thisInstance.hideSearchMenu();
			var advanceSearchInstance = new Vtiger_AdvanceSearch_Js();
			advanceSearchInstance.setParentContainer(currentTarget.closest('.js-global-search__input'));
			advanceSearchInstance.initiateSearch().done(function () {
				advanceSearchInstance.selectBasicSearchValue();
			});
		});
		$('.searchIcon').on('click', function (e) {
			var currentTarget = $(this).closest('.js-global-search__input').find('.js-global-search__value');
			var pressEvent = $.Event("keypress");
			pressEvent.which = 13;
			currentTarget.trigger(pressEvent);
		});
		thisInstance.registerAnnouncements();
		thisInstance.registerHotKeys();
		thisInstance.registerToggleButton();
		thisInstance.registerSiteBarButton($('#centerPanel'));
		//this.registerCalendarButtonClickEvent();
		//After selecting the global search module, focus the input element to type
		$('.basicSearchModulesList').on('change', function () {
			var value = $(this).closest('.js-global-search__input').find('.js-global-search__value')
			setTimeout(function () {
				value.focus();
			}, 100);
		});
		thisInstance.basicSearch();
		quickCreateModal.on("click", ".quickCreateModule", function (e, params) {
			var moduleName = $(e.currentTarget).data('name');
			quickCreateModal.modal('hide');
			thisInstance.quickCreateModule(moduleName);
		});

		thisInstance.registerMobileEvents();
		thisInstance.registerReminderNotice();
		thisInstance.registerReminderNotification();
		thisInstance.registerQuickCreateSearch();
	}
});
$(document).ready(function () {
	window.addEventListener('popstate', (event) => {
		if (event.state) {
			window.location.href = event.state;
		}
	});
	Vtiger_Header_Js.getInstance().registerEvents();
});
