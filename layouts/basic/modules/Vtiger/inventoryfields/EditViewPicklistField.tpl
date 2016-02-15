{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	{assign var="INPUT_TYPE" value='text'}
	{if $FIELD->get('displaytype') == 10}
		{assign var="INPUT_TYPE" value='hidden'}
		<span class="{$FIELD->getColumnName()}">{$ITEM_VALUE}</span>
	{/if}
	<select class="form-control {if !empty($ITEM_DATA['name'])}select2{/if} {$FIELD->getColumnName()}" name="{$FIELD->getColumnName()}{$ROW_NO}" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}>
		{foreach from=$FIELD->getPicklistValues($ITEM_DATA['name']) item=ROW}
			<option value="{$ROW['value']}" data-module="{$ROW['module']}" {if $ROW['value'] == $VALUE} selected {/if}>{$ROW['name']}</option>
		{/foreach}
	</select>
{/strip}
