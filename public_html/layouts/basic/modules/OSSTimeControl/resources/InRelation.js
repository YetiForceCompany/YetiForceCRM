/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
							barWidth: 0.9,
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
				$(".sumaryRelatedTimeControl .switchChartContainer").toggle(function () {
					$(this).find('[data-fa-i2svg]').removeClass('fa-chevron-up').addClass('fa-chevron-down');
					$(".chartContainer").hide();
				}, function () {
					$(this).find('[data-fa-i2svg]').removeClass('fa-chevron-down').addClass('fa-chevron-up');
					$(".chartContainer").show();
				});
			},
			registerEvents: function () {
				this.chart = $('.sumaryRelatedTimeControl .chartBlock');
				this.loadChart();
				this.registerSwitch();
			}
		});

	}
	var instance = new OSSTimeControl_Calendar_Js();
	instance.registerEvents();
	window.loadInRelationTomeControl = true;
});

