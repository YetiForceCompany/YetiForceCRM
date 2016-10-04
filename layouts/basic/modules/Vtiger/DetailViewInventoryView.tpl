{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
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
								<span class="inventoryLineItemHeader">{vtranslate($FIELD->get('label'), $MODULE_NAME)}:</span>&nbsp;
								{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('DetailView',$MODULE_NAME)}
								{include file=$FIELD_TPL_NAME|@vtemplate_path:$MODULE_NAME ITEM_VALUE=$INVENTORY_ROWS[0][$FIELD->get('columnname')]}
							</th>
						{/foreach}
					</tr>
				</thead>
			</table>
		{/if}
		{assign var="FIELDS_TEXT_ALIGN_RIGHT" value=['TotalPrice','Tax','MarginP','Margin','Purchase','Discount','NetPrice','GrossPrice','UnitPrice','Quantity']}
		<table class="table blockContainer inventoryItems">
			<thead>
				<tr>
					{foreach item=FIELD from=$FIELDS[1]}
						<th {if $FIELD->get('colspan') neq 0 } style="width: {$FIELD->get('colspan')}%" {/if} class="textAlignCenter">
							{vtranslate($FIELD->get('label'), $MODULE_NAME)}
						</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach key=KEY item=INVENTORY_ROW from=$INVENTORY_ROWS}
					{assign var="ROW_NO" value=$KEY+1}
					{if $INVENTORY_ROW['name']}
						{assign var="ROW_MODULE" value=vtlib\Functions::getCRMRecordType($INVENTORY_ROW['name'])}
					{/if}
					<tr>
						{foreach item=FIELD from=$FIELDS[1]}
							<td {if in_array($FIELD->getName(), $FIELDS_TEXT_ALIGN_RIGHT)}class="textAlignRight"{/if}>
								{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('DetailView',$MODULE_NAME)}
								{include file=$FIELD_TPL_NAME|@vtemplate_path:$MODULE_NAME ITEM_VALUE=$INVENTORY_ROW[$FIELD->get('columnname')]}
							</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
			<tfoot>
				<tr>
					{foreach item=FIELD from=$FIELDS[1]}
						<td {if $FIELD->get('colspan') neq 0 } style="width: {$FIELD->get('colspan')}%" {/if}  class="col{$FIELD->getName()} textAlignRight {if !$FIELD->isSummary()}hideTd{else}wisableTd{/if}" data-sumfield="{lcfirst($FIELD->get('invtype'))}">
							{if $FIELD->isSummary()}
								{assign var="SUM" value=$FIELD->getSummaryValuesFromData($INVENTORY_ROWS)}
								{CurrencyField::convertToUserFormat($SUM, null, true)}
							{/if}
						</td>
					{/foreach}
				</tr>
			</tfoot>
		</table>
		{include file='DetailViewInventorySummary.tpl'|@vtemplate_path:$MODULE_NAME}
	{/if}
{/strip}
