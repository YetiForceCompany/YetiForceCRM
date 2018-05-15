class GanttField {

	constructor(container, projectData) {
		this.container = $(container);
		this.projectData = projectData;
		this.registerLanguage();
		this.registerTemplates();
		this.loadProject();
	}

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

	registerTemplates() {
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
					<button id="j-gantt_show-critical-path-btn" class="button textual icon requireCanSeeCriticalPath" title="CRITICAL_PATH"><span class="teamworkIcon">&pound;</span></button>
					<span class="ganttButtonSeparator requireCanSeeCriticalPath"></span>
					<button id="j-gantt__resize-0-btn" class="button textual icon"><span class="teamworkIcon">F</span>
					</button>
					<button id="j-gantt__resize-50-btn" class="button textual icon"><span class="teamworkIcon">O</span>
					</button>
					<button id="j-gantt__resize-100-btn" class="button textual icon"><span class="teamworkIcon">R</span>
					</button>
					<span class="ganttButtonSeparator"></span>
					<button id="j-gantt__fullscreen-btn" class="button textual icon" title="FULLSCREEN"><span class="teamworkIcon">@</span></button>
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
      				<th class="gdfColHeader gdfResizable" style="width:100px">${app.vtranslate("JS_NO.", "Project")}</th>
					<th class="gdfColHeader gdfResizable" style="width:300px">${app.vtranslate("JS_NAME", "Project")}</th>
					<th class="gdfColHeader gdfResizable" style="width:100px">${app.vtranslate("JS_PRIORITY", "Project")}</th>
					<th class="gdfColHeader gdfResizable" style="width:100px">${app.vtranslate("JS_STATUS", "Project")}</th>
					<th class="gdfColHeader gdfResizable" style="width:100px">${app.vtranslate("JS_DURATION_SHORT", "Dni")}</th>
					<th class="gdfColHeader gdfResizable" style="width:100px">%</th>
					<th class="gdfColHeader gdfResizable" style="width:100px">deps</th>
				</tr>
				</thead>
			</table>`;
			}
		});

		this.ganttTemplateFunctions.push({
			type: "TASKROW",
			render(obj) {
				return `<tr id="tid_${obj.id}" taskId="${obj.id}" class="taskEditRow ${obj.isParent() ? 'isParent' : ''} ${obj.collapsed ? 'collapsed' : ''}" level="${obj.level}">
	   			<td class="gdfCell">${obj.no}</td>
				<td class="gdfCell indentCell" style="padding-left:${obj.level * 10 + 18}px;">
					<div class="exp-controller" align="center"></div>
					<input type="text" name="name" value="${obj.name}" placeholder="name" ${obj.canWrite ? 'canWrite' : 'disabled'}>
				</td>
				<td class="gdfCell"><input type="text" name="priority" autocomplete="off" value="${obj.priority_label ? obj.priority_label : ''}"></td>
				<td class="gdfCell"><input type="text" name="status" autocomplete="off" value="${obj.internal_status ? obj.internal_status : ''}"></td>
				<td class="gdfCell"><input type="text" name="duration" autocomplete="off" value="${obj.duration}"></td>
				<td class="gdfCell"><input type="text" name="progress" class="validated" entrytype="PERCENTILE" autocomplete="off" value="${obj.progress ? obj.progress : ''}" ${obj.progressByWorklog ? "readOnly" : ""}></td>
				<td class="gdfCell"><input type="text" name="depends" autocomplete="off" value="${obj.depends}"></td>
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

	loadProject() {
		console.log('initializing gantt...');
		const gantt = new GanttMaster(this.ganttTemplateFunctions);
		gantt.init(this.container);
		gantt.loadProject(this.projectData);
		this.registerEvents();
	}

	registerEvents() {
		const container = this.container;
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
			gantt.gantt.showCriticalPath = !gantt.gantt.showCriticalPath;
			gantt.redraw();
		});
		$('#j-gantt__resize-0-btn', container).on('click', function (e) {
			e.preventDefault();
			gantt.splitter.resize(.1);
		});
		$('#j-gantt__resize-50-btn', container).on('click', function (e) {
			e.preventDefault();
			gantt.splitter.resize(50);
		});
		$('#j-gantt__resize-100-btn', container).on('click', function (e) {
			e.preventDefault();
			gantt.splitter.resize(100);
		});
		$('#j-gantt__fullscreen-btn', container).on('click', function (e) {
			e.preventDefault();
			container.trigger('fullScreen.gantt');
		});
	}

}

App.Fields.Gantt = GanttField;
