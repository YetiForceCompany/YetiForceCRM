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
	<div class="tpl-SummaryWidgets js-list__form" data-js="container">
		<input type="hidden" name="page" value="{$PAGING_MODEL->get('page')}"/>
		<input type="hidden" name="pageLimit" value="{$LIMIT}"/>
		<input type="hidden" name="col" value="{$COLUMNS}"/>
		<input type="hidden" name="relatedModule" value="{$RELATED_MODULE_NAME}"/>
		<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}"/>
		<input type="hidden" id="relationId" value="{$RELATION_ID}"/>
		{if $RELATED_MODULE_NAME && $RELATED_RECORDS}
			{include file=\App\Layout::getTemplatePath("SummaryWidgetsContent.tpl", $MODULE) RELATED_RECORDS=$RELATED_RECORDS}
		{elseif $PAGING_MODEL->get('nrt') == 1}
			<div class="summaryWidgetContainer">
				<p class="textAlignCenter">{\App\Language::translate('LBL_NO_RELATED',$MODULE)} {\App\Language::translate($RELATED_MODULE_NAME, $RELATED_MODULE_NAME)}</p>
			</div>
		{/if}
		{if $NO_RESULT_TEXT && $RELATED_ENTIRES_COUNT == 0}
			<div class="summaryWidgetContainer js-no-comments-msg-container p-md-2 p-1">
				<p class="textAlignCenter">{\App\Language::translate('LBL_NO_RECORDS_FOUND',$MODULE_NAME)}</p>
			</div>
		{/if}
		{if $LIMIT neq 0 && $RELATED_ENTIRES_COUNT >= $LIMIT}
			<div class="d-flex py-1">
				<div class="ml-auto">
					{if !$IS_READ_ONLY}
						<button type="button" class="btn btn-primary btn-sm moreRecentRecords" data-label-key="{$RELATED_MODULE_NAME}" data-relation-id="{$RELATION_ID}">
							{\App\Language::translate('LBL_MORE',$MODULE_NAME)}
						</button>
					{else if !empty($PARENT_RECORD)}
						<a href="{$PARENT_RECORD->getDetailViewUrl()}" class="btn btn-primary btn-xs moreRecentRecords" data-label-key="{$RELATED_MODULE_NAME}">
							{\App\Language::translate('LBL_MORE',$MODULE_NAME)}
						</a>
					{/if}
				</div>
			</div>
		{/if}
	</div>
{/strip}
