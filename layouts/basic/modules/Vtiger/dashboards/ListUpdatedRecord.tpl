{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-md-8">
			<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(),$MODULE_NAME)}</strong></div>
		</div>
		<div class="col-md-4">
			<div class="box pull-right">
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</div>
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row" >
		<div class="col-md-6 pull-right">
			<select class="widgetFilter form-control input-sm" id="listUpdatedRecordLimit" title="{vtranslate('LBL_RECORDS_LIMIT')}" name="number" >
				<option title="{vtranslate('LBL_ALL')}" value="all" >{vtranslate('LBL_ALL')}</option>
				<option title="20" value="10" >10</option>
				<option title="25" value="25" >25</option>
				<option title="50" value="50" >50</option>
			</select>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/ListUpdatedRecordContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
{/strip}
