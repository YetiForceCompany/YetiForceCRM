{************************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
************************************************************************************}
{assign var=ACCESSIBLE_USERS value=$CURRENTUSER->getAccessibleUsers()}
{assign var=ACCESSIBLE_GROUPS value=$CURRENTUSER->getAccessibleGroups()}
{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-md-8">
			<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle())}"><strong>{vtranslate($WIDGET->getTitle())}</strong></div>
		</div>
		<div class="col-md-4">
			<div class="box pull-right">
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</div>
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row" >
		<div class="col-md-12">
			{if $LISTVIEWLINKS}
				<div class="pull-right">&nbsp;
					<button class="btn btn-default btn-sm goToListView" data-url="{$WIDGET_MODEL->getTargetModuleModel()->getListViewUrl()}" title="{vtranslate('LBL_GO_TO_RECORDS_LIST', $MODULE_NAME)}" >
						<span class="glyphicon glyphicon-th-list"></span>
					</button>
				</div>
			{/if}
			<div class="pull-right">&nbsp;
				<button class="btn btn-default btn-sm changeRecordSort" title="{vtranslate('LBL_SORT_DESCENDING', $MODULE_NAME)}" alt="{vtranslate('LBL_SORT_DESCENDING', $MODULE_NAME)}" data-sort="{if $DATA['sortorder'] eq 'desc'}asc{else}desc{/if}" data-asc="{vtranslate('LBL_SORT_ASCENDING', $MODULE_NAME)}" data-desc="{vtranslate('LBL_SORT_DESCENDING', $MODULE_NAME)}">
					<span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true" ></span>
				</button>
			</div>
			<div class="pull-right">
				<select class="widgetFilter form-control sortFieldName input-sm" name="sortFieldName" title="{vtranslate('LBL_CUSTOM_FILTER')}">
					{foreach item=FIELD from=$WIDGET_MODEL->getHeaders()}
						{assign var="FIELD_VALUE" value=$FIELD->get('table')|cat:'.'|cat:$FIELD->get('name')}
						<option value="{$FIELD_VALUE}" {if $DATA['sortFieldName'] eq $FIELD_VALUE} selected {/if}>{vtranslate($FIELD->get('label'),$BASE_MODULE)}</option>
					{/foreach}
				</select>
			</div>
		</div>
	</div>
</div>

<div class="dashboardWidgetContent">
	{include file="dashboards/ProductsSoldToRenewContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
