{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewPicklist -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	{assign var=INPUT_TYPE value='text'}
	{if $FIELD->isReadOnly()}
		{assign var=INPUT_TYPE value='hidden'}
		<span class="{$FIELD->getColumnName()}">{$ITEM_VALUE}</span>
	{/if}
	<select class="form-control form-control-sm selectInv {$FIELD->getColumnName()}" name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" {if $FIELD->isReadOnly()}readonly="readonly" {/if}>
		{foreach from=$FIELD->getPicklistValues() item=ITEM}
			<option value="{\App\Purifier::encodeHtml($ITEM)}" {if $ITEM == $VALUE}selected{/if}>{\App\Purifier::encodeHtml($ITEM)}</option>
		{/foreach}
	</select>
	<!-- /tpl-Base-inventoryfields-EditViewPicklist -->
{/strip}
