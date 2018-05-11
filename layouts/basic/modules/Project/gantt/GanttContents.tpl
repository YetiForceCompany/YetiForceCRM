{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{literal}
	<style>

	</style>
{/literal}
<div id="j-gantt" data-js="container"></div>
{literal}
<script>

	window.ganttData = {/literal}{$DATA}{literal};
	console.log(ganttData);



		let ganttTemplateFunctions = [];

		ganttTemplateFunctions.push({
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

		ganttTemplateFunctions.push({
			type: "TASKSEDITHEAD",
			render(obj) {
				return `<table class="gdfTable" cellspacing="0" cellpadding="0">
				<thead>
				<tr style="height:40px">
					<th class="gdfColHeader" style="width:35px; border-right: none"></th>
      				<th class="gdfColHeader" style="width:25px;"></th>
					<th class="gdfColHeader gdfResizable" style="width:300px">name</th>
					<th class="gdfColHeader gdfResizable" style="width:100px">dur.</th>
					<th class="gdfColHeader gdfResizable" style="width:100px">%</th>
				</tr>
				</thead>
			</table>`;
			}
		});

		ganttTemplateFunctions.push({
			type: "TASKROW",
			render(obj) {
				return `<tr id="tid_${obj.id}" taskId="${obj.id}" class="taskEditRow ${obj.isParent() ? 'isParent' : ''} ${obj.collapsed ? 'collapsed' : ''}" level="${obj.level}">
				<th class="gdfCell edit" align="right" style="cursor:pointer;"><span class="taskRowIndex">(#=obj.getRow()+1#)</span> <span class="teamworkIcon" style="font-size:12px;" >e</span></th>
    			<td class="gdfCell noClip" align="center"><div class="taskStatus cvcColorSquare" status="(#=obj.status#)"></div></td>
				<td class="gdfCell indentCell" style="padding-left:${obj.level * 10 + 18}px;">
					<div class="exp-controller" align="center"></div>
					<input type="text" name="name" value="${obj.name}" placeholder="name" ${obj.canWrite ? 'canWrite' : 'disabled'}>
				</td>
				<td class="gdfCell"><input type="text" name="duration" autocomplete="off" value="${obj.duration}"></td>
				<td class="gdfCell"><input type="text" name="progress" class="validated" entrytype="PERCENTILE" autocomplete="off" value="${obj.progress ? obj.progress : ''}" ${obj.progressByWorklog ? "readOnly" : ""}></td>
			</tr>`;
			}
		});

		ganttTemplateFunctions.push({
			type: "TASKEMPTYROW",
			render(obj) {
				return `<tr class="taskEditRow emptyRow">
				<th class="gdfCell" align="right"></th>
				<td class="gdfCell noClip" align="center"></td>
				<td class="gdfCell"></td>
				<td class="gdfCell"></td>
				<td class="gdfCell"></td>
			</tr>`;
			}
		});

		ganttTemplateFunctions.push({
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


		ganttTemplateFunctions.push({
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

		ganttTemplateFunctions.push({
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

		ganttTemplateFunctions.push({
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

		ganttTemplateFunctions.push({
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

		ganttTemplateFunctions.push({
			type: "RESOURCE_ROW",
			render(obj) {
				return `<tr resId="${obj.id}" class="resRow">
				<td><input type="text" name="name" value="${obj.name}" style="width:100%;" class="formElements"></td>
				<td align="center"><span class="teamworkIcon delRes del" style="cursor: pointer">d</span></td>
			</tr>`;
			}
		});




</script>
{/literal}
{*
<hr/>
<style>
	td {
		padding-left: 10px;
	}
	label {
		display: inline;
	}
	.weekend {
		background: #f4f7f4 !important;
	}
</style>
<div class="gantt_task_scale" style="width:100%;padding:5px 0px 5px 0px;">
	<script type="text/javascript" src="{\App\Layout::getPublicUrl('libraries/gantt/dhtmlxgantt.js')}"></script>
	<table>
		<tr style="run-in">
			<fieldset>
				<legend class="d-none">{\App\Language::translate('LBL_FILTERING',$QUALIFIED_MODULE)}</legend>
				<td><strong>&nbsp;{\App\Language::translate('LBL_FILTERING',$QUALIFIED_MODULE)}:&nbsp;</strong></td>
				<td>
					<input name="filter" id="all" class="filter" type="radio" value="" checked="true"><label for="all"><span>&nbsp;{\App\Language::translate('LBL_ALL_PRIORITY',$QUALIFIED_MODULE)}</span></label>
				</td>
				<td><input name="filter" id="low" class="filter" type="radio" value="PLL_LOW"><label for="low"><span>&nbsp;{\App\Language::translate('LBL_LOW_PRIORITY',$QUALIFIED_MODULE)}</span></label>
				</td>
				<td><input name="filter" id="high" class="filter" type="radio" value="PLL_HIGH"><label for="high"><span>&nbsp;{\App\Language::translate('LBL_HIGH_PRIORITY',$QUALIFIED_MODULE)}</span></label>
				</td>
			</fieldset>
			<fieldset>
				<legend class="d-none">{\App\Language::translate('LBL_ZOOMING',$QUALIFIED_MODULE)}</legend>
				<td><strong><span>|&nbsp;&nbsp;</span> {\App\Language::translate('LBL_ZOOMING',$QUALIFIED_MODULE)}:&nbsp;</strong>
				</td>
				<td>
					<input name="scales" id="days" class="zoom" type="radio" value="trplweek" checked="true"><label for="days"><span>&nbsp;{\App\Language::translate('LBL_DAYS_CHART',$QUALIFIED_MODULE)}</span></label>
				</td>
				<td><input name="scales" id="months" class="zoom" type="radio" value="year"><label for="months"><span>&nbsp;{\App\Language::translate('LBL_MONTHS_CHART',$QUALIFIED_MODULE)}</span></label>
				</td>
			</fieldset>
		</tr>
	</table>
</div>
<div id="gantt_here" style="width:100%;height:600px;"></div>
<script>

	$(document).ready(function () {
		// filtering
		gantt.attachEvent('onBeforeTaskDisplay', function (id, task) {
			if (gantt_filter) {
				value = task.priority;
				if (typeof value !== 'string')
					return false;
				var priorityOption = [value.toUpperCase(), 'PLL_' + value.toUpperCase()];
				if (jQuery.inArray(gantt_filter, priorityOption) === -1)
					return false;
			}
			return true;
		});
		jQuery('.zoom').on('click', function () {
			value = jQuery(this).val();
			switch (value) {
				case 'trplweek':
					gantt.config.scale_unit = 'month';
					gantt.config.date_scale = '%F, %Y';
					gantt.config.scale_height = 50;
					gantt.config.subscales = [
						{
							unit: 'day',
							step: 1,
							date: '%j, %D'
						}
					];
					break;
				case 'year':
					gantt.config.scale_unit = 'month';
					gantt.config.date_scale = '%F';
					gantt.config.scale_height = 50;
					gantt.config.subscales = [
						{
							unit: 'week',
							step: 1,
							date: '#%W'
						}
					];
					break;
			}
			gantt.render();
		});
		var gantt_filter = '';
		jQuery('.filter').on('click', function (node) {
			gantt_filter = jQuery(this).val();
			gantt.refreshData();
		});

		// cell painting
		gantt.templates.task_cell_class = function (item, date) {
			if (date.getDay() === 0 || date.getDay() === 6) {
				return 'weekend';
			}
		};

		gantt.locale.date = {
			month_full: [app.vtranslate('JS_JANUARY'), app.vtranslate('JS_FEBRUARY'), app.vtranslate('JS_MARCH'),
				app.vtranslate('JS_APRIL'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUNE'), app.vtranslate('JS_JULY'),
				app.vtranslate('JS_AUGUST'), app.vtranslate('JS_SEPTEMBER'), app.vtranslate('JS_OCTOBER'),
				app.vtranslate('JS_NOVEMBER'), app.vtranslate('JS_DECEMBER')],
			month_short: [app.vtranslate('JS_JAN'), app.vtranslate('JS_FEB'), app.vtranslate('JS_MAR'),
				app.vtranslate('JS_APR'), app.vtranslate('JS_MAY'), app.vtranslate('JS_JUN'), app.vtranslate('JS_JUL'),
				app.vtranslate('JS_AUG'), app.vtranslate('JS_SEP'), app.vtranslate('JS_OCT'), app.vtranslate('JS_NOV'),
				app.vtranslate('JS_DEC')],
			day_full: [app.vtranslate('JS_SUNDAY'), app.vtranslate('JS_MONDAY'), app.vtranslate('JS_TUESDAY'),
				app.vtranslate('JS_WEDNESDAY'), app.vtranslate('JS_THURSDAY'), app.vtranslate('JS_FRIDAY'),
				app.vtranslate('JS_SATURDAY')],
			day_short: [app.vtranslate('JS_SUN'), app.vtranslate('JS_MON'), app.vtranslate('JS_TUE'),
				app.vtranslate('JS_WED'), app.vtranslate('JS_THU'), app.vtranslate('JS_FRI'),
				app.vtranslate('JS_SAT')]
		};

		gantt.templates.grid_file = function (item) {
			return '<div class="gantt_tree_icon gantt_file ' + 'userIcon-' + item.module + '"></div>';
		},
			gantt.templates.grid_folder = function (item) {
				return '<div class="gantt_tree_icon gantt_folder_' + (item.$open ? 'open' : 'closed') + ' ' + 'userIcon-' + item.module + '"></div>';
			},
			gantt.templates.rightside_text = function (start, end, task) {
				if (task.type === gantt.config.types.milestone) {
					return task.text;
				}
				return '';
			};

		gantt.config.columns = [{
			name: 'text',
			label: app.vtranslate('JS_NAME'),
			width: '*',
			tree: true
		}, {
			name: 'progress',
			label: app.vtranslate('JS_PROGRESS'),
			template: function (obj) {
				if (typeof obj.progress !== 'undefined') {
					return Math.round(obj.progress * 100) + '%';
				}
				return '';
			},
			align: 'center',
		}, {
			name: 'priority',
			label: app.vtranslate('JS_PRIORITY'),
			template: function (obj) {
				if (typeof obj.priority !== 'undefined') {
					return obj.priority_label;
				}
				return '';
			},
			align: 'center',
		}];

		gantt.config.scale_unit = 'month';
		gantt.config.date_scale = '%F, %Y';
		gantt.config.scale_height = 50;

		gantt.config.subscales = [
			{
				unit: 'day',
				step: 1,
				date: '%j, %D'
			}
		];
		gantt._get_safe_type = function (type) {
			if (type === gantt.config.types.milestone) {
				return gantt.config.types.milestone;
			} else if (type === gantt.config.types.project) {
				return gantt.config.types.project;
			}
			return 'task';
		};
		gantt._on_dblclick = function (){};
		gantt.config.drag_links = false;
		gantt.config.drag_progress = false;
		gantt.config.drag_move = false;
		gantt.config.drag_resize = false;

		gantt.init('gantt_here');
		window.ganttData.data = window.ganttData.data.map((item) => {
			if(typeof item.progress!=='undefined' && item.progress!==null) {
				item.progress = item.progress / 100;
			}
			return item;
		});
		gantt.parse(window.ganttData);
	});

</script>
*}
