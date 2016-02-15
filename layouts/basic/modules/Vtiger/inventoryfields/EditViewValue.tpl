{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	{assign var="INPUT_TYPE" value='text'}
	{if $FIELD->get('displaytype') == 10}
		{assign var="INPUT_TYPE" value='hidden'}
		<span class="{$FIELD->getColumnName()}Text valueText">
			{$FIELD->getDisplayValue($VALUE)}
		</span>
	{/if}
	<input name="{$FIELD->getColumnName()}{$ROW_NO}" type="{$INPUT_TYPE}" class="form-control {$FIELD->getColumnName()} valueVal" value="{$FIELD->getEditValue($VALUE)}" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}/>
{/strip}
