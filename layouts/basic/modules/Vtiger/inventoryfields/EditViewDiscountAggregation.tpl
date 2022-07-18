{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewDiscountAggregation -->
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	{if $VALUE === ''}
		{assign var=VALUE value=$INVENTORY_MODEL->getDiscountsConfig('aggregation')}
	{/if}
	<select name="inventory[{$ROW_NO}][{$FIELD->getColumnName()}]" class="select2 js-discount_aggreg"
		title="{\App\Language::translate('LBL_DISCOUNT_AGGREGATION', $MODULE)}" {if $FIELD->isReadOnly()}readonly="readonly" {/if} data-js="change|val">
		{foreach from=$FIELD->getPicklistValues() item=ITEM key=KEY}
			<option value="{$KEY}" {if $KEY == $VALUE}selected{/if}>{\App\Language::translate($ITEM, $MODULE)}</option>
		{/foreach}
	</select>
	<!-- /tpl-Base-inventoryfields-EditViewDiscountAggregation -->
{/strip}
