{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{assign var=CONF_DATA value=\App\Json::decode(html_entity_decode($WIDGET->get('data')))}
<script type="text/javascript">
	YetiForce_Bar_Widget_Js('YetiForce_Summationbymonths_Widget_Js',{}, {
		loadChart: function () {
			var thisInstance = this;
			var chartData = thisInstance.generateData();
			var chartArea = thisInstance.getPlotContainer(false);
			var months = [ "JS_JANUARY", "JS_FEBRUARY", "JS_MARCH", "JS_APRIL", "JS_MAY", "JS_JUNE",
					"JS_JULY", "JS_AUGUST", "JS_SEPTEMBER", "JS_OCTOBER", "JS_NOVEMBER", "JS_DECEMBER" ];
		   
			var options = {
				series: {
					bars: {
						show: true,
						barWidth: 0.4,
					},
				},
				xaxis: {
					ticks: chartData['ticks'],
					autoscaleMargin: .05
				},
				yaxis: {
					{if $CONF_DATA['plotTickSize']}
						tickSize: {$CONF_DATA['plotTickSize']},
					{/if}
					{if $CONF_DATA['plotLimit']}
						max: {$CONF_DATA['plotLimit']},
					{/if}
				},
				grid: {
					hoverable: true
				},
			};
			thisInstance.plotInstance = $.plot(chartArea, chartData['chartData'], options);
			chartArea.bind('plothover', function (event, pos, item) {
				if (item) {
					var html = '';
					$("#tooltip").remove();
					var x = item.datapoint[0].toFixed(0),
                        y = item.datapoint[1];
					var html = '<div id="tooltip">';
					html += item.series.label + "<br />" + app.vtranslate(months[x-1]) + "<br />" + y;

					html += '</div>';
					$(html).css( {
								position: 'absolute',
								top:  pos.pageY - 30,
								left: pos.pageX+ 20,
								border: '1px solid #DAD9D9',
								padding: '2px',
								'z-index': 1050,
								'background-color': '#f5f5f5',
							}).appendTo("body").fadeIn(200);
	
				} else {
					$("#tooltip").fadeOut();
				}
			});
		}
	});
</script>
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-md-8">
			<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}">
				<strong>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</strong>
			</div>
		</div>
		<div class="col-md-4">
			<div class="box pull-right">
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</div>
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row" >
		<div class="col-md-6">
		</div>
		<div class="col-md-6">
			{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/SummationByMonthsContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>

