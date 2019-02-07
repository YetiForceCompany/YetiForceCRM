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
	<input type="hidden" id="pageNumber" value="{$CURRENT_PAGE}" />
	<input type="hidden" id="totalCount" value="{$PAGING_INFO['recordCount']}" />
	<input type="hidden" id="totalPageCount" value="{$PAGING_INFO['pageCount']}" />
	<input type="hidden" id="recordsCount" value="{$PAGING_INFO['recordCount']}" />
	<input type="hidden" id="selectedIds" name="selectedIds" />
	<input type="hidden" id="excludedIds" name="excludedIds" />
	<input type="hidden" id="alphabetValue" value="{$ALPHABET_VALUE}" />
	<input type="hidden" id="pageStartRange" value="{$PAGING_INFO['startSequence']}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_INFO['endSequence']}" />
	<input type="hidden" id="previousPageExist" {if $CURRENT_PAGE neq 1}value="1"{/if} />
	<input type="hidden" id="nextPageExist" value="{$PAGING_INFO['nextPageExists']}" />
	<input type="hidden" id="pageLimit" value="{$PAGING_INFO['pageLimit']}" />
	<input type="hidden" id="noOfEntries" value="{$PAGING_INFO['recordCount']}" />
	<div id="selectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="selectAllMsg">{\App\Language::translate('LBL_SELECT_ALL',$MODULE)}&nbsp;{\App\Language::translate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
	</div>
	<div id="deSelectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="deSelectAllMsg">{\App\Language::translate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
	</div>
	<div class="listViewContentDiv" id="listViewContents">
		<div class="listViewEntriesDiv u-overflow-scroll-xsm-down">
			<input type="hidden" value="{$COLUMN_NAME}" id="orderBy" />
			<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
			<span class="listViewLoadingImageBlock d-none modal noprint" id="loadingListViewModal">
				<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}" />
				<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
			</span>
			{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
			<table class="table table-bordered listViewEntriesTable js-fixed-thead" data-js="floatThead">
				<thead>
					<tr class="listViewHeaders">
						<th width="5%">
							<input type="checkbox" title="{\App\Language::translate('LBL_SELECT_ALL')}" id="listViewEntriesMainCheckBox" />
						</th>
						<th nowrap>
							<a href="javascript:void(0);" id="portalname" class="portalListViewHeader"
							   data-nextsortorderval="{if $COLUMN_NAME eq 'portalname'}{$NEXT_SORT_ORDER}{else}ASC{/if}">{\App\Language::translate('LBL_BOOKMARK_NAME', $MODULE)}
								&nbsp;&nbsp;{if $COLUMN_NAME eq 'portalname'}<img class="{$SORT_IMAGE} icon-white">{/if}</a>
						</th>
						<th nowrap>
							<a href="javascript:void(0);" id="portalurl" class="portalListViewHeader"
							   data-nextsortorderval="{if $COLUMN_NAME eq 'portalurl'}{$NEXT_SORT_ORDER}{else}ASC{/if}">{\App\Language::translate('LBL_BOOKMARK_URL', $MODULE)}
								&nbsp;&nbsp;{if $COLUMN_NAME eq 'portalurl'}<img class="{$SORT_IMAGE} icon-white">{/if}</a></a>
						</th>
						<th nowrap>
							<a href="javascript:void(0);" id="createdtime" class="portalListViewHeader"
							   data-nextsortorderval="{if $COLUMN_NAME eq 'createdtime'}{$NEXT_SORT_ORDER}{else}ASC{/if}">{\App\Language::translate('LBL_CREATED_ON', $MODULE)}
								&nbsp;&nbsp;{if $COLUMN_NAME eq 'createdtime'}<img class="{$SORT_IMAGE} icon-white">{/if}</a></a>
						</th>
						<th nowrap class="{$WIDTHTYPE}"></th>
					</tr>
				</thead>
				<tbody>
					{foreach item=LISTVIEW_ENTRY key=RECORD_ID from=$LISTVIEW_ENTRIES}
						<tr class="listViewEntries" data-id="{$RECORD_ID}" data-recordurl="index.php?module=Portal&view=Detail&record={$RECORD_ID}">
							<td width="5%" class="{$WIDTHTYPE}">
								<input type="checkbox" value="{$RECORD_ID}" title="{\App\Language::translate('LBL_SELECT_SINGLE_ROW')}" class="listViewEntriesCheckBox" />
							</td>
							<td class="listViewEntryValue {$WIDTHTYPE}" nowrap>
								<a href="index.php?module=Portal&view=Detail&record={$RECORD_ID}" sl-processed="1">{$LISTVIEW_ENTRY->get('portalname')}</a>
							</td>
							<td class="listViewEntryValue {$WIDTHTYPE}" nowrap>
								<a class="urlField u-cursor-pointer" href="{if substr($LISTVIEW_ENTRY->get('portalurl'), 0, 4) neq 'http'}//{/if}{$LISTVIEW_ENTRY->get('portalurl')}" target="_blank"
								   rel="noreferrer noopener" sl-processed="1">{$LISTVIEW_ENTRY->get('portalurl')}</a>
							</td>
							<td class="listViewEntryValue {$WIDTHTYPE}" nowrap>{$LISTVIEW_ENTRY->get('createdtime')}</td>
							<td nowrap class="{$WIDTHTYPE}">
								<div class="actions float-right">
									<span class="actionImages">
										<span title="{\App\Language::translate('LBL_EDIT', $MODULE)}" class="fas fa-edit alignMiddle editRecord"></span>&nbsp;
										<span title="{\App\Language::translate('LBL_DELETE', $MODULE)}" class="fas fa-trash-alt alignMiddle deleteRecord"></span>
									</span>
								</div>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
			{if $PAGING_INFO['recordCount'] eq '0'}
				<table class="emptyRecordsDiv">
					<tbody>
						<tr>
							<td>
								<button class="addBookmark btn btn-link tdUnderline"> {\App\Language::translate('LBL_NO')} {\App\Language::translate('LBL_FOUND')} {\App\Language::translate('LBL_BOOKMARKS', $MODULE)}. {\App\Language::translate('LBL_BOOKMARK', $MODULE)}</button>
							</td>
						</tr>
					</tbody>
				</table>
			{/if}
		</div>
	</div>
</div>
</div>
{/strip}
