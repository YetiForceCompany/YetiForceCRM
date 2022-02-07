{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MailSmtp-ListViewContents -->
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
					<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}"
						{if method_exists($LISTVIEW_ENTRY,'getDetailViewUrl')}data-recordurl="{$LISTVIEW_ENTRY->getDetailViewUrl()}" {/if}>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
							{assign var=LAST_COLUMN value=$LISTVIEW_HEADER@last}
							<td class="listViewEntryValue {$WIDTHTYPE}" width="{$WIDTH}%" nowrap>
								&nbsp;{App\Language::translate($LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME), $QUALIFIED_MODULE)}
								{if $LAST_COLUMN && $LISTVIEW_ENTRY->getRecordLinks()}
								</td>
								<td nowrap class="{$WIDTHTYPE}">
									<div class="float-right actions">
										<span class="actionImages">
											{foreach item=RECORD_LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
												{assign var="RECORD_LINK_URL" value=$RECORD_LINK->getUrl()}
												<a class="{$RECORD_LINK->getClassName()}" {if stripos($RECORD_LINK_URL, 'javascript:')===0} onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};
														if (event.stopPropagation){ldelim}
																	event.stopPropagation();{rdelim} else{ldelim}
																				event.cancelBubble = true;{rdelim}" {else} href='{$RECORD_LINK_URL}' {/if}>
													<span class="{$RECORD_LINK->getIcon()}" title="{App\Language::translate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}"></span>
												</a>
												{if !$RECORD_LINK@last}
													&nbsp;
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
							{App\Language::translate('LBL_NO_RECORDS_FOUND', $QUALIFIED_MODULE)}
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
	</div>
	<!-- /tpl-Settings-MailSmtp-ListViewContents -->
{/strip}
