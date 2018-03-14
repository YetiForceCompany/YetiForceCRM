{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{assign var=CONF_DATA value=\App\Json::decode(html_entity_decode($WIDGET->get('data')))}
<script type="text/javascript">
YetiForce_Bar_Widget_Js('YetiForce_Summationbymonths_Widget_Js',{}, {
	getTooltipsOptions: function getTooltipsOptions(){
		const months = [ "JS_JAN", "JS_FEB", "JS_MAR", "JS_APR", "JS_MAY", "JS_JUN",
				"JS_JUL", "JS_AUG", "JS_SEP", "JS_OCT", "JS_NOV", "JS_DEC" ];
		const fullMonths = [ "JS_JANUARY", "JS_FEBRUARY", "JS_MARCH", "JS_APRIL", "JS_MAY", "JS_JUNE",
				"JS_JULY", "JS_AUGUST", "JS_SEPTEMBER", "JS_OCTOBER", "JS_NOVEMBER", "JS_DECEMBER" ];
		const data = this.chartData;
		return {
			tooltips:{
				callbacks:{
					label:function(item){
						return app.parseNumberToShow(item.yLabel);
					},
					title:function(item){
						return app.vtranslate(fullMonths[item[0].index])+' '+data.years[item[0].datasetIndex];
					},
				}
			},
		};
	},
	getOptions: function getOptions(){
		return {
			legend: {
				display:true,
			},
			scales: {
				 yAxes: [{
					stacked:true,
					ticks:{
						callback:function(label,index,labels){
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
				xAxes:[
					{
						stacked:true
					}
				]
			},
		};
	},
});
</script>
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-md-8">
			<div class="dashboardTitle" title="{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}">
				<strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</strong>
			</div>
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
