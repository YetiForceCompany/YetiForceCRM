{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-BusinessHours-ListViewContents -->
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">
	<input type="hidden" value="1" id="pageNumber">
	<input type="hidden" value="0" id="totalCount">
	<input type="hidden" id="previousPageExist" value="false" />
	<input type="hidden" id="nextPageExist" value="false" />
	<div class="listViewEntriesDiv u-overflow-scroll-non-desktop">
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
								{if $LISTVIEW_HEADERNAME==='default_times'}
									<span class="mr-2">{App\Language::translate('LBL_REACTION_TIME','ServiceContracts')}:</span> {$LISTVIEW_ENTRY->getDisplayValue('reaction_time')}<br />
									<span class="mr-2">{App\Language::translate('LBL_IDLE_TIME','ServiceContracts')}:</span> {$LISTVIEW_ENTRY->getDisplayValue('idle_time')}<br />
									<span class="mr-2">{App\Language::translate('LBL_RESOLVE_TIME','ServiceContracts')}:</span> {$LISTVIEW_ENTRY->getDisplayValue('resolve_time')}
								{else}
									{$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
									{if $LISTVIEW_HEADERNAME==='working_days' && $LISTVIEW_ENTRY->getDisplayValue('holidays')}
										, {$LISTVIEW_ENTRY->getDisplayValue('holidays')}
									{/if}
								{/if}
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
												{if !$RECORD_LINK@last} {/if}
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
		{if count($LISTVIEW_ENTRIES) eq '0'}
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
	<!-- /tpl-Settings-BusinessHours-ListViewContents -->
{/strip}
