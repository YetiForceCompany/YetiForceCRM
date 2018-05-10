$(document).ready(() => {
	const gantt = new GanttMaster();


	// bind events to buttons
	$('#j-gantt__expand-all-btn').on('click',function(){
		$('#workSpace').trigger('expandAll.gantt');
	});
	$('#j-gantt__collapse-all-btn').on('click', function () {
		$('#workSpace').trigger('collapseAll.gantt');
	});
	$('#j-gantt__zoom-in-btn').on('click', function () {
		$('#workSpace').trigger('zoomPlus.gantt');
	});
	$('#j-gantt__zoom-out-btn').on('click', function () {
		$('#workSpace').trigger('zoomMinus.gantt');
	});
	$('#j-gantt__print-btn').on('click', function () {
		$('#workSpace').trigger('print.gantt');
	});
	$('#j-gantt__show-critical-path-btn').on('click', function () {
		ge.gantt.showCriticalPath = !ge.gantt.showCriticalPath;
		ge.redraw();
	});
	$('#j-gantt__resize-0-btn').on('click', function () {
		ge.splitter.resize(.1);
	});
	$('#j-gantt__resize-50-btn').on('click', function () {
		ge.splitter.resize(50);
	});
	$('#j-gantt__resize-100-btn').on('click', function () {
		ge.splitter.resize(100);
	});
	$('#j-gantt__fullscreen-btn').on('click', function () {
		$('#workSpace').trigger('fullScreen.gantt');
	});
	$.JST.loadDecorator("RESOURCE_ROW", function (resTr, res) {
		resTr.find(".delRes").click(function () {
			$(this).closest("tr").remove()
		});
	});

	$.JST.loadDecorator("ASSIGNMENT_ROW", function (assigTr, taskAssig) {
		var resEl = assigTr.find("[name=resourceId]");
		var opt = $("<option>");
		resEl.append(opt);
		for (var i = 0; i < taskAssig.task.master.resources.length; i++) {
			var res = taskAssig.task.master.resources[i];
			opt = $("<option>");
			opt.val(res.id).html(res.name);
			if (taskAssig.assig.resourceId == res.id)
				opt.attr("selected", "true");
			resEl.append(opt);
		}
		var roleEl = assigTr.find("[name=roleId]");
		for (var i = 0; i < taskAssig.task.master.roles.length; i++) {
			var role = taskAssig.task.master.roles[i];
			var optr = $("<option>");
			optr.val(role.id).html(role.name);
			if (taskAssig.assig.roleId == role.id)
				optr.attr("selected", "true");
			roleEl.append(optr);
		}

		if (taskAssig.task.master.permissions.canWrite && taskAssig.task.canWrite) {
			assigTr.find(".delAssig").click(function () {
				var tr = $(this).closest("[assId]").fadeOut(200, function () {
					$(this).remove()
				});
			});
		}

	});

	function getDemoProject() {
		//console.debug("getDemoProject")
		ret = {
			"tasks": [
				{
					"id": -1,
					"name": "Gantt editor",
					"progress": 0,
					"progressByWorklog": false,
					"relevance": 0,
					"type": "",
					"typeId": "",
					"description": "",
					"code": "",
					"level": 0,
					"status": "STATUS_ACTIVE",
					"depends": "",
					"canWrite": true,
					"start": 1396994400000,
					"duration": 20,
					"end": 1399586399999,
					"startIsMilestone": false,
					"endIsMilestone": false,
					"collapsed": false,
					"assigs": [],
					"hasChild": true
				},
				{
					"id": -2,
					"name": "coding",
					"progress": 0,
					"progressByWorklog": false,
					"relevance": 0,
					"type": "",
					"typeId": "",
					"description": "",
					"code": "",
					"level": 1,
					"status": "STATUS_ACTIVE",
					"depends": "",
					"canWrite": true,
					"start": 1396994400000,
					"duration": 10,
					"end": 1398203999999,
					"startIsMilestone": false,
					"endIsMilestone": false,
					"collapsed": false,
					"assigs": [],
					"hasChild": true
				},
				{
					"id": -3,
					"name": "gantt part",
					"progress": 0,
					"progressByWorklog": false,
					"relevance": 0,
					"type": "",
					"typeId": "",
					"description": "",
					"code": "",
					"level": 2,
					"status": "STATUS_ACTIVE",
					"depends": "",
					"canWrite": true,
					"start": 1396994400000,
					"duration": 2,
					"end": 1397167199999,
					"startIsMilestone": false,
					"endIsMilestone": false,
					"collapsed": false,
					"assigs": [],
					"hasChild": false
				},
				{
					"id": -4,
					"name": "editor part",
					"progress": 0,
					"progressByWorklog": false,
					"relevance": 0,
					"type": "",
					"typeId": "",
					"description": "",
					"code": "",
					"level": 2,
					"status": "STATUS_SUSPENDED",
					"depends": "3",
					"canWrite": true,
					"start": 1397167200000,
					"duration": 4,
					"end": 1397685599999,
					"startIsMilestone": false,
					"endIsMilestone": false,
					"collapsed": false,
					"assigs": [],
					"hasChild": false
				},
				{
					"id": -5,
					"name": "testing",
					"progress": 0,
					"progressByWorklog": false,
					"relevance": 0,
					"type": "",
					"typeId": "",
					"description": "",
					"code": "",
					"level": 1,
					"status": "STATUS_SUSPENDED",
					"depends": "2:5",
					"canWrite": true,
					"start": 1398981600000,
					"duration": 5,
					"end": 1399586399999,
					"startIsMilestone": false,
					"endIsMilestone": false,
					"collapsed": false,
					"assigs": [],
					"hasChild": true
				},
				{
					"id": -6,
					"name": "test on safari",
					"progress": 0,
					"progressByWorklog": false,
					"relevance": 0,
					"type": "",
					"typeId": "",
					"description": "",
					"code": "",
					"level": 2,
					"status": "STATUS_SUSPENDED",
					"depends": "",
					"canWrite": true,
					"start": 1398981600000,
					"duration": 2,
					"end": 1399327199999,
					"startIsMilestone": false,
					"endIsMilestone": false,
					"collapsed": false,
					"assigs": [],
					"hasChild": false
				},
				{
					"id": -7,
					"name": "test on ie",
					"progress": 0,
					"progressByWorklog": false,
					"relevance": 0,
					"type": "",
					"typeId": "",
					"description": "",
					"code": "",
					"level": 2,
					"status": "STATUS_SUSPENDED",
					"depends": "6",
					"canWrite": true,
					"start": 1399327200000,
					"duration": 3,
					"end": 1399586399999,
					"startIsMilestone": false,
					"endIsMilestone": false,
					"collapsed": false,
					"assigs": [],
					"hasChild": false
				},
				{
					"id": -8,
					"name": "test on chrome",
					"progress": 0,
					"progressByWorklog": false,
					"relevance": 0,
					"type": "",
					"typeId": "",
					"description": "",
					"code": "",
					"level": 2,
					"status": "STATUS_SUSPENDED",
					"depends": "6",
					"canWrite": true,
					"start": 1399327200000,
					"duration": 2,
					"end": 1399499999999,
					"startIsMilestone": false,
					"endIsMilestone": false,
					"collapsed": false,
					"assigs": [],
					"hasChild": false
				}
			], "selectedRow": 2, "deletedTaskIds": [],
			"resources": [
				{"id": "tmp_1", "name": "Resource 1"},
				{"id": "tmp_2", "name": "Resource 2"},
				{"id": "tmp_3", "name": "Resource 3"},
				{"id": "tmp_4", "name": "Resource 4"}
			],
			"roles": [
				{"id": "tmp_1", "name": "Project Manager"},
				{"id": "tmp_2", "name": "Worker"},
				{"id": "tmp_3", "name": "Stakeholder"},
				{"id": "tmp_4", "name": "Customer"}
			], "canWrite": true, "canDelete": true, "canWriteOnParent": true, canAdd: true
		}


		//actualize data
		var offset = new Date().getTime() - ret.tasks[0].start;
		for (var i = 0; i < ret.tasks.length; i++) {
			ret.tasks[i].start = ret.tasks[i].start + offset;
		}
		return ret;
	}

	const container = $("#j-gantt");
	gantt.init(container, window.ganttTemplateFunctions); // from GanttContents.tpl
	gantt.loadProject(getDemoProject());
});
