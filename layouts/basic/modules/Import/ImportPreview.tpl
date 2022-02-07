{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Import-ImportPreview modal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg modal-full" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate($MODULE, $MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="listViewContentDiv" id="listViewContents">
						<div class="listViewEntriesDiv u-overflow-scroll-non-desktop">
							<table class="table table-bordered listViewEntriesTable">
								<thead>
									<tr class="listViewHeaders">
										<th class="{$WIDTHTYPE}"></th>
										{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
											<th class="{$WIDTHTYPE}">
												{App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE_NAME)}
											</th>
										{/foreach}
									</tr>
								</thead>
								{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES}
									<tr>
										<td class="{$WIDTHTYPE}">
											<a role="button" class="btn btn-sm btn-default" href="index.php?module={$MODULE_NAME}&view=Detail&record={$LISTVIEW_ENTRY->getId()}"><span class="fas fa-th-list "></span></a>
										</td>
										{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
											<td class="{$WIDTHTYPE}">
												{if $LISTVIEW_HEADER->get('fromOutsideList') eq true}
													{$LISTVIEW_HEADER->getDisplayValue($LISTVIEW_ENTRY->get($LISTVIEW_HEADER->getFieldName()))}
												{else}
													{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADER->getFieldName(),true)}
												{/if}
											</td>
										{/foreach}
									</tr>
								{/foreach}
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
