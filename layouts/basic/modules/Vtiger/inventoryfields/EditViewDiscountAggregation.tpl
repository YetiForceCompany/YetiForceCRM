{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-inventoryfields-EditViewDiscountAggregation -->
	{assign var=VALUE value=$INVENTORY_MODEL->getEditValue($ITEM_DATA, $FIELD->getColumnName())}
	<select class="select2 js-discount_aggreg"
		title="{\App\Language::translate($FIELD->getLabel(), $MODULE_NAME)}" {if $FIELD->isReadOnly()}readonly="readonly" {/if} data-js="change|val">
		{foreach from=$FIELD->getPicklistValues() item=ITEM key=KEY}
			<option value="{\App\Purifier::encodeHtml($KEY)}" {if $KEY === $VALUE}selected{/if}>{\App\Language::translate($ITEM, $MODULE_NAME)}</option>
		{/foreach}
	</select>
	<!-- /tpl-Base-inventoryfields-EditViewDiscountAggregation -->
{/strip}
