{************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
************************************************************************************}
{strip}
	<!-- tpl-Base-dashboards-Minilist -->
	{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
	{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
	{assign var=CURRENTUSERID value=$USER_MODEL->getId()}
	<div class="dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters">
			<div class="col-ceq-xsm-6">
				{if $FILTER_FIELD}
					{if isset($SEARCH_DETAILS[$FILTER_FIELD->getName()])}
						{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$FILTER_FIELD->getName()]}
					{else}
						{assign var=SEARCH_INFO value=[]}
					{/if}
					<div class="widgetFilterByField">
						{include file=\App\Layout::getTemplatePath($FILTER_FIELD->getUITypeModel()->getListSearchTemplateName(), $BASE_MODULE) MODULE=$BASE_MODULE FIELD_MODEL=$FILTER_FIELD SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL CLASS_SIZE='input-group-sm'}
					</div>
				{/if}
			</div>
			<div class="col-ceq-xsm-6">
				{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME) SOURCE_MODULE=$BASE_MODULE}
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/MiniListContents.tpl', $MODULE_NAME)}
	</div>
	<div class="dashboardWidgetFooter">
		{include file=\App\Layout::getTemplatePath('dashboards/MiniListFooter.tpl', $MODULE_NAME)}
	</div>
	<!-- /tpl-Base-dashboards-Minilist -->
{/strip}
