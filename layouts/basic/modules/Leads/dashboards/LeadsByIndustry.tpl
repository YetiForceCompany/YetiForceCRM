{*<!--
/*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
************************************************************************************/
-->*}
<script type="text/javascript">
	YetiForce_Bar_Widget_Js('YetiForce_LeadsByIndustry_Widget_Js', {}, {});
</script>
<div class="dashboardWidgetHeader">
	{assign var=WIDGET_WIDTH value=$WIDGET->getWidth()}
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
				<input type="text" name="createdtime" title="{\App\Language::translate('Created Time', $MODULE_NAME)}"
					class="dateRangeField form-control widgetFilter text-center" value="{implode(',', $DTIME)}"
					aria-label="Small" aria-describedby="inputGroup-sizing-sm" />
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
