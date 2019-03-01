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
							return App.Fields.Double.formatToDisplay(label);
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
						return App.Fields.Double.formatToDisplay(item.yLabel);
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
	<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
	</div>
	<hr class="widgetHr" />
	<div class="row no-gutters" >
		<div class="col-ceq-xsm-6">
		</div>
		<div class="col-ceq-xsm-6">
			{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/SummationByMonthsContents.tpl', $MODULE_NAME)}
</div>
