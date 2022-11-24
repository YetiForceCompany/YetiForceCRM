{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-InventoryBlock -->
	{assign var=FIELDS value=$INVENTORY_MODEL->getFieldsByBlock(1)}
	{assign var=GROUP_FIELD value=$INVENTORY_MODEL->getField('grouplabel')}
	{assign var=BLOCK_ITEMS_HIDE value=$GROUP_FIELD && !$GROUP_FIELD->isOpened()}
	<div class="js-toggle-panel js-inv-container-content c-panel mb-2 mt-2" data-js="click">
		<div class="js-block-header c-panel__header py-2">
			<span class="iconToggle fas fa-chevron-down fa-xs m-2" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down" style="min-width: 15px;"></span>
			<div class="row w-100 ml-1">
				{if $GROUP_FIELD}
					<th class="text-center u-w-1per-45px">
						<button type="button" title="{\App\Language::translate('LBL_INV_ADD_BLOCK', $MODULE_NAME)}"
							class="btn btn-sm btn-light js-inv-add-group border mb-1 mb-lg-0 text-nowrap"
							data-js="click">
							<span class="fas fa-layer-group"></span>
						</button>
					</th>
				{/if}
				<div class="{if $GROUP_FIELD}mt-2 mt-sm-0 col-sm-8{else}col-12{/if}">
					{include file=\App\Layout::getTemplatePath('Edit/InventoryAddItem.tpl', $MODULE_NAME)}
				</div>
			</div>
		</div>
		{assign var="IS_VISIBLE_COMMENTS" value=false}
		{assign var="IS_OPENED_COMMENTS" value=false}
		{assign var="FIELDS_COMMENT" value=$INVENTORY_MODEL->getFieldsByType('Comment')}
		{foreach item=FIELD from=$FIELDS_COMMENT}
			{if !$IS_VISIBLE_COMMENTS || !$IS_OPENED_COMMENTS}
				{if $FIELD->isVisible()}
					{assign var="IS_VISIBLE_COMMENTS" value=true}
				{/if}
				{if $FIELD->isOpened()}
					{assign var="IS_OPENED_COMMENTS" value=true}
				{/if}
			{else}
				{break}
			{/if}
		{/foreach}
		<div class="c-panel__body p-0 js-block-content">
			<div class="table-responsive">
				<table class="table table-bordered inventoryItems mb-0 border-0">
					<thead>
						<tr>
							<th class="text-center u-w-1per-45px"></th>
							{foreach item=FIELD from=$FIELDS}
								<th {if !$FIELD->isEditable()}colspan="0" {/if}
									class="col{$FIELD->getType()}{if !$FIELD->isEditable()} d-none{/if} u-table-column__before-block u-table-column__before-block--inventory{if $FIELD->get('colspan') neq 0 } u-table-column__vw-{$FIELD->get('colspan')}{/if} text-center text-nowrap middle">
									<div class="custom-control-inline mr-0">
										<div class="p-0 form-control-plaintext">{\App\Language::translate($FIELD->get('label'), $FIELD->getModuleName())}</div>
										{if in_array($FIELD->getType(), ['Discount']) && $INVENTORY_MODEL->isField('discountmode')}
											{assign var=DISCOUNT_MODE value=$INVENTORY_MODEL->getField('discountmode')->getEditValue($INVENTORY_ROW)}
											<div class="js-inv-discount_global js-change-discount {if $DISCOUNT_MODE == 1}d-none{/if}">
												<button type="button" class="btn btn-primary btn-xs ml-1" title="{\App\Language::translate('LBL_SET_GLOBAL_DISCOUNT', $MODULE)}">
													<span class="fas fa-sliders-h"></span>
												</button>
											</div>
										{elseif in_array($FIELD->getType(), ['Tax','TaxPercent']) && $INVENTORY_MODEL->isField('taxmode')}
											{assign var=TAX_MODE value=$INVENTORY_MODEL->getField('taxmode')->getEditValue($INVENTORY_ROW)}
											<div class="js-inv-tax_global changeTax {if $TAX_MODE == 1}d-none{/if}">
												<button type="button" class="btn btn-primary btn-xs ml-1" title="{\App\Language::translate('LBL_SET_GLOBAL_TAX', $MODULE)}">
													<span class="fas fa-sliders-h"></span>
												</button>
											</div>
										{/if}
									</div>
								</th>
							{/foreach}
						</tr>
					</thead>
					<tbody class="js-inventory-items-body" data-js="container">
						{foreach key=KEY item=ITEM_DATA from=$INVENTORY_MODEL->transformData($INVENTORY_ROWS)}
							{if !empty($ITEM_DATA['add_header'])}
								{include file=\App\Layout::getTemplatePath('Edit/InventoryHeaderItem.tpl', $MODULE_NAME)}
							{/if}
							{assign var=ROW_NO value=$ROW_NO+1}
							{include file=\App\Layout::getTemplatePath('Edit/InventoryItem.tpl', $MODULE_NAME) HIDE_ROW=$BLOCK_ITEMS_HIDE}
						{foreachelse}
							{if $INVENTORY_MODEL->getField('name')->isRequired()}
								{assign var=ROW_NO value=$ROW_NO+1}
								{assign var="ITEM_DATA" value=$RECORD->getInventoryDefaultDataFields()}
								{include file=\App\Layout::getTemplatePath('Edit/InventoryItem.tpl', $MODULE_NAME)}
							{/if}
						{/foreach}
					</tbody>
					<tfoot {if !$INVENTORY_MODEL->getSummaryFields()}class="d-none" {/if}>
						<tr>
							<td colspan="1" class="hideTd u-w-1per-45px">&nbsp;&nbsp;</td>
							{foreach item=FIELD from=$FIELDS}
								<td {if !$FIELD->isEditable()}colspan="0" {/if}
									class="col{$FIELD->getType()}{if !$FIELD->isEditable()} d-none{/if} text-right text-nowrap {if !$FIELD->isSummaryEnabled()} hideTd{else} wisableTd{/if}"
									data-sumfield="{lcfirst($FIELD->getType())|escape}">
									{if $FIELD->isSummaryEnabled()}
										{CurrencyField::convertToUserFormat($FIELD->getSummaryValuesFromData($INVENTORY_ROWS), null, true)}
									{/if}
									{if $FIELD->getType() == 'Name' && $INVENTORY_MODEL->isField('price')}
										{\App\Language::translate('LBL_SUMMARY', $MODULE_NAME)}
									{/if}
								</td>
							{/foreach}
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
	{if !empty($ADD_EMPTY_ROW)}
		{assign var="ITEM_DATA" value=$RECORD->getInventoryDefaultDataFields()}
		<table id="blackIthemTable" class="noValidate d-none">
			{assign var="INVENTORY_LBLS" value=[]}
			{foreach item=MAIN_MODULE from=$INVENTORY_MODEL->getField('name')->getModules()}
				{$INVENTORY_LBLS[$MAIN_MODULE]=\App\Language::translateSingularModuleName($MAIN_MODULE)}
			{/foreach}
			<tbody class="js-inventory-base-item" data-module-lbls="{App\Purifier::encodeHtml(\App\Json::encode($INVENTORY_LBLS))}">
				{assign var="ROW_NO" value='_NUM_'}
				{include file=\App\Layout::getTemplatePath('Edit/InventoryItem.tpl', $MODULE_NAME)}
				{include file=\App\Layout::getTemplatePath('Edit/InventoryHeaderItem.tpl', $MODULE_NAME)}
			</tbody>
		</table>
	{/if}
	<!-- /tpl-Base-Edit-InventoryBlock -->
{/strip}
