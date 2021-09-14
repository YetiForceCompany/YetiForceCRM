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
	<div class="tpl-Vtiger-dashboards-ListUpdatedRecord dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters justify-content-end m-0">
			<div class="col-ceq-xsm-6 input-group input-group-sm">
				<div class="input-group-prepend">
					<span class="input-group-text">
						<span class="fas fa-filter"></span>
					</span>
				</div>
				<select class="widgetFilter select2 form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" id="listUpdatedRecordLimit" title="{\App\Language::translate('LBL_RECORDS_LIMIT')}" name="number" >
					<option title="{\App\Language::translate('LBL_ALL')}" value="all" >{\App\Language::translate('LBL_ALL')}</option>
					<option title="20" value="10" >10</option>
					<option title="25" value="25" >25</option>
					<option title="50" value="50" >50</option>
				</select>
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/ListUpdatedRecordContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
