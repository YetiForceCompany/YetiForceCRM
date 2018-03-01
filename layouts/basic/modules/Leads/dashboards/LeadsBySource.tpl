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
<div class="dashboardWidgetHeader">
	{assign var=WIDGET_WIDTH value=$WIDGET->getWidth()}
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
	<div class="row">
		<div class="col-sm-6">
			<div class="input-group input-group-sm">
				<span class=" input-group-prepend">
					<span class="input-group-text">
						<span class="fas fa-calendar-alt iconMiddle"></span>
					</span>
				</span>
				<input type="text" name="createdtime" title="{\App\Language::translate('Created Time', $MODULE_NAME)}" class="dateRangeField form-control widgetFilter text-center" value="{implode(' - ', $DTIME)}" aria-label="Small" aria-describedby="inputGroup-sizing-sm"/>
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
