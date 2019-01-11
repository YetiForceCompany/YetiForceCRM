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
		this.containerParent = this.container.parent();
		this.options = {
			maxRows: 30,
			style: {
				'chart-row-bar-polygon': {
					'stroke': '#E74C3C00',
					'stroke-width': 0,
					'fill': '#F75C4C',
				},
				'chart-row-progress-bar-outline': {
					'stroke': '#E74C3C00',
					'stroke-width': 0
				},
				'header-title': {
					'max-width': '50%'
				}
			},
			title: {
				label: LANG.JS_GANTT_TITLE,
				html: true,
			},
			taskList: {
				expander: {
					straight: false,
				},
				columns: [
					{
						id: 1,
						label: app.vtranslate('JS_NO.', 'Project'),
						html: true,
						value: 'number',
						width: 65,
					},
					{
						id: 2, label: app.vtranslate('JS_NAME'),
						html: true,
						value: 'label', width: 280, expander: true
					},
					{id: 3, label: app.vtranslate('JS_PRIORITY'), value: 'priority_label', width: 70},
					{id: 3, label: app.vtranslate('JS_STATUS'), value: 'status_label', width: 80},
					{
						id: 4,
						label: app.vtranslate('JS_DURATION_SHORT', 'Project'),
						value: (task) => {
							return task.duration / 24 / 60 / 60;
						},
						width: 45,
						style: {
							'task-list-header-label': {
								'text-align': 'center',
								'width': '100%'
							},
							'task-list-item-value-container': {
								'text-align': 'center',
								'width': '100%'
							}
						}
					},
					{id: 5, label: app.vtranslate('JS_ASSIGNED', 'Project'), value: 'assigned_user_name', width: 150},
					{
						id: 5, label: '%', value: 'progress', width: 35, style: {
							'task-list-header-label': {
								'text-align': 'center',
								'width': '100%'
							},
							'task-list-item-value-container': {
								'text-align': 'center',
								'width': '100%'
							}
						}
					}
				]
			},
		};
		this.registerLanguage();
		if (typeof projectData !== 'undefined') {
			this.options.title.label = projectData;
			this.loadProject(projectData);
		}
		this.registerEvents();
	}

	/**
	 * Register language translations globally (replace old ones)
	 */
	registerLanguage() {
		this.options.locale = {
			'code': CONFIG.langKey,
			'name': CONFIG.langKey,
			'weekdays': [LANG.JS_MONDAY, LANG.JS_TUESDAY, LANG.JS_WEDNESDAY, LANG.JS_THURSDAY, LANG.JS_FRIDAY, LANG.JS_SATURDAY, LANG.JS_SUNDAY],
			'weekdaysShort': [LANG.JS_MON, LANG.JS_TUE, LANG.JS_WED, LANG.JS_THU, LANG.JS_FRI, LANG.JS_SAT, LANG.JS_SUN],
			'weekdaysMin': [LANG.JS_MON, LANG.JS_TUE, LANG.JS_WED, LANG.JS_THU, LANG.JS_FRI, LANG.JS_SAT, LANG.JS_SUN],
			'months': [LANG.JS_JANUARY, LANG.JS_FEBRUARY, LANG.JS_MARCH, LANG.JS_APRIL, LANG.JS_MAY, LANG.JS_JUNE, LANG.JS_JULY, LANG.JS_AUGUST, LANG.JS_SEPTEMBER, LANG.JS_NOVEMBER, LANG.JS_OCTOBER, LANG.JS_DECEMBER],
			'monthsShort': [LANG.JS_JAN, LANG.JS_FEB, LANG.JS_MAR, LANG.JS_APR, LANG.JS_MAY, LANG.JS_JUN, LANG.JS_JUL, LANG.JS_AUG, LANG.JS_SEP, LANG.JS_NOV, LANG.JS_OCT, LANG.JS_DEC],
			'ordinal': n => `${n}`,
			'Now': LANG.JS_GANTT_NOW,
			'X-Scale': LANG.JS_GANTT_ZOOM_X,
			'Y-Scale': LANG.JS_GANTT_ZOOM_Y,
			'Task list width': LANG.JS_GANTT_TASKLIST,
			'Before/After': LANG.JS_GANTT_EXPAND,
			'Display task list': LANG.JS_GANTT_TASKLIST_VISIBLE
		};
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
	 * Add icons to tasks
	 * @param {array} tasks
	 * @returns {array}
	 */
	addIcons(tasks) {
		return tasks.map((task) => {
			let icon = 'briefcase';
			if (task.type === 'milestone') {
				icon = 'folder';
			} else if (task.type === 'task') {
				icon = 'file';
			}
			const iconClass = 'fas fa-' + icon;
			task.label = `<span class="${iconClass} fa-lg mr-1"></span> ${task.label}`;
			return task;
		});
	}

	/**
	 * Resize gantt chart
	 */
	resize() {
		let offsetTop = this.container.offset().top;
		let contentHeight = $('body').eq(0).height() - $('.js-footer').eq(0).height();
		let height = contentHeight - offsetTop - 160;
		if (height < 0) {
			height = 0;
		}
		this.options.maxHeight = height;
		if (typeof this.ganttState !== 'undefined' && this.ganttState) {
			this.ganttState.maxHeight = height;
		}
	}

	/**
	 * Load project
	 */
	loadProject(projectData) {
		this.projectData = projectData;
		this.allTasks = this.addIcons(this.projectData.tasks);
		if (typeof projectData.title !== 'undefined' && projectData.title) {
			this.options.title.label = projectData.title;
		} else {
			this.options.title.label = '<span class="fas fa-briefcase mr-1"></span> ' + LANG.JS_GANTT_TITLE;
		}
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
		this.resize();
		const self = this;
		if (typeof self.ganttElastic === 'undefined') {
			GanttElastic.component.components['gantt-header'] = Header;
			GanttElastic.mount({
				el: '#' + this.container.attr('id'),
				tasks: this.allTasks,
				options: this.options,
				ready(ganttElasticInstance) {
					self.ganttElastic = ganttElasticInstance;
					self.ganttState = ganttElasticInstance.state;
				}
			});
			this.container = this.containerParent.find('.gantt-elastic').eq(0);
		} else {
			self.ganttState.tasks = this.allTasks;
		}
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
		this.loadProject(data);
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
		const container = this.container;
		container.find('.js-gantt__front-filter').on('click', function (e) {
			e.preventDefault();
			self.showFiltersModal();
		});
		container.find('[data-toggle="tooltip"]').tooltip();
		window.addEventListener('resize', () => {
			this.resize();
		});
	}
}
