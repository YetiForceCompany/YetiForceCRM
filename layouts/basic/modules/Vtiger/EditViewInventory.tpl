{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="INVENTORY_FIELD" value=Vtiger_InventoryField_Model::getInstance($MODULE)}
	{assign var="FIELDS" value=$INVENTORY_FIELD->getFields(true)}
	{if count($FIELDS) neq 0}
		{assign var="DISCOUNTS_CONFIG" value=Vtiger_Inventory_Model::getDiscountsConfig()}
		{assign var="TAXS_CONFIG" value=Vtiger_Inventory_Model::getTaxesConfig()}
		{assign var="BASE_CURRENCY" value=Vtiger_Util_Helper::getBaseCurrency()}

		{assign var="COLUMNS" value=$INVENTORY_FIELD->getColumns()}
		{assign var="INVENTORY_ROWS" value=$RECORD->getInventoryData()}
		{assign var="MAIN_PARAMS" value=$INVENTORY_FIELD->getMainParams($FIELDS[1])}
		{assign var="COUNT_FIELDS0" value=count($FIELDS[0])}
		{assign var="COUNT_FIELDS1" value=count($FIELDS[1])}
		{assign var="COUNT_FIELDS2" value=count($FIELDS[2])}

		{if in_array("currency",$COLUMNS)}
			{if count($INVENTORY_ROWS) > 0}
				{assign var="CURRENCY" value=$INVENTORY_ROWS[0]['currency']}
			{else}
				{assign var="CURRENCY" value=$BASE_CURRENCY['id']}
			{/if}
			{assign var="CURRENCY_SYMBOLAND" value=vtlib\Functions::getCurrencySymbolandRate($CURRENCY)}
		{/if}
		{assign var="INVENTORY_ITEMS_NO" value=count($INVENTORY_ROWS)}
		<input type="hidden" class="aggregationTypeDiscount" value="{$DISCOUNTS_CONFIG['aggregation']}">
		<input type="hidden" class="aggregationTypeTax" value="{$TAXS_CONFIG['aggregation']}">
		<input name="inventoryItemsNo" id="inventoryItemsNo" type="hidden" value="{if $INVENTORY_ITEMS_NO}{$INVENTORY_ITEMS_NO}{else}1{/if}" />
		<input id="accountReferenceField" type="hidden" value="{$INVENTORY_FIELD->getReferenceField()}" />
		<input id="inventoryLimit" type="hidden" value="{$MAIN_PARAMS['limit']}" />
		<div class="table-responsive">
			<table class="table table-bordered inventoryHeader blockContainer">
				<thead>
					<tr data-rownumber="0">
						<th class="btn-toolbar">
							{foreach item=MAIN_MODULE from=$MAIN_PARAMS['modules']}
								{assign var="CRMENTITY" value=CRMEntity::getInstance($MAIN_MODULE)}
								<span class="btn-group">
									<button type="button" data-module="{$MAIN_MODULE}" data-field="{$CRMENTITY->table_index}" 
											data-wysiwyg="{$INVENTORY_FIELD->isWysiwygType($MAIN_MODULE)}" class="btn btn-default addItem">
										<span class="glyphicon glyphicon-plus"></span>&nbsp;<strong>{vtranslate('LBL_ADD',$MODULE)} {vtranslate('SINGLE_'|cat:$MAIN_MODULE,$MAIN_MODULE)}</strong>
									</button>
								</span>
							{/foreach}
						</th>
						{foreach item=FIELD from=$FIELDS[0]}
							<th {if !$FIELD->isEditable()}class="hide"{/if}>
								<span class="inventoryLineItemHeader">{vtranslate($FIELD->get('label'), $MODULE)}</span>&nbsp;&nbsp;
								{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('EditView',$MODULE)}
								{include file=$FIELD_TPL_NAME|@vtemplate_path:$MODULE ITEM_VALUE=$INVENTORY_ROWS[0][$FIELD->get('columnname')]}
							</th>
						{/foreach}
					</tr>
				</thead>
			</table>
		</div>
		<div class="table-responsive">
			<table class="table blockContainer inventoryItems">
				{if count($FIELDS[1]) neq 0}
					<thead>
						<tr>
							<th style="width: 5%;">&nbsp;&nbsp;</th>
							{foreach item=FIELD from=$FIELDS[1]}
								<th {if $FIELD->get('colspan') neq 0 } style="width: {$FIELD->get('colspan') * 0.95}%"{/if} class="col{$FIELD->getName()} {if !$FIELD->isEditable()} hide{/if} textAlignCenter">
									{vtranslate($FIELD->get('label'), $MODULE)}
								</th>
							{/foreach}
						</tr>
					</thead>
				{/if}
				<tbody>
					{foreach key=KEY item=ITEM_DATA from=$INVENTORY_ROWS}
						{assign var="ROW_NO" value=$KEY+1}
						{include file='EditViewInventoryItem.tpl'|@vtemplate_path:$MODULE}
					{foreachelse}
						{assign var="ROW_NO" value=1}
						{include file='EditViewInventoryItem.tpl'|@vtemplate_path:$MODULE}
					{/foreach}
				</tbody>
				<tfoot>
					<tr>
						<td colspan="1" class="hideTd" style="min-width: 50px">&nbsp;&nbsp;</td>
						{foreach item=FIELD from=$FIELDS[1]}
							<td colspan="1" class="col{$FIELD->getName()}{if !$FIELD->isEditable()} hide{/if} textAlignRight 
								{if !$FIELD->isSummary()} hideTd{else} wisableTd{/if}" data-sumfield="{lcfirst($FIELD->get('invtype'))}">
								{if $FIELD->isSummary()}
									{assign var="SUM" value=0}
									{foreach key=KEY item=ITEM_VALUE from=$INVENTORY_ROWS}
										{assign var="SUM" value=($SUM + $ITEM_VALUE[$FIELD->get('columnname')])}
									{/foreach}
									{CurrencyField::convertToUserFormat($SUM, null, true)}
								{/if}
								{if $FIELD->getName() == 'Name' && in_array("price",$COLUMNS)}
									{vtranslate('LBL_SUMMARY', $MODULE)}
								{/if}
							</td>
						{/foreach}
					</tr>
				</tfoot>
			</table>
		</div>
		{include file='EditViewInventorySummary.tpl'|@vtemplate_path:$MODULE}
		{assign var="ITEM_DATA" value=$RECORD->getInventoryDefaultDataFields()}
		<table id="blackIthemTable" class="noValidate hide">
			<tbody>
				{assign var="ROW_NO" value='_NUM_'}
				{include file='EditViewInventoryItem.tpl'|@vtemplate_path:$MODULE}
			</tbody>
		</table>
		<br/>
	{/if}
{/strip}
