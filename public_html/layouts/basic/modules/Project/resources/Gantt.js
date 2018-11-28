/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

class Gantt {

	/**
	 * Constructor
	 *
	 * @param {jQuery|HTMLElement|string} container
	 * @param {object} projectData
	 */
	constructor(container, projectData) {
		this.container = $(container);
		this.registerLanguage();
		this.options = {
			style: {
				"tree-row-bar-polygon": {
					"stroke": "#E74C3C00",
					"stroke-width": 0,
					"fill": "#F75C4C",
				},
				"tree-row-progress-bar-outline": {
					"stroke": "#E74C3C00",
					"stroke-width": 0
				},
			},
			title: {
				label: 'Gantt',
				html: false,
			},
			taskList: {
				columns: [
					{
						id: 1,
						label: app.vtranslate('JS_NO.', 'Project'),
						html: true,
						value: 'number',
						width: 65,
						style: {
							"task-list-header-label": {
								'text-align': 'center',
								'width': '100%'
							},
							"task-list-item-value": {
								'text-align': 'center',
								'width': '100%'
							}
						}
					},
					{id: 2, label: app.vtranslate("JS_NAME"), value: 'label', width: 280, expander: true},
					{
						id: 3, label: app.vtranslate("JS_PRIORITY"), value: 'priority_label', width: 70, style: {
							"task-list-header-label": {
								'text-align': 'center',
								'width': '100%'
							},
							"task-list-item-value": {
								'text-align': 'center',
								'width': '100%'
							}
						}
					},
					{
						id: 3, label: app.vtranslate("JS_STATUS"), value: 'status_label', width: 100, style: {
							"task-list-header-label": {
								'text-align': 'center',
								'width': '100%'
							},
							"task-list-item-value": {
								'text-align': 'center',
								'width': '100%'
							}
						}
					},
					{
						id: 4,
						label: app.vtranslate("JS_DURATION_SHORT", "Project"),
						value: (task) => {
							return task.duration / 24 / 60 / 60;
						},
						width: 45,
						style: {
							"task-list-header-label": {
								'text-align': 'center',
								'width': '100%'
							},
							"task-list-item-value": {
								'text-align': 'center',
								'width': '100%'
							}
						}
					},
					{id: 5, label: app.vtranslate("JS_ASSIGNED", "Project"), value: 'assigned_user_name', width: 150},
					{
						id: 5, label: '%', value: 'progress', width: 35, style: {
							"task-list-header-label": {
								'text-align': 'center',
								'width': '100%'
							},
							"task-list-item-value": {
								'text-align': 'center',
								'width': '100%'
							}
						}
					}
				]
			},
			locale: {
				code: CONFIG.langKey,
				name: CONFIG.langKey,
				weekdays: [LANG.JS_MONDAY, LANG.JS_TUESDAY, LANG.JS_WEDNESDAY, LANG.JS_THURSDAY, LANG.JS_FRIDAY, LANG.JS_SATURDAY, LANG.JS_SUNDAY],
				weekdaysShort: [LANG.JS_MON, LANG.JS_TUE, LANG.JS_WED, LANG.JS_THU, LANG.JS_FRI, LANG.JS_SAT, LANG.JS_SUN],
				weekdaysMin: [LANG.JS_MON, LANG.JS_TUE, LANG.JS_WED, LANG.JS_THU, LANG.JS_FRI, LANG.JS_SAT, LANG.JS_SUN],
				months: [LANG.JS_JANUARY, LANG.JS_FEBRUARY, LANG.JS_MARCH, LANG.JS_APRIL, LANG.JS_MAY, LANG.JS_JUNE, LANG.JS_JULY, LANG.JS_AUGUST, LANG.JS_SEPTEMBER, LANG.JS_NOVEMBER, LANG.JS_OCTOBER, LANG.JS_DECEMBER],
				monthsShort: [LANG.JS_JAN, LANG.JS_FEB, LANG.JS_MAR, LANG.JS_APR, LANG.JS_MAY, LANG.JS_JUN, LANG.JS_JUL, LANG.JS_AUG, LANG.JS_SEP, LANG.JS_NOV, LANG.JS_OCT, LANG.JS_DEC],
				ordinal: n => `${n}`,
			}
		};
		if (typeof projectData !== 'undefined') {
			this.options.title.label = projectData
			this.loadProject(projectData);
		}
	}

