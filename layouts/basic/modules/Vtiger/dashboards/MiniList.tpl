{************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
************************************************************************************}
{strip}
	{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
	{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
	{assign var=CURRENTUSERID value=$USER_MODEL->getId()}
	<div class="tpl-dashboards-Minilist dashboardWidgetHeader">
		<div class="row">
			<div class="col-md-8">
				<h5 class="dashboardTitle h6" title="{App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), $MODULE_NAME))}"><strong>{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</strong></h5>
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
				{if $FILTER_FIELD}
					<div class="widgetFilterByField">
						{include file=\App\Layout::getTemplatePath($FILTER_FIELD->getUITypeModel()->getListSearchTemplateName(), $BASE_MODULE) MODULE=$BASE_MODULE FIELD_MODEL=$FILTER_FIELD SEARCH_INFO=[] USER_MODEL=$USER_MODEL}
					</div>
				{/if}
			</div>
			<div class="col-sm-6">
				{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/MiniListContents.tpl', $MODULE_NAME)}
	</div>
	<div class="dashboardWidgetFooter">
		{include file=\App\Layout::getTemplatePath('dashboards/MiniListFooter.tpl', $MODULE_NAME)}
	</div>
{/strip}
