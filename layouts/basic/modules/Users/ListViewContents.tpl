{*<!--
/*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
************************************************************************************/
-->*}
{strip}
	<input type="hidden" id="listViewEntriesCount" value="{$LISTVIEW_ENTRIES_COUNT}" />
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="pageNumberValue" value="{$PAGE_NUMBER}" />
	<input type="hidden" id="pageLimitValue" value="{$PAGING_MODEL->getPageLimit()}" />
	<input type="hidden" id="numberOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}" />
	<input type="hidden" id="alphabetSearchKey" value="{$MODULE_MODEL->getAlphabetSearchField()}" />
	<input type="hidden" id="Operator" value="{$OPERATOR}" />
	<input type="hidden" id="alphabetValue" value="{$ALPHABET_VALUE}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" id="listMaxEntriesMassEdit" value="{\App\Config::main('listMaxEntriesMassEdit')}" />
	<input type="hidden" id="autoRefreshListOnChange"
		value="{App\Config::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type="hidden" id="selectedIds" name="selectedIds" />
	<input type="hidden" id="excludedIds" name="excludedIds" />
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">
	<input type="hidden" class="js-empty-fields" data-js="value" value="{\App\Purifier::encodeHtml(\App\Json::encode($LOCKED_EMPTY_FIELDS))}" />
	{include file=\App\Layout::getTemplatePath('ListViewAlphabet.tpl', $MODULE)}
	<div id="selectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="selectAllMsg">{\App\Language::translate('LBL_SELECT_ALL',$MODULE)}
				&nbsp;{\App\Language::translate($MODULE ,$MODULE)}&nbsp;</a></strong>
	</div>
	<div id="deSelectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="deSelectAllMsg">{\App\Language::translate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
	</div>
	{function LINKS_BUTTONS LINKS=[]}
		{if count($LINKS) > 0}
			{assign var=ONLY_ONE value=count($LINKS) eq 1}
			<div class="actions">
				{if $ONLY_ONE}
					{foreach from=$LINKS item=LINK}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listViewBasic'}
					{/foreach}
				{else}
					<div class="dropright u-remove-dropdown-icon">
						<button class="btn btn-sm btn-light toolsAction dropdown-toggle" type="button"
							data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<span class="fas fa-wrench" aria-hidden="true"></span>
							<span class="sr-only">{\App\Language::translate('LBL_ACTIONS')}</span>
						</button>
						<div class="dropdown-menu" aria-label="{\App\Language::translate('LBL_ACTIONS')}">
							{foreach from=$LINKS item=LINK}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listViewBasic'}
							{/foreach}
						</div>
					</div>
				{/if}
			</div>
		{/if}
	{/function}
	<div class="listViewEntriesDiv u-overflow-scroll-non-desktop">
		<input type="hidden" value="{$ORDER_BY}" id="orderBy" />
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
		<span class="listViewLoadingImageBlock d-none modal" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image"
				title="{\App\Language::translate('LBL_LOADING')}" />
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</span>
		<table class="table tableBorderHeadBody listViewEntriesTable {$WIDTHTYPE} js-fixed-thead" data-js="floatThead">
			<thead>
				<tr class="listViewHeaders">
					<th width="2%" colspan="2">
						<input type="checkbox" id="listViewEntriesMainCheckBox"
							title="{\App\Language::translate('LBL_SELECT_ALL')}" />
					</th>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<th class="noWrap {if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}columnSorted{/if}">
							<a href="javascript:void(0);" class="listViewHeaderValues js-listview_header" data-js="click"
								{if $LISTVIEW_HEADER->isListviewSortable()}data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}{$NEXT_SORT_ORDER}{else}ASC{/if}" {/if}
								data-columnname="{$LISTVIEW_HEADER->getColumnName()}">{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE)}
								&nbsp;&nbsp;{if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}&nbsp;&nbsp;<span
									class="{$SORT_IMAGE}"></span>{/if}</a>
						</th>
					{/foreach}
				</tr>
				{if $MODULE_MODEL->isQuickSearchEnabled()}
					<tr class="bg-white">
						<td class="listViewSearchTd" colspan="2">
							<div class="flexWrapper">
								<a class="btn btn-light" role="button" href="javascript:void(0);" data-trigger="listSearch">
									<span class="fas fa-search" title="{\App\Language::translate('LBL_SEARCH')}"></span>
									<span class="sr-only">{\App\Language::translate('LBL_SEARCH')}</span>
								</a>
								<a class="btn btn-light float-right listRemoveBtn" role="button"
									href="index.php?module={$MODULE}&parent=Settings&view={$VIEW}">
									<span class="fas fa-times"
										title="{\App\Language::translate('LBL_CLEAR_SEARCH')}"></span>
									<span class="sr-only">{\App\Language::translate('LBL_CLEAR_SEARCH')}</span>

								</a>
							</div>
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS name=SEARCH_HEADERS}
							<td>
								{assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
								{assign var=FIELD_NAME value=$LISTVIEW_HEADER->getName()}
								{if !empty($SEARCH_DETAILS[$FIELD_NAME])}
									{assign var="SEARCH_INFO" value=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()]}
								{else}
									{assign var="SEARCH_INFO" value=[]}
								{/if}
								{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME)
													FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL}
							</td>
						{/foreach}
					</tr>
				{/if}
			</thead>
			<tbody>
				{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
					<tr class="listViewEntries" data-id='{$LISTVIEW_ENTRY->getId()}'
						data-recordUrl='{$LISTVIEW_ENTRY->getDetailViewUrl()}'
						id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
						<td class="noWrap leftRecordActions listButtons {$WIDTHTYPE}">
							{assign var=LINKS value=$LISTVIEW_ENTRY->getRecordListViewLinksLeftSide()}
							<input type="hidden" name="deleteActionUrl" value="{$LISTVIEW_ENTRY->getDeleteUrl()}">
							<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" title="{\App\Language::translate('LBL_SELECT_SINGLE_ROW')}" class="listViewEntriesCheckBox" />
							{assign var=NEXT_LINKS value=[]}
							{if isset($LINKS['BUTTONS'])}
								{assign var=NEXT_LINKS value=$LINKS['BUTTONS']}
								{assign var=LINKS value=array_diff_key($LINKS,['BUTTONS'=>''])}
							{/if}
							{LINKS_BUTTONS LINKS=$LINKS}
							{foreach from=$NEXT_LINKS item=LINK}
								{LINKS_BUTTONS LINKS=[$LINK]}
							{/foreach}
						</td>
						<td class="{$WIDTHTYPE}">
							<div class="d-flex justify-content-center">
								{assign var=IMAGE value=$LISTVIEW_ENTRY->getImage()}
								{if $IMAGE}
									<div class="px-0">
										<img src="{$IMAGE.url}"
											class="c-img__user rounded-circle" alt="{$LISTVIEW_ENTRY->getName()}"
											title="{$LISTVIEW_ENTRY->getName()}">
									</div>
								{else}
									<div class="px-0">
										<img class="c-img__user" alt=""
											src="{\App\Layout::getImagePath('DefaultUserIcon.png')}">
									</div>
								{/if}
							</div>
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->getFieldName()}
							<td class="{$WIDTHTYPE}" nowrap>
								{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}
							</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
			<tfoot class="listViewSummation">
				<tr>
					<td></td>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<td {if $LISTVIEW_HEADER@last}colspan="2" {/if}
							class="noWrap {if !empty($LISTVIEW_HEADER->isCalculateField())}border{/if}">
							{if !empty($LISTVIEW_HEADER->isCalculateField())}
								<button class="btn btn-sm btn-light js-popover-tooltip" data-js="popover"
									data-operator="sum" data-field="{$LISTVIEW_HEADER->getName()}"
									data-content="{\App\Language::translate('LBL_CALCULATE_SUM_FOR_THIS_FIELD')}">
									<span class="fas fa-signal"></span>
								</button>
								<span class="calculateValue"></span>
							{/if}
						</td>
					{/foreach}
				</tr>
			</tfoot>
		</table>
		{if $LISTVIEW_ENTRIES_COUNT eq '0'}
			<table class="emptyRecordsDiv">
				<tbody>
					<tr>
						<td>
							{\App\Language::translate('LBL_NO_RECORDS_MATCHED_THIS_CRITERIA')}
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
	</div>
{/strip}
