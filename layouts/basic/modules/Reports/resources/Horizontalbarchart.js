/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
Report_Verticalbarchart_Js('Report_Horizontalbarchart_Js', {}, {
	generateChartData: function () {
		if (Reports_ChartDetailView_Js.isEmptyData()) {
			Reports_ChartDetailView_Js.displayNoDataMessage();
			return false;
		}

		var jsonData = jQuery('input[name=data]').val();
		var data = this.data = JSON.parse(jsonData);
		var values = data['values'];

		var chartData = [];
		var yMaxValue = 0;

		if (data['type'] == 'singleBar') {
			for (var i in values) {
				var multiValue = values[i];
				chartData[i] = [];
				for (var j in multiValue) {
					chartData[i].push(multiValue[j]);
					chartData[i].push(parseInt(i) + 1);
					if (multiValue[j] > yMaxValue) {
						yMaxValue = multiValue[j];
					}
				}
			}
			chartData = [chartData];
		} else {
			chartData = [];
			chartData[0] = [];
			chartData[1] = [];
			chartData[2] = [];
			for (var i in values) {
				var multiValue = values[i];
				for (var j in multiValue) {
					chartData[j][i] = [];
					chartData[j][i].push(multiValue[j]);
					chartData[j][i].push(parseInt(i) + 1);
					if (multiValue[j] > yMaxValue) {
						yMaxValue = multiValue[j];
					}
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

	},
	loadChart: function () {
		var data = this.generateChartData();
		var labels = data['labels'];

		jQuery.jqplot('chartcontent', data['chartData'], {
			title: data['title'],
			animate: !$.jqplot.use_excanvas,
			seriesDefaults: {
				renderer: $.jqplot.BarRenderer,
				showDataLabels: true,
				pointLabels: {show: true, location: 'e', edgeTolerance: -15},
				shadowAngle: 135,
				rendererOptions: {
					barDirection: 'horizontal'
				}
			},
			axes: {
				yaxis: {
					tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
					renderer: jQuery.jqplot.CategoryAxisRenderer,
					ticks: labels,
					tickOptions: {
						angle: -45
					}
				}
			},
			legend: {
				show: true,
				location: 'e',
				placement: 'outside',
				showSwatch: true,
				showLabels: true,
				labels: data['data_labels']
			}
		});
		jQuery('table.jqplot-table-legend').css('width', '95px');
	},
	postInitializeCalls: function () {
		var thisInstance = this;
		this.getContainer().on("jqplotDataClick", function (ev, gridpos, datapos, neighbor, plot) {
			var linkUrl = thisInstance.data['links'][neighbor[1] - 1];
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
});


Report_Verticalbarchart_Js('Report_Linechart_Js', {}, {
	generateData: function () {

		var jsonData = jQuery('input[name=data]').val();
		var data = this.data = JSON.parse(jsonData);
		var values = data['values'];

		var chartData = [];
		var yMaxValue = 0;

		chartData[1] = []
		chartData[2] = []
		chartData[0] = []
		for (var i in values) {
			var value = values[i];
			for (var j in value) {
				chartData[j].push(value[j]);
			}
		}
		yMaxValue = yMaxValue + yMaxValue * 0.15;

		return {'chartData': chartData,
			'yMaxValue': yMaxValue,
			'labels': data['labels'],
			'data_labels': data['data_labels'],
			'title': data['graph_label']
		};
	},
	loadChart: function () {
		var data = this.generateData();
		var plot2 = $.jqplot('chartcontent', data['chartData'], {
			title: data['title'],
			legend: {
				show: true,
				labels: data['data_labels'],
				location: 'ne',
				showSwatch: true,
				showLabels: true,
				placement: 'outside'
			},
			seriesDefaults: {
				pointLabels: {
					show: true
				}
			},
			axes: {
				xaxis: {
					min: 0,
					pad: 1,
					renderer: $.jqplot.CategoryAxisRenderer,
					ticks: data['labels'],
					tickOptions: {
						formatString: '%b %#d'
					}
				}
			},
			cursor: {
				show: true
			}
		});
		jQuery('table.jqplot-table-legend').css('width', '95px');
	}
});
$( document ).ready(function() {
	(new Reports_ChartDetailView_Js()).registerEvents();
});
