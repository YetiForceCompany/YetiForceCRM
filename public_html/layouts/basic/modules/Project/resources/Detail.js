/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Project_Detail_Js", {}, {
	detailViewRecentTicketsTabLabel: 'Trouble Tickets',
	/**
	 * Function to register event for create related record
	 * in summary view widgets
	 */
	registerSummaryViewContainerEvents: function (summaryViewContainer) {
		this._super(summaryViewContainer);
	},
	/**
	 * Function to load module summary of Projects
	 */
	loadModuleSummary: function () {
		var summaryParams = {};
		summaryParams['module'] = app.getModuleName();
		summaryParams['view'] = "Detail";
		summaryParams['mode'] = "showModuleSummaryView";
		summaryParams['record'] = jQuery('#recordId').val();
		AppConnector.request(summaryParams).then(
			function (data) {
				jQuery('.js-widget-general-info').html(data);
			}
		);
	},
	/**
	 * load gantt
	 */
	loadGantt(container = '#c-gantt__container', ganttData = null) {
		let parent = $(container).parent();
		let html = $(container).html();
		$(container).remove();
		container = $(parent).append(html);
		if (!ganttData) {
			let ganttDataStr = $(parent).find('#ganttData').val();
			ganttData = JSON.parse(JSON.parse(ganttDataStr, true), true);
		}
		this.gantt = App.Fields.Gantt.register(container, ganttData);
	},
	registerEvents: function () {
		var detailContentsHolder = this.getContentHolder();
		var thisInstance = this;
		this._super();
		detailContentsHolder.on('click', '.moreRecentTickets', function () {
			var recentTicketsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentTicketsTabLabel);
			recentTicketsTab.trigger('click');
		});
		this.loadGantt();
	}
});
