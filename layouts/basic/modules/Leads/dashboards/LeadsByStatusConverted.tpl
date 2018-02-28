{*<!--
/*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
************************************************************************************/
-->*}
<script type="text/javascript">
	YetiForce_Bar_Widget_Js('Vtiger_Leadsbystatusconverted_Widget_Js',{},{
		loadChart:function(){
			const options = {
					maintainAspectRatio: false,
					title: {
						display: false
					},
					legend: {
						display: false
					},
					scales: {
						yAxes: [{
								ticks: {
									beginAtZero: true,
									callback: function (value, index, values) {
										return app.parseNumberToShow(value);
									}
								}
						}],
						xAxes:[{
							ticks:{
								minRotation:75,
							}
						}]
					},
					events: ["mousemove", "mouseout", "click", "touchstart", "touchmove", "touchend"],
				};
			var thisInstance = this;
			var data = thisInstance.generateData();
			thisInstance.applyDefaultDatalabelsConfig(data);
			thisInstance.chartInstance = new Chart(
					thisInstance.getPlotContainer().getContext("2d"),
					{
						type: 'bar',
						data: data,
						options: options,
					}
			);
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
			<div class="dashboardTitle" title="{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</strong></div>
		</div>
		<div class="col-md-4">
			<div class="box float-right">
				{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<hr class="widgetHr" />
	<div class="row" >
		<div class="col-sm-6">
			<div class="input-group input-group-sm">
				<span class=" input-group-prepend">
					<span class="input-group-text">
						<span class="fas fa-calendar-alt iconMiddle margintop3" title="{\App\Language::translate('Created Time', $MODULE_NAME)} &nbsp; {\App\Language::translate('LBL_BETWEEN', $MODULE_NAME)}"></span></span>
				</span>
				<input type="text" name="createdtime" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter form-control textAlignCenter"  value="{implode(',', $DTIME)}" aria-label="Small" aria-describedby="inputGroup-sizing-sm"/>
			</div>
		</div>
		<div class="col-sm-6">
			{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetContents.tpl', $MODULE_NAME)}
</div>
