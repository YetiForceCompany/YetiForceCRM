{if count($DATA2)}
{literal}
	<script type="text/javascript">
		$(document).ready(function(){
			function generateData() {
				var jData = $('.timeProjectData').val();
				var data = JSON.parse(jData);  
				var chartData = [];
				for(var index in data) {
					chartData.push(data[index]);
					chartData[data[index].id] = data[index];
				}

				return {'chartData':chartData, 'ticks': data['ticks']};
			}
			$(function () {
				var chartData = generateData();
				var css_id = "#timeProject";
				var options = {
					xaxis: {
						minTickSize: 0,
						ticks: false
					},
					yaxis: { 
						min: 0 ,
						tickDecimals: 0,
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
		
			});
		});
{/literal}
	</script>
	<div style="width: 80%; margin: auto; text-align:center;margin-bottom:20px;">
		{vtranslate('LBL_TOTAL_TIME')}<br/>
		{vtranslate('LBL_SUMMARY')}<br/>
		<input class="timeProjectData" type="hidden" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATA2))}' />
		<div id="timeProject" style="height:400px;width:100%;"></div>
	</div>
{else}
	<div class="alert alert-warning">
		<p>{vtranslate('LBL_TOTAL_TIME')} {vtranslate('LBL_SUMMARY')} </p>
		{vtranslate('LBL_RECORDS_NO_FOUND')}
	</div>	
{/if}
