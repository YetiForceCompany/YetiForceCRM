{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=CURRENCIES value=Vtiger_Functions::getAllCurrency(true)}
	{assign var=SELECTED_CURRENCY value=$SUP_VALUE}
	{if $SELECTED_CURRENCY eq ''}
		{assign var=USER_CURRENCY_ID value=$USER_MODEL->get('currency_id')}
		{foreach item=CURRENCY from=$CURRENCIES}
			{if $CURRENCY.id eq $USER_CURRENCY_ID}
				{assign var=SELECTED_CURRENCY value=$CURRENCY.id}
			{/if}
		{/foreach}
	{/if}
	<select class="select2 supDataField" data-minimum-results-for-search="-1" name="{$FIELD->getColumnName()}{$ROW_NO}" title="{vtranslate('LBL_CURRENCY', $SUPMODULE)}" {if $FIELD->get('displaytype') == 10}readonly="readonly"{/if}>
		{foreach item=CURRENCY key=count from=$CURRENCIES}
			<option value="{$CURRENCY.id}" class="textShadowNone" data-conversion-rate="{$CURRENCY.conversion_rate}" {if $SELECTED_CURRENCY eq $CURRENCY.id}selected{/if}>
				{vtranslate($CURRENCY.currency_name, $SUPMODULE)} ({$CURRENCY.currency_symbol})
			</option>
		{/foreach}
	</select>
{/strip}
