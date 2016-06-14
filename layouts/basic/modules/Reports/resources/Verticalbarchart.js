/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

Vtiger_Barchat_Widget_Js('Report_Verticalbarchart_Js', {}, {
	postInitializeCalls: function () {
		jQuery('table.jqplot-table-legend').css('width', '95px');
		var thisInstance = this;
		this.getContainer().on('jqplotDataClick', function (ev, gridpos, datapos, neighbor, plot) {
			var linkUrl = thisInstance.data['links'][neighbor[0] - 1];
			if (linkUrl)
				window.location.href = linkUrl;
		});

		this.getContainer().on("jqplotDataHighlight", function (evt, seriesIndex, pointIndex, neighbor) {
			$('.jqplot-event-canvas').css('cursor', 'pointer');
		});
		this.getContainer().on("jqplotDataUnhighlight", function (evt, seriesIndex, pointIndex, neighbor) {
			$('.jqplot-event-canvas').css('cursor', 'auto');
		});
	},
	postLoadWidget: function () {
		if (!Reports_ChartDetailView_Js.isEmptyData()) {
			this.loadChart();
		} else {
			this.positionNoDataMsg();
		}
		this.postInitializeCalls();
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
	generateChartData: function () {
		var jsonData = this.getContainer().find('input[name=data]').val();
		var data = this.data = JSON.parse(jsonData);
		var values = data['values'];

		var chartData = [];
		var yMaxValue = 0;

		if (data['type'] == 'singleBar') {
			chartData[0] = [];
			for (var i in values) {
				var multiValue = values[i];
				for (var j in multiValue) {
					chartData[0].push(multiValue[j]);
					if (multiValue[j] > yMaxValue)
						yMaxValue = multiValue[j];
				}
			}
		} else {
			chartData[0] = [];
			chartData[1] = [];
			chartData[2] = [];
			for (var i in values) {
				var multiValue = values[i];
				var info = [];
				for (var j in multiValue) {
					chartData[j].push(multiValue[j]);
					if (multiValue[j] > yMaxValue)
						yMaxValue = multiValue[j];
				}
			}
		}
		yMaxValue = yMaxValue + (yMaxValue * 0.15);

		return {'chartData': chartData,
			'yMaxValue': yMaxValue,
			'labels': data['labels'],
			'data_labels': data['data_labels'],
			'title': data['graph_label']
		};
	}
});
