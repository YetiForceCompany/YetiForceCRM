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
{assign var="totalCount" value=0}
{assign var="totalModulesSearched" value=count($MATCHING_RECORDS)}
{foreach key=module item=searchRecords from=$MATCHING_RECORDS}
    {assign var=modulesCount value=count($searchRecords)}
    {assign var="totalCount" value=$totalCount+$modulesCount}
{/foreach}
<div class="globalSearchResults">
	<div class="row-fluid">
		<div class="header highlightedHeader padding1per">
			<div class="row-fluid">
				<span class="span6"><strong>{vtranslate('LBL_SEARCH_RESULTS',$MODULE)}&nbsp;({$totalCount})</strong></span>
				{if $IS_ADVANCE_SEARCH }
				<span class="span6">
					<span class="pull-right">
						<a href="javascript:void(0);" id="showFilter">{vtranslate('LBL_SAVE_MODIFY_FILTER',$MODULE)}</a>
					</span>
				</span>
				{/if}
			</div>
		</div>
		<div class="contents">
			{if $totalCount eq 100}
				<div class='alert alert-block'>
					<button type=button class="close" data-dismiss="alert">&times;</button>
					{if $SEARCH_MODULE}
						{vtranslate('LBL_GLOBAL_SEARCH_MAX_MESSAGE_FOR_MODULE', 'Vtiger')}
					{else}
						{vtranslate('LBL_GLOBAL_SEARCH_MAX_MESSAGE', 'Vtiger')}
					{/if}
				</div>
			{/if}
		{foreach key=module item=searchRecords from=$MATCHING_RECORDS name=matchingRecords}
			{assign var="modulesCount" value=count($searchRecords)}
			<form method="POST" action="index.php?module={$module}&view=List" name="form_{$module}"  enctype="multipart/form-data">
				<input type="hidden" id="recordList" name="searchResult" value="{Zend_Json::encode(array_keys($searchRecords))}" />
				<label class="clearfix">
					<span onclick="form_{$module}.submit()"><i class="icon-list" style="margin-top: 2px;"></i> <strong>{vtranslate($module)}&nbsp;({$modulesCount})</strong></span>
					<!-- &nbsp;&nbsp;<i title="" class="icon-th-list alignMiddle"></i> -->
					{if {$smarty.foreach.matchingRecords.index+1} eq 1}
						<span class="pull-right"><p class="muted">{vtranslate('LBL_CREATED_ON', $MODULE)}</small></p></span>
					{/if}
					
				</label>
				<ul class="nav">
				{foreach item=recordObject from=$searchRecords name=globalSearch}
					{assign var="ID" value="{$module}_globalSearch_row_{$smarty.foreach.globalSearch.index+1}"}
					{assign var=DETAILVIEW_URL value=$recordObject->getDetailViewUrl()}
					<li id="{$ID}">
						<a target="_blank" id="{$ID}_link" class="cursorPointer" {if stripos($DETAILVIEW_URL, 'javascript:')===0} 
								onclick='{$DETAILVIEW_URL|substr:strlen("javascript:")}' {else} onclick='window.location.href="{$DETAILVIEW_URL}"' {/if}>{$recordObject->getName()}
							<span id="{$ID}_time" class="pull-right" title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($recordObject->get('createdtime'))}">{Vtiger_Util_Helper::formatDateDiffInStrings($recordObject->get('createdtime'))}</span>
						</a>
					</li>
				{foreachelse}
					<li>{vtranslate('LBL_NO_RECORDS', $module)}</li>
				{/foreach}
				</ul>
			</form>	
		{/foreach}
		</div>
	</div>
</div>
{/strip}
