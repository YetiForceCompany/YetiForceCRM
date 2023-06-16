{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<script type="text/javascript">
		YetiForce_Bar_Widget_Js(
			'YetiForce_EstimatedValueByStatus_Widget_Js', {}, {
				getBasicOptions: function getBasicOptions() {
					let options = this._super();
					options.tooltip.valueFormatter = (value) => (typeof value === 'number' ? App.Fields.Double.formatToDisplay(value) + ' ' + CONFIG.currencySymbol : value);
					return options;
				},
			}
		);
	</script>
	<div class="dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters">
			<div class="col">
				{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
