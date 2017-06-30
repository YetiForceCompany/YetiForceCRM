{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<input name="{$FIELD->getColumnName()}{$ROW_NO}" type="hidden" value="{$FIELD->getEditValue($VALUE)}" class="totalPrice" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if} />
	<span class="totalPriceText">{$FIELD->getEditValue($VALUE)}</span>
{/strip}
