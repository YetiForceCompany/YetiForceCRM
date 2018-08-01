/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

class Gantt {

	/**
	 * Constructor
	 *
	 * @param {jQUery|HTMLElement|string} container
	 * @param {object} projectData
	 */
	constructor(container, projectData) {
		this.container = $(container);
		this.registerLanguage();
		this.registerTemplates();
		if (typeof projectData !== 'undefined') {
			this.loadProject(projectData);
		}
	}

	/**
	 * Register language translations globally (replace old ones)
	 */
	registerLanguage() {
		GanttMaster.messages.GANTT_ERROR_LOADING_DATA_TASK_REMOVED = app.vtranslate("JS_GANTT_ERROR_LOADING_DATA_TASK_REMOVED", 'Project');
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
		Number.currencyFormat = "###,##0.00";
	}

	/**
	 * Register gantt templates globally
	 *
	 * @returns {string}
	 */
	registerTemplates() {
		const self = this;
		this.ganttTemplateFunctions = [];
		this.ganttTemplateFunctions.push({
			type: "GANTBUTTONS",
			render(obj) {
				return `<div class="ganttButtonBar noprint">
				<div class="buttons">
					<button class="js-gantt__expand-all-btn button textual icon " title="${app.vtranslate('JS_GANTT_EXPAND_ALL')}"><span class="teamworkIcon">6</span></button>
					<button class="js-gantt__collapse-all-btn button textual icon " title="${app.vtranslate('JS_GANTT_COLLAPSE_ALL')}"><span class="teamworkIcon">5</span></button>
					<span class="ganttButtonSeparator"></span>
					<button class="js-gantt__zoom-out-btn button textual icon " title="${app.vtranslate('JS_GANTT_ZOOM_OUT')}"><span class="teamworkIcon">)</span></button>
					<button class="js-gantt__zoom-in-btn button textual icon " title="${app.vtranslate('JS_GANTT_ZOOM_IN')}"><span class="teamworkIcon">(</span></button>
					<span class="ganttButtonSeparator"></span>
					<button class="js-gantt__resize-0-btn button textual icon" title="${app.vtranslate('JS_GANTT_RESIZE_0')}"><span class="teamworkIcon">F</span>
					</button>
					<button class="js-gantt__resize-50-btn button textual icon" title="${app.vtranslate('JS_GANTT_RESIZE_50')}"><span class="teamworkIcon">O</span>
					</button>
					<button class="js-gantt__resize-100-btn button textual icon" title="${app.vtranslate('JS_GANTT_RESIZE_100')}"><span class="teamworkIcon">R</span>
					</button>
					<span class="ganttButtonSeparator"></span>
					<button class="js-gantt__front-filter button textual icon" title="${app.vtranslate('JS_GANTT_FILTER')}"><span class="teamworkIcon">f</span></button>
				</div>
			</div>`;
			}
		});

		this.ganttTemplateFunctions.push({
			type: "TASKSEDITHEAD",
			render(obj) {
				return `<table class="gdfTable" cellspacing="0" cellpadding="0">
				<thead>
				<tr style="height:40px">
      				<th class="gdfColHeader gdfResizable" style="width:80px">${app.vtranslate("JS_NO.", "Project")}</th>
					<th class="gdfColHeader gdfResizable" style="width:300px">${app.vtranslate("JS_NAME")}</th>
					<th class="gdfColHeader gdfResizable" style="width:60px">${app.vtranslate("JS_PRIORITY")}</th>
					<th class="gdfColHeader gdfResizable" style="width:100px">${app.vtranslate("JS_STATUS")}</th>
					<th class="gdfColHeader gdfResizable" style="width:70px">${app.vtranslate("JS_DURATION_SHORT", "Project")}</th>
					<th class="gdfColHeader gdfResizable" style="width:50px">%</th>
					<th class="gdfColHeader gdfResizable" style="width:200px">${app.vtranslate("JS_ASSIGNED", "Project")}</th>
				</tr>
				</thead>
			</table>`;
			}
		});

		this.ganttTemplateFunctions.push({
			type: "TASKROW",
			render(obj) {
				return `<tr id="tid_${obj.id}" taskId="${obj.id}" class="taskEditRow ${obj.isParent() ? 'isParent' : ''} ${obj.collapsed ? 'collapsed' : ''}" level="${obj.level}">
	   			<td class="gdfCell text-center">${obj.no}</td>
				<td class="gdfCell indentCell" style="padding-left:${obj.level * 10 + 18}px;" title="${obj.name}" data-toggle="tooltip">
					<div class="exp-controller" align="center"></div>
					${obj.type === 'project' ? '<span class="fas fa-briefcase"></span>' : obj.type === 'milestone' ? '<i class="fas fa-folder"></i>' : '<i class="fas fa-file"></i>'}
					<input type="text" name="name" value="${obj.name}" placeholder="name" ${obj.canWrite ? 'canWrite' : 'disabled'}>
				</td>
				<td class="gdfCell"><input type="text" name="priority" class="text-center" autocomplete="off" value="${obj.priority_label ? obj.priority_label : ''}"></td>
				<td class="gdfCell"><input type="text" name="status" class="text-center" autocomplete="off" value="${obj.status_label ? obj.status_label : ''}"></td>
				<td class="gdfCell"><input type="text" name="duration" class="text-center" autocomplete="off" value="${obj.duration}"></td>
				<td class="gdfCell"><input type="text" name="progress" class="validated text-center" entrytype="PERCENTILE" autocomplete="off" value="${obj.progress ? obj.progress : ''}" ${obj.progressByWorklog ? "readOnly" : ""}></td>
				<td class="gdfCell"><input type="text" name="assigned" class="text-center" autocomplete="off" value="${obj.assigned_user_name}"></td>
			</tr>`;
			}
		});


		this.ganttTemplateFunctions.push({
			type: "TASKEMPTYROW",
			render(obj) {
				return `<tr class="taskEditRow emptyRow">
				<td class="gdfCell"></td>
				<td class="gdfCell"></td>
				<td class="gdfCell"></td>
				<td class="gdfCell"></td>
				<td class="gdfCell"></td>
				<td class="gdfCell"></td>
				<td class="gdfCell"></td>
			</tr>`;
			}
		});

		this.ganttTemplateFunctions.push({
			type: "TASKBAR",
			render(obj) {
				return `<div class="taskBox taskBoxDiv" taskId="${obj.id}">
				<div class="layout ${obj.hasExternalDep ? 'extDep' : ''}">
					<div class="taskProgress"
						 style="width:${obj.progress > 100 ? 100 : obj.progress}%; background-color:${obj.progress > 100 ? 'red' : 'rgb(153,255,51);'};"></div>
					<div class="milestone ${obj.startIsMilestone ? 'active' : ''}"></div>
					<div class="taskLabel"></div>
					<div class="milestone end ${obj.endIsMilestone ? 'active' : ''}"></div>
				</div>
			</div>`;
			}
		});


		this.ganttTemplateFunctions.push({
			type: "CHANGE_STATUS",
			render(obj) {
				return `<div class="taskStatusBox">
				<div class="taskStatus cvcColorSquare" status="STATUS_ACTIVE" title="Active"></div>
				<div class="taskStatus cvcColorSquare" status="STATUS_DONE" title="Completed"></div>
				<div class="taskStatus cvcColorSquare" status="STATUS_FAILED" title="Failed"></div>
				<div class="taskStatus cvcColorSquare" status="STATUS_SUSPENDED" title="Suspended"></div>
				<div class="taskStatus cvcColorSquare" status="STATUS_WAITING" title="Waiting" style="display: none;"></div>
				<div class="taskStatus cvcColorSquare" status="STATUS_UNDEFINED" title="Undefined"></div>
			</div>`;
			}
		});

		this.ganttTemplateFunctions.push({
			type: "ASSIGNMENT_ROW",
			render(obj) {
				return `<tr taskId="${obj.task.id}" assId="${obj.assig.id}" class="assigEditRow">
				<td><select name="resourceId" class="formElements" ${obj.assig.id.indexOf("tmp_") == 0 ? "" : "disabled"}></select></td>
				<td><select type="select" name="roleId" class="formElements"></select></td>
				<td><input type="text" name="effort" value="${getMillisInHoursMinutes(obj.assig.effort)}" size="5" class="formElements"></td>
				<td align="center"><span class="teamworkIcon delAssig del" style="cursor: pointer">d</span></td>
			</tr>`;
			}
		});

		this.ganttTemplateFunctions.push({
			type: "RESOURCE_ROW",
			render(obj) {
				return `<tr resId="${obj.id}" class="resRow">
				<td><input type="text" name="name" value="${obj.name}" style="width:100%;" class="formElements"></td>
				<td align="center"><span class="teamworkIcon delRes del" style="cursor: pointer">d</span></td>
			</tr>`;
			}
		});
		// must be declared but for now not needed
		this.ganttTemplateFunctions.push({
			type: "TASKEDITHEAD",
			render(obj) {
				return "";
			}
		});
		this.ganttTemplateFunctions.push({
			type: "TASK_EDITOR",
			render(obj) {
				return "";
			}
		});
		this.ganttTemplateFunctions.push({
			type: "RESOURCE_EDITOR",
			render(obj) {
				return "";
			}
		});
		this.ganttTemplateFunctions.push({
			type: "RESOURCE_EDITOR",
			render(obj) {
				return "";
			}
		});
	}

