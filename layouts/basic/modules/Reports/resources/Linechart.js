/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

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

