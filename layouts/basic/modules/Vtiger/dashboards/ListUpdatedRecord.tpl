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
		<div class="row">
			<div class="col-md-8">
				<h5 class="dashboardTitle h6" title="{App\Purifier::encodeHtml(App\Language::translate($WIDGET->getTitle(), $MODULE_NAME))}"><strong>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(),$MODULE_NAME)}</strong></h5>
			</div>
			<div class="col-md-4">
				<div class="box float-right">
					{include file=\App\Layout::getTemplatePath('dashboards/DashboardHeaderIcons.tpl', $MODULE_NAME)}
				</div>
			</div>
		</div>
		<hr class="widgetHr" />
		<div class="row justify-content-end m-0">
			<div class="col-md-6 input-group input-group-sm">
				<div class="input-group-prepend">
					<span class="input-group-text">
						<span class="fas fa-filter"></span>
					</span>
				</div>
				<select class="widgetFilter form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" id="listUpdatedRecordLimit" title="{\App\Language::translate('LBL_RECORDS_LIMIT')}" name="number" >
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
