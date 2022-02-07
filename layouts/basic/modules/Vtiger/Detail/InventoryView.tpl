{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-InventoryView -->
	{assign var="INVENTORY_MODEL" value=Vtiger_Inventory_Model::getInstance($MODULE_NAME)}
	{assign var="FIELDS" value=$INVENTORY_MODEL->getFieldsForView($VIEW)}
	{assign var="INVENTORY_ROWS" value=$RECORD->getInventoryData()}
	{if $FIELDS && $INVENTORY_MODEL->isField('name') && $INVENTORY_ROWS}
		{assign var="BASE_CURRENCY" value=Vtiger_Util_Helper::getBaseCurrency()}
		{assign var="MAIN_PARAMS" value=$INVENTORY_MODEL->getField('name')->getParamsConfig()}
		{assign var="REFERENCE_MODULE_DEFAULT" value=''}
		{if isset($FIELDS[0])}
			{assign var=INVENTORY_ROW value=current($INVENTORY_ROWS)}
			{if isset($INVENTORY_ROW['currency'])}
				{assign var="CURRENCY" value=$INVENTORY_ROW['currency']}
			{else}
				{assign var="CURRENCY" value=$BASE_CURRENCY['id']}
			{/if}
			{assign var="CURRENCY_SYMBOLAND" value=\App\Fields\Currency::getById($CURRENCY)}
			<table class="table table-bordered blockContainer">
				<thead>
					<tr>
						<th style="width: 40%;"></th>
						{foreach item=FIELD from=$FIELDS[0]}
							<th>
								<span class="inventoryLineItemHeader">{\App\Language::translate($FIELD->get('label'), $MODULE_NAME)}
									:</span>&nbsp;
								{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('DetailView',$MODULE_NAME)}
								{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME) ITEM_VALUE=$INVENTORY_ROW[$FIELD->getColumnName()] MODULE=$MODULE_NAME}
							</th>
						{/foreach}
					</tr>
				</thead>
			</table>
		{/if}
		{assign var="FIELDS_TEXT_ALIGN_RIGHT" value=['TotalPrice','Tax','MarginP','Margin','Purchase','Discount','NetPrice','GrossPrice','UnitPrice','Quantity','Unit','TaxPercent']}
		<div class="table-responsive">
			<table class="table table-bordered">
				<thead>
					<tr>
						{foreach item=FIELD from=$FIELDS[1]}
							<th class="textAlignCenter u-table-column__before-block u-table-column__before-block--inventory{if $FIELD->get('colSpan') neq 0 } u-table-column__vw-{$FIELD->get('colSpan')}{/if}">
								{\App\Language::translate($FIELD->get('label'), $MODULE_NAME)}
							</th>
						{/foreach}
					</tr>
				</thead>
				<tbody class="js-inventory-items-body" data-js="container">
					{assign var="ROW_NO" value=0}
					{foreach key=KEY item=INVENTORY_ROW from=$INVENTORY_ROWS}
						{assign var="ROW_NO" value=$ROW_NO+1}
						{assign var="ROW_MODULE" value=\App\Record::getType($INVENTORY_ROW['name'])}
						<tr class="js-inventory-row" data-product-id="{$INVENTORY_ROW['name']}" data-js="data-product-id">
							{foreach item=FIELD from=$FIELDS[1]}
								<td {if in_array($FIELD->getType(), $FIELDS_TEXT_ALIGN_RIGHT)}class="textAlignRight text-nowrap" {/if}>
									{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('DetailView',$MODULE_NAME)}
									{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME) ITEM_VALUE=$INVENTORY_ROW[$FIELD->getColumnName()]}
								</td>
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
				<tfoot>
					<tr>
						{foreach item=FIELD from=$FIELDS[1]}
							<th class="col{$FIELD->getType()} textAlignCenter {if !$FIELD->isSummary()}hideTd{/if}">
								{if $FIELD->isSummary()}
									{\App\Language::translate($FIELD->get('label'), $MODULE_NAME)}
								{/if}
							</th>
						{/foreach}
					</tr>
					<tr>
						{foreach item=FIELD from=$FIELDS[1]}
							<td class="col{$FIELD->getType()} textAlignRight text-nowrap {if !$FIELD->isSummary()}hideTd{else}wisableTd{/if}"
								data-sumfield="{lcfirst($FIELD->getType())}">
								{if $FIELD->isSummary()}
									{assign var="SUM" value=$FIELD->getSummaryValuesFromData($INVENTORY_ROWS)}
									{CurrencyField::convertToUserFormat($SUM, null, true)}
								{/if}
							</td>
						{/foreach}
					</tr>
				</tfoot>
			</table>
		</div>
		{include file=\App\Layout::getTemplatePath('Detail/InventorySummary.tpl', $MODULE_NAME)}
	{/if}
	<!-- /tpl-Base-Detail-InventoryView -->
{/strip}
