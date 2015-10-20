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
{assign var=ACCESSIBLE_USERS value=$CURRENTUSER->getAccessibleUsers()}
{assign var=ACCESSIBLE_GROUPS value=$CURRENTUSER->getAccessibleGroups()}
{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-md-8">
			<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</strong></div>
		</div>
		<div class="col-md-4">
			<div class="box pull-right">
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</div>
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row" >
		<div class="col-md-12 widget_header">
			{if $LISTVIEWLINKS}
				<div class="pull-right">&nbsp;
					<button class="btn btn-default btn-sm goToListView">
						<span title="{vtranslate('LBL_GO_TO_RECORDS_LIST', $MODULE_NAME)}" class="glyphicon glyphicon-th-list"></span>
					</button>
				</div>
			{/if}
			<div class="pull-right">&nbsp;
				<button class="btn btn-default btn-sm changeRecordSort" title="{vtranslate('LBL_SORT_DESCENDING', $MODULE_NAME)}" alt="{vtranslate('LBL_SORT_DESCENDING', $MODULE_NAME)}" data-sort="asc" data-asc="{vtranslate('LBL_SORT_ASCENDING', $MODULE_NAME)}" data-desc="{vtranslate('LBL_SORT_DESCENDING', $MODULE_NAME)}">
					<span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true" ></span>
				</button>
			</div>
			<div class="pull-right">
				{include file="dashboards/SelectAccessibleTemplate.tpl"|@vtemplate_path:$MODULE_NAME}
			</div>
			{if $SWITCH}
				<div class="pull-left">
					<input class="switchBtn switchBtnReload status" type="checkbox" checked="" data-size="small" data-label-width="5" data-on-text="{$SWITCH[0].label}" data-off-text="{$SWITCH[1].label}" data-on-val="{$SWITCH[0].name}" data-off-val="{$SWITCH[1].name}" data-urlparams="switchParams">
				</div>
			{/if}
		</div>
	</div>
</div>
<div name="history" class="dashboardWidgetContent">
	{include file="dashboards/CalendarActivitiesContents.tpl"|@vtemplate_path:$MODULE_NAME WIDGET=$WIDGET}
</div>
