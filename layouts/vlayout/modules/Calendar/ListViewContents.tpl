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
<input type="hidden" id="view" value="{$VIEW}" />
<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
<input type="hidden" id="alphabetSearchKey" value= "{$MODULE_MODEL->getAlphabetSearchField()}" />
<input type="hidden" id="Operator" value="{$OPERATOR}" />
<input type="hidden" id="alphabetValue" value="{$ALPHABET_VALUE}" />
<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
<input type="hidden" id="listMaxEntriesMassEdit" value="{vglobal('listMaxEntriesMassEdit')}" />
<input type="hidden" id="autoRefreshListOnChange" value="{PerformancePrefs::getBoolean('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">


{assign var = ALPHABETS_LABEL value = vtranslate('LBL_ALPHABETS', 'Vtiger')}
{assign var = ALPHABETS value = ','|explode:$ALPHABETS_LABEL}

<div class="alphabetSorting noprint">
	<table width="100%" class="table-bordered" style="border: 1px solid #ddd;table-layout: fixed">
		<tbody>
			<tr>
			{foreach item=ALPHABET from=$ALPHABETS}
				<td class="alphabetSearch textAlignCenter cursorPointer {if $ALPHABET_VALUE eq $ALPHABET} highlightBackgroundColor {/if}" style="padding : 0px !important"><a id="{$ALPHABET}" href="#">{$ALPHABET}</a></td>
			{/foreach}
			<td class="alphabetSearch textAlignCenter cursorPointer">
				<a href="index.php?view=List&module={$MODULE}" >
					<span class="glyphicon glyphicon-remove"></span>
				</a>
			</td>
			</tr>
		</tbody>
	</table>
</div>
<div id="selectAllMsgDiv" class="alert-block msgDiv noprint">
	<strong><a id="selectAllMsg">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
</div>
<div id="deSelectAllMsgDiv" class="alert-block msgDiv noprint">
	<strong><a id="deSelectAllMsg">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
</div>
<div class="contents-topscroll noprint">
	<div class="topscroll-div">
		&nbsp;
	 </div>
