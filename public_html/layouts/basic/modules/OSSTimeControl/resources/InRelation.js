/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

jQuery(document).ready(function ($) {
	if (window.loadInRelationTomeControl == undefined) {
		jQuery.Class(
			'OSSTimeControl_Calendar_Js',
			{},
			{
				chart: false,
				loadChart: function () {
					var data = $('.sumaryRelatedTimeControl .widgetData').val();
					if (data == undefined || data == '') {
						return false;
					}
					var jdata = JSON.parse(data);
					if (jdata.datasets.length == 0 || jdata.datasets[0].data.length == 0) {
						return false;
					}

					jdata.datasets[0].datalabels = {
						font: {
							weight: 'bold'
						},
						color: 'white',
						anchor: 'end',
						align: 'start'
					};

					new Chart($(this.chart).find('canvas')[0].getContext('2d'), {
						type: 'bar',
						data: jdata,
						options: {
							tooltips: {
								callbacks: {
									labelColor: function (tooltipItem, chart) {
										return {
											borderColor: jdata.datasets[0].backgroundColor[tooltipItem['index']],
											backgroundColor: jdata.datasets[0].borderColor[tooltipItem['index']]
										};
									},
									title: function ([tooltipItem], chart) {
										return jdata.datasets[0].tooltips[tooltipItem['index']];
									},
									label: function (tooltipItem, chart) {
										return jdata.datasets[0].dataFormatted[tooltipItem['index']];
									}
								}
							},
							legend: {
								display: false
							},
							title: {
								display: true,
								position: 'top',
								text: jdata.title
							},
							maintainAspectRatio: false,
							scales: {
								yAxes: [
									{
										ticks: {
											beginAtZero: true
										}
									}
								]
							}
						}
					});
				},
				registerSwitch: function () {
					$('.switchChartContainer').on('click', function () {
						var chartContainer = $('.chartContainer')[0];
						if ($(chartContainer).is(':visible')) {
							$(this).find('.fas').removeClass('fa-chevron-up').addClass('fa-chevron-down');
							$('.chartContainer').hide();
						} else {
							$(this).find('.fas').removeClass('fa-chevron-down').addClass('fa-chevron-up');
							$('.chartContainer').show();
						}
					});
				},
				registerEvents: function () {
					this.chart = $('.sumaryRelatedTimeControl .chartBlock');
					this.loadChart();
					this.registerSwitch();
				}
			}
		);
	}
	var instance = new OSSTimeControl_Calendar_Js();
	instance.registerEvents();
	window.loadInRelationTomeControl = true;
});
