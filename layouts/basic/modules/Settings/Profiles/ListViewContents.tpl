{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Profiles-ListViewContents -->
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" value="{$ORDER_BY}" id="orderBy" />
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type='hidden' value="{$PAGE_NUMBER}" id="pageNumber">
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id="pageLimit">
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">
	<div class="listViewEntriesDiv u-overflow-scroll-non-desktop" style='overflow-x:auto;'>
		{assign var="NAME_FIELDS" value=$MODULE_MODEL->getNameFields()}
		{assign var=WIDTH value={99/(count($LISTVIEW_HEADERS))}}
		<table class="table table-bordered table-sm listViewEntriesTable">
			{include file=\App\Layout::getTemplatePath('ListView/TableHeader.tpl', $QUALIFIED_MODULE)}
			<tbody>
				{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES}
					<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" {' '}
						{if method_exists($LISTVIEW_ENTRY,'getDetailViewUrl')}data-recordurl="{$LISTVIEW_ENTRY->getDetailViewUrl()}" {/if}>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
							{assign var=LAST_COLUMN value=$LISTVIEW_HEADER@last}
							<td class="listViewEntryValue {$WIDTHTYPE}" width="{$WIDTH}%" nowrap>
								&nbsp;{\App\Language::translate($LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME), $QUALIFIED_MODULE)}
								{if $LAST_COLUMN && $LISTVIEW_ENTRY->getRecordLinks()}
								</td>
								<td nowrap class="{$WIDTHTYPE} rightRecordActions listButtons {$WIDTHTYPE}">
									{assign var=LINKS value=$LISTVIEW_ENTRY->getRecordLinks()}
									{if count($LINKS) > 0}
										<div class="actions">
											<div class="float-right">
												{foreach from=$LINKS item=LINK}
													{if $LISTVIEW_ENTRIES_COUNT === 1}
														{if $LINK->getLabel() neq 'LBL_DELETE_RECORD'}
															{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $QUALIFIED_MODULE) BUTTON_VIEW='listViewBasic' MODULE_NAME=$QUALIFIED_MODULE MODULE=$QUALIFIED_MODULE}
														{/if}
													{else}
														{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $QUALIFIED_MODULE) BUTTON_VIEW='listViewBasic' MODULE_NAME=$QUALIFIED_MODULE MODULE=$QUALIFIED_MODULE}
													{/if}
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
							{\App\Language::translate('LBL_NO_RECORDS_FOUND', $QUALIFIED_MODULE)}
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
	</div>
	<!-- /tpl-Settings-Profiles-ListViewContents -->
{/strip}