</div>
<div class="listViewEntriesDiv contents-bottomscroll">
	<div class="bottomscroll-div">
	<input type="hidden" value="{$ORDER_BY}" id="orderBy">
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
	<span class="listViewLoadingImageBlock hide modal noprint" id="loadingListViewModal">
		<img class="listViewLoadingImage" src="{vimage_path('loading.gif')}" alt="no-image" title="{vtranslate('LBL_LOADING', $MODULE)}"/>
		<p class="listViewLoadingMsg">{vtranslate('LBL_LOADING_LISTVIEW_CONTENTS', $MODULE)}........</p>
	</span>
	{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
	<table class="table table-bordered listViewEntriesTable">
		<thead>
			<tr class="listViewHeaders">
				<th>
					<input type="checkbox" id="listViewEntriesMainCheckBox" title="{vtranslate('LBL_SELECT_ALL')}" />
				</th>
				{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
				<th nowrap {if $LISTVIEW_HEADER@last} colspan="2" {/if}>
					<a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('column')}">{vtranslate($LISTVIEW_HEADER->get('label'), $MODULE)}
						&nbsp;&nbsp;{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('column')}<img class="{$SORT_IMAGE} icon-white">{/if}</a>
				</th>
				{/foreach}
			</tr>
		</thead>
        <tr>
			<td>
				<a class="btn btn-default" data-trigger="listSearch" href="javascript:void(0);">
					<span class="glyphicon glyphicon-search"></span>
				</a>
			</td>
         {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
             <td>
                 {assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}     
                {include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$MODULE) 
                    FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()] USER_MODEL=$CURRENT_USER_MODEL}
             </td>
         {/foreach}
         <td> 
			<a class="btn btn-default" href="index.php?view=List&module={$MODULE}" >
				<span class="glyphicon glyphicon-remove"></span>
			</a>
         </td>
        </tr>
		{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
			{assign var=CURRENT_USER_ID value=$CURRENT_USER_MODEL->getId()}
			{assign var=RAWDATA value=$LISTVIEW_ENTRY->getRawData()}
			{assign var=OWNER_ID value=$RAWDATA['smownerid']}
			{assign var=DETAIL_VIEW_URL value=$LISTVIEW_ENTRY->getDetailViewUrl()}
			{assign var=FULL_DETAIL_VIEW_URL value=$LISTVIEW_ENTRY->getFullDetailViewUrl()}
			{assign var=EDIT_VIEW_URL value=$LISTVIEW_ENTRY->getEditViewUrl()}
			{assign var=IS_DELETE value='true'}
			{assign var=visibility value='true'}
			{if in_array($OWNER_ID, $GROUPS_IDS)}
				{assign var=visibility value=false}
			{else if $OWNER_ID == $CURRENT_USER_ID}
				{assign var=visibility value=false}
			{/if}
			{if !$CURRENT_USER_MODEL->isAdminUser() && $LISTVIEW_ENTRY->get('activitytype') != 'Task' && $LISTVIEW_ENTRY->get('visibility') == 'Private' && $OWNER_ID && $visibility}
				{assign var=DETAIL_VIEW_URL value=''}
				{assign var=FULL_DETAIL_VIEW_URL value=''}
				{assign var=EDIT_VIEW_URL value=''}
				{assign var=IS_DELETE value=false}
			{/if}
		<tr class="listViewEntries" data-id='{$LISTVIEW_ENTRY->getId()}' 
			{if $DETAIL_VIEW_URL} data-recordUrl='{$DETAIL_VIEW_URL}' {/if} id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
			{if $LISTVIEW_ENTRY->colorList neq ''}
				<style>
				#{$MODULE}_listView_row_{$smarty.foreach.listview.index+1} > td {
					background-color: {$LISTVIEW_ENTRY->colorList.background};
					color: {$LISTVIEW_ENTRY->colorList.text};
				}
				</style>
			{/if}
            <td class="{$WIDTHTYPE}">
				<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox" title="{vtranslate('LBL_SELECT_SINGLE_ROW')}"/>
			</td>
			{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
			{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
			<td class="listViewEntryValue {$WIDTHTYPE}" data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}" nowrap>
				{if $LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4'}
					<a {if $LISTVIEW_HEADER->isNameField() eq true}class="moduleColor_{$MODULE}"{/if} href="{$LISTVIEW_ENTRY->getDetailViewUrl()}">{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>
				{else}
					{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
				{/if}
				{if $LISTVIEW_HEADER@last}
				</td><td nowrap class="{$WIDTHTYPE}">		
				<div class="actions pull-right">
					<span class="actionImages">
						{assign var=CURRENT_ACTIVITY_LABELS value=Calendar_Module_Model::getComponentActivityStateLabel('current')}
                        {if $IS_MODULE_EDITABLE && $EDIT_VIEW_URL && in_array($RAWDATA.status,$CURRENT_ACTIVITY_LABELS)}
                            <a class="showModal" data-url="{$LISTVIEW_ENTRY->getActivityStateModalUrl()}"><span title="{vtranslate('LBL_SET_RECORD_STATUS', $MODULE)}" class="glyphicon glyphicon-ok alignMiddle"></span></a>&nbsp;
                        {/if}
						{if $FULL_DETAIL_VIEW_URL}
							<a href="{$FULL_DETAIL_VIEW_URL}"><span title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="glyphicon glyphicon-th-list alignMiddle"></span></a>&nbsp;
						{/if}
						{if $IS_MODULE_EDITABLE && $EDIT_VIEW_URL}
							<a href='{$EDIT_VIEW_URL}'><span title="{vtranslate('LBL_EDIT', $MODULE)}" class="glyphicon glyphicon-pencil alignMiddle"></span></a>&nbsp;
						{/if}
						{if $IS_MODULE_DELETABLE && $IS_DELETE}
							<a class="deleteRecordButton"><span title="{vtranslate('LBL_DELETE', $MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
						{/if}
					</span>
				</div></td>
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
					{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
					{vtranslate('LBL_RECORDS_NO_FOUND')}.{if $IS_MODULE_EDITABLE} <a href="{$MODULE_MODEL->getCreateRecordUrl()}">{vtranslate('LBL_CREATE_SINGLE_RECORD')}</a>{/if}
				</td>
			</tr>
		</tbody>
	</table>
{/if}
</div>
</div>
{/strip}
