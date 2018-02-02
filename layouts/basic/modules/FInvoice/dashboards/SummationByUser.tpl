{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<script type="text/javascript">
	YetiForce_Bar_Widget_Js('YetiForce_Summationbyuser_Widget_Js',{}, {
		loadChart: function () {
			var thisInstance = this;
			var chartData = thisInstance.generateData();
			var chartArea = thisInstance.getPlotContainer(false);
			var options = {
				series: {
					bars: {
						show: true,
						//barWidth: 0.4,
					},
				},
				bars: {
					barWidth: .8,
				},
				xaxis: {
					ticks: [],
					autoscaleMargin: .05
				},
				{if $PARAM['showUsers']}
				grid: {
					hoverable: true
				},
				{/if}
				legend: {
					show: false
				}
			};
			thisInstance.plotInstance = $.plot(chartArea, chartData['chartData'], options);
			{if $PARAM['showUsers']}
			chartArea.bind('plothover', function (event, pos, item) {
				if (item) {
					var html = '';
					$("#tooltip").remove();
					var html = '<div id="tooltip">';
					html += item.series.label;
					html += '</div>';
					$(html).css({
						position: 'absolute',
						top: pos.pageY,
						left: pos.pageX + 20,
						border: '1px solid #DAD9D9',
						padding: '2px',
						'z-index': 1050,
						'background-color': '#f5f5f5',
					}).appendTo("body").fadeIn(200);

				} else {
					$("#tooltip").fadeOut();
				}
			});
			{/if}
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
				<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter width90 form-control" value="{implode(',',$DTIME)}" />
			</div>
		</div>
		<div class="col-md-6">

		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/SummationByMonthsContents.tpl', $MODULE_NAME)}
</div>
