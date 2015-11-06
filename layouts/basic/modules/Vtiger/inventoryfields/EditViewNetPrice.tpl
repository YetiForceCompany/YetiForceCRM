{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	<input name="{$FIELD->getColumnName()}{$ROW_NO}" type="hidden" value="{$FIELD->getEditValue($VALUE)}" class="netPrice" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}/>
	<span class="netPriceText">{$FIELD->getEditValue($VALUE)}</span>
{/strip}
