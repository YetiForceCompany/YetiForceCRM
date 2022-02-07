{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewGrossPrice -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<input name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" type="hidden" value="{$FIELD->getEditValue($VALUE)}" class="grossPrice" {if $FIELD->isReadOnly()}readonly="readonly" {/if} />
	<span class="grossPriceText">{$FIELD->getEditValue($VALUE)}</span>
	<!-- /tpl-Base-inventoryfields-EditViewGrossPrice -->
{/strip}
