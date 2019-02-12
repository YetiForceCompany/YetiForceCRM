{if $DATA['show_chart']}
{literal}
	<script>
		$(document).ready(function () {
			function generateData() {
				var jData = $('.chartData').val();
				var data = JSON.parse(jData);
				var chartData = [];

				for (var index in data['chart']) {
					chartData.push(data['chart'][index]);
					chartData[data['chart'][index].id] = data['chart'][index];
				}

				return {'chartData': chartData};
			}

			$(function () {
				var chartData = generateData();
				var css_id = "#timeHelpDesk";
				var options = {
					xaxis: {
						minTickSize: 0,
						ticks: false
					},
					yaxis: {
						min: 0
					},
					series: {
						bars: {
							show: true,
							barWidth: 0.9,
							dataLabels: false,
							align: "center",
							lineWidth: 0
						},
						stack: true
					}
				};
				$.plot($(css_id), chartData['chartData'], options);

				window.onresize = function (event) {
					$.plot($(css_id), chartData['chartData'], options);
				}
			});
		});
		{/literal}
	</script>
	<div style="width: 80%; margin: auto; text-align: center;">{\App\Language::translate('OSSTimeControl','OSSTimeControl')}
		: {\App\Language::translate('LBL_USERS')}<br/>
		<input class="chartData" type="hidden" value='{\App\Purifier::encodeHtml(\App\Json::encode($DATA))}'/>
		<div id="timeHelpDesk" style="height:400px;width:100%;"></div>
	</div>
{else}
	<div class="alert alert-warning">
		{\App\Language::translate('LBL_RECORDS_NO_FOUND')}
	</div>
{/if}

