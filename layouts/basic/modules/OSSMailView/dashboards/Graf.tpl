{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<script type="text/javascript">
	Vtiger_Widget_Js('Vtiger_Graf_Widget_Js', {}, {
		getType: function getType() {
			return 'bar';
		}
	});
</script>
<div class="dashboardWidgetHeader">
	<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME) CLASSNAME="col-md-6"}
		<div class="d-inline-flex">
			{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
		</div>
	</div>
	<hr class="widgetHr" />
	<div class="row no-gutters">
		<div class="col-ceq-xsm-6">
			{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
		</div>
		<div class="col-ceq-xsm-6">
			<div class="input-group input-group-sm">
				<span class="input-group-prepend">
					<span class="input-group-text">
						<span class="fas fa-filter iconMiddle margintop3"
							title="{\App\Language::translate('Assigned To', $MODULE_NAME)}"></span>
					</span>
				</span>
				<select class="widgetFilter select2" id="dateFilter" name="dateFilter" aria-label="Small"
					aria-describedby="inputGroup-sizing-sm">
					<option value="Today">{\App\Language::translate('Today', $MODULE_NAME)}</option>
					<option value="Yesterday">{\App\Language::translate('Yesterday', $MODULE_NAME)}</option>
					<option value="Current week">{\App\Language::translate('Current week', $MODULE_NAME)}</option>
					<option value="Previous week">{\App\Language::translate('Previous week', $MODULE_NAME)}</option>
					<option value="Current month">{\App\Language::translate('Current month', $MODULE_NAME)}</option>
					<option value="Previous month">{\App\Language::translate('Previous month', $MODULE_NAME)}</option>
				</select>
			</div>
		</div>
	</div>
</div>
<div name="history" class="dashboardWidgetContent">
	{include file=\App\Layout::getTemplatePath('dashboards/DashBoardWidgetContents.tpl', $MODULE_NAME)}
</div>
