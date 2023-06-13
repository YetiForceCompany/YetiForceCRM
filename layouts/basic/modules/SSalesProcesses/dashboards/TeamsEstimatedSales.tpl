{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<script type="text/javascript">
		YetiForce_Bar_Widget_Js('YetiForce_TeamsEstimatedSales_Widget_Js', {}, {
			getBasicOptions: function getBasicOptions() {
				let options = this._super();
				options.legend = null;
				options.tooltip = {
					formatter: function(params, ticket, callback) {
						let value = App.Fields.Double.formatToDisplay(params.value);
						let header = params.seriesName;
						return '<strong>' + header + '</strong><br>' + params.marker + params.data.fullLabel + ': <strong>' + value + '</strong>';
					}
				}

				return options;
			}
		});
		YetiForce_TeamsEstimatedSales_Widget_Js('YetiForce_ActualSalesOfTeam_Widget_Js', {}, {});
	</script>
	<div class="dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters">
			<div class="col-ceq-xsm-6">
				<div class="input-group input-group-sm">
					<div class=" input-group-prepend">
						<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
							<span class="fas fa-calendar-alt"></span>
						</span>
					</div>
					<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter form-control text-center" value="{$DTIME}">
					<span class="input-group-append">
						<span class="input-group-text">
							<input name="compare" class="widgetFilter mr-2" type="checkbox" {if $COMPARE}checked{/if} title="{\App\Language::translate('LBL_COMPARE_TO_LAST_PERIOD', $MODULE_NAME)}">
							<a href="#" class="js-popover-tooltip" data-js="popover" title="" data-placement="top" data-content="{\App\Language::translate('LBL_COMPARE_TO_LAST_PERIOD', $MODULE_NAME)}"><span class="fas fa-info-circle"></span></a>
						</span>
					</span>
				</div>
			</div>
			<div class="col-ceq-xsm-6">
				{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