	/**
	 * Register language translations globally (replace old ones)
	 */
	registerLanguage() {
		/*GanttMaster.messages.GANTT_ERROR_LOADING_DATA_TASK_REMOVED = app.vtranslate("JS_GANTT_ERROR_LOADING_DATA_TASK_REMOVED", 'Project');
		GanttMaster.messages.INVALID_DATE_FORMAT = app.vtranslate("JS_INVALID_DATE_FORMAT", 'Project');
		GanttMaster.messages.GANTT_SEMESTER_SHORT = app.vtranslate("JS_GANTT_SEMESTER_SHORT", 'Project');
		GanttMaster.messages.GANTT_SEMESTER = app.vtranslate("JS_GANTT_SEMESTER", 'Project');
		GanttMaster.messages.GANTT_QUARTER_SHORT = app.vtranslate("JS_GANTT_QUARTER_SHORT", 'Project');
		GanttMaster.messages.GANTT_QUARTER = app.vtranslate("JS_GANTT_QUARTER", 'Project');
		GanttMaster.messages.GANTT_WEEK = app.vtranslate("JS_GANTT_WEEK", 'Project');
		GanttMaster.messages.GANTT_WEEK_SHORT = app.vtranslate("JS_GANTT_WEEK_SHORT", 'Project');
		Gantt_i18n.YES = app.vtranslate("JS_YES", 'Project');
		Gantt_i18n.NO = app.vtranslate("JS_NO", 'Project');
		Gantt_i18n.INVALID_DATA = app.vtranslate("JS_INVALID_DATA", 'Project');
		Gantt_i18n.ERROR_ON_FIELD = app.vtranslate("JS_ERROR_ON_FIELD", 'Project');
		Gantt_i18n.OUT_OF_BOUDARIES = app.vtranslate("JS_OUT_OF_BOUDARIES", 'Project');
		Gantt_i18n.ERR_FIELD_MAX_SIZE_EXCEEDED = app.vtranslate("JS_ERR_FIELD_MAX_SIZE_EXCEEDED", 'Project');
		Gantt_i18n.WEEK_SHORT = app.vtranslate("JS_WEEK_SHORT", 'Project');
		Gantt_i18n.PROCEED = app.vtranslate("JS_PROCEED", 'Project');
		Gantt_i18n.PREV = app.vtranslate("JS_PREV", 'Project');
		Gantt_i18n.NEXT = app.vtranslate("JS_NEXT", 'Project');
		Gantt_i18n.HINT_SKIP = app.vtranslate("JS_HINT_SKIP", 'Project');
		Date.monthNames = App.Fields.Date.fullMonthsTranslated.map(month => month);
		Date.monthAbbreviations = App.Fields.Date.monthsTranslated.map(month => month);
		Date.dayAbbreviations = App.Fields.Date.daysTranslated.map(day => day);
		Date.dayNames = App.Fields.Date.fullDaysTranslated.map(day => day);
		Date.firstDayOfWeek = CONFIG.firstDayOfWeekNo;
		Date.defaultFormat = CONFIG.dateFormat;
		Date.today = app.vtranslate('JS_TODAY');
		Number.decimalSeparator = CONFIG.currencyDecimalSeparator;
		Number.groupingSeparator = CONFIG.currencyGroupingSeparator;
		Number.currencyFormat = "###,##0.00";*/
	}

