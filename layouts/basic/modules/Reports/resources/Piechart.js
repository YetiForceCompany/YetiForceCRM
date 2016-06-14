/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

Vtiger_Pie_Widget_Js('Report_Piechart_Js', {}, {
	postInitializeCalls: function () {
		var clickThrough = jQuery('input[name=clickthrough]').val();
		if (clickThrough != '') {
			var thisInstance = this;
			this.getContainer().on("jqplotDataClick", function (evt, seriesIndex, pointIndex, neighbor) {
				var linkUrl = thisInstance.data['links'][pointIndex];
				if (linkUrl)
					window.location.href = linkUrl;
			});
			this.getContainer().on("jqplotDataHighlight", function (evt, seriesIndex, pointIndex, neighbor) {
				$('.jqplot-event-canvas').css('cursor', 'pointer');
			});
			this.getContainer().on("jqplotDataUnhighlight", function (evt, seriesIndex, pointIndex, neighbor) {
				$('.jqplot-event-canvas').css('cursor', 'auto');
			});
		}
	},
	postLoadWidget: function () {
		if (!Reports_ChartDetailView_Js.isEmptyData()) {
			this.loadChart();
		} else {
			this.positionNoDataMsg();
		}
	},
	positionNoDataMsg: function () {
		Reports_ChartDetailView_Js.displayNoDataMessage();
	},
	
	getPlotContainer: function (useCache) {
		if (typeof useCache == 'undefined') {
			useCache = false;
		}
		if (this.plotContainer == false || !useCache) {
			var container = this.getContainer();
			this.plotContainer = container.find('#chartcontent');
		}
		return this.plotContainer;
	},
	init: function () {
		this._super(jQuery('#reportContentsDiv'));
	},
	generateData: function () {

		var jsonData = jQuery('input[name=data]').val();
		var data = this.data = JSON.parse(jsonData);
		var values = data['values'];

		var chartData = [];
		for (var i in values) {
			chartData[i] = [];
			chartData[i].push(data['labels'][i]);
			chartData[i].push(values[i]);
		}
		return {'chartData': chartData, 'labels': data['labels'], 'data_labels': data['data_labels'], 'title': data['graph_label']};
	}

});
