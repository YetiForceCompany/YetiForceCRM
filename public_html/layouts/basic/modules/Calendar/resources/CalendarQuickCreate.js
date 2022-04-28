/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 *  Class representing a modal calendar.
 * @extends Calendar_Calendar_Js
 */
window.Calendar_CalendarQuickCreate_Js = class Calendar_CalendarQuickCreate_Js extends Calendar_Calendar_Js {
	constructor(container, readonly) {
		super(container, readonly);
		this.isSwitchAllDays = false;
		this.sidebarName = 'add'; //available: add, status, edit
		this.eventCreate = false;
		this.module = 'Calendar';
		this.renderCalendar();
		this.registerEvents();
	}
	/**
	 * Function sets calendar module's options
	 * @returns {{eventClick: function, headerToolbar: object, selectable: boolean}}
	 */
	setCalendarModuleOptions() {
		let options = super.setCalendarModuleOptions();
		options.selectable = true;
		options.headerToolbar = {
			left: `dayGridMonth,${app.getMainParams('weekView')},${app.getMainParams('dayView')},today`,
			center: 'prevYear,prev,title,next,nextYear',
			right: ''
		};
		options.eventClick = function (info) {
			info.jsEvent.preventDefault();
		};
		return options;
	}
	/**
	 * Get selected users
	 * @returns {{ selectedIds: array, excludedIds: array }}
	 */
	getSelectedUsersCalendar() {
		return { selectedIds: [this.container.find('.assigned_user_id').val()], excludedIds: [] };
	}
	/**
	 * Function registers calendar events
	 */
	registerEvents() {
		const calendarView = this.getCalendarView();
		this.switchContainer = $(`<div class="js-calendar-switch-container"></div>`).insertAfter(
			calendarView.find('.fc-center')
		);
		this.registerSwitchEvents();
		this.registerUsersChange();
		this.registerAutofillTime();
		this.registerPopoverButtonsClickEvent();
	}
	/**
	 * Function registers calendar switch event
	 */
	registerSwitchEvents() {
		if (app.getMainParams('hiddenDays', true) !== false) {
			this.switchContainer.html(this.createSwitch());
			this.switchContainer.find('input').on('change', (e) => {
				const currentTarget = $(e.currentTarget);
				let hiddenDays = [];
				if (typeof currentTarget.data('on-text') !== 'undefined') {
					hiddenDays = app.getMainParams('hiddenDays', true);
					this.isSwitchAllDays = false;
				} else {
					this.isSwitchAllDays = true;
				}
				this.fullCalendar.setOption('hiddenDays', hiddenDays);
				this.registerSwitchEvents();
			});
		}
	}
	/**
	 * Generate filter buttons
	 * @returns {string}
	 */
	createSwitch() {
		let on = app.vtranslate('JS_WORK_DAYS'),
			off = app.vtranslate('JS_ALL'),
			state = this.isSwitchAllDays;
		return `<div class="btn-group btn-group-toggle js-switch c-calendar-switch" data-toggle="buttons">
					<label class="btn btn-outline-primary c-calendar-switch__button js-switch--label-on ${state ? '' : 'active'}">
						<input type="radio" name="options" data-on-text="${on}" autocomplete="off" ${state ? '' : 'checked'}>${on}
					</label>
					<label class="btn btn-outline-primary c-calendar-switch__button ${state ? 'active' : ''}">
						<input type="radio" name="options" data-off-text="${off}" autocomplete="off" ${state ? 'checked' : ''}>${off}
					</label>
				</div>`;
	}
	/**
	 * Function registers select's user change event
	 */
	registerUsersChange() {
		this.container.find('.assigned_user_id').on('change', () => {
			this.reloadCalendarData();
		});
	}
	/**
	 * Function invokes by FullCalendar, sets selected days in form
	 * @param {Object} info
	 * @returns
	 */
	selectDays(info) {
		if (this.sidebarName === 'status') {
			this.sidebarName = 'add';
			this.getCalendarCreateView().done(() => {
				this.selectDays(info);
			});
			return;
		}
		this.selectCallbackCreateModal(this.container, info);
	}

	/** @inheritdoc */
	registerEditForm(sideBar) {
		let editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(sideBar.find('[name="module"]').val()),
			params = [];
		let rightFormCreate = sideBar.find('form.js-form');
		editViewInstance.registerBasicEvents(rightFormCreate);
		rightFormCreate.validationEngine(app.validationEngineOptions);
		App.Fields.Picklist.showSelect2ElementView(sideBar.find('select'));
		sideBar.find('.js-summary-close-edit').on('click', () => {
			this.getCalendarCreateView();
		});
		App.Components.QuickCreate.registerPostLoadEvents(rightFormCreate, params);
		App.Fields.Text.Editor.register(sideBar.find('.js-editor'), { height: '5em', toolbar: 'Min' });
	}

	/** @inheritdoc */
	updateSidebar(sidebar, data) {
		const modalTitleContainer = $('.js-modal-title__container'),
			modalTitles = modalTitleContainer.find('[class*="js-modal-title"]');
		data = $(data);

		modalTitles.addClass('d-none');
		if (data.hasClass('js-edit-form')) {
			let title = data.find('.js-sidebar-title ').data('title');
			modalTitles.filter(`.js-modal-title--${title}`).removeClass('d-none');
			this.sidebarName = title;
		} else if (data.hasClass('js-activity-state')) {
			modalTitles.filter('.js-modal-title--status').removeClass('d-none');
			this.sidebarName = 'status';
		}
		sidebar.find('.js-qc-form').html(data);
	}
};

jQuery.Class(
	'Calendar_QuickCreate_Js',
	{},
	{
		registerEvents: function (container) {
			new Calendar_CalendarQuickCreate_Js(container.closest('.js-modal-container'), true);
			container.find('.js-activity-buttons button').on('click', function (e) {
				let form = container.find('form');
				let currentTarget = $(e.currentTarget);
				if (1 === currentTarget.data('type')) {
					form.append('<input type=hidden name="activitystatus" value="' + currentTarget.data('state') + '">');
					form.submit();
				} else {
					container.find('.js-activity-buttons').remove();
					form.find('[name="record"]').val('');
					form.append('<input type=hidden name="postponed" value="true">');
					form.append('<input type=hidden name="followup" value="' + currentTarget.data('id') + '">');
				}
			});
		}
	}
);