	/**
	 * Get branch that fulfill statuses
	 *
	 * @param {String} moduleName
	 * @param {String[]} statuses
	 * @param {Object} current
	 * @returns {Object}
	 */
	getBranchesWithStatus(moduleName, statuses, current) {
		current = {...current};
		let children = [];
		current.children = current.children.map(task => task);
		for (let task of current.children) {
			children.push(this.getBranchesWithStatus(moduleName, statuses, task));
		}
		current.children = children;
		return current;
	}

	/**
	 * Filter project data
	 *
	 * @param {Object} projectData
	 * @returns {Object}
	 */
	filterProjectData(projectData) {
		let newProjectData = Object.assign({}, projectData);
		let tree = this.makeTree(newProjectData.tasks);
		for (let moduleName in this.filter.status) {
			if (this.filter.status.hasOwnProperty(moduleName)) {
				tree = this.getBranchesWithStatus(moduleName, this.filter.status[moduleName], tree);
			}
		}
		newProjectData.tasks = this.flattenTree(tree);
		return newProjectData;
	}

	/**
	 * Load project
	 */
	loadProject(projectData) {
		this.projectData = projectData;
		this.allTasks = this.projectData.tasks;
		if (typeof this.allTasks === 'undefined') {
			$('.js-hide-filter').addClass('d-none');
			$('.js-show-add-record').removeClass('d-none');
			return;
		}
		this.statuses = this.projectData.statuses;
		this.filter = {status: this.projectData.activeStatuses};
		this.container.closest('form').on('submit', (ev) => {
			ev.preventDefault();
			ev.stopPropagation();
			return false;
		});
		GanttElastic.mount({
			el: '#' + this.container.attr('id'),
			tasks: this.allTasks,
			options: this.options
		});
	}

	/**
	 * Load project from ajax request
	 * @param {object} params - request params such as module/action and projectId
	 */
	loadProjectFromAjax(params) {
		const progressInstance = jQuery.progressIndicator({blockInfo: {enabled: true}});
		AppConnector.request(params).done((response) => {
			this.loadProject(response.result);
			progressInstance.progressIndicator({mode: 'hide'});
		});
	}

	/**
	 * Load new data to gantt
	 *
	 * @param {Object} data
	 */
	reloadData(data) {
		this.gantt.loadProject(data);
	}

	/**
	 * Save filter and reload data.
	 *
	 * @param {Object} filterOptions
	 */
	saveFilter(filterOptions) {
		this.filter = filterOptions;
		this.reloadData(this.filterProjectData(this.projectData));
	}

	/**
	 * Get status from value (object with other props)
	 * @param {String} value
	 * @param {String} moduleName
	 * @returns {Object}
	 */
	getStatusFromValue(value, moduleName) {
		for (let status of this.statuses[moduleName]) {
			if (status.value === value) {
				return Object.assign({}, status);
			}
		}
		app.errorLog(`Status not found [${value}]`);
	}

