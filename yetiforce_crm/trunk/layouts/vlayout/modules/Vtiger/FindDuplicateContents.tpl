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
<input type="hidden" id="listViewEntriesCount" value="{$LISTVIEW_ENTRIES_COUNT}" />
<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
<input type="hidden" id="pageNumber" value= "{$PAGE_NUMBER}"/>
<input type="hidden" id="pageLimit" value= "{$PAGING_MODEL->getPageLimit()}" />
<input type="hidden" id="noOfEntries" value= "{$LISTVIEW_ENTRIES_COUNT}" />
<input type="hidden" id="duplicateSearchFields" value={Zend_Json::encode($DUPLICATE_SEARCH_FIELDS)} />
<input type="hidden" id="viewName" value="{$VIEW_NAME}" />
<input type="hidden" id="totalCount" value="{$TOTAL_COUNT}" />
<input type='hidden' id='ignoreEmpty' value="{$IGNORE_EMPTY}" />

<div id="selectAllMsgDiv" class="alert-block msgDiv">
	<strong><a id="selectAllMsg">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
</div>
<div id="deSelectAllMsgDiv" class="alert-block msgDiv">
	<strong><a id="deSelectAllMsg">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
</div>
<div class="contents-topscroll">
	<div class="topscroll-div">
		&nbsp;
	 </div>
</div>
<div class="listViewEntriesDiv contents-bottomscroll">
	<table class="table-bordered row-fluid textAlignCenter">
		<thead>
			<tr class="listViewHeaders">
				<th width="5%">
					<input type="checkbox" id="listViewEntriesMainCheckBox" />
				</th>
				{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
				<th nowrap {*if $LISTVIEW_HEADER@last} colspan="2" {/if*}>
					<a class="listViewHeaderValues">{vtranslate($LISTVIEW_HEADER->get('label'), $MODULE)}</a>
				</th>
				{/foreach}
				<th>{vtranslate('LBL_MERGE_SELECT', $MODULE)}</th>
				<th>{vtranslate('LBL_ACTION', $MODULE)}</th>
			</tr>
		</thead>
		{assign var=mergeRecordCount value=0}
		{foreach item=LISTVIEW_ENTRY key=GROUP_NAME from=$LISTVIEW_ENTRIES}
			{assign var=groupCount value=$LISTVIEW_ENTRY|@sizeof}
			{assign var=recordCount value=0}
			{foreach item=RECORD from=$LISTVIEW_ENTRY name=listview}
				<tr class="listViewEntries" data-id='{$RECORD.recordid}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
					<td width="5%" style='border-bottom:1px solid #DDD;'>
						<input type="checkbox" value="{$RECORD.recordid}" class="listViewEntriesCheckBox"/>
					</td>
					{assign var=sameRowValues value=true}
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
					{if $LISTVIEW_HEADER->get('name') eq 'recordid'}
						<td nowrap style='border-bottom:1px solid #DDD;'>
							<a href="{$MODULE_MODEL->getDetailViewUrl($RECORD.recordid)}">{$RECORD[$LISTVIEW_HEADER->get('name')]}</a>
						</td>
					{else}
						<td nowrap style='border-bottom:1px solid #DDD;'>
							{$LISTVIEW_HEADER->getDisplayValue($RECORD[$LISTVIEW_HEADER->get('column')], $RECORD.recordid)}
						</td>
					{/if}
					{/foreach}
					<td style='border-bottom:1px solid #DDD;'>
						<input type="checkbox" data-id='{$RECORD.recordid}' name="mergeRecord" data-group="{$GROUP_NAME}"/>
					</td>
					{if $recordCount eq 0}
					<td align='center' rowspan="{$groupCount}" style="border-left:1px solid #DDD;border-bottom:1px solid #DDD;vertical-align: middle;text-align: center">
						<input type="button" value="{vtranslate("LBL_MERGE",'Vtiger')}" name="merge" class="btn btn-success" data-group="{$GROUP_NAME}">
					</td>
					{/if}
					{assign var=recordCount value=$recordCount+1}
				</tr>
			{/foreach}
		{/foreach}
	</table>
	{if $LISTVIEW_ENTRIES_COUNT eq '0'}
	<table class="emptyRecordsDiv">
		<tbody>
			<tr>
				<td>
					{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
					{vtranslate('LBL_NO_DUPLICATED_FOUND', $MODULE)}
				</td>
			</tr>
		</tbody>
	</table>
	{/if}
	</div>
