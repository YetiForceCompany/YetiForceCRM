{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewPicklist -->
	{assign var=VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, $FIELD->getColumnName())}
	{assign var=INPUT_TYPE value='text'}
	{if $FIELD->isReadOnly()}
		{assign var=INPUT_TYPE value='hidden'}
		<span class="{$FIELD->getColumnName()}">{\App\Purifier::encodeHtml($VALUE)}</span>
	{/if}
	<div class="input-group-sm">
		<select class="form-control form-control-sm selectInv {$FIELD->getColumnName()}" name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" {if $FIELD->isReadOnly()}readonly="readonly" {/if}>
			{foreach from=$FIELD->getPicklistValues() item=ITEM}
				<option value="{\App\Purifier::encodeHtml($ITEM)}" {if $ITEM == $VALUE}selected{/if}>{\App\Language::translate($ITEM, $FIELD->getModuleName())}</option>
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Base-inventoryfields-EditViewPicklist -->
{/strip}
