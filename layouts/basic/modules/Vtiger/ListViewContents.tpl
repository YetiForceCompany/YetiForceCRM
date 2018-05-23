{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" id="listMaxEntriesMassEdit" value="{\AppConfig::main('listMaxEntriesMassEdit')}" />
	<input type="hidden" id="autoRefreshListOnChange" value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">

	{include file=\App\Layout::getTemplatePath('ListViewAlphabet.tpl', $MODULE_NAME)}
	<div class="clearfix"></div>
	<div id="selectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="selectAllMsg" href="#">{\App\Language::translate('LBL_SELECT_ALL',$MODULE)}&nbsp;{\App\Language::translate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
	</div>
	<div id="deSelectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="deSelectAllMsg" href="#">{\App\Language::translate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
	</div>
	<div class="listViewEntriesDiv">
		<input type="hidden" value="{$ORDER_BY}" id="orderBy" />
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
		<div class="listViewLoadingImageBlock d-none modal noprint" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}" />
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</div>
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<table class="table tableBorderHeadBody listViewEntriesTable {$WIDTHTYPE} {if $VIEW_MODEL && !$VIEW_MODEL->isEmpty('entityState')}listView{$VIEW_MODEL->get('entityState')}{/if}">
			<thead>
				<tr class="listViewHeaders">
					<th class="p-2">
						<label class="sr-only" for="listViewEntriesMainCheckBox">{\App\Language::translate('LBL_SELECT_ALL')}</label>
						<input type="checkbox" id="listViewEntriesMainCheckBox" title="{\App\Language::translate('LBL_SELECT_ALL')}" />
					</th>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<th class="noWrap p-2 {if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}columnSorted{/if}" {if !empty($LISTVIEW_HEADER->get('maxwidthcolumn'))}style="width:{$LISTVIEW_HEADER->get('maxwidthcolumn')}%"{/if} {if $LISTVIEW_HEADER@last}colspan="2"{/if}>
							<a href="javascript:void(0);" class="listViewHeaderValues float-left" {if $LISTVIEW_HEADER->isListviewSortable()}data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}{$NEXT_SORT_ORDER}{else}ASC{/if}"{/if} data-columnname="{$LISTVIEW_HEADER->getColumnName()}">{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE)}
								&nbsp;&nbsp;{if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}<span class="{$SORT_IMAGE}"></span>{/if}</a>
								{if $LISTVIEW_HEADER->getFieldDataType() eq 'tree' || $LISTVIEW_HEADER->getFieldDataType() eq 'categoryMultipicklist'}
								{assign var=LISTVIEW_HEADER_NAME value=$LISTVIEW_HEADER->getName()}
								<div class="d-flex align-items-center">
									<input class="searchInSubcategories mr-1" type="checkbox" id="searchInSubcategories{$LISTVIEW_HEADER_NAME}" title="{\App\Language::translate('LBL_SEARCH_IN_SUBCATEGORIES',$MODULE_NAME)}" name="searchInSubcategories" value="1" data-columnname="{$LISTVIEW_HEADER->getColumnName()}" {if !empty($SEARCH_DETAILS[$LISTVIEW_HEADER_NAME]['specialOption'])} checked {/if}>
									<span class="js-popover-tooltip delay0 fas fa-info-circle" data-js="popover" data-placement="top" data-original-title="{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE)}"
										  data-content="{\App\Language::translate('LBL_SEARCH_IN_SUBCATEGORIES',$MODULE_NAME)}">
									</span>
								</div>
							{/if}
						</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				{if $MODULE_MODEL->isQuickSearchEnabled()}
					<tr>
						<td class="listViewSearchTd">
							<div class="flexWrapper">
								<a class="btn btn-light" role="button" data-trigger="listSearch" href="javascript:void(0);">
									<span class="fas fa-search" title="{\App\Language::translate('LBL_SEARCH')}"></span>
								</a>
								<a class="btn btn-light float-right listRemoveBtn" role="button" href="index.php?view={$VIEW}&module={$MODULE}">
									<span class="fas fa-times" title="{\App\Language::translate('LBL_CLEAR_SEARCH')}"></span>
								</a>
							</div>
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							<td class="pl-1">
								{assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
								{assign var=LISTVIEW_HEADER_NAME value=$LISTVIEW_HEADER->getName()}
								{if isset($SEARCH_DETAILS[$LISTVIEW_HEADER_NAME])}
									{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$LISTVIEW_HEADER_NAME]}
								{else}
									{assign var=SEARCH_INFO value=[]}
								{/if}
								{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME)
                    FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL}
							</td>
						{/foreach}
						<td class="reducePadding"></td>
					</tr>
				{/if}
				{assign var="LISTVIEW_HEADER_COUNT" value=count($LISTVIEW_HEADERS)}
				{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
					{assign var="RECORD_ID" value=$LISTVIEW_ENTRY->getId()}
					{assign var="RECORD_COLORS" value=$LISTVIEW_ENTRY->getListViewColor()}
					<tr class="listViewEntries" data-id='{$LISTVIEW_ENTRY->getId()}' data-recordUrl='{$LISTVIEW_ENTRY->getDetailViewUrl()}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
						<td class="{$WIDTHTYPE} noWrap leftRecordActions" {if $RECORD_COLORS['leftBorder']}style="border-left-color: {$RECORD_COLORS['leftBorder']};"{/if}>
							{include file=\App\Layout::getTemplatePath('ListViewLeftSide.tpl', $MODULE_NAME)}
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS name=listHeaderForeach}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->getFieldName()}
							<td class="listViewEntryValue noWrap {$WIDTHTYPE}" data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}" data-raw-value="{\App\Purifier::encodeHtml($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME))}">
								{if ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->getUIType() eq '4') && $MODULE_MODEL->isListViewNameFieldNavigationEnabled() eq true && $LISTVIEW_ENTRY->isViewable()}
									<a {if $LISTVIEW_HEADER->isNameField() eq true}class="modCT_{$MODULE} js-list-field" data-js="width" {/if} href="{$LISTVIEW_ENTRY->getDetailViewUrl()}">
										{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}
									</a>
								{else}
									{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}
								{/if}
							</td>
						{/foreach}
						<td class="noWrap rightRecordActions reducePadding">
							{include file=\App\Layout::getTemplatePath('ListViewRightSide.tpl', $MODULE_NAME)}
						</td>
					</tr>
				{/foreach}
			</tbody>
			<tfoot class="listViewSummation">
				<tr>
					<td></td>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<td {if !empty($LISTVIEW_HEADER->get('maxwidthcolumn'))}style="width:{$LISTVIEW_HEADER->get('maxwidthcolumn')}%"{/if} {if $LISTVIEW_HEADER@last}colspan="2"{/if} class="noWrap {if !empty($LISTVIEW_HEADER->isCalculateField())}border{/if}" >
							{if !empty($LISTVIEW_HEADER->isCalculateField())}
								<button class="btn btn-sm btn-light js-popover-tooltip" data-js="popover" type="button" data-operator="sum" data-field="{$LISTVIEW_HEADER->getName()}" data-content="{\App\Language::translate('LBL_CALCULATE_SUM_FOR_THIS_FIELD')}">
									<span class="fas fa-signal" title="{\App\Language::translate('LBL_CALCULATE_SUM_FOR_THIS_FIELD')}"></span>
								</button>
								<span class="calculateValue"></span>
							{/if}
						</td>
					{/foreach}
				</tr>
			</tfoot>
		</table>
		<!--added this div for Temporarily -->
		{if $LISTVIEW_ENTRIES_COUNT eq '0'}
			<table class="emptyRecordsDiv">
				<tbody>
					<tr>
						<td>
							{\App\Language::translate('LBL_RECORDS_NO_FOUND')}.{if $IS_MODULE_EDITABLE} <a href="{$MODULE_MODEL->getCreateRecordUrl()}">{\App\Language::translate('LBL_CREATE_SINGLE_RECORD')}</a>{/if}
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
	</div>
{/strip}

