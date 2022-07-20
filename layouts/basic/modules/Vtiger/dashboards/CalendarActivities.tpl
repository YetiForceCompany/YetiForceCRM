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
{strip}
	{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
	{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
	{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
	<div class="dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME) CLASSNAME="col-md-6"}
			<div class="d-inline-flex">
				{if \App\Privilege::isPermitted($SOURCE_MODULE, 'CreateView')}
					<button class="btn btn-sm btn-light js-widget-quick-create" data-js="click" type="button"
						data-module-name="{$SOURCE_MODULE}"
						aria-label="{\App\Language::translate('LBL_ADD_RECORD')}">
						<span class='fas fa-plus' title="{\App\Language::translate('LBL_ADD_RECORD')}"></span>
					</button>
				{/if}
				<button class="btn btn-light btn-sm ml-1 changeRecordSort"
					title="{\App\Language::translate('LBL_SORT_DESCENDING', $MODULE_NAME)}"
					data-sort="{if isset($DATA['sortorder']) && $DATA['sortorder'] eq 'desc'}asc{else}desc{/if}"
					data-asc="{\App\Language::translate('LBL_SORT_ASCENDING', $MODULE_NAME)}"
					data-desc="{\App\Language::translate('LBL_SORT_DESCENDING', $MODULE_NAME)}">
					<span class="fas fa-sort-amount-down"></span>
				</button>
				{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
			</div>
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters">
			<div class="col-4">
				<div class="input-group input-group-sm">
					<span class="input-group-prepend">
						<span class="input-group-text">
							<span class="fas fa-filter iconMiddle margintop3"
								title="{\App\Language::translate('Assigned To', $MODULE_NAME)}"></span>
						</span>
					</span>
					<select class="widgetFilter select2 form-control" aria-label="Small"
						aria-describedby="inputGroup-sizing-sm" name="activitytype"
						title="{\App\Language::translate('Activity Type',$SOURCE_MODULE)}">
						<option value="all">{\App\Language::translate('LBL_ALL')}</option>
						{foreach item=TYPE from=Calendar_Module_Model::getCalendarTypes()}
							<option value="{\App\Purifier::encodeHtml($TYPE)}" {if $TYPE === $ACTIVITYTYPE} selected{/if}>
								{\App\Language::translate($TYPE,$SOURCE_MODULE)}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="col-4">
				<div class="input-group input-group-sm">
					<span class="input-group-prepend">
						<span class="input-group-text">
							<span class="fas fa-filter iconMiddle margintop3"
								title="{\App\Language::translate('Priority', $MODULE_NAME)}"></span>
						</span>
					</span>
					<select class="widgetFilter select2 form-control" name="taskpriority"
						title="{\App\Language::translate('Priority',$SOURCE_MODULE)}">
						<option value="all">{\App\Language::translate('LBL_ALL')}</option>
						{foreach item=PRIORITY from=App\Fields\Picklist::getValuesName('taskpriority')}
							<option value="{\App\Purifier::encodeHtml($PRIORITY)}" {if PRIORITY === $TASK_PRIORITY} selected{/if}>
								{\App\Language::translate($PRIORITY,$SOURCE_MODULE)}
							</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="col-4">
				{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<div name="history" class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/CalendarActivitiesContents.tpl', $MODULE_NAME) WIDGET=$WIDGET}
	</div>
{/strip}
