{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewPicklistField -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	{assign var="INPUT_TYPE" value='text'}
	{if $FIELD->isReadOnly()}
		{assign var="INPUT_TYPE" value='hidden'}
		<span class="{$FIELD->getColumnName()}">{$ITEM_VALUE}</span>
	{/if}
	<select class="form-control selectInv {$FIELD->getColumnName()}" name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" {if $FIELD->isReadOnly()}readonly="readonly"{/if}>
		{if $ROW_NO === '_NUM_'}
			{assign var="REFERENCE_MODULE" value=''}
		{/if}
		{foreach from=$FIELD->getPicklistValues($REFERENCE_MODULE) item=ROW}
			<option value="{$ROW['value']}" data-module="{$ROW['module']}" {if $ROW['value'] == $VALUE} selected {/if}>{$ROW['name']}</option>
		{/foreach}
	</select>
	<!-- tpl-Base-inventoryfields-EditViewPicklistField -->
{/strip}
