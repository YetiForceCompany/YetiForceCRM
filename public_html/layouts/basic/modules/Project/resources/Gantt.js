$(document).ready(() => {

	const gantt = new GanttMaster(ganttTemplateFunctions);
	const container = $("#j-gantt");
	gantt.init(container);
	//gantt.loadProject(getDemoProject());
	gantt.loadProject(window.ganttData);
	console.log('gantt initialized')

	// bind events to buttons
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
					"canWrite": false,
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
			],
			"canWrite": false, "canDelete": false, "canWriteOnParent": false, canAdd: false
		}


		//actualize data
		var offset = new Date().getTime() - ret.tasks[0].start;
		for (var i = 0; i < ret.tasks.length; i++) {
			ret.tasks[i].start = ret.tasks[i].start + offset;
		}
		return ret;
	}


});
