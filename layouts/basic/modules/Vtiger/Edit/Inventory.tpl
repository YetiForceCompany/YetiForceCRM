{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Edit-Inventory -->
	<div class="js-inv-container">
		{assign var="INVENTORY_MODEL" value=Vtiger_Inventory_Model::getInstance($MODULE_NAME)}
		{if $INVENTORY_MODEL->isField('name')}
			{assign var="BASIC_FIELD" value=$INVENTORY_MODEL->getField('name')}
			{assign var="BASE_CURRENCY" value=Vtiger_Util_Helper::getBaseCurrency()}
			{assign var="INVENTORY_ROWS" value=$RECORD->getInventoryData()}
			{if $INVENTORY_ROWS}
				{assign var="INVENTORY_ROW" value=current($INVENTORY_ROWS)}
			{else}
				{assign var="INVENTORY_ROW" value=[]}
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
			{assign var="RELATED_FIELD" value=\App\Field::getRelatedFieldForModule($MODULE_NAME, 'Accounts')}
			{assign var="MAIN_PARAMS" value=$BASIC_FIELD->getParamsConfig()}
			<input type="hidden" class="js-discount-config" value="{App\Purifier::encodeHtml(\App\Json::encode(Vtiger_Inventory_Model::getDiscountsConfig()))}">
			<input type="hidden" class="js-tax-config" value="{App\Purifier::encodeHtml(\App\Json::encode(Vtiger_Inventory_Model::getTaxesConfig()))}">
			<input type="hidden" id="inventoryItemsNo" value="{if $INVENTORY_ITEMS_NO}{$INVENTORY_ITEMS_NO}{else}1{/if}" />
			<input type="hidden" id="accountReferenceField" value="{if $RELATED_FIELD}{$RELATED_FIELD['fieldname']}{/if}" />
			<input type="hidden" id="inventoryLimit" value="{$MAIN_PARAMS['limit']}" />
			<input type="hidden" id="isRequiredInventory" value="{$BASIC_FIELD->isRequired()}" />
			{include file=\App\Layout::getTemplatePath('Edit/InventoryHeader.tpl', $MODULE_NAME)}
			{assign var=ROW_NO value=0}
			{assign var=GROUP_FIELD value=$INVENTORY_MODEL->getField('grouplabel')}
			{if $INVENTORY_ROWS && $GROUP_FIELD}
				{foreach from=$GROUP_FIELD->getDataByGroup($INVENTORY_ROWS) item=BLOCK_DATA name=inv_blocks}
					{assign var=ITEMS_DATA value=current($BLOCK_DATA)}
					{include file=\App\Layout::getTemplatePath('Edit/InventoryBlock.tpl', $MODULE_NAME) INVENTORY_ROW=$ITEMS_DATA INVENTORY_ROWS=$BLOCK_DATA BLOCK_EXPANDED=$GROUP_FIELD->isOpened($smarty.foreach.inv_blocks.iteration) ADD_EMPTY_ROW=$smarty.foreach.inv_blocks.last}
					{assign var=ROW_NO value=count($ITEMS_DATA)}
				{/foreach}
			{else}
				{include file=\App\Layout::getTemplatePath('Edit/InventoryBlock.tpl', $MODULE_NAME) BLOCK_EXPANDED=true ADD_EMPTY_ROW=true}
			{/if}
			{include file=\App\Layout::getTemplatePath('Edit/InventorySummary.tpl', $MODULE_NAME) ITEM_DATA=$INVENTORY_ROW}
		{/if}
	</div>
	<!-- /tpl-Base-Edit-Inventory -->
{/strip}
