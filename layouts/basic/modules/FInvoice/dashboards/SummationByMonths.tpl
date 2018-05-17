{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{assign var=CONF_DATA value=\App\Json::decode(html_entity_decode($WIDGET->get('data')))}
<script type="text/javascript">
YetiForce_Bar_Widget_Js('YetiForce_SummationByMonths_Widget_Js',{}, {
	getBasicOptions: function getBasicOptions(chartData){
		return {
			legend: {
				display:true,
			},
			scales: {
				 yAxes: [{
					stacked:true,
					ticks: {
						callback: function yAxisTickCallback(label,index,labels) {
							return app.parseNumberToShow(label);
						},
						{if $CONF_DATA['plotTickSize']}
							stepValue: {$CONF_DATA['plotTickSize']},
						{/if}
						{if $CONF_DATA['plotLimit']}
							max: {$CONF_DATA['plotLimit']},
						{/if}
					},
				}],
				xAxes: [
					{
						stacked: true
					}
				]
			},
			tooltips: {
				callbacks: {
					label: function tooltipLabelCallback(item) {
						return app.parseNumberToShow(item.yLabel);
					},
					title: function tooltipTitleCallback(item) {
						return app.vtranslate(App.Fields.Date.fullMonths[item[0].index])+' '+chartData.years[item[0].datasetIndex];
					},
				}
			},
		};
	},
});
</script>
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-md-8">
			<h5 class="dashboardTitle h6" title="{App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), $MODULE_NAME))}">
				<strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</strong>
			</h5>
		</div>
		<div class="col-md-4">
			<div class="box float-right">
				{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<hr class="widgetHr" />
	<div class="row" >
		<div class="col-md-6">
		</div>
		<div class="col-md-6">
			{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/SummationByMonthsContents.tpl', $MODULE_NAME)}
</div>