	/**
	 * Open modal with status filters
	 */
	showFiltersModal() {
		const self = this;
		const box = bootbox.dialog({
			show: 'false',
			message: `<div class="js-gantt__filter-modal form" data-js="container">
				<div class="form-group">
					<label>${app.vtranslate("JS_PROJECT_STATUSES", 'Project')}:</label>
					<select class="select2 form-control" id="js-gantt__filter-project" multiple>
						${self.statuses.Project.map((status) => {
				return `<option value="${status.value}" ${this.filter.status.Project.map(status => status.value).indexOf(status.value) >= 0 ? 'selected' : ''}>${status.label}</option>`;
			})}
					</select>
				</div>
				<div class="form-group">
				<label>${app.vtranslate("JS_MILESTONE_STATUSES", 'Project')}:</label>
					<select class="select2 form-control" id="js-gantt__filter-milestone" multiple>
						${self.statuses.ProjectMilestone.map((status) => {
				return `<option value="${status.value}" ${this.filter.status.ProjectMilestone.map(status => status.value).indexOf(status.value) >= 0 ? 'selected' : ''}>${status.label}</option>`;
			})}
					</select>
				</div>
				<div class="form-group">
				<label>${app.vtranslate("JS_TASK_STATUSES", 'Project')}:</label>
					<select class="select2 form-control" id="js-gantt__filter-task" multiple>
						${self.statuses.ProjectTask.map((status) => {
				return `<option value="${status.value}" ${this.filter.status.ProjectTask.map(status => status.value).indexOf(status.value) >= 0 ? 'selected' : ''}>${status.label}</option>`;
			})}
					</select>
				</div>
			</div>`,
			title: '<span class="fas fa-filter"></span> ' + app.vtranslate('JS_FILTER_BY_STATUSES', 'Project'),
			buttons: {
				success: {
					label: '<span class="fas fa-check mr-1"></span>' + app.vtranslate('JS_UPDATE_GANTT', 'Project'),
					className: "btn-success",
					callback: function () {
						self.saveFilter({
							status: {
								Project: $('#js-gantt__filter-project', this).val().map((status) => {
									return self.getStatusFromValue(status, 'Project');
								}),
								ProjectMilestone: $('#js-gantt__filter-milestone', this).val().map((status) => {
									return self.getStatusFromValue(status, 'ProjectMilestone');
								}),
								ProjectTask: $('#js-gantt__filter-task', this).val().map((status) => {
									return self.getStatusFromValue(status, 'ProjectTask');
								}),
							}
						});
					}
				},
				danger: {
					label: '<span class="fas fa-times mr-1"></span>' + app.vtranslate('JS_CANCEL'),
					className: "btn-danger",
					callback: function () {
					}
				}
			}
		});
		App.Fields.Picklist.showSelect2ElementView($(box).find('.select2'));
		box.show();
	}

	/**
	 * Register events for gantt actions in current container
	 */
	registerEvents() {
		/*const container = this.container;
		const self = this;
		container.find('.js-gantt__expand-all-btn', container).on('click', function (e) {
			e.preventDefault();
			container.trigger('expandAll.gantt');
		});
		container.find('.js-gantt__collapse-all-btn', container).on('click', function (e) {
			e.preventDefault();
			container.trigger('collapseAll.gantt');
		});
		container.find('.js-gantt__zoom-in-btn', container).on('click', function (e) {
			e.preventDefault();
			container.trigger('zoomPlus.gantt');
		});
		container.find('.js-gantt__zoom-out-btn').on('click', function (e) {
			e.preventDefault();
			container.trigger('zoomMinus.gantt');
		});
		container.find('.js-gantt__print-btn').on('click', function (e) {
			e.preventDefault();
			container.trigger('print.gantt');
		});
		container.find('.js-gantt__show-critical-path-btn').on('click', function (e) {
			e.preventDefault();
			this.gantt.gantt.showCriticalPath = !gantt.gantt.showCriticalPath;
			this.gantt.redraw();
		}.bind(this));
		container.find('.js-gantt__resize-0-btn').on('click', function (e) {
			e.preventDefault();
			this.gantt.splitter.resize(.1);
		}.bind(this));
		container.find('.js-gantt__resize-50-btn').on('click', function (e) {
			e.preventDefault();
			this.gantt.splitter.resize(50);
		}.bind(this));
		container.find('.js-gantt__resize-100-btn').on('click', function (e) {
			e.preventDefault();
			this.gantt.splitter.resize(100);
		}.bind(this));
		container.find('.js-gantt__fullscreen-btn').on('click', function (e) {
			e.preventDefault();
			container.trigger('fullScreen.gantt');
		}.bind(this));
		container.find('.js-gantt__front-filter').on('click', function (e) {
			e.preventDefault();
			self.showFiltersModal();
		});
		*/
		container.find('[data-toggle="tooltip"]').tooltip();
	}
}
