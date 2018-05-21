/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/


class GanttField {

	/**
	 * Constructor
	 *
	 * @param {jQUery|HTMLElement|string} container
	 * @param {object} projectData
	 */
	constructor(container, projectData) {
		this.container = $(container);
		this.projectData = projectData;
		this.registerLanguage();
		this.registerTemplates();
		this.loadProject();
	}

	/**
	 * Register language translations globally (replace old ones)
	 */
	registerLanguage() {
		GanttMaster.messages = LANG;
		GanttMaster.i18n = LANG;
		Date.monthNames = App.Fields.Date.fullMonthsTranslated.map(month => month);
		Date.monthAbbreviations = App.Fields.Date.monthsTranslated.map(month => month);
		Date.dayAbbreviations = App.Fields.Date.daysTranslated.map(day => day);
		Date.dayNames = App.Fields.Date.fullDaysTranslated.map(day => day);
		Date.firstDayOfWeek = CONFIG.firstDayOfWeekNo;
		Date.defaultFormat = CONFIG.dateFormat;
		Date.today = app.vtranslate('JS_TODAY', 'Project');
		Number.decimalSeparator = CONFIG.currencyDecimalSeparator;
		Number.groupingSeparator = CONFIG.currencyGroupingSeparator;
		Number.currencyFormat = "###,##0.00";
	}

