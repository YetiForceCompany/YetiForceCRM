{if count($DATA['chart'])}
	{literal}
		<script type="text/javascript">
			$(document).ready(function () {

				function generateData() {
					var jData = $('.chartData').val();
					var data = JSON.parse(jData);
					var chartData = [];

					for (var index in data['chart']) {
						chartData.push(data['chart'][index]);
						chartData[data['chart'][index].id] = data['chart'][index];
					}

					return {'chartData': chartData, 'ticks': data['ticks']};
				}
				$(function () {
					var chartData = generateData();
					var css_id = "#timeEmployees";
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
								barWidth: .9,
								dataLabels: false,
								align: "center",
								lineWidth: 0
							},
							stack: true
						}
					};
					$.plot($(css_id), chartData['chartData'], options);
				});
			});
		{/literal}
	</script>
	<div style="width: 80%; margin: auto; text-align:center; margin-bottom:20px;">
		{\App\Language::translate('LBL_TOTAL_TIME')}<br />
		{\App\Language::translate('LBL_USER')}<br />
		<input class="chartData" type="hidden" value='{\App\Purifier::encodeHtml(\App\Json::encode($DATA))}' />
		<div id="timeEmployees" style="height:400px;width:100%;"></div>
	</div>
{else}
	<div class="alert alert-warning">
		<p>{\App\Language::translate('LBL_TOTAL_TIME')}  {\App\Language::translate('LBL_USER')}</p>
		{\App\Language::translate('LBL_RECORDS_NO_FOUND')}
	</div>	
{/if}
