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
<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
<input type="hidden" id="numberOfEntries" value= "{$LISTVIEW_ENTRIES_COUNT}" />
<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}" />
<input type='hidden' id='pageNumber' value="{$PAGE_NUMBER}">
<input type='hidden' id='pageLimit' value="{$PAGING_MODEL->getPageLimit()}">
<input type="hidden" id="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}">
<input type="hidden" id="deletedRecordsTotalCount" value="{$DELETED_RECORDS_TOTAL_COUNT}">  
<input type="hidden" id="listMaxEntriesMassEdit" value="{vglobal('listMaxEntriesMassEdit')}" />

<div id="selectAllMsgDiv" class="alert-block msgDiv">
	<strong><a id="selectAllMsg">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($SOURCE_MODULE ,$SOURCE_MODULE)}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
</div>
<div id="deSelectAllMsgDiv" class="alert-block msgDiv">
	<strong><a id="deSelectAllMsg">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
</div>
<div class="contents-topscroll noprint stick" data-position="top">
	<div class="topscroll-div"></div>
</div>
<div class="listViewEntriesDiv contents-bottomscroll">
	<div class="bottomscroll-div">
	<input type="hidden" value="{$ORDER_BY}" id="orderBy">
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
	<span class="listViewLoadingImageBlock hide modal" id="loadingListViewModal">
		<img class="listViewLoadingImage" src="{vimage_path('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}"/>
		<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
	</span>
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<table class="table table-bordered listViewEntriesTable {$WIDTHTYPE}">
		<thead>
			<tr class="listViewHeaders">
				<th width="5%">
					<input type="checkbox" title="{vtranslate('LBL_SELECT_ALL')}" id="listViewEntriesMainCheckBox" />
				</th>
				{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
				<th {if $LISTVIEW_HEADER@last}colspan="2"{/if} class="noWrap {if $COLUMN_NAME eq $LISTVIEW_HEADER->get('column')}columnSorted{/if}">
					<a href="javascript:void(0);" class="listViewHeaderValues" {if $LISTVIEW_HEADER->isListviewSortable()}data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}"{/if} data-columnname="{$LISTVIEW_HEADER->get('column')}">
						{vtranslate($LISTVIEW_HEADER->get('label'), $SOURCE_MODULE)}
						&nbsp;&nbsp;
						{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('column')}
							<span class="{$SORT_IMAGE}"></span>
						{/if}
					</a>
				</th>
				{/foreach}
			</tr>
		</thead>
		{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
		<tr class="listViewEntries" data-id='{$LISTVIEW_ENTRY->getId()}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
            <td  width="5%" class="{$WIDTHTYPE}">
				<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" title="{vtranslate('LBL_SELECT_SINGLE_ROW')}" class="listViewEntriesCheckBox"/>
			</td>
			{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
			{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
			<td class="listViewEntryValue {$WIDTHTYPE}" nowrap>
				{if $LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4'}
					<a {if $LISTVIEW_HEADER->isNameField() eq true}class="moduleColor_{$MODULE}"{/if} href="{$LISTVIEW_ENTRY->getDetailViewUrl()}">{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}</a>
				{else}
					{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}
				{/if}
				{if $LISTVIEW_HEADER@last}
				</td><td nowrap class="{$WIDTHTYPE}">
				<div class="pull-right actions">
					<span class="actionImages">
						<a class="restoreRecordButton"><i title="{vtranslate('LBL_RESTORE', $MODULE)}" class="glyphicon glyphicon-refresh alignMiddle"></i></a>&nbsp;
						<a class="deleteRecordButton"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></i></a>
					</span>
				</div>
				</td>
				{/if}
			</td>
			{/foreach}
		</tr>
		{/foreach}
	</table>

<!--added this div for Temporarily -->
{if $LISTVIEW_ENTRIES_COUNT eq '0'}
	<table class="emptyRecordsDiv">
		<tbody>
			<tr>
				<td>
					{vtranslate('LBL_NO_RECORDS_FOUND', $MODULE)} {vtranslate($SOURCE_MODULE, $SOURCE_MODULE)}.
				</td>
			</tr>
		</tbody>
	</table>
{/if}
</div>
</div>
{/strip}
