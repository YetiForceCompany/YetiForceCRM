{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-Widget-InventoryBlock -->
	<div class="listViewEntriesDiv table-responsive">
		<input type="hidden" name="page" value="{$PAGING_MODEL->get('page')}" />
		<table class="table listViewEntriesTable c-detail-widget__table inventoryItems">
			<thead>
				<tr class="text-left listViewHeaders">
					{foreach from=$HEADER_FIELD item=FIELD key=NAME}
						<th class="p-1" nowrap>
							{\App\Language::translate($FIELD->getLabel(), $MODULE_NAME)}
						</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach from=$ENTRIES item=INVENTORY_ROW}
					{if !empty($INVENTORY_ROW['add_header']) && $GROUP_FIELD  && !empty($INVENTORY_ROW[$GROUP_FIELD->getColumnName()])}
						<tr class="inventoryRowGroup">
							<td class="p-1" colspan="{count($HEADER_FIELD)}" nowrap>
								<div class="u-font-weight-700">
									{$GROUP_FIELD->getListViewDisplayValue($INVENTORY_ROW[$GROUP_FIELD->getColumnName()], $INVENTORY_ROW)}
								</div>
							</td>
						</tr>
					{/if}
					<tr class="listViewEntries">
						{foreach from=$HEADER_FIELD item=FIELD key=NAME}
							<td nowrap>{$FIELD->getListViewDisplayValue($INVENTORY_ROW[$FIELD->getColumnName()], $INVENTORY_ROW)}</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	{if $PAGING_MODEL->isNextPageExists()}
		<div class="d-flex py-1">
			<div class="ml-auto">
				<button type="button" class="btn btn-primary btn-sm moreRecentRecords">{\App\Language::translate('LBL_MORE',$MODULE_NAME)}</button>
			</div>
		</div>
	{/if}
	<!-- /tpl-Base-Detail-Widget-InventoryBlock -->
{/strip}
