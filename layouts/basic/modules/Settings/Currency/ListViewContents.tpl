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
	<!-- tpl-Settings-Currency-ListViewContents -->
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}"/>
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}"/>
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}"/>
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}"/>
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}"/>
	<input type="hidden" value="{$ORDER_BY}" id="orderBy"/>
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder"/>
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}"/>
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">
	<div class="listViewEntriesDiv u-overflow-scroll-non-desktop" style='overflow-x:auto;'>
		<span class="listViewLoadingImageBlock d-none modal" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image"
				 title="{\App\Language::translate('LBL_LOADING')}"/>
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</span>
		{assign var="NAME_FIELDS" value=$MODULE_MODEL->getNameFields()}
		{assign var=WIDTH value={99/(count($LISTVIEW_HEADERS))}}
		<table class="table tableRWD table-bordered table-sm listViewEntriesTable">
			{include file=\App\Layout::getTemplatePath('ListView/TableHeader.tpl', $QUALIFIED_MODULE) EMPTY_COLUMN=1}
			<tbody>
			{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES}
				<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}"
					{if method_exists($LISTVIEW_ENTRY,'getDetailViewUrl')}data-recordurl="{$LISTVIEW_ENTRY->getDetailViewUrl()}"{/if}
				>
					<td width="1%" nowrap class="{$WIDTHTYPE}">
						{if !empty($MODULE) && $MODULE eq 'CronTasks'}
							<img src="{\App\Layout::getImagePath('drag.png')}" class="alignTop"
								 title="{\App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
						{/if}
					</td>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
						{assign var=LAST_COLUMN value=$LISTVIEW_HEADER@last}
					<td class="listViewEntryValue {$WIDTHTYPE}" width="{$WIDTH}%" nowrap>
						{if $LISTVIEW_HEADERNAME  eq 'currency_status' }
							{if {$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}  eq 'Active' }
								&nbsp;{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}
							{else}
								&nbsp;{\App\Language::translate('LBL_INACTIVE',$QUALIFIED_MODULE)}
							{/if}
						{elseif $LISTVIEW_HEADERNAME eq 'currency_name'}
							{\App\Language::translate($LISTVIEW_ENTRY->getDisplayValue('currency_code'), 'Other.Currency')}
						{else}
							&nbsp;{$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
						{/if}
						{if $LAST_COLUMN}
							</td>
							<td nowrap class="{$WIDTHTYPE}">
								{assign var=LINKS value=$LISTVIEW_ENTRY->getRecordLinks()}
								{if count($LINKS) > 0}
									<div class="actions">
										<div class="float-right">
											{foreach from=$LINKS item=LINK}
												{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $QUALIFIED_MODULE) BUTTON_VIEW='listViewBasic' MODULE=$QUALIFIED_MODULE}
											{/foreach}
										</div>
									</div>
								{/if}
							</td>
						{/if}
						</td>
					{/foreach}
				</tr>
			{/foreach}
			</tbody>
		</table>
		<!--added this div for Temporarily -->
		{if $LISTVIEW_ENTRIES_COUNT eq '0'}
			<table class="emptyRecordsDiv">
				<tbody>
				<tr>
					<td>
						{\App\Language::translate('LBL_EQ_ZERO')} {\App\Language::translate(MODULE, $QUALIFIED_MODULE)} {\App\Language::translate('LBL_FOUND')}
					</td>
				</tr>
				</tbody>
			</table>
		{/if}
	</div>
	<!-- /tpl-Settings-Currency-ListViewContents -->
{/strip}
