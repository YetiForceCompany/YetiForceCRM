/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
window.Occurrences_CalendarExtended_Js = class extends Calendar_CalendarExtended_Js {
	constructor(container, readonly) {
		super(container, readonly, false);
		this.browserHistory = false;
	}
	getCalendarSidebarData() {}
	registerEditForm() {}
	registerCacheSettings() {}
	registerPinUser() {}
	/**
	 * Get calendar create view.
	 * @returns {promise}
	 */
	getCalendarCreateView() {
		let self = this;
		let aDeferred = jQuery.Deferred();

		if (this.calendarCreateView !== false) {
			aDeferred.resolve(this.calendarCreateView.clone(true, true));
			return aDeferred.promise();
		}
		let progressInstance = jQuery.progressIndicator();
		this.loadCalendarCreateView()
			.done(function(data) {
				progressInstance.hide();
				self.calendarCreateView = data;
				aDeferred.resolve(data.clone(true, true));
			})
			.fail(function() {
				progressInstance.hide();
			});
		return aDeferred.promise();
	}
	/**
	 * Set calendar module options.
	 * @returns {{allDaySlot: boolean, dayClick: object, selectable: boolean}}
	 */
	setCalendarModuleOptions() {
		let self = this;
		return {
			allDaySlot: false,
			dayClick: this.eventCreate
				? function(date) {
						self.registerDayClickEvent(date.format());
						self.getCalendarView().fullCalendar('unselect');
				  }
				: false,
			selectable: false
		};
	}

	/**
	 * Register day click event.
	 * @param {string} date
	 */
	registerDayClickEvent(date) {
		let self = this;
		self.getCalendarCreateView().done(function(data) {
			if (data.length <= 0) {
				return;
			}
			let dateElement = data.find('[name="date_start"]');
			let dateFormat = dateElement.data('dateFormat').toUpperCase(),
				timeFormat = dateElement.data('hour-format'),
				defaultTimeFormat = 'hh:mm A';
			if (timeFormat == 24) {
				defaultTimeFormat = 'HH:mm';
			}
			let startDateInstance = Date.parse(date);
			let startDateString = moment(date).format(dateFormat);
			let startTimeString = moment(date).format(defaultTimeFormat);
			let endDateInstance = Date.parse(date);
			let endDateString = moment(date).format(dateFormat);

			let view = self.getCalendarView().fullCalendar('getView');
			let endTimeString;
			if ('month' == view.name) {
				let diffDays = parseInt((endDateInstance - startDateInstance) / (1000 * 60 * 60 * 24));
				if (diffDays > 1) {
					let defaultFirstHour = app.getMainParams('startHour');
					let explodedTime = defaultFirstHour.split(':');
					startTimeString = explodedTime['0'];
					let defaultLastHour = app.getMainParams('endHour');
					explodedTime = defaultLastHour.split(':');
					endTimeString = explodedTime['0'];
				} else {
					let now = new Date();
					startTimeString = moment(now).format(defaultTimeFormat);
					endTimeString = moment(now)
						.add(15, 'minutes')
						.format(defaultTimeFormat);
				}
			} else {
				endTimeString = moment(endDateInstance)
					.add(30, 'minutes')
					.format(defaultTimeFormat);
			}
			data.find('[name="date_start"]').val(startDateString + ' ' + startTimeString);
			data.find('[name="date_end"]').val(endDateString + ' ' + endTimeString);

			let headerInstance = new Vtiger_Header_Js();
			headerInstance.handleQuickCreateData(data, {
				callbackFunction(data) {
					self.loadCalendarData();
				}
			});
		});
	}
	/**
	 * Add event data to render.
	 */
	getEventRenderData(calendarDetails) {
		const calendar = this.getCalendarView();
		const eventObject = {
			id: calendarDetails._recordId,
			title: calendarDetails._recordLabel,
			start: calendar.fullCalendar('moment', calendarDetails.date_start.value).format(),
			end: calendar.fullCalendar('moment', calendarDetails.date_end.value).format(),
			start_display: calendarDetails.date_start.display_value,
			end_display: calendarDetails.date_end.display_value,
			url: `index.php?module=${CONFIG.module}&view=Detail&record=${calendarDetails._recordId}`,
			className: `js-popover-tooltip--record ownerCBg_${calendarDetails.assigned_user_id.value} picklistCBr_${
				CONFIG.module
			}_${
				$('.js-calendar__filter__select[data-cache="calendar-types"]').length
					? this.eventTypeKeyName + '_' + calendarDetails[this.eventTypeKeyName]['value']
					: ''
			}`,
			allDay: typeof calendarDetails.allday === 'undefined' ? false : calendarDetails.allday.value == 'on'
		};
		return eventObject;
	}
	isNewEventToDisplay(eventObject) {
		return true;
	}

	/**
	 * Render calendar
	 */
	renderCalendar() {
		let self = this,
			basicOptions = this.setCalendarOptions(),
			options = {
				header: {
					left: 'year,month,' + app.getMainParams('weekView') + ',' + app.getMainParams('dayView'),
					center: 'prevYear,prev,title,next,nextYear',
					right: 'today'
				},
				views: {
					basic: {
						eventLimit: false
					},
					year: {
						eventLimit: 10,
						eventLimitText: app.vtranslate('JS_COUNT_RECORDS'),
						titleFormat: 'YYYY',
						select: function(start, end) {},
						loadView: function() {
							self.getCalendarView()
								.fullCalendar('getCalendar')
								.view.render();
						}
					},
					month: {
						titleFormat: this.parseDateFormat('month'),
						loadView: function() {
							self.loadCalendarData();
						}
					},
					week: {
						titleFormat: this.parseDateFormat('week'),
						loadView: function() {
							self.loadCalendarData();
						}
					},
					day: {
						titleFormat: this.parseDateFormat('day'),
						loadView: function() {
							self.loadCalendarData();
						}
					},
					basicDay: {
						type: 'agendaDay',
						loadView: function() {
							self.loadCalendarData();
						}
					}
				},
				select: function(start, end) {
					self.selectDays(start, end);
					self.getCalendarView().fullCalendar('unselect');
				},
				eventRender: function(event, element) {
					self.eventRenderer(event, element);
				},
				viewRender: function(view, element) {
					if (view.type !== 'year') {
						self.loadCalendarData(view);
					}
				},
				addCalendarEvent(calendarDetails) {
					self.getCalendarView().fullCalendar('renderEvent', self.getEventData(calendarDetails));
				}
			};
		options = Object.assign(basicOptions, options);
		options.eventClick = function(calEvent, jsEvent) {
			jsEvent.preventDefault();
			const link = $(this).attr('href');
			if (link && $.inArray('js-show-modal', calEvent.className) !== -1) {
				app.showModalWindow(null, link);
			}
		};
		this.calendar.fullCalendar(options);
	}
};