	/**
	 * Get options for filters as template of html option elements
	 *
	 * @returns {string}
	 */
	getStatusFilterOptions() {
		let template = '';
		for (let moduleName in this.projectData.picklists) {
			let picklists = this.projectData.picklists[moduleName];
			for (let picklistName in picklists) {
				let picklist = picklists[picklistName];
				picklist.forEach((item) => {
					template += `<option value="${moduleName}|${picklistName}|${item.value}">${moduleName}->${picklistName}->${item.label}</option>`;
				});
			}
		}
		return template;
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
					<button id="j-gantt__expand-all-btn" class="button textual icon " title="EXPAND_ALL"><span class="teamworkIcon">6</span></button>
					<button id="j-gantt__collapse-all-btn" class="button textual icon " title="COLLAPSE_ALL"><span class="teamworkIcon">5</span></button>
					<span class="ganttButtonSeparator"></span>
					<button id="j-gantt__zoom-out-btn" class="button textual icon " title="zoom out"><span class="teamworkIcon">)</span></button>
					<button id="j-gantt__zoom-in-btn" class="button textual icon " title="zoom in"><span class="teamworkIcon">(</span></button>
					<span class="ganttButtonSeparator"></span>
					<button id="j-gantt__print-btn" class="button textual icon " title="Print"><span class="teamworkIcon">p</span></button>
					<span class="ganttButtonSeparator"></span>
					<button id="j-gantt__resize-0-btn" class="button textual icon"><span class="teamworkIcon">F</span>
					</button>
					<button id="j-gantt__resize-50-btn" class="button textual icon"><span class="teamworkIcon">O</span>
					</button>
					<button id="j-gantt__resize-100-btn" class="button textual icon"><span class="teamworkIcon">R</span>
					</button>
					<span class="ganttButtonSeparator"></span>
					<button id="j-gantt__front-filter" class="button textual icon"><span class="teamworkIcon">f</span></button>
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
					<th class="gdfColHeader gdfResizable" style="width:300px">${app.vtranslate("JS_NAME", "Project")}</th>
					<th class="gdfColHeader gdfResizable" style="width:60px">${app.vtranslate("JS_PRIORITY", "Project")}</th>
					<th class="gdfColHeader gdfResizable" style="width:100px">${app.vtranslate("JS_STATUS", "Project")}</th>
					<th class="gdfColHeader gdfResizable" style="width:70px">${app.vtranslate("JS_DURATION_SHORT", "Dni")}</th>
					<th class="gdfColHeader gdfResizable" style="width:50px">%</th>
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
					${obj.type === 'project' ? '<i class="fas fa-briefcase"></i>' : obj.type === 'milestone' ? '<i class="fas fa-folder"></i>' : '<i class="fas fa-file"></i>'}
					<input type="text" name="name" value="${obj.name}" placeholder="name" ${obj.canWrite ? 'canWrite' : 'disabled'}>
				</td>
				<td class="gdfCell"><input type="text" name="priority" class="text-center" autocomplete="off" value="${obj.priority_label ? obj.priority_label : ''}"></td>
				<td class="gdfCell"><input type="text" name="status" class="text-center" autocomplete="off" value="${obj.internal_status ? obj.internal_status : ''}"></td>
				<td class="gdfCell"><input type="text" name="duration" class="text-center" autocomplete="off" value="${obj.duration}"></td>
				<td class="gdfCell"><input type="text" name="progress" class="validated text-center" entrytype="PERCENTILE" autocomplete="off" value="${obj.progress ? obj.progress : ''}" ${obj.progressByWorklog ? "readOnly" : ""}></td>
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
			type: "TASK_EDITOR",
			render(obj) {
				return `<div class="ganttTaskEditor">
				<h2 class="taskData">Task editor</h2>
				<table cellspacing="1" cellpadding="5" width="100%" class="taskData table" border="0">
					<tr>
						<td width="200" style="height: 80px" valign="top">
							<label for="code">code/short name</label><br>
							<input type="text" name="code" id="code" value="" size=15 class="formElements" autocomplete='off' maxlength=255 style='width:100%' oldvalue="1">
						</td>
						<td colspan="3" valign="top"><label for="name" class="required">name</label><br><input type="text" name="name" id="name" class="formElements" autocomplete='off' maxlength=255 style='width:100%' value="" required="true" oldvalue="1"></td>
					</tr>


					<tr class="dateRow">
						<td nowrap="">
							<div style="position:relative">
								<label for="start">start</label>&nbsp;&nbsp;&nbsp;&nbsp;
								<input type="checkbox" id="startIsMilestone" name="startIsMilestone" value="yes"> &nbsp;<label for="startIsMilestone">is milestone</label>&nbsp;
								<br><input type="text" name="start" id="start" size="8" class="formElements dateField validated date" autocomplete="off" maxlength="255" value="" oldvalue="1" entrytype="DATE">
								<span title="calendar" id="starts_inputDate" class="teamworkIcon openCalendar" onclick="$(this).dateField({inputField:$(this).prevAll(':input:first'),isSearchField:false});">m</span>
							</div>
						</td>
						<td nowrap="">
							<label for="end">End</label>&nbsp;&nbsp;&nbsp;&nbsp;
							<input type="checkbox" id="endIsMilestone" name="endIsMilestone" value="yes"> &nbsp;<label for="endIsMilestone">is milestone</label>&nbsp;
							<br><input type="text" name="end" id="end" size="8" class="formElements dateField validated date" autocomplete="off" maxlength="255" \`value="" oldvalue="1" entrytype="DATE">
							<span title="calendar" id="ends_inputDate" class="teamworkIcon openCalendar" onclick="$(this).dateField({inputField:$(this).prevAll(':input:first'),isSearchField:false});">m</span>
						</td>
						<td nowrap="">
							<label for="duration" class=" ">Days</label><br>
							<input type="text" name="duration" id="duration" size="4" class="formElements validated durationdays" title="Duration is in working days." autocomplete="off" maxlength="255" value="" oldvalue="1" entrytype="DURATIONDAYS">&nbsp;
						</td>
					</tr>

					<tr>
						<td colspan="2">
							<label for="status" class=" ">status</label><br>
							<select id="status" name="status" class="taskStatus" status="${obj.status}" onchange="$(this).attr('STATUS',$(this).val());">
								<option value="STATUS_ACTIVE" class="taskStatus" status="STATUS_ACTIVE">active</option>
								<option value="STATUS_WAITING" class="taskStatus" status="STATUS_WAITING">suspended
								</option>
								<option value="STATUS_SUSPENDED" class="taskStatus" status="STATUS_SUSPENDED">
									suspended
								</option>
								<option value="STATUS_DONE" class="taskStatus" status="STATUS_DONE">completed</option>
								<option value="STATUS_FAILED" class="taskStatus" status="STATUS_FAILED">failed</option>
								<option value="STATUS_UNDEFINED" class="taskStatus" status="STATUS_UNDEFINED">
									undefined
								</option>
							</select>
						</td>

						<td valign="top" nowrap>
							<label>progress</label><br>
							<input type="text" name="progress" id="progress" size="7" class="formElements validated percentile" autocomplete="off" maxlength="255" value="" oldvalue="1" entrytype="PERCENTILE">
						</td>
					</tr>

					</tr>
					<tr>
						<td colspan="4">
							<label for="description">Description</label><br>
							<textarea rows="3" cols="30" id="description" name="description" class="formElements" style="width:100%"></textarea>
						</td>
					</tr>
				</table>

				<h2>Assignments</h2>
				<table cellspacing="1" cellpadding="0" width="100%" id="assigsTable">
					<tr>
						<th style="width:100px;">name</th>
						<th style="width:70px;">Role</th>
						<th style="width:30px;">est.wklg.</th>
						<th style="width:30px;" id="addAssig"><span class="teamworkIcon"
																	style="cursor: pointer">+</span></th>
					</tr>
				</table>

				<div style="text-align: right; padding-top: 20px">
					<span id="saveButton" class="button first" onClick="$(this).trigger('saveFullEditor.gantt');">Save</span>
				</div>

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
			type: "RESOURCE_EDITOR",
			render(obj) {
				return `<div class="resourceEditor" style="padding: 5px;">
				<h2>Project team</h2>
				<table cellspacing="1" cellpadding="0" width="100%" id="resourcesTable">
					<tr>
						<th style="width:100px;">name</th>
						<th style="width:30px;" id="addResource"><span class="teamworkIcon"
																	   style="cursor: pointer">+</span></th>
					</tr>
				</table>

				<div style="text-align: right; padding-top: 20px">
					<button id="resSaveButton" class="button big">Save</button>
				</div>
			</div>`;
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
	}

	/**
	 * Load project
	 */
	loadProject() {
		this.gantt = new GanttMaster(this.ganttTemplateFunctions);
		this.gantt.resourceUrl = '/libraries/jQueryGantt/res/';
		this.gantt.init(this.container);
		this.allTasks = this.projectData.tasks;
		if(this.allTasks.length>0) {
			this.filteredTasks = this.allTasks.map(task => task);
			this.gantt.loadProject(this.projectData);
			this.registerEvents();
		}
	}

	/**
	 * Load new data to gantt
	 * @param data
	 */
	reloadData(data){
		this.gantt.loadProject(data);
	}

	/**
	 * Load active statuses for front filter
	 * @param callback
	 * @returns {$.deffered|Promise}
	 */
	loadStatuses(callback){
		if(typeof this.statuses === 'undefined') {
			return AppConnector.request({
				module: 'Project',
				action: 'Statuses',
			}).then((response)=>{
				return response.result;
			});
		}
		let promise = $.Deferred();
		promise.resolve(this.statuses);
		return promise.promise();
	}

	/**
	 * Open modal with status filters
	 */
	showFiltersModal(){
		console.log(this.statuses);
		const self = this;
		const box = bootbox.dialog({
			show:'false',
			message: `<div class="j-gantt__filter-modal form" data-js="container">
				<div class="form-group">
					<label>${app.vtranslate("JS_PROJECT_STATUSES",'Project')}:</label>
					<select class="select2 form-control" multiple>
						${self.statuses.project.map((status)=>{
							return `<option value="${status.value}">${status.name}</option>`;			
						})}
					</select>
				</div>
				<div class="form-group">
				<label>${app.vtranslate("JS_MILESTONE_STATUSES", 'Project')}:</label>
					<select class="select2 form-control" multiple>
						${self.statuses.milestone.map((status) => {
							return `<option value="${status.value}">${status.name}</option>`;			
						})}
					</select>
				</div>
				<div class="form-group">
				<label>${app.vtranslate("JS_TASK_STATUSES", 'Project')}:</label>
					<select class="select2 form-control" multiple>
						${self.statuses.task.map((status) => {
							return `<option value="${status.value}">${status.name}</option>`;			
						})}
					</select>
				</div>
			</div>`,
			title: '<i class="fas fa-filter"></i> ' + app.vtranslate('JS_FILTER_BY_STATUSES','Project'),
			buttons: {
				success: {
					label: '<span class="fas fa-check mr-1"></span>' + app.vtranslate('JS_UPDATE_GANTT','Project'),
					className: "btn-success",
					callback: function () {
						console.log('save')
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
		$('#j-gantt__expand-all-btn', container).on('click', function (e) {
			e.preventDefault();
			container.trigger('expandAll.gantt');
		});
		$('#j-gantt__collapse-all-btn', container).on('click', function (e) {
			e.preventDefault();
			container.trigger('collapseAll.gantt');
		});
		$('#j-gantt__zoom-in-btn', container).on('click', function (e) {
			e.preventDefault();
			container.trigger('zoomPlus.gantt');
		});
		$('#j-gantt__zoom-out-btn', container).on('click', function (e) {
			e.preventDefault();
			container.trigger('zoomMinus.gantt');
		});
		$('#j-gantt__print-btn', container).on('click', function (e) {
			e.preventDefault();
			container.trigger('print.gantt');
		});
		$('#j-gantt__show-critical-path-btn', container).on('click', function (e) {
			e.preventDefault();
			this.gantt.gantt.showCriticalPath = !gantt.gantt.showCriticalPath;
			this.gantt.redraw();
		}.bind(this));
		$('#j-gantt__resize-0-btn', container).on('click', function (e) {
			e.preventDefault();
			this.gantt.splitter.resize(.1);
		}.bind(this));
		$('#j-gantt__resize-50-btn', container).on('click', function (e) {
			e.preventDefault();
			this.gantt.splitter.resize(50);
		}.bind(this));
		$('#j-gantt__resize-100-btn', container).on('click', function (e) {
			e.preventDefault();
			this.gantt.splitter.resize(100);
		}.bind(this));
		$('#j-gantt__fullscreen-btn', container).on('click', function (e) {
			e.preventDefault();
			container.trigger('fullScreen.gantt');
		}.bind(this));
		$('#j-gantt__front-filter', container).on('click',function(){
			self.loadStatuses().then((statuses)=> {
				self.statuses = statuses;
				self.showFiltersModal();
			});
		});
		$('[data-toggle="tooltip"]').tooltip();
	}
}


jQuery.Class("Vtiger_Gantt_Js", {
	getInstance: function () {
		if (Vtiger_Gantt_Js.listInstance == false) {
			var module = app.getModuleName();
			var parentModule = app.getParentModuleName();
			if (parentModule == 'Settings') {
				var moduleClassName = parentModule + "_" + module + "Gantt_Js";
				if (typeof window[moduleClassName] === "undefined") {
					moduleClassName = module + "Gantt_Js";
				}
				var fallbackClassName = parentModule + "_Vtiger_Gantt_Js";
				if (typeof window[fallbackClassName] === "undefined") {
					fallbackClassName = "Vtiger_Gantt_Js";
				}
			} else {
				moduleClassName = module + "Gantt_Js";
				fallbackClassName = "Vtiger_Gantt_Js";
			}
			if (typeof window[moduleClassName] !== "undefined") {
				var instance = new window[moduleClassName]();
			} else {
				var instance = new window[fallbackClassName]();
			}
			Vtiger_Gantt_Js.ganttInstance = instance;
			return instance;
		}
		return Vtiger_Gantt_Js.ganttInstance;
	},
}, {
	//contains the List View element.
	listViewContainer: false,
	//Contains list view top menu element
	listViewTopMenuContainer: false,
	//Contains list view content element
	listViewContentContainer: false,
	//Contains filter Block Element
	filterBlock: false,
	filterSelectElement: false,
	listSearchInstance: false,
	noEventsListSearch: true,
	getListSearchInstance: function (events) {
		if (events != undefined) {
			this.noEventsListSearch = events;
		}
		if (this.listSearchInstance == false && (this.getListViewContainer().find('.searchField').length || this.getListViewContainer().find('.picklistSearchField').length)) {
			this.listSearchInstance = YetiForce_ListSearch_Js.getInstance(this.getListViewContainer(), this.noEventsListSearch);
		}
		return this.listSearchInstance;
	},
	getListViewContainer: function () {
		if (this.listViewContainer == false) {
			this.listViewContainer = jQuery('div.listViewPageDiv');
		}
		return this.listViewContainer;
	},
	getListViewTopMenuContainer: function () {
		if (this.listViewTopMenuContainer == false) {
			this.listViewTopMenuContainer = jQuery('.listViewTopMenuDiv');
		}
		return this.listViewTopMenuContainer;
	},
	getListViewContentContainer: function () {
		if (this.listViewContentContainer == false) {
			this.listViewContentContainer = jQuery('.listViewContentDiv');
		}
		return this.listViewContentContainer;
	},
	getFilterBlock: function () {
		if (this.filterBlock == false) {
			var filterSelectElement = this.getFilterSelectElement();
			if (filterSelectElement.length <= 0) {
				this.filterBlock = jQuery();
			} else if (filterSelectElement.is('select')) {
				this.filterBlock = filterSelectElement.data('select2').$dropdown;
			}
		}
		return this.filterBlock;
	},
	getFilterSelectElement: function () {

		if (this.filterSelectElement == false) {
			this.filterSelectElement = jQuery('#customFilter');
		}
		return this.filterSelectElement;
	},
	getDefaultParams: function () {
		var params = {
			module: app.getModuleName(),
			action: 'GanttData',
		};
		if (app.getParentModuleName()) {
			params.parent = app.getParentModuleName();
		}
		return params;
	},

	getCurrentCvId: function () {
		return jQuery('#customFilter').find('option:selected').data('id');
	},
	getGanttData(urlParams){
		var aDeferred = $.Deferred();
		if (typeof urlParams === "undefined") {
			urlParams = {};
		}
		var thisInstance = this;
		var progressIndicatorElement = $.progressIndicator({
			'position': 'html',
			'blockInfo': {
				'enabled': true
			}
		});
		var defaultParams = this.getDefaultParams();
		var urlParams = $.extend(defaultParams, urlParams);
		AppConnector.request(urlParams).then(function (data) {
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			aDeferred.resolve(data);
			app.notifyPostAjaxReady();
		}, function (textStatus, errorThrown) {
			aDeferred.reject(textStatus, errorThrown);
		});
		return aDeferred.promise();
	},
	/**
	 * load gantt
	 */
	loadGantt(container = '#c-gantt__container', ganttData = false) {
		let parent = $(container).parent();
		let html = $(container).html();
		$(container).remove();
		container = $(parent).append(html);
		if (!ganttData) {
			let ganttDataStr = $(parent).find('#ganttData').val();
			ganttData = JSON.parse(ganttDataStr, true);
		}
		this.gantt = App.Fields.Gantt.register(container, ganttData);
	},
	reloadData(data){
		this.gantt.reloadData(data);
	},
	/*
	 * Function to register the event for changing the custom Filter
	 */
	registerChangeCustomFilterEvent: function () {
		var thisInstance = this;
		this.getFilterSelectElement().on('change', function (event) {
			$(`.nav-item[data-cvid='${thisInstance.getCurrentCvId()}'] .nav-link`).tab('show');
			var currentTarget = jQuery(event.currentTarget);
			var selectOption = currentTarget.find(':selected');
			app.setMainParams('pageNumber', '1');
			app.setMainParams('pageToJump', '1');
			app.setMainParams('orderBy', selectOption.data('orderby'));
			app.setMainParams('sortOrder', selectOption.data('sortorder'));
			var urlParams = {
				"viewname": jQuery(this).val(),
			};
			thisInstance.getGanttData(urlParams).then(function (data) {
				thisInstance.breadCrumbsFilter(selectOption.text());
				thisInstance.reloadData(data.result);
			});
			event.stopPropagation();
		});
	},
	breadCrumbsFilter: function (text) {
		var breadCrumbs = jQuery('.breadcrumbsContainer');
		var breadCrumbsLastSpan = breadCrumbs.last('span');
		var filterExist = breadCrumbsLastSpan.find('.breadCrumbsFilter');
		if (filterExist.length && text != undefined) {
			filterExist.text(' [' + app.vtranslate('JS_FILTER') + ': ' + text + ']');
		} else if (filterExist.length < 1) {
			text = (text == undefined) ? this.getFilterSelectElement().find(':selected').text() : text;
			if (breadCrumbsLastSpan.hasClass('breadCrumbsFilter')) {
				breadCrumbsLastSpan.text(': ' + text);
			} else {
				breadCrumbs.append('<small class="breadCrumbsFilter hideToHistory p-1 js-text-content" data-js="text"> [' + app.vtranslate('JS_FILTER') + ': ' + text + ']</small>');
			}
		}
	},
	ListViewPostOperation: function () {
		return true;
	},

	/*
	 * function to register the click event event for create filter
	 */
	registerCreateFilterClickEvent: function (event) {
		var thisInstance = this;
		//to close the dropdown
		thisInstance.getFilterSelectElement().data('select2').close();
		var currentElement = jQuery(event.currentTarget);
		var liElement = currentElement.find('#createFilter');
		var createUrl = liElement.data('createurl');
		Vtiger_CustomView_Js.loadFilterView(createUrl);
	},

	/*
	 * Function to register the hover event for customview filter options
	 */
	registerCustomFilterOptionsHoverEvent: function () {
		var thisInstance = this;
		var listViewTopMenuDiv = this.getListViewTopMenuContainer();
		var filterBlock = this.getFilterBlock()
		if (filterBlock != false) {
			filterBlock.on('mouseenter mouseleave', 'li.select2-results__option[role="treeitem"]', function (event) {
				var liElement = $(event.currentTarget);
				var liFilterImages = liElement.find('.filterActionImgs');
				if (liElement.hasClass('group-result')) {
					return;
				}

				if (event.type === 'mouseenter') {
					if (liFilterImages.length > 0) {
						liFilterImages.show();
					} else {
						thisInstance.performFilterImageActions(liElement);
					}

				} else {
					liFilterImages.hide();
				}
			});
		}
	},
	performFilterImageActions: function (liElement) {
		jQuery('.filterActionImages').clone(true, true).removeClass('filterActionImages').addClass('filterActionImgs').appendTo(liElement).removeClass('d-none');
		var currentOptionElement = this.getSelectOptionFromChosenOption(liElement);
		var deletable = currentOptionElement.data('deletable');
		if (deletable != '1') {
			liElement.find('.deleteFilter').remove();
		}
		var editable = currentOptionElement.data('editable');
		if (editable != '1') {
			liElement.find('.editFilter').remove();
		}
		var pending = currentOptionElement.data('pending');
		if (pending != '1') {
			liElement.find('.approveFilter').remove();
		}
		var approve = currentOptionElement.data('public');
		if (approve != '1') {
			liElement.find('.denyFilter').remove();
		}
		if ($("#createFilter").length == 0) {
			liElement.find('.duplicateFilter').remove();
		}
	},
	getSelectOptionFromChosenOption: function (liElement) {
		var id = liElement.attr("id");
		var idArr = id.split("-");
		var currentOptionId = '';
		if (idArr.length > 0) {
			currentOptionId = idArr[idArr.length - 1];
		} else {
			return false;
		}
		return jQuery('#filterOptionId_' + currentOptionId);
	},
	changeCustomFilterElementView: function () {
		var thisInstance = this;
		var filterSelectElement = this.getFilterSelectElement();
		if (filterSelectElement.length > 0 && filterSelectElement.is("select")) {
			App.Fields.Picklist.showSelect2ElementView(filterSelectElement, {
				templateSelection: function (data) {
					var resultContainer = jQuery('<span></span>');
					resultContainer.append(jQuery(jQuery('.filterImage').clone().get(0)).show());
					resultContainer.append(data.text);
					return resultContainer;
				},
				customSortOptGroup: true,
				closeOnSelect: true
			});

			var select2Instance = filterSelectElement.data('select2');
			jQuery('.filterActionsDiv').appendTo(select2Instance.$dropdown.find('.select2-dropdown:last')).removeClass('d-none').on('click', function (e) {
				thisInstance.registerCreateFilterClickEvent(e);
			});
		}
	},
	registerEvents: function () {
		this.breadCrumbsFilter();
		//this.registerHeadersClickEvent();
		this.changeCustomFilterElementView();
		this.registerChangeCustomFilterEvent();
		this.registerCustomFilterOptionsHoverEvent();
		//this.triggerDisplayTypeEvent();
		Vtiger_Helper_Js.showHorizontalTopScrollBar();
		this.getListSearchInstance(false);
		this.loadGantt();
	},
});

