/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
		this.headerContainer = this.containerParent.parent().find('.js-gantt-header').eq(0);
		this.weekStart = 6 - CONFIG.firstDayOfWeekNos;
		let workingDays = [1, 2, 3, 4, 5];
		this.options = {
			slots: {
				header: {
					beforeOptions: `<button class="btn btn-primary pb-2 mr-2 h-100 js-gantt__front-filter"><span class="fas fa-filter"></span> ${LANG.JS_GANTT_FILTER}</button>`
				}
			},
			maxRows: 30,
			times: {
				timeZoom: 20
			},
			calendar: {
				workingDays
			},
			title: {
				label: LANG.JS_GANTT_TITLE,
				html: true
			},
			taskList: {
				expander: {
					straight: false
				},
				columns: [
					{
						id: 1,
						label: app.vtranslate('JS_NO.'),
						html: true,
						value: 'number',
						width: 65
					},
					{
						id: 2,
						label: app.vtranslate('JS_NAME'),
						html: true,
						value: 'label',
						width: 280,
						expander: true
					},
					{ id: 3, label: app.vtranslate('JS_PRIORITY'), value: 'priority_label', width: 70 },
					{ id: 3, label: app.vtranslate('JS_STATUS'), value: 'status_label', width: 80 },
					{
						id: 4,
						label: app.vtranslate('JS_DAYS'),
						value: (task) => {
							return task.duration / 24 / 60 / 60 / 1000;
						},
						width: 75,
						style: {
							'task-list-header-label': {
								'text-align': 'center',
								width: '100%'
							},
							'task-list-item-value-container': {
								'text-align': 'center',
								width: '100%'
							}
						}
					},
					{
						id: 5,
						label: app.vtranslate('JS_PLANNED'),
						value: (task) => {
							return task.planned_duration;
						},
						width: 85,
						style: {
							'task-list-header-label': {
								'text-align': 'center',
								width: '100%'
							},
							'task-list-item-value-container': {
								'text-align': 'center',
								width: '100%'
							}
						}
					},
					{
						id: 6,
						label: app.vtranslate('JS_REALISATION'),
						value: 'sum_time',
						width: 85,
						style: {
							'task-list-header-label': {
								'text-align': 'center',
								width: '100%'
							},
							'task-list-item-value-container': {
								'text-align': 'center',
								width: '100%'
							}
						}
					},
					{
						id: 7,
						label: app.vtranslate('JS_ASSIGNED', 'Project'),
						value: 'assigned_user_name',
						width: 110
					},
					{
						id: 8,
						label: '%',
						value: 'progress',
						width: 35,
						style: {
							'task-list-header-label': {
								'text-align': 'center',
								width: '100%'
							},
							'task-list-item-value-container': {
								'text-align': 'center',
								width: '100%'
							}
						}
					}
				]
			}
		};
		this.dynamicStyle = {
			'chart-expander-wrapper': {
				'line-height': '1'
			},
			'chart-row-bar-polygon': {
				stroke: '#E74C3C00',
				'stroke-width': 0,
				fill: '#F75C4C'
			},
			'chart-row-progress-bar-outline': {
				stroke: '#E74C3C00',
				'stroke-width': 0
			},
			'chart-days-highlight-rect': {
				fill: '#f3f5f780'
			},
			'header-title': {
				float: 'none',
				display: 'inline-flex',
				overflow: 'hidden'
			},
			'header-options': {
				float: 'none',
				display: 'inline-flex'
			},
			'header-title--html': {
				'white-space': 'nowrap',
				overflow: 'hidden',
				'text-overflow': 'ellipsis',
				'padding-left': '0',
				'letter-spacing': '0'
			},
			'slot-header-beforeOptions': {
				height: '100%',
				'vertical-align': 'top'
			}
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
			name: CONFIG.langKey,
			weekStart: this.weekStart,
			weekdays: [
				LANG.JS_SUNDAY,
				LANG.JS_MONDAY,
				LANG.JS_TUESDAY,
				LANG.JS_WEDNESDAY,
				LANG.JS_THURSDAY,
				LANG.JS_FRIDAY,
				LANG.JS_SATURDAY
			],
			weekdaysShort: [LANG.JS_SUN, LANG.JS_MON, LANG.JS_TUE, LANG.JS_WED, LANG.JS_THU, LANG.JS_FRI, LANG.JS_SAT],
			weekdaysMin: [LANG.JS_SUN, LANG.JS_MON, LANG.JS_TUE, LANG.JS_WED, LANG.JS_THU, LANG.JS_FRI, LANG.JS_SAT],
			months: [
				LANG.JS_JANUARY,
				LANG.JS_FEBRUARY,
				LANG.JS_MARCH,
				LANG.JS_APRIL,
				LANG.JS_MAY,
				LANG.JS_JUNE,
				LANG.JS_JULY,
				LANG.JS_AUGUST,
				LANG.JS_SEPTEMBER,
				LANG.JS_NOVEMBER,
				LANG.JS_OCTOBER,
				LANG.JS_DECEMBER
			],
			monthsShort: [
				LANG.JS_JAN,
				LANG.JS_FEB,
				LANG.JS_MAR,
				LANG.JS_APR,
				LANG.JS_MAY,
				LANG.JS_JUN,
				LANG.JS_JUL,
				LANG.JS_AUG,
				LANG.JS_SEP,
				LANG.JS_NOV,
				LANG.JS_OCT,
				LANG.JS_DEC
			],
			ordinal: (n) => `${n}`,
			Now: LANG.JS_GANTT_NOW,
			'X-Scale': LANG.JS_GANTT_ZOOM_X,
			'Y-Scale': LANG.JS_GANTT_ZOOM_Y,
			'Task list width': LANG.JS_GANTT_TASKLIST,
			'Before/After': LANG.JS_GANTT_EXPAND,
			'Display task list': LANG.JS_GANTT_TASKLIST_VISIBLE
		};
	}

	/**
	 * Filter project data
	 *
	 * @param {Object} _projectData
	 * @returns {Object}
	 */
	filterProjectData(_projectData) {
		let tasks = this.allTasks.map((task) => Object.assign({}, task));
		for (let moduleName in this.filter.status) {
			if (this.filter.status.hasOwnProperty(moduleName)) {
				const visibleLabels = this.filter.status[moduleName].map((status) => status.label);
				tasks = tasks.filter((task) => {
					return task.module !== moduleName || visibleLabels.indexOf(task.status_label) >= 0;
				});
			}
		}
		return tasks;
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
		let height = contentHeight - offsetTop - 100;
		if (height < 300) {
			height = 300;
		}
		this.options.maxHeight = height;
		if (typeof this.ganttState !== 'undefined' && this.ganttState) {
			this.ganttState.maxHeight = height;
		}
	}

	/**
	 * Register gantt header actions
	 */
	registerHeaderActions() {
		this.headerContainer.find('.js-gantt-header__btn-filter').on('click', (e) => {
			e.preventDefault();
			this.showFiltersModal();
		});
		this.headerContainer.find('.js-gantt-header__btn-center').on('click', (e) => {
			this.ganttElastic.$emit('recenterPosition');
		});
		this.headerContainer.find('.js-gantt-header__range-slider--x').on('input', (e) => {
			this.ganttElastic.$emit('times-timeZoom-change', Number(e.target.value));
		});
		this.headerContainer.find('.js-gantt-header__range-slider--y').on('input', (e) => {
			this.ganttElastic.$emit('row-height-change', Number(e.target.value));
		});
		this.headerContainer.find('.js-gantt-header__range-slider--task-list-width').on('input', (e) => {
			this.ganttElastic.$emit('taskList-width-change', Number(e.target.value));
		});
		this.headerContainer.find('.js-gantt-header__range-slider--scope').on('input', (e) => {
			this.ganttElastic.$emit('scope-change', Number(e.target.value));
		});
		this.headerContainer.find('.js-gantt-header__range-slider--task-list-visible').on('change', (e) => {
			this.ganttState.options.taskList.display = $(e.target).is(':checked');
		});
		this.ganttElastic.$watch('state.taskList.display', (value) => {
			this.headerContainer.find('.js-gantt-header__range-slider--task-list-visible').prop('checked', value);
		});
		this.headerContainer
			.find('.js-gantt-header__range-slider--task-list-visible')
			.prop('checked', this.ganttState.options.taskList.display ? 'checked' : false);
	}

	/**
	 * Load project
	 */
	loadProject(projectData) {
		this.projectData = projectData;
		if (typeof this.projectData.tasks === 'undefined' || this.projectData.tasks.length === 0) {
			$('.js-hide-filter').addClass('d-none');
			$('.js-show-add-record').removeClass('d-none');
			return;
		} else {
			this.allTasks = this.addIcons(this.projectData.tasks);
		}
		this.statuses = this.projectData.statuses;
		this.filter = { status: this.projectData.activeStatuses };
		this.container.closest('form').on('submit', (ev) => {
			ev.preventDefault();
			ev.stopPropagation();
			return false;
		});
		this.resize();
		const self = this;
		if (typeof self.ganttElastic === 'undefined') {
			this.ganttApp = GanttElastic.mount({
				el: '#' + this.container.attr('id'),
				data: {
					tasks: this.allTasks,
					options: this.options,
					dynamicStyle: this.dynamicStyle
				},
				ready(ganttElasticInstance) {
					self.ganttElastic = ganttElasticInstance;
					self.ganttState = ganttElasticInstance.state;
					self.registerHeaderActions();
				}
			});
			this.container = this.containerParent.find('.gantt-elastic').eq(0);
		} else {
			self.ganttApp.tasks = this.allTasks;
		}
	}

	/**
	 * Load project from ajax request
	 * @param {object} params - request params such as module/action and projectId
	 */
	loadProjectFromAjax(params) {
		const self = this,
			progressInstance = jQuery.progressIndicator({
				blockInfo: {
					enabled: true,
					onBlock: () => {
						AppConnector.request(params).done((response) => {
							self.loadProject(response.result);
							progressInstance.progressIndicator({ mode: 'hide' });
						});
					}
				}
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
		this.ganttApp.tasks = this.filterProjectData(this.projectData);
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
		app.showModalHtml({
			headerIcon: 'fas fa-filter',
			header: app.vtranslate('JS_FILTER_BY_STATUSES'),
			body: `<div class="js-gantt__filter-modal form" data-js="container">
				<div class="form-group">
					<label>${app.vtranslate('JS_PROJECT_STATUSES')}:</label>
					<select class="select2 form-control js-gantt__filter-project"  multiple>
						${self.statuses.Project.map((status) => {
							return `<option value="${status.value}" ${
								this.filter.status.Project.map((status) => status.value).indexOf(status.value) >= 0 ? 'selected' : ''
							}>${status.label}</option>`;
						})}
					</select>
				</div>
				<div class="form-group">
				<label>${app.vtranslate('JS_MILESTONE_STATUSES')}:</label>
					<select class="select2 form-control js-gantt__filter-milestone" multiple>
						${self.statuses.ProjectMilestone.map((status) => {
							return `<option value="${status.value}" ${
								this.filter.status.ProjectMilestone.map((status) => status.value).indexOf(status.value) >= 0
									? 'selected'
									: ''
							}>${status.label}</option>`;
						})}
					</select>
				</div>
				<div class="form-group">
				<label>${app.vtranslate('JS_TASK_STATUSES')}:</label>
					<select class="select2 form-control js-gantt__filter-task" multiple>
						${self.statuses.ProjectTask.map((status) => {
							return `<option value="${status.value}" ${
								this.filter.status.ProjectTask.map((status) => status.value).indexOf(status.value) >= 0
									? 'selected'
									: ''
							}>${status.label}</option>`;
						})}
					</select>
				</div>
			</div>`,
			footerButtons: [
				{ text: app.vtranslate('JS_UPDATE_GANTT'), icon: 'fas fa-check', class: 'btn-success js-success' },
				{ text: app.vtranslate('JS_CANCEL'), icon: 'fas fa-times', class: 'btn-danger', data: { dismiss: 'modal' } }
			],
			cb: function (modal) {
				modal.on('click', '.js-success', function (e) {
					self.saveFilter({
						status: {
							Project: modal
								.find('.js-gantt__filter-project')
								.val()
								.map((status) => {
									return self.getStatusFromValue(status, 'Project');
								}),
							ProjectMilestone: modal
								.find('.js-gantt__filter-milestone')
								.val()
								.map((status) => {
									return self.getStatusFromValue(status, 'ProjectMilestone');
								}),
							ProjectTask: modal
								.find('.js-gantt__filter-task')
								.val()
								.map((status) => {
									return self.getStatusFromValue(status, 'ProjectTask');
								})
						}
					});
					app.hideModalWindow();
				});
			}
		});
	}

	/**
	 * Register events for gantt actions in current container
	 */
	registerEvents() {
		const container = this.container;
		container.find('[data-toggle="tooltip"]').tooltip();
		window.addEventListener('resize', () => {
			this.resize();
		});
	}
}
