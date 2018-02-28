{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<script type="text/javascript">
	YetiForce_Bar_Widget_Js('YetiForce_Summationbyuser_Widget_Js',{}, {
		loadChart: function () {
			const thisInstance = this;
			const data =thisInstance.applyDefaultDatalabelsConfig(thisInstance.generateData());
			thisInstance.chartInstance = new Chart(
					thisInstance.getPlotContainer().getContext("2d"),
					{
						type: 'bar',
						data: data,
						options: thisInstance.applyDefaultOptions({
							tooltips:{
								callbacks:{
									title: function tooltipsTitleCallback(tooltipItems,data){
										if(typeof data.fullLabels!=='undefined'){
											return data.fullLabels[tooltipItems[0].index];
										}
										return '';
									}
								}
							}
						}),
					}
			);
		}
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
			<div class="input-group input-group-sm">
				<span class="input-group-prepend">
					<div class="input-group-text">
						<span class="fas fa-calendar-alt iconMiddle "></span>
					</div>
				</span>
				<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter form-control" value="{implode(',',$DTIME)}" />
			</div>
		</div>
		<div class="col-md-6">

		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/SummationByMonthsContents.tpl', $MODULE_NAME)}
</div>
