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
{strip}
	{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
	{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
	{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
	<div class="dashboardWidgetHeader">
		<div class="row">
			<div class="col-md-6">
				<h5 class="dashboardTitle h6" title="{App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), $MODULE_NAME))}"><strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</strong></h5>
			</div>
			<div class="col-md-6">
				<div class="box float-right">
					{if \App\Privilege::isPermitted('Calendar', 'CreateView')}
						<a class="btn btn-sm btn-light" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('Calendar');
								return false;" aria-label="{\App\Language::translate('LBL_ADD_RECORD')}" href="#" role="button">
							<span class='fas fa-plus' title="{\App\Language::translate('LBL_ADD_RECORD')}"></span>
						</a>
					{/if}&nbsp;
					<button class="btn btn-light btn-sm changeRecordSort" title="{\App\Language::translate('LBL_SORT_DESCENDING', $MODULE_NAME)}" data-sort="{if $DATA['sortorder'] eq 'desc'}asc{else}desc{/if}" data-asc="{\App\Language::translate('LBL_SORT_ASCENDING', $MODULE_NAME)}" data-desc="{\App\Language::translate('LBL_SORT_DESCENDING', $MODULE_NAME)}">
						<span class="fas fa-sort-amount-down" ></span>
					</button>
					{if $LISTVIEWLINKS}&nbsp;
						<button class="btn btn-light btn-sm goToListView" title="{\App\Language::translate('LBL_GO_TO_RECORDS_LIST', $MODULE_NAME)}" >
							<span class="fas fa-th-list"></span>
						</button>
					{/if}&nbsp;
					{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
				</div>
			</div>
		</div>
		<hr class="widgetHr" />
		<div class="row" >
			<div class="col-md-6">
				<div class="input-group input-group-sm flex-nowrap">
					<span class="input-group-prepend">
						<span class="input-group-text">
							<span class="fas fa-filter iconMiddle margintop3" title="{\App\Language::translate('Assigned To', $MODULE_NAME)}"></span>
						</span>
					</span>
					<div class="select2Wrapper">
						<select class="widgetFilter select2 form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" name="activitytype" title="{\App\Language::translate('Activity Type',$SOURCE_MODULE)}">
							<option value="all">{\App\Language::translate('LBL_ALL')}</option>
							{foreach item=TYPE from=Calendar_Module_Model::getCalendarTypes()}
								<option value="{$TYPE}">{\App\Language::translate($TYPE,$SOURCE_MODULE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<div name="history" class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/CalendarActivitiesContents.tpl', $MODULE_NAME) WIDGET=$WIDGET}
	</div>
{/strip}