	/**
	 * Make tree from task array
	 *
	 * @param {Array} tasks
	 * @param {Object} parent
	 * @returns {Object}
	 */
	makeTree(tasks, parent = {id: 0}) {
		parent = {...parent};
		if (typeof parent.children === 'undefined') {
			parent.children = [];
		}
		for (let child of tasks) {
			child = {...child};
			if (typeof child.parent === 'undefined') {
				child.parent = 0;
			}
			if (child.parent === parent.id) {
				child = this.makeTree(tasks, child);
				parent.children.push(child);
			}
		}
		return parent;
	}

	/**
	 * Flatten tree to array
	 *
	 * @param {Object} tree
	 * @param {Array} flat
	 * @returns {Array}
	 */
	flattenTree(tree, flat = []) {
		for (let child of tree.children) {
			flat.push(child);
			flat = this.flattenTree(child, flat);
		}
		return flat;
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
			if (task.module !== moduleName || statuses.map(status => status.value).indexOf(task.normalized_status) > -1 || statuses.length === 0) {
				children.push(this.getBranchesWithStatus(moduleName, statuses, task));
			}
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
		this.statuses = this.projectData.statuses;
		this.filter = {status: this.projectData.activeStatuses};
		this.gantt = new GanttMaster(this.ganttTemplateFunctions);
		this.gantt.resourceUrl = '/libraries/jquery-gantt-editor/res/';
		this.gantt.init(this.container);
		this.allTasks = this.projectData.tasks;
		if (this.allTasks.length > 0) {
			this.gantt.loadProject($.extend(true, {}, this.filterProjectData(this.projectData)));
			this.registerEvents();
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
		const container = this.container;
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
		container.find('[data-toggle="tooltip"]').tooltip();
	}
}
