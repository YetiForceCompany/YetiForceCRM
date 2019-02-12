{if $DATA['show_chart']}
{literal}
	<script type="text/javascript">
		$(document).ready(() => {
			$.Class('YetiForce_ProjectTimeControl_Widget_Js', {}, {
				chart: false,
				loadChart: function () {
					var data = $('.widgetData').val();
					if (data === undefined || data === '') {
						return false;
					}
					var jdata = JSON.parse(data);
					if (jdata.datasets.length === 0 || jdata.datasets[0].data.length === 0) {
						return false;
					}
					jdata.datasets[0].datalabels = {
						font: {
							weight: 'bold'
						},
						color: 'white',
						backgroundColor:'rgba(0,0,0,0.2)',
						anchor: 'end',
						align: 'start',
					};
					new Chart($(this.chart).find("canvas")[0].getContext("2d"), {
						type: 'bar',
						data: jdata,
						options: {
							tooltips: {
								callbacks: {
									labelColor: function (tooltipItem, chart) {
										return {
											borderColor: jdata.datasets[0].backgroundColor[tooltipItem['index']],
											backgroundColor: jdata.datasets[0].borderColor[tooltipItem['index']]
										}
									},
									title: function ([tooltipItem], chart) {
										return jdata.datasets[0].tooltips[tooltipItem['index']];
									},
									label: function (tooltipItem, chart) {
										return jdata.datasets[0].data[tooltipItem['index']];
									}
								}
							},
							legend: {
								display: false,
							},
							title: {
								display: false,
							},
							maintainAspectRatio: false,
							scales: {
								yAxes: [{
									ticks: {
										beginAtZero: true
									}
								}]
							}
						}
					});
				},
				registerSwitch: function () {
					$(".switchChartContainer").on('click', function () {
						const chartContainer = $('.chartContainer').get(0);
						if ($(chartContainer).is(':visible')) {
							$(this).find('.fas').removeClass('fa-chevron-up').addClass('fa-chevron-down');
							$(".chartContainer").hide();
						} else {
							$(this).find('.fas').removeClass('fa-chevron-down').addClass('fa-chevron-up');
							$(".chartContainer").show();
						}
					});
				},
				registerEvents: function () {
					this.chart = $('.widgetChartContainer');
					this.loadChart();
					this.registerSwitch();
				}
			});
			const instance = new YetiForce_ProjectTimeControl_Widget_Js();
			instance.registerEvents();
		});
	</script>
{/literal}
	<div class="container">
		<h4 class="text-center">{\App\Language::translate('LBL_TOTAL_TIME')}</h4>
		<h5 class="text-center">{\App\Language::translate('LBL_USER')}</h5>
		<input class="widgetData" name="data" type="hidden"
			   value="{\App\Purifier::encodeHtml(\App\Json::encode($DATA))}"/>
		<div class="widgetChartContainer chartcontent c-time-employees">
			<canvas></canvas>
		</div>
	</div>
{else}
	<div class="alert alert-warning">
		<p>{\App\Language::translate('LBL_TOTAL_TIME')}  {\App\Language::translate('LBL_USER')}</p>
		{\App\Language::translate('LBL_RECORDS_NO_FOUND')}
	</div>
{/if}
