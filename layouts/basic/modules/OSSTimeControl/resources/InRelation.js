/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery(document).ready(function ($) {
	if (window.loadInRelationTomeControl == undefined) {
		jQuery.Class("OSSTimeControl_Calendar_Js", {
		}, {
			chart: false,
			loadChart: function () {
				var data = $('.sumaryRelatedTimeControl .widgetData').val();
				if (data == undefined || data == '') {
					return false;
				}
				var jdata = JSON.parse(data);
				var chartData = [];
				var ticks = [];
				var name = [];
				for (var index in jdata) {
					chartData.push(jdata[index]['data']);
					ticks.push(jdata[index]['initial']);
					name[jdata[index]['name'][0]] = jdata[index]['name'][1];
				}
				var options = {
					xaxis: {
						minTickSize: 1,
						ticks: ticks
					},
					yaxis: {

					},
					grid: {
						hoverable: true,
						//clickable: true
					},
					series: {
						bars: {
							show: true,
							barWidth: .9,
							dataLabels: false,
							align: "center",
							//lineWidth: 0
						},
						valueLabels: {
							show: true,
							showAsHtml: true,
							align: "center",
							valign: 'middle',
						},
						stack: true
					}
				};
				$.plot(this.chart, [chartData], options);
			},
			registerSwitch: function () {
				$( ".sumaryRelatedTimeControl .switchChartContainer" ).toggle(function() {
					$(this).find('.glyphicon').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
					$( ".chartContainer" ).hide();
				}, function() {
					$(this).find('.glyphicon').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
					$( ".chartContainer" ).show();
				});
			},
			registerEvents: function () {
				this.chart = $('.sumaryRelatedTimeControl .chartBlock');
				this.loadChart();
				this.registerSwitch();
			}
		});
		var instance = new OSSTimeControl_Calendar_Js();
		$('div.details div.contents').on('Detail.LoadContents.PostLoad', function (e, data) {
			if ($(data).hasClass('sumaryRelatedTimeControl')) {
				instance.registerEvents();
			}
		});
		$('body').on('LoadRelatedRecordList.PostLoad', function (e, data) {
			if (data.params.relatedModule == 'OSSTimeControl') {
				instance.registerEvents();
			}
		});
		instance.registerEvents();
	}
	window.loadInRelationTomeControl = true;
});

