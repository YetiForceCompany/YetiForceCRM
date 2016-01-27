{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{assign var=DATA value=Zend_Json::decode(html_entity_decode($WIDGET->get('data')))}
<script type="text/javascript">
	YetiForce_Bar_Widget_Js('YetiForce_Summationbymonths_Widget_Js',{}, {
		loadChart: function () {
			var thisInstance = this;
			var chartData = thisInstance.generateData();
			var chartArea = thisInstance.getPlotContainer(false);

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
					{if $DATA['plotTickSize']}
						tickSize: {$DATA['plotTickSize']},
					{/if}
					{if $DATA['plotLimit']}
						max: {$DATA['plotLimit']},
					{/if}
				},
				grid: {
				}
			};
			thisInstance.plotInstance = $.plot(chartArea, chartData['chartData'], options);
		}
	});
</script>
<div class="dashboardWidgetHeader">
	{foreach key=index item=cssModel from=$STYLES}
		<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
	{/foreach}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
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

