{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewNetPrice -->
	{assign var=VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, $FIELD->getColumnName())}
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" type="hidden" value="{$VALUE|escape}" class="netPrice" {if $FIELD->isReadOnly()}readonly="readonly" {/if} />
	<span class="netPriceText">{$VALUE|escape}</span>
	<!-- /tpl-Base-inventoryfields-EditViewNetPrice -->
{/strip}
