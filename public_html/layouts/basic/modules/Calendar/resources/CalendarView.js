/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
/**
 *  Class representing an extended calendar.
 * @extends Calendar_Calendar_Js
 */
window.calendarLoaded = false; //Global calendar flag needed for correct loading data from history browser in year view
window.Calendar_Calendar_Js = class Calendar_Calendar_Js extends Vtiger_Calendar_Js {
	/**
	 * Go to records list
	 * @param {string} link
	 */
	static goToRecordsList(link) {
		const self = app.pageController,
			status = app.getMainParams('activityStateLabels', true),
			options = self.getDefaultParams();
		if (options['cvid']) {
			link += '&viewname=' + options['cvid'];
		} else {
			link += '&viewname=All';
		}
		let searchParams = '["activitystatus","e","' + status[app.getMainParams('showType')].join() + '"]';
		searchParams += ',["date_start","bw","' + options['start'] + ' 00:00:00,' + options['end'] + ' 23:59:59"]';
		if (options['user']) {
			searchParams += ',["assigned_user_id","e","' + options['user']['selectedIds'].join('##') + '"]';
		}
		link += '&search_params=[[' + encodeURIComponent(searchParams) + ']]';
		window.location.href = link;
	}
	/**
	 * Create calendar's options.
	 * @param {jQuery} container
	 * @param {bool} readonly
	 * @param {boolean} browserHistory
	 */
	constructor(container, readonly, browserHistory = true) {
		super(container, readonly, browserHistory);
	}
	/**
	 * Set calendar module options.
	 * @returns {{allDaySlot: boolean, dayClick: object, selectable: boolean}}
	 */
	setCalendarModuleOptions() {
		const self = this;
		return {
			allDaySlot: false,
			dateClick: (args) => {
				if (this.eventCreate == 1) {
					this.showCalendarCreateView().done((form) => {
						if (this.getSidebarView().find('.tab-pane.active').hasClass('js-right-panel-event') === false) {
							this.getSidebarView().find('.js-right-panel-event-link').trigger('click');
						}
						this.dayCallbackCreateModal(form, args);
					});
				}
			},
			selectable: false,
			eventClick: function (info) {
				info.jsEvent.preventDefault();
				const element = $(info.el);
				let link = element.attr('href');
				if (!link) {
					link = element.find('a').attr('href');
				}
				if (!self.readonly && self.eventEdit) {
					self.showSidebarEvent(link);
				} else {
					window.location.assign(link.replace('view=', 'xview=') + '&view=Detail');
				}
			}
		};
	}
	/**
	 * Show sidebar event
	 * @param {*} params
	 * @returns {Promise}
	 */
	showSidebarEvent(params) {
		const self = this,
			aDeferred = $.Deferred();
		const progressInstance = $.progressIndicator({ blockInfo: { enabled: true } });
		if (typeof params == 'number') {
			params = {
				module: this.module,
				view: 'EventForm',
				record: params
			};
		}
		AppConnector.request(params)
			.done((data) => {
				progressInstance.progressIndicator({ mode: 'hide' });
				self.openRightPanel();
				this.updateSidebar(data);
				let sidebar = self.getSidebarView();
				if (sidebar.find('form').length) {
					self.registerEditForm(sidebar);
				} else {
					app.showNewScrollbar(sidebar.find('.js-calendar__form__wrapper'), {
						suppressScrollX: true
					});
					sidebar.find('.js-activity-state .js-summary-close-edit').on('click', function () {
						self.showCalendarCreateView();
					});
					sidebar.find('.js-activity-state .editRecord').on('click', function () {
						self.showSidebarEvent($(this).data('id'));
					});
				}
				aDeferred.resolve(sidebar.find('.js-qc-form'));
			})
			.fail((error) => {
				progressInstance.progressIndicator({ mode: 'hide' });
				app.errorLog(error);
			});
		return aDeferred.promise();
	}
	/**
	 * Open sidebar right panel
	 */
	openRightPanel() {
		if (this.getSidebarView().hasClass('hideSiteBar')) {
			this.getSidebarView().find('.js-toggle-site-bar-right-button').trigger('click');
		}
	}
	/**
	 * Update sidebar
	 * @param {html} data
	 */
	updateSidebar(data) {
		this.getSidebarView().find('.js-qc-form').html(data);
		this.showRightPanelForm();
	}
	/**
	 * Show sidebar right panel form
	 */
	showRightPanelForm() {
		const calendarRightPanel = this.getSidebarView();
		if (!calendarRightPanel.find('.js-right-panel-event').hasClass('active')) {
			calendarRightPanel.find('.js-right-panel-event-link').trigger('click');
		}
		app.showNewScrollbar(calendarRightPanel.find('.js-calendar__form__wrapper'), {
			suppressScrollX: true
		});
	}
	/**
	 * Register events to EditView
	 * @param {jQuery} sideBar
	 */
	registerEditForm(sideBar) {
		const editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(sideBar.find('[name="module"]').val());
		let rightFormCreate = sideBar.find('form.js-form');
		editViewInstance.registerBasicEvents(rightFormCreate);
		rightFormCreate.validationEngine(app.validationEngineOptions);
		App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
		sideBar.find('.js-summary-close-edit').on('click', () => {
			this.showCalendarCreateView();
		});
		App.Components.QuickCreate.registerPostLoadEvents(rightFormCreate, {
			callbackFunction: this.registerAfterSubmitForm(this)
		});
		new App.Fields.Text.Editor(sideBar.find('.js-editor'), { height: '5em', toolbar: 'Min' });
	}
	/**
	 * Register actions to do after save record
	 * @param {object} self
	 * @param {object} data
	 * @returns {function}
	 */
	registerAfterSubmitForm(self) {
		let returnFunction = function (data) {
			if (data.success) {
				self.reloadCalendarData();
				self.refreshDatesRowView();
				self.getSidebarView().find('.js-qc-form').html('');
				self.showCalendarCreateView();
				window.popoverCache = {};
			}
		};
		return returnFunction;
	}
	/**
	 * Load calendar data
	 */
	loadCalendarData() {
		const self = this,
			progressInstance = $.progressIndicator({ blockInfo: { enabled: true } });
		let options = this.getDefaultParams();
		self.fullCalendar.removeAllEvents();
		self.clearFilterButton(options['user']);
		options.historyUrl = `index.php?module=${options['module']}&view=Calendar&history=true&viewType=${
			this.fullCalendar.view.type
		}&start=${options['start']}&end=${options['end']}&user=${JSON.stringify(options['user'])}&time=${
			options['time']
		}&cvid=${options['cvid']}&hiddenDays=${this.fullCalendar.getOption('hiddenDays')}`;
		let connectorMethod = window['AppConnector']['request'];
		if (this.browserHistory && window.calendarLoaded) {
			connectorMethod = window['AppConnector']['requestPjax'];
		}
		if (this.browserHistoryConfig && Object.keys(this.browserHistoryConfig).length && !window.calendarLoaded) {
			options = Object.assign(options, {
				start: this.browserHistoryConfig.start,
				end: this.browserHistoryConfig.end,
				user: this.browserHistoryConfig.user,
				time: this.browserHistoryConfig.time,
				cvid: this.browserHistoryConfig.cvid
			});
			connectorMethod = window['AppConnector']['request'];
			app.setMainParams('showType', this.browserHistoryConfig.time);
			app.setMainParams('usersId', this.browserHistoryConfig.user);
		}
		connectorMethod(options).done((events) => {
			self.fullCalendar.removeAllEvents();
			self.fullCalendar.addEventSource(events.result);
			progressInstance.progressIndicator({ mode: 'hide' });
		});
		window.calendarLoaded = true;
	}
	/**
	 * Show create view
	 * @returns {Promise}
	 */
	showCalendarCreateView() {
		const aDeferred = $.Deferred();
		if (this.eventCreate == 1) {
			const sideBar = this.getSidebarView(),
				qcForm = sideBar.find('.js-qc-form');
			if (qcForm.find('form').length > 0 && qcForm.find('input[name=record]').length === 0) {
				aDeferred.resolve(qcForm);
			} else {
				let progressInstance = $.progressIndicator({ blockInfo: { enabled: true } });
				this.showSidebarEvent({ module: this.module, view: 'EventForm' })
					.done(() => {
						progressInstance.progressIndicator({ mode: 'hide' });
						this.registerAutofillTime();
						aDeferred.resolve(qcForm);
					})
					.fail((error) => {
						progressInstance.progressIndicator({ mode: 'hide' });
						app.errorLog(error);
					});
			}
		} else {
			aDeferred.reject();
		}
		return aDeferred.promise();
	}
	/**
	 * Auto select date in create view in extended calendar
	 */
	registerAutofillTime() {
		if (app.getMainParams('autofillTime')) {
			this.container.find('.js-autofill').prop('checked', 'checked').trigger('change');
		}
	}
	/**
	 * Register cache settings
	 */
	registerCacheSettings() {
		const self = this;
		$('.siteBarRight .filterField').each(function (index) {
			let name = $(this).attr('id');
			let value = app.moduleCacheGet(name);
			let element = $('#' + name);
			if (element.length > 0 && value != null) {
				if (element.attr('type') == 'checkbox') {
					element.prop('checked', value);
				}
			}
		});
		this.getCalendarView()
			.find('.fc-toolbar .fc-button')
			.on('click', function (e) {
				let element = $(e.currentTarget);
				if (element.hasClass('fc-' + self.fullCalendar.view.type + '-button')) {
					app.moduleCacheSet('defaultView', self.fullCalendar.view.type);
				} else if (
					element.hasClass('fc-prev-button') ||
					element.hasClass('fc-next-button') ||
					element.hasClass('fc-today-button')
				) {
					app.moduleCacheSet('start', App.Fields.Date.dateToUserFormat(self.fullCalendar.view.activeStart));
					app.moduleCacheSet('end', App.Fields.Date.dateToUserFormat(self.fullCalendar.view.activeEnd));
				}
			});
		const keys = app.moduleCacheKeys();
		if (keys.length > 0) {
			let alert = $('#moduleCacheAlert');
			alert.find('.cacheClear').on('click', function (e) {
				app.moduleCacheClear();
				alert.addClass('d-none');
				location.reload();
			});
		}
	}
	/**
	 * Register site bar events
	 */
	registerSiteBarEvents() {
		let calendarRightPanel = $('.js-calendar-right-panel');
		calendarRightPanel.find('.js-show-sitebar').on('click', () => {
			if (calendarRightPanel.hasClass('hideSiteBar')) {
				calendarRightPanel.find('.js-toggle-site-bar-right-button').trigger('click');
			}
		});
	}
	/**
	 * Register popover buttons' click
	 */
	registerPopoverButtonsClickEvent() {
		$(document).on('click', '.js-calendar-popover__button', this.showCalendarPopoverLinkInSidebar.bind(this));
	}
	/**
	 *  Show popover link in sidebar
	 * @param {jQuery.Event} e click event
	 * @returns {boolean}
	 */
	showCalendarPopoverLinkInSidebar(e) {
		let href = e.currentTarget.href;
		const hrefObject = app.convertUrlToObject(href);
		if (hrefObject.module !== 'Calendar' || (hrefObject.view !== 'Edit' && hrefObject.view !== 'Detail')) {
			return true;
		} else {
			e.preventDefault();
			const sidebarView = hrefObject.view === 'Edit' ? 'EventForm' : 'ActivityState';
			href = href.replace(hrefObject.view, sidebarView);
			this.showSidebarEvent(href);
		}
	}
	/**
	 * Register events
	 */
	registerEvents() {
		super.registerEvents();
		this.registerSiteBarEvents();
		this.registerPopoverButtonsClickEvent();
		ElementQueries.listen();
		this.showCalendarCreateView();
	}
};
