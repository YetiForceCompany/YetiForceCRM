{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Settings-Base-ListViewContents -->
	{if \App\Layout::checkTemplatePath('ListView/CustomHeader.tpl', $QUALIFIED_MODULE)}
		{include file=\App\Layout::getTemplatePath('ListView/CustomHeader.tpl', $QUALIFIED_MODULE)}
	{/if}
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" value="{$ORDER_BY}" id="orderBy" />
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">
	<div class="listViewEntriesDiv u-overflow-scroll-non-desktop">
		<span class="listViewLoadingImageBlock d-none modal" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}" />
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</span>
		{assign var="NAME_FIELDS" value=$MODULE_MODEL->getNameFields()}
		{assign var=WIDTH value={99/(count($LISTVIEW_HEADERS))}}
		<table class="table tableRWD table-bordered table-sm listViewEntriesTable">
			{include file=\App\Layout::getTemplatePath('ListView/TableHeader.tpl', $QUALIFIED_MODULE)}
			<tbody>
				{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES}
					<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" {if method_exists($LISTVIEW_ENTRY,'getDetailViewUrl')}data-recordurl="{$LISTVIEW_ENTRY->getDetailViewUrl()}" {/if}>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
							{assign var=LAST_COLUMN value=$LISTVIEW_HEADER@last}
							<td class="listViewEntryValue {$WIDTHTYPE}" width="{$WIDTH}%" nowrap>
								{$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
								{if $LAST_COLUMN && $LISTVIEW_ENTRY->getRecordLinks()}
								</td>
								<td nowrap class="{$WIDTHTYPE} rightRecordActions listButtons {$WIDTHTYPE}">
									{assign var=LINKS value=$LISTVIEW_ENTRY->getRecordLinks()}
									{if count($LINKS) > 0}
										<div class="actions">
											<div class="float-right">
												{foreach from=$LINKS item=LINK}
													{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $QUALIFIED_MODULE) BUTTON_VIEW='listViewBasic' MODULE_NAME=$QUALIFIED_MODULE MODULE=$QUALIFIED_MODULE}
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
		{if $LISTVIEW_ENTRIES_COUNT eq '0'}
			<table class="emptyRecordsDiv">
				<tbody>
					<tr>
						<td>
							{\App\Language::translate('LBL_NO_RECORDS_FOUND', $QUALIFIED_MODULE)}
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
	</div>
	<!-- /tpl-Settings-Base-ListViewContents -->
{/strip}
