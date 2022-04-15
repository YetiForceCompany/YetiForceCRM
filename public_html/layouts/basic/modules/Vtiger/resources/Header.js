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

//Show Alert if user is on a unsupported browser (IE7, IE8, ..etc)
if (
	/MSIE 6.0/.test(navigator.userAgent) ||
	/MSIE 7.0/.test(navigator.userAgent) ||
	/MSIE 8.0/.test(navigator.userAgent) ||
	/MSIE 9.0/.test(navigator.userAgent)
) {
	if (app.getCookie('oldbrowser') != 'true') {
		app.setCookie('oldbrowser', true, 365);
		window.location.href = 'layouts/basic/modules/Vtiger/browsercompatibility/Browser_compatibility.html';
	}
}

$.Class(
	'Vtiger_Header_Js',
	{
		self: false,
		getInstance: function () {
			if (this.self != false) {
				return this.self;
			}
			this.self = new Vtiger_Header_Js();
			return this.self;
		}
	},
	{
		menuContainer: false,
		contentContainer: false,
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
			$('.js-quickcreate-search').on('keyup', function () {
				let value = $(this).val().toLowerCase();
				$('.quickCreateModules .js-quickcreate-search-item a').filter(function () {
					let item = $(this).closest('.js-quickcreate-search-item');
					if ($(this).text().toLowerCase().indexOf(value) > -1) {
						item.removeClass('d-none');
					} else {
						item.addClass('d-none');
					}
				});
				$('.js-quickcreate-search-block').hide();
				$('.js-quickcreate-search-item')
					.not('.d-none')
					.each(function () {
						$(this).closest('.js-quickcreate-search-block').show();
					});
			});
		},
		showAnnouncement: function () {
			let thisInstance = this;
			let announcementContainer = $('#announcements');
			let announcements = announcementContainer.find('.announcement');
			if (announcements.length > 0) {
				let announcement = announcements.first();
				let aid = announcement.data('id');

				app.showModalWindow(
					announcement.find('.modal'),
					function (modal) {
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
							});
						});
					},
					'',
					{ backdrop: 'static', keyboard: false }
				);
			}
		},
		registerAnnouncements: function () {
			let thisInstance = this;
			let announcementContainer = $('#announcements');
			if (announcementContainer.length == 0) {
				return false;
			}
			thisInstance.showAnnouncement();
		},
		registerCalendarButtonClickEvent: function () {
			let element = $('#calendarBtn');
			let currentDate = element.data('date');
			element.on('click', function (e) {
				e.stopImmediatePropagation();
				element.closest('div.nav').find('div.open').removeClass('open');
				let calendar = $('#' + element.data('datepickerId'));
				if ($(calendar).is(':visible')) {
					element.DatePickerHide();
				} else {
					element.DatePickerShow();
				}
			});
			element.DatePicker({
				format: App.Fields.Date.convertToDatePickerFormat(element.data('dateFormat')),
				date: currentDate,
				calendars: 1,
				starts: 1,
				className: 'globalCalendar'
			});
		},
		isFreeDay: function (dayOfWeek) {
			if (dayOfWeek == 0 || dayOfWeek == 6) {
				return true;
			}
			return false;
		},
		basicSearch: function () {
			let thisInstance = this;
			$('.js-global-search__value').on('keypress', function (e) {
				let currentTarget = $(e.currentTarget);
				if (e.which == 13) {
					thisInstance.hideSearchMenu();
					thisInstance.labelSearch(currentTarget);
				}
			});
			$('.js-global-search-operator').on('click', function (e) {
				let currentTarget = $(e.target);
				let block = currentTarget.closest('.js-global-search__input');
				block.find('.js-global-search__value').data('operator', currentTarget.data('operator'));
				block.find('.js-global-search-operator .dropdown-item').removeClass('active');
				currentTarget.closest('.dropdown-item').addClass('active');
			});
			if ($('#gsAutocomplete').val() == 1) {
				$.widget('custom.gsAutocomplete', $.ui.autocomplete, {
					_create: function () {
						this._super();
						this.widget().menu('option', 'items', '> :not(.ui-autocomplete-category)');
					},
					_renderMenu: function (ul, items) {
						let that = this,
							currentCategory = '';
						$.each(items, function (index, item) {
							if (item.category != currentCategory) {
								ul.append("<li class='ui-autocomplete-category'>" + item.category + '</li>');
								currentCategory = item.category;
							}
							that._renderItemData(ul, item);
						});
					},
					_renderItemData: function (ul, item) {
						return this._renderItem(ul, item).data('ui-autocomplete-item', item);
					},
					_renderItem: function (ul, item) {
						let url = 'index.php?module=' + item.module + '&view=Detail&record=' + item.id;
						return $('<li>')
							.data('item.autocomplete', item)
							.append($("<a href='" + url + "' title='" + item.label + "'></a>").html(item.label))
							.appendTo(ul);
					}
				});
				$('.js-global-search__value').gsAutocomplete({
					minLength: app.getMainParams('gsMinLength'),
					source: function (request, response) {
						let basicSearch = new Vtiger_BasicSearch_Js();
						basicSearch.reduceNumberResults = app.getMainParams('gsAmountResponse');
						basicSearch.returnHtml = false;
						basicSearch.setMainContainer(this.element.closest('.js-global-search__input'));
						basicSearch.search(request.term).done(function (data) {
							data = JSON.parse(data);
							let serverDataFormat = data.result;
							let reponseDataList = [];
							for (let id in serverDataFormat) {
								let responseData = serverDataFormat[id];
								reponseDataList.push(responseData);
							}
							response(reponseDataList);
						});
					},
					classes: {
						'ui-autocomplete': 'u-overflow-y-auto u-overflow-x-hidden u-max-h-70vh u-max-w-sm-70 u-max-w-lg-40'
					},
					select: function (event, ui) {
						let selectedItemData = ui.item;
						if (selectedItemData.permitted) {
							let url = 'index.php?module=' + selectedItemData.module + '&view=Detail&record=' + selectedItemData.id;
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
			let val = currentTarget.val();
			if (val == '') {
				app.showAlert(app.vtranslate('JS_PLEASE_ENTER_SOME_VALUE'));
				currentTarget.focus();
				return false;
			}
			let progress = $.progressIndicator({
				position: 'html',
				blockInfo: {
					enabled: true
				}
			});
			let basicSearch = new Vtiger_BasicSearch_Js();
			basicSearch.setMainContainer(currentTarget.closest('.js-global-search__input'));
			basicSearch.search(val).done(function (data) {
				basicSearch.showSearchResults(data);
				progress.progressIndicator({
					mode: 'hide'
				});
			});
		},
		registerHotKeys: function () {
			$('.hotKey').each(function (index) {
				let thisObject = this;
				let key = $(thisObject).data('hotkeys');
				if (key != '') {
					Mousetrap.bind(key, function () {
						thisObject.click();
					});
				}
			});
		},
		registerReminderNotice: function () {
			let self = this;
			$('#page').before(
				`<div class="remindersNoticeContainer" tabindex="-1" role="dialog" aria-label="${app.vtranslate(
					'JS_REMINDER'
				)}" aria-hidden="true"></div>`
			);
			let block = $('.remindersNoticeContainer');
			let remindersNotice = $('.remindersNotice');
			remindersNotice.on('click', function () {
				if (!remindersNotice.hasClass('autoRefreshing')) {
					Vtiger_Index_Js.requestReminder();
				}
				self.hideActionMenu();
				self.hideBreadcrumbActionMenu();
				block.toggleClass('toggled');
				self.hideReminderNotification();
				app.closeSidebar();
				self.hideSearchMenu();
			});
		},
		registerReminderNotification: function () {
			let self = this;
			$('#page').before('<div class="remindersNotificationContainer" tabindex="-1" role="dialog"></div>');
			let block = $('.remindersNotificationContainer');
			let remindersNotice = $('.notificationsNotice');
			remindersNotice.on('click', function () {
				if (!remindersNotice.hasClass('autoRefreshing')) {
					Vtiger_Index_Js.getNotificationsForReminder();
				}
				self.hideActionMenu();
				self.hideBreadcrumbActionMenu();
				block.toggleClass('toggled');
				self.hideReminderNotice();
				app.closeSidebar();
				self.hideSearchMenu();
			});
		},
		toggleBreadcrumbActions(container) {
			let actionsContainer = container.find('.js-header-toggle__actions');
			if (!actionsContainer.length) {
				return;
			}
			let actionBtn = container.find('.js-header-toggle__actions-btn');
			if (!actionsContainer.closest('.js-btn-toolbar').length) {
				const actionBtnMargin = 5;
				const cssActionsTop = {
					top: actionBtn.offset().top + actionBtn.outerHeight() + actionBtnMargin
				};
				actionsContainer.css(cssActionsTop);
			}
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
			this.toggleBreadcrumbActions(container);
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
			let container = $('.tableRWD');
			container.find('thead tr th:gt(1)').attr('data-hide', 'phone');
			container.find('thead tr th:gt(3)').attr('data-hide', 'tablet,phone');
			container.find('thead tr th:last').attr('data-hide', '');
			let whichColumnEnable = container.find('thead').attr('col-visible-alltime');
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
			let records = $('.customTableRWD').find('[data-toggle-visible=false]');
			records.find('.footable-toggle').css('display', 'none');
		},
		registerSiteBarButton(container) {
			const key = 'ShowHideRightPanel' + app.getModuleName();
			let cache = !container.find('.toggleSiteBarRightButton').data('nocache');
			if (cache && app.cacheGet(key) == 'show') {
				this.toggleSiteBar(container.find('.toggleSiteBarRightButton'));
			} else if (cache && app.cacheGet(key) == null) {
				if (container.find('.siteBarRight').data('showpanel') == 1) {
					this.toggleSiteBar(container.find('.toggleSiteBarRightButton'));
				}
			}
			container.find('.toggleSiteBarRightButton').on('click', (e) => {
				let toggleButton = $(e.currentTarget);
				if (!toggleButton.data('nocache')) {
					if (toggleButton.closest('.siteBarRight').hasClass('hideSiteBar')) {
						app.cacheSet(key, 'show');
					} else {
						app.cacheSet(key, 'hide');
					}
				}

				this.toggleSiteBar(toggleButton);
			});
		},
		toggleSiteBar(toogleButton) {
			$('.rowContent').toggleClass('js-sitebar--active');
			toogleButton.closest('.siteBarRight').toggleClass('hideSiteBar');
			toogleButton.find('.fas').toggleClass('fa-chevron-right fa-chevron-left');
			toogleButton.toggleClass('hideToggleSiteBarRightButton');
		},
		registerToggleButton: function () {
			$('.buttonTextHolder .dropdown-menu a').on('click', function () {
				$(this).parents('.d-inline-block').find('.dropdown-toggle .textHolder').html($(this).text());
			});
		},
		registerKnowledgeBaseModal() {
			$('.js-knowledge-base-modal').on('click', () => {
				if (window.KnowledgeBaseModalVueComponent.mounted === undefined) {
					window.KnowledgeBaseModalVueComponent.mount({
						el: '#KnowledgeBaseModal',
						state: {
							moduleName: 'KnowledgeBase',
							dialog: true
						}
					});
					KnowledgeBaseModalVueComponent.mounted = true;
				} else {
					vuexStore.commit('KnowledgeBase/setDialog', true);
				}
			});
		},
		registerChat() {
			if (window === window.parent && window.ChatModalVueComponent !== undefined) {
				window.ChatModalVueComponent.mount({
					el: '#ChatModalVue'
				});
			}
		},
		registerEvents: function () {
			let thisInstance = this;
			const container = thisInstance.getContentsContainer(),
				menuContainer = container.find('.js-menu--scroll'),
				quickCreateModal = container.find('.quickCreateModules');
			app.showNewScrollbarLeft(menuContainer, { suppressScrollX: true });
			app.showNewScrollbar(menuContainer.find('.subMenu').last(), { suppressScrollX: true });
			thisInstance.registerFooTable(); //Enable footable
			$('.js-clear-history').on('click', () => {
				app.clearBrowsingHistory();
			});
			$('.globalSearch').on('click', function () {
				let currentTarget = $(this);
				thisInstance.hideSearchMenu();
				let advanceSearchInstance = new Vtiger_AdvanceSearch_Js();
				advanceSearchInstance.setParentContainer(currentTarget.closest('.js-global-search__input'));
				advanceSearchInstance.initiateSearch();
			});
			$('.searchIcon').on('click', function (e) {
				let currentTarget = $(this).closest('.js-global-search__input').find('.js-global-search__value');
				let pressEvent = $.Event('keypress');
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
				let value = $(this).closest('.js-global-search__input').find('.js-global-search__value');
				setTimeout(function () {
					value.focus();
				}, 100);
			});
			thisInstance.basicSearch();
			quickCreateModal.on('click', '.quickCreateModule', function (e, params) {
				let moduleName = $(e.currentTarget).data('name');
				quickCreateModal.modal('hide');
				App.Components.QuickCreate.createRecord(moduleName);
			});
			thisInstance.registerReminderNotification();
			thisInstance.registerMobileEvents();
			thisInstance.registerReminderNotice();
			thisInstance.registerQuickCreateSearch();
			thisInstance.registerKnowledgeBaseModal();
			thisInstance.registerChat();
		}
	}
);
jQuery(function () {
	window.addEventListener('popstate', (event) => {
		if (event.state) {
			window.location.href = event.state;
		}
	});
	Vtiger_Header_Js.getInstance().registerEvents();
});
