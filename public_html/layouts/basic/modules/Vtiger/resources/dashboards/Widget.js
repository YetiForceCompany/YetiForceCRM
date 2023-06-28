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

jQuery.Class(
	'Vtiger_Widget_Js',
	{
		widgetPostLoadEvent: 'Vtiger.Dashboard.PostLoad',
		widgetPostRefereshEvent: 'Vtiger.Dashboard.PostRefresh',
		instances: {},
		id: +new Date() - 0,
		DOM_ATTRIBUTE_KEY: 'w_instance_',
		getInstance: function getInstance(container, widgetClassName, moduleName) {
			if (typeof moduleName === 'undefined') {
				moduleName = app.getModuleName();
			}
			let id = container.attr(this.DOM_ATTRIBUTE_KEY);
			if (!id) {
				id = this.DOM_ATTRIBUTE_KEY + this.id++;
				container.attr(this.DOM_ATTRIBUTE_KEY, id);
			}
			if (this.instances[id] !== undefined) {
				return this.instances[id];
			}

			const moduleClass = window[moduleName + '_' + widgetClassName + '_Widget_Js'];
			const fallbackClass = window['Vtiger_' + widgetClassName + '_Widget_Js'];
			const yetiClass = window['YetiForce_' + widgetClassName + '_Widget_Js'];
			const basicClass = YetiForce_Widget_Js;
			let instance;
			let className = '';
			if (typeof moduleClass !== 'undefined') {
				instance = new moduleClass(container, false, widgetClassName);
				className = moduleName + '_' + widgetClassName + '_Widget_Js';
			} else if (typeof fallbackClass !== 'undefined') {
				instance = new fallbackClass(container, false, widgetClassName);
				className = 'Vtiger_' + widgetClassName + '_Widget_Js';
			} else if (typeof yetiClass !== 'undefined') {
				instance = new yetiClass(container, false, widgetClassName);
				className = 'YetiForce_' + widgetClassName + '_Widget_Js';
			} else {
				instance = new basicClass(container, false, widgetClassName);
				className = 'YetiForce_Widget_Js';
			}
			instance.className = className;
			this.instances[id] = instance;

			return instance;
		}
	},
	{
		container: false,
		paramCache: false,
		init: function init(container, reload, widgetClassName) {
			container = $(container);
			this.setContainer(container);
			this.registerWidgetPostLoadEvent(container);
			if (!reload) {
				this.registerWidgetPostRefreshEvent(container);
			}
			this.registerCache(container);
		},
		areColorsFromDividingField() {
			return !!Number(this.getContainer().find('[name="colorsFromDividingField"]').val());
		},

		/**
		 * Get widget data
		 * @returns {*}
		 */
		getWidgetData(reload = false) {
			if (typeof this.widgetData !== 'undefined' && !reload) {
				return this.widgetData;
			}
			let widgetDataEl = this.getContainer().find('.widgetData');
			if (widgetDataEl.length) {
				return (this.widgetData = JSON.parse(widgetDataEl.val()));
			}
			return '';
		},

		getContainer: function getContainer() {
			return this.container;
		},
		/**
		 * Get widget content
		 * @returns {jQuery}
		 */
		getContainerContent: function getContainer() {
			return this.getContainer().find('.dashboardWidgetContent');
		},
		setContainer: function setContainer(element) {
			this.container = element;
			return this;
		},
		isEmptyData: function isEmptyData() {
			return this.getContainer().find('.widgetData').length === 0 || this.getContainer().find('.noDataMsg').length > 0;
		},
		getUserDateFormat: function getUserDateFormat() {
			return CONFIG.dateFormat;
		},

		registerRecordsCount: function registerRecordsCount() {
			var thisInstance = this;
			var recordsCountBtn = thisInstance.getContainer().find('.recordCount');
			recordsCountBtn.on('click', function () {
				var url = recordsCountBtn.data('url');
				AppConnector.request(url).done(function (response) {
					recordsCountBtn.find('.count').html(response.result.totalCount);
					recordsCountBtn.find('.fas').addClass('d-none').attr('aria-hidden', true);
					recordsCountBtn.find('a').removeClass('d-none').attr('aria-hidden', false);
				});
			});
		},
		/**
		 * Load scrollbar
		 */
		loadScrollbar: function loadScrollbar() {
			let container = this.getContainerContent();
			if (!container.length) {
				return;
			}
			const widget = container.closest('.dashboardWidget');
			const content = widget.find('.dashboardWidgetContent');
			const footer = widget.find('.dashboardWidgetFooter');
			let adjustedHeight = widget.innerHeight() - widget.find('.dashboardWidgetHeader').outerHeight();
			if (footer.length) {
				adjustedHeight -= footer.outerHeight();
			}
			if (!content.length) {
				return;
			}
			content.css('height', adjustedHeight + 'px');
			content.css('max-height', adjustedHeight + 'px');
			if (typeof this.scrollbar !== 'undefined') {
				this.scrollbar.update();
			} else {
				this.scrollbar = app.showNewScrollbar(content, {
					wheelPropagation: true
				});
			}
		},
		restrictContentDrag: function restrictContentDrag() {
			this.getContainer().on('mousedown.draggable', function (e) {
				var element = jQuery(e.target);
				var isHeaderElement = element.closest('.dashboardWidgetHeader').length > 0 ? true : false;
				if (isHeaderElement) {
					return;
				}
				//Stop the event propagation so that drag will not start for contents
				e.stopPropagation();
			});
		},

		positionNoDataMsg: function positionNoDataMsg() {
			var container = this.getContainer();
			var widgetContentsContainer = container.find('.dashboardWidgetContent');
			var noDataMsgHolder = widgetContentsContainer.find('.noDataMsg');
			noDataMsgHolder.position({
				my: 'center center',
				at: 'center center',
				of: widgetContentsContainer
			});
		},

		//Place holdet can be extended by child classes and can use this to handle the post load
		postLoadWidget: function postLoadWidget() {
			if (this.isEmptyData()) {
				this.positionNoDataMsg();
			}
			this.registerFilter();
			this.registerFilterChangeEvent();
			this.restrictContentDrag();
			this.registerWidgetSwitch();
			this.registerChangeSorting();
			this.registerLoadMore();
			this.registerHeaderButtons();
			this.loadScrollbar();
			this.registerResize();
		},
		registerResize: function resize() {
			let container = this.getContainerContent();
			if (!container.length) {
				return false;
			}

			new ResizeObserver(() => {
				this.loadScrollbar();
			}).observe(container.get(0));
		},
		postRefreshWidget: function postRefreshWidget() {
			this.loadScrollbar();
			if (this.isEmptyData()) {
				this.positionNoDataMsg();
			}
			this.registerLoadMore();
		},
		setSortingButton: function setSortingButton(currentElement) {
			if (currentElement.length) {
				let container = this.getContainer(),
					drefresh = container.find('.js-widget-refresh'),
					url = drefresh.data('url');
				url = url.replace('&sortorder=desc', '');
				url = url.replace('&sortorder=asc', '');
				url += '&sortorder=';
				let sort = currentElement.data('sort'),
					sortorder = 'desc',
					icon = 'fa-sort-amount-down',
					iconBase = 'fa-sort-amount-up';
				if (sort == 'desc') {
					sortorder = 'asc';
					icon = 'fa-sort-amount-up';
					iconBase = 'fa-sort-amount-down';
				}
				currentElement.data('sort', sortorder);
				currentElement.attr('title', currentElement.data(sortorder));
				currentElement.attr('alt', currentElement.data(sortorder));
				url += sortorder;
				currentElement.find('.fas').removeClass(iconBase).addClass(icon);
				drefresh.data('url', url);
			}
		},

		printImage(imgEl, title, width, height) {
			const print = window.open('', 'PRINT', 'height=' + height + ',width=' + width);
			print.document.write('<html><head><title>' + title + '</title>');
			print.document.write('</head><body >');
			print.document.write($('<div>').append(imgEl).html());
			print.document.write('</body></html>');
			print.document.close(); // necessary for IE >= 10
			print.focus(); // necessary for IE >= 10
			setTimeout(function () {
				print.print();
				print.close();
			}, 1000);
		},
		registerHeaderButtons: function registerHeaderButtons() {
			const container = this.getContainer();
			container.find('.js-widget-quick-create').on('click', function (e) {
				App.Components.QuickCreate.createRecord($(this).data('module-name'));
			});
		},
		registerChangeSorting: function registerChangeSorting() {
			var thisInstance = this;
			var container = this.getContainer();
			thisInstance.setSortingButton(container.find('.changeRecordSort'));
			container.find('.changeRecordSort').on('click', function (e) {
				var drefresh = container.find('.js-widget-refresh');
				thisInstance.setSortingButton(jQuery(e.currentTarget));
				drefresh.click();
			});
		},
		registerWidgetSwitch: function registerWidgetSwitch() {
			var thisInstance = this;
			var switchButtons = this.getContainer().find('.dashboardWidgetHeader .js-switch--calculations');
			thisInstance.setUrlSwitch(switchButtons);
			switchButtons.on('change', (e) => {
				var currentElement = $(e.currentTarget);
				var dashboardWidgetHeader = currentElement.closest('.dashboardWidgetHeader');
				var drefresh = dashboardWidgetHeader.find('.js-widget-refresh');
				thisInstance.setUrlSwitch(currentElement).done(function (data) {
					if (data) {
						drefresh.click();
					}
				});
			});
		},
		setUrlSwitch: function setUrlSwitch(switchButtons) {
			var aDeferred = jQuery.Deferred();
			switchButtons.each(function (index, e) {
				var currentElement = jQuery(e);
				var dashboardWidgetHeader = currentElement.closest('.dashboardWidgetHeader');
				var drefresh = dashboardWidgetHeader.find('.js-widget-refresh');
				var url = drefresh.data('url');
				var urlparams = currentElement.data('urlparams');
				if (urlparams !== '') {
					var switchUrl = currentElement.data('url-value');
					url = url.replace('&' + urlparams + '=' + switchUrl, '');
					url += '&' + urlparams + '=' + switchUrl;
					drefresh.data('url', url);
					aDeferred.resolve(true);
				} else {
					aDeferred.reject();
				}
			});
			return aDeferred.promise();
		},
		getFilterData: function getFilterData() {
			return {};
		},
		/**
		 * Refresh widget
		 * @returns {undefined}
		 */
		refreshWidget: function refreshWidget() {
			let thisInstance = this;
			let parent = this.getContainer();
			let element = parent.find('.js-widget-refresh');
			let url = element.data('url');
			let contentContainer = parent.find('.dashboardWidgetContent');
			let params = url;
			let widgetFilters = parent.find('.widgetFilter');
			if (widgetFilters.length > 0) {
				params = {};
				params.url = url;
				params.data = {};
				widgetFilters.each((index, domElement) => {
					let widgetFilter = $(domElement);
					let filterName = widgetFilter.attr('name');
					if ('checkbox' === widgetFilter.attr('type')) {
						params.data[filterName] = widgetFilter.is(':checked');
					} else {
						params.data[filterName] = widgetFilter.val();
					}
				});
			}

			let additionalWidgetFilters = parent.find('.js-chartFilter__additional-filter-field');
			if (additionalWidgetFilters.length > 0) {
				params = {};
				params.url = url;
				params.data = {};
				additionalWidgetFilters.each((index, domElement) => {
					let widgetFilter = jQuery(domElement);
					let filterName = widgetFilter.attr('name');
					let arr = false;
					if (filterName.substr(-2) === '[]') {
						arr = true;
						filterName = filterName.substr(0, filterName.length - 2);
						if (!Array.isArray(params.data[filterName])) {
							params.data[filterName] = [];
						}
					}
					if ('checkbox' === widgetFilter.attr('type')) {
						if (arr) {
							params.data[filterName].push(widgetFilter.is(':checked'));
						} else {
							params.data[filterName] = widgetFilter.is(':checked');
						}
					} else {
						if (arr) {
							params.data[filterName].push(widgetFilter.val());
						} else {
							params.data[filterName] = widgetFilter.val();
						}
					}
				});
			}
			let refreshContainer = this.getContainerContent();
			let refreshContainerFooter = parent.find('.dashboardWidgetFooter');
			this.clear();
			refreshContainer.progressIndicator();
			if (
				this.paramCache &&
				(additionalWidgetFilters.length || widgetFilters.length || parent.find('.listSearchContributor'))
			) {
				thisInstance.setFilterToCache(params.url ? params.url : params, params.data ? params.data : {});
			}
			AppConnector.request(params)
				.done((data) => {
					data = $(data);
					let footer = data.filter('.widgetFooterContent');
					refreshContainer.progressIndicator({
						mode: 'hide'
					});
					if (footer.length) {
						footer = footer.clone(true, true);
						refreshContainerFooter.html(footer);
						data.each(function (n, e) {
							if (jQuery(this).hasClass('widgetFooterContent')) {
								data.splice(n, 1);
							}
						});
					}
					contentContainer.html(data).trigger(YetiForce_Widget_Js.widgetPostRefereshEvent);
				})
				.fail(() => {
					refreshContainer.progressIndicator({
						mode: 'hide'
					});
				});
		},
		clear: function clear() {
			this.getContainerContent().html('');
			this.getContainer().find('.dashboardWidgetFooter').html('');
		},
		registerFilter: function registerFilter() {
			const container = this.getContainer();
			const search = container.find('.listSearchContributor');
			const refreshBtn = container.find('.js-widget-refresh');
			const originalUrl = refreshBtn.data('url');
			const selects = container.find('.select2noactive');
			search.css('width', '100%');
			search.parent().addClass('w-100');
			search.each((index, element) => {
				const fieldInfo = $(element).data('fieldinfo');
				$(element).attr('placeholder', fieldInfo.label).data('placeholder', fieldInfo.label);
			});
			App.Fields.Picklist.changeSelectElementView(selects, 'select2', {
				containerCssClass: 'form-control'
			});
			App.Fields.Date.register(container);
			App.Fields.Date.registerRange(container);
			App.Fields.DateTime.register(container);
			search.on('change apply.daterangepicker', (e) => {
				let searchParams = [];
				container.find('.listSearchContributor').each((index, domElement) => {
					let searchInfo = [];
					const searchContributorElement = $(domElement);
					const fieldInfo = searchContributorElement.data('fieldinfo');
					const fieldName = searchContributorElement.attr('name');
					let searchValue = searchContributorElement.val();
					if (typeof searchValue === 'object') {
						if (searchValue == null) {
							searchValue = '';
						} else {
							searchValue = searchValue.join('##');
						}
					} else if ($.inArray(fieldInfo.type, ['tree']) >= 0) {
						searchValue = searchValue.replace(/,/g, '##');
					}
					searchValue = searchValue.trim();
					if (searchValue.length <= 0) {
						//continue
						return true;
					}
					let searchOperator = 'a';
					if (fieldInfo.hasOwnProperty('searchOperator')) {
						searchOperator = fieldInfo.searchOperator;
					} else if (
						jQuery.inArray(fieldInfo.type, [
							'modules',
							'time',
							'userCreator',
							'owner',
							'picklist',
							'tree',
							'boolean',
							'fileLocationType',
							'userRole',
							'multiReferenceValue',
							'currencyList'
						]) >= 0
					) {
						searchOperator = 'e';
					} else if (fieldInfo.type === 'date' || fieldInfo.type === 'datetime') {
						searchOperator = 'bw';
					} else if (fieldInfo.type === 'multipicklist' || fieldInfo.type === 'categoryMultipicklist') {
						searchOperator = 'c';
					}
					searchInfo.push(fieldName);
					searchInfo.push(searchOperator);
					searchInfo.push(searchValue);
					if ($.inArray(fieldInfo.type, ['tree', 'categoryMultipicklist']) != -1) {
						let searchInSubcategories = $(
							'.listViewHeaders .searchInSubcategories[data-columnname="' + fieldName + '"]'
						).prop('checked');
						if (searchInSubcategories) {
							searchOperator = 'ch';
						}
					}
					searchParams.push(searchInfo);
				});
				let url = originalUrl + '&search_params=' + JSON.stringify([searchParams]);
				refreshBtn.data('url', url);
				refreshBtn.trigger('click');
			});
		},
		registerFilterChangeEvent: function registerFilterChangeEvent() {
			let container = this.getContainer();
			container.on('change', '.widgetFilter', (e) => {
				container.find('.js-widget-refresh').trigger('click');
			});
			if (container.find('.widgetFilterByField').length) {
				App.Fields.Picklist.showSelect2ElementView(container.find('.select2noactive'));
				this.getContainer().on('change', '.widgetFilterByField .form-control', (e) => {
					container.find('.js-widget-refresh').trigger('click');
				});
			}
		},
		registerWidgetPostLoadEvent: function registerWidgetPostLoadEvent(container) {
			var thisInstance = this;
			container.on(YetiForce_Widget_Js.widgetPostLoadEvent, function (e) {
				thisInstance.postLoadWidget();
			});
		},
		registerWidgetPostRefreshEvent: function registerWidgetPostRefreshEvent(container) {
			var thisInstance = this;
			container.off(YetiForce_Widget_Js.widgetPostRefereshEvent);
			container.on(YetiForce_Widget_Js.widgetPostRefereshEvent, function (e) {
				thisInstance.postRefreshWidget();
			});
		},

		registerLoadMore: function registerLoadMore() {
			var thisInstance = this;
			var parent = thisInstance.getContainer();
			var contentContainer = parent.find('.dashboardWidgetContent');
			contentContainer.off('click', '.showMoreHistory');
			contentContainer.on('click', '.showMoreHistory', function (e) {
				var element = jQuery(e.currentTarget);
				element.hide();
				var parent = jQuery(e.delegateTarget).closest('.dashboardWidget');
				jQuery(parent).find('.slimScrollDiv').css('overflow', 'visible');
				var url = element.data('url') + '&content=true';
				let additionalFilter = parent.find('.widgetFilter');
				if (additionalFilter.length > 0) {
					additionalFilter.each(function () {
						url += '&' + $(this).attr('name') + '=' + $(this).val();
					});
				}
				if (parent.find('.changeRecordSort').length > 0) {
					url += '&sortorder=' + parent.find('.changeRecordSort').data('sort');
				}
				contentContainer.progressIndicator();
				AppConnector.request(url).done(function (data) {
					contentContainer.progressIndicator({
						mode: 'hide'
					});
					jQuery(parent).find('.dashboardWidgetContent').append(data);
					element.parent().remove();
					thisInstance.postRefreshWidget();
				});
			});
		},
		setFilterToCache: function setFilterToCache(url, data) {
			var paramCache = url;
			var container = this.getContainer();
			paramCache = paramCache.replace('&content=', '&notcontent=');
			for (var i in data) {
				if (typeof data[i] == 'object') {
					data[i] = JSON.stringify(data[i]);
				}
				paramCache += '&' + i + '=' + data[i];
			}
			var userId = CONFIG.userId;
			var name = container.attr('id');
			app.cacheSet(name + '_' + userId, paramCache);
		},
		registerCache: function registerCache(container) {
			if (container.data('cache') == 1) {
				this.paramCache = true;
			}
		}
	}
);
Vtiger_Widget_Js('YetiForce_Widget_Js', {}, {});

