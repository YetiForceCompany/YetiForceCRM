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
		<table class="table tableRWD table-bordered table-sm listViewEntriesTable">
			<thead>
				<tr class="listViewHeaders">
					{assign var=WIDTH value={99/(count($LISTVIEW_HEADERS))}}
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<th nowrap class="{$WIDTHTYPE}">
							<a {if !($LISTVIEW_HEADER->has('sort'))} class="listViewHeaderValues u-cursor-pointer js-listview_header" data-js="click" data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('name')}" {/if}>{\App\Language::translate($LISTVIEW_HEADER->get('label'), $QUALIFIED_MODULE)}
								{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}&nbsp;&nbsp;<span class="{$SORT_IMAGE}"></span>{/if}</a>
						</th>
					{/foreach}
					<th width='15%'></th>
				</tr>
			</thead>
			<tbody>
				{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES}
					<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}"
						{if method_exists($LISTVIEW_ENTRY,'getDetailViewUrl')}data-recordurl="{$LISTVIEW_ENTRY->getDetailViewUrl()}" {/if}>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
							{if $LISTVIEW_HEADERNAME eq 'all_tasks'}
								{assign var=ALL_TASKS value=$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
							{else if $LISTVIEW_HEADERNAME eq 'active_tasks'}
								{assign var=ACTIVE_TASKS value=$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
							{/if}
							{assign var=LAST_COLUMN value=$LISTVIEW_HEADER@last}
							<td class="listViewEntryValue {$WIDTHTYPE}" data-name="{$LISTVIEW_HEADERNAME}">
								&nbsp;{$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
								{if $LAST_COLUMN && $LISTVIEW_ENTRY->getRecordLinks()}
								</td>
								<td class="{$WIDTHTYPE}">
									<div class="float-right actions">
										<span class="actionImages">
											{foreach item=RECORD_LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
												{assign var="RECORD_LINK_URL" value=$RECORD_LINK->getUrl()}
												<a {if stripos($RECORD_LINK_URL, 'javascript:')===0}
														onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};if (event.stopPropagation){ldelim}
																event.stopPropagation();{rdelim} else{ldelim} event.cancelBubble = true;{rdelim}"
													{else}
														href='{$RECORD_LINK_URL}'
													{/if}
												class="{$RECORD_LINK->get('class')}
															{if ($RECORD_LINK->getLabel() eq 'LBL_ACTIVATION_TASKS' && $ACTIVE_TASKS eq $ALL_TASKS) ||
																($RECORD_LINK->getLabel() eq 'LBL_DEACTIVATION_TASKS' && $ACTIVE_TASKS eq 0)}
																{' '}d-none
															{/if}">
												<span class="{$RECORD_LINK->getIcon()}" title="{\App\Language::translate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}"></span>
											</a>
											{if !$RECORD_LINK@last}
												&nbsp;&nbsp;
											{/if}
										{/foreach}
									</span>
								</div>
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
						{\App\Language::translate('LBL_NO_RECORDS_FOUND', $QUALIFIED_MODULE)}
					</td>
				</tr>
			</tbody>
		</table>
	{/if}
</div>
{/strip}
