{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Inventory -->
	{assign var="INVENTORY_MODEL" value=Vtiger_Inventory_Model::getInstance($MODULE_NAME)}
	{if $INVENTORY_MODEL->isField('name')}
		{assign var="FIELDS" value=$INVENTORY_MODEL->getFieldsByBlocks()}
		{assign var="DISCOUNTS_CONFIG" value=Vtiger_Inventory_Model::getDiscountsConfig()}
		{assign var="TAXS_CONFIG" value=Vtiger_Inventory_Model::getTaxesConfig()}
		{assign var="TAX_DEFAULT" value=Vtiger_Inventory_Model::getDefaultGlobalTax()}
		{assign var="BASE_CURRENCY" value=Vtiger_Util_Helper::getBaseCurrency()}

		{assign var="INVENTORY_ROWS" value=$RECORD->getInventoryData()}
		{assign var="INVENTORY_ROW" value=current($INVENTORY_ROWS)}
		{assign var="MAIN_PARAMS" value=$INVENTORY_MODEL->getField('name')->getParamsConfig()}
		{assign var="IS_REQUIRED_INVENTORY" value=$INVENTORY_MODEL->getField('name')->isRequired()}
		{assign var="COUNT_FIELDS1" value=count($FIELDS[1])}
		{assign var="COUNT_FIELDS2" value=0}
		{assign var="REFERENCE_MODULE_DEFAULT" value=''}
		{assign var="IS_VISIBLE_DESCRIPTION" value=false}
		{if isset($FIELDS[2])}
			{assign var="COUNT_FIELDS2" value=count($FIELDS[2])}
			{foreach item=FIELD from=$FIELDS[2]}
				{if $FIELD->isVisible()}
					{assign var="IS_VISIBLE_DESCRIPTION" value=true}
					{break}
				{/if}
			{/foreach}
		{/if}
		{if $INVENTORY_MODEL->isField('currency')}
			{if $INVENTORY_ROW && !empty($INVENTORY_ROW['currency'])}
				{assign var="CURRENCY" value=$INVENTORY_ROW['currency']}
			{else}
				{assign var="CURRENCY" value=$BASE_CURRENCY['id']}
			{/if}
			{assign var="CURRENCY_SYMBOLAND" value=\App\Fields\Currency::getById($CURRENCY)}
		{/if}
		{assign var="INVENTORY_ITEMS_NO" value=count($INVENTORY_ROWS)}
		{assign var="RELATED_FIELD" value=\App\Field::getRelatedFieldForModule($MODULE_NAME, 'Account')}
		<input type="hidden" class="aggregationTypeDiscount" value="{$DISCOUNTS_CONFIG['aggregation']}">
		<input type="hidden" class="aggregationTypeTax" value="{$TAXS_CONFIG['aggregation']}">
		<input type="hidden" value="{if $INVENTORY_ITEMS_NO}{$INVENTORY_ITEMS_NO}{else}1{/if}" id="inventoryItemsNo"/>
		<input id="accountReferenceField" type="hidden" value="{if $RELATED_FIELD}{$RELATED_FIELD['fieldname']}{/if}"/>
		<input id="inventoryLimit" type="hidden" value="{$MAIN_PARAMS['limit']}"/>
		<input id="isRequiredInventory" type="hidden" value="{$IS_REQUIRED_INVENTORY}"/>
		<div class="table-responsive mx-1">
			<table class="table table-bordered inventoryHeader blockContainer mb-0">
				<thead>
				<tr data-rownumber="0" class="d-flex u-min-w-650px">
					<th class="btn-toolbar col-3 d-flex justify-content-center mb-0 border-bottom-0 p-0">
						{foreach item=MAIN_MODULE from=$MAIN_PARAMS['modules'] name=moduleList}
							{if \App\Module::isModuleActive($MAIN_MODULE)}
								{if $smarty.foreach.moduleList.first}
									{assign var=REFERENCE_MODULE_DEFAULT value=$MAIN_MODULE}
								{/if}
								<div class="btn-group-sm d-flex align-items-center justify-content-center ml-lg-1">
									<button type="button" data-module="{$MAIN_MODULE}"
											title="{\App\Language::translate('LBL_ADD',$MODULE_NAME)} {\App\Language::translate('SINGLE_'|cat:$MAIN_MODULE,$MAIN_MODULE)}"
											class="btn btn-light js-add-item border mb-1 mb-lg-0"
											data-js="click">
										<span class="fas fa-plus"></span>&nbsp;<strong>{\App\Language::translate('SINGLE_'|cat:$MAIN_MODULE,$MAIN_MODULE)}</strong>
									</button>
								</div>
							{/if}
						{/foreach}
					</th>
					{assign var="ROW_NO" value=1}
					{if isset($FIELDS[0])}
						{foreach item=FIELD from=$FIELDS[0]}
							<th class="{if !$FIELD->isEditable()}d-none {/if}col-3 border-bottom-0">
								<span class="inventoryLineItemHeader">{\App\Language::translate($FIELD->get('label'), $MODULE)}</span>&nbsp;&nbsp;
								{assign var="FIELD_TPL_NAME" value="inventoryfields/"|cat:$FIELD->getTemplateName('EditView',$MODULE)}
								{assign var="COLUMN_NAME" value=$FIELD->get('columnName')}
								{if isset($INVENTORY_ROW[$COLUMN_NAME])}
									{assign var="ITEM_VALUE" value=NULL}
								{else}
									{assign var="ITEM_VALUE" value=$INVENTORY_ROW[$COLUMN_NAME]}
								{/if}
								{include file=\App\Layout::getTemplatePath($FIELD_TPL_NAME, $MODULE)}
							</th>
						{/foreach}
					{/if}
				</tr>
				</thead>
			</table>
		</div>
		<div class="table-responsive mx-1">
			<table class="table blockContainer inventoryItems">
				{if count($FIELDS[1]) neq 0}
					<thead>
					<tr>
						<th class="text-center u-w-1per-45px"></th>
						{foreach item=FIELD from=$FIELDS[1]}
							<th class="col{$FIELD->getType()} {if !$FIELD->isEditable()} d-none{/if} text-center text-nowrap {if $FIELD->getType()=='Name'}u-w-3per-250px{/if}">
								{\App\Language::translate($FIELD->get('label'), $MODULE)}
							</th>
						{/foreach}
					</tr>
					</thead>
				{/if}
				<tbody class="js-inventory-items-body" data-js="container">
				{assign var=ROW_NO value=0}
				{foreach key=KEY item=ITEM_DATA from=$INVENTORY_ROWS}
					{assign var=ROW_NO value=$ROW_NO+1}
					{include file=\App\Layout::getTemplatePath('Edit/InventoryItem.tpl', $MODULE_NAME)}
					{foreachelse}
					{if $IS_REQUIRED_INVENTORY}
						{assign var="ROW_NO" value=1}
						{include file=\App\Layout::getTemplatePath('Edit/InventoryItem.tpl', $MODULE_NAME)}
					{/if}
				{/foreach}
				</tbody>
				<tfoot>
				<tr>
					<td colspan="1" class="hideTd">&nbsp;&nbsp;</td>
					{foreach item=FIELD from=$FIELDS[1]}
						<td colspan="1" class="col{$FIELD->getType()}{if !$FIELD->isEditable()} d-none{/if} text-right
								{if !$FIELD->isSummary()} hideTd{else} wisableTd{/if}"
							data-sumfield="{lcfirst($FIELD->getType())}">
							{if $FIELD->isSummary()}
								{assign var="SUM" value=0}
								{foreach key=KEY item=ITEM_VALUE from=$INVENTORY_ROWS}
									{assign var="SUM" value=($SUM + $ITEM_VALUE[$FIELD->get('columnName')])}
								{/foreach}
								{CurrencyField::convertToUserFormat($SUM, null, true)}
							{/if}
							{if $FIELD->getType() == 'Name' && $INVENTORY_MODEL->isField('price')}
								{\App\Language::translate('LBL_SUMMARY', $MODULE)}
							{/if}
						</td>
					{/foreach}
				</tr>
				</tfoot>
			</table>
		</div>
		{include file=\App\Layout::getTemplatePath('Edit/InventorySummary.tpl', $MODULE)}
		{assign var="ITEM_DATA" value=$RECORD->getInventoryDefaultDataFields()}
		<table id="blackIthemTable" class="noValidate d-none">
			<tbody class="js-inventory-base-item">
			{assign var="ROW_NO" value='_NUM_'}
			{include file=\App\Layout::getTemplatePath('Edit/InventoryItem.tpl', $MODULE)}
			</tbody>
		</table>
	{/if}
	<!-- /tpl-Base-Edit-Inventory -->
{/strip}