YetiForce_Widget_Js(
	'YetiForce_Calendar_Widget_Js',
	{},
	{
		calendarView: false,
		calendarCreateView: false,
		fullCalendar: false,
		/**
		 * Register calendar
		 */
		registerCalendar: function () {
			const self = this,
				container = this.getContainer();
			//Default time format
			let userTimeFormat = CONFIG.hourFormat;
			if (userTimeFormat == 24) {
				userTimeFormat = {
					hour: '2-digit',
					minute: '2-digit',
					hour12: false,
					meridiem: false
				};
			} else {
				userTimeFormat = {
					hour: 'numeric',
					minute: '2-digit',
					meridiem: 'short'
				};
			}
			//Default first hour of the day
			let defaultFirstHour = app.getMainParams('startHour');
			let explodedTime = defaultFirstHour.split(':');
			defaultFirstHour = explodedTime['0'];
			let defaultDate = app.getMainParams('defaultDate');
			if (this.paramCache && defaultDate != moment().format('YYYY-MM-DD')) {
				defaultDate = moment(defaultDate).format('D') == 1 ? moment(defaultDate) : moment(defaultDate).add(1, 'M');
			}
			container.find('.js-widget-quick-create').on('click', function (e) {
				App.Components.QuickCreate.createRecord($(this).data('module-name'));
			});
			this.fullCalendar = new FullCalendar.Calendar(this.getCalendarView().get(0), {
				headerToolbar: { left: ' ', center: 'prev title next', right: ' ' },
				initialDate: defaultDate,
				eventTimeFormat: userTimeFormat,
				slotLabelFormat: userTimeFormat,
				scrollTime: defaultFirstHour,
				firstDay: CONFIG.firstDayOfWeekNo,
				initialView: 'dayGridMonth',
				editable: false,
				slotDuration: 15,
				defaultTimedEventDuration: '01:00:00',
				dayMaxEventRows: false,
				allDaySlot: false,
				moreLinkContent: app.vtranslate('JS_MORE'),
				allDayText: app.vtranslate('JS_ALL_DAY'),
				noEventsText: app.vtranslate('JS_NO_RECORDS'),
				viewHint: '$0',
				contentHeight: 'auto',
				buttonText: {
					today: '',
					year: app.vtranslate('JS_YEAR'),
					week: app.vtranslate('JS_WEEK'),
					month: app.vtranslate('JS_MONTH'),
					day: app.vtranslate('JS_DAY'),
					dayGridMonth: app.vtranslate('JS_MONTH'),
					dayGridWeek: app.vtranslate('JS_WEEK'),
					listWeek: app.vtranslate('JS_WEEK'),
					dayGridDay: app.vtranslate('JS_DAY'),
					timeGridDay: app.vtranslate('JS_DAY')
				},
				navLinkHint: (_dateStr, zonedDate) => {
					return App.Fields.Date.dateToUserFormat(zonedDate);
				},
				dayHeaderContent: (arg) => {
					return App.Fields.Date.daysTranslated[arg.date.getUTCDay()];
				},
				titleFormat: (args) => {
					return Calendar_Js.monthFormat[CONFIG.dateFormat]
						.replace('YYYY', args.date['year'])
						.replace('MMMM', App.Fields.Date.fullMonthsTranslated[args.date['month']]);
				},
				dateClick: (args) => {
					let date = moment(args.date).format(CONFIG.dateFormat.toUpperCase());
					App.Components.QuickCreate.createRecord('Calendar', {
						noCache: true,
						data: {
							date_start: date,
							due_date: date
						},
						callbackFunction: function () {
							self.getCalendarView().closest('.dashboardWidget').find('.js-widget-refresh').trigger('click');
						}
					});
				},
				eventClick: function (info) {
					info.jsEvent.preventDefault();
					let url = $(info.el).attr('href');
					if (url !== undefined) {
						let params = [];
						url += '&viewname=' + container.find('select.widgetFilter.customFilter').val();
						const owner = container.find('.widgetFilter.owner option:selected');
						if (owner.val() != 'all') {
							params.push(['assigned_user_id', 'e', owner.val()]);
						}
						if (container.find('.widgetFilterSwitch').length > 0) {
							const status = container.find('.widgetFilterSwitch').data();
							params.push(['activitystatus', 'e', status[container.find('.widgetFilterSwitch').val()]]);
						}
						const date = App.Fields.Date.dateToUserFormat(info.event.start);
						params.push(
							['activitytype', 'e', info.event.extendedProps.activityType],
							['date_start', 'bw', date + ' 00:00:00,' + date + ' 23:59:59']
						);
						url += '&search_params=' + encodeURIComponent(JSON.stringify([params]));
						window.location.href = `${url}`;
					}
				}
			});
			this.fullCalendar.render();
			this.getCalendarView()
				.find('td.fc-day-top')
				.on('mouseenter', function () {
					jQuery('<span class="plus pull-left fas fa-plus"></span>').prependTo($(this));
				})
				.on('mouseleave', function () {
					$(this).find('.plus').remove();
				});
			const switchBtn = container.find('.js-switch--calendar');
			switchBtn.on('change', (e) => {
				const currentTarget = $(e.currentTarget);
				if (typeof currentTarget.data('on-text') !== 'undefined') container.find('.widgetFilterSwitch').val('current');
				else if (typeof currentTarget.data('off-text') !== 'undefined')
					container.find('.widgetFilterSwitch').val('history');
				this.refreshWidget();
			});
		},
		/**
		 * Load calendar data
		 */
		loadCalendarData: function () {
			this.fullCalendar.removeAllEvents();
			const start_date = App.Fields.Date.dateToUserFormat(this.fullCalendar.view.activeStart),
				end_date = App.Fields.Date.dateToUserFormat(this.fullCalendar.view.activeEnd),
				parent = this.getContainer();
			let user = parent.find('.owner').val();
			if (user == 'all') {
				user = '';
			}
			let params = {
				module: 'Calendar',
				action: 'Calendar',
				mode: 'getEvents',
				start: start_date,
				end: end_date,
				user: user,
				widget: true
			};
			if (parent.find('.customFilter').length > 0) {
				params.customFilter = parent.find('.customFilter').val();
			}
			let widgetFilterSwitch = parent.find('.widgetFilterSwitch');
			if (widgetFilterSwitch.length > 0) {
				params.time = widgetFilterSwitch.val();
				let defaultFilter = widgetFilterSwitch.data('default-filter');
				if (defaultFilter !== undefined) {
					params.customFilter = defaultFilter;
				}
			}
			if (this.paramCache) {
				this.setFilterToCache(this.getContainer().find('.js-widget-refresh').data('url'), {
					owner: user,
					customFilter: params.customFilter,
					start: start_date
				});
			}
			AppConnector.request(params).done((events) => {
				this.fullCalendar.addEventSource(events.result);
			});
		},
		/**
		 * Get calendar view container
		 * @returns {jQuery}
		 */
		getCalendarView: function () {
			if (this.calendarView === false) {
				this.calendarView = this.getContainer().find('.js-calendar__container');
			}
			return this.calendarView;
		},
		/**
		 * Update month name
		 */
		getMonthName: function () {
			let month = this.getCalendarView().find('.fc-toolbar h2').text();
			if (month) {
				this.getContainer()
					.find('.headerCalendar .month')
					.html('<h3>' + month + '</h3>');
			}
		},
		/**
		 * Register change view
		 */
		registerChangeView: function () {
			let thisInstance = this;
			let container = this.getContainer();
			container.find('.fc-toolbar').addClass('d-none');
			let month = container.find('.fc-toolbar h2').text();
			if (month) {
				container
					.find('.headerCalendar')
					.removeClass('d-none')
					.find('.month')
					.append('<h3>' + month + '</h3>');
				let button = container.find('.headerCalendar button');
				button.each(function () {
					let tag = jQuery(this).data('type');
					jQuery(this).on('click', function () {
						thisInstance
							.getCalendarView()
							.find('.fc-toolbar .' + tag)
							.trigger('click');
						thisInstance.loadCalendarData();
						thisInstance.getMonthName();
					});
				});
			}
		},
		/** @inheritdoc */
		loadScrollbar: function loadScrollbar() {
			if (this.fullCalendar) {
				this.fullCalendar.updateSize();
			}
			this._super();
		},
		/** @inheritdoc */
		postLoadWidget: function () {
			this.registerCalendar();
			this.loadCalendarData(true);
			this.registerChangeView();
			this.registerFilterChangeEvent();
			this.registerResize();
		},
		/** @inheritdoc */
		refreshWidget: function () {
			let thisInstance = this;
			let refreshContainer = this.getContainer().find('.dashboardWidgetContent');
			refreshContainer.progressIndicator();
			thisInstance.loadCalendarData();
			refreshContainer.progressIndicator({
				mode: 'hide'
			});
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_CalendarActivities_Widget_Js',
	{},
	{
		modalView: false,
		postLoadWidget: function () {
			this._super();
			this.registerActivityChange();
			this.registerListViewButton();
		},
		postRefreshWidget: function () {
			this._super();
			this.registerActivityChange();
		},
		registerActivityChange: function () {
			var thisInstance = this;
			var refreshContainer = this.getContainer().find('.dashboardWidgetContent');
			refreshContainer.find('.changeActivity').on('click', function (e) {
				if (jQuery(e.target).is('a') || thisInstance.modalView) {
					return;
				}
				var url = jQuery(this).data('url');
				if (typeof url !== 'undefined') {
					var callbackFunction = function () {
						thisInstance.modalView = false;
					};
					thisInstance.modalView = true;
					app.showModalWindow(null, url, callbackFunction);
				}
			});
		},

		registerListViewButton: function () {
			const thisInstance = this,
				container = thisInstance.getContainer();
			container.find('.goToListView').on('click', function () {
				let status;
				let activitiesStatus = container.data('name');
				if (activitiesStatus === 'OverdueActivities') {
					status = 'PLL_OVERDUE';
				} else if (activitiesStatus === 'CalendarActivities') {
					status = 'PLL_IN_REALIZATION##PLL_PLANNED';
				} else {
					status = 'PLL_IN_REALIZATION##PLL_PLANNED##PLL_OVERDUE';
				}
				let url = 'index.php?module=Calendar&view=List&viewname=All';
				url += '&search_params=[[';
				let owner = container.find('.widgetFilter.owner option:selected');
				if (owner.val() !== 'all') {
					url += '["assigned_user_id","e","' + owner.val() + '"],';
				}
				url += '["activitystatus","e","' + encodeURIComponent(status) + '"]]]';
				window.location.href = url;
			});
		}
	}
);
YetiForce_CalendarActivities_Widget_Js('YetiForce_CreatedNotMineActivities_Widget_Js', {}, {});
YetiForce_CalendarActivities_Widget_Js('YetiForce_CreatedNotMineOverdueActivities_Widget_Js', {}, {});
YetiForce_CalendarActivities_Widget_Js('YetiForce_OverDueActivities_Widget_Js', {}, {});
YetiForce_CalendarActivities_Widget_Js('YetiForce_OverdueActivities_Widget_Js', {}, {});
YetiForce_Widget_Js(
	'YetiForce_ProductsSoldToRenew_Widget_Js',
	{},
	{
		modalView: false,
		postLoadWidget: function () {
			this._super();
			this.registerAction();
			this.registerListViewButton();
		},
		postRefreshWidget: function () {
			this._super();
			this.registerAction();
		},
		registerAction: function () {
			var thisInstance = this;
			var refreshContainer = this.getContainer().find('.dashboardWidgetContent');
			refreshContainer.find('.rowAction').on('click', function (e) {
				if (jQuery(e.target).is('a') || thisInstance.modalView) {
					return;
				}
				var url = jQuery(this).data('url');
				if (typeof url !== 'undefined') {
					var callbackFunction = function () {
						thisInstance.modalView = false;
					};
					thisInstance.modalView = true;
					app.showModalWindow(null, url, callbackFunction);
				}
			});
		},
		registerListViewButton: function () {
			var thisInstance = this;
			var container = thisInstance.getContainer();
			container.on('click', '.goToListView', function () {
				var url = jQuery(this).data('url');
				var orderBy = container.find('.orderby');
				var sortOrder = container.find('.changeRecordSort');
				if (orderBy.length) {
					url += '&orderby=' + orderBy.val();
				}
				if (sortOrder.length) {
					url += '&sortorder=' + sortOrder.data('sort').toUpperCase();
				}
				window.location.href = url;
			});
		}
	}
);
YetiForce_ProductsSoldToRenew_Widget_Js('YetiForce_ServicesSoldToRenew_Widget_Js', {}, {});

YetiForce_Widget_Js(
	'YetiForce_History_Widget_Js',
	{},
	{
		postLoadWidget: function () {
			this._super();
			this.registerLoadMore();
		},
		postRefreshWidget: function () {
			this._super();
			this.registerLoadMore();
		},
		registerLoadMore: function () {
			var thisInstance = this;
			var parent = thisInstance.getContainer();
			var contentContainer = parent.find('.dashboardWidgetContent');
			var loadMoreHandler = contentContainer.find('.load-more');
			loadMoreHandler.on('click', function () {
				var parent = thisInstance.getContainer();
				var element = parent.find('.js-widget-refresh');
				var url = element.data('url');
				var params = url;
				var widgetFilters = parent.find('.widgetFilter');
				if (widgetFilters.length > 0) {
					params = {
						url: url,
						data: {}
					};
					widgetFilters.each(function (index, domElement) {
						var widgetFilter = jQuery(domElement);
						var filterName = widgetFilter.attr('name');
						var filterValue = widgetFilter.val();
						params.data[filterName] = filterValue;
					});
				}

				var filterData = thisInstance.getFilterData();
				if (!jQuery.isEmptyObject(filterData)) {
					if (typeof params == 'string') {
						params = {
							url: url,
							data: {}
						};
					}
					params.data = jQuery.extend(params.data, thisInstance.getFilterData());
				}

				// Next page.
				params.data['page'] = loadMoreHandler.data('nextpage');
				var refreshContainer = parent.find('.dashboardWidgetContent');
				refreshContainer.progressIndicator();
				AppConnector.request(params)
					.done(function (data) {
						refreshContainer.progressIndicator({
							mode: 'hide'
						});
						loadMoreHandler.replaceWith(data);
						thisInstance.registerLoadMore();
					})
					.fail(function () {
						refreshContainer.progressIndicator({
							mode: 'hide'
						});
					});
			});
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_MiniList_Widget_Js',
	{},
	{
		postLoadWidget: function () {
			this.restrictContentDrag();
			this.registerFilter();
			this.registerFilterChangeEvent();
			this.registerRecordsCount();
			this.registerResize();
		},
		postRefreshWidget: function () {
			this.registerRecordsCount();
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_UpcomingEvents_Widget_Js',
	{},
	{
		postLoadWidget: function () {
			this.registerFilterChangeEvent();
			this.registerResize();
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_Notebook_Widget_Js',
	{},
	{
		// Override widget specific functions.
		postLoadWidget: function () {
			this.registerNotebookEvents();
		},
		registerNotebookEvents: function () {
			this.container.on('click', '.dashboard_notebookWidget_edit', () => {
				this.editNotebookContent();
			});
			this.container.on('click', '.dashboard_notebookWidget_save', () => {
				this.saveNotebookContent();
			});
		},
		editNotebookContent: function () {
			$('.dashboard_notebookWidget_view', this.container).hide();
			let editContainer = $('.dashboard_notebookWidget_text', this.container).show();
			let editTextArea = editContainer.find('textarea');
			editTextArea.css(
				'height',
				this.container.innerHeight() -
					this.container.find('.dashboardWidgetHeader').innerHeight() -
					editTextArea.prev().innerHeight() -
					16
			);
		},
		saveNotebookContent: function () {
			let textarea = $('.dashboard_notebookWidget_textarea', this.container),
				url = this.container.data('url'),
				params = url + '&content=true&mode=save&contents=' + encodeURIComponent(textarea.val()),
				refreshContainer = this.container.find('.dashboardWidgetContent');
			refreshContainer.progressIndicator();
			AppConnector.request(params).done((data) => {
				refreshContainer.progressIndicator({
					mode: 'hide'
				});
				$('.dashboardWidgetContent', this.container).html(data);
			});
		}
	}
);

YetiForce_Widget_Js(
	'YetiForce_Multifilter_Widget_Js',
	{},
	{
		multifilterControlsView: false,
		multifilterContentView: false,
		multifilterSettingsView: false,
		registerSubmit() {
			this.getContainer()
				.find('.js-multifilter-save')
				.on('click', (e) => {
					let progressIndicatorElement = $.progressIndicator({
						position: 'html',
						blockInfo: {
							enabled: true
						}
					});
					let widgetId = this.getMultifilterControls().attr('data-widgetid');
					let actions = this.getContainer().find('.js-select').val();
					AppConnector.request({
						action: 'Widget',
						mode: 'updateWidgetConfig',
						module: app.getModuleName(),
						widgetid: widgetId,
						widgetData: { customMultiFilter: actions }
					}).done((_) => {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						this.refreshWidget();
					});
				});
		},
		loadData() {
			let widgetId = this.getMultifilterControls().attr('data-widgetid'),
				multifilterIds = this.getMultifilterSettings().find('.js-select option:selected'),
				params = [];
			this.getMultifilterContent().html('');
			$.each(multifilterIds, (i, e) => {
				let element = $(e);
				let existFilter = this.getMultifilterContent().find('[data-id="' + element.val() + '"]');
				if (0 < existFilter.length) {
					return true;
				}
				params.push({
					module: element.data('module'),
					modulename: element.data('module'),
					view: 'ShowWidget',
					name: 'Multifilter',
					content: true,
					widget: true,
					widgetid: widgetId,
					filterid: element.val()
				});
			});
			this.loadListData(params);
		},
		loadListData(params) {
			if (!params.length) {
				return false;
			}
			const self = this;
			let multiFilterContent = self.getMultifilterContent();
			let param = params.shift();
			AppConnector.request(param)
				.done(function (data) {
					if (
						self
							.getMultifilterSettings()
							.find('option[value="' + param.filterid + '"]')
							.is(':selected') &&
						!multiFilterContent.find('.detailViewTable[data-id="' + param.filterid + '"]').length
					) {
						self.registerRecordsCount(multiFilterContent.append(data).children('div:last-child'));
						self.registerShowHideBlocks();
						self.loadListData(params);
					}
				})
				.fail(function (error) {
					app.errorLog(error);
					self.loadListData(params);
				});
		},
		registerShowHideModuleSettings() {
			this.getMultifilterControls()
				.find('.js-widget-settings')
				.on('click', () => {
					this.getMultifilterSettings().toggleClass('d-none');
				});
		},
		registerShowHideBlocks() {
			let detailContentsHolder = this.getMultifilterContent();
			detailContentsHolder.find('.blockHeader').off('click');
			detailContentsHolder.find('.blockHeader').click(function () {
				let currentTarget = $(this).find('.js-block-toggle').not('.d-none'),
					closestBlock = currentTarget.closest('.js-toggle-panel'),
					bodyContents = closestBlock.find('.blockContent'),
					data = currentTarget.data();
				let hideHandler = function () {
					bodyContents.addClass('d-none');
				};
				let showHandler = function () {
					bodyContents.removeClass('d-none');
				};
				if ('show' == data.mode) {
					hideHandler();
					currentTarget.addClass('d-none');
					closestBlock.find('[data-mode="hide"]').removeClass('d-none');
				} else {
					showHandler();
					currentTarget.addClass('d-none');
					closestBlock.find("[data-mode='show']").removeClass('d-none');
				}
			});
		},
		registerRecordsCount(container) {
			let url = container.data('url');
			AppConnector.request(url).done(function (data) {
				container.find('.js-count').html(data.result.totalCount);
			});
		},
		getMultifilterControls() {
			if (this.multifilterControlsView == false) {
				this.multifilterControlsView = this.getContainer().find('.js-multifilterControls');
			}
			return this.multifilterControlsView;
		},
		getMultifilterContent() {
			if (this.multifilterContentView == false) {
				this.multifilterContentView = this.getContainer().find('.js-multifilterContent');
			}
			return this.multifilterContentView;
		},
		getMultifilterSettings() {
			if (this.multifilterSettingsView == false) {
				this.multifilterSettingsView = this.getContainer().find('.js-settings-widget');
			}
			return this.multifilterSettingsView;
		},
		postLoadWidget() {
			this.loadData();
			this.registerSubmit();
			this.registerShowHideModuleSettings();
		},
		refreshWidget() {
			this.loadData();
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_UpcomingProjectTasks_Widget_Js',
	{},
	{
		postLoadWidget: function () {
			this._super();
			this.registerListViewButton();
		},
		registerListViewButton: function () {
			const container = this.getContainer();
			container.find('.goToListView').on('click', function () {
				let url = 'index.php?module=ProjectTask&view=List&viewname=All';
				url += '&search_params=[[';
				let owner = container.find('.widgetFilter.owner option:selected');
				if (owner.val() !== 'all') {
					url += '["assigned_user_id","e","' + owner.val() + '"],';
				}
				url +=
					'["projecttaskstatus","e","' + encodeURIComponent(container.find('[name="status"]').data('value')) + '"]]]';
				app.openUrl(url);
			});
		}
	}
);
YetiForce_UpcomingProjectTasks_Widget_Js('YetiForce_CompletedProjectTasks_Widget_Js', {}, {});
YetiForce_Widget_Js(
	'YetiForce_Updates_Widget_Js',
	{},
	{
		postLoadWidget: function () {
			this._super();
			this.registerEvents();
			this.registerLoadMore();
		},
		postRefreshWidget: function () {
			this._super();
			this.registerContentEvents(this.getContainer());
			app.registerPopoverEllipsisIcon(this.getContainer().find('.js-popover-tooltip--ellipsis-icon'));
		},
		registerEvents: function () {
			const container = this.getContainer();
			const self = this;
			let modalContainer = container.find('.js-update-widget-modal');
			app.registerPopoverEllipsisIcon(container.find('.js-popover-tooltip--ellipsis-icon'));
			container.find('.js-update-widget-button').on('click', function () {
				let modal = modalContainer.clone(true);
				let widgetData = JSON.parse(container.find('.js-widget-data').val());
				if (widgetData) {
					for (let i in widgetData.actions) {
						modal.find('.js-tracker-action[value="' + widgetData.actions[i] + '"]').prop('checked', true);
					}
					modal.find('[name="owner"]').val(widgetData.owner);
					modal.find('[name="historyOwner"]').val(widgetData.historyOwner);
				}
				App.Fields.Picklist.showSelect2ElementView(modal.find('select'));
				app.showModalWindow(modal, function (data) {
					self.registerSubmit(data);
				});
			});
			this.registerContentEvents(container);
		},
		registerSubmit(data) {
			data.find('.js-modal__save').on('click', (e) => {
				let progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				let actions = [];
				$.each(data.find('.js-tracker-action:checked'), function () {
					actions.push($(this).val());
				});
				AppConnector.request({
					action: 'Widget',
					mode: 'saveUpdatesWidgetConfig',
					module: 'ModTracker',
					widgetId: this.getContainer().find('.js-widget-id').val(),
					trackerActions: actions,
					owner: data.find('[name="owner"]').val(),
					historyOwner: data.find('[name="historyOwner"]').val()
				}).done((data) => {
					progressIndicatorElement.progressIndicator({ mode: 'hide' });
					this.refreshWidget();
					app.hideModalWindow();
				});
			});
		},
		registerContentEvents() {
			const container = this.getContainer();
			$('.js-history-detail', container).on('click', (e) => {
				let actionId = e.currentTarget.dataset.action;
				let widgetData = JSON.parse(container.find('.js-widget-data').val());
				let progressIndicatorElement = $.progressIndicator({
					position: 'html',
					blockInfo: {
						enabled: true
					}
				});
				let params = {
					view: 'UpdatesDetail',
					module: 'ModTracker',
					widgetId: this.getContainer().find('.js-widget-id').val(),
					trackerAction: e.currentTarget.dataset.action,
					sourceModule: e.currentTarget.dataset.module,
					owner: widgetData.owner,
					historyOwner: widgetData.historyOwner,
					dateRange: container.find('[name="dateRange"]').val(),
					page: 1
				};
				AppConnector.request(params)
					.done((modal) => {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						app.showModalWindow(modal, function (data) {
							data.on('click', '.showMoreHistory', (e) => {
								AppConnector.request(e.currentTarget.dataset.url).done((result) => {
									$(e.target).parent().remove();
									data.find('.modal-body').append($(result).filter('.modal-body').get(0).childNodes);
								});
							});
						});
					})
					.fail((error) => {
						progressIndicatorElement.progressIndicator({ mode: 'hide' });
						app.errorLog(error);
					});
			});
		}
	}
);
YetiForce_Widget_Js(
	'YetiForce_TimeCounter_Widget_Js',
	{},
	{
		/** @type {number} Hours of the timer */
		hr: 0,
		/** @type {number} Timer minutes */
		min: 0,
		/** @type {number} Seconds of the timer */
		sec: 0,
		/** @type {boolean} Starting a timer */
		counter: true,
		/** @type {(string|number)} Time to start work */
		timeStart: '',
		/** @type {(string|number)} End of work time */
		timeStop: '',
		/**
		 * Show quick create form
		 */
		postLoadWidget: function () {
			this._super();
			this.registerNavigatorButtons();
		},
		/**
		 * Register events on the navigation buttons.
		 */
		registerNavigatorButtons: function () {
			const container = this.getContainer();
			let btnStart = container.find('.js-time-counter-start');
			let btnStop = container.find('.js-time-counter-stop');
			let btnReset = container.find('.js-time-counter-reset');
			let navigatorButtons = container.find('.js-navigator-buttons');
			let btnMinutes = container.find('.js-time-counter-minute');
			btnStart.on('click', () => {
				navigatorButtons.addClass('active');
				btnStart.addClass('d-none');
				btnStop.removeClass('d-none');
				btnReset.removeClass('d-none');
				btnMinutes.attr('disabled', true);
				btnMinutes.removeClass('btn-outline-success');
				btnMinutes.addClass('btn-outline-danger');
				this.startTimerCounter();
			});
			btnStop.on('click', () => {
				this.stopTimerCounter(false);
			});
			btnReset.on('click', () => {
				navigatorButtons.removeClass('active');
				btnReset.addClass('d-none');
				btnStop.addClass('d-none');
				btnStart.removeClass('d-none');
				btnMinutes.attr('disabled', false);
				btnMinutes.removeClass('btn-outline-danger');
				btnMinutes.addClass('btn-outline-success');
				this.resetTimerCounter();
			});
			if (btnMinutes.length > 1) {
				btnMinutes.on('click', (e) => {
					this.counter = false;
					let element = $(e.currentTarget);
					this.min = element.data('value');
					let dateEnd = new Date();
					let hours = (dateEnd.getHours() < 10 ? '0' : '') + dateEnd.getHours();
					let minutes = (dateEnd.getMinutes() < 10 ? '0' : '') + dateEnd.getMinutes();
					this.timeStop = hours + ':' + minutes;
					this.stopTimerCounter(true);
				});
			}
		},
		/**
		 * Time counting starts
		 */
		startTimerCounter: function () {
			let dateStart = new Date();
			let hours = (dateStart.getHours() < 10 ? '0' : '') + dateStart.getHours();
			let minutes = (dateStart.getMinutes() < 10 ? '0' : '') + dateStart.getMinutes();
			this.timeStart = hours + ':' + minutes;
			if (this.counter === true) {
				this.counter = false;
				this.timeCounter();
			}
		},
		/**
		 * Time counting ends.
		 * @param {boolean} $afterTime
		 */
		stopTimerCounter: function ($afterTime) {
			if (this.counter === false) {
				this.counter = true;
				let quickCreateParams = {};
				let customParams = {};
				if ($afterTime) {
					this.setStopAfterTime();
				} else {
					this.setStopBeforeTime();
				}
				customParams['time_start'] = this.timeStart;
				customParams['time_end'] = this.timeStop;
				quickCreateParams['data'] = customParams;
				quickCreateParams['noCache'] = true;
				App.Components.QuickCreate.createRecord('OSSTimeControl', quickCreateParams);
			}
		},
		/**
		 * Sets the end time before ending the call.
		 */
		setStopBeforeTime: function () {
			if (parseInt(this.sec) > 30 || parseInt(this.min) === 0) {
				this.min = parseInt(this.min) + 1;
			}
			let dateStart = new Date();
			dateStart.setHours(this.timeStart.split(':')[0]);
			dateStart.setMinutes(this.timeStart.split(':')[1]);
			dateStart.setHours(dateStart.getHours() + parseInt(this.hr));
			dateStart.setMinutes(dateStart.getMinutes() + parseInt(this.min));
			let hours = (dateStart.getHours() < 10 ? '0' : '') + dateStart.getHours();
			let minutes = (dateStart.getMinutes() < 10 ? '0' : '') + dateStart.getMinutes();
			this.timeStop = hours + ':' + minutes;
			this.sec = 0;
			this.min = 0;
			this.hr = 0;
		},

		/**
		 * Sets the end time after ending the call.
		 */
		setStopAfterTime: function () {
			let dateEnd = new Date();
			dateEnd.setHours(this.timeStop.split(':')[0]);
			dateEnd.setMinutes(this.timeStop.split(':')[1]);
			dateEnd.setHours(dateEnd.getHours() - parseInt(this.hr));
			dateEnd.setMinutes(dateEnd.getMinutes() - parseInt(this.min));
			let hours = (dateEnd.getHours() < 10 ? '0' : '') + dateEnd.getHours();
			let minutes = (dateEnd.getMinutes() < 10 ? '0' : '') + dateEnd.getMinutes();
			this.timeStart = hours + ':' + minutes;
			this.sec = 0;
			this.min = 0;
			this.hr = 0;
		},
		/**
		 * Resets the counting operation.
		 */
		resetTimerCounter: function () {
			if (this.counter === false) {
				this.counter = true;
				this.sec = 0;
				this.min = 0;
				this.hr = 0;
			}
			this.getContainer().find('.js-time-counter').html('00:00:00');
		},
		/**
		 * Counting time from the moment of starting work.
		 */
		timeCounter: function () {
			if (this.counter === false) {
				this.sec = parseInt(this.sec);
				this.min = parseInt(this.min);
				this.hr = parseInt(this.hr);
				this.sec = this.sec + 1;
				if (this.sec === 60) {
					this.min = this.min + 1;
					this.sec = 0;
				}
				if (this.min === 60) {
					this.hr = this.hr + 1;
					this.min = 0;
					this.sec = 0;
				}
				if (this.sec < 10 || this.sec === 0) {
					this.sec = '0' + this.sec;
				}
				if (this.min < 10 || this.min === 0) {
					this.min = '0' + this.min;
				}
				if (this.hr < 10 || this.hr === 0) {
					this.hr = '0' + this.hr;
				}
				this.getContainer()
					.find('.js-time-counter')
					.html(this.hr + ':' + this.min + ':' + this.sec);
				setTimeout((_) => {
					this.timeCounter();
				}, 1000);
			}
		}
	}
);
