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
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters justify-content-end">
			<div class="col-ceq-xsm-6 input-group input-group-sm">
				<div class="input-group-prepend">
					<span class="input-group-text">
						<span class="fas fa-filter"></span>
					</span>
				</div>
				<select class="widgetFilter form-control" aria-label="Small" aria-describedby="inputGroup-sizing-sm" id="historyType" title="{\App\Language::translate('LBL_HISTORY_TYPE')}" name="type">
					<option title="{\App\Language::translate('LBL_ALL')}" value="all" {if isset($DATA['type']) && $DATA['type'] eq 'all'}selected{/if}>{\App\Language::translate('LBL_ALL')}</option>
					{if $COMMENTS_MODULE_MODEL->isPermitted('DetailView')}
						<option title="{\App\Language::translate('LBL_COMMENTS')}" value="comments" {if isset($DATA['type']) && $DATA['type'] eq 'comments'}selected{/if}>{\App\Language::translate('LBL_COMMENTS')}</option>{/if}
						<option value="updates" title="{\App\Language::translate('LBL_UPDATES')}" {if isset($DATA['type']) && $DATA['type'] eq 'updates'}selected{/if}>{\App\Language::translate('LBL_UPDATES')}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="dashboardWidgetContent">
			{include file=\App\Layout::getTemplatePath('dashboards/HistoryContents.tpl', $MODULE_NAME)}
		</div>
		{/strip}
