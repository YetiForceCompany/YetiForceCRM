{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-InventoryGroupSummary -->
	{if !empty($GROUP_FIELD) && $INVENTORY_MODEL->isField('name') && !empty($INVENTORY_ROWS) && array_filter(array_column($INVENTORY_ROWS, 'grouplabel'))}
		{assign var=GROUP_LABELS value=array_column($INVENTORY_ROWS, 'grouplabel', 'groupid')}
		{assign var=INVENTORY_ROW value=current($INVENTORY_ROWS)}
		<div class="js-toggle-panel js-inv-container-group c-panel mb-2 mt-2" data-js="click">
			<div class="js-block-header c-panel__header py-2">
				<span class="iconToggle fas {if !empty($BLOCK_EXPANDED)}fa-chevron-down{else}fa-chevron-right{/if} fa-xs m-2" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down" style="min-width: 15px;"></span>
				<div class="row w-100 ml-1 u-font-weight-700">
					{\App\Language::translate('LBL_INV_GROUP_SUMMARY', $MODULE_NAME)}
				</div>
			</div>
			<div class="c-panel__body p-0 js-block-content {if !empty($BLOCK_EXPANDED)}d-none{/if}">
				<div class="table-responsive">
					<table class="table table-bordered inventoryItems mb-0 border-0">
						<thead>
							<tr>
								<th class="text-center u-w-1per-45px">
									{\App\Language::translate($GROUP_FIELD->getLabel(), $MODULE_NAME)}
								</th>
								{foreach item=FIELD from=$INVENTORY_MODEL->getFieldsByBlock(1)}
									{if !$FIELD->isVisibleInDetail() || !$FIELD->isSummary()}{continue}{/if}
									<th colspan="0"
										class="col{$FIELD->getType()}{if !$FIELD->isEditable()} d-none{/if} u-table-column__before-block u-table-column__before-block--inventory{if $FIELD->get('colspan') neq 0 } u-table-column__vw-{$FIELD->get('colspan')}{/if} text-center text-nowrap">
										{\App\Language::translate($FIELD->getLabel(), $FIELD->getModuleName())}
									</th>
								{/foreach}
							</tr>
						</thead>
						<tbody>
							{foreach key=GROUP_ID item=GROUP_LABEL from=$GROUP_LABELS}
								<tr>
									<td class="p-2 u-font-weight-700">{\App\Purifier::encodeHtml($GROUP_FIELD->getDisplayValue($GROUP_LABEL, $INVENTORY_ROW, true))}</td>
									{foreach item=FIELD from=$INVENTORY_MODEL->getFieldsByBlock(1)}
										{if !$FIELD->isVisibleInDetail() || !$FIELD->isSummary()}{continue}{/if}
										<td class="p-2 text-center text-nowrap">
											{$FIELD->getDisplayValue($FIELD->getSummaryValuesFromData($INVENTORY_ROWS, $GROUP_ID), $INVENTORY_ROW)}
										</td>
									{/foreach}
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	{/if}
	<!-- /tpl-Base-Detail-InventoryGroupSummary -->
{/strip}
