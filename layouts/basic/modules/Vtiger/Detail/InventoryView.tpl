{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="INVENTORY_FIELD" value=Vtiger_InventoryField_Model::getInstance($MODULE_NAME)}
	{assign var="FIELDS" value=$INVENTORY_FIELD->getFields(true, [], 'Detail')}

	{if count($FIELDS) neq 0}
		{assign var="COLUMNS" value=$INVENTORY_FIELD->getColumns()}
		{assign var="INVENTORY_ROWS" value=$RECORD->getInventoryData()}
		{assign var="MAIN_PARAMS" value=$INVENTORY_FIELD->getMainParams($FIELDS[1])}
		{assign var="COUNT_FIELDS0" value=count($FIELDS[0])}
		{assign var="COUNT_FIELDS1" value=count($FIELDS[1])}
		{assign var="COUNT_FIELDS2" value=count($FIELDS[2])}
		{assign var="BASE_CURRENCY" value=Vtiger_Util_Helper::getBaseCurrency()}
		{if in_array("currency",$COLUMNS)}
			{if count($INVENTORY_ROWS) > 0 && $INVENTORY_ROWS[0]['currency'] != NULL}
				{assign var="CURRENCY" value=$INVENTORY_ROWS[0]['currency']}
			{else}
				{assign var="CURRENCY" value=$BASE_CURRENCY['id']}
			{/if}
			{assign var="CURRENCY_SYMBOLAND" value=vtlib\Functions::getCurrencySymbolandRate($CURRENCY)}
		{/if}
		{if count($FIELDS[0]) neq 0}
			<table class="table table-bordered inventoryHeader blockContainer">
				<thead>
				<tr>
					<th style="width: 40%;"></th>
					{foreach item=FIELD from=$FIELDS[0]}
						<th>
                            <span class="inventoryLineItemHeader">{\App\Language::translate($FIELD->get('label'), $MODULE_NAME)}
								:</span>&nbsp;
							{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('DetailView',$MODULE_NAME)}
							{assign var="COLUMN_NAME" value=$FIELD->get('columnname')}
							{if $COLUMN_NAME === 'currency'}
								{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME)  ITEM_VALUE=$CURRENCY}
							{else}
								{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME)  ITEM_VALUE=$INVENTORY_ROWS[0][$COLUMN_NAME] MODULE=$MODULE_NAME}
							{/if}
						</th>
					{/foreach}
				</tr>
				</thead>
			</table>
		{/if}
		{assign var="FIELDS_TEXT_ALIGN_RIGHT" value=['TotalPrice','Tax','MarginP','Margin','Purchase','Discount','NetPrice','GrossPrice','UnitPrice','Quantity']}
		<div class="table-responsive">
			<table class="table blockContainer inventoryItems">
				<thead>
				<tr>
					{foreach item=FIELD from=$FIELDS[1]}
						<th {if $FIELD->get('colspan') neq 0 } style="width: {$FIELD->get('colspan')}%" {/if}
								class="textAlignCenter">
							{\App\Language::translate($FIELD->get('label'), $MODULE_NAME)}
						</th>
					{/foreach}
				</tr>
				</thead>
				<tbody class="js-inventory-items-body" data-js="container">
				{foreach key=KEY item=INVENTORY_ROW from=$INVENTORY_ROWS}
					{assign var="ROW_NO" value=$KEY+1}
					{if $INVENTORY_ROW['name']}
						{assign var="ROW_MODULE" value=\App\Record::getType($INVENTORY_ROW['name'])}
					{/if}
					<tr>
						{foreach item=FIELD from=$FIELDS[1]}
							<td {if in_array($FIELD->getName(), $FIELDS_TEXT_ALIGN_RIGHT)}class="textAlignRight"{/if}>
								{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('DetailView',$MODULE_NAME)}
								{if $FIELD->get('columnname') === 'unit'}
									{assign var="INV_RECORD_MODEL" value=Vtiger_Record_Model::getInstanceById($INVENTORY_ROW['name'])}
									{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME) ITEM_VALUE=\App\Language::translate($INVENTORY_ROW[$FIELD->get('columnname')], $INV_RECORD_MODEL->getModuleName())}
								{else}
									{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE_NAME) ITEM_VALUE=$INVENTORY_ROW[$FIELD->get('columnname')]}
								{/if}

							</td>
						{/foreach}
					</tr>
				{/foreach}
				</tbody>
				<tfoot>
				<tr>
					{foreach item=FIELD from=$FIELDS[1]}
						<td {if $FIELD->get('colspan') neq 0 } style="width: {$FIELD->get('colspan')}%" {/if}
								class="col{$FIELD->getName()} textAlignRight {if !$FIELD->isSummary()}hideTd{else}wisableTd{/if}"
								data-sumfield="{lcfirst($FIELD->get('invtype'))}">
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
{/strip}
