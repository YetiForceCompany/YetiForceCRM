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
	<input type="hidden" name="page" value="{$PAGING_MODEL->get('page')}" />
	<input type="hidden" name="pageLimit" value="{$LIMIT}" />
	<input type="hidden" name="col" value="{$COLUMNS}" />
	<input type="hidden" name="relatedModule" value="{$RELATED_MODULE_NAME}" />
	<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}" />
	{if $RELATED_MODULE_NAME && $RELATED_RECORDS}
	<!--
		<input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
		<input type="hidden" value="{$ORDER_BY}" id="orderBy">
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
		<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
		<input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
		<input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
	-->
		{assign var=FILENAME value="SummaryWidgetsContent.tpl"}
		{include file=$FILENAME|vtemplate_path:$MODULE RELATED_RECORDS=$RELATED_RECORDS}
	{elseif $PAGING_MODEL->get('nrt') == 1}
		<div class="summaryWidgetContainer">
			<p class="textAlignCenter">{vtranslate('LBL_NO_RELATED',$MODULE)} {vtranslate($RELATED_MODULE_NAME, $RELATED_MODULE_NAME)}</p>
		</div>
	{/if}
	{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
	{if $LIMIT neq 'no_limit' && $NUMBER_OF_RECORDS >= $LIMIT}
		<div class="row">
			<div class="pull-right">
				<a href="" class="moreRecentRecords cursorPointer" data-label-key="{$RELATED_MODULE_NAME}" >{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
			</div>
		</div>
	{/if}
{/strip}
