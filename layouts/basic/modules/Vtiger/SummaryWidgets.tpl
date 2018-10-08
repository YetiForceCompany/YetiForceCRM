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
	<div class="tpl-SummaryWidgets">
		<input type="hidden" name="page" value="{$PAGING_MODEL->get('page')}"/>
		<input type="hidden" name="pageLimit" value="{$LIMIT}"/>
		<input type="hidden" name="col" value="{$COLUMNS}"/>
		<input type="hidden" name="relatedModule" value="{$RELATED_MODULE_NAME}"/>
		<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}"/>
		{if $RELATED_MODULE_NAME && $RELATED_RECORDS}
			{include file=\App\Layout::getTemplatePath("SummaryWidgetsContent.tpl", $MODULE) RELATED_RECORDS=$RELATED_RECORDS}
		{elseif $PAGING_MODEL->get('nrt') == 1}
			<div class="summaryWidgetContainer">
				<p class="textAlignCenter">{\App\Language::translate('LBL_NO_RELATED',$MODULE)} {\App\Language::translate($RELATED_MODULE_NAME, $RELATED_MODULE_NAME)}</p>
			</div>
		{/if}
		{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
		{if $NUMBER_OF_RECORDS == 0}
			<div class="summaryWidgetContainer js-no-comments-msg-container">
				<p class="textAlignCenter">{\App\Language::translate('LBL_NO_RECORDS_FOUND',$MODULE_NAME)}</p>
			</div>
		{/if}
		{if !$IS_READ_ONLY && $LIMIT neq 0 && $NUMBER_OF_RECORDS >= $LIMIT}
			<div class="d-flex py-1">
				<div class="ml-auto">
					<button type="button" class="btn btn-primary btn-sm moreRecentRecords"
							data-label-key="{$RELATED_MODULE_NAME}">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}</button>
				</div>
			</div>
		{/if}
	</div>
{/strip}
